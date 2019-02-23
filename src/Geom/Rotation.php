<?php

namespace Lyx\Geom;

final class Rotation
{
  public static function rotateX($x, $y, $radians)
  {
    return $x * cos($radians) - $y * sin($radians);
  }

  public static function rotateY($x, $y, $radians)
  {
    return $x * sin($radians) + $y * cos($radians);
  }

  public static function getRotBoxBounds($width, $height, $angle /* degrees */)
  {
    $radians = deg2rad($angle);

    $tmp = [
      self::rotateX(0, 0, 0-$radians),
      self::rotateX($width, 0, 0-$radians),
      self::rotateX(0, $height, 0-$radians),
      self::rotateX($width, $height, 0-$radians)
    ];
    $minX = round(min($tmp), 2);
    $maxX = round(max($tmp), 2);
    $rw = ceil($maxX) - floor($minX);

    $tmp = [
      self::rotateY(0, 0, 0-$radians),
      self::rotateY($width, 0, 0-$radians),
      self::rotateY(0, $height, 0-$radians),
      self::rotateY($width, $height, 0-$radians)
    ];
    $minY = round(min($tmp), 2);
    $maxY = round(max($tmp), 2);
    $rh = ceil($maxY) - floor($minY);

    return (object)['width' => $rw, 'height' => $rh];
  }
}
