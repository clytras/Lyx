<?php

namespace Lyx\Geom;

class Rect
{
  public $x;
  public $y;
  public $width;
  public $height;

  public function __construct()
  {
    $this->resetRect();

    $an = func_num_args(); // arguments number
    $aa = func_get_args(); // arguments array
    
    if($an == 1 && is_array($aa[0])) {
      $aa = $aa[0];
      $an = count($aa);
    }

    if($an == 4)
      $this->setRect($aa[0], $aa[1], $aa[2], $aa[3]);
    elseif($an == 2)
      $this->setRect(0, 0, $aa[0], $aa[1]);
    elseif($an == 1)
      $this->setRect(0, 0, 0, 0);
  }

  public function resetRect()
  {
    $this->setRect(0, 0, 0, 0);
  }

  public function setRect($x, $y, $w, $h)
  {
    $this->x = $x;
    $this->y = $y;
    $this->width = $w;
    $this->height = $h;
  }

  public function setSize($width, $height)
  {
    if($width !== NULL) $this->width = $width;
    if($height !== NULL) $this->height = $height;
  }

  public function setPoint($x, $y)
  {
    if($x !== NULL) $this->x = $x;
    if($y !== NULL) $this->y = $y;
  }

  public function setWidth($width)
  {
    $this->setSize($width, NULL);
  }

  public function setHeight($height)
  {
    $this->setSize(NULL, $height);
  }

  public function setX($x)
  {
    $this->setPoint($x, NULL);
  }

  public function setY($y)
  {
    $this->setPoint(NULL, $y);
  }

  public function getSize()
  {
    return [
      'width' => $this->width,
      'height' => $this->height
    ];
  }

  public function getPoint()
  {
    return [
      'x' => $this->x,
      'y' => $this->y
    ];
  }

  public function getX()
  {
    return $this->x;
  }

  public function getY()
  {
    return $this->y;
  }

  public function getWidth()
  {
    return $this->width;
  }

  public function getHeight()
  {
    return $this->height;
  }

  public function getLeft()
  {
    return $this->x;
  }

  public function getTop()
  {
    return $this->y;
  }

  public function getRight()
  {
    return $this->x + $this->width;
  }

  public function getBottom()
  {
    return $this->y + $this->height;
  }

  public function getTotalWidth()
  {
    return $this->x + $this->width;
  }

  public function setTotalWidth($totalwidth)
  {
    $this->width = $totalwidth - $this->x;
  }

  public function getTotalHeight()
  {
    return $this->y + $this->height;
  }

  public function setTotalHeight($totalheight)
  {
    $this->height = $totalheight - $this->y;
  }
}
