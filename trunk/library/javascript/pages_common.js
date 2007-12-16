
function setText(srcObj,dstObj) {

var srcElem = document.getElementById(srcObj);
var elemVal = srcElem.options[srcElem.selectedIndex].value;

var dstElem = document.getElementById(dstObj);
var dstElemVal = dstElem.value;
var res = (dstElemVal * elemVal);
dstElem.value = res;

/*
	// some debugging information which could be useful:
dstElem.value = "srcObj: " + srcObj + " - srcElem: " + srcElem;
dstElem.value = "srcElemVal: " + elemVal + " - dstElemVal: " + dstElemVal;
*/


}



function setStringText(srcObj,dstObj) {

var srcElem = document.getElementById(srcObj);
var elemVal = srcElem.options[srcElem.selectedIndex].value;

var dstElem = document.getElementById(dstObj);
var dstElemVal = dstElem.value;
dstElem.value = elemVal;

}




function toggleShowDiv(pass) {

	var divs = document.getElementsByTagName('div');
	for(i=0;i<divs.length;i++) {
		if (divs[i].id.match(pass)) {
			if (document.getElementById) {							// compatible with IE5 and NS6
//				if (divs[i].style.visibility=="visible")
				if (divs[i].style.display=="inline")
//					divs[i].style.visibility="hidden";
					divs[i].style.display="none";
				else
//					divs[i].style.visibility="visible";
	 				divs[i].style.display="inline";
			} else if (document.layers) {							// compatible with Netscape 4
				if (document.layers[divs[i]].display=='visible')
					document.layers[divs[i]].display = 'hidden';
				else
					document.layers[divs[i]].display = 'visible';
			} else {
				if (document.all.hideShow.divs[i].visibility=='visible')		// compatible with IE4
					document.all.hideShow.divs[i].visibility = 'hidden';
				else
					document.all.hideShow.divs[i].visibility = 'visible';
			}
		}
	}
}


function small_window(user,pass,time) {
  var newWindow;
  var currentTime = new Date();
  var props = "scrollBars=yes,resizable=yes,toolbar=no,menubar=no,location=no,directories=no,width=500,height=200";
  newWindow = window.open("about:blank","blank",props);
  newWindow.document.write("Thank you. <br/>");
  newWindow.document.write("Your username is: ");
  newWindow.document.write(user);
  newWindow.document.write("<br/>");
  newWindow.document.write("Your password is: ");
  newWindow.document.write(pass);
  newWindow.document.write("<br/>");
  newWindow.document.write("Your timecredit is: ");
  newWindow.document.write(time);
  newWindow.document.write("<br/>");
  newWindow.document.write("<br/>");
  newWindow.document.write("Receipt produced on: ");
  newWindow.document.write(currentTime);
  newWindow.document.write("<br/>");
  newWindow.document.write("Enginx HotSpot System ");
  newWindow.document.write("<br/>");
}


function SetChecked(val,chkName,formname) {
        dml=document.forms[formname];
        len = dml.elements.length;
        var i=0;
        for( i=0 ; i<len ; i++) {
                if (dml.elements[i].name==chkName) {
                dml.elements[i].checked=val;
                }
        }
}


function removeUserCheckbox(formname) {

        var count = 0;
        var form = document.getElementsByTagName('input');
        for (var i=0; i < form.length; ++i) {
                var e = form[i];
                if (e.type == 'checkbox'
                && e.checked)
                ++count;
        }


        if (confirm("You are about to remove " + count + " records from database\nDo you want to continue?"))  {
		document.forms[formname].action="mng-del.php";
		document.forms[formname].submit();
                return true;
        }

        return false;
}


function removeHotspotCheckbox(formname) {

        var count = 0;
        var form = document.getElementsByTagName('input');
        for (var i=0; i < form.length; ++i) {
                var e = form[i];
                if (e.type == 'checkbox'
                && e.checked)
                ++count;
        }


        if (confirm("You are about to remove " + count + " records from database\nDo you want to continue?"))  {
		document.forms[formname].action="mng-hs-del.php";
		document.forms[formname].submit();
                return true;
        }

        return false;
}


function removeNASCheckbox(formname) {

        var count = 0;
        var form = document.getElementsByTagName('input');
        for (var i=0; i < form.length; ++i) {
                var e = form[i];
                if (e.type == 'checkbox'
                && e.checked)
                ++count;
        }


        if (confirm("You are about to remove " + count + " records from database\nDo you want to continue?"))  {
		document.forms[formname].action="mng-rad-nas-del.php";
		document.forms[formname].submit();
                return true;
        }

        return false;
}
