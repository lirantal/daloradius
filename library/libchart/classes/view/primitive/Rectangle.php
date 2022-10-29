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
     * A rectangle identified by the top-left and the bottom-right corners.
     *
     * @author Jean-Marc Trémeaux (jm.tremeaux at gmail.com)
     * @Created on 27 july 2007
     */
    class Rectangle {
        /**
         * Top left X.
         */
        public $x1;

        /**
         * Top left Y.
         */
        public $y1;
        
        /**
         * Bottom right X.
         */
        public $x2;
        
        /**
         * Bottom right Y.
         */
        public $y2;
    
        /**
         * Constructor of Rectangle.
         *
         * @param x1 Left edge coordinate
         * @param y1 Upper edge coordinate
         * @param x2 Right edge coordinate
         * @param y2 Bottom edge coordinate
         */
        public function Rectangle($x1, $y1, $x2, $y2) {
            $this->x1 = $x1;
            $this->y1 = $y1;
            $this->x2 = $x2;
            $this->y2 = $y2;
        }
        
        /**
         * Apply a padding and returns the resulting rectangle.
         * The result is an enlarged rectangle.
         *
         * @return Padded rectangle
         */
        public function getPaddedRectangle($padding) {
            $rectangle = new Rectangle(
                    $this->x1 + $padding->left,
                    $this->y1 + $padding->top,
                    $this->x2 - $padding->right,
                    $this->y2 - $padding->bottom
            );
            
            //echo "(" . $this->x1 . "," . $this->y1 . ") (" . $this->x2 . "," . $this->y2 . ")<br>";
            return $rectangle;
        }
    }
?>