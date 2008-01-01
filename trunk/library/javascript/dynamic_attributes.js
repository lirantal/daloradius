var dictCounter = 1;		// counter for the different attributes html elements
var ajax = new Array();

// recieves the select box object of the vendors list
function getVendorsList(sel) {

        document.getElementById(sel).options.length = 1;     // empty attributes list

        var index = ajax.length;
	ajax[index] = new sack();

        ajax[index].requestFile = 'include/management/dynamic_attributes.php?getVendorsList=yes';  // Specifying which file to get
        ajax[index].onCompletion = function(){ createVendors(index,sel) };        // Specify function that will be executed after file has been processed
        ajax[index].runAJAX();          // Execute AJAX function

}

function createVendors(index,sel) {
	var objVendors = document.getElementById(sel);
        eval(ajax[index].response);     // Executing the response from Ajax as Javascript code
}


function getAttributesList(sel,attributesSel) {

        var vendorName = sel.options[sel.selectedIndex].value;

        document.getElementById(attributesSel).options.length = 0;     // empty attributes list
        if(vendorName.length>0) {

              var index = ajax.length;
                ajax[index] = new sack();

                ajax[index].requestFile = 'include/management/dynamic_attributes.php?vendorAttributes='+vendorName;  // Specifying which file to get
                ajax[index].onCompletion = function(){ createAttributes(index,attributesSel) };        // Specify function that will be executed after file has bee$
                ajax[index].runAJAX();          // Execute AJAX function
        }
}

function createAttributes(index,attributesSel) {
        var objAttributes = document.getElementById(attributesSel);
        eval(ajax[index].response);     // Executing the response from Ajax as Javascript code
}

function getValuesList(sel,valuesSel,opSel,tableSel,attrTooltip,attrType) {
        var attributeName = sel.options[sel.selectedIndex].value;
        document.getElementById(valuesSel).value = '';		 // clear input
        document.getElementById(opSel).options.length = 0;       // clear select box
	if (document.getElementById(tableSel).type == "select")
	        document.getElementById(tableSel).options.length = 0;    // clear select box

        document.getElementById(attrType).value = '';       	 // clear input
        document.getElementById(attrTooltip).value = '';         // clear input
        if(attributeName.length>0) {
                var index = ajax.length;
                ajax[index] = new sack();
                ajax[index].requestFile = 'include/management/dynamic_attributes.php?getValuesForAttribute='+attributeName;    // Specifying which file to get
                ajax[index].onCompletion = function(){ createValues(index,valuesSel,opSel,tableSel,attrTooltip,attrType) };   // Specify function that will be executed after file has been found
                ajax[index].runAJAX();          // Execute AJAX function
        }
}

function createValues(index,valuesSel,opSel,tableSel,attrTooltip,attrType) {

	var objTooltip = document.getElementById(attrTooltip);
	var objType = document.getElementById(attrType);
        var objValues = document.getElementById(valuesSel);
        var objOP = document.getElementById(opSel);
        var objTable = document.getElementById(tableSel);
        eval(ajax[index].response);     // Executing the response from Ajax as Javascript code
}



function addElement(enableTable) {
  dictCounter++;			// incrementing elements counter

  var divContainer = document.getElementById('divContainer');
  var divCounter = document.getElementById('divCounter');
  var num = (document.getElementById('divCounter').value -1)+ 2;
  divCounter.value = num;

  var attributeDiv = document.createElement('div');
  var divIdName = 'attrib'+num+'Div';
  attributeDiv.setAttribute('id',divIdName);

  if (enableTable == 1) {
	tableElement = "&nbsp;&nbsp;Table: "+
	 	"<select id='dictTable"+num+"' name='dictValues"+dictCounter+"[]' style='width: 90px'>"+
		"</select>";
  } else {
	tableElement = "<input type='hidden' id='dictTable"+num+"' name='dictValues"+dictCounter+"[]' >";
  };

  var content = "" +
	"	<table border='2' class='table1'>"+
	"		<thead>"+
	"                                               <tr>"+
	"                                                        <th colspan='10'> <?php echo $l['table']['Attributes']; ?> </th>"+
	"                                               </tr>"+
	"               </thead>"+
	"        <tr>"+
	"                <td>"+
	"		 Vendor:"+
	"                <select id='dictVendors"+num+"' onchange=\"getAttributesList(this,'dictAttributes"+num+"')\""+
	"                        style='width: 215px' onclick=\"getVendorsList('dictVendors"+num+"')\" >"+
	"                        <option value=''>Select Vendor...</option>"+
	"                </select>"+
	"                &nbsp;&nbsp;Attribute:"+
	"		 <select id='dictAttributes"+num+"' name='dictValues"+dictCounter+"[]' onchange=\"getValuesList(this,'dictValues"+num+"','dictOP"+num+"','dictTable"+num+"','dictTooltip"+num+"','dictType"+num+"')\" style='width: 270px'>"+
	"                </select>"+
	"                </td>"+
	"        </tr>"+
	"        <tr>"+
	"           <td>"+
	"		 &nbsp;&nbsp;Value:"+
	"                <input type='text' id='dictValues"+num+"' name='dictValues"+dictCounter+"[]' style='width: 115px'>"+
	"                "+
	"                &nbsp;&nbsp;OP:"+
	"                <select id='dictOP"+num+"' name='dictValues"+dictCounter+"[]' style='width: 45px'>"+
	"                </select>"+
	"                "+
	tableElement+
	"                &nbsp;&nbsp;Function:"+
	"                <select id='dictFunc' name='dictFunc'>"+
	"                </select>"+
        "           </td>"+
	"	</tr>"+
	"	<tr>"+
	"           <td>"+
	"                <div id='dictInfo"+num+"' style='display:none;visibility:visible'>"+
        "        <span id='dictTooltip"+num+"'>"+
	"        <b>Attribute Tooltip:</b>"+
        "        </span>"+
        "        <br/>"+
        "       <span id='dictType"+num+"'>"+
	"        <b>Type:</b>"+
        "       </span>"+
	"	</div>"+
        "       </td>"+
	"        </tr>"+
	"<a href=\"javascript:;\" onclick=\"addElement("+enableTable+");\">Add</a>"+
	"&nbsp;"+
	"<a href=\"javascript:;\" onclick=\"removeElement(\'"+divIdName+"\')\">Remove</a>"+
	"&nbsp;"+
	"<a href=\"javascript:;\" onclick=\"toggleShowDiv(\'dictInfo"+num+"\')\">Help</a>"+
	"</table><br/>";

  attributeDiv.innerHTML = content;
  divContainer.appendChild(attributeDiv);
}


function removeElement(divNum) {
  var divContainer = document.getElementById('divContainer');
  var attributeDiv = document.getElementById(divNum);
  divContainer.removeChild(attributeDiv);
}
