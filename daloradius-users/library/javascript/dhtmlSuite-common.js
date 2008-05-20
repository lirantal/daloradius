if(!window.DHTMLSuite)var DHTMLSuite=new Object();/************************************************************************************************************
	@fileoverview
	DHTML Suite for Applications.
	Copyright (C)2006  Alf Magne Kalleland(post@dhtmlgoodies.com)<br>
	<br>
	This library is free software; you can redistribute it and/or<br>
	modify it under the terms of the GNU Lesser General Public<br>
	License as published by the Free Software Foundation; either<br>
	version 2.1 of the License, or (at your option)any later version.<br>
	<br>
	This library is distributed in the hope that it will be useful,<br>
	but WITHOUT ANY WARRANTY; without even the implied warranty of<br>
	MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the GNU<br>
	Lesser General Public License for more details.<br>
	<br>
	You should have received a copy of the GNU Lesser General Public<br>
	License along with this library; if not, write to the Free Software<br>
	Foundation, Inc., 51 Franklin Street, Fifth Floor, Boston, MA  02110-1301  USA<br>
	<br>
	<br>
	www.dhtmlgoodies.com<br> 
	Alf Magne Kalleland<br>

************************************************************************************************************/

/**
*
*@package DHTMLSuite for applications
*@copyright Copyright &copy; 2006, www.dhtmlgoodies.com
*@author Alf Magne Kalleland <post@dhtmlgoodies.com>
 */

/****** 
Some prototypes:
**/

	
// Creating a trim method
if(!String.trim)String.prototype.trim=function(){ return this.replace(/^\s+|\s+$/, ''); };
var DHTMLSuite_funcs=new Object();
if(!window.DHTML_SUITE_THEME)var DHTML_SUITE_THEME='blue';
if(!window.DHTML_SUITE_THEME_FOLDER)var DHTML_SUITE_THEME_FOLDER='../themes/';
if(!window.DHTML_SUITE_JS_FOLDER)var DHTML_SUITE_JS_FOLDER='../js/separateFiles/';

/************************************************************************************************************
*
* Global variables
*
************************************************************************************************************/

	
// {{{ DHTMLSuite.createStandardObjects()
/**
*Create objects used by all scripts
 *
*@public
 */

var DHTMLSuite=new Object();

var standardObjectsCreated=false;	
// The classes below will check this variable, if it is false, default help objects will be created
DHTMLSuite.eventEls=new Array();	
// Array of elements that has been assigned to an event handler.

var widgetDep=new Object();
	
// Widget dependencies
widgetDep['formValidator']=['dhtmlSuite-formUtil.js'];	
// Form validator widget
widgetDep['paneSplitter']=['dhtmlSuite-paneSplitter.js','dhtmlSuite-paneSplitterModel.js','dhtmlSuite-dynamicContent.js','ajax.js'];
widgetDep['menuBar']=['dhtmlSuite-menuBar.js','dhtmlSuite-menuItem.js','dhtmlSuite-menuModel.js'];
widgetDep['windowWidget']=['dhtmlSuite-windowWidget.js','dhtmlSuite-resize.js','dhtmlSuite-dragDropSimple.js','ajax.js','dhtmlSuite-dynamicContent.js'];
widgetDep['colorWidget']=['dhtmlSuite-colorWidgets.js','dhtmlSuite-colorUtil.js'];
widgetDep['colorSlider']=['dhtmlSuite-colorWidgets.js','dhtmlSuite-colorUtil.js','dhtmlSuite-slider.js'];
widgetDep['colorPalette']=['dhtmlSuite-colorWidgets.js','dhtmlSuite-colorUtil.js'];
widgetDep['calendar']=['dhtmlSuite-calendar.js','dhtmlSuite-dragDropSimple.js'];
widgetDep['dragDropTree']=['dhtmlSuite-dragDropTree.js'];
widgetDep['slider']=['dhtmlSuite-slider.js'];
widgetDep['dragDrop']=['dhtmlSuite-dragDrop.js'];
widgetDep['imageEnlarger']=['dhtmlSuite-imageEnlarger.js','dhtmlSuite-dragDropSimple.js'];
widgetDep['imageSelection']=['dhtmlSuite-imageSelection.js'];
widgetDep['floatingGallery']=['dhtmlSuite-floatingGallery.js','dhtmlSuite-mediaModel.js'];
widgetDep['contextMenu']=['dhtmlSuite-contextMenu.js','dhtmlSuite-menuBar.js','dhtmlSuite-menuItem.js','dhtmlSuite-menuModel.js'];
widgetDep['dynamicContent']=['dhtmlSuite-dynamicContent.js','ajax.js'];
widgetDep['textEdit']=['dhtmlSuite-textEdit.js','dhtmlSuite-textEditModel.js','dhtmlSuite-listModel.js'];
widgetDep['listModel']=['dhtmlSuite-listModel.js'];
widgetDep['resize']=['dhtmlSuite-resize.js'];
widgetDep['dragDropSimple']=['dhtmlSuite-dragDropSimple.js'];
widgetDep['dynamicTooltip']=['dhtmlSuite-dynamicTooltip.js','dhtmlSuite-dynamicContent.js','ajax.js'];
widgetDep['modalMessage']=['dhtmlSuite-modalMessage.js','dhtmlSuite-dynamicContent.js','ajax.js'];
widgetDep['tableWidget']=['dhtmlSuite-tableWidget.js','ajax.js'];
widgetDep['progressBar']=['dhtmlSuite-progressBar.js'];
widgetDep['tabView']=['dhtmlSuite-tabView.js','dhtmlSuite-dynamicContent.js','ajax.js'];
widgetDep['infoPanel']=['dhtmlSuite-infoPanel.js','dhtmlSuite-dynamicContent.js','ajax.js'];
widgetDep['form']=['dhtmlSuite-formUtil.js','dhtmlSuite-dynamicContent.js','ajax.js'];
widgetDep['autoComplete']=['dhtmlSuite-autoComplete.js','ajax.js'];
widgetDep['chainedSelect']=['dhtmlSuite-chainedSelect.js','ajax.js'];

var depCache=new Object();

DHTMLSuite.include=function(widget){
	if(!widgetDep[widget]){
	alert('Cannot find the files for widget '+widget+'. Please verify that the name is correct');
	return;
	}
	var files=widgetDep[widget];
	for(var no=0;no<files.length;no++){
	if(!depCache[files[no]]){
		document.write('<'+'script');
		document.write(' language="javascript"');
		document.write(' type="text/javascript"');
		document.write(' src="'+DHTML_SUITE_JS_FOLDER+files[no]+'">');
		document.write('</'+'script'+'>');
		depCache[files[no]]=true;
	}
	}
}

DHTMLSuite.discardElement=function(element){ 
	element=DHTMLSuite.commonObj.getEl(element);
	var gBin=document.getElementById('IELeakGBin'); 
	if (!gBin){ 
	gBin=document.createElement('DIV'); 
	gBin.id='IELeakGBin'; 
	gBin.style.display='none'; 
	document.body.appendChild(gBin); 
	} 
	
// move the element to the garbage bin 
	gBin.appendChild(element); 
	gBin.innerHTML=''; 
} 

DHTMLSuite.createStandardObjects=function(){
	DHTMLSuite.clientInfoObj=new DHTMLSuite.clientInfo();	
// Create browser info object
	DHTMLSuite.clientInfoObj.init();
	if(!DHTMLSuite.configObj){	
// If this object isn't allready created, create it.
	DHTMLSuite.configObj=new DHTMLSuite.config();	
// Create configuration object.
	DHTMLSuite.configObj.init();
	}
	DHTMLSuite.commonObj=new DHTMLSuite.common();	
// Create configuration object.
	DHTMLSuite.variableStorage=new DHTMLSuite.globalVariableStorage();;	
// Create configuration object.
	DHTMLSuite.commonObj.init();
	DHTMLSuite.domQueryObj=new DHTMLSuite.domQuery();

	DHTMLSuite.commonObj.addEvent(window,'unload',function(){ DHTMLSuite.commonObj.__clearMemoryGarbage(); });

	standardObjectsCreated=true;
}

/************************************************************************************************************
*	Configuration class used by most of the scripts
*
*	Created:		August, 19th, 2006
* 	Update log:
*
************************************************************************************************************/

/**
* @constructor
* @class Store global variables/configurations used by the classes below. Example: If you want to  
*	 change the path to the images used by the scripts, change it here. An object of this
*	 class will always be available to the other classes. The name of this object is 
*	"DHTMLSuite.configObj".	<br><br>
*
*	If you want to create an object of this class manually, remember to name it "DHTMLSuite.configObj"
*	This object should then be created before any other objects. This is nescessary if you want
*	the other objects to use the values you have put into the object. <br>
* @version		1.0
* @version 1.0
* @author	Alf Magne Kalleland(www.dhtmlgoodies.com)
**/
DHTMLSuite.config=function(){
	var imagePath;	
// Path to images used by the classes. 
	var cssPath;	
// Path to CSS files used by the DHTML suite.

	var defaultCssPath;
	var defaultImagePath;
}

DHTMLSuite.config.prototype={
	
// {{{ init()
	/**
	*	Initializes the config object-the config class is used to store global properties used by almost all widgets
	 *
	*@public
	 */
	init:function(){
	this.imagePath=DHTML_SUITE_THEME_FOLDER+DHTML_SUITE_THEME+'/images/';	
// Path to images
	this.cssPath=DHTML_SUITE_THEME_FOLDER+DHTML_SUITE_THEME+'/css/';	
// Path to images

	this.defaultCssPath=this.cssPath;
	this.defaultImagePath=this.imagePath;

	}
	
// }}}
	,
	
// {{{ setCssPath()
	/**
	*This method will save a new CSS path, i.e. where the css files of the dhtml suite are located(the folder).
	 *	This method is rarely used. Default value is the variable DHTML_SUITE_THEME_FOLDER+DHTML_SUITE_THEME+'/css',
	 *	which means that the css path is set dynamically based on which theme you choose.
	 *
	*@param string newCssPath=New path to css files(folder-remember to have a slash(/)at the end)
	*@public
	 */

	setCssPath:function(newCssPath){
	this.cssPath=newCssPath;
	}
	
// }}}
	,
	
// {{{ resetCssPath()
	/**
	*@deprecated
	*Resets css path back to default value which is ../css_dhtmlsuite/
	*This method is deprecated.
	 *
	*@public
	 */
	resetCssPath:function(){
	this.cssPath=this.defaultCssPath;
	}
	
// }}}
	,
	
// {{{ resetImagePath()
	/**
	*@deprecated
	 *
	*Resets css path back to default path which is DHTML_SUITE_THEME_FOLDER+DHTML_SUITE_THEME+'/css'
	*This method is deprecated. 
	*@public
	 */
	resetImagePath:function(){
	this.imagePath=this.defaultImagePath;
	}
	
// }}}
	,
	
// {{{ setImagePath()
	/**
	*This method will save a new image file path, i.e. where the image files used by the dhtml suite ar located
	 *
	*@param string newImagePath=New path to image files (remember to have a slash(/)at the end)
	*@public
	 */
	setImagePath:function(newImagePath){
	this.imagePath=newImagePath;
	}
	
// }}}
}

DHTMLSuite.globalVariableStorage=function(){
	var menuBar_highlightedItems;	
// Array of highlighted menu bar items
	this.menuBar_highlightedItems=new Array();

	var arrayDSObjects;	
// Array of objects of class menuItem.
	var arrayOfDhtmlSuiteObjects;
	this.arrayDSObjects=new Array();
	this.arrayOfDhtmlSuiteObjects=this.arrayDSObjects;
	var ajaxObjects;
	this.ajaxObjects=new Array();
}

DHTMLSuite.globalVariableStorage.prototype={

}

/************************************************************************************************************
*	A class with general methods used by most of the scripts
*
*	Created:		August, 19th, 2006
*	Purpose of class:	A class containing common method used by one or more of the gui classes below, 
* 			example: loadCSS. 
*			An object("DHTMLSuite.commonObj")of this  class will always be available to the other classes. 
* 	Update log:
*
************************************************************************************************************/

/**
* @constructor
* @class A class containing common method used by one or more of the gui classes below, example: loadCSS. An object("DHTMLSuite.commonObj")of this  class will always be available to the other classes. 
* @version 1.0
* @author	Alf Magne Kalleland(www.dhtmlgoodies.com)
**/

DHTMLSuite.common=function(){
	var loadedCSSFiles;	
// Array of loaded CSS files. Prevent same CSS file from being loaded twice.
	var cssCacheStatus;	
// Css cache status
	var eventEls;
	var isOkToSelect;	
// Boolean variable indicating if it's ok to make text selections

	this.okToSelect=true;
	this.cssCacheStatus=true;	
// Caching of css files=on(Default)
	this.eventEls=new Array();
}

DHTMLSuite.common.prototype={

	
// {{{ init()
	/**
	*This method initializes the DHTMLSuite_common object.
	 *	This class contains a lot of useful methods used by most widgets.
	 *
	*@public
	 */
	init:function(){
	this.loadedCSSFiles=new Array();
	}
	
// }}}
	,
	
// {{{ loadCSS()
	/**
	*This method loads a CSS file(Cascading Style Sheet)dynamically-i.e. an alternative to <link> tag in the document.
	 *
	*@param string cssFile=Name of css file. It will be loaded from the path specified in the DHTMLSuite.common object
	*@param Boolean prefixConfigPath=Use config path as prefix.
	*@public
	 */
	loadCSS:function(cssFile,prefixConfigPath){
	if(!prefixConfigPath&&prefixConfigPath!==false)prefixConfigPath=true;
	if(!this.loadedCSSFiles[cssFile]){
		this.loadedCSSFiles[cssFile]=true;
		var lt=document.createElement('LINK');
		if(!this.cssCacheStatus){
		if(cssFile.indexOf('?')>=0)cssFile=cssFile+'&'; else cssFile=cssFile+'?';
		cssFile=cssFile+'rand='+ Math.random();	
// To prevent caching
		}
		if(prefixConfigPath){
		lt.href=DHTMLSuite.configObj.cssPath+cssFile;
		}else{
		lt.href=cssFile;
		}
		lt.rel='stylesheet';
		lt.media='screen';
		lt.type='text/css';
		document.getElementsByTagName('HEAD')[0].appendChild(lt);
	}
	}
	
// }}}
	,
	
// {{{ __setTextSelOk()
	/**
	*Is it ok to make text selections ?
	 *
	*@param Boolean okToSelect 
	*@private
	 */
	__setTextSelOk:function(okToSelect){
	this.okToSelect=okToSelect;
	}
	
// }}}
	,
	
// {{{ __setTextSelOk()
	/**
	*Returns true if it's ok to make text selections, false otherwise.
	 *
	*@return Boolean okToSelect 
	*@private
	 */
	__isTextSelOk:function(){
	return this.okToSelect;
	}
	
// }}}
	,
	
// {{{ setCssCacheStatus()
	/**
	*Specify if css files should be cached or not. 
	 *
	 *	@param Boolean cssCacheStatus=true=cache on, false=cache off
	 *
	*@public
	 */
	setCssCacheStatus:function(cssCacheStatus){
	  this.cssCacheStatus=cssCacheStatus;
	}
	
// }}}
	,
	
// {{{ getEl()
	/**
	*Return a reference to an object
	 *
	*@param Object elRef=Id, name or direct reference to element
	*@return Object HTMLElement-direct reference to element
	*@public
	 */
	getEl:function(elRef){
	if(typeof elRef=='string'){
		if(document.getElementById(elRef))return document.getElementById(elRef);
		if(document.forms[elRef])return document.forms[elRef];
		if(document[elRef])return document[elRef];
		if(window[elRef])return window[elRef];
	}
	return elRef;	
// Return original ref.

	}
	
// }}}
	,
	
// {{{ isArray()
	/**
	*Return true if element is an array
	 *
	*@param Object el=Reference to HTML element
	*@public
	 */
	isArray:function(el){
	if(el.constructor.toString().indexOf("Array")!=-1)return true;
	return false;
	}
	
// }}}
	,
	
// {{{ getStyle()
	/**
	*Return specific style attribute for an element
	 *
	*@param Object el=Reference to HTML element
	*@param String property=Css property
	*@public
	 */
	getStyle:function(el,property){
	el=this.getEl(el);
	if (document.defaultView&&document.defaultView.getComputedStyle){
		var retVal=null;
		var comp=document.defaultView.getComputedStyle(el, '');
		if (comp){
		retVal=comp[property];
		}
		return el.style[property]||retVal;
	}
	if (document.documentElement.currentStyle&&DHTMLSuite.clientInfoObj.isMSIE){
		var retVal=null;
		if(el.currentStyle)value=el.currentStyle[property];
		return (el.style[property]||retVal);
	}
	return el.style[property];
	}
	
// }}}
	,
	
// {{{ getLeftPos()
	/**
	*This method will return the left coordinate(pixel)of an HTML element
	 *
	*@param Object el=Reference to HTML element
	*@public
	 */
	getLeftPos:function(el){	 
	/*
	if(el.getBoundingClientRect){ 
// IE
		var box=el.getBoundingClientRect();
		return (box.left/1+Math.max(document.body.scrollLeft,document.documentElement.scrollLeft));
	}
	*/
	if(document.getBoxObjectFor){
		if(el.tagName!='INPUT'&&el.tagName!='SELECT'&&el.tagName!='TEXTAREA')return document.getBoxObjectFor(el).x
	}	 
	var returnValue=el.offsetLeft;
	while((el=el.offsetParent)!=null){
		if(el.tagName!='HTML'){
		returnValue += el.offsetLeft;
		if(document.all)returnValue+=el.clientLeft;
		}
	}
	return returnValue;
	}
	
// }}}
	,
	
// {{{ getTopPos()
	/**
	*This method will return the top coordinate(pixel)of an HTML element/tag
	 *
	*@param Object el=Reference to HTML element
	*@public
	 */
	getTopPos:function(el){
	/*
	if(el.getBoundingClientRect){	
// IE
		var box=el.getBoundingClientRect();
		return (box.top/1+Math.max(document.body.scrollTop,document.documentElement.scrollTop));
	}
	*/
	if(document.getBoxObjectFor){
		if(el.tagName!='INPUT'&&el.tagName!='SELECT'&&el.tagName!='TEXTAREA')return document.getBoxObjectFor(el).y
	}

	var returnValue=el.offsetTop;
	while((el=el.offsetParent)!=null){
		if(el.tagName!='HTML'){
		returnValue += (el.offsetTop-el.scrollTop);
		if(document.all)returnValue+=el.clientTop;
		}
	} 
	return returnValue;
	}
	
// }}}
	,
	
// {{{ getCookie()
	/**
	 *
	*	These cookie functions are downloaded from 
	*	http:
//www.mach5.com/support/analyzer/manual/html/General/CookiesJavaScript.htm
	 *
	* This function returns the value of a cookie
	 *
	*@param String name=Name of cookie
	*@param Object inputObj=Reference to HTML element
	*@public
	 */
	getCookie:function(name){ 
	var start=document.cookie.indexOf(name+"="); 
	var len=start+name.length+1; 
	if ((!start)&&(name!=document.cookie.substring(0,name.length)))return null; 
	if (start==-1)return null; 
	var end=document.cookie.indexOf(";",len); 
	if (end==-1)end=document.cookie.length; 
	return unescape(document.cookie.substring(len,end)); 
	} 
	
// }}}
	,
	
// {{{ setCookie()
	/**
	 *
	*	These cookie functions are downloaded from 
	*	http:
//www.mach5.com/support/analyzer/manual/html/General/CookiesJavaScript.htm
	 *
	* This function creates a cookie. (This method has been slighhtly modified)
	 *
	*@param String name=Name of cookie
	*@param String value=Value of cookie
	*@param Int expires=Timestamp-days
	*@param String path=Path for cookie (Usually left empty)
	*@param String domain=Cookie domain
	*@param Boolean secure=Secure cookie(SSL)
	*
	*@public
	 */
	setCookie:function(name,value,expires,path,domain,secure){ 
	expires=expires*60*60*24*1000;
	var today=new Date();
	var expires_date=new Date( today.getTime()+(expires));
	var cookieString=name+"=" +escape(value)+
		((expires)?";expires="+expires_date.toGMTString():"")+
		((path)?";path="+path:"")+
		((domain)?";domain="+domain:"")+
		((secure)?";secure":""); 
	document.cookie=cookieString; 
	}
	
// }}}
	,
	
// {{{ deleteCookie()
	/**
	 *
	* This function deletes a cookie. (This method has been slighhtly modified)
	 *
	*@param String name=Name of cookie
	*@param String path=Path for cookie (Usually left empty)
	*@param String domain=Cookie domain
	*
	*@public
	 */
	deleteCookie:function( name, path, domain )
	{
	if ( this.getCookie( name ))document.cookie=name+"=" +
	(( path )?";path="+path:"")+
	(( domain )?";domain="+domain:"" )+
	";expires=Thu, 01-Jan-1970 00:00:01 GMT";
	}
	
// }}}
	,
	
// {{{ cancelEvent()
	/**
	 *
	* This function only returns false. It is used to cancel selections and drag
	 *
	*
	*@public
	 */

	cancelEvent:function(){
	return false;
	}
	
// }}}
	,
	
// {{{ addEvent()
	/**
	 *
	* This function adds an event listener to an element on the page.
	 *
	 *	@param Object whichObject=Reference to HTML element(Which object to assigne the event)
	 *	@param String eventType=Which type of event, example "mousemove" or "mouseup" (NOT "onmousemove")
	 *	@param functionName=Name of function to execute. 
	*
	*@public
	 */
	addEvent:function( obj, type, fn,suffix ){
	if(!suffix)suffix='';
	if ( obj.attachEvent ){
		if ( typeof DHTMLSuite_funcs[type+fn+suffix]!='function'){
		DHTMLSuite_funcs[type+fn+suffix]=function(){
			fn.apply(window.event.srcElement);
		};
		obj.attachEvent('on'+type, DHTMLSuite_funcs[type+fn+suffix] );
		}
		obj=null;
	} else {
		obj.addEventListener( type, fn, false );
	}
	this.__addEventEl(obj);
	}

	
// }}}
	,
	
// {{{ removeEvent()
	/**
	 *
	* This function removes an event listener from an element on the page.
	 *
	 *	@param Object whichObject=Reference to HTML element(Which object to assigne the event)
	 *	@param String eventType=Which type of event, example "mousemove" or "mouseup"
	 *	@param functionName=Name of function to execute. 
	*
	*@public
	 */
	removeEvent:function(obj,type,fn,suffix){ 
	if ( obj.detachEvent ){
	obj.detachEvent( 'on'+type, DHTMLSuite_funcs[type+fn+suffix] );
		DHTMLSuite_funcs[type+fn+suffix]=null;
		obj=null;
	} else {
		obj.removeEventListener( type, fn, false );
	}
	} 
	
// }}}
	,
	
// {{{ __clearMemoryGarbage()
	/**
	 *
	* This function is used for Internet Explorer in order to clear memory when the page unloads.
	 *
	*
	*@private
	 */
	__clearMemoryGarbage:function(){
		/* Example of event which causes memory leakage in IE 
		DHTMLSuite.commonObj.addEvent(expandRef,"click",function(){ window.refToMyMenuBar[index].__changeMenuBarState(this); })
		We got a circular reference.
		*/
	if(!DHTMLSuite.clientInfoObj.isMSIE)return;

	for(var no=0;no<DHTMLSuite.eventEls.length;no++){
		try{
		var el=DHTMLSuite.eventEls[no];
		el.onclick=null;
		el.onmousedown=null;
		el.onmousemove=null;
		el.onmouseout=null;
		el.onmouseover=null;
		el.onmouseup=null;
		el.onfocus=null;
		el.onblur=null;
		el.onkeydown=null;
		el.onkeypress=null;
		el.onkeyup=null;
		el.onselectstart=null;
		el.ondragstart=null;
		el.oncontextmenu=null;
		el.onscroll=null;
		el=null; 
		}catch(e){
		}
	}

	for(var no in DHTMLSuite.variableStorage.arrayDSObjects){
		DHTMLSuite.variableStorage.arrayDSObjects[no]=null;
	}

	window.onbeforeunload=null;
	window.onunload=null;
	DHTMLSuite=null;
	}
	
// }}}
	,
	
// {{{ __addEventEl()
	/**
	 *
	* Add element to garbage collection array. The script will loop through this array and remove event handlers onload in ie.
	 *
	*
	*@private
	 */
	__addEventEl:function(el){
	DHTMLSuite.eventEls[DHTMLSuite.eventEls.length]=el;
	}
	
// }}}
	,
	
// {{{ getSrcElement()
	/**
	 *
	* Returns a reference to the HTML element which triggered an event.
	 *	@param Event e=Event object
	 *
	*
	*@public
	 */
	getSrcElement:function(e){
	var el;
	if (e.target)el=e.target;
		else if (e.srcElement)el=e.srcElement;
		if (el.nodeType==3)
// defeat Safari bug
		el=el.parentNode;
	return el;
	}
	
// }}}
	,
	
// {{{ getKeyFromEvent()
	/**
	 *
	* Returns key from event object
	 *	@param Event e=Event object
	*
	*@public
	 */	 
	getKeyFromEvent:function(e){
	var code=this.getKeyCode(e);
	return String.fromCharCode(code);
	}
	
// }}}
	,
	
// {{{ getKeyCode()
	/**
	 *
	* Returns key code from event
	 *	@param Event e=Event object
	*
	*@public
	 */	 
	getKeyCode:function(e){
	if (e.keyCode)code=e.keyCode; else if (e.which)code=e.which;  
	return code;
	}
	
// }}}
	,
	
// {{{ isObjectClicked()
	/**
	 *
	* Returns true if an object is clicked, false otherwise. This method will also return true if you clicked on a sub element
	 *	@param Object obj=Reference to HTML element
	 *	@param Event e=Event object
	 *
	*
	*@public
	 */	  
	isObjectClicked:function(obj,e){
	var src=this.getSrcElement(e);
	var string=src.tagName+'('+src.className+')';
	if(src==obj)return true;
	while(src.parentNode&&src.tagName.toLowerCase()!='html'){
		src=src.parentNode;
		string=string+','+src.tagName+'('+src.className+')';
		if(src==obj)return true;
	}
	return false;
	}
	
// }}}
	,
	
// {{{ getObjectByClassName()
	/**
	 *
	* Walks up the DOM tree and returns first found object with a given class name
	 *
	 *	@param Event e=Event object
	 *	@param String className=CSS-Class name
	 *
	*
	*@public
	 */	 
	getObjectByClassName:function(e,className){
	var src=this.getSrcElement(e);
	if(src.className==className)return src;
	while(src&&src.tagName.toLowerCase()!='html'){
		src=src.parentNode;
		if(src.className==className)return src;
	}
	return false;
	}
	
//}}}
	,
	
// {{{ getObjectByAttribute()
	/**
	 *
	* Walks up the DOM tree and returns first found object with a given attribute set
	 *
	 *	@param Event e=Event object
	 *	@param String attribute=Custom attribute
	 *
	*
	*@public
	 */	 
	getObjectByAttribute:function(e,attribute){
	var src=this.getSrcElement(e);
	var att=src.getAttribute(attribute);
	if(!att)att=src[attribute];
	if(att)return src;
	while(src&&src.tagName.toLowerCase()!='html'){
		src=src.parentNode;
		var att=src.getAttribute('attribute');
		if(!att)att=src[attribute];
		if(att)return src;
	}
	return false;
	}
	
//}}}
	,
	
// {{{ getUniqueId()
	/**
	 *
	* Returns a unique numeric id
	 *
	 *
	*
	*@public
	 */
	getUniqueId:function(){
	var no=Math.random()+'';
	no=no.replace('.','');
	var no2=Math.random()+'';
	no2=no2.replace('.','');
	return no+no2;
	}
	
// }}}
	,
	
// {{{ getAssociativeArrayFromString()
	/**
	 *
	* Returns an associative array from a comma delimited string
	* @param String propertyString-commaseparated string(example: "id:myid,title:My title,contentUrl:includes/tab.inc")
	 *
	 *	@return Associative array of keys+property value(example: key: id, value:myId)
	*@public
	 */
	getAssociativeArrayFromString:function(propertyString){
	if(!propertyString)return;
	var retArray=new Array();
	var items=propertyString.split(/,/g);
	for(var no=0;no<items.length;no++){
		var tokens=items[no].split(/:/);
		retArray[tokens[0]]=tokens[1];
	}
	return retArray;
	}
	
// }}}
	,
	
// {{{ correctPng()
	/**
	 *
	* Correct png for old IE browsers
	* @param Object el-Id or direct reference to image
	 *
	*@public
	 */
	correctPng:function(el){
	el=DHTMLSuite.commonObj.getEl(el);
	var img=el;
	var width=img.width;
	var height=img.height;
	var html='<span style="display:inline-block;filter:progid:DXImageTransform.Microsoft.AlphaImageLoader(src=\''+img.src+'\',sizingMethod=\'scale\');width:'+width+';height:'+height+'"></span>';
	img.outerHTML=html;

	}
	,
	
// {{{ __evaluateJs()
	/**
	*Evaluate Javascript in the inserted content
	 *
	*@private
	 */
	__evaluateJs:function(obj){
	obj=this.getEl(obj);
	var scriptTags=obj.getElementsByTagName('SCRIPT');
	var string='';
	var jsCode='';
	for(var no=0;no<scriptTags.length;no++){
		if(scriptTags[no].src){
		var head=document.getElementsByTagName("head")[0];
		var scriptObj=document.createElement("script");

		scriptObj.setAttribute("type", "text/javascript");
		scriptObj.setAttribute("src", scriptTags[no].src);  
		}else{
		if(DHTMLSuite.clientInfoObj.isOpera){
			jsCode=jsCode+scriptTags[no].text+'\n';
		}
		else
			jsCode=jsCode+scriptTags[no].innerHTML;
		}
	}
	if(jsCode)this.__installScript(jsCode);
	}
	
// }}}
	,
	
// {{{ __installScript()
	/**
	* "Installs" the content of a <script> tag.
	 *
	*@private
	 */
	__installScript:function ( script ){
	try{
		if (!script)
		return;
		if (window.execScript){
		window.execScript(script)
		}else if(window.jQuery&&jQuery.browser.safari){ 
// safari detection in jQuery
		window.setTimeout(script,0);
		}else{
		window.setTimeout( script, 0 );
		} 
	}catch(e){

	}
	}
	
// }}}
	,
	
// {{{ __evaluateCss()
	/**
	* Evaluates css
	 *
	*@private
	 */
	__evaluateCss:function(obj){
	obj=this.getEl(obj);
	var cssTags=obj.getElementsByTagName('STYLE');
	var head=document.getElementsByTagName('HEAD')[0];
	for(var no=0;no<cssTags.length;no++){
		head.appendChild(cssTags[no]);
	}
	}
}

/************************************************************************************************************
*	Client info class
*
*	Created:		August, 18th, 2006
* 	Update log:
*
************************************************************************************************************/

/**
* @constructor
* @class Purpose of class: Provide browser information to the classes below. Instead of checking for
*	 browser versions and browser types in the classes below, they should check this
*	 easily by referncing properties in the class below. An object("DHTMLSuite.clientInfoObj")of this 
*	 class will always be accessible to the other classes.*@version 1.0
* @author	Alf Magne Kalleland(www.dhtmlgoodies.com)
**/

DHTMLSuite.clientInfo=function(){
	var browser;		
// Complete user agent information

	var isOpera;		
// Is the browser "Opera"
	var isMSIE;		
// Is the browser "Internet Explorer"
	var isOldMSIE;		
// Is this browser and older version of Internet Explorer ( by older, we refer to version 6.0 or lower)
	var isFirefox;		
// Is the browser "Firefox"
	var navigatorVersion;	
// Browser version
	var isOldMSIE;
}

DHTMLSuite.clientInfo.prototype={

	
// {{{ init()
	/**
	* This method initializes the clientInfo object. This is done automatically when you create a widget object.
	 *
	*@public
	 */
	init:function(){
	this.browser=navigator.userAgent;
	this.isOpera=(this.browser.toLowerCase().indexOf('opera')>=0)?true:false;
	this.isFirefox=(this.browser.toLowerCase().indexOf('firefox')>=0)?true:false;
	this.isMSIE=(this.browser.toLowerCase().indexOf('msie')>=0)?true:false;
	this.isOldMSIE=(this.browser.toLowerCase().match(/msie\s[0-6]/gi))?true:false;
	this.isSafari=(this.browser.toLowerCase().indexOf('safari')>=0)?true:false;
	this.navigatorVersion=navigator.appVersion.replace(/.*?MSIE\s(\d\.\d).*/g,'$1')/1;
	this.isOldMSIE=(this.isMSIE&&this.navigatorVersion<7)?true:false;
	}
	
// }}}
	,
	
// {{{ getBrowserWidth()
	/**
	 *
	 *
	* This method returns the width of the browser window(i.e. inner width)
	 *
	*
	*@public
	 */
	getBrowserWidth:function(){
	if(self.innerWidth)return self.innerWidth;
	return document.documentElement.offsetWidth;
	}
	
// }}}
	,
	
// {{{ getBrowserHeight()
	/**
	 *
	 *
	* This method returns the height of the browser window(i.e. inner height)
	 *
	*
	*@public
	 */
	getBrowserHeight:function(){
	if(self.innerHeight)return self.innerHeight;
	return document.documentElement.offsetHeight;
	}
}

/************************************************************************************************************
*	DOM query class 
*
*	Created:		August, 31th, 2006
*
* 	Update log:
*
************************************************************************************************************/

/**
* @constructor
* @class Purpose of class:	Gives you a set of methods for querying elements on a webpage. When an object
*	 of this class has been created, the method will also be available via the document object.
*	 Example: var elements=document.getElementsByClassName('myClass');
* @version 1.0
* @author	Alf Magne Kalleland(www.dhtmlgoodies.com)
**/

DHTMLSuite.domQuery=function(){
	
// Make methods of this class a member of the document object. 
	document.getElementsByClassName=this.getElementsByClassName;
	document.getElementsByAttribute=this.getElementsByAttribute;
}

DHTMLSuite.domQuery.prototype={
}
