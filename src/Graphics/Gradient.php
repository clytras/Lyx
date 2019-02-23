<?php

namespace Lyx\Graphics;

// Gradient class imports

use Lyx\Graphics\Color;
use Lyx\Geom\Rotation;
use function Lyx\Graphics\Core\imagecopymerge_alpha;

class Gradient
{
  public $colors,
    $color_start,
    $color_end,
    $width,
    $height,
    $axis,
    $rotation,
    $im;

  public function __construct()
  {
    $ac = func_num_args();
    $av = func_get_args();
    
    $this->init();
    if($ac == 1)
      $this->width = $this->height = intval($av[0]);
    elseif($ac == 2) {
      $this->width = intval($av[0]);
      $this->height = intval($av[1]);
    }
  }

  private function init()
  {
    $this->resetColors();
    $this->width = 0;
    $this->height = 0;
    $this->axis = 'x';
    $this->rotation = 0;
    $this->im = NULL;
  }

  public function resetColors()
  {
    $this->colors = array();
    $this->color_start = NULL;
    $this->color_end = NULL;
  }

  public function getWidth()
  {
    return $this->width;
  }

  public function setWidth($width)
  {
    $this->width = $width;
  }

  public function getHeight()
  {
    return $this->height;
  }

  public function setHeight($height)
  {
    $this->height = $height;
  }

  public function getAxis()
  {
    return $this->axis;
  }

  public function setAxis($axis)
  {
    $this->axis = $axis;
  }

  public function getRotation()
  {
    return $this->rotation;
  }

  public function setRotation($rotation)
  {
    $this->rotation = $this->validRotation($rotation);
  }

  private function validRotation($rotation)
  {
    $vr = 0;
    if($rotation > 0 && $rotation < 360)
      $vr = $rotation;
    
    return $vr;
  }

  public function hasRotation()
  {
    return $this->rotation > 0 && $this->rotation < 360;
  }

  public function addColor($color, $position = '')
  {
    $position = strtolower($position);
    
    if($position == 'start')
      $this->color_start = new Color($color);
    elseif($position == 'end')
      $this->color_end = new Color($color);
    else
      array_push($this->colors, new Color($color));
  }

  public function addColors()
  {
    foreach(func_get_args() as $col) {
      if(is_array($col))
        foreach($col as $co)
          array_push($this->colors, new Color($co));
      else
        array_push($this->colors, new Color($col));
    }
  }

  private function getAllColors()
  {
    $cols = $this->colors;
    if($this->color_start !== NULL)
      array_unshift($cols, $this->color_start);
    
    if($this->color_end !== NULL)
      array_push($cols, $this->color_end);
    
    return $cols;
  }

  public function generate()
  {
    if($this->im !== NULL) {
      imagedestroy($this->im);
      $this->im = NULL;
    }

    if($this->width == 0 || $this->height == 0)
      return FALSE;
    
    if($this->hasRotation()) {
      $rotbox = Rotation::getRotBoxBounds($this->width, $this->height, $this->rotation);
      $gw = $rotbox->width + 4;
      $gh = $rotbox->height + 4;
    } else {
      $gw = $this->width;
      $gh = $this->height;
    }

    $cols = $this->getAllColors();
    $cols_num = count($cols);
    
    if($cols_num == 0)
      return FALSE;
    elseif($cols_num == 1) {
      $this->im = imagecreatetruecolor($gw, $gh);
      imagefill($final, 0, 0, imagecolorallocate($final, $r, $g, $b));
    } else {
      $this->im = imagecreatetruecolor($gw, $gh);
      imagealphablending($this->im, FALSE);
      imagesavealpha($this->im, TRUE);
      
      $transparent = imagecolorallocatealpha($this->im, 255, 255, 255, 127);
      imagefilledrectangle($this->im, 0, 0, $gw, $gh, $transparent);
      
      $segments = $cols_num - 1;
      $isxaxis = $this->axis == 'x';
      
      if($isxaxis)
        $segsize = $gw / $segments;
      else
        $segsize = $gh / $segments;
      
      for($i = 0, $calcs = 0; $i < $segments; $i++) {
        $gx = $isxaxis ? round($segsize) * $i : 0;
        $gy = $isxaxis ? 0 : round($segsize) * $i;
        $gcx = $isxaxis ? round($segsize) : $gw;
        $gcy = $isxaxis ? $gh : round($segsize);

        $calcs += round($segsize);

        if($i == $segments - 1) {
          if($isxaxis)
            $gcx += $gw - $calcs;
          else
            $gcy += $gh - $calcs;
        }

        $segim = self::linearagradientfill(
          $gcx,
          $gcy,
          $cols[$i],
          $cols[$i+1],
          $this->axis);
        imagecopy($this->im, $segim,
          $gx,
          $gy,
          0,
          0,
          $gcx,
          $gcy);
        imagedestroy($segim);
      }
    }
    
    if($this->hasRotation()) {
      $retim = imagecreatetruecolor($this->width, $this->height);
      imagealphablending($retim, FALSE);
      imagesavealpha($retim, TRUE);

      $transparent = imagecolorallocatealpha($retim, 255, 255, 255, 127);
      imagefilledrectangle($retim, 0, 0, $this->width, $this->height, $transparent);

      $rotbox = Rotation::getRotBoxBounds($gw, $gh, $this->rotation);
      $rx = $rotbox->width / 2 - $this->width / 2;
      $ry = $rotbox->height / 2 - $this->height / 2;

      $rotim = imagerotate($this->im, $this->rotation, -1);
      imagecopy($retim, $rotim, 0, 0, $rx, $ry, $gw, $gh);
      imagedestroy($this->im);
      imagedestroy($rotim);
      $this->im = $retim;
    }

    return $this->im;
  }

  public static function linearagradientfill($width, $height, $startColor, $endColor, $axis = 'x')
  {
    $final = imagecreatetruecolor($width, $height);
    $hasAlpha = $startColor->hasAlpha() || $endColor->hasAlpha();
    
    if($hasAlpha) {
      imagealphablending($final, false);
      imagesavealpha($final, true);
      $transparent = imagecolorallocatealpha($final, 255, 255, 255, 127);
      imagefilledrectangle($final, 0, 0, $width, $height, $transparent);
    }
    
    if($startColor->isTransparent()) {
      if(!$endColor->isTransparent()) {
        $r = $endColor->r;
        $g = $endColor->g;
        $b = $endColor->b;
      } else
        $r = $g = $b = 0;
    } else {
      $r = $startColor->r;
      $g = $startColor->g;
      $b = $startColor->b;
    }
    
    $a = $startColor->getPhpAlpha();

    $isXAxis = $axis == 'x';

    if ($width == 0 || $height == 0) {
      if(!$hasAlpha)
        imagefill($final, 0, 0, imagecolorallocate($final, $r, $g, $b));
    } else {
      if($endColor->isTransparent()) {
        if(!$startColor->isTransparent()) {
          $endR = $startColor->r;
          $endG = $startColor->g;
          $endB = $startColor->b;
        } else
          $endR = $endG = $endB = 0;
        
      } else {
        $endR = $endColor->r;
        $endG = $endColor->g;
        $endB = $endColor->b;
      }

      $endA = $endColor->getPhpAlpha();

      $incR = $endR - $r;
      $incG = $endG - $g;
      $incB = $endB - $b;
      $incA = $endA - $a;

      $absDisR = abs($incR);
      $absDisG = abs($incG);
      $absDisB = abs($incB);
      $absDisA = abs($incA);

      $distance = max($absDisR, $absDisG, $absDisB, $absDisA);

      if ($distance == 0) {
        if(!$hasAlpha)
          imagefill($final, 0, 0, imagecolorallocate($final, $r, $g, $b));
      } else {
        if($isXAxis) {
          if($distance > $width) $distance = $width;
          $sliver = imagecreatetruecolor($distance, 1);
        } else {
          if($distance > $height) $distance = $height;
          $sliver = imagecreatetruecolor(1, $distance);
        }

        $sliverMax = $distance - 1;

        $incA /= $sliverMax;
        $incR /= $sliverMax;
        $incG /= $sliverMax;
        $incB /= $sliverMax;

        $style = array();

        if($hasAlpha) {
          imagealphablending($sliver, false);
          imagesavealpha($sliver, true);
        }

        for ($t = 0; $t < $distance; $t++) {
          if($hasAlpha) {
            $style[$t] = imagecolorallocatealpha($sliver, $r, $g, $b, $a);
            $a += $incA;
          }	else
            $style[$t] = imagecolorallocate($sliver, $r, $g, $b);

          $r += $incR;
          $g += $incG;
          $b += $incB;
        }
        
        imagesetstyle($sliver, $style);

        if($isXAxis) {
          imageline($sliver, 0, 0, $sliverMax ,0, IMG_COLOR_STYLED);
          imagecopyresized($final, $sliver, 0, 0, 0, 0, $width, $height, $distance, 1);
        } else {
          imageline($sliver, 0, 0, 0, $sliverMax, IMG_COLOR_STYLED);
          imagecopyresized($final, $sliver, 0, 0, 0, 0, $width, $height, 1, $distance);
        }

        imagedestroy($sliver);
      }
    }
    return $final;
  }
}
