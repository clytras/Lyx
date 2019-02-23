<?php

namespace Lyx\Geom;

class Point
{
  public $x;
  public $y;

  public function __construct()
  {
    $this->resetPoint();

    $ac = func_num_args(); // arguments number
    $av = func_get_args(); // arguments array
    
    if($ac == 1) {
      if(is_array($av[0])) {
        $av = $av[0];
        $an = count($av);
      } elseif(is_object($av[0]) && get_class($av[0]) == __CLASS__) {
        $av = array($av[0]->x, $av[0]->y);
        $ac = count($av);
      }
    }

    if($ac == 2)
      $this->setPoint($av[0], $av[1]);
    elseif($ac == 1)
      $this->setPoint($av[0], $av[0]);
  }

  function resetPoint()
  {
    $this->setPoint(0, 0);
  }

  function setPoint($x, $y)
  {
    if($x !== NULL) $this->x = $x;
    if($y !== NULL) $this->y = $y;
  }

  function setX($x)
  {
    $this->x = $x;
  }

  function setY($y)
  {
    $this->y = $y;
  }

  function getPoint()
  {
    return [
      'x' => $this->x,
      'y' => $this->y
    ];
  }

  function __toString()
  {
    return "X: {$this->x}, Y: {$this->y}";
  }
}
