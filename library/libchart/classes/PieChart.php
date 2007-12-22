<?php
	/** Libchart - PHP chart library
	*	
	* Copyright (C) 2005-2006 Jean-Marc Trémeaux (jm.tremeaux at gmail.com)
	* 	
	* This library is free software; you can redistribute it and/or
	* modify it under the terms of the GNU Lesser General Public
	* License as published by the Free Software Foundation; either
	* version 2.1 of the License, or (at your option) any later version.
	* 
	* This library is distributed in the hope that it will be useful,
	* but WITHOUT ANY WARRANTY; without even the implied warranty of
	* MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the GNU
	* Lesser General Public License for more details.
	* 
	* You should have received a copy of the GNU Lesser General Public
	* License along with this library; if not, write to the Free Software
	* Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA  02110-1301  USA
	* 
	*/
	
	/**
	* Pie chart
	*
	* @author   Jean-Marc Trémeaux (jm.tremeaux at gmail.com)
	*/

	class PieChart extends Chart
	{
		/**
		* Creates a new pie chart
		*
		* @access	public
    		* @param	integer		width of the image
    		* @param	integer		height of the image
		*/
		
		function PieChart($width = 600, $height = 250)
		{
			parent::Chart($width, $height);

			$this->setMargin(5);
			$this->setLabelMarginLeft(30);
			$this->setLabelMarginRight(30);
			$this->setLabelMarginTop(50);
			$this->setLabelMarginBottom(30);
			$this->setLabelMarginCenter(20);

			$this->setPieRatio(0.55);

			$this->labelBoxWidth = 15;
			$this->labelBoxHeight = 15;
		}

		/**
		* Set the ratio of the pie image over the legend
		*
		* @access	public
		* @param	double		ratio (value between 0 and 1)
		*/
		
		function setPieRatio($pieRatio)
		{
			$this->pieRatio = $pieRatio;
		}

		/**
		* Compare two sampling point values, order from biggest to lowest value
		*
		* @access	private
		* @param	double		first value
		* @param	double		second value
		* @return	integer		result of the comparison
		*/
		
		function sortPie($v1, $v2)
		{
			return $v1[0] == $v2[0] ? 0 :
				$v1[0] > $v2[0] ? -1 :
				1;
		}
		
		/**
		* Compute pie values in percentage and sort them
		*
		* @access	private
		*/
		
		function computePercent()
		{
			$this->total = 0;
			$this->percent = array();

			foreach($this->point as $point)
				$this->total += $point->getY();

			foreach($this->point as $point)
			{
				$percent = $this->total == 0 ? 0 : 100 * $point->getY() / $this->total;

				array_push($this->percent, array($percent, $point));
			}

			usort($this->percent, array("PieChart", "sortPie"));
		}

		/**
		* Set the margin between the pie image and the legend
		*
		* @access	public
		* @param	integer		margin value in pixels
		*/
		
		function setLabelMarginCenter($labelMarginCenter)
		{
			$this->labelMarginCenter = $labelMarginCenter;
		}

		/**
		* Draw a gray box with nice borders
		*
		* @access	private
		* @param	integer		top left coordinate (x)
		* @param	integer		top left coordinate (y)
		* @param	integer		bottom right coordinate (x)
		* @param	integer		bottom right coordinate (y)
		*/
		
		function outlinedBox($x1, $y1, $x2, $y2)
		{
			imagefilledrectangle($this->img, $x1, $y1, $x2, $y2, $this->axisColor1->getColor($this->img));
			imagerectangle($this->img, $x1, $y1, $x1 + 1, $y1 + 1, $this->axisColor2->getColor($this->img));
			imagerectangle($this->img, $x2 - 1, $y1, $x2, $y1 + 1, $this->axisColor2->getColor($this->img));
			imagerectangle($this->img, $x1, $y2 - 1, $x1 + 1, $y2, $this->axisColor2->getColor($this->img));
			imagerectangle($this->img, $x2 - 1, $y2 - 1, $x2, $y2, $this->axisColor2->getColor($this->img));
		}

		/**
		* Compute image layout
		*
		* @access	private
		*/
		
		function computeLabelMargin()
		{
			$graphWidth = $this->width - $this->margin * 2 - $this->labelMarginLeft - $this->labelMarginCenter - $this->labelMarginRight;
			
			$this->pieTLX = $this->margin + $this->labelMarginLeft;
			$this->pieTLY = $this->margin + $this->labelMarginTop;
			$this->pieBRX = $this->pieTLX + $graphWidth * $this->pieRatio;
			$this->pieBRY = $this->height - $this->margin - $this->labelMarginBottom;
			
			$this->pieCenterX = $this->pieTLX + ($this->pieBRX - $this->pieTLX) / 2;
			$this->pieCenterY = $this->pieTLY + ($this->pieBRY - $this->pieTLY) / 2;
			
			$this->pieWidth = round(($this->pieBRX - $this->pieTLX) * 4 / 5);
			$this->pieHeight = round(($this->pieBRY - $this->pieTLY) * 3.7 / 5);
			$this->pieDepth = round($this->pieWidth * 0.05);

			$this->labelTLX = $this->pieBRX + $this->labelMarginCenter;
			$this->labelTLY = $this->pieTLY;
			$this->labelBRX = $this->pieTLX + $this->labelMarginCenter + $graphWidth;
			$this->labelBRY = $this->pieBRY;

		}

		/**
		* Creates the pie chart image
		*
		* @access	private
		*/
		
		function createImage()
		{
			parent::createImage();

			$pieColors = array(
				array(2, 78, 0),
				array(148, 170, 36),
				array(233, 191, 49),
				array(240, 127, 41),
				array(243, 63, 34),
				array(190, 71, 47),
				array(135, 81, 60),
				array(128, 78, 162),
				array(121, 75, 255),
				array(142, 165, 250),
				array(162, 254, 239),
				array(137, 240, 166),
				array(104, 221, 71),
				array(98, 174, 35),
				array(93, 129, 1)
			);

			$this->pieColor = array();
			$this->pieShadowColor = array();
			$shadowFactor = 0.5;

			foreach($pieColors as $colorRGB)
			{
				list($red, $green, $blue) = $colorRGB;

				$color = new Color($red, $green, $blue);
				$shadowColor = new Color($red * $shadowFactor, $green * $shadowFactor, $blue * $shadowFactor);

				array_push($this->pieColor, $color);
				array_push($this->pieShadowColor, $shadowColor);
			}

			$this->axisColor1 = new Color(201, 201, 201);
			$this->axisColor2 = new Color(158, 158, 158);

			$this->aquaColor1 = new Color(242, 242, 242);
			$this->aquaColor2 = new Color(231, 231, 231);
			$this->aquaColor3 = new Color(239, 239, 239);
			$this->aquaColor4 = new Color(253, 253, 253);

			// Legend box

			$this->outlinedBox($this->pieTLX, $this->pieTLY, $this->pieBRX, $this->pieBRY);

			// Aqua-like background

			$aquaColor = Array($this->aquaColor1, $this->aquaColor2, $this->aquaColor3, $this->aquaColor4);

			for($i = $this->pieTLY + 2; $i < $this->pieBRY - 1; $i++)
			{
				$color = $aquaColor[($i + 3) % 4];
				$this->primitive->line($this->pieTLX + 2, $i, $this->pieBRX - 2, $i, $color);
			}
		}

		/**
		* Print legend
		*
		* @access	private
		*/
		
		function printLabel()
		{
			$i = 0;

			$boxX1 = $this->labelTLX + $this->margin;
			$boxX2 = $boxX1 + $this->labelBoxWidth;

			foreach($this->percent as $a)
			{
				list($percent, $point) = $a;
				$legend = $point->getX();
				
				$color = $this->pieColor[$i % count($this->pieColor)];

				$boxY1 = $this->labelTLY + $this->margin + $i * ($this->labelBoxHeight + $this->margin);
				$boxY2 = $boxY1 + $this->labelBoxHeight;

				$this->outlinedBox($boxX1, $boxY1, $boxX2, $boxY2);
				imagefilledrectangle($this->img, $boxX1 + 2, $boxY1 + 2, $boxX2 - 2, $boxY2 - 2, $color->getColor($this->img));

				$this->text->printText($this->img, $boxX2 + $this->margin, $boxY1 + $this->labelBoxHeight / 2, $this->textColor, $legend, $this->text->fontCondensed, $this->text->VERTICAL_CENTER_ALIGN);

				$i++;
			}
		}

		/**
		* Draw a 2D disc
		*
		* @access	private
		* @param	integer		center coordinate (y)
		* @param	array		colors for each portion
		* @param	bitfield	drawing mode
		*/
		
		function drawDisc($cy, $colorArray, $mode)
		{
			$i = 0;
			$angle1 = 0;
			$percentTotal = 0;

			foreach($this->percent as $a)
			{
				list($percent, $point) = $a;

				$color = $colorArray[$i % count($colorArray)];

				$percentTotal += $percent;
				$angle2 = $percentTotal * 360 / 100;

				imagefilledarc($this->img, $this->pieCenterX, $cy, $this->pieWidth, $this->pieHeight, $angle1, $angle2, $color->getColor($this->img), $mode);

				$angle1 = $angle2;

				$i++;
			}
		}

		/**
		* Print the percentage text
		*
		* @access	private
		*/
		
		function drawPercent()
		{
			$angle1 = 0;
			$percentTotal = 0;

			foreach($this->percent as $a)
			{
				list($percent, $point) = $a;

				// If value is null, don't print percentage
				
				if($percent <= 0)
					continue;

				$percentTotal += $percent;
				$angle2 = $percentTotal * 2 * M_PI / 100;

				$angle = $angle1 + ($angle2 - $angle1) / 2;
				$text = number_format($percent) . "%";

				$x = cos($angle) * ($this->pieWidth + 35) / 2 + $this->pieCenterX;
				$y = sin($angle) * ($this->pieHeight + 35) / 2 + $this->pieCenterY;

				$this->text->printText($this->img, $x, $y, $this->textColor, $text, $this->text->fontCondensed, $this->text->HORIZONTAL_CENTER_ALIGN | $this->text->VERTICAL_CENTER_ALIGN);

				$angle1 = $angle2;
			}
		}

		/**
		* Print the pie chart
		*
		* @access	private
		*/
		
		function printPie()
		{
			// Silhouette

			for ($cy = $this->pieCenterY + $this->pieDepth / 2; $cy >= $this->pieCenterY - $this->pieDepth / 2; $cy--)
				$this->drawDisc($cy, $this->pieShadowColor, IMG_ARC_EDGED);


			// Top

			$this->drawDisc($this->pieCenterY - $this->pieDepth / 2, $this->pieColor, IMG_ARC_PIE);

			// Top Outline

			$this->drawPercent();
		}

		/**
		* Render the chart image
		*
		* @access	public
		* @param	string		name of the file to render the image to (optional)
		*/
		
		function render($fileName = null)
		{
			$this->computeLabelMargin();
			$this->computePercent();
			$this->createImage();
			$this->printLogo();
			$this->printTitle();
			$this->printPie();
			$this->printLabel();

			if(isset($fileName))
				imagepng($this->img, $fileName);
			else
				imagepng($this->img);
		}
	}
?>
