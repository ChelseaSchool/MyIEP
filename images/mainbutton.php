<?php

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
  $im    = imagecreatefrompng(EGPS_PATH . 'images/mainbutton.png');

  $colour = imagecolorallocate($im,0,33,66);
  imagettftextalign($im, 12, 0, 75, 22, $colour, $font,$string,'C');
  imagepng($im);
  imagedestroy($im);

?>
