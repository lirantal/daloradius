

function setText(srcObj,dstObj) {

var srcElem = document.getElementById(srcObj);
var elemVal = srcElem.options[srcElem.selectedIndex].value;

var dstElem = document.getElementById(dstObj);
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
  var props = 'scrollBars=yes,resizable=yes,toolbar=no,menubar=no,location=no,directories=no,width=500,height=200';
  newWindow = window.open("", "Client Receipt", props);
  newWindow.document.write("<html><title>Customer Receipt</title><body><br/>");
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
  newWindow.document.write(" </body></html>");
}

