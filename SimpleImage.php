<?php
/*
* File: SimpleImage.php
* Author: Simon Jarvis
* Copyright: 2006 Simon Jarvis
* Date: 08/11/06
* Link: http://www.white-hat-web-design.co.uk/articles/php-image-resizing.php
* 
* This program is free software; you can redistribute it and/or 
* modify it under the terms of the GNU General Public License 
* as published by the Free Software Foundation; either version 2 
* of the License, or (at your option) any later version.
* 
* This program is distributed in the hope that it will be useful, 
* but WITHOUT ANY WARRANTY; without even the implied warranty of 
* MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the 
* GNU General Public License for more details: 
* http://www.gnu.org/licenses/gpl.html
*
*/
 
class SimpleImage {
   
   var $image;
   var $image_type;
   
   function load($filename) {
      $image_info = getimagesize($filename);
      $this->image_type = $image_info[2];
      if( $this->image_type == IMAGETYPE_JPEG ) {
         $this->image = imagecreatefromjpeg($filename);
      } elseif( $this->image_type == IMAGETYPE_GIF ) {
         $this->image = imagecreatefromgif($filename);
      } elseif( $this->image_type == IMAGETYPE_PNG ) {
         $this->image = imagecreatefrompng($filename);
		imagealphablending($this->image, true);
		imagesavealpha($this->image, true);
      }
   }
   function save($filename, $image_type=IMAGETYPE_JPEG, $compression=100, $permissions=null) {
      if( $image_type == IMAGETYPE_JPEG ) {
         imagejpeg($this->image,$filename,$compression);
      } elseif( $image_type == IMAGETYPE_GIF ) {
         imagegif($this->image,$filename);         
      } elseif( $image_type == IMAGETYPE_PNG ) {
         imagepng($this->image,$filename);
      }   
      if( $permissions != null) {
         chmod($filename,$permissions);
      }
   }
   function output($image_type=IMAGETYPE_JPEG) {
      if( $image_type == IMAGETYPE_JPEG ) {
         imagejpeg($this->image);
      } elseif( $image_type == IMAGETYPE_GIF ) {
         imagegif($this->image);         
      } elseif( $image_type == IMAGETYPE_PNG ) {
         imagepng($this->image);
      }   
   }
   function getCanvasWidth(){
   	if (isset($this->canvasWidth))return $this->canvasWidth();
   	else return $this->getWidth();
   }
   function getCanvasHeight(){
   	if (isset($this->canvasHeight))return $this->canvasHeight();
   	else return $this->getHeight();
   }
   function getWidth() {
      return imagesx($this->image);
   }
   function getHeight() {
      return imagesy($this->image);
   }
   function resizeToHeight($height) {
      $ratio = $height / $this->getHeight();
      $width = $this->getWidth() * $ratio;
      $this->resize($width,$height);
   }
   function resizeToWidth($width) {
      $ratio = $width / $this->getWidth();
      $height = $this->getheight() * $ratio;
      $this->resize($width,$height);
   }
   function resizeToMaxHeight($height) {
    	 if ($height>$this->getHeight())return;
      $ratio = $height / $this->getHeight();
      $width = $this->getWidth() * $ratio;
      $this->resize($width,$height);
   }
   function resizeToMaxWidth($width) {
   	 if ($width>$this->getWidth())return;
      $ratio = $width / $this->getWidth();
      $height = $this->getheight() * $ratio;
      $this->resize($width,$height);
   }
   function setCanvas($width,$height,$bgcolor=array(255,255,255)){
      $new_image = imagecreatetruecolor($width, $height);
	 $color = imagecolorallocate($new_image, $bgcolor[0], $bgcolor[1], $bgcolor[2]);
	 imagefilledrectangle($new_image, 0, 0, $width, $height, $color);      
      
      $offset_x = ($width-$this->getWidth())/2;
      $offset_y = ($height-$this->getHeight())/2;
      imagecopyresampled($new_image, $this->image, $offset_x, $offset_y, 0, 0, $this->getWidth(), $this->getHeight(), $this->getWidth(), $this->getHeight());
      $this->image = $new_image;   
   }
   function scale($scale) {
      $width = $this->getWidth() * $scale/100;
      $height = $this->getheight() * $scale/100; 
      $this->resize($width,$height);
   }
   function resize($width,$height) {
      $new_image = imagecreatetruecolor($width, $height);
      imagesavealpha($new_image, true);
      $trans_colour = imagecolorallocatealpha($new_image, 0, 0, 0, 127);
	 imagefill($new_image, 0, 0, $trans_colour);
      imagecopyresampled($new_image, $this->image, 0, 0, 0, 0, $width, $height, $this->getWidth(), $this->getHeight());
      $this->image = $new_image;   
   } 
   	     
   	function zoomToMinimum($min_width,$min_height){
   		if ($this->getWidth()<$min_width){
   			$min_height = ($this->getWidth()*$this->getHeight())/$min_width;
   			$this->resize($min_width,$min_height);
   		}
   		
   		if ($this->getHeight()<$min_height){
   			$min_width = ($this->getHeight()*$this->getWidth())/$min_height;
   			$this->resize($min_width,$min_height);
   		}
   		
   	}
   	     
	function createImage($width,$height){
		$new_image = imagecreatetruecolor($width, $height);
		imagesavealpha($new_image, true);
		
		imagealphablending($new_image, false);
		$trans_colour = imagecolorallocatealpha($new_image, 255, 255, 255, 127);
		imagefill($new_image, 0, 0, $trans_colour);
		
		imagealphablending($new_image, true);
	     $this->image = $new_image;   
	}
	function paint($image,$x=0,$y=0){
     	imagecopyresampled($this->image, $image, $x, $y, 0, 0, imagesx($image), imagesy($image), imagesx($image), imagesy($image));
	}
}