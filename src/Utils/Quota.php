<?php

namespace Lyx\Utils;

class Quota
{
  public $source,
    $value,
    $type,
    $bounds;

  const PERCENT = 1,
    PERMILLE = 2,
    PERTENTHOUSAND = 3,
    FRACTION = 4,
    DEFAULT_TYPE = self::PERCENT;

  const PERCENT_SIGN = '&#37;',
    PERMILLE_SIGN = '&#8240;',
    PERTENTHOUSAND_SIGN = '&#8241;';

  const PERCENT_MIN = 0, PERCENT_MAX = 100,
    PERMILLE_MIN = 0, PERMILLE_MAX = 1000,
    PERTENTHOUSAND_MIN = 0, PERTENTHOUSAND_MAX = 10000,
    FRACTION_MIN = 0.0, FRACTION_MAX = 1.0;

  const BOUNDS_FAIL = 1, // returns FALSE if quota is out of bounds
    BOUNDS_IN = 2, // limits the bounds to MIN if < MIN and MAX if > MAX
    BOUNDS_OUT = 3, // bounds are free ex.: PERCENT can have an 120% quota
    DEFAULT_BOUNDS = self::BOUNDS_IN;

  private static $type_names = [
    self::PERCENT => 'Percent',
    self::PERMILLE => 'Per mille',
    self::PERTENTHOUSAND => 'Per ten thousand',
    self::FRACTION => 'Fraction'
  ];

  private static $bound_names = [
    self::BOUNDS_FAIL => 'Bounds fail',
    self::BOUNDS_IN => 'Bounds in',
    self::BOUNDS_OUT => 'Bounds out'
  ];

  public function __construct()
  {
    $this->init();

    if(func_num_args() > 0) {
      if(func_num_args() >= 3)
        $bounds = func_get_arg(2);
      else
        $bounds = false;
      
      if(func_num_args() >= 2)
        $type = func_get_arg(1);
      else
        $type = false;
      
      $this->setQuota(func_get_arg(0), $type, $bounds);
    }
  }

  public function init()
  {
    $this->resetQuota();
    $this->type = self::DEFAULT_TYPE;
    $this->bounds = self::DEFAULT_BOUNDS;
  }

  public function resetQuota()
  {
    $this->value = 0;
    $this->source = '';
  }

  public function setQuota($value, $type = false, $bounds = false)
  {
    $this->resetQuota();

    if($bounds === false || $bounds === null)
      $bounds = $this->bounds;
    else
      $this->bounds = $bounds;

    if($type === false || $type === null)
      $type = self::scanQuotaType($value, $bounds);
    else
      $this->type = $type;

    if($type !== false) {
      $qv = self::getQuotaValue($value, $type, $bounds);
      if($qv !== false) {
        $this->source = $value;
        $this->type = $type;
        $this->value = $qv;
        $this->bounds = $bounds;
      }
    }
  }

  public function getQuota()
  {
    return $this->toQuota();
  }

  public function toQuota($format = '')
  {
    return self::quotaToString($this->value, $this->type, $format);
  }

  public function toString()
  {
    return "Source: {$this->source}, Value: {$this->value}, ".
      "Type: ".self::typeName($this->type).", ".
      "Bounds: ".self::boundsName($this->bounds);
  }

  public function getTypeName()
  {
    return self::typeName($this->type);
  }

  public function getBoundName()
  {
    return self::boundsName($this->bounds);
  }

  public function setValue($value)
  {
    $ret = false;

    if(is_numeric($value)) {
      if(self::isFraction($value))
        $value = floatval($value);
      else
        $value = intval(value);
      
      $value = self::boundValue($value,
        self::getTypeMin($this->type),
        self::getTypeMax($this->type),
        $this->bounds);

      if($value !== false) {
        $this->value = $value;
        $ret = true;
      }
    }
    
    return $ret;
  }

  public function getValue()
  {
    return $this->value;
  }

  public function setType($type)
  {
    if($this->type == $type)
      return;
    
    $ret = false;

    $bv = self::boundValue($this->value,
      self::getTypeMin($type),
      self::getTypeMax($type),
      $this->bounds);

    if($bv !== false) {
      $this->type = $type;
      $ret = true;
    }
    
    return $ret;
  }

  public function getType()
  {
    return $this->type;
  }

  public function setBounds($bounds)
  {
    $ret = false;

    $value = self::boundValue($this->value,
      self::getTypeMin($this->type),
      self::getTypeMax($this->type),
      $bounds);

    if($value !== false) {
      $this->bounds = $bounds;
      $ret = true;
    }
    
    return $ret;
  }

  public function getBounds()
  {
    return $this->bounds;
  }

  public function setSource($value)
  {
    return $this->setQuota($value);
  }

  public function getSource()
  {
    return $this->source;
  }

  public function translate($quotafrom)
  {
    return self::translateQuota($this->value,
      $quotafrom,
      $this->type,
      $this->bounds);
  }


  // Static methods ---------------------------------------------------------------------

  public static function scanQuotaType($value, $bounds = self::DEFAULT_BOUNDS)
  {
    $ret = false;
    $types = array(self::FRACTION,
      self::PERCENT,
      self::PERMILLE,
      self::PERTENTHOUSAND);
    
    foreach($types as $type) {
      if(self::isValidQuota($value, $type, $bounds) !== false) {
        $ret = $type;
        break;
      }
    }
    
    return $ret;
  }

  public static function isValidQuota($value, $type = self::DEFAULT_TYPE, $bounds = self::DEFAULT_BOUNDS)
  {
    switch($type) {
      case self::FRACTION: return self::isValidFraction($value, $bounds);
      case self::PERCENT: return self::isValidPercent($value, $bounds);
      case self::PERMILLE: return self::isValidPermille($value, $bounds);
      case self::PERTENTHOUSAND: return self::isValidPertenthousand($value, $bounds);
    }
    
    return false;
  }

  public static function getQuotaValue($value, $type = self::DEFAULT_TYPE, $bounds = self::DEFAULT_BOUNDS)
  {
    switch($type) {
      case self::FRACTION: return self::getFractionValue($value, $bounds);
      case self::PERCENT: return self::getPercentValue($value, $bounds);
      case self::PERMILLE: return self::getPermilleValue($value, $bounds);
      case self::PERTENTHOUSAND: return self::getPertenthousandValue($value, $bounds);
    }
    
    return false;
  }

  public static function isValidPercent($value, $bounds = self::BOUNDS_IN)
  {
    return self::getPercentValue($value, $bounds) !== false; 
  }

  public static function isValidPermille($value, $bounds = self::BOUNDS_IN)
  {
    return self::getPermilleValue($value, $bounds) !== false; 
  }

  public static function isValidPertenthousand($value, $bounds = self::DEFAULT_BOUNDS)
  {
    return self::getPertenthousandValue($value, $bounds) !== false; 
  }

  public static function isValidFraction($value, $bounds = self::BOUNDS_IN)
  {
    return self::getFractionValue($value, $bounds) !== false; 
  }

  public static function getPercentValue($value, $bounds = self::BOUNDS_IN)
  {
    return self::get_signed_value($value, self::getTypeSign(self::PERCENT), self::PERCENT_MIN, self::PERCENT_MAX, $bounds);
  }

  public static function getPermilleValue($value, $bounds = self::BOUNDS_IN)
  {
    return self::get_signed_value($value, self::getTypeSign(self::PERMILLE), self::PERMILLE_MIN, self::PERMILLE_MAX, $bounds);
  }

  public static function getPertenthousandValue($value, $bounds = self::BOUNDS_IN)
  {
    return self::get_signed_value($value, self::getTypeSign(self::PERTENTHOUSAND), self::PERTENTHOUSAND_MIN, self::PERTENTHOUSAND_MAX, $bounds);
  }

  public static function getFractionValue($value, $bounds = self::DEFAULT_BOUNDS)
  {
    if(self::isFraction($value))
      return self::boundValue(floatval($value), self::FRACTION_MIN, self::FRACTION_MAX, $bounds);
    return false;
  }

  public static function getTypeSign($type)
  {
    $sign = '';

    switch($type) {
      case self::PERCENT:
        $sign = html_entity_decode(self::PERCENT_SIGN);
        break;
      case self::PERMILLE:
        $sign = html_entity_decode(self::PERMILLE_SIGN);
        break;
      case self::PERTENTHOUSAND:
        $sign = html_entity_decode(self::PERTENTHOUSAND_SIGN);
        break;
    }
    
    return $sign;
  }

  public static function hasSign($value, $type = self::DEFAULT_TYPE)
  {
    $ret = false;
    if(is_string($value)) {
      $sgn = self::getTypeSign($type);
      $ret = substr($value, -strlen($sgn)) == $sgn;
    }
    return $ret;
  }

  public static function isFraction($value)
  {
    $ret = false;
    if(is_string($value)) {
      if(is_numeric($value))
        $ret = strpos($value, '.') !== false;
    }
    else
      $ret = is_float($value);
    
    return $ret;
  }

  public static function signToType($value)
  {
    $ret = false;
    
    $types = array(self::PERCENT,
      self::PERMILLE,
      self::PERTENTHOUSAND);

    foreach($types as $type) {
      if(self::hasSign($value, $type)) {
        $ret = $type;
        break;
      }
    }
    
    return $ret;
  }

  public static function isTypeSignend($type)
  {
    return $type == self::PERCENT || $type == self::PERMILLE || $type == self::PERTENTHOUSAND;
  }

  private static function get_signed_value($value, $sgn, $min, $max, $bounds = self::DEFAULT_BOUNDS)
  {
    if(is_string($value)) {
      if(substr($value, -strlen($sgn)) == $sgn) {
        $value = substr($value, 0, -strlen($sgn));
      }
    }
    
    if(is_numeric($value))
      return self::boundValue((float)$value, $min, $max, $bounds);

    return false;
  }

  public static function getTypeMin($type)
  {
    switch($type) {
      case self::FRACTION: return self::FRACTION_MIN;
      case self::PERCENT: return self::PERCENT_MIN;
      case self::PERMILLE: return self::PERMILLE_MIN;
      case self::PERTENTHOUSAND: return self::PERTENTHOUSAND_MIN;
    }
    return 0;
  }

  public static function getTypeMax($type)
  {
    switch($type) {
      case self::FRACTION: return self::FRACTION_MAX;
      case self::PERCENT: return self::PERCENT_MAX;
      case self::PERMILLE: return self::PERMILLE_MAX;
      case self::PERTENTHOUSAND: return self::PERTENTHOUSAND_MAX;
    }
    return 0;
  }

  public static function quotaToString($value, $type, $format = '')
  {
    if(strlen($format) > 0)
      $value = sprintf($format, $value);
    else
      $value = (string)$value;
    
    $sgn = self::getTypeSign($type);
    if(strlen($sgn) > 0 && substr(trim($value), -1) != $sgn)
      $value .= $sgn;

    return $value;
  }

  public static function boundValue($value, $min, $max, $bounds)
  {
    $ret = false;
    if($bounds == self::BOUNDS_IN || $bounds == self::BOUNDS_FAIL) {
      if($value >= $min && $value <= $max)
        $ret = $value;
      elseif($bounds == self::BOUNDS_IN) {
        if($value < $min)
          $ret = $min;
        elseif($value > $max)
          $ret = $max;
      }
    }
    else
      $ret = $value;
    
    return $ret;
  }

  public static function translateQuota($value, $quotafrom, $type, $bounds)
  {
    $qt = self::getQuotaValue($value, $type, $bounds);
    if($qt !== false)
      return (((float)$qt) / self::getTypeMax($type)) * ((float)$quotafrom);
    else
      return false;
  }

  public static function quotaOrNumber($value, $quotafrom, $default, $type = null, $bounds = null)
  {
    $ret = false;

    if($bounds === null)
      $bounds = self::DEFAULT_BOUNDS;
    
    if($type === null)
      $type = self::scanQuotaType($value, $bounds);
    
    if($type !== false)
      if(self::isValidQuota($value, $type, $bounds))
        $ret = self::translateQuota($value, $quotafrom, $type, $bounds);
    
    if($ret === false) {
      if(is_numeric($value))
        $ret = (float)$value;
      else
        $ret = $default;
    }
    
    return $ret;
  }

  public static function signOrNumber($value, $quotafrom, $default, $type = null, $bounds = null)
  {
    $ret = false;

    if($bounds === null)
      $bounds = self::DEFAULT_BOUNDS;
    
    if($type === null)
      $type = self::scanQuotaType($value, $bounds);

    if($type !== false && $type !== self::FRACTION)
      if(self::hasSign($value, $type))
        if(self::isValidQuota($value, $type, $bounds))
          $ret = self::translateQuota($value, $quotafrom, $type, $bounds);

    if($ret === false) {
      if(is_numeric($value))
        $ret = (float)$value;
      else
        $ret = $default;
    }
    
    return $ret;
  }

  public static function fractionOrSignOrNumber($value, $quotafrom, $default, $type = null, $bounds = null)
  {
    $ret = false;

    if($bounds === null)
      $bounds = self::DEFAULT_BOUNDS;
    
    if(self::isFraction($value))
      $type = self::FRACTION;
    elseif($type === null)
      $type = self::scanQuotaType($value, $bounds);

    if($type === self::FRACTION) {
      if(self::isValidFraction($value, $bounds))
        $ret = self::translateQuota($value, $quotafrom, $type, $bounds);
    } elseif(self::hasSign($value, $type))
      if(self::isValidQuota($value, $type, $bounds))
        $ret = self::translateQuota($value, $quotafrom, $type, $bounds);

    if($ret === false) {
      if(is_numeric($value))
        $ret = (float)$value;
      else
        $ret = $default;
    }
    
    return $ret;
  }

  public static function typeName($type)
  {
    return self::$type_names[$type];
  }

  public static function boundsName($bound)
  {
    return self::$bound_names[$bound];
  }

  // public static function getPercent($value, $type = self::DEFAULT_TYPE)
  // {
  //   if(substr(trim($value), -1) == '%')
  //     return substr(trim($value), 0, -1);
  //   else
  //     return $value;
  // }

  // public static function percentOrNumber($value, $percentfrom, $default, $type = self::DEFAULT_TYPE)
  // {
  //   if(self::isPercent($value))
  //     return self::translatePercent($value, $percentfrom, $round, $type);
  //   elseif(is_numeric($value))
  //     return (float)$value;
  //   else
  //     return $default;
  // }

  // public static function translatePercent($value, $percentfrom, $type = self::DEFAULT_TYPE)
  // {
  //   $retval = (((float)self::getPercent($value, $type)) / 100) * ((float)$percentfrom);
  //   return $retval;
  // }
}
