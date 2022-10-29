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
     * Base abstract class for rendering both horizontal and vertical bar charts.
     *
     * @author Jean-Marc Trémeaux (jm.tremeaux at gmail.com)
     */
    abstract class BarChart extends Chart {
        protected $bound;
        protected $axis;
        protected $hasSeveralSerie;
        
        /**
         * Creates a new bar chart.
         *
         * @param integer width of the image
         * @param integer height of the image
         */
        protected function BarChart($width, $height) {
            parent::Chart($width, $height);

            // Initialize the bounds
            $this->bound = new Bound();
            $this->bound->setLowerBound(0);
        }

        /**
         * Compute the axis.
         */
        protected function computeAxis() {
            $this->axis = new Axis($this->bound->getYMinValue(), $this->bound->getYMaxValue());
            $this->axis->computeBoundaries();
        }

        /**
         * Create the image.
         */
        protected function createImage() {
            parent::createImage();

            // Get graphical obects
            $img = $this->plot->getImg();
            $palette = $this->plot->getPalette();
            $text = $this->plot->getText();
            $primitive = $this->plot->getPrimitive();
            
            // Get the graph area
            $graphArea = $this->plot->getGraphArea();

            // Aqua-like background
            for ($i = $graphArea->y1; $i < $graphArea->y2; $i++) {
                $color = $palette->backgroundColor[($i + 3) % 4];
                $primitive->line($graphArea->x1, $i, $graphArea->x2, $i, $color);
            }

            // Axis
            imagerectangle($img, $graphArea->x1 - 1, $graphArea->y1, $graphArea->x1, $graphArea->y2, $palette->axisColor[0]->getColor($img));
            imagerectangle($img, $graphArea->x1 - 1, $graphArea->y2, $graphArea->x2, $graphArea->y2 + 1, $palette->axisColor[0]->getColor($img));
        }

        /**
         * Returns true if the data set has some data.
         * @param minNumberOfPoint Minimum number of points (1 for bars, 2 for lines).
         *
         * @return true if data set empty
         */
        protected function isEmptyDataSet($minNumberOfPoint) {
            if ($this->dataSet instanceof XYDataSet) {
                $pointList = $this->dataSet->getPointList();
                $pointCount = count($pointList);
                return $pointCount < $minNumberOfPoint;
            } else if ($this->dataSet instanceof XYSeriesDataSet) {
                $serieList = $this->dataSet->getSerieList();
                reset($serieList);
                if (count($serieList) > 0) {
                    $serie = current($serieList);
                    $pointList = $serie->getPointList();
                    $pointCount = count($pointList);
                    return $pointCount < $minNumberOfPoint;
                }
            } else {
                die("Error: unknown dataset type");
            }
        }

        /**
         * Checks the data model before rendering the graph.
         */
        protected function checkDataModel() {
            // Check if a dataset was defined
            if (!$this->dataSet) {
                die("Error: No dataset defined.");
            }
            
            // Bar charts accept both XYDataSet and XYSeriesDataSet
            if ($this->dataSet instanceof XYDataSet) {
                // The dataset contains only one serie
                $this->hasSeveralSerie = false;
            } else if ($this->dataSet instanceof XYSeriesDataSet) {
                // Check if each series has the same number of points
                unset($lastPointCount);
                $serieList = $this->dataSet->getSerieList();
                for ($i = 0; $i < count($serieList); $i++) {
                    $serie = $serieList[$i];
                    $pointCount = count($serie->getPointList());
                    if (isset($lastPointCount) && $pointCount != $lastPointCount) {
                        die("Error: serie <" . $i . "> doesn't have the same number of points as last serie (last one: <" . $lastPointCount. ">, this one: <" . $pointCount. ">).");
                    }
                    $lastPointCount = $pointCount;
                }
                
                // The dataset contains several series
                $this->hasSeveralSerie = true;
            } else {
                die("Error: Bar chart accept only XYDataSet and XYSeriesDataSet");
            }
        }

        /**
         * Return the data as a series list (for consistency).
         *
         * @return List of series
         */
        protected function getDataAsSerieList() {
            // Get the data as a series list
            $serieList = null;
            if ($this->dataSet instanceof XYSeriesDataSet) {
                $serieList = $this->dataSet->getSerieList();
            } else if ($this->dataSet instanceof XYDataSet) {
                $serieList = array();
                array_push($serieList, $this->dataSet);
            }
            
            return $serieList;
        }
        
        /**
         * Return the first serie of the list, or the dataSet itself if there is no serie.
         *
         * @return XYDataSet
         */
        protected function getFirstSerieOfList() {
            $pointList = null;
            if ($this->dataSet instanceof XYSeriesDataSet) {
                // For a series dataset, print the legend from the first serie
                $serieList = $this->dataSet->getSerieList();
                reset($serieList);
                $serie = current($serieList);
                $pointList = $serie->getPointList();
            } else if ($this->dataSet instanceof XYDataSet) {
                $pointList = $this->dataSet->getPointList();
            }
            
            return $pointList;
        }
        
        /**
         * Retourns the bound.
         *
         * @return bound Bound
         */
        public function getBound() {
            return $this->bound;
        }
    }
?>