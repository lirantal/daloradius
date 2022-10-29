<?php
    /* Libchart - PHP chart library
     * Copyright (C) 2005-2011 Jean-Marc Trémeaux (jm.tremeaux at gmail.com)
     * 
     * This program is free software: you can redistribute it and/or modify
     * it under the terms of the GNU General Public License as published by
     * the Free Software Foundation, either version 3 of the License, or
     * (at your option) any later version.
     * 
     * This program is distributed in the hope that it will be useful,
     * but WITHOUT ANY WARRANTY; without even the implied warranty of
     * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
     * GNU General Public License for more details.
     *
     * You should have received a copy of the GNU General Public License
     * along with this program.  If not, see <http://www.gnu.org/licenses/>.
     * 
     */
    
    /**
     * Graphic primitives, extends GD with chart related primitives.
     *
     * @author Jean-Marc Trémeaux (jm.tremeaux at gmail.com)
     */
    class Primitive {
        private $img;
    
        /**
         * Creates a new primitive object
         *
         * @param    resource    GD image resource
         */
        public function Primitive($img) {
            $this->img = $img;
        }
        
        /**
         * Draws a straight line.
         *
         * @param integer line start (X)
         * @param integer line start (Y)
         * @param integer line end (X)
         * @param integer line end (Y)
         * @param Color line color
         */
        public function line($x1, $y1, $x2, $y2, $color, $width = 1) {
            imagefilledpolygon($this->img, array($x1, $y1 - $width / 2, $x1, $y1 + $width / 2, $x2, $y2 + $width / 2, $x2, $y2 - $width / 2), 4, $color->getColor($this->img));
            // imageline($this->img, $x1, $y1, $x2, $y2, $color->getColor($this->img));
        }

        /**
         * Draw a filled gray box with thick borders and darker corners.
         *
         * @param integer top left coordinate (x)
         * @param integer top left coordinate (y)
         * @param integer bottom right coordinate (x)
         * @param integer bottom right coordinate (y)
         * @param Color edge color
         * @param Color corner color
         */
        public function outlinedBox($x1, $y1, $x2, $y2, $color0, $color1) {
            imagefilledrectangle($this->img, $x1, $y1, $x2, $y2, $color0->getColor($this->img));
            imagerectangle($this->img, $x1, $y1, $x1 + 1, $y1 + 1, $color1->getColor($this->img));
            imagerectangle($this->img, $x2 - 1, $y1, $x2, $y1 + 1, $color1->getColor($this->img));
            imagerectangle($this->img, $x1, $y2 - 1, $x1 + 1, $y2, $color1->getColor($this->img));
            imagerectangle($this->img, $x2 - 1, $y2 - 1, $x2, $y2, $color1->getColor($this->img));
        }

    }
?>