/************************************************************************************************************<br>
<br>
	@fileoverview
	Rounded corners class<br>
	(C) www.dhtmlgoodies.com, September 2006<br>
	<br>
	This is a script from www.dhtmlgoodies.com. You will find this and a lot of other scripts at our website.	<br>
	<br>
	Terms of use:<br>
	Look at the terms of use at http://www.dhtmlgoodies.com/index.html?page=termsOfUse<br>
	<br>
	Thank you!<br>
	<br>
	www.dhtmlgoodies.com<br>
	Alf Magne Kalleland<br>
<br>
************************************************************************************************************/

// {{{ Constructor
function DHTMLgoodies_roundedCorners()
{
	var roundedCornerTargets;
	
	this.roundedCornerTargets = new Array();
	
}
	var string = '';
// }}}
DHTMLgoodies_roundedCorners.prototype = {

	// {{{ addTarget() 
    /**
     *
	 *
     *  Add rounded corners to an element
     *
     *	@param String divId = Id of element on page. Example "leftColumn" for &lt;div id="leftColumn">
     *	@param Int xRadius = Y radius of rounded corners, example 10
     *	@param Int yRadius = Y radius of rounded corners, example 10
     *  @param String color = Background color of element, example #FFF or #AABBCC
     *  @param String color = backgroundColor color of element "behind", example #FFF or #AABBCC
     *  @param Int padding = Padding of content - This will be added as left and right padding(not top and bottom)
     *  @param String heightOfContent = Optional argument. You can specify a fixed height of your content. example "15" which means pixels, or "50%". 
     *  @param String whichCorners = Optional argument. Commaseparated list of corners, example "top_left,top_right,bottom_left"
     * 
     * @public
     */		
    addTarget : function(divId,xRadius,yRadius,color,backgroundColor,padding,heightOfContent,whichCorners)
    {	
    	var index = this.roundedCornerTargets.length;
    	this.roundedCornerTargets[index] = new Array();
    	this.roundedCornerTargets[index]['divId'] = divId;
    	this.roundedCornerTargets[index]['xRadius'] = xRadius;
    	this.roundedCornerTargets[index]['yRadius'] = yRadius;
    	this.roundedCornerTargets[index]['color'] = color;
    	this.roundedCornerTargets[index]['backgroundColor'] = backgroundColor;
    	this.roundedCornerTargets[index]['padding'] = padding;
    	this.roundedCornerTargets[index]['heightOfContent'] = heightOfContent;
    	this.roundedCornerTargets[index]['whichCorners'] = whichCorners;  
    	
    }
    // }}}
    ,
	// {{{ init()
    /**
     *
	 *
     *  Initializes the script
     *
     * 
     * @public
     */	    
	init : function()
	{
		
		for(var targetCounter=0;targetCounter < this.roundedCornerTargets.length;targetCounter++){
			
			// Creating local variables of each option
			whichCorners = this.roundedCornerTargets[targetCounter]['whichCorners'];
			divId = this.roundedCornerTargets[targetCounter]['divId'];
			xRadius = this.roundedCornerTargets[targetCounter]['xRadius'];
			yRadius = this.roundedCornerTargets[targetCounter]['yRadius'];
			color = this.roundedCornerTargets[targetCounter]['color'];
			backgroundColor = this.roundedCornerTargets[targetCounter]['backgroundColor'];
			padding = this.roundedCornerTargets[targetCounter]['padding'];
			heightOfContent = this.roundedCornerTargets[targetCounter]['heightOfContent'];
			whichCorners = this.roundedCornerTargets[targetCounter]['whichCorners'];

			// Which corners should we add rounded corners to?
			var cornerArray = new Array();
			if(!whichCorners || whichCorners=='all'){
				cornerArray['top_left'] = true;
				cornerArray['top_right'] = true;
				cornerArray['bottom_left'] = true;
				cornerArray['bottom_right'] = true;
			}else{
				cornerArray = whichCorners.split(/,/gi);
				for(var prop in cornerArray)cornerArray[cornerArray[prop]] = true;
			}
					
				
			var factorX = xRadius/yRadius;	// How big is x radius compared to y radius
		
			var obj = document.getElementById(divId);	// Creating reference to element
			obj.style.backgroundColor=null;	// Setting background color blank
			obj.style.backgroundColor='transparent';
			var content = obj.innerHTML;	// Saving HTML content of this element
			obj.innerHTML = '';	// Setting HTML content of element blank-
			
	
			
			
			// Adding top corner div.
			
			if(cornerArray['top_left'] || cornerArray['top_right']){
				var topBar_container = document.createElement('DIV');
				topBar_container.style.height = yRadius + 'px';
				topBar_container.style.overflow = 'hidden';	
		
				obj.appendChild(topBar_container);		
				var currentAntialiasSize = 0;
				var savedRestValue = 0;
				
				for(no=1;no<=yRadius;no++){
					var marginSize = (xRadius - (this.getY((yRadius - no),yRadius,factorX)));					
					var marginSize_decimals = (xRadius - (this.getY_withDecimals((yRadius - no),yRadius,factorX)));					
					var restValue = xRadius - marginSize_decimals;		
					var antialiasSize = xRadius - marginSize - Math.floor(savedRestValue)
					var foregroundSize = xRadius - (marginSize + antialiasSize);	
					
					var el = document.createElement('DIV');
					el.style.overflow='hidden';
					el.style.height = '1px';					
					if(cornerArray['top_left'])el.style.marginLeft = marginSize + 'px';				
					if(cornerArray['top_right'])el.style.marginRight = marginSize + 'px';	
					topBar_container.appendChild(el);				
					var y = topBar_container;		
					
					for(var no2=1;no2<=antialiasSize;no2++){
						switch(no2){
							case 1:
								if (no2 == antialiasSize)
									blendMode = ((restValue + savedRestValue) /2) - foregroundSize;
								else {
								  var tmpValue = this.getY_withDecimals((xRadius - marginSize - no2),xRadius,1/factorX);
								  blendMode = (restValue - foregroundSize - antialiasSize + 1) * (tmpValue - (yRadius - no)) /2;
								}						
								break;							
							case antialiasSize:								
								var tmpValue = this.getY_withDecimals((xRadius - marginSize - no2 + 1),xRadius,1/factorX);								
								blendMode = 1 - (1 - (tmpValue - (yRadius - no))) * (1 - (savedRestValue - foregroundSize)) /2;							
								break;
							default:			
								var tmpValue2 = this.getY_withDecimals((xRadius - marginSize - no2),xRadius,1/factorX);
								var tmpValue = this.getY_withDecimals((xRadius - marginSize - no2 + 1),xRadius,1/factorX);		
								blendMode = ((tmpValue + tmpValue2) / 2) - (yRadius - no);							
						}
						
						el.style.backgroundColor = this.__blendColors(backgroundColor,color,blendMode);
						y.appendChild(el);
						y = el;
						var el = document.createElement('DIV');
						el.style.height = '1px';	
						el.style.overflow='hidden';
						if(cornerArray['top_left'])el.style.marginLeft = '1px';
						if(cornerArray['top_right'])el.style.marginRight = '1px';    						
						el.style.backgroundColor=color;					
					}
					
					y.appendChild(el);				
					savedRestValue = restValue;
				}
			}
			
			// Add content
			var contentDiv = document.createElement('DIV');
			contentDiv.className = obj.className;
			contentDiv.style.border='1px solid ' + color;
			contentDiv.innerHTML = content;
			contentDiv.style.backgroundColor=color;
			contentDiv.style.paddingLeft = padding + 'px';
			contentDiv.style.paddingRight = padding + 'px';
	
			if(!heightOfContent)heightOfContent = '';
			heightOfContent = heightOfContent + '';
			if(heightOfContent.length>0 && heightOfContent.indexOf('%')==-1)heightOfContent = heightOfContent + 'px';
			if(heightOfContent.length>0)contentDiv.style.height = heightOfContent;
			
			obj.appendChild(contentDiv);
	
		
			if(cornerArray['bottom_left'] || cornerArray['bottom_right']){
				var bottomBar_container = document.createElement('DIV');
				bottomBar_container.style.height = yRadius + 'px';
				bottomBar_container.style.overflow = 'hidden';	
		
				obj.appendChild(bottomBar_container);		
				var currentAntialiasSize = 0;
				var savedRestValue = 0;
				
				var errorOccured = false;
				var arrayOfDivs = new Array();
				for(no=1;no<=yRadius;no++){
					
					var marginSize = (xRadius - (this.getY((yRadius - no),yRadius,factorX)));					
					var marginSize_decimals = (xRadius - (this.getY_withDecimals((yRadius - no),yRadius,factorX)));						
	
					var restValue = (xRadius - marginSize_decimals);				
					var antialiasSize = xRadius - marginSize - Math.floor(savedRestValue)
					var foregroundSize = xRadius - (marginSize + antialiasSize);	
					
					var el = document.createElement('DIV');
					el.style.overflow='hidden';
					el.style.height = '1px';					
					if(cornerArray['bottom_left'])el.style.marginLeft = marginSize + 'px';				
					if(cornerArray['bottom_right'])el.style.marginRight = marginSize + 'px';	
					bottomBar_container.insertBefore(el,bottomBar_container.firstChild);				
					
					var y = bottomBar_container;		
					
					for(var no2=1;no2<=antialiasSize;no2++){
						switch(no2){
							case 1:
								if (no2 == antialiasSize)
									blendMode = ((restValue + savedRestValue) /2) - foregroundSize;
								else {
								  var tmpValue = this.getY_withDecimals((xRadius - marginSize - no2),xRadius,1/factorX);
								  blendMode = (restValue - foregroundSize - antialiasSize + 1) * (tmpValue - (yRadius - no)) /2;
								}						
								break;							
							case antialiasSize:								
								var tmpValue = this.getY_withDecimals((xRadius - marginSize - no2 + 1),xRadius,1/factorX);								
								blendMode = 1 - (1 - (tmpValue - (yRadius - no))) * (1 - (savedRestValue - foregroundSize)) /2;							
								break;
							default:			
								var tmpValue2 = this.getY_withDecimals((xRadius - marginSize - no2),xRadius,1/factorX);
								var tmpValue = this.getY_withDecimals((xRadius - marginSize - no2 + 1),xRadius,1/factorX);		
								blendMode = ((tmpValue + tmpValue2) / 2) - (yRadius - no);							
						}
						
						el.style.backgroundColor = this.__blendColors(backgroundColor,color,blendMode);
						
						if(y==bottomBar_container)arrayOfDivs[arrayOfDivs.length] = el;
						
						try{	// Need to look closer at this problem which occures in Opera.
							var firstChild = y.getElementsByTagName('DIV')[0];
							y.insertBefore(el,y.firstChild);
						}catch(e){
							y.appendChild(el);							
							errorOccured = true;
						}
						y = el;
						
						var el = document.createElement('DIV');
						el.style.height = '1px';	
						el.style.overflow='hidden';
						if(cornerArray['bottom_left'])el.style.marginLeft = '1px';
						if(cornerArray['bottom_right'])el.style.marginRight = '1px';    						
										
					}
					
					if(errorOccured){	// Opera fix
						for(var divCounter=arrayOfDivs.length-1;divCounter>=0;divCounter--){
							bottomBar_container.appendChild(arrayOfDivs[divCounter]);
						}
					}
					
					el.style.backgroundColor=color;	
					y.appendChild(el);				
					savedRestValue = restValue;
				}
	
			}			
		}
	}		
	// }}}
	,		
	// {{{ getY()
    /**
     *
	 *
     *  Add rounded corners to an element
     *
     *	@param Int x = x Coordinate
     *	@param Int maxX = Size of rounded corners
	 *
     * 
     * @private
     */		
	getY : function(x,maxX,factorX){
		// y = sqrt(100 - x^2)			
		// Y = 0.5 * ((100 - x^2)^0.5);			
		return Math.max(0,Math.ceil(factorX * Math.sqrt( (maxX * maxX) - (x*x)) ));
		
	}	
	// }}}
	,		
	// {{{ getY_withDecimals()
    /**
     *
	 *
     *  Add rounded corners to an element
     *
     *	@param Int x = x Coordinate
     *	@param Int maxX = Size of rounded corners
	 *
     * 
     * @private
     */		
	getY_withDecimals : function(x,maxX,factorX){
		// y = sqrt(100 - x^2)			
		// Y = 0.5 * ((100 - x^2)^0.5);			
		return Math.max(0,factorX * Math.sqrt( (maxX * maxX) - (x*x)) );
		
	}
	

	,

	// {{{ __blendColors()
    /**
     *
	 *
     *  Simply blending two colors by extracting red, green and blue and subtracting difference between colors from them.
     * 	Finally, we multiply it with the blendMode value
     *
     *	@param String colorA = RGB color
     *	@param String colorB = RGB color
     *	@param Float blendMode 
	 *
     * 
     * @private
     */		
	__blendColors : function (colorA, colorB, blendMode) {
		if(colorA.length=='4'){	// In case we are dealing with colors like #FFF
			colorA = '#' + colorA.substring(1,1) + colorA.substring(1,1) + colorA.substring(2,1) + colorA.substring(2,1) + colorA.substring(3,1) + colorA.substring(3,1);
		}	
		if(colorB.length=='4'){	// In case we are dealing with colors like #FFF
			colorB = '#' + colorB.substring(1,1) + colorB.substring(1,1) + colorB.substring(2,1) + colorB.substring(2,1) + colorB.substring(3,1) + colorB.substring(3,1);
		}
		var colorArrayA = [parseInt('0x' + colorA.substring(1,3)), parseInt('0x' + colorA.substring(3, 5)), parseInt('0x' + colorA.substring(5, 7))];	// Create array of Red, Green and Blue ( 0-255)
		var colorArrayB = [parseInt('0x' + colorB.substring(1,3)), parseInt('0x' + colorB.substring(3, 5)), parseInt('0x' + colorB.substring(5, 7))];	// Create array of Red, Green and Blue ( 0-255)		
		var red = Math.round(colorArrayA[0] + (colorArrayB[0] - colorArrayA[0])*blendMode).toString(16);	// Create new Red color ( Hex )
		var green = Math.round(colorArrayA[1] + (colorArrayB[1] - colorArrayA[1])*blendMode).toString(16);	// Create new Green color ( Hex )
		var blue = Math.round(colorArrayA[2] + (colorArrayB[2] - colorArrayA[2])*blendMode).toString(16);	// Create new Blue color ( Hex )
		
		if(red.length==1)red = '0' + red;
		if(green.length==1)green = '0' + green;
		if(blue.length==1)blue = '0' + blue;
			
		return '#' + red + green+ blue;	// Return new RGB color
	}
}				
