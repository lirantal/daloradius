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
    ajax[index].requestFile = 'include/management/dynamic_attributes.php?getVendorsList=yes';

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
        ajax[index].requestFile = `include/management/dynamic_attributes.php?vendorAttributes=${vendorName}`;

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


function getValuesList(sel,valuesSel,opSel,tableSel,attrTooltip,attrType,attrHelper) {

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
        ajax[index].requestFile = `include/management/dynamic_attributes.php?getValuesForAttribute=${attributeName}&instanceNum=${num}&dictValueId=${valuesSel}`;    
        
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
    
    var attributeCustom = document.getElementById('dictAttributesCustom');
	var attributeCustomVal = attributeCustom.value;

	if (attrElement == 1) {
        var attributeOfDatabase = document.getElementById('dictAttributesDatabase');
        var attributeOfDatabaseVal = attributeOfDatabase.options[attributeOfDatabase.selectedIndex].value;
		addElement(1, 'dictAttributesDatabase');
	} else {
		addElement(1, 'dictAttributesCustom');
	}

}

function addElement(enableTable, elementId) {

    // incrementing elements counter
    dictCounter++;			

    var divContainer = document.getElementById('divContainer');
    var divCounter = document.getElementById('divCounter');
    var num = document.getElementById('divCounter').value + 1;
    divCounter.value = num;

    var attributeDiv = document.createElement('div');
    var divIdName = `attrib${num}Div`;
    attributeDiv.setAttribute('id',divIdName);


    if (enableTable == 1) {
    tableElement = '&nbsp;&nbsp;<b>Target:</b>&nbsp;'
                 + `<select id="dictTable${num}" name="dictValues${dictCounter}[]" style="width: 70px" class="form">`
                 + '</select>';
    } else {
        tableElement = `<input type="hidden" id="dictTable${num}" name="dictValues${dictCounter}[]">`;
    }

    // get top-page attribute's value
    var srcElem = document.getElementById(elementId);

	if (elementId == 'dictAttributesDatabase') {
      var elemVal = srcElem.options[srcElem.selectedIndex].value;
	} else {
      var elemVal = srcElem.value;
	}

    var onclick_remove = `javascript:removeElement('${divIdName}');`;
    var onclick_info = `javascript:toggleShowDiv('dictInfo${num}');`;

    var content = "<br><fieldset>"
                + '<b>Attribute:</b><input type="text" id="dictAttributes1" '
                + `name="dictValues${dictCounter}[]" value="${elemVal}" ` + 'style="width: 220px" class="form">'
                + '<b>Value:</b><input type="text" ' + `id="dictValues${num}" name="dictValues${dictCounter}[]" `
                + 'style="width: 220px" class="form">'
                + `<span id="dictHelper${num}"></span>`
                + "<b>Op:</b>"
                + `<select id="dictOP${num}" name="dictValues${dictCounter}[]" style="width: 45px" class="form"></select>`
                + tableElement + "<br>"
                + `<div id="dictInfo${num}" style="display:none;visibility:visible">`
                + `<span id="dictTooltip${num}">`
                + "<b>Description:</b></span><br>"
                + `<span id="dictType${num}">`
                + "<b>Type:<b/></span></div><hr>"
                + '<a href="#top"><img src="static/images/icons/arrow_up.png" alt="^" /></a>'
                + `<input type="button" name="removeAttributes" value="Remove" onclick="${onclick_remove}" class="button">`
                + `<input type="button" name="infoAttribute" value="Info" onclick="${onclick_info}" class="button">`
                + "</fieldset>";


    attributeDiv.innerHTML = content;
    divContainer.appendChild(attributeDiv);

    getValuesList(elementId, 'dictValues'+num, 'dictOP'+num, 'dictTable'+num, 'dictTooltip'+num, 'dictType'+num, 'dictHelper'+num);

}


function removeElement(divNum) {
  var divContainer = document.getElementById('divContainer');
  var attributeDiv = document.getElementById(divNum);
  divContainer.removeChild(attributeDiv);
}
