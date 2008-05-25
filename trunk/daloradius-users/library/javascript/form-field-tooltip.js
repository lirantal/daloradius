/************************************************************************************************************

	Form field tooltip
	(C) www.dhtmlgoodies.com, September 2006

	This is a script from www.dhtmlgoodies.com. You will find this and a lot of other scripts at our website.	
	
	Terms of use:
	Look at the terms of use at http://www.dhtmlgoodies.com/index.html?page=termsOfUse
	
	Thank you!
	
	www.dhtmlgoodies.com
	Alf Magne Kalleland

************************************************************************************************************/

var DHTMLgoodies_globalTooltipObj;


/** 
Constructor 
**/
function DHTMLgoodies_formTooltip()
{
	var tooltipDiv;
	var tooltipText;
	var tooltipContentDiv;				// Reference to inner div with tooltip content
	var imagePath;						// Relative path to images
	var arrowImageFile;					// Name of arrow image
	var arrowImageFileRight;			// Name of arrow image
	var arrowRightWidth;
	var arrowTopHeight;
	var tooltipWidth;					// Width of tooltip
	var roundedCornerObj;				// Reference to object of class DHTMLgoodies_roundedCorners
	var tooltipBgColor;
	var closeMessage;					// Close message
	var activeInput;					// Reference to currently active input
	var tooltipPosition;				// Tooltip position, possible values: "below" or "right"
	var tooltipCornerSize;				// Size of rounded corners
	var displayArrow;					// Display arrow above or at the left of the tooltip?
	var cookieName;						// Name of cookie
	var disableTooltipPossibility;		// Possibility of disabling tooltip
	var disableTooltipByCookie;			// If tooltip has been disabled, save the settings in cookie, i.e. for other pages with the same cookie name.
	var disableTooltipMessage;
	var tooltipDisabled;
	var isMSIE;
	var tooltipIframeObj;
	var pageBgColor;					// Color of background - used in ie when applying iframe which covers select boxes
	var currentTooltipObj;				// Reference to form field which tooltip is currently showing for
	
	this.currentTooltipObj = false,
	this.tooltipDiv = false,
	this.tooltipText = false;
	this.imagePath = 'images/';
	this.arrowImageFile = 'green-arrow.gif';
	this.arrowImageFileRight = 'green-arrow-right.gif';
	this.tooltipWidth = 200;
	this.tooltipBgColor = '#317082';
//	this.tooltipBgColor = '#006600';
//	this.tooltipBgColor = '#CCCC00';
//	this.tooltipBgColor = '#99CC00';
//	this.tooltipBgColor = '#336600';
//	this.tooltipBgColor = '#CEE574';
//	this.tooltipBgColor = '#4F9EC9';
	this.closeMessage = 'Close';
	this.disableTooltipMessage = 'Don\'t show this message again';
	this.activeInput = false;
	this.tooltipPosition = 'right';
	this.arrowRightWidth = 16;			// Default width of arrow when the tooltip is on the right side of the inputs.
	this.arrowTopHeight = 13;			// Default height of arrow at the top of tooltip
	this.tooltipCornerSize = 10;
	this.displayArrow = true;
	this.cookieName = 'DHTMLgoodies_tooltipVisibility';
	this.disableTooltipByCookie = false;
	this.tooltipDisabled = false;
	this.disableTooltipPossibility = true;
	this.tooltipIframeObj = false;
	this.pageBgColor = '#FFFFFF';
	
	DHTMLgoodies_globalTooltipObj = this;
	
	if(navigator.userAgent.indexOf('MSIE')>=0)this.isMSIE = true; else this.isMSIE = false;
}


DHTMLgoodies_formTooltip.prototype = {
	// {{{ initFormFieldTooltip()
    /**
     *
	 *
     *  Initializes the tooltip script. Most set methods needs to be executed before you call this method.
     * 
     * @public
     */		
	initFormFieldTooltip : function()
	{
		var formElements = new Array();
		var inputs = document.getElementsByTagName('a');
		for(var no=0;no<inputs.length;no++){
			var attr = inputs[no].getAttribute('tooltipText');
			if(!attr)attr = inputs[no].tooltipText;
			if(attr)formElements[formElements.length] = inputs[no];
		}

		var inputs = document.getElementsByTagName('INPUT');
		for(var no=0;no<inputs.length;no++){
			var attr = inputs[no].getAttribute('tooltipText');
			if(!attr)attr = inputs[no].tooltipText;
			if(attr)formElements[formElements.length] = inputs[no];
		}
			
		var inputs = document.getElementsByTagName('TEXTAREA');
		for(var no=0;no<inputs.length;no++){
			var attr = inputs[no].getAttribute('tooltipText');
			if(!attr)attr = inputs[no].tooltipText;
			if(attr)formElements[formElements.length] = inputs[no];
		}
		var inputs = document.getElementsByTagName('SELECT');
		for(var no=0;no<inputs.length;no++){
			var attr = inputs[no].getAttribute('tooltipText');
			if(!attr)attr = inputs[no].tooltipText;
			if(attr)formElements[formElements.length] = inputs[no];
		}
			
		window.refToFormTooltip = this;
		
		for(var no=0;no<formElements.length;no++){
			formElements[no].onfocus = this.__displayTooltip;
		}
		this.addEvent(window,'resize',function(){ window.refToFormTooltip.__positionCurrentToolTipObj(); });
		
		this.addEvent(document.documentElement,'click',function(e){ window.refToFormTooltip.__autoHideTooltip(e); });
	}
	
	// }}}
	,		
	// {{{ setTooltipPosition()
    /**
     *
	 *
     *  Specify position of tooltip(below or right)
     *	@param String newPosition (Possible values: "below" or "right") 
     * 
     * @public
     */	
	setTooltipPosition : function(newPosition)
	{
		this.tooltipPosition = newPosition;
	}
	// }}}
	,		
	// {{{ setCloseMessage()
    /**
     *
	 *
     *  Specify "Close" message
     *	@param String closeMessage
     * 
     * @public
     */
	setCloseMessage : function(closeMessage)
	{
		this.closeMessage = closeMessage;
	}
	// }}}
	,	
	// {{{ setDisableTooltipMessage()
    /**
     *
	 *
     *  Specify disable tooltip message at the bottom of the tooltip
     *	@param String disableTooltipMessage
     * 
     * @public
     */
	setDisableTooltipMessage : function(disableTooltipMessage)
	{
		this.disableTooltipMessage = disableTooltipMessage;
	}
	// }}}
	,		
	// {{{ setTooltipDisablePossibility()
    /**
     *
	 *
     *  Specify whether you want the disable link to appear or not.
     *	@param Boolean disableTooltipPossibility
     * 
     * @public
     */
	setTooltipDisablePossibility : function(disableTooltipPossibility)
	{
		this.disableTooltipPossibility = disableTooltipPossibility;
	}
	// }}}
	,		
	// {{{ setCookieName()
    /**
     *
	 *
     *  Specify name of cookie. Useful if you're using this script on several pages. 
     *	@param String newCookieName
     * 
     * @public
     */
	setCookieName : function(newCookieName)
	{
		this.cookieName = newCookieName;
	}
	// }}}
	,		
	// {{{ setTooltipWidth()
    /**
     *
	 *
     *  Specify width of tooltip
     *	@param Int newWidth
     * 
     * @public
     */	
	setTooltipWidth : function(newWidth)
	{
		this.tooltipWidth = newWidth;
	}
	
	// }}}
	,		
	// {{{ setArrowVisibility()
    /**
     *
	 *
     *  Display arrow at the top or at the left of the tooltip?
     *	@param Boolean displayArrow
     * 
     * @public
     */	
	
	setArrowVisibility : function(displayArrow)
	{
		this.displayArrow = displayArrow;
	}
	
	// }}}
	,		
	// {{{ setTooltipBgColor()
    /**
     *
	 *
     *  Send true to this method if you want to be able to save tooltip visibility in cookie. If it's set to true,
     *	It means that when someone returns to the page, the tooltips won't show.
     * 
     *	@param Boolean disableTooltipByCookie
     * 
     * @public
     */	
	setDisableTooltipByCookie : function(disableTooltipByCookie)
	{
		this.disableTooltipByCookie = disableTooltipByCookie;
	}	
	// }}}
	,		
	// {{{ setTooltipBgColor()
    /**
     *
	 *
     *  This method specifies background color of tooltip
     *	@param String newBgColor
     * 
     * @public
     */	
	setTooltipBgColor : function(newBgColor)
	{
		this.tooltipBgColor = newBgColor;
	}
	
	// }}}
	,		
	// {{{ setTooltipCornerSize()
    /**
     *
	 *
     *  Size of rounded corners around tooltip
     *	@param Int newSize (0 = no rounded corners)
     * 
     * @public
     */	
	setTooltipCornerSize : function(tooltipCornerSize)
	{
		this.tooltipCornerSize = tooltipCornerSize;
	}
	
	// }}}
	,
	// {{{ setTopArrowHeight()
    /**
     *
	 *
     *  Size height of arrow at the top of tooltip
     *	@param Int arrowTopHeight
     * 
     * @public
     */	
	setTopArrowHeight : function(arrowTopHeight)
	{
		this.arrowTopHeight = arrowTopHeight;
	}
	
	// }}}
	,	
	// {{{ setRightArrowWidth()
    /**
     *
	 *
     *  Size width of arrow when the tooltip is on the right side of inputs
     *	@param Int arrowTopHeight
     * 
     * @public
     */	
	setRightArrowWidth : function(arrowRightWidth)
	{
		this.arrowRightWidth = arrowRightWidth;
	}
	
	// }}}
	,	
	// {{{ setPageBgColor()
    /**
     *
	 *
     *  Specify background color of page.
     *	@param String pageBgColor
     * 
     * @public
     */	
	setPageBgColor : function(pageBgColor)
	{
		this.pageBgColor = pageBgColor;
	}
	
	// }}}
	,		
	// {{{ __hideTooltip()
    /**
     *
	 *
     *  This method displays the tooltip
     *
     * 
     * @private
     */		
	__displayTooltip : function()
	{
		if(DHTMLgoodies_globalTooltipObj.disableTooltipByCookie){
			var cookieValue = DHTMLgoodies_globalTooltipObj.getCookie(DHTMLgoodies_globalTooltipObj.cookieName) + '';	
			if(cookieValue=='1')DHTMLgoodies_globalTooltipObj.tooltipDisabled = true;
		}	
		
		if(DHTMLgoodies_globalTooltipObj.tooltipDisabled)return;	// Tooltip disabled
		var tooltipText = this.getAttribute('tooltipText');
		DHTMLgoodies_globalTooltipObj.activeInput = this;
		
		if(!tooltipText)tooltipText = this.tooltipText;
		DHTMLgoodies_globalTooltipObj.tooltipText = tooltipText;

		
		if(!DHTMLgoodies_globalTooltipObj.tooltipDiv)DHTMLgoodies_globalTooltipObj.__createTooltip();
		
		DHTMLgoodies_globalTooltipObj.__positionTooltip(this);
		
		
		
	
		DHTMLgoodies_globalTooltipObj.tooltipContentDiv.innerHTML = tooltipText;
		DHTMLgoodies_globalTooltipObj.tooltipDiv.style.display='block';
		
		if(DHTMLgoodies_globalTooltipObj.isMSIE){
			if(DHTMLgoodies_globalTooltipObj.tooltipPosition == 'below'){
				DHTMLgoodies_globalTooltipObj.tooltipIframeObj.style.height = (DHTMLgoodies_globalTooltipObj.tooltipDiv.clientHeight - DHTMLgoodies_globalTooltipObj.arrowTopHeight);
			}else{
				DHTMLgoodies_globalTooltipObj.tooltipIframeObj.style.height = (DHTMLgoodies_globalTooltipObj.tooltipDiv.clientHeight);
			}
		}
		
	}
	// }}}
	,		
	// {{{ __hideTooltip()
    /**
     *
	 *
     *  This function hides the tooltip
     *
     * 
     * @private
     */		
	__hideTooltip : function()
	{
		try{
			DHTMLgoodies_globalTooltipObj.tooltipDiv.style.display='none';
		}catch(e){
		}
		
	}
	// }}}
	,
	// {{{ getSrcElement()
    /**
     *
	 *
     *  Return the source of an event.
     *
     * 
     * @private
     */		
    getSrcElement : function(e)
    {
    	var el;
		if (e.target) el = e.target;
			else if (e.srcElement) el = e.srcElement;
			if (el.nodeType == 3) // defeat Safari bug
				el = el.parentNode;
		return el;	
    }	
	// }}}
	,
	__autoHideTooltip : function(e)
	{

		if(document.all)e = event;	
		var src = this.getSrcElement(e);
		if(src.tagName.toLowerCase()!='a' && src.tagName.toLowerCase()!='input' && src.tagName.toLowerCase().toLowerCase()!='textarea' && src.tagName.toLowerCase().toLowerCase()!='select')this.__hideTooltip();

		var attr = src.getAttribute('tooltipText');
		if(!attr)attr = src.tooltipText;
		if(!attr){
			this.__hideTooltip();
		}
		
	}
	// }}}
	,		
	// {{{ __hideTooltipFromLink()
    /**
     *
	 *
     *  This function hides the tooltip
     *
     * 
     * @private
     */	
	__hideTooltipFromLink : function()
	{
		
		this.activeInput.focus();
		window.refToThis = this;
		setTimeout('window.refToThis.__hideTooltip()',10);
	}
	// }}}
	,		
	// {{{ disableTooltip()
    /**
     *
	 *
     *  Hide tooltip and disable it
     *
     * 
     * @public
     */	
	disableTooltip : function()
	{
		this.__hideTooltipFromLink();
		if(this.disableTooltipByCookie)this.setCookie(this.cookieName,'1',500);	
		this.tooltipDisabled = true;	
	}	
	// }}}
	,		
	// {{{ __positionTooltip()
    /**
     *
	 *
     *  This function creates the tooltip elements
     *
     * 
     * @private
     */	
	__createTooltip : function()
	{
		this.tooltipDiv = document.createElement('DIV');
		this.tooltipDiv.style.position = 'absolute';
		
		if(this.displayArrow){
			var topDiv = document.createElement('DIV');
			
			if(this.tooltipPosition=='below'){
				
				topDiv.style.marginLeft = '20px';
				var arrowDiv = document.createElement('IMG');
				arrowDiv.src = this.imagePath + this.arrowImageFile + '?rand='+ Math.random();
				arrowDiv.style.display='block';
				topDiv.appendChild(arrowDiv);
					
			}else{
				topDiv.style.marginTop = '5px';
				var arrowDiv = document.createElement('IMG');
				arrowDiv.src = this.imagePath + this.arrowImageFileRight + '?rand='+ Math.random();	
				arrowDiv.style.display='block';
				topDiv.appendChild(arrowDiv);					
				topDiv.style.position = 'absolute';			
			}
			
			this.tooltipDiv.appendChild(topDiv);	
		}
		
		var outerDiv = document.createElement('DIV');
		outerDiv.style.position = 'relative';
		outerDiv.style.zIndex = 1000;
		if(this.tooltipPosition!='below' && this.displayArrow){			
			outerDiv.style.left = this.arrowRightWidth + 'px';
		}
				
		outerDiv.id = 'DHTMLgoodies_formTooltipDiv';
		outerDiv.className = 'DHTMLgoodies_formTooltipDiv';
		outerDiv.style.backgroundColor = this.tooltipBgColor;
		this.tooltipDiv.appendChild(outerDiv);

		if(this.isMSIE){
			this.tooltipIframeObj = document.createElement('<IFRAME name="tooltipIframeObj" width="' + this.tooltipWidth + '" frameborder="no" src="about:blank"></IFRAME>');
			this.tooltipIframeObj.style.position = 'absolute';
			this.tooltipIframeObj.style.top = '0px';
			this.tooltipIframeObj.style.left = '0px';
			this.tooltipIframeObj.style.width = (this.tooltipWidth) + 'px';
			this.tooltipIframeObj.style.zIndex = 100;
			this.tooltipIframeObj.background = this.pageBgColor;
			this.tooltipIframeObj.style.backgroundColor= this.pageBgColor;
			this.tooltipDiv.appendChild(this.tooltipIframeObj);	
			if(this.tooltipPosition!='below' && this.displayArrow){
				this.tooltipIframeObj.style.left = (this.arrowRightWidth) +  'px';	
			}else{
				this.tooltipIframeObj.style.top = this.arrowTopHeight + 'px';	
			}

			setTimeout("self.frames['tooltipIframeObj'].document.documentElement.style.backgroundColor='" + this.pageBgColor + "'",500);

		}
		
		this.tooltipContentDiv = document.createElement('DIV');	
		this.tooltipContentDiv.style.position = 'relative';	
		this.tooltipContentDiv.id = 'DHTMLgoodies_formTooltipContent';
		outerDiv.appendChild(this.tooltipContentDiv);			
		
		var closeDiv = document.createElement('DIV');
		closeDiv.style.textAlign = 'center';
	
		closeDiv.innerHTML = '<A class="DHTMLgoodies_formTooltip_closeMessage" href="#" onclick="DHTMLgoodies_globalTooltipObj.__hideTooltipFromLink();return false">' + this.closeMessage + '</A>';
		
		if(this.disableTooltipPossibility){
			var tmpHTML = closeDiv.innerHTML;
			tmpHTML = tmpHTML + ' | <A class="DHTMLgoodies_formTooltip_closeMessage" href="#" onclick="DHTMLgoodies_globalTooltipObj.disableTooltip();return false">' + this.disableTooltipMessage + '</A>';
			closeDiv.innerHTML = tmpHTML;
		} 
		
		outerDiv.appendChild(closeDiv);
		
		document.body.appendChild(this.tooltipDiv);
		
		
				
		if(this.tooltipCornerSize>0){
			this.roundedCornerObj = new DHTMLgoodies_roundedCorners();
			// (divId,xRadius,yRadius,color,backgroundColor,padding,heightOfContent,whichCorners)
			this.roundedCornerObj.addTarget('DHTMLgoodies_formTooltipDiv',this.tooltipCornerSize,this.tooltipCornerSize,this.tooltipBgColor,this.pageBgColor,5);
			this.roundedCornerObj.init();
		}
		

		this.tooltipContentDiv = document.getElementById('DHTMLgoodies_formTooltipContent');
	}
	// }}}
	,
	addEvent : function(whichObject,eventType,functionName)
	{ 
	  if(whichObject.attachEvent){ 
	    whichObject['e'+eventType+functionName] = functionName; 
	    whichObject[eventType+functionName] = function(){whichObject['e'+eventType+functionName]( window.event );} 
	    whichObject.attachEvent( 'on'+eventType, whichObject[eventType+functionName] ); 
	  } else 
	    whichObject.addEventListener(eventType,functionName,false); 	    
	} 	
	// }}}
	,
	__positionCurrentToolTipObj : function()
	{
		if(DHTMLgoodies_globalTooltipObj.activeInput)this.__positionTooltip(DHTMLgoodies_globalTooltipObj.activeInput);
		
	}
	// }}}
	,		
	// {{{ __positionTooltip()
    /**
     *
	 *
     *  This function positions the tooltip
     *
     * @param Obj inputObj = Reference to text input
     * 
     * @private
     */	
	__positionTooltip : function(inputObj)
	{	
		var offset = 0;
		if(!this.displayArrow)offset = 3;	
		if(this.tooltipPosition=='below'){
			this.tooltipDiv.style.left = this.getLeftPos(inputObj)+  'px';
			this.tooltipDiv.style.top = (this.getTopPos(inputObj) + inputObj.offsetHeight + offset) + 'px';
		}else{
		
			this.tooltipDiv.style.left = (this.getLeftPos(inputObj) + inputObj.offsetWidth + offset)+  'px';
			this.tooltipDiv.style.top = this.getTopPos(inputObj) + 'px';			
		}
		this.tooltipDiv.style.width=this.tooltipWidth + 'px';
		
	}
	,
	// {{{ getTopPos()
    /**
     * This method will return the top coordinate(pixel) of an object
     *
     * @param Object inputObj = Reference to HTML element
     * @public
     */	
	getTopPos : function(inputObj)
	{		
	  var returnValue = inputObj.offsetTop;
	  while((inputObj = inputObj.offsetParent) != null){
	  	if(inputObj.tagName!='HTML'){
	  		returnValue += inputObj.offsetTop;
	  		if(document.all)returnValue+=inputObj.clientTop;
	  	}
	  } 
	  return returnValue;
	}
	// }}}
	
	,
	// {{{ getLeftPos()
    /**
     * This method will return the left coordinate(pixel) of an object
     *
     * @param Object inputObj = Reference to HTML element
     * @public
     */	
	getLeftPos : function(inputObj)
	{	  
	  var returnValue = inputObj.offsetLeft;
	  while((inputObj = inputObj.offsetParent) != null){
	  	if(inputObj.tagName!='HTML'){
	  		returnValue += inputObj.offsetLeft;
	  		if(document.all)returnValue+=inputObj.clientLeft;
	  	}
	  }
	  return returnValue;
	}
	
	,
	
	// {{{ getCookie()
    /**
     *
     * 	These cookie functions are downloaded from 
	 * 	http://www.mach5.com/support/analyzer/manual/html/General/CookiesJavaScript.htm
	 *
     *  This function returns the value of a cookie
     *
     * @param String name = Name of cookie
     * @param Object inputObj = Reference to HTML element
     * @public
     */	
	getCookie : function(name) { 
	   var start = document.cookie.indexOf(name+"="); 
	   var len = start+name.length+1; 
	   if ((!start) && (name != document.cookie.substring(0,name.length))) return null; 
	   if (start == -1) return null; 
	   var end = document.cookie.indexOf(";",len); 
	   if (end == -1) end = document.cookie.length; 
	   return unescape(document.cookie.substring(len,end)); 
	} 	
	// }}}
	,	
	
	// {{{ setCookie()
    /**
     *
     * 	These cookie functions are downloaded from 
	 * 	http://www.mach5.com/support/analyzer/manual/html/General/CookiesJavaScript.htm
	 *
     *  This function creates a cookie. (This method has been slighhtly modified)
     *
     * @param String name = Name of cookie
     * @param String value = Value of cookie
     * @param Int expires = Timestamp - days
     * @param String path = Path for cookie (Usually left empty)
     * @param String domain = Cookie domain
     * @param Boolean secure = Secure cookie(SSL)
     * 
     * @public
     */	
	setCookie : function(name,value,expires,path,domain,secure) { 
		expires = expires * 60*60*24*1000;
		var today = new Date();
		var expires_date = new Date( today.getTime() + (expires) );
	    var cookieString = name + "=" +escape(value) + 
	       ( (expires) ? ";expires=" + expires_date.toGMTString() : "") + 
	       ( (path) ? ";path=" + path : "") + 
	       ( (domain) ? ";domain=" + domain : "") + 
	       ( (secure) ? ";secure" : ""); 
	    document.cookie = cookieString; 
	}
	// }}}
		
		
}
