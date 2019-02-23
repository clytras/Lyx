<?php

namespace Lyx\Math;

class BCMath
{
  public static function bcceil($number)
  {
    if(strpos($number, '.') !== false) {
      if($number[0] != '-')
        return bcadd($number, 1, 0);

      return bcsub($number, 0, 0);
    }

    return $number;
  }

  public static function bcfloor($number)
  {
    if(strpos($number, '.') !== false) {
      if($number[0] != '-')
        return bcadd($number, 0, 0);

      return bcsub($number, 1, 0);
    }

    return $number;
  }

  public static function bcround($number, $precision = 0)
  {
    if(strpos($number, '.') !== false) {
      if($number[0] != '-')
        return bcadd($number, '0.' . str_repeat('0', $precision) . '5', $precision);

      return bcsub($number, '0.' . str_repeat('0', $precision) . '5', $precision);
    }

    return $number;
  }
}
