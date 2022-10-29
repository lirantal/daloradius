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
     * Set of data in the form of (x, y) items.
     *
     * @author Jean-Marc Trémeaux (jm.tremeaux at gmail.com)
     * Created on 10 may 2007
     */
    class XYDataSet extends DataSet {
        private $pointList;
    
        /**
         * Constructor of XYDataSet.
         *
         */
        public function XYDataSet() {
            $this->pointList = array();
        }
    
        /**
         * Add a new point to the dataset.
         *
         * @param Point Point to add to the dataset
         */
        
        public function addPoint($point) {
            array_push($this->pointList, $point);
        }

        /**
         * Getter of pointList.
         *
         * @return List of points.
         */
        public function getPointList() {
            return $this->pointList;
        }
    }
?>