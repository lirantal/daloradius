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
	* Graphic primitives, extends GD with chart related primitives
	*
	* @author   Jean-Marc Trémeaux (jm.tremeaux at gmail.com)
	*/

	class Primitive
	{
		/**
		* Creates a new primitive object
		*
		* @access	public
		* @param	resource	GD image resource
		*/
		
		function Primitive($img)
		{
			$this->img = $img;
		}
		
		/**
		* Draws a straight line
		*
		* @access	public
		* @param	integer		line start (X)
		* @param	integer		line start (Y)
		* @param	integer		line end (X)
		* @param	integer		line end (Y)
		* @param	Color		line color
		*/
		
		function line($x1, $y1, $x2, $y2, $color, $width = 1)
		{
			imagefilledpolygon($this->img, array($x1, $y1 - $width / 2, $x1, $y1 + $width / 2, $x2, $y2 + $width / 2, $x2, $y2 - $width / 2), 4, $color->getColor($this->img));
//			imageline($this->img, $x1, $y1, $x2, $y2, $color->getColor($this->img));
		}
	}
?>
