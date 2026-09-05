// Counter for the submitted attribute groups. The PHP form handler expects
// four values under each dictValuesN[] name: attribute, value, operator, table.
var dictCounter = 1;
var daloAttributeRequestCounter = 0;

function attributeRequestId(element) {
    var id = String(++daloAttributeRequestCounter);
    element.dataset.attributeRequestId = id;
    return id;
}

function attributeRequestIsCurrent(element, id) {
    return element && element.isConnected && element.dataset.attributeRequestId === id;
}

function addOption(select, value, label) {
    var option = document.createElement('option');
    option.value = value;
    option.textContent = label;
    select.appendChild(option);
}

function populateRecommendedSelect(element, values, recommended) {
    var seen = Object.create(null);
    element.replaceChildren();
    if (recommended) {
        addOption(element, recommended, recommended);
        seen[recommended] = true;
    }
    values.forEach(function(value) {
        if (!seen[value]) {
            addOption(element, value, value);
            seen[value] = true;
        }
    });
}

function resetAttributeFields(valuesElem, opElem, tableElem, tooltipElem, typeElem, helperElem) {
    valuesElem.type = 'text';
    valuesElem.value = '';
    valuesElem.removeAttribute('list');
    valuesElem.removeAttribute('placeholder');
    opElem.replaceChildren();
    if (tableElem.type === 'select-one') {
        tableElem.replaceChildren();
    } else {
        tableElem.value = '';
    }
    helperElem.replaceChildren();
    tooltipElem.textContent = 'Description: (n/a)';
    typeElem.textContent = 'Type: (n/a)';
}

async function getVendorsList(sel) {
    var vendors = document.getElementById(sel);
    if (!vendors) return;

    var requestId = attributeRequestId(vendors);
    vendors.disabled = true;
    try {
        var data = await daloRequestJSON('library/ajax/attributes.php', { getVendorsList: 'yes' });
        if (!attributeRequestIsCurrent(vendors, requestId)) return;
        vendors.replaceChildren();
        addOption(vendors, '', '');
        (data.vendors || []).forEach(function(vendor) { addOption(vendors, vendor, vendor); });
        vendors.disabled = data.vendors.length === 0;
        if (data.vendors.length === 0) alert('No vendors found. Is the dictionary empty?');
    } catch (error) {
        if (!attributeRequestIsCurrent(vendors, requestId)) return;
        vendors.disabled = vendors.options.length <= 1;
        alert(error.message);
    }
}

async function getAttributesList(sel, attributesSel) {
    var attributes = document.getElementById(attributesSel);
    if (!sel || !attributes) return;

    var vendorName = sel.value;
    var requestId = attributeRequestId(attributes);
    attributes.replaceChildren();
    addOption(attributes, '', 'Select Attribute...');
    attributes.disabled = true;
    if (!vendorName) return;

    try {
        var data = await daloRequestJSON('library/ajax/attributes.php', { vendorAttributes: vendorName });
        if (!attributeRequestIsCurrent(attributes, requestId) || sel.value !== vendorName) return;
        (data.attributes || []).forEach(function(attribute) { addOption(attributes, attribute, attribute); });
        attributes.disabled = data.attributes.length === 0;
        if (data.attributes.length === 0) alert('No attributes found for ' + vendorName + '.');
    } catch (error) {
        if (!attributeRequestIsCurrent(attributes, requestId) || sel.value !== vendorName) return;
        alert(error.message);
    }
}

function buildAttributeHelper(valuesElem, helperElem, helper) {
    if (!helper || helper.type === 'none') return;
    if (helper.type === 'datetime') {
        valuesElem.type = 'datetime-local';
        valuesElem.value = helper.initialValue || '';
        return;
    }
    if (helper.type === 'date') {
        valuesElem.type = 'text';
        valuesElem.value = helper.initialValue || '';
        return;
    }

    var options = Array.isArray(helper.options) ? helper.options : [];
    if (helper.type === 'select') {
        var select = document.createElement('select');
        select.className = 'form-select';
        options.forEach(function(option) { addOption(select, option.value, option.label); });
        select.addEventListener('change', function() { valuesElem.value = select.value; });
        helperElem.appendChild(select);
        return;
    }

    if (helper.type === 'datalist') {
        var datalist = document.createElement('datalist');
        datalist.id = 'attributeHelper' + (++daloAttributeRequestCounter);
        options.forEach(function(option) { addOption(datalist, option.value, option.label); });
        helperElem.appendChild(datalist);
        valuesElem.setAttribute('list', datalist.id);
        valuesElem.setAttribute('placeholder', 'double click or start typing...');
    }
}

async function loadAttributeDetails(attributeName, valuesSel, opSel, tableSel, attrTooltip, attrType, attrHelper) {
    var valuesElem = document.getElementById(valuesSel);
    var opElem = document.getElementById(opSel);
    var tableElem = document.getElementById(tableSel);
    var tooltipElem = document.getElementById(attrTooltip);
    var typeElem = document.getElementById(attrType);
    var helperElem = document.getElementById(attrHelper);
    if (!valuesElem || !opElem || !tableElem || !tooltipElem || !typeElem || !helperElem) return;

    resetAttributeFields(valuesElem, opElem, tableElem, tooltipElem, typeElem, helperElem);
    var requestId = attributeRequestId(valuesElem);
    if (!attributeName) return;

    try {
        var data = await daloRequestJSON('library/ajax/attributes.php', { getValuesForAttribute: attributeName });
        if (!attributeRequestIsCurrent(valuesElem, requestId)) return;
        populateRecommendedSelect(opElem, data.operators || [], data.recommendedOperator || '');
        if (tableElem.type === 'select-one') {
            populateRecommendedSelect(tableElem, data.tables || [], data.recommendedTable || '');
        } else {
            tableElem.value = data.recommendedTable || (data.tables || [])[0] || '';
        }
        tooltipElem.textContent = 'Description: ' + (data.description || '(n/a)');
        typeElem.textContent = 'Type: ' + (data.type || '(n/a)');
        buildAttributeHelper(valuesElem, helperElem, data.helper);
    } catch (error) {
        if (!attributeRequestIsCurrent(valuesElem, requestId)) return;
        alert(error.message);
    }
}

function getValuesList(sel, valuesSel, opSel, tableSel, attrTooltip, attrType, attrHelper) {
    var selected = document.getElementById(sel);
    if (!selected) return;
    var attributeName = selected.value;
    return loadAttributeDetails(attributeName, valuesSel, opSel, tableSel, attrTooltip, attrType, attrHelper);
}

function parseAttribute(attrElement) {
    var attrId = attrElement === 1 ? 'dictAttributesDatabase' : 'dictAttributesCustom';
    var attribute = document.getElementById(attrId);
    if (attribute && attribute.value !== '') addElement(1, attrId);
}

function addElement(enableTable, elementId) {
    var source = document.getElementById(elementId);
    var divContainer = document.getElementById('divContainer');
    var divCounter = document.getElementById('divCounter');
    if (!source || !divContainer || !divCounter) return;

    dictCounter++;
    var num = parseInt(divCounter.value, 10) + 1;
    divCounter.value = num;
    var fieldName = 'dictValues' + dictCounter + '[]';
    var attributeName = source.value;
    var divIdName = 'attrib' + num + 'Div';
    var attributeFieldset = document.createElement('fieldset');
    attributeFieldset.id = divIdName;
    attributeFieldset.className = 'd-flex flex-column';

    var content = '<div class="d-flex flex-row justify-content-center align-items-center gap-2 my-1">'
        + '<div class="align-self-end">'
        + '<a class="mx-1" href="#top" data-bs-toggle="tooltip" data-bs-placement="top" data-bs-title="Top"><i class="bi bi-chevron-double-up"></i></a>'
        + '<a class="mx-1" href="#" data-bs-toggle="tooltip" data-bs-placement="top" data-bs-title="Remove" onclick="removeElement(\'' + divIdName + '\'); return false"><i class="bi bi-x-circle-fill text-danger"></i></a>'
        + '<a class="mx-1" href="#" data-bs-toggle="tooltip" data-bs-placement="top" data-bs-title="Info" onclick="document.getElementById(\'dictInfo' + num + '\').classList.toggle(\'d-none\'); return false"><i class="bi bi-info-circle-fill"></i></a>'
        + '</div>'
        + '<div><label for="dictAttributes' + num + '" class="form-label mb-1">Attribute</label>'
        + '<input type="text" id="dictAttributes' + num + '" name="' + fieldName + '" class="form-control"></div>'
        + '<div><label for="dictValues' + num + '" class="form-label mb-1">Value</label>'
        + '<input type="text" id="dictValues' + num + '" name="' + fieldName + '" class="form-control"></div>'
        + '<div><span id="dictHelper' + num + '"></span></div>'
        + '<div><label for="dictOP' + num + '" class="form-label mb-1"><abbr title="Operator">Op</abbr></label>'
        + '<select id="dictOP' + num + '" name="' + fieldName + '" class="form-select"></select></div>';

    if (enableTable === 1) {
        content += '<div><label for="dictTable' + num + '" class="form-label mb-1">Target</label>'
            + '<select id="dictTable' + num + '" name="' + fieldName + '" class="form-select"></select></div>';
    } else {
        content += '<input type="hidden" id="dictTable' + num + '" name="' + fieldName + '">';
    }

    content += '</div><div id="dictInfo' + num + '" class="d-flex flex-column justify-content-start d-none">'
        + '<div id="dictTooltip' + num + '">Description: (n/a)</div>'
        + '<div id="dictType' + num + '">Type: (n/a)</div></div>';

    attributeFieldset.innerHTML = content;
    divContainer.appendChild(attributeFieldset);
    document.getElementById('dictAttributes' + num).value = attributeName;
    loadAttributeDetails(attributeName, 'dictValues' + num, 'dictOP' + num, 'dictTable' + num,
                         'dictTooltip' + num, 'dictType' + num, 'dictHelper' + num);
}

function removeElement(divNum) {
    var attribute = document.getElementById(divNum);
    if (attribute) attribute.remove();
}
