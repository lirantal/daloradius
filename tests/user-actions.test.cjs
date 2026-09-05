const test = require('node:test');
const assert = require('node:assert/strict');
const fs = require('node:fs');
const path = require('node:path');
const vm = require('node:vm');
const source = fs.readFileSync(path.join(__dirname, '../app/common/static/js/pages_common.js'), 'utf8');

function setup(fetch, selected = ["O'Reilly & + é 50%", 'second']) {
    class Element {
        constructor() { this.children = []; this.attrs = {}; this.disabled = false; }
        set innerHTML(_) { assert.fail('HTML must not be evaluated'); }
        set textContent(text) { this.text = text; }
        get textContent() { return this.text || this.children.map(c => c.textContent).join(''); }
        appendChild(child) { this.children.push(child); }
        replaceChildren() { this.children = []; this.text = ''; }
        setAttribute(k, v) { this.attrs[k] = v; }
        removeAttribute(k) { delete this.attrs[k]; }
        getAttribute(k) { return this.attrs[k]; }
    }
    const target = new Element();
    const buttons = ['disableUser()', "refillSessionTimeCheckbox('listall')", 'enableCheckbox()', 'removeCheckbox()']
        .map(onclick => { const e = new Element(); e.attrs.onclick = onclick; return e; });
    buttons[2].disabled = true;
    const token = { value: 'fixture-token' };
    const form = { querySelector: () => token, querySelectorAll: selector => {
        assert.equal(selector, 'input[type="checkbox"][name="username[]"]:checked');
        return selected.map(value => ({ value }));
    } };
    const alerts = [];
    const context = vm.createContext({ URL, URLSearchParams, fetch, alert: m => alerts.push(m), confirm: () => true,
        document: { baseURI: 'http://localhost/operators/mng-list-all.php', forms: { listall: form },
            getElementById: id => id === 'returnMessages' ? target : null,
            querySelector: () => token, querySelectorAll: () => buttons, createElement: () => new Element() }
    });
    vm.runInContext(source, context);
    // Existing layout can include pages_common.js twice.
    vm.runInContext(source, context);
    return { context, target, buttons, token, form, alerts };
}
function response(data = { success: true, message: 'Done', level: 'success' }, extra = {}) {
    return { ok: true, status: 200, redirected: false, headers: { get: () => 'application/json' }, json: async () => data, ...extra };
}

test('individual mutations use POST body, exact usernames and CSRF', async () => {
    for (const action of ['userEnable', 'userDisable', 'userMail', 'refillSessionTime', 'refillSessionTraffic']) {
        const username = "O'Reilly & + é 50%";
        const { context, target } = setup(async (url, options) => {
            assert.equal(url.search, '');
            assert.equal(options.method, 'POST');
            assert.equal(options.credentials, 'same-origin');
            assert.equal(options.body.get('action'), action);
            assert.equal(options.body.get('csrf_token'), 'fixture-token');
            assert.deepEqual(options.body.getAll('username[]'), [username]);
            return response({ success: true, message: '<script>not HTML</script>', level: 'success' });
        });
        assert.equal(await context.userAction(action, [username]), true);
        assert.equal(target.textContent, '<script>not HTML</script>');
    }
});

test('disabled-state consultation remains a read-only GET', async () => {
    const { context, token, target } = setup(async (url, options) => {
        assert.equal(options.method, 'GET');
        assert.equal(options.body, undefined);
        assert.equal(url.searchParams.get('action'), 'checkDisabled');
        assert.equal(url.searchParams.has('csrf_token'), false);
        return response({ success: true, message: '', level: 'success', disabled: false });
    });
    token.value = '';
    await context.userAction('checkDisabled', ['alice']);
    assert.equal(target.textContent, '');
});

test('bulk wrappers select only the named form usernames', async () => {
    for (const [wrapper, action] of [['disableCheckbox', 'userDisable'], ['enableCheckbox', 'userEnable'],
        ['mailCheckbox', 'userMail'], ['refillSessionTimeCheckbox', 'refillSessionTime'], ['refillSessionTrafficCheckbox', 'refillSessionTraffic']]) {
        let calls = 0;
        const { context } = setup(async (url, options) => {
            calls++;
            assert.deepEqual(options.body.getAll('username[]'), ["O'Reilly & + é 50%", 'second']);
            assert.equal(options.body.get('action'), action);
            return response();
        });
        context[wrapper]('listall');
        await new Promise(resolve => setImmediate(resolve));
        assert.equal(calls, 1);
    }
});

test('empty selection and missing CSRF never send', async () => {
    const { context, token, alerts, target } = setup(() => assert.fail('must not send'), []);
    context.disableCheckbox('listall');
    assert.deepEqual(alerts, ['No items selected']);
    assert.equal(await context.userAction('userEnable', []), false);
    token.value = '';
    assert.equal(await context.userAction('userEnable', ['alice']), false);
    assert.match(target.textContent, /CSRF/);
});

test('double click is blocked and original button states restored', async () => {
    let resolve, calls = 0;
    const { context, target, buttons } = setup(() => { calls++; return new Promise(r => { resolve = r; }); });
    const first = context.userAction('refillSessionTime', ['alice']);
    assert.equal(buttons[0].disabled, true);
    assert.equal(buttons[1].disabled, true);
    assert.equal(buttons[3].disabled, false);
    assert.equal(target.attrs['aria-busy'], 'true');
    assert.equal(await context.userAction('refillSessionTime', ['alice']), false);
    context.refillSessionTimeCheckbox('listall');
    assert.equal(calls, 1);
    resolve(response());
    await first;
    assert.equal(buttons[0].disabled, false);
    assert.equal(buttons[1].disabled, false);
    assert.equal(buttons[2].disabled, true);
    assert.equal(target.attrs['aria-busy'], undefined);
});

for (const [name, fetch, expected] of [
    ['network', async () => { throw new Error('offline'); }, /not been retried/],
    ['redirect', async () => response({}, { redirected: true }), /session has expired/],
    ['403', async () => response({}, { ok: false, status: 403 }), /Permission denied/],
    ['HTML error', async () => response({}, { ok: false, status: 500, headers: { get: () => 'text/html' } }), /not been retried/],
    ['invalid JSON', async () => response({}, { json: async () => { throw new Error('parse'); } }), /not been retried/],
    ['invalid result', async () => response({ success: true }), /not been retried/],
    ['business failure', async () => response({ success: false, message: 'Already disabled', level: 'danger' }), /Already disabled/],
    ['partial billing failure', async () => response({ success: false, message: 'Check billing records', level: 'danger' }, { status: 500, ok: false }), /Check billing/],
]) {
    test('failure without false success or retry: ' + name, async () => {
        let calls = 0;
        const { context, target, buttons } = setup((...args) => { calls++; return fetch(...args); });
        assert.equal(await context.userAction('refillSessionTraffic', ['alice']), false);
        assert.equal(calls, 1);
        assert.match(target.textContent, expected);
        assert.equal(target.children[0].className, 'alert alert-danger');
        assert.equal(buttons[0].disabled, false);
    });
}

test('all direct action callers migrated and no SACK consumer left on these pages', () => {
    for (const page of ['mng-edit', 'bill-pos-edit', 'mng-list-all', 'mng-search', 'bill-pos-list', 'rep-batch-details']) {
        const php = fs.readFileSync(path.join(__dirname, '../app/operators', page + '.php'), 'utf8');
        assert.doesNotMatch(php, /ajaxGeneric|static\/js\/ajax.js/);
        if (page.endsWith('edit')) {
            assert.match(php, /json_encode\(\$username, JSON_HEX_TAG/);
            assert.match(php, /userAction\("checkDisabled"/);
        }
    }
});
