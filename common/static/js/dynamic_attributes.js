// counter for the different attributes html elements
var dictCounter = 1;
var ajax = new Array();


// recieves the select box object of the vendors list
function getVendorsList(sel) {
    // empty attributes list
    document.getElementById(sel).options.length = 1;

    var index = ajax.length;
	ajax[index] = new sack();

    // Specifying which file to get
    ajax[index].requestFile = 'library/ajax/attributes.php?getVendorsList=yes';

    // Specify function that will be executed after file has been processed
    ajax[index].onCompletion = function(){ createVendors(index,sel) };

    // Execute AJAX function
    ajax[index].runAJAX();
}


function createVendors(index, sel) {
	var objVendors = document.getElementById(sel);

    // Executing the response from Ajax as Javascript code
    eval(ajax[index].response);
}


function getAttributesList(sel, attributesSel) {

    var vendorName = sel.options[sel.selectedIndex].value;

    // empty attributes list
    document.getElementById(attributesSel).options.length = 0;

    if (vendorName.length > 0) {
        var index = ajax.length;
        ajax[index] = new sack();

        // Specifying which file to get
        ajax[index].requestFile = `library/ajax/attributes.php?vendorAttributes=${vendorName}`;

        // Specify function that will be executed after file has bee$
        ajax[index].onCompletion = function(){ createAttributes(index,attributesSel) };

        // Execute AJAX function
        ajax[index].runAJAX();
    }
}


function createAttributes(index, attributesSel) {
    var objAttributes = document.getElementById(attributesSel);

    // Executing the response from Ajax as Javascript code
    eval(ajax[index].response);
}


function getValuesList(sel, valuesSel, opSel, tableSel, attrTooltip, attrType, attrHelper) {

    var attributeName = document.getElementById(sel).value;

    // clear input
    document.getElementById(valuesSel).value = '';		 
    
    // clear select box
    document.getElementById(opSel).options.length = 0;       

    if (document.getElementById(tableSel).type == "select") {
        // clear select box
        document.getElementById(tableSel).options.length = 0;    
    }

    // clear input
    document.getElementById(attrType).value = '';       	 
    
    // clear input
    document.getElementById(attrTooltip).value = '';         

    // clear input
    document.getElementById(attrHelper).value = '';

    num = dictCounter - 1;

    if(attributeName.length > 0) {
        var index = ajax.length;
        ajax[index] = new sack();
        
        // Specifying which file to get
        ajax[index].requestFile = `library/ajax/attributes.php?getValuesForAttribute=${attributeName}&instanceNum=${num}&dictValueId=${valuesSel}`;    
        
        // Specify function that will be executed after file has been found
        ajax[index].onCompletion = function(){ createValues(index,valuesSel,opSel,tableSel,attrTooltip,attrType,attrHelper) };   
        
        // Execute AJAX function
        ajax[index].runAJAX();          
    }
}


function createValues(index, valuesSel, opSel, tableSel, attrTooltip, attrType, attrHelper) {
	var objHelper = document.getElementById(attrHelper);
	var objTooltip = document.getElementById(attrTooltip);
	var objType = document.getElementById(attrType);
    var objValues = document.getElementById(valuesSel);
    var objOP = document.getElementById(opSel);
    var objTable = document.getElementById(tableSel);
    
    // Executing the response from Ajax as Javascript code
    eval(ajax[index].response);     
}


function parseAttribute(attrElement) {
  
    if (attrElement == 1) {
        var attrId = 'dictAttributesDatabase';
        
        var attributeOfDatabase = document.getElementById(attrId);
        var attributeOfDatabaseVal = attributeOfDatabase.options[attributeOfDatabase.selectedIndex].value;
        
        var shouldAdd = attributeOfDatabaseVal != '';
		
	} else {
        var attrId = 'dictAttributesCustom';
        
        var attributeCustom = document.getElementById(attrId);
        var attributeCustomVal = attributeCustom.value;
		
        var shouldAdd = attributeCustomVal != '';
        
	}
    
    if (shouldAdd) {
        addElement(1, attrId);
    }
}

function addElement(enableTable, elementId) {

    // incrementing elements counter
    dictCounter++;			

    var divContainer = document.getElementById('divContainer');
    var divCounter = document.getElementById('divCounter');
    var num = parseInt(divCounter.value) + 1;
    divCounter.value = num;

    var attributeDiv = document.createElement('div');
    var divIdName = `attrib${num}Div`;
    attributeDiv.setAttribute('id',divIdName);


    // get top-page attribute's value
    var srcElem = document.getElementById(elementId);

	if (elementId == 'dictAttributesDatabase') {
      var elemVal = srcElem.options[srcElem.selectedIndex].value;
	} else {
      var elemVal = srcElem.value;
	}

    
    var onclick_remove = `removeElement('${divIdName}')`;
    var onclick_info = `document.getElementById('dictInfo${num}').classList.toggle('d-none')`;

    var content = `<fieldset id="${divIdName}" class="d-flex flex-column">`;
        
        content += '<div class="d-flex flex-row justify-content-center align-items-center gap-2 my-1">';
        
        content += '<div class="align-self-end">'
                + '<a class="mx-1" href="#top" data-bs-toggle="tooltip" data-bs-placement="top" data-bs-title="Top">'
                + '<i class="bi bi-chevron-double-up"></i></a>'
                + '<a class="mx-1" href="#" data-bs-toggle="tooltip" data-bs-placement="top" data-bs-title="Remove"'
                + ` onclick="${onclick_remove}"><i class="bi bi-x-circle-fill text-danger"></i></a>`
                + '<a class="mx-1" href="#" data-bs-toggle="tooltip" data-bs-placement="top" data-bs-title="Info"'
                + ` onclick="${onclick_info}"><i class="bi bi-info-circle-fill"></i></a>`
                + '</div>';
        
        content += '<div>'
                +  `<label for="dictAttributes${num}" class="form-label mb-1">Attribute</label>`
                +  `<input type="text" id="dictAttributes${num}" name="dictValues${dictCounter}[]" value="${elemVal}" `
                +  ' class="form-control">'
                +  '</div>';
        
        content += '<div>'
                +  `<label for="dictValues${num}" class="form-label mb-1">Value</label>`
                +  `<input type="text" id="dictValues${num}" name="dictValues${dictCounter}[]" `
                +  ' class="form-control">'
                +  '</div>';
        
        content += `<div><span id="dictHelper${num}"></span></div>`;
        
        content += '<div>'
                +  `<label for="dictOP${num}" class="form-label mb-1"><abbr title="Operator">Op</abbr></label>`
                + `<select id="dictOP${num}" name="dictValues${dictCounter}[]" class="form-select"></select>`
                + '</div>';
        
        if (enableTable == 1) {
            content += '<div>'
                    +  `<label for="dictTable${num}" class="form-label mb-1">Target</label>`
                    +  `<select id="dictTable${num}" name="dictValues${dictCounter}[]" class="form-select"></select>`
                    +  '</div>';
        } else {
            content += `<input type="hidden" id="dictTable${num}" name="dictValues${dictCounter}[]">`;
        }
        
        content += '</div>';
                
        content += `<div id="dictInfo${num}" class="d-flex flex-column justify-content-start d-none">`
                + `<div id="dictTooltip${num}">`
                + '<strong>Description:</strong> (n/a)</div>'
                + `<div id="dictType${num}">`
                + '<strong>Type:</strong> (n/a)</div>'
                + '</div>';
        
        content += '</fieldset>';
        
    attributeDiv.innerHTML = content;
    divContainer.appendChild(attributeDiv);

    getValuesList(elementId, 'dictValues'+num, 'dictOP'+num, 'dictTable'+num, 'dictTooltip'+num, 'dictType'+num, 'dictHelper'+num);

}


function removeElement(divNum) {
  var divContainer = document.getElementById('divContainer');
  var attributeDiv = document.getElementById(divNum);
  divContainer.removeChild(attributeDiv);
}
