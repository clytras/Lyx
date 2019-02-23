<?php

namespace Lyx\Graphics\Core;

// Graphics core functions

/** 
 * PNG ALPHA CHANNEL SUPPORT for imagecopymerge(); 
 * This is a function like imagecopymerge but it handle alpha channel well!!! 
 **/

// A fix to get a function like imagecopymerge WITH ALPHA SUPPORT 
// Main script by aiden dot mail at freemail dot hu 
// Transformed to imagecopymerge_alpha() by rodrigo dot polo at gmail dot com 

function imagecopymerge_alpha(
  $dst_im, // Destination image
  $src_im, // Source image
  $dst_x, // Destination X
  $dst_y, // Destination Y
  $src_x, // Source X
  $src_y, // Source Y
  $src_w, // Source width
  $src_h, // Source height
  $pct, // Alpha percentage
  $alp = NULL // Alpha value if alpha percentage is omitted
) { 

	if($alp === NULL) {
    if(!isset($pct))
      return false;

    $pct /= 100;
    $alp = 127 * $pct;
	}

  // Get image width and height 
  $w = imagesx( $src_im ); 
  $h = imagesy( $src_im ); 

  // Turn alpha blending off 
  imagealphablending( $src_im, false ); 

  // Find the most opaque pixel in the image (the one with the smallest alpha value) 
  $minalpha = 127; 
  for($x = 0; $x < $w; $x++) 
  for($y = 0; $y < $h; $y++) { 
    $alpha = (imagecolorat($src_im, $x, $y) >> 24) & 0xFF; 
    if($alpha < $minalpha)
      $minalpha = $alpha; 
  }

  //loop through image pixels and modify alpha for each 
  for($x = 0; $x < $w; $x++) { 
    for($y = 0; $y < $h; $y++) { 
      //get current alpha value (represents the TANSPARENCY!) 
      $colorxy = imagecolorat($src_im, $x, $y); 
      $alpha = ($colorxy >> 24) & 0xFF; 

      //calculate new alpha 
      if($minalpha !== 127)
        $alpha = 127 + /*127 * $pct*/ $alp * ($alpha - 127) / (127 - $minalpha); 
      else
        $alpha += /*127 * $pct; */ $alp;

      //get the color index with new alpha 
      $alphacolorxy = imagecolorallocatealpha($src_im, ($colorxy >> 16) & 0xFF, ($colorxy >> 8) & 0xFF, $colorxy & 0xFF, $alpha); 

      //set pixel with the new color + opacity 
      if(!imagesetpixel($src_im, $x, $y, $alphacolorxy))
        return false; 
    }
  }
  
  // The image copy 
  imagecopy($dst_im, $src_im, $dst_x, $dst_y, $src_x, $src_y, $src_w, $src_h); 
}