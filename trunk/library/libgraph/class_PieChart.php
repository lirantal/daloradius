<?php
/* PieChart - Create simple 3D pie charts using GD
 * Version 1.0.0
 * Copyright 2004, Steve Blinch
 * http://code.blitzaffe.com/php/
 *
 * REQUIREMENTS
 *
 * This class requires the GD image library extension.
 *
 *
 * EXAMPLE
 *
 * // PieChart usage example
 * require_once('class_PieChart.php');
 * $pie = &new PieChart();
 *
 * // setup our data; value corresponds to the percentage
 * $piedata = array(
 *		array('value'=>10,'title'=>'Fruit'),
 *		array('value'=>25,'title'=>'Vegetables'),
 *		array('value'=>40,'title'=>'Meat'),
 *		array('value'=>10,'title'=>'Dairy'),
 *		array('value'=>15,'title'=>'Pepsi'),
 * );
 * // pass the data to the pie chart class
 * $pie->data($piedata);
 *
 * // create a 170x110px image with a 150x150px pie chart, with a
 * // drop shadow under the pie chart, antialiasing enabled, and legend disabled
 * $pie->create_image(170,110,150,150,true,true,false);
 *
 * // display the image (outputs appropriate headers to the browser for a PNG
 * // image, then displays the PNG)
 * $pie->display();
 *
 * // clean up
 * $pie->destroy_image();
 *
 *
 */
class PieChart {
	
	function PieChart() {
		unset($this->image);

		$this->cursor = 0;
		$this->outline = true;
		
		$this->stock_colors = array(
			array(0xFF,0x00,0x00),
			array(0x00,0xFF,0x00),
			array(0x00,0x00,0xFF),
			array(0xFF,0xFF,0x00),
			array(0xFF,0x00,0xFF),
			array(0x00,0xFF,0xFF),
			array(0xFF,0x7F,0x7F),
			array(0x7F,0xFF,0x00),
			array(0xFF,0xFF,0x7F),
			array(0x7F,0x00,0xFF),
			array(0x7F,0xFF,0x7F),
			array(0xFF,0x7F,0x00),
			array(0x00,0x7F,0xFF),
			array(0xFF,0x7F,0xFF),
			array(0x7F,0x7F,0xFF),
			array(0x00,0xFF,0x7F),
			array(0x7F,0xFF,0xFF),
			array(0x9F,0x2F,0x2F),
			array(0x2F,0x9F,0x00),
			array(0x9F,0x9F,0x2F),
			array(0x2F,0x00,0x9F),
			array(0x2F,0x9F,0x2F),
			array(0x9F,0x2F,0x00),
			array(0x00,0x2F,0x9F),
			array(0x9F,0x2F,0x9F),
			array(0x2F,0x2F,0x9F),
			array(0x00,0x9F,0x2F),
			array(0x2F,0x9F,0x9F),
		);
	}
	
	function data(&$data) {
		$this->slices = array();
		$total = 0;
		
		reset($data);
		foreach ($data as $k=>$item) {
			$total += $item["value"];
		}
		
		reset($data);
		$current = 0;
		$color = 0;
		
		foreach ($data as $k=>$item) {
			$value = floor($item["value"] * 360 / $total);
			if ($value<2) $value = 2;
			if ($item["color"]) {
				$this->colors[$color] = $item["color"];
			} else {
				$this->colors[$color] = $this->stock_colors[$color];
			}
			$percent = floor($item["value"] * 100 / $total);
			$this->slices[] = array(
				$current,
				$current+$value,
				$color,
				$percent,
				$item["title"]
			);
			$color++;
			$current += $value;
		}
		$this->slices[count($this->slices)-1][1] = 360;
	}
	
	function create_image($image_width,$image_height,$pie_width,$pie_height,$shadow=true,$antialias=true,$legend=true) {
		$this->imagewidth = $image_width;
		$this->imageheight = $image_height;

		$x = 10;

		$this->antialias = $antialias;
		if ($this->antialias) {
			$this->imagewidth *= 3;
			$this->imageheight *= 3;
			
			$pie_width *= 3;
			$pie_height *= 3;
			$x *= 3;
		}
		
		$y = floor(($this->imageheight - $pie_height) / 2);
		
		$this->legend = $legend;


		if ($shadow) {
			$this->shadowsize = floor($this->imagewidth / 30);
			
			$pie_width -= $this->shadowsize;
			$pie_height -= $this->shadowsize;
//			$y -= ($this->shadowsize / 2);
		}
		
		$image = imagecreate($this->imagewidth,$this->imageheight);  
		$this->draw(&$image,$x,$y,$pie_width,$pie_height,$shadow);
	}

	function draw(&$image,$x,$y,$pie_width,$pie_height,$shadow=true) {
		
		$this->width = $pie_width;
		$this->height = $pie_height;
		$this->draw_x = $x;
		$this->draw_y = $y;

		$this->shadowsize = floor($this->width / 30);
		
		$this->halfheight = floor($this->height/2);
		$this->pieheight = floor($this->height/10);
		
		$this->shadow = $shadow;
		
		$this->centerx = $this->draw_x + floor($this->width/2);
		$this->centery = $this->draw_y + floor($this->height/2);
		
		$this->draw_pie(&$image);
		
	}

	function dropsegment(&$image,$startdeg,$enddeg,&$color) {
		global $hilight;
		//imagestring($image,1,2,2,"({$this->centerx},{$this->centery},{$this->width},{$this->height})",2);

		if ($startdeg>=180 && $enddeg>180) {
			return; // upper half doesn't need to be dropped
		} elseif ($startdeg<90 && $enddeg>90) {
			$this->dropsegment(&$image,$startdeg,90,&$color);
			$this->dropsegment(&$image,90,$enddeg,&$color);
		} elseif ($startdeg>=90 && $enddeg>90 && $enddeg<=180) {
			
			if ($enddeg>180) $enddeg = 180;
			
			//$color = $hilight;
			$x = $this->centerx + floor(cos(deg2rad($startdeg)) * ($this->width/2));
			$x2 = $this->centerx + floor(cos(deg2rad($enddeg)) * ($this->width/2)) + 1;
			
			$y = $this->centery + floor(sin(deg2rad($enddeg)) * ($this->halfheight/2));
			if ($x2<$x) { $z=$x2; $x2=$x; $x=$z; }
			imagefilledrectangle($image,$x,$y,$x2,$y+$this->pieheight,$color);
			
		} elseif ($startdeg>=90 && $enddeg>180) {
			$this->dropsegment(&$image,$startdeg,180,&$color);
		} else {
			$x = floor($this->centerx + cos(deg2rad($startdeg)) * ($this->width/2));
			$x2 = floor($this->centerx + cos(deg2rad($enddeg)) * ($this->width/2));
			$y = floor($this->centery + sin(deg2rad($startdeg)) * ($this->halfheight/2));
			if ($x2<$x) { $z=$x2; $x2=$x; $x=$z; }
			//$this->cursor += 8;
			//imagestring($image,1,2,15+$this->cursor,"($x,$x2,$y)",2);

			imagefilledrectangle($image,$x,$y,$x2,$y+$this->pieheight,$color);
		}
	}
	
	function darken($rgb) {
		return $rgb-50>0?$rgb-50:0;
	}
	
	function add_title(&$image,$color,$percentage,$title,$textcolor) {
		$line_height = 10;
		$box_size = 5;
		
		
		$y = $this->centery - (count($this->slices)*$line_height/2) + $this->cursor;
		$x = $this->draw_x+$this->width+$this->shadowsize+5;
		
		imagefilledrectangle(
			$image,
			$x,
			$y+($line_height-$box_size) / 2 + 2,
			$x+$box_size,
			$y+$box_size+($line_height-$box_size) / 2 + 2,
			$color
		);
		$x += 12;
		
		$s = sprintf('%2d%% - %s',$percentage,$title);
		
		$ifw = imagefontwidth(2);
		$w = $ifw * strlen($s);
		if ($x+$w>$this->imagewidth-5) {
			$newstrlen = floor(($this->imagewidth-5-$x) / $ifw)-3;
			$s = substr($s,0,$newstrlen)."...";
		}
		imagestring(
			$image,2,
			$x,
			$y,
			$s,
			$textcolor
		);
		$this->cursor += $line_height;
	}
	
	function allocate_colors(&$image,&$white,&$black,&$gray) {
		$white = imagecolorallocate($image,0xFF,0xFF,0xFF);
		$black = imagecolorallocate($image,0x00,0x00,0x00);
		$gray = imagecolorallocate($image,0xDF,0xDF,0xDF);
		for ($i=0; $i<count($this->colors); $i++) {
			$this->color_lookup[$i] = imagecolorallocate(
				$image,
				$this->colors[$i][0],
				$this->colors[$i][1],
				$this->colors[$i][2]
			);
			$this->color_dark[$i] = imagecolorallocate(
				$image,
				$this->darken($this->colors[$i][0]),
				$this->darken($this->colors[$i][1]),
				$this->darken($this->colors[$i][2])
			);
		}
	}

	function draw_pie(&$image) {
		$this->allocate_colors($image,$white,$black,$gray);
		
		if ($this->shadow) {
			$shadow = imagecolorallocate($image, 0xDD, 0xDD, 0xDD);  
			imagefilledellipse($image, $this->centerx+$this->shadowsize,$this->centery+$this->pieheight+$this->shadowsize,$this->width,$this->halfheight,$shadow);
		}

		reset($this->slices);
		foreach ($this->slices as $k=>$slice) {
			imagefilledarc ($image, $this->centerx, $this->centery+$this->pieheight, $this->width, $this->halfheight, $slice[0], $slice[1], $this->color_dark[$slice[2]], IMG_ARC_PIE);
		}

		reset($this->slices);
		foreach ($this->slices as $k=>$slice) {
			$this->dropsegment($image,$slice[0],$slice[1],$this->color_dark[$slice[2]]);
		}

		reset($this->slices);
		foreach ($this->slices as $k=>$slice) {
			imagefilledarc ($image, $this->centerx, $this->centery, $this->width, $this->halfheight, $slice[0], $slice[1], $this->color_lookup[$slice[2]], IMG_ARC_PIE);
		}

		if ($this->antialias) {
			$newwidth = floor($this->imagewidth/3);
			$newheight = floor($this->imageheight/3);
			
			$newimage = imagecreatetruecolor($newwidth, $newheight);  
			$this->allocate_colors($newimage,$white,$black,$gray);

	//		$white = imagecolorallocate($newimage, 0xFF, 0xFF, 0xFF);  
			
			imagecopyresampled($newimage,$image,0,0,0,0,$newwidth,$newheight,$this->imagewidth,$this->imageheight);
			imagedestroy($image);
			$image = &$newimage;
			$this->draw_x /= 3;
			$this->draw_y /= 3;
			$this->width /= 3;
			$this->centery /= 3;
			$this->imagewidth = $newwidth;
			$this->imageheight = $newheight;
		}
		if ($this->outline) imagerectangle($image,0,0,$this->imagewidth-1,$this->imageheight-1,$gray);

		reset($this->slices);
	
		if ($this->legend) {
			foreach ($this->slices as $k=>$slice) {
	//			$deg = $slice[0] + ($slice[1] - $slice[0]);
				$this->add_title($image,$this->color_lookup[$slice[2]],$slice[3],$slice[4],$black);
			}
		}
		
		
		$this->image = &$image;
	}
	
	function destroy_image() {
		imagedestroy($this->image);
		unset($this->image);
	}
	
	function display() {
		//header("Content-type: image/png");
		imagepng($this->image); 
	}
	
	function save($filename) {
		imagepng($this->image,$filename); 
	}
}

?>
