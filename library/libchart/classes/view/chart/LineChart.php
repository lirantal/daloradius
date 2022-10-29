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
     * Line chart.
     *
     * @author Jean-Marc Trémeaux (jm.tremeaux at gmail.com)
     */
    class LineChart extends BarChart {
        /**
         * Creates a new line chart.
         * Line charts allow for XYDataSet and XYSeriesDataSet in order to plot several lines.
         *
         * @param integer width of the image
         * @param integer height of the image
         */
        public function LineChart($width = 600, $height = 250) {
            parent::BarChart($width, $height);

            $this->plot->setGraphPadding(new Padding(5, 30, 50, 50));
        }

        /**
         * Computes the layout.
         */
        protected function computeLayout() {
            if ($this->hasSeveralSerie) {
                $this->plot->setHasCaption(true);
            }
            $this->plot->computeLayout();
        }
        
        /**
         * Print the axis.
         */
        protected function printAxis() {
            $minValue = $this->axis->getLowerBoundary();
            $maxValue = $this->axis->getUpperBoundary();
            $stepValue = $this->axis->getTics();

            // Get graphical obects
            $img = $this->plot->getImg();
            $palette = $this->plot->getPalette();
            $text = $this->plot->getText();
            
            // Get the graph area
            $graphArea = $this->plot->getGraphArea();
            
            // Vertical axis
            for ($value = $minValue; $value <= $maxValue; $value += $stepValue) {
                $y = $graphArea->y2 - ($value - $minValue) * ($graphArea->y2 - $graphArea->y1) / ($this->axis->displayDelta);

                imagerectangle($img, $graphArea->x1 - 3, $y, $graphArea->x1 - 2, $y + 1, $palette->axisColor[0]->getColor($img));
                imagerectangle($img, $graphArea->x1 - 1, $y, $graphArea->x1, $y + 1, $palette->axisColor[1]->getColor($img));

                $text->printText($img, $graphArea->x1 - 5, $y, $this->plot->getTextColor(), $value, $text->fontCondensed, $text->HORIZONTAL_RIGHT_ALIGN | $text->VERTICAL_CENTER_ALIGN);
            }

            // Get first serie of a list
            $pointList = $this->getFirstSerieOfList();
            
            // Horizontal Axis
            $pointCount = count($pointList);
            reset($pointList);
            $columnWidth = ($graphArea->x2 - $graphArea->x1) / ($pointCount - 1);

            for ($i = 0; $i < $pointCount; $i++) {
                $x = $graphArea->x1 + $i * $columnWidth;

                imagerectangle($img, $x - 1, $graphArea->y2 + 2, $x, $graphArea->y2 + 3, $palette->axisColor[0]->getColor($img));
                imagerectangle($img, $x - 1, $graphArea->y2, $x, $graphArea->y2 + 1, $palette->axisColor[1]->getColor($img));

                $point = current($pointList);
                next($pointList);

                $label = $point->getX();

                $text->printDiagonal($img, $x - 5, $graphArea->y2 + 10, $this->plot->getTextColor(), $label);
            }
        }

        /**
         * Print the lines.
         */
        protected function printLine() {
            $minValue = $this->axis->getLowerBoundary();
            $maxValue = $this->axis->getUpperBoundary();
            
            // Get the data as a list of series for consistency
            $serieList = $this->getDataAsSerieList();
            
            // Get graphical obects
            $img = $this->plot->getImg();
            $palette = $this->plot->getPalette();
            $text = $this->plot->getText();
            $primitive = $this->plot->getPrimitive();
            
            // Get the graph area
            $graphArea = $this->plot->getGraphArea();
            
            $lineColorSet = $palette->lineColorSet;
            $lineColorSet->reset();
            for ($j = 0; $j < count($serieList); $j++) {
                $serie = $serieList[$j];
                $pointList = $serie->getPointList();
                $pointCount = count($pointList);
                reset($pointList);

                $columnWidth = ($graphArea->x2 - $graphArea->x1) / ($pointCount - 1);

                $lineColor = $lineColorSet->currentColor();
                $lineColorShadow = $lineColorSet->currentShadowColor();
                $lineColorSet->next();
                $x1 = null;
                $y1 = null;
                for ($i = 0; $i < $pointCount; $i++) {
                    $x2 = $graphArea->x1 + $i * $columnWidth;

                    $point = current($pointList);
                    next($pointList);

                    $value = $point->getY();
                    
                    $y2 = $graphArea->y2 - ($value - $minValue) * ($graphArea->y2 - $graphArea->y1) / ($this->axis->displayDelta);

                    // Draw line 
                    if ($x1) {
                        $primitive->line($x1, $y1, $x2, $y2, $lineColor, 4);
                        $primitive->line($x1, $y1 - 1, $x2, $y2 - 1, $lineColorShadow, 2);
                    }
                    
                    $x1 = $x2;
                    $y1 = $y2;
                }
            }
        }
        
        /**
         * Renders the caption.
         */
        protected function printCaption() {
            // Get the list of labels
            $labelList = $this->dataSet->getTitleList();
            
            // Create the caption
            $caption = new Caption();
            $caption->setPlot($this->plot);
            $caption->setLabelList($labelList);
            
            $palette = $this->plot->getPalette();
            $lineColorSet = $palette->lineColorSet;
            $caption->setColorSet($lineColorSet);
            
            // Render the caption
            $caption->render();
        }

        /**
         * Render the chart image.
         *
         * @param string name of the file to render the image to (optional)
         */
        public function render($fileName = null) {
            // Check the data model
            $this->checkDataModel();

            $this->bound->computeBound($this->dataSet);
            $this->computeAxis();
            $this->computeLayout();
            $this->createImage();
            $this->plot->printLogo();
            $this->plot->printTitle();
            if (!$this->isEmptyDataSet(2)) {
                $this->printAxis();
                $this->printLine();
                if ($this->hasSeveralSerie) {
                    $this->printCaption();
                }
            }

            $this->plot->render($fileName);
        }
    }
?>