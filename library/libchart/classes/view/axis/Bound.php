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
     * Object representing the bounds of a dataset (its minimal and maximal values) on its vertical axis.
     * The bounds are automatically calculated from a XYDataSet or XYSeriesDataSet.
     * Default (calculated) bounds can be overriden using the setLowerBound() and setUpperBound() methods.
     *
     * @author Jean-Marc Trémeaux (jm.tremeaux at gmail.com)
     * Created on 25 july 2007
     */
     class Bound {
        /**
         * Manually set lower bound, overrides the value calculated by computeBound().
         */
        private $lowerBound = null;

        /**
         * Manually set upper bound, overrides the value calculated by computeBound().
         */
        private $upperBound = null;
        
        /**
         * Computed min bound.
         */
        private $yMinValue = null;
        
        /**
         * Computed max bound.
         */
        private $yMaxValue = null;
        
        /**
         * Compute the boundaries on the axis.
         *
         * @param dataSet The data set
         */
        public function computeBound($dataSet) {
            // Check if the data set is empty
            $dataSetEmpty = true;
            $serieList = null;
            if ($dataSet instanceof XYDataSet) {
                $pointList = $dataSet->getPointList();
                $dataSetEmpty = count($pointList) == 0;
                
                if (!$dataSetEmpty) {
                    // Process it as a serie
                    $serieList = array();
                    array_push($serieList, $dataSet);
                }
            } else if ($dataSet instanceof XYSeriesDataSet) {
                $serieList = $dataSet->getSerieList();
                if (count($serieList) > 0) {
                    $serie = current($serieList);
                    $dataSetEmpty = count($serie) == 0;
                }
            } else {
                die("Error: unknown dataset type");
            }
            
            // If the dataset is empty, default some bounds
            $yMin = 0;
            $yMax = 1;
            if (!$dataSetEmpty) {
                // Compute lower and upper bound on the value axis
                unset($yMin);
                unset($yMax);

                foreach ($serieList as $serie) {
                    foreach ($serie->getPointList() as $point) {
                        $y = $point->getY();
                        
                        if (!isset($yMin)) {
                            $yMin = $y;
                            $yMax = $y;
                        } else {
                            if ($y < $yMin) {
                                $yMin = $y;
                            }
            
                            if ($y > $yMax) {
                                $yMax = $y;
                            }
                        }
                    }
                }
            }

            // If user specified bounds and they are actually greater than computer bounds, override computed bounds
            if (isset($this->lowerBound) && $this->lowerBound < $yMin) {
                $this->yMinValue = $this->lowerBound;
            } else {
                $this->yMinValue = $yMin;
            }

            if (isset($this->upperBound) && $this->upperBound > $yMax) {
                $this->yMaxValue = $this->upperBound;
            } else {
                $this->yMaxValue = $yMax;
            }
        }

        /**
         * Getter of yMinValue.
         *
         * @return min bound
         */
        public function getYMinValue() {
            return $this->yMinValue;
        }

        /**
         * Getter of yMaxValue.
         *
         * @return max bound
         */
        public function getYMaxValue() {
            return $this->yMaxValue;
        }

        /**
         * Set manually the lower boundary value (overrides the automatic formatting).
         * Typical usage is to set the bars starting from zero.
         *
         * @param double lower boundary value
         */
        public function setLowerBound($lowerBound) {
            $this->lowerBound = $lowerBound;
        }

        /**
         * Set manually the upper boundary value (overrides the automatic formatting).
         *
         * @param double upper boundary value
         */
        public function setUpperBound($upperBound) {
            $this->upperBound = $upperBound;
        }
     }
?>