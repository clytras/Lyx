<?php

namespace Lyx\Geom;

class Size
{
  public $width;
  public $height;

  public function __construct()
  {
    $this->resetSize();

    $ac = func_num_args(); // arguments number
    $av = func_get_args(); // arguments array
    
    if($ac == 1) {
      if(is_array($av[0])) {
        $av = $av[0];
        $an = count($av);
      } elseif(is_object($av[0]) && get_class($av[0]) == __CLASS__) {
        $av = array($av[0]->width, $av[0]->height);
        $ac = count($av);
      }
    }

    if($ac == 2)
      $this->setSize($av[0], $av[1]);
    elseif($ac == 1)
      $this->setSize($av[0], $av[0]);
  }

  public function resetSize()
  {
    $this->setSize(0, 0);
  }

  public function setSize($width, $height)
  {
    if($width !== NULL) $this->width = $width;
    if($height !== NULL) $this->height = $height;
  }

  public function setWidth($width)
  {
    $this->width = $width;
  }

  public function getWidth()
  {
    return $this->width;
  }

  public function setHeight($height)
  {
    $this->height = $height;
  }

  public function getHeight()
  {
    return $this->height;
  }

  public function getSize()
  {
    return [
      'width' => $this->width,
      'height' => $this->height
    ];
  }

  public function __toString()
  {
    return "Width: {$this->width}, Height: {$this->height}";
  }
}
