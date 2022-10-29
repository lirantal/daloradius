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
     * The plot holds graphical attributes, and is responsible for computing the layout of the graph.
     * The layout is quite simple right now, with 4 areas laid out like that:
     * (of course this is subject to change in the future).
     *
     * output area------------------------------------------------|
     * |  (outer padding)                                         |
     * |  image area--------------------------------------------| |
     * |  | (title padding)                                     | |
     * |  | title area----------------------------------------| | |
     * |  | |-------------------------------------------------| | |
     * |  |                                                     | |
     * |  | (graph padding)              (caption padding)      | |
     * |  | graph area----------------|  caption area---------| | |
     * |  | |                         |  |                    | | |
     * |  | |                         |  |                    | | |
     * |  | |                         |  |                    | | |
     * |  | |                         |  |                    | | |
     * |  | |                         |  |                    | | |
     * |  | |-------------------------|  |--------------------| | |
     * |  |                                                     | |
     * |  |-----------------------------------------------------| |
     * |                                                          |
     * |----------------------------------------------------------|
     *
     * All area dimensions are known in advance , and the optional logo is drawn in absolute coordinates.
     *
     * @author Jean-Marc Trémeaux (jm.tremeaux at gmail.com)
     * Created on 27 july 2007
     */
    class Plot {
        // Style properties
        protected $title;
        
        /**
         * Location of the logo. Can be overriden to your personalized logo.
         */
        protected $logoFileName;
        
        /**
         * Outer area, whose dimension is the same as the PNG returned.
         */
        protected $outputArea;
        
        /**
         * Outer padding surrounding the whole image, everything outside is blank.
         */
        protected $outerPadding;
        
        /**
         * Coordinates of the area inside the outer padding.
         */
        protected $imageArea;
        
        /**
         * Fixed title height in pixels.
         */
        protected $titleHeight;
        
        /**
         * Padding of the title area.
         */
        protected $titlePadding;
        
        /**
         *  Coordinates of the title area.
         */
        protected $titleArea;
        
        /**
         * True if the plot has a caption.
         */
        protected $hasCaption;
        
        /**
         * Ratio of graph/caption in width.
         */
        protected $graphCaptionRatio;
        
        /**
         * Padding of the graph area.
         */
        protected $graphPadding;
        
        /**
         * Coordinates of the graph area.
         */
        protected $graphArea;
        
        /**
         * Padding of the caption area.
         */
        protected $captionPadding;
        
        /**
         * Coordinates of the caption area.
         */
        protected $captionArea;
        
        /**
         * Text writer.
         */
        protected $text;
        
        /**
         * Color palette.
         */
        protected $palette;
        
        /**
         * GD image
         */
        protected $img;

        /**
         * Drawing primitives
         */
        protected $primitive;

        protected $backGroundColor;
        protected $textColor;

        /**
         * Constructor of Plot.
         *
         * @param integer width of the image
         * @param integer height of the image
         */
        public function Plot($width, $height) {
            $this->width = $width;
            $this->height = $height;

            $this->text = new Text();
            $this->palette = new Palette();
            
            // Default layout
            $this->outputArea = new Rectangle(0, 0, $width - 1, $height - 1);
            $this->outerPadding = new Padding(5);
            $this->titleHeight = 26;
            $this->titlePadding = new Padding(5);
            $this->hasCaption = false;
            $this->graphCaptionRatio = 0.50;
            $this->graphPadding = new Padding(50);
            $this->captionPadding = new Padding(15);
        }

        /**
         * Compute the area inside the outer padding (outside is white).
         */
        private function computeImageArea() {
            $this->imageArea = $this->outputArea->getPaddedRectangle($this->outerPadding);
        }
        
        /**
         * Compute the title area.
         */
        private function computeTitleArea() {
            $titleUnpaddedBottom = $this->imageArea->y1 + $this->titleHeight + $this->titlePadding->top + $this->titlePadding->bottom;
            $titleArea = new Rectangle(
                    $this->imageArea->x1,
                    $this->imageArea->y1,
                    $this->imageArea->x2,
                    $titleUnpaddedBottom - 1
            );
            $this->titleArea = $titleArea->getPaddedRectangle($this->titlePadding);
        }
        
        /**
         * Compute the graph area.
         */
        private function computeGraphArea() {
            $titleUnpaddedBottom = $this->imageArea->y1 + $this->titleHeight + $this->titlePadding->top + $this->titlePadding->bottom;
            $graphArea = null;
            if ($this->hasCaption) {
                $graphUnpaddedRight = $this->imageArea->x1 + ($this->imageArea->x2 - $this->imageArea->x1) * $this->graphCaptionRatio
                        + $this->graphPadding->left + $this->graphPadding->right;
                $graphArea = new Rectangle(
                        $this->imageArea->x1,
                        $titleUnpaddedBottom,
                        $graphUnpaddedRight - 1,
                        $this->imageArea->y2
                );
            } else {
                $graphArea = new Rectangle(
                        $this->imageArea->x1,
                        $titleUnpaddedBottom,
                        $this->imageArea->x2,
                        $this->imageArea->y2
                );
            }
            $this->graphArea = $graphArea->getPaddedRectangle($this->graphPadding);
        }
        
        /**
         * Compute the caption area.
         */
        private function computeCaptionArea() {
            $graphUnpaddedRight = $this->imageArea->x1 + ($this->imageArea->x2 - $this->imageArea->x1) * $this->graphCaptionRatio
                    + $this->graphPadding->left + $this->graphPadding->right;
            $titleUnpaddedBottom = $this->imageArea->y1 + $this->titleHeight + $this->titlePadding->top + $this->titlePadding->bottom;
            $captionArea = new Rectangle(
                    $graphUnpaddedRight,
                    $titleUnpaddedBottom,
                    $this->imageArea->x2,
                    $this->imageArea->y2
            );
            $this->captionArea = $captionArea->getPaddedRectangle($this->captionPadding);
        }
        
        /**
         * Compute the layout of all areas of the graph.
         */
        public function computeLayout() {
            $this->computeImageArea();
            $this->computeTitleArea();
            $this->computeGraphArea();
            if ($this->hasCaption) {
                $this->computeCaptionArea();
            }
        }
        
        /**
         * Creates and initialize the image.
         */
        public function createImage() {
            $this->img = imagecreatetruecolor($this->width, $this->height);
            
            $this->primitive = new Primitive($this->img);

            $this->backGroundColor = new Color(255, 255, 255);
            $this->textColor = new Color(0, 0, 0);

            // White background
            imagefilledrectangle($this->img, 0, 0, $this->width - 1, $this->height - 1, $this->backGroundColor->getColor($this->img));
            
            //imagerectangle($this->img, $this->imageArea->x1, $this->imageArea->y1, $this->imageArea->x2, $this->imageArea->y2, $this->palette->red->getColor($this->img));
        }

        /**
         * Print the title to the image.
         */
        public function printTitle() {
            $yCenter = $this->titleArea->y1 + ($this->titleArea->y2 - $this->titleArea->y1) / 2;
            $this->text->printCentered($this->img, $yCenter, $this->textColor, $this->title, $this->text->fontCondensedBold);
        }

        /**
         * Print the logo image to the image.
         */
        public function printLogo() {
            @$logoImage = imageCreateFromPNG($this->logoFileName);

            if ($logoImage) {
                imagecopymerge($this->img, $logoImage, 2 * $this->outerPadding->left, $this->outerPadding->top, 0, 0, imagesx($logoImage), imagesy($logoImage), 100);
            }
        }

        /**
         * Renders to a file or to standard output.
         *
         * @param fileName File name (optional)
         */
        public function render($fileName) {
            if (isset($fileName)) {
                imagepng($this->img, $fileName);
            } else {
                imagepng($this->img);
            }
        }

        /**
         * Sets the title.
         *
         * @param string New title
         */
        public function setTitle($title) {
            $this->title = $title;
        }

        /**
         * Sets the logo image file name.
         *
         * @param string New logo image file name
         */
        public function setLogoFileName($logoFileName) {
            $this->logoFileName = $logoFileName;
        }

        /**
         * Return the GD image.
         *
         * @return GD Image
         */
        public function getImg() {
            return $this->img;
        }

        /**
         * Return the palette.
         *
         * @return palette
         */
        public function getPalette() {
            return $this->palette;
        }

        /**
         * Return the text.
         *
         * @return text
         */
        public function getText() {
            return $this->text;
        }

        /**
         * Return the primitive.
         *
         * @return primitive
         */
        public function getPrimitive() {
            return $this->primitive;
        }

        /**
         * Return the outer padding.
         *
         * @param integer Outer padding value in pixels
         */
        public function getOuterPadding() {
            return $outerPadding;
        }

        /**
         * Set the outer padding.
         *
         * @param integer Outer padding value in pixels
         */
        public function setOuterPadding($outerPadding) {
            $this->outerPadding = $outerPadding;
        }

        /**
         * Return the title height.
         *
         * @param integer title height
         */
        public function setTitleHeight($titleHeight) {
            $this->titleHeight = $titleHeight;
        }

        /**
         * Return the title padding.
         *
         * @param integer title padding
         */
        public function setTitlePadding($titlePadding) {
            $this->titlePadding = $titlePadding;
        }

        /**
         * Return the graph padding.
         *
         * @param integer graph padding
         */
        public function setGraphPadding($graphPadding) {
            $this->graphPadding = $graphPadding;
        }

        /**
         * Set if the graph has a caption.
         *
         * @param boolean graph has a caption
         */
        public function setHasCaption($hasCaption) {
            $this->hasCaption = $hasCaption;
        }

        /**
         * Set the caption padding.
         *
         * @param integer caption padding
         */
        public function setCaptionPadding($captionPadding) {
            $this->captionPadding = $captionPadding;
        }

        /**
         * Set the graph/caption ratio.
         *
         * @param integer caption padding
         */
        public function setGraphCaptionRatio($graphCaptionRatio) {
            $this->graphCaptionRatio = $graphCaptionRatio;
        }

        /**
         * Return the graph area.
         *
         * @return graph area
         */
        public function getGraphArea() {
            return $this->graphArea;
        }

        /**
         * Return the caption area.
         *
         * @return caption area
         */
        public function getCaptionArea() {
            return $this->captionArea;
        }

        /**
         * Return the text color.
         *
         * @return text color
         */
        public function getTextColor() {
            return $this->textColor;
        }
    }
?>