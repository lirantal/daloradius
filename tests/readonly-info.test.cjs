const test = require('node:test');
const assert = require('node:assert/strict');
const fs = require('node:fs');
const path = require('node:path');
const vm = require('node:vm');
const root = path.resolve(__dirname, '..');

// Minimal DOM double: assigning HTML would fail, not silently pass this test.
class Element {
    constructor(tag = 'div') { this.tag = tag; this.children = []; this.style = {}; this.attrs = {}; this.isConnected = true; }
    set textContent(value) { this.children = [String(value)]; }
    get textContent() { return this.children.map(x => typeof x === 'string' ? x : x.textContent).join(''); }
    set innerHTML(value) { throw new Error('Unsafe HTML assignment'); }
    appendChild(node) { this.children.push(node); }
    replaceChildren(node) { this.children = node.children; }
    setAttribute(key, value) { this.attrs[key] = value; }
    removeAttribute(key) { delete this.attrs[key]; }
}
function setup(fetch) {
    const nodes = { a: new Element(), b: new Element() };
    const context = vm.createContext({ URL, URLSearchParams, fetch, window: { location: { origin: 'http://localhost' } }, document: {
        baseURI: 'http://localhost/operators/mng-list-all.php',
        getElementById: id => nodes[id],
        createElement: tag => new Element(tag),
        createDocumentFragment: () => new Element('fragment'),
        createTextNode: text => { const node = new Element('text'); node.textContent = text; return node; }
    } });
    for (const name of ['request.js', 'readonly_info.js']) {
        vm.runInContext(fs.readFileSync(path.join(root, 'app/common/static/js', name), 'utf8'), context);
    }
    return { nodes, info: vm.runInContext('daloInfo', context), request: context.daloRequestJSON };
}
function response(data, extra = {}) {
    return { ok: true, status: 200, redirected: false, headers: { get: () => 'application/json; charset=UTF-8' }, json: async () => data, ...extra };
}

test('same-origin GET encodes parameter values exactly once', async () => {
    const value = "é O'Reilly & + / 50%";
    const { info, nodes } = setup(async (url, options) => {
        assert.equal(url.pathname, '/operators/library/ajax/user_info.php');
        assert.equal(url.searchParams.get('username'), value);
        assert.equal(url.searchParams.has('divContainer'), false);
        assert.equal(options.method, 'GET');
        assert.equal(options.credentials, 'same-origin');
        assert.equal(options.cache, 'no-store');
        return response({ upload: '1 KB', download: '(n/a)' });
    });
    await info.user('a', new URLSearchParams({ username: value }).toString());
    assert.equal(nodes.a.textContent, 'Upload: 1 KBDownload: (n/a)');
    assert.equal(nodes.a.attrs['aria-busy'], undefined);
});

test('hotspot totals and untrusted descriptions render as text', async () => {
    const payload = "<img src=x onerror=alert(1)> O'Reilly & é";
    const { info, nodes } = setup(async url => response(url.pathname.endsWith('hotspot_info.php')
        ? { upload: '(n/a)', download: '2 MB', hits: 7 } : { description: payload }));
    await info.hotspot('a', 'hotspot=foo');
    assert.equal(nodes.a.textContent, 'Total Uploads: (n/a)Total Downloads: 2 MBTotal Hits: 7');
    await info.attribute('b', 'attribute=foo');
    assert.equal(nodes.b.textContent, 'Description: ' + payload);
    assert.equal(nodes.b.children[1].tag, 'span');
});

for (const [name, fetch, expected] of [
    ['redirected login', async () => response({}, { redirected: true }), /session has expired/],
    ['401', async () => response({}, { status: 401, ok: false }), /session has expired/],
    ['403', async () => response({}, { status: 403, ok: false }), /permission/],
    ['500', async () => response({}, { status: 500, ok: false }), /HTTP 500/],
    ['network', async () => { throw new Error('offline'); }, /connection/],
    ['HTML', async () => response({}, { headers: { get: () => 'text/html' } }), /Invalid response/],
    ['broken JSON', async () => response({}, { json: async () => { throw new Error('parse'); } }), /Invalid response/],
    ['null JSON', async () => response(null), /Invalid response/],
    ['array JSON', async () => response([]), /Invalid response/],
    ['missing fields', async () => response({}), /Invalid response/],
    ['object value', async () => response({ upload: {}, download: '0 B' }), /Invalid response/]
]) {
    test('visible error: ' + name, async () => {
        const { info, nodes } = setup(fetch);
        await info.user('a', 'username=test');
        assert.match(nodes.a.textContent, expected);
        assert.equal(nodes.a.attrs['aria-busy'], undefined);
    });
}

test('cross-origin requests are rejected before fetch', async () => {
    const { request } = setup(() => assert.fail('must not fetch'));
    await assert.rejects(request('https://example.com/', {}), /Invalid request destination/);
});

test('latest request wins per target; other rows remain independent', async () => {
    const jobs = [];
    const { info, nodes } = setup(() => new Promise(resolve => jobs.push(resolve)));
    const first = info.user('a', 'username=old');
    const second = info.user('a', 'username=new');
    const other = info.user('b', 'username=other');
    jobs[1](response({ upload: 'new', download: 'new' })); await second;
    jobs[2](response({ upload: 'other', download: 'other' })); await other;
    jobs[0](response({}, { ok: false, status: 500 })); await first;
    assert.equal(nodes.a.textContent, 'Upload: newDownload: new');
    assert.equal(nodes.b.textContent, 'Upload: otherDownload: other');
});

test('missing and detached targets are safe', async () => {
    let resolve;
    const { info, nodes } = setup(() => new Promise(r => { resolve = r; }));
    await info.user('missing', 'username=test');
    const job = info.user('a', 'username=test');
    nodes.a.isConnected = false;
    resolve(response({ upload: 'late', download: 'late' })); await job;
    assert.equal(nodes.a.textContent, 'Loading...');
});

test('all read-only page callers load the new scripts; mutation pages keep SACK', () => {
    const keep = new Set(['bill-pos-list.php', 'mng-list-all.php', 'mng-search.php', 'rep-batch-details.php']);
    let count = 0;
    for (const name of fs.readdirSync(path.join(root, 'app/operators'))) {
        if (!name.endsWith('.php')) continue;
        const source = fs.readFileSync(path.join(root, 'app/operators', name), 'utf8');
        assert.doesNotMatch(source, /ajaxGeneric\(['"]library\/ajax\/(user_info|hotspot_info|vendor_attribute_info)\.php/);
        if (!source.includes('daloInfo.')) continue;
        count++;
        assert.ok(source.includes('static/js/request.js'), name);
        assert.ok(source.includes('static/js/readonly_info.js'), name);
        assert.equal(source.includes('static/js/ajax.js'), keep.has(name), name);
        assert.equal(source.includes('static/js/ajaxGeneric.js'), keep.has(name), name);
    }
    assert.equal(count, 18);
});
