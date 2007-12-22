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
	* Sampling point
	*
	* @author   Jean-Marc Trémeaux (jm.tremeaux at gmail.com)
	*/

	class Point
	{
		/**
		* Creates a new sampling point of coordinates (x, y)
		*
		* @access	public
    		* @param	integer		x coordinate (label)
    		* @param	integer		y coordinate (value)
		*/
		
		function Point($x, $y)
		{
			$this->x = $x;
			$this->y = $y;
		}

		/**
		* Gets the x coordinate (label)
		*
		* @access	public
    		* @return	integer		x coordinate (label)
		*/
		
		function getX()
		{
			return $this->x;
		}

		/**
		* Gets the y coordinate (value)
		*
		* @access	public
    		* @return	integer		y coordinate (value)
		*/
		
		function getY()
		{
			return $this->y;
		}
	}
?>
