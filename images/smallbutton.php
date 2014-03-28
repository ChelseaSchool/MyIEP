<?php
/**
 * button.php -- eGPS main page button w/ text overlay
 *
 *
 * Copyright (c) 2005 Grasslands Regional Division #6
 * All rights reserved
 *
 * Created: May 25, 2005
 * By: M. Nielsen
 * Modified: July 21, 2005
 *
 */
  define('EGPS_PATH','../');

  //dl('gd.so');

  header("Content-type: image/png");

  // Put center-rotated ttf-text into image
  // Same signature as imagettftext();
  function imagettftextalign($image, $size, $angle, $x, $y, $color, $font, $text, $alignment='C') {
  
   //check width of the text
   $bbox = imagettfbbox ($size, $angle, $font, $text);
   $textWidth = $bbox[2] - $bbox[0];
   switch ($alignment) {
       case "R":
           $x -= $textWidth;
           break;
       case "C":
           $x -= $textWidth / 2;
           break;
   }

   imagettftext ($image, $size, $angle, $x, $y, $color, $font, $text);

   }

  $string = $_GET['title'];
  $font = EGPS_PATH . "layout/fonts/chanticl.ttf";
  $im    = imagecreatefrompng(EGPS_PATH . 'images/smallbutton.png');

  $white =   imagecolorallocate($im, 255,255,255);

  //if the string is too long we drop a size in font...
  if(strlen($string) > 14) {
      $size = 8;
  } else {
      $size = 10;
  }
  imagettftextalign($im, $size, 0, 50, 16, $white, $font,$string,'C');
  imagepng($im);
  imagedestroy($im);

?>