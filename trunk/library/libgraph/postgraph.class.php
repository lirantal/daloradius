<?php
//============================================================================
// PostGraph Class. PHP Class to draw bar graphs.
// Version: 1.0
// Copyright (c) Maros Fric, qualityunit.com 2004
// All rights reserved
// 
// This library is free software; you can redistribute it and/or
// modify it under the terms of the GNU Lesser General Public
// License as published by the Free Software Foundation; either
// version 2.1 of the License, or (at your option) any later version.
//
// This library is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the GNU
// Lesser General Public License for more details.
// 
// You should have received a copy of the GNU Lesser General Public
// License along with this library; if not, write to the Free Software
// Foundation, Inc., 59 Temple Place, Suite 330, Boston, MA  02111-1307  USA
// 
// Copy of GNU Lesser General Public License at: http://www.gnu.org/copyleft/lesser.txt
//
// For support contact support@qualityunit.com
//============================================================================

class PostGraph
{
    var $img;
    
    var $graphWidth; // Graph Image Width
    var $graphHeight; // Graph Image Height
    var $textPadding; // Graph Text Padding
    
    var $graphTitle; // Graph main Title at the top center
    var $graphXTitle; // Graph Title at x axe
    var $graphYTitle; // Graph Title at y axe
    
    var $yTicks;  // Number of Ticks on y axe
    var $yNumberFormat; // Format numbers at y axe
    var $yValueMode; // Mode that defines place of y numbers. 3 for outside of bar, 2 inside, 1 inside if bar height is bigger then 13
    
    var $textXOrientation; // Text orientation at x axe. Usually normal.
    
    var $data = null; // Graph data
    var $countData; // Count of data
    var $dataSum; // Summary of data
    var $maxData; // Maximum value of data
    var $maxTextLength; // Lenght of maximum value
    
    var $colorWhiteArray; // White color in RGB format
    var $colorLinesArray; // Line color in RGB format
    var $colorBarsArray; // Bar color in RGB format
    var $colorBackgroundArray; // Background color in RGB format
    var $colorStyleArray; // Style color in RGB format
    var $colorTextArray; // Text color in RGB format
    var $colorAboveBarArray; // Number color above the bar in RGB format
    var $colorInsideBarArray; // Number color inside the bar in RGB format
    
    //------------------------------------------------------------------------

   /** 
    * constructor Create an PostGraph object.
    *
    * @param width Width of graph image. If it is not defined, width is set to 400.
    * @param height Height of graph image. If it is not defined, height is set to 300.
    * @returns null
    */
    function PostGraph($width = 400, $height = 300)
    {
         register_shutdown_function(array(&$this, '_PostGraph'));
         
         $this->graphWidth = $width;
         $this->graphHeight = $height;
         $this->textPadding = 3;
         $this->yTicks = 10;
         $this->yNumberFormat = '';
         $this->yValueMode = 3;
         $this->textXOrientation = 'horizontal';
         
         $this->colorWhiteArray = array(255, 255, 255);
         $this->colorLinesArray = array(72, 107, 143);
         $this->colorBarsArray = array(72, 107, 143);
         $this->colorBackgroundArray = array(231, 231, 231);
         $this->colorStyleArray = array(170, 170, 170);
         $this->colorTextArray = array(0, 0, 0);
         $this->colorAboveBarArray = array(0, 0, 0);
         $this->colorInsideBarArray = array(255, 255, 255);

    }
    
    //------------------------------------------------------------------------

   /** 
    * create graph image with functions. Create the image, initialise Colors and
    * area, draw axis, bars and titles.
    *
    * @param null
    * @returns nothing
    */
    function drawImage()
    {
        /* Create initial image */
        $this->img = ImageCreate($this->graphWidth, $this->graphHeight);
        
        $this->initColors();
        
        $this->initArea();
        
        $this->drawYAxe();
        
        $this->drawXAxe();
        
        $this->drawBars();
        
        $this->drawTitles();
    }
    
    //------------------------------------------------------------------------

   /** 
    * initialise all graph colors
    *
    * @param null
    * @returns nothing
    */
    function initColors()
    {
        $this->colorWhite = ImageColorAllocate($this->img, $this->colorWhiteArray[0], $this->colorWhiteArray[1], $this->colorWhiteArray[2]);
        $this->colorLines = ImageColorAllocate($this->img, $this->colorLinesArray[0], $this->colorLinesArray[1], $this->colorLinesArray[2]);
        $this->colorBars = ImageColorAllocate($this->img, $this->colorBarsArray[0], $this->colorBarsArray[1], $this->colorBarsArray[2]);
        $this->colorBackground = ImageColorAllocate($this->img, $this->colorBackgroundArray[0], $this->colorBackgroundArray[1], $this->colorBackgroundArray[2]);
        $this->colorStyle = ImageColorAllocate($this->img, $this->colorStyleArray[0], $this->colorStyleArray[1], $this->colorStyleArray[2]);
        $this->colorText = ImageColorAllocate($this->img, $this->colorTextArray[0], $this->colorTextArray[1], $this->colorTextArray[2]);
        $this->colorAboveBar = ImageColorAllocate($this->img, $this->colorAboveBarArray[0], $this->colorAboveBarArray[1], $this->colorAboveBarArray[2]);
        $this->colorInsideBar = ImageColorAllocate($this->img, $this->colorInsideBarArray[0], $this->colorInsideBarArray[1], $this->colorInsideBarArray[2]);
    }
    
    //------------------------------------------------------------------------

   /**
    * initialise graph area, draw background rectangle and fill with background color
    *
    * @param null
    * @returns nothing
    */
    function initArea()
    {
        $this->posXStart = 55;
        $this->posXEnd = $this->graphWidth - 5;
        $this->posYStart = 35;
        $this->posYEnd = $this->graphHeight - 15 - ($this->maxTextLength*6+15);
        
        ImageFilledRectangle($this->img, $this->posXStart, $this->posYStart, $this->posXEnd , $this->posYEnd, $this->colorBackground);
        ImageRectangle($this->img, $this->posXStart, $this->posYStart, $this->posXEnd , $this->posYEnd, $this->colorLines);
    }

    //------------------------------------------------------------------------

   /**
    * draw x axe with lines and numbers
    *
    * @param null
    * @returns nothing
    */
    function drawXAxe()
    {
        // draw lines
        $startPos = $this->posXStart;
        $step = round(( ($this->posXEnd - $this->posXStart) / $this->countData), 2);
        for($i=0; $i<=$this->countData; $i++) 
        {
            ImageLine($this->img, $startPos, $this->posYEnd-5, $startPos, $this->posYEnd+5, $this->colorLines);
            
            $startPos += $step;
        }
        
        // draw numbers
        $startPos = $this->posXStart;
        foreach($this->data as $key => $value)
        {
            if($this->textXOrientation == 'horizontal')
                ImageString($this->img, 1, $startPos+((($this->posXEnd-$this->posXStart)/$this->countData)/2)-5, $this->posYEnd+11, $key, $this->colorText);
            else
                ImageStringUp($this->img, 2, $startPos+((($this->posXEnd-$this->posXStart)/$this->countData)/2)-5, $this->posYEnd+5+strlen($key)*6, $key, $this->colorText);

            $startPos += $step;
        }
    }
    
    //------------------------------------------------------------------------

   /**
    * draw y axe with lines and numbers
    *
    * @param null
    * @returns nothing
    */
    function drawYAxe()
    {
        // draw lines
        $top = $this->posYStart;
        $step = round((($this->posYEnd-$this->posYStart)/$this->yTicks),2);
        for($i=0; $i<=$this->yTicks; $i++)
        {
            $style = array($this->colorStyle, $this->colorStyle, $this->colorStyle, $this->colorStyle, IMG_COLOR_TRANSPARENT, IMG_COLOR_TRANSPARENT, IMG_COLOR_TRANSPARENT);
            ImageSetStyle($this->img, $style);
            
            ImageLine($this->img, $this->posXStart-5, $top, $this->posXStart+5, $top, $this->colorLines);
            
            $top += $step;
        }
        
        // draw numbers
        $xAxeValue = $this->maxData;
        $top = $this->posYStart;
        for($i=0; $i<=$this->yTicks; $i++) 
        {
            ImageString($this->img, 2, $this->posXStart-12-strlen($xAxeValue)*4, $top-6, $xAxeValue, $this->colorText);
            
            $xAxeValue -= ($this->maxData/$this->yTicks);
            if($xAxeValue < 0.01)
                $xAxeValue = 0;
                
            $top += $step;
        }
    }
    
    //------------------------------------------------------------------------

   /**
    * draw bars with number at top. Bars value is in variable data.
    *
    * @param null
    * @returns nothing
    */
    function drawBars()
    {
        $startPos = $this->posXStart;
        $step = (($this->posXEnd - $this->posXStart)/$this->countData)/2;
        foreach($this->data as $key => $value) 
        {
            $barWidth = (0.75*($this->posXEnd-$this->posXStart)/$this->countData)/2;
            $barHeight = (($this->posYEnd-$this->posYStart)*$value)/$this->maxData;

            ImageFilledRectangle($this->img, $startPos + $step- $barWidth, $this->posYEnd - $barHeight, $startPos + $step + $barWidth, $this->posYEnd, $this->colorBars);
            
            $startX = $startPos + $step - (strlen($value)*3);
            
            if(($barHeight>13 && $this->yValueMode == 1) || $this->yValueMode == 2) 
            {
                $startY = $this->posYEnd - $barHeight;
                ImageString($this->img, 1, $startX, $startY, $value, $this->colorInsideBar);
            } 
            else 
            {
                $startY = $this->posYEnd - $barHeight - 13;
                ImageString($this->img, 1, $startX, $startY, $value, $this->colorAboveBar);
            }
            
            $startPos = round((($this->posXEnd-$this->posXStart)/$this->countData),2) + $startPos;            
        }
    }
    
    //------------------------------------------------------------------------

   /**
    * draw titles of graph. Main title on the top center, verticaly on the left 
    * side and horizontaly on the botton
    *
    * @param null
    * @returns nothing
    */
    function drawTitles()
    {
        ImageString($this->img, 5, $this->graphWidth/2-strlen($this->graphTitle)*4, $this->textPadding, $this->graphTitle, $this->colorText);
        ImageStringUp($this->img, 3, $this->textPadding, $this->graphHeight/2+strlen($this->graphYTitle)*3, $this->graphYTitle, $this->colorText);
        ImageString($this->img, 3, $this->graphWidth/2-strlen($this->graphXTitle)*3, $this->posYEnd+$this->textPadding+($this->maxTextLength*6+15)-10, $this->graphXTitle, $this->colorText);
    }
    
    //------------------------------------------------------------------------

   /**
    * draw graph to the output
    *
    * @param null
    * @returns nothing
    */
    function printImage()
    {
       // header("Content-type: image/jpeg");
        ImagePNG($this->img);

    }

    //------------------------------------------------------------------------

    /**
    * set graph titles defined by user - main, x axis, y axis
    *
    * @param mainTitle Main Title of graph.
    * @param xTitle Title of graph x axe.
    * @param yTitle Title of graph y axe.
    * @returns nothing
    */
    function setGraphTitles($mainTitle, $xTitle, $yTitle)
    {
        $this->graphTitle = $mainTitle;
        $this->graphXTitle = $xTitle;    
        $this->graphYTitle = $yTitle;
    }
    
    //------------------------------------------------------------------------

    /**
    * set all graph colors defined by user
    *
    * @param userColors RGB Color array for 8 colors. Must be defined all.
    * @returns nothing
    */
    function setColors($userColors)
    {
      if($userColors != "" && count($userColors) == 24)
      {
        $this->colorWhiteArray = array(0 => $userColors[0], 1 => $userColors[1], 2 => $userColors[2]);
        $this->colorLinesArray = array(0 => $userColors[3], 1 => $userColors[4], 2 => $userColors[5]);
        $this->colorBarsArray = array(0 => $userColors[6], 1 => $userColors[7], 2 => $userColors[8]);
        $this->colorBackgroundArray = array(0 => $userColors[9], 1 => $userColors[10], 2 => $userColors[11]);
        $this->colorStyleArray = array(0 => $userColors[12], 1 => $userColors[13], 2 => $userColors[14]);
        $this->colorTextArray = array(0 => $userColors[15], 1 => $userColors[16], 2 => $userColors[17]);
        $this->colorAboveBarArray = array(0 => $userColors[18], 1 => $userColors[19], 2 => $userColors[20]);
        $this->colorInsideBarArray = array(0 => $userColors[21], 1 => $userColors[22], 2 => $userColors[23]);
      }
    }

    //------------------------------------------------------------------------

    /**
    * set graph white color defined by user
    *
    * @param userRGB RGB Color array.
    * @returns nothing
    */
    function setWhiteColor($userRGB)
    {
      if($userRGB != "" && count($userRGB) == 3)
        $this->colorWhiteArray = array(0 => $userRGB[0], 1 => $userRGB[1], 2 => $userRGB[2]);
    }

    //------------------------------------------------------------------------

    /**
    * set graph line color defined by user
    *
    * @param userRGB RGB Color array for line like axis.
    * @returns nothing
    */
    function setLinesColor($userRGB)
    {
      if($userRGB != "" && count($userRGB) == 3)
        $this->colorLinesArray = array(0 => $userRGB[0], 1 => $userRGB[1], 2 => $userRGB[2]);
    }

    //------------------------------------------------------------------------

    /**
    * set graph bar color defined by user
    *
    * @param userRGB RGB Color array for bar.
    * @returns nothing
    */
    function setBarsColor($userRGB)
    {
      if($userRGB != "" && count($userRGB) == 3)
        $this->colorBarsArray = array(0 => $userRGB[0], 1 => $userRGB[1], 2 => $userRGB[2]);
    }

    //------------------------------------------------------------------------

    /**
    * set graph background color defined by user
    *
    * @param userRGB RGB Color array for background.
    * @returns nothing
    */
    function setBackgroundColor($userRGB)
    {
      if($userRGB != "" && count($userRGB) == 3)
        $this->colorBackgroundArray = array(0 => $userRGB[0], 1 => $userRGB[1], 2 => $userRGB[2]);
    }

    //------------------------------------------------------------------------

    /**
    * set graph style color defined by user
    *
    * @param userRGB RGB Color array for style.
    * @returns nothing
    */
    function setStyleColor($userRGB)
    {
      if($userRGB != "" && count($userRGB) == 3)
        $this->colorStyleArray = array(0 => $userRGB[0], 1 => $userRGB[1], 2 => $userRGB[2]);
    }

    //------------------------------------------------------------------------

    /**
    * set graph text color defined by user
    *
    * @param userRGB RGB Color array for text.
    * @returns nothing
    */
    function setTextColor($userRGB)
    {
      if($userRGB != "" && count($userRGB) == 3)
        $this->colorTextArray = array(0 => $userRGB[0], 1 => $userRGB[1], 2 => $userRGB[2]);
    }

    //------------------------------------------------------------------------

    /**
    * set graph text above bar color defined by user
    *
    * @param userRGB RGB Color array for text whitch is above bar.
    * @returns nothing
    */
    function setAboveBarColor($userRGB)
    {
      if($userRGB != "" && count($userRGB) == 3)
        $this->colorAboveBarArray = array(0 => $userRGB[0], 1 => $userRGB[1], 2 => $userRGB[2]);
    }

    //------------------------------------------------------------------------

    /**
    * set graph text inside bar color defined by user
    *
    * @param userRGB RGB Color array for text witch is inside bar.
    * @returns nothing
    */
    function setInsideBarColor($userRGB)
    {
      if($userRGB != "" && count($userRGB) == 3)
        $this->colorInsideBarArray = array(0 => $userRGB[0], 1 => $userRGB[1], 2 => $userRGB[2]);
    }

    //------------------------------------------------------------------------

    /**
    * set ticks on y axe
    *
    * @param ticks Set ticks to max value of data. If max value is 0 then ticks
    * is set to 1.Default value is defined.
    * @returns nothing
    */
    function setYTicks($ticks)
    {
        $this->yTicks = $ticks;
        
        if($this->data != null && $this->yNumberFormat == 'integer')
        {
            if($this->yTicks > $this->maxData)
                $this->yTicks = $this->maxData;
        }
            
        if($this->yTicks == 0)
            $this->yTicks = 1;
    }

    //------------------------------------------------------------------------

    /**
    * set number format at y axe
    *
    * @param format If format is set to 'integer' then y axe have integer numbers
    * @returns nothing
    */
    function setYNumberFormat($format)
    {
        $this->yNumberFormat = $format;
    }

    //------------------------------------------------------------------------

    /**
    * set data for post graph
    *
    * @param data Set graph data. Compute data summary, data count, find
    * max value and set number of y ticks.
    * @returns nothing
    */
    function setData($data)
    {
        if(count($data) == 0)
            $data = array('' => 0);
            
        $this->data = $data;
        $this->computeDataSum();
        $this->findMaxValues();
        
        $this->countData = count($data);

        if($this->yTicks > $this->maxData && $this->yNumberFormat == 'integer')
            $this->yTicks = $this->maxData;

        if($this->yTicks == 0)
            $this->yTicks = 1;
    }
    
    //------------------------------------------------------------------------

    /**
    * set orientation of digits at graph x axe. Allowed parameters - 'horizontal', 'vertical'
    *
    * @param orientation Orientation of digits at x axe.
    * @returns nothing
    */
    function setXTextOrientation($orientation)
    {
        $this->textXOrientation = $orientation;
    }
    
    //------------------------------------------------------------------------

    /**
    * compute data summary
    *
    * @param null
    * @returns nothing
    */
    function computeDataSum()
    {
        if(!is_array($this->data))
            return;
            
        $this->dataSum = 0;
        
        foreach($this->data as $key => $value) 
            $this->dataSum += $value;
    }

    //------------------------------------------------------------------------

    /**
    * find maximum value of data and do special round with him
    *
    * @param null
    * @returns null of data not exists
    */
    function findMaxValues()
    {
        if(!is_array($this->data))
            return;
            
        $this->maxData = 0;
        $this->maxTextLength = 0;
        foreach($this->data as $key => $value)
        {
            if($this->maxData < $value)
                $this->maxData = $value;
                
            $length = strlen($key);
            if($this->maxTextLength < $length)
                $this->maxTextLength = $length;
        }

        $this->maxData = $this->specialRound($this->maxData);

        if($this->maxData == 0)
            $this->maxData = 1;
    }
    
    //------------------------------------------------------------------------

    /**
    * do special round on max value of data. Round at first digit for 2 digit
    * int number. Round at second digit for bigger int number. 
    *
    * @param number Float or int number
    * @returns number Special round number
    */
    function specialRound($number) 
    {
        if(strlen(ceil($number)) < 2) // check if number is smaller then 10 
        {
            if( (strpos($number, ".")) !== false ) // float number ?
            {
              if(substr($number, 0, 1) == "0" || substr($number, 0, 1) == ".") $dot_place = 0;
              else $dot_place = 1;

              $length = strlen($number);
              $undot_number = str_replace(".", "", $number);
              if($undot_number >= 100)
              {
                $undot_number = (int)$undot_number;
                $lenght_i = strlen($undot_number);
                $undot_number = substr($undot_number, 0, 2) + 1;
                if(strlen($undot_number) == 3 && $number >= 0.99) // more then 2 digit number ?
                  $dot_place = 1;
                if($dot_place == 0)
                  $number = "0.";
                else $number = substr($undot_number,0,$dot_place).".";
                if( ($length-$lenght_i) > 2)
                {
                  if(strlen($undot_number) == 3)
                    $lenght_i++;
                  $number = str_pad($number, ($length - $lenght_i), '0');
                }
                $number .= substr($undot_number,$dot_place);
              }
              else if($undot_number >= 10) // bigger float number. Increment first not 0 digit
              {
                $undot_number = (int)$undot_number;
                $undot_number = substr($undot_number, 0, 1) + 1;
                $div_flag = false;
                if(strlen($undot_number) == 2 && $number >= 0.9 && $number < 1) // more then 1 digit number ?
                {
                  $dot_place = 1;
                  $div_flag = true;
                }
                if($dot_place == 0)
                  $number = "0.";
                else $number = substr($undot_number,0,$dot_place);
                
                if( $length > 4 )
                {
                  if(strlen($undot_number) == 2)
                    $length--;
                  $number = str_pad($number, ($length - 2), '0'); // add some 0
                }

                $number .= substr($undot_number,$dot_place);
                if( $div_flag ) // if we have 100 then change dot place
                  $number /= 10;
              }
            }
            return $number;
        } 
        else // big number
        {
            $number = ceil($number);
            $length = strlen($number);

            if(substr($number, 1) == 0)
              return $number;
            
            if($length < 3) // if number is smaller then 3 digit increment it 1 digit, other digits set to 0
            {
              $firstDigit = substr($number, 0, 1) + 1;
              $nextDigits = str_pad("", $length - 1, '0');
              $number = $firstDigit.$nextDigits;
            }
            else // else if number is bigger then take first and second digit and as integer increment it, other digits set to 0
            {
              $firstAndSecondDigit = substr($number, 0, 2) + 1;
              $nextDigits = str_pad("", $length - 2, '0');
              $number = $firstAndSecondDigit.$nextDigits;
            }

            return $number; // finally set the y axe maximum
        }
    }
    
    //------------------------------------------------------------------------

    /**
    * descruction of graph object
    *
    * @param null
    * @returns nothing
    */
    function _PostGraph () 
    {
        //ImageDestroy($this->img);
        return;
    }

    //------------------------------------------------------------------------
}

?>
