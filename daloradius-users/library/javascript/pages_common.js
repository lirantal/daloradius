/***********************************************************************
 * changeInteger
 * this function implements a spinbox, it increments or decrement
 * the value in a text input (which needs to be an integer)
 *
 * dstObj		- the destination object text input
 * action		- increment or decrement
 ***********************************************************************/
function changeInteger(dstObj,action) {

	var dstElem = document.getElementById(dstObj);
	var dstElemVal = dstElem.value;
	if (action == 'increment') {
		dstElem.value = parseInt(dstElemVal)+1;
	} else {
		if (dstElemVal <= 0)
			exit;
		dstElem.value = parseInt(dstElemVal)-1;
	}

}

function toggleUserAuth() {

	//disable the mac auth
	document.newuser.macaddress.disabled=true;
	document.newuser.group_macaddress.disabled=true;

	// disable pincode auth
	document.newuser.pincode.disabled=true;
	document.newuser.group_pincode.disabled=true;

	//enable the user auth
	document.newuser.username.disabled=false;
	document.newuser.password.disabled=false;
	document.newuser.passwordType.disabled=false;
	document.newuser.group.disabled=false;
	document.newuser.usergroup.disabled=false;

}

function togglePinCode() {

	// disable pincode auth
	document.newuser.pincode.disabled=false;
	document.newuser.group_pincode.disabled=false;

	//disable the mac auth
	document.newuser.macaddress.disabled=true;
	document.newuser.group_macaddress.disabled=true;

	// disable the user auth
	document.newuser.username.disabled=true;
	document.newuser.password.disabled=true;
	document.newuser.passwordType.disabled=true;
	document.newuser.group.disabled=true;
	document.newuser.usergroup.disabled=true;

}

function toggleMacAuth(state) {

	// enable the mac auth
	document.newuser.macaddress.disabled=false;
	document.newuser.group_macaddress.disabled=false;

	// disable the user auth
	document.newuser.username.disabled=true;
	document.newuser.password.disabled=true;
	document.newuser.passwordType.disabled=true;
	document.newuser.group.disabled=true;
	document.newuser.usergroup.disabled=true;

	// disable pincode auth
	document.newuser.pincode.disabled=true;
	document.newuser.group_pincode.disabled=true;

}


/***********************************************************************
 * setText
 * srcObj	- an integer to be multiplied
 * dstObj	- the dstination object is multiplied by it's value 
 * 		  and the value of the source object.
 ***********************************************************************/
function setText(srcObj,dstObj) {

	var srcElem = document.getElementById(srcObj);
	var elemVal = srcElem.options[srcElem.selectedIndex].value;

	var dstElem = document.getElementById(dstObj);
	var dstElemVal = dstElem.value;
	var res = (dstElemVal * elemVal);
	dstElem.value = res;

}


/***********************************************************************
 * setStringText
 * srcId	- the source object text
 * dstId	- the dstination object is set to the source object text
 ***********************************************************************/
function setStringText(srcId,dstId) {

	var srcElem = document.getElementById(srcId);

	if (srcElem.type == "text")
		var elemVal = srcElem.value;

	if (srcElem.type == "select-one")
		var elemVal = srcElem.options[srcElem.selectedIndex].value;

	var dstElem = document.getElementById(dstId);
	dstElem.value = elemVal;

}


/***********************************************************************
 * setStringTextMulti
 * srcId	- the source object text
 * dstId1	- the dstination object is set to the source object text
 * dstId2	- 
 ***********************************************************************/
function setStringTextMulti(srcId,dstId1, dstId2) {

	var srcElem = document.getElementById(srcId);

	if (srcElem.type == "select-one")
		var elemVal = srcElem.options[srcElem.selectedIndex].value;

	var srcElemValArray = elemVal.split("||");

	var dstElem1 = document.getElementById(dstId1);
	dstElem1.value = srcElemValArray[0];

	var dstElem2 = document.getElementById(dstId2);
	dstElem2.value = srcElemValArray[1];

}



/***********************************************************************
 * toggleShowDiv
 * toggles a div on/off (visible/hidden)
 * 
 * idName		- the id name of the target div to toggle on/off
 * 			  (visible/hidden)
 ***********************************************************************/
function toggleShowDiv(idName) {

	var divs = document.getElementsByTagName('div');
	for(i=0;i<divs.length;i++) {
		if (divs[i].id.match(idName)) {
			if (document.getElementById) {							// compatible with IE5 and NS6
				if (divs[i].style.display=="inline")
					divs[i].style.display="none";
				else
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

/***********************************************************************
 * small_window
 * opens up a small window with quick accounts information
 *
 * user		- the username
 * pass		- the password
 * time		- the credit time that is left for the user
 ***********************************************************************/
function small_window(user,pass,time) {

	var newWindow;
	var currentTime = new Date();
	var props = "scrollBars=yes,resizable=yes,toolbar=no,menubar=no,location=no,directories=no,width=500,height=200";
	newWindow = window.open("about:blank","blank",props);

        newWindow.document.write("<html><body>");
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
        newWindow.document.write("</body></html>");
        newWindow.document.close();

}


/***********************************************************************
 * toggleShowDiv
 * user		- the username
 * pass		- the password
 * time		- the credit time that is left for the user
 ***********************************************************************/
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


/***********************************************************************
 * removeCheckbox
 * submits a form with checkbox values to a remote page
 * 
 * formName	- the form name
 * pageDst	- the page destination to be submitted 
 *
 ***********************************************************************/
function removeCheckbox(formName,pageDst) {

        var count = 0;
        var form = document.getElementsByTagName('input');

        for (var i=0; i < form.length; ++i) {
                var e = form[i];
                if (e.type == 'checkbox'
                && e.checked)
                ++count;
        }


	// if no items were checked there's no reason to submit the form
	if (count == 0) {
		alert("No items selected");
		return;
	}


        if (confirm("You are about to remove " + count + " records from database\nDo you want to continue?"))  {
		document.forms[formName].action=pageDst;
		document.forms[formName].submit();
                return true;
        }

        return false;
}
