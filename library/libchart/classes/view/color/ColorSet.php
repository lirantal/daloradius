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
     * A set of colors, used for drawing series of data.
     *
     * @author Jean-Marc Trémeaux (jm.tremeaux at gmail.com)
     * Created on 26 july 2007
     */
    class ColorSet {
        public $colorList;
        public $shadowColorList;
    
        /**
         * ColorSet constructor.
         *
         * @param $shadowFactor Shadow factor
         * @param $colorArray Colors as an array
         */
        public function ColorSet($colorList, $shadowFactor) {
            $this->colorList = $colorList;
            $this->shadowColorList = array();

            // Generate the shadow color set
            foreach ($colorList as $color) {
                $shadowColor = $color->getShadowColor($shadowFactor);

                array_push($this->shadowColorList, $shadowColor);
            }
        }
        
        /**
         * Reset the iterator over the collections of colors.
         */
        public function reset() {
            reset($this->colorList);
            reset($this->shadowColorList);
        }

        /**
         * Iterate over the colors and shadow colors. When we go after the last one, loop over.
         *
         */
        public function next() {
            $value = next($this->colorList);
            next($this->shadowColorList);
            
            // When we go after the last value, loop over.
            if ($value == FALSE) {
                $this->reset();
            }
        }

        /**
         * Returns the current color.
         *
         * @return Current color
         */
        public function currentColor() {
            return current($this->colorList);
        }

        /**
         * Returns the current shadow color.
         *
         * @return Current shadow color
         */
        public function currentShadowColor() {
            return current($this->shadowColorList);
        }
    }
?>