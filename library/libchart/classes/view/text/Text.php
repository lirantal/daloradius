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
     * Text drawing helper
     *
     * @author Jean-Marc Trémeaux (jm.tremeaux at gmail.com)
     */
    class Text {
        public $HORIZONTAL_LEFT_ALIGN = 1;
        public $HORIZONTAL_CENTER_ALIGN = 2;
        public $HORIZONTAL_RIGHT_ALIGN = 4;
        public $VERTICAL_TOP_ALIGN = 8;
        public $VERTICAL_CENTER_ALIGN = 16;
        public $VERTICAL_BOTTOM_ALIGN = 32;

        /**
         * Creates a new text drawing helper.
         */
        public function Text() {
            $baseDir = dirname(__FILE__) . "/../../../";
        
            // Free low-res fonts based on Bitstream Vera <http://dejavu.sourceforge.net/wiki/>
            $this->fontCondensed = $baseDir . "fonts/DejaVuSansCondensed.ttf";
            $this->fontCondensedBold = $baseDir . "fonts/DejaVuSansCondensed-Bold.ttf";
        }

        /**
         * Print text.
         *
         * @param Image GD image
         * @param integer text coordinate (x)
         * @param integer text coordinate (y)
         * @param Color text color
         * @param string text value
         * @param string font file name
         * @param bitfield text alignment
         */
        public function printText($img, $px, $py, $color, $text, $fontFileName, $align = 0) {
            if (!($align & $this->HORIZONTAL_CENTER_ALIGN) && !($align & $this->HORIZONTAL_RIGHT_ALIGN)) {
                $align |= $this->HORIZONTAL_LEFT_ALIGN;
            }

            if (!($align & $this->VERTICAL_CENTER_ALIGN) && !($align & $this->VERTICAL_BOTTOM_ALIGN)) {
                $align |= $this->VERTICAL_TOP_ALIGN;
            }

            $fontSize = 8;
            $lineSpacing = 1;

             list ($llx, $lly, $lrx, $lry, $urx, $ury, $ulx, $uly) = imageftbbox($fontSize, 0, $fontFileName, $text, array("linespacing" => $lineSpacing));

            $textWidth = $lrx - $llx;
            $textHeight = $lry - $ury;

            $angle = 0;

            if ($align & $this->HORIZONTAL_CENTER_ALIGN) {
                $px -= $textWidth / 2;
            }

            if ($align & $this->HORIZONTAL_RIGHT_ALIGN) {
                $px -= $textWidth;
            }

            if ($align & $this->VERTICAL_CENTER_ALIGN) {
                $py += $textHeight / 2;
            }

            if ($align & $this->VERTICAL_TOP_ALIGN) {
                $py += $textHeight;
            }

            imagettftext($img, $fontSize, $angle, $px, $py, $color->getColor($img), $fontFileName, $text);
        }
        
        /**
         * Print text centered horizontally on the image.
         *
         * @param Image GD image
         * @param integer text coordinate (y)
         * @param Color text color
         * @param string text value
         * @param string font file name
         */
        public function printCentered($img, $py, $color, $text, $fontFileName) {
            $this->printText($img, imagesx($img) / 2, $py, $color, $text, $fontFileName, $this->HORIZONTAL_CENTER_ALIGN | $this->VERTICAL_CENTER_ALIGN);
        }

        /**
         * Print text in diagonal.
         *
         * @param Image GD image
         * @param integer text coordinate (x)
         * @param integer text coordinate (y)
         * @param Color text color
         * @param string text value
         */
        public function printDiagonal($img, $px, $py, $color, $text) {
            $fontSize = 8;
            $fontFileName = $this->fontCondensed;

            $lineSpacing = 1;

             list ($lx, $ly, $rx, $ry) = imageftbbox($fontSize, 0, $fontFileName, $text, array("linespacing" => $lineSpacing));
            $textWidth = $rx - $lx;

            $angle = -45;

            imagettftext($img, $fontSize, $angle, $px, $py, $color->getColor($img), $fontFileName, $text);
        }
    }
?>