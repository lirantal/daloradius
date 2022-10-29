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
     * Pie chart.
     *
     * @author Jean-Marc Trémeaux (jm.tremeaux at gmail.com)
     */
    class PieChart extends Chart {
        protected $pieCenterX;
        protected $pieCenterY;
    
        /**
         * Constructor of a pie chart.
         *
         * @param integer width of the image
         * @param integer height of the image
         */
        public function PieChart($width = 600, $height = 250) {
            parent::Chart($width, $height);
            $this->plot->setGraphPadding(new Padding(15, 10, 30, 30));
        }

        /**
         * Computes the layout.
         */
        protected function computeLayout() {
            $this->plot->setHasCaption(true);
            $this->plot->computeLayout();
            
            // Get the graph area
            $graphArea = $this->plot->getGraphArea();

            // Compute the coordinates of the pie
            $this->pieCenterX = $graphArea->x1 + ($graphArea->x2 - $graphArea->x1) / 2;
            $this->pieCenterY = $graphArea->y1 + ($graphArea->y2 - $graphArea->y1) / 2;

            $this->pieWidth = round(($graphArea->x2 - $graphArea->x1) * 4 / 5);
            $this->pieHeight = round(($graphArea->y2 - $graphArea->y1) * 3.7 / 5);
            $this->pieDepth = round($this->pieWidth * 0.05);
        }
        
        /**
         * Compare two sampling point values, order from biggest to lowest value.
         *
         * @param double first value
         * @param double second value
         * @return integer result of the comparison
         */
        protected function sortPie($v1, $v2) {
            return $v1[0] == $v2[0] ? 0 :
                $v1[0] > $v2[0] ? -1 :
                1;
        }
        
        /**
         * Compute pie values in percentage and sort them.
         */
        protected function computePercent() {
            $this->total = 0;
            $this->percent = array();

            $pointList = $this->dataSet->getPointList();
            foreach ($pointList as $point) {
                $this->total += $point->getY();
            }

            foreach ($pointList as $point) {
                $percent = $this->total == 0 ? 0 : 100 * $point->getY() / $this->total;

                array_push($this->percent, array($percent, $point));
            }

            // Sort data points
            if ($this->config->getSortDataPoint()) {
                usort($this->percent, array("PieChart", "sortPie"));
            }
        }

        /**
         * Creates the pie chart image.
         */
        protected function createImage() {
            parent::createImage();

            // Get graphical obects
            $img = $this->plot->getImg();
            $palette = $this->plot->getPalette();
            $primitive = $this->plot->getPrimitive();
            
            // Get the graph area
            $graphArea = $this->plot->getGraphArea();

            // Legend box
            $primitive->outlinedBox($graphArea->x1, $graphArea->y1, $graphArea->x2, $graphArea->y2, $palette->axisColor[0], $palette->axisColor[1]);

            // Aqua-like background
            for ($i = $graphArea->y1 + 2; $i < $graphArea->y2 - 1; $i++) {
                $color = $palette->backgroundColor[($i + 3) % 4];
                $primitive->line($graphArea->x1 + 2, $i, $graphArea->x2 - 2, $i, $color);
            }
        }

        /**
         * Renders the caption.
         */
        protected function printCaption() {
            // Create a list of labels
            $labelList = array();
            foreach($this->percent as $percent) {
                list($percent, $point) = $percent;
                $label = $point->getX();
                
                array_push($labelList, $label);
            }
            
            // Create the caption
            $caption = new Caption();
            $caption->setPlot($this->plot);
            $caption->setLabelList($labelList);
            
            $palette = $this->plot->getPalette();
            $pieColorSet = $palette->pieColorSet;
            $caption->setColorSet($pieColorSet);

            // Render the caption
            $caption->render();
        }

        /**
         * Draw a 2D disc.
         *
         * @param integer Center coordinate (y)
         * @param array Colors for each portion
         * @param bitfield Drawing mode
         */
        protected function drawDisc($cy, $colorArray, $mode) {
            // Get graphical obects
            $img = $this->plot->getImg();

            $i = 0;
            $oldAngle = 0;
            $percentTotal = 0;

            foreach ($this->percent as $a) {
                list ($percent, $point) = $a;

                // If value is null, don't draw this arc
                if ($percent <= 0) {
                    continue;
                }
                
                $color = $colorArray[$i % count($colorArray)];

                $percentTotal += $percent;
                $newAngle = $percentTotal * 360 / 100;

                // imagefilledarc doesn't like null values (#1)
                if ($newAngle - $oldAngle >= 1) {
                    imagefilledarc($img, $this->pieCenterX, $cy, $this->pieWidth, $this->pieHeight, $oldAngle, $newAngle, $color->getColor($img), $mode);
                }

                $oldAngle = $newAngle;

                $i++;
            }
        }

        /**
         * Print the percentage text.
         */
        protected function drawPercent() {
            // Get graphical obects
            $img = $this->plot->getImg();
            $palette = $this->plot->getPalette();
            $text = $this->plot->getText();
            $primitive = $this->plot->getPrimitive();
            
            $angle1 = 0;
            $percentTotal = 0;

            foreach ($this->percent as $a) {
                list ($percent, $point) = $a;

                // If value is null, the arc isn't drawn, no need to display percent
                if ($percent <= 0) {
                    continue;
                }

                $percentTotal += $percent;
                $angle2 = $percentTotal * 2 * M_PI / 100;

                $angle = $angle1 + ($angle2 - $angle1) / 2;
                $label = number_format($percent) . "%";

                $x = cos($angle) * ($this->pieWidth + 35) / 2 + $this->pieCenterX;
                $y = sin($angle) * ($this->pieHeight + 35) / 2 + $this->pieCenterY;

                $text->printText($img, $x, $y, $this->plot->getTextColor(), $label, $text->fontCondensed, $text->HORIZONTAL_CENTER_ALIGN | $text->VERTICAL_CENTER_ALIGN);

                $angle1 = $angle2;
            }
        }

        /**
         * Print the pie chart.
         */
        protected function printPie() {
            // Get graphical obects
            $img = $this->plot->getImg();
            $palette = $this->plot->getPalette();
            $text = $this->plot->getText();
            $primitive = $this->plot->getPrimitive();

            // Get the pie color set
            $pieColorSet = $palette->pieColorSet;
            $pieColorSet->reset();

            // Silhouette
            for ($cy = $this->pieCenterY + $this->pieDepth / 2; $cy >= $this->pieCenterY - $this->pieDepth / 2; $cy--) {
                $this->drawDisc($cy, $palette->pieColorSet->shadowColorList, IMG_ARC_EDGED);
            }

            // Top
            $this->drawDisc($this->pieCenterY - $this->pieDepth / 2, $palette->pieColorSet->colorList, IMG_ARC_PIE);

            // Top Outline
            if ($this->config->getShowPointCaption()) {
                $this->drawPercent();
            }
        }

        /**
         * Render the chart image.
         *
         * @param string name of the file to render the image to (optional)
         */
        public function render($fileName = null) {
            $this->computePercent();
            $this->computeLayout();
            $this->createImage();
            $this->plot->printLogo();
            $this->plot->printTitle();
            $this->printPie();
            $this->printCaption();

            $this->plot->render($fileName);
        }
    }
?>