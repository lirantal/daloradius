const test = require('node:test');
const assert = require('node:assert/strict');
const fs = require('node:fs');
const path = require('node:path');
const vm = require('node:vm');

const root = path.join(__dirname, '..');
const source = fs.readFileSync(path.join(root, 'app/common/static/js/dynamic_attributes.js'), 'utf8');

class Element {
    constructor(type = 'div') {
        this.type = type;
        this.children = [];
        this.dataset = {};
        this.attributes = {};
        this.value = '';
        this.disabled = false;
        this.isConnected = true;
    }
    get options() { return this.children; }
    appendChild(child) { child.isConnected = this.isConnected; this.children.push(child); }
    replaceChildren(...children) { this.children = children; }
    setAttribute(name, value) { this.attributes[name] = String(value); }
    removeAttribute(name) { delete this.attributes[name]; }
    addEventListener(name, callback) { this['on' + name] = callback; }
    remove() { this.isConnected = false; }
    set textContent(value) { this.text = value; }
    get textContent() { return this.text || ''; }
}

function setup(request) {
    const elements = {};
    const alerts = [];
    const document = {
        baseURI: 'http://localhost/operators/mng-new.php',
        createElement: tag => new Element(tag === 'select' ? 'select-one' : tag),
        getElementById: id => elements[id] || null,
    };
    const context = vm.createContext({ document, daloRequestJSON: request, alert: message => alerts.push(message) });
    vm.runInContext(source, context);
    return { context, elements, alerts };
}

function deferred() {
    let resolve;
    const promise = new Promise(r => { resolve = r; });
    return { promise, resolve };
}

function detailElements(elements, suffix) {
    elements['values' + suffix] = new Element('text');
    elements['op' + suffix] = new Element('select-one');
    elements['table' + suffix] = new Element('select-one');
    elements['tooltip' + suffix] = new Element();
    elements['type' + suffix] = new Element();
    elements['helper' + suffix] = new Element();
}

function detail(attribute, helper = { type: 'none', options: [], initialValue: null }) {
    return {
        attribute, found: true, recommendedOperator: ':=', recommendedTable: 'reply',
        operators: ['==', ':='], tables: ['check', 'reply'], description: 'Description ' + attribute,
        type: 'string', helper,
    };
}

test('latest vendor and attribute requests win independently', async () => {
    const pending = new Map();
    const { context, elements } = setup((path, params) => {
        const key = params.vendorAttributes || 'vendors';
        const item = deferred(); pending.set(key, item); return item.promise;
    });
    const vendor = elements.vendor = new Element('select-one');
    const attributes = elements.attributes = new Element('select-one');

    vendor.value = 'slow';
    const slow = context.getAttributesList(vendor, 'attributes');
    vendor.value = 'fast';
    const fast = context.getAttributesList(vendor, 'attributes');
    pending.get('fast').resolve({ vendor: 'fast', attributes: ['Fast-Attribute'] });
    await fast;
    pending.get('slow').resolve({ vendor: 'slow', attributes: ['Slow-Attribute'] });
    await slow;
    assert.deepEqual(attributes.options.map(option => option.value), ['', 'Fast-Attribute']);

    const firstReload = context.getVendorsList('vendor');
    const secondReload = context.getVendorsList('vendor');
    // Both reloads use the same map key, so resolve the current request and leave the stale one detached.
    pending.get('vendors').resolve({ vendors: ['Current'] });
    await secondReload;
    assert.deepEqual(vendor.options.map(option => option.value), ['', 'Current']);
    vendor.isConnected = false;
    assert.equal(await Promise.race([firstReload.then(() => true), Promise.resolve(false)]), false);
});

test('latest attribute wins per row, rows stay independent and removal is safe', async () => {
    const pending = {};
    const { context, elements } = setup((path, params) => {
        pending[params.getValuesForAttribute] = deferred();
        return pending[params.getValuesForAttribute].promise;
    });
    detailElements(elements, 'A');
    detailElements(elements, 'B');
    const staleA = context.loadAttributeDetails('old-A', 'valuesA', 'opA', 'tableA', 'tooltipA', 'typeA', 'helperA');
    const currentA = context.loadAttributeDetails('new-A', 'valuesA', 'opA', 'tableA', 'tooltipA', 'typeA', 'helperA');
    const removedB = context.loadAttributeDetails('B', 'valuesB', 'opB', 'tableB', 'tooltipB', 'typeB', 'helperB');
    elements.valuesB.isConnected = false;
    pending['new-A'].resolve(detail('new-A'));
    await currentA;
    pending['old-A'].resolve(detail('old-A'));
    await staleA;
    pending.B.resolve(detail('B'));
    await removedB;
    assert.equal(elements.tooltipA.textContent, 'Description: Description new-A');
    assert.equal(elements.tooltipB.textContent, 'Description: (n/a)');
});

test('changing an attribute resets every old helper state', async () => {
    const responses = [
        detail('first', { type: 'datalist', options: [{ value: 'one', label: 'one' }], initialValue: null }),
        detail('second', { type: 'none', options: [], initialValue: null }),
    ];
    const { context, elements } = setup(async () => responses.shift());
    detailElements(elements, 'A');
    await context.loadAttributeDetails('first', 'valuesA', 'opA', 'tableA', 'tooltipA', 'typeA', 'helperA');
    assert.equal(elements.valuesA.attributes.placeholder, 'double click or start typing...');
    assert.ok(elements.valuesA.attributes.list);
    assert.equal(elements.helperA.children.length, 1);
    await context.loadAttributeDetails('second', 'valuesA', 'opA', 'tableA', 'tooltipA', 'typeA', 'helperA');
    assert.equal(elements.valuesA.type, 'text');
    assert.equal(elements.valuesA.attributes.placeholder, undefined);
    assert.equal(elements.valuesA.attributes.list, undefined);
    assert.equal(elements.helperA.children.length, 0);
});

test('date, list, volume, rate and Mikrotik helper contracts render', async () => {
    const helpers = [
        { type: 'datetime', options: [], initialValue: '2026-09-05T12:30' },
        { type: 'date', options: [], initialValue: 'Sat 5 Sep 2026 12:30:00 CEST' },
        { type: 'datalist', options: [{ value: 'PPP', label: 'PPP' }], initialValue: null },
        { type: 'select', options: [{ value: '10485760', label: '10 MB' }], initialValue: null },
        { type: 'select', options: [{ value: '32000', label: '32 Kbps' }], initialValue: null },
        { type: 'datalist', options: [{ value: '128k/128k', label: '128k/128k' }], initialValue: null },
    ];
    const { context, elements } = setup(async (path, params) => detail(params.getValuesForAttribute, helpers.shift()));
    detailElements(elements, 'A');
    for (const attribute of ['datetime', 'date', 'list', 'volume', 'rate', 'mikrotik']) {
        await context.loadAttributeDetails(attribute, 'valuesA', 'opA', 'tableA', 'tooltipA', 'typeA', 'helperA');
        assert.equal(elements.opA.options[0].value, ':=');
        assert.equal(elements.tableA.options[0].value, 'reply');
    }
    assert.equal(helpers.length, 0);
});

test('submitted PHP field grouping and all SACK assets are preserved/removed as required', () => {
    assert.match(source, /fieldName = 'dictValues' \+ dictCounter \+ '\[\]'/);
    assert.match(source, /name="' \+ fieldName/);
    const pages = fs.readdirSync(path.join(root, 'app/operators')).filter(name => name.endsWith('.php'));
    for (const page of pages) {
        const php = fs.readFileSync(path.join(root, 'app/operators', page), 'utf8');
        assert.doesNotMatch(php, /static\/js\/ajax(?:Generic)?\.js/);
        if (php.includes('static/js/dynamic_attributes.js')) assert.match(php, /static\/js\/request\.js/);
    }
    assert.equal(fs.existsSync(path.join(root, 'app/common/static/js/ajax.js')), false);
    assert.equal(fs.existsSync(path.join(root, 'app/common/static/js/ajaxGeneric.js')), false);
    assert.doesNotMatch(source, /\beval\s*\(|\bsack\s*\(|\bvar ajax\b/);
});
