<?php

namespace Lyx\Graphics;

//lximport('utils.quota');

use Lyx\Utils\Quota;


// RGB <=> #RRGGBB
// RGBA <=> #AARRGGBB
// Alpha >= 00 && <= 7F

// Color class

class Color
{
  public $red,
    $green,
    $blue,
    $alpha,
    $r,
    $g,
    $b,
    $a;

  private $color;

  const RGB_MIN = 0x00,
    RGB_MAX = 0xFF,
    ALPHA_MIN = 0x00, // TRANSPARENT
    ALPHA_MAX = 0xFF, // OPAQUE
    HUE_MIN = 0,
    HUE_MAX = 360,
    SLA_MIN = 0, // 0%
    SLA_MAX = 100, // 100%
    OPACITY_MIN = Quota::FRACTION_MAX, // OPAQUE
    OPACITY_MAX = Quota::FRACTION_MIN, // TRANSPARENT
    PHP_ALPHA_MIN = 0, // OPAQUE
    PHP_ALPHA_MAX = 0x7F, // TRANSPARENT
    PHP_TRANSPARENT = self::PHP_ALPHA_MAX,
    PHP_OPAQUE = self::PHP_ALPHA_MIN;

  const CSS_RGB_PATTERN  = "/^rgb\(\s*([0-9]{1,3}\%?)\s*,\s*([0-9]{1,3}\%?)\s*,\s*([0-9]{1,3}\%?)\s*\)$/i",
    CSS_RGBA_PATTERN = "/^rgba\(\s*([0-9]{1,3}\%?)\s*,\s*([0-9]{1,3}\%?)\s*,\s*([0-9]{1,3}\%?)\s*,\s*(0|1|0?\.[0-9]+|[0-9]{1,3}\%?)\s*\)$/i",
    CSS_HSL_PATTERN  = "/^hsl\(\s*([0-9]{1,3}\%?|0?\.[0-9]+)\s*,\s*(0|1|0?\.[0-9]+|[0-9]{1,3}\%?)\s*,\s*(0|1|0?\.[0-9]+|[0-9]{1,3}\%?)\s*\)$/i",
    CSS_HSLA_PATTERN = "/^hsla\(\s*([0-9]{1,3}\%?|0?\.[0-9]+)\s*,\s*(0|1|0?\.[0-9]+|[0-9]{1,3}\%?)\s*,\s*(0|1|0?\.[0-9]+|[0-9]{1,3}\%?)\s*,\s*(0|1|0?\.[0-9]+|[0-9]{1,3}\%?)\s*\)$/i",
    CSS_TRANSPARENT_PATTENR = "/^(transparent|alpha)$/i", // 'alpha' is not a w3c standard
    HEX_PATTERN = "/^#?|0x?[a-fA-F0-9]+$/i";

  const CSS_HEX_SIGN = '#';

  const BINARY = 1,
    RGB = 2,
    RGBA = 3,
    HSL = 4,
    HSLA = 5,
    HEX = 6,
    HEXA = 7,
    CSS_HEX = 8,
    CSS_HEXA = 9,
    CSS_NAME = 10,
    CSS_RGB = 11,
    CSS_RGBA = 12,
    CSS_HSL = 13,
    CSS_HSLA = 14,
    TRANSPARENT = 15;

  public static $COLOR_NAMES  =  [ 
    //  Colors  as  they  are  defined  in  HTML  3.2 
    "black"   => '000000', 
    "maroon"  => '800000', 
    "green"   => '008000', 
    "olive"   => '808000', 
    "navy"    => '000080', 
    "purple"  => '800080', 
    "teal"    => '008080', 
    "gray"    => '808080', 
    "silver"  => 'C0C0C0', 
    "red"     => 'FF0000', 
    "lime"    => '00FF00', 
    "yellow"  => 'FFFF00', 
    "blue"    => '0000FF', 
    "fuchsia" => 'FF00FF', 
    "aqua"    => '00FFFF', 
    "white"   => 'FFFFFF', 

    //  Additional  colors  as  they  are  used  by  Netscape  and  IE 
    "aliceblue"             => 'F0F8FF', 
    "antiquewhite"          => 'FAEBD7', 
    "aquamarine"            => '7FFFD4', 
    "azure"                 => 'F0FFFF', 
    "beige"                 => 'F5F5DC', 
    "blueviolet"            => '8A2BE2', 
    "brown"                 => 'A52A2A', 
    "burlywood"             => 'DEB887', 
    "cadetblue"             => '5F9EA0', 
    "chartreuse"            => '7FFF00', 
    "chocolate"             => 'D2691E', 
    "coral"                 => 'FF7F50', 
    "cornflowerblue"        => '6495ED', 
    "cornsilk"              => 'FFF8DC', 
    "crimson"               => 'DC143C', 
    "darkblue"              => '00008B', 
    "darkcyan"              => '008B8B', 
    "darkgoldenrod"         => 'B8860B', 
    "darkgray"              => 'A9A9A9', 
    "darkgreen"             => '006400', 
    "darkkhaki"             => 'BDB76B', 
    "darkmagenta"           => '8B008B', 
    "darkolivegreen"        => '556B2F', 
    "darkorange"            => 'FF8C00', 
    "darkorchid"            => '9932CC', 
    "darkred"               => '8B0000', 
    "darksalmon"            => 'E9967A', 
    "darkseagreen"          => '8FBC8F', 
    "darkslateblue"         => '483D8B', 
    "darkslategray"         => '2F4F4F', 
    "darkturquoise"         => '00CED1', 
    "darkviolet"            => '9400D3', 
    "deeppink"              => 'FF1493', 
    "deepskyblue"           => '00BFFF', 
    "dimgray"               => '696969', 
    "dodgerblue"            => '1E90FF', 
    "firebrick"             => 'B22222', 
    "floralwhite"           => 'FFFAF0', 
    "forestgreen"           => '228B22', 
    "gainsboro"             => 'DCDCDC', 
    "ghostwhite"            => 'F8F8FF', 
    "gold"                  => 'FFD700', 
    "goldenrod"             => 'DAA520', 
    "greenyellow"           => 'ADFF2F', 
    "honeydew"              => 'F0FFF0', 
    "hotpink"               => 'FF69B4', 
    "indianred"             => 'CD5C5C', 
    "indigo"                => '4B0082', 
    "ivory"                 => 'FFFFF0', 
    "khaki"                 => 'F0E68C', 
    "lavender"              => 'E6E6FA', 
    "lavenderblush"         => 'FFF0F5', 
    "lawngreen"             => '7CFC00', 
    "lemonchiffon"          => 'FFFACD', 
    "lightblue"             => 'ADD8E6', 
    "lightcoral"            => 'F08080', 
    "lightcyan"             => 'E0FFFF', 
    "lightgoldenrodyellow"  => 'FAFAD2', 
    "lightgreen"            => '90EE90', 
    "lightgrey"             => 'D3D3D3', 
    "lightpink"             => 'FFB6C1', 
    "lightsalmon"           => 'FFA07A', 
    "lightseagreen"         => '20B2AA', 
    "lightskyblue"          => '87CEFA', 
    "lightslategray"        => '778899', 
    "lightsteelblue"        => 'B0C4DE', 
    "lightyellow"           => 'FFFFE0', 
    "limegreen"             => '32CD32', 
    "linen"                 => 'FAF0E6', 
    "mediumaquamarine"	  	=> '66CDAA', 
    "mediumblue"            => '0000CD', 
    "mediumorchid"          => 'BA55D3', 
    "mediumpurple"          => '9370D0', 
    "mediumseagreen"        => '3CB371', 
    "mediumslateblue"       => '7B68EE', 
    "mediumspringgreen"     => '00FA9A', 
    "mediumturquoise"       => '48D1CC', 
    "mediumvioletred"       => 'C71585', 
    "midnightblue"          => '191970', 
    "mintcream"             => 'F5FFFA', 
    "mistyrose"             => 'FFE4E1', 
    "moccasin"              => 'FFE4B5', 
    "navajowhite"           => 'FFDEAD', 
    "oldlace"               => 'FDF5E6', 
    "olivedrab"             => '6B8E23', 
    "orange"                => 'FFA500', 
    "orangered"             => 'FF4500', 
    "orchid"                => 'DA70D6', 
    "palegoldenrod"         => 'EEE8AA', 
    "palegreen"             => '98FB98', 
    "paleturquoise"         => 'AFEEEE', 
    "palevioletred"         => 'DB7093', 
    "papayawhip"            => 'FFEFD5', 
    "peachpuff"             => 'FFDAB9', 
    "peru"                  => 'CD853F', 
    "pink"                  => 'FFC0CB', 
    "plum"                  => 'DDA0DD', 
    "powderblue"            => 'B0E0E6', 
    "rosybrown"             => 'BC8F8F', 
    "royalblue"             => '4169E1', 
    "saddlebrown"           => '8B4513', 
    "salmon"                => 'FA8072', 
    "sandybrown"            => 'F4A460', 
    "seagreen"              => '2E8B57', 
    "seashell"              => 'FFF5EE', 
    "sienna"                => 'A0522D', 
    "skyblue"               => '87CEEB', 
    "slateblue"             => '6A5ACD', 
    "slategray"             => '708090', 
    "snow"                  => 'FFFAFA', 
    "springgreen"           => '00FF7F', 
    "steelblue"             => '4682B4', 
    "tan"                   => 'D2B48C', 
    "thistle"               => 'D8BFD8', 
    "tomato"                => 'FF6347', 
    "turquoise"             => '40E0D0', 
    "violet"                => 'EE82EE', 
    "wheat"                 => 'F5DEB3', 
    "whitesmoke"            => 'F5F5F5', 
    "yellowgreen"           => '9ACD32'
  ];

  public function __construct()
  {
    $this->init();
    $colorarray = NULL;

    if(func_num_args() == 1) {
      $this->color = func_get_arg(0);
      $colorarray = self::colorToArray($this->color);
    } elseif(func_num_args() == 3) {
      $this->color = func_get_args();
      $colorarray = self::rgbToArray($this->color[0], $this->color[1], $this->color[2]);
    } elseif(func_num_args() == 4) {
      $this->color = func_get_args();
      $colorarray = self::rgbaToArray($this->color[0], $this->color[1], $this->color[2], $this->color[3]);
    }
    
    if(is_array($colorarray) && count($colorarray) == 3)
      list($this->red, $this->green, $this->blue) = array_values($colorarray);
    elseif(count((array)$colorarray) == 4)
      list($this->red, $this->green, $this->blue, $this->alpha) = array_values($colorarray);
  }

  private function init()
  {
    $this->r =& $this->red;
    $this->g =& $this->green;
    $this->b =& $this->blue;
    $this->a =& $this->alpha;
    $this->resetColor();
  }

  public function getRed() { return $this->red; }
  public function setRed($value) { $this->red = self::between($value, self::RGB_MIN, self::RGB_MAX); return $this; }
  public function getGreen() { return $this->green; }
  public function setGreen($value) { $this->green = self::between($value, self::RGB_MIN, self::RGB_MAX); return $this; }
  public function getBlue() { return $this->blue; }
  public function setBlue($value) { $this->blue = self::between($value, self::RGB_MIN, self::RGB_MAX); return $this; }
  public function getAlpha() { return $this->alpha; }
  public function setAlpha($value) { $this->alpha = self::between($value, self::ALPHA_MIN, self::ALPHA_MAX); return $this; }
  public function setRgb($r, $g, $b)
  {
    $this->setRed($r);
    $this->setGreen($g);
    $this->setBlue($b);
  }
  public function getPhpAlpha()
  {
    if($this->alpha === NULL)
      return self::PHP_OPAQUE;
    else
      return self::alphaToPhpAlpha($this->alpha);
  }
  public function hasAlpha() { return $this->alpha !== NULL; }
  public function isTransparent()
  {
    return $this->red === NULL &&
      $this->green === NULL &&
      $this->blue === NULL &&
      $this->alpha !== NULL;
  }
  public function resetAlpha() { $this->alpha = NULL; return $this; }

  public function getOpacity()
  {
    if($this->alpha === NULL)
      $a = self::ALPHA_MAX;
    else
      $a = $this->alpha;

    return self::alphaToOpacity($a);
  }

  public function setOpacity($opacity)
  {
    $opacity = (float)$opacity;
    
    if($opacity >= Quota::FRACTION_MIN &&
        $opacity <= Quota::FRACTION_MAX)
    {
      $this->alpha = self::opacityToAlpha($opacity);
    }
    
    return $this;
  }

  public function resetColor()
  {
    $this->red = self::RGB_MIN;
    $this->green = self::RGB_MIN;
    $this->blue = self::RGB_MIN;
    $this->alpha = NULL;
  }

  public function toBinary($forcealpha = FALSE)
  {
    if($this->alpha === NULL && $forcealpha)
      $a = self::ALPHA_MAX;
    else
      $a = $this->alpha;

    return self::rgbaToBinary($this->red, $this->green, $this->blue, $a);
  }

  public function toHex($removealpha = TRUE)
  {
    if($removealpha)
      $a = NULL;
    else
      $a = $this->alpha;
    
    return self::rgbaToHexa($this->red, $this->green, $this->blue, $a);
  }

  public function toHexa($forcealpha = FALSE)
  {
    if($this->alpha === NULL && $forcealpha)
      $a = self::ALPHA_MAX;
    else
      $a = $this->alpha;
    
    return self::rgbaToHexa($this->red, $this->green, $this->blue, $a);
  }

  public function toCssHex($removealpha = TRUE)
  {
    return self::CSS_HEX_SIGN.$this->toHex($removealpha);
  }

  public function toCssHexa($forcealpha = FALSE)
  {
    return self::CSS_HEX_SIGN.$this->toHexa($forcealpha);
  }

  public function toCssRgb($removealpha = TRUE)
  {
    if($removealpha)
      $a = NULL;
    else
      $a = $this->alpha;

    return self::rgbaToCssRgba($this->red, $this->green, $this->blue, $a);
  }

  public function toCssRgba($forcealpha = FALSE)
  {
    if($this->alpha === NULL && $forcealpha)
      $a = self::ALPHA_MAX;
    else
      $a = $this->alpha;
      
    return self::rgbaToCssRgba($this->red, $this->green, $this->blue, $a);
  }

  public function toCssHsl($removealpha = TRUE)
  {
    if($removealpha)
      $a = NULL;
    else
      $a = $this->alpha;

    return self::rgbaToCssHsla($this->red, $this->green, $this->blue, $a);
  }

  public function toCssHsla($forcealpha = FALSE)
  {
    if($this->alpha === NULL && $forcealpha)
      $a = self::ALPHA_MAX;
    else
      $a = $this->alpha;

    return self::rgbaToCssHsla($this->red, $this->green, $this->blue, $a);
  }

  public function toCssName($ifnotfound = self::CSS_HEX)
  {
    return self::rgbToCssName($this->red, $this->green, $this->blue, $this->alpha, $ifnotfound);
  }

  public function toArray($keys = '' /* could be 'r,g,b' */)
  {
    return $this->toRgbArray($keys);
  }

  public function toRgbArray($keys = '')
  {
    return self::rgbaToArray($this->red, $this->green, $this->blue, $this->alpha, $keys, TRUE);
  }

  public function getRgbArray($keys = '', $round = FALSE)
  {
    return self::rgbaToArray($this->red, $this->green, $this->blue, $this->alpha, $keys, FALSE);
  }

  public function toHslArray($keys = '', $round = TRUE)
  {
    return self::rgbaToHsla($this->red, $this->green, $this->blue, $this->alpha);
  }

  public function cloneColor()
  {
    return clone $this;
  }

  public function __toString()
  {
    return $this->toRgbString();
  }

  public function toRgbString()
  {
    $ret = "Red: {$this->red}, Green: {$this->green}, Blue: {$this->blue}";
    
    if($this->alpha !== NULL)
      $ret .= ", Alpha: {$this->alpha}";
    
    return $ret;
  }

  public function toHslString($round = TRUE)
  {
    $hsl = (object)self::rgbToHsl(round($this->red),
                    round($this->green),
                    round($this->blue));
    
    if($round) {
      $hsl->h = (int)round($hsl->h);
      $hsl->s = (int)round($hsl->s);
      $hsl->l = (int)round($hsl->l);
    }
    
    $ret = "Hue: {$hsl->h}, Saturation: {$hsl->s}, Lightness: {$hsl->l}";
    

    if($this->alpha !== NULL)
      $ret .= sprintf(", Alpha: %.2f", $this->alpha / self::ALPHA_MAX);
    
    return $ret;
  }

  // Color names --------------------------------------------

  public static function isColorName($name)
  {
    return array_key_exists(strtolower($name), self::$COLOR_NAMES);
  }

  public static function nameToHex($color_name)
  {
    return self::$COLOR_NAMES[strtolower($color_name)];
  }

  public static function isNameColor($hexcolor)
  {
    return self::hexToName($hexcolor) !== FALSE;
  }

  public static function hexToName($hexcolor)
  {
    $hexcolor = self::hexColorClearSign($hexcolor);
    $hexcolor = self::hexColorExpand($hexcolor);
    
    return array_search(strtoupper($hexcolor), self::$COLOR_NAMES);
  }

  public static function detectColor(
    $color,
    $type = self::RGB,
    $forcealpha = FALSE,
    $round = TRUE
  ) {
    $ca = NULL;

    if(is_object($color) && get_class($color) == __CLASS__)
      $ca = $color->toArray();
    elseif(self::isColorName($color))
      $ca = self::hexToRgb(self::nameToHex($color));
    elseif(is_string($color))
      $ca = self::cssToRgb($color);
    elseif(is_numeric($color)) {
      if(intval($color) <= 0x00FFFFFF)
        $ca = self::binaryToRgb($color);
      else
        $ca = self::binaryToRgba($color);
    }
    elseif(is_array($color))
      $ca = $color;
    
    if(empty($ca) || !(is_array($ca) && count($ca) >= 3))
      $ca = array(self::RGB_MIN, self::RGB_MIN, self::RGB_MIN);

    $ca = array_values($ca);
    if($forcealpha) {
      if(count($ca) < 4)
        array_push($ca, self::ALPHA_MAX);
      elseif(count($ca) > 4)
        $ca = array_slice($ca, 0, 4);
    } elseif(count($ca) > 3) {
      if($type == self::RGB ||
          $type == self::HSL ||
          $type == self::CSS_HEX ||
          $type == self::CSS_RGB ||
          $type == self::CSS_HSL) // Dont use alpha value for types that do not specify alpha. Use types with alpha instead
      {
        $ca = array_slice($ca, 0, 3);
      }
    }
    
    return self::rgbaArrayToType($ca, $type, $round);
  }

  public static function rgbaToType(
    $r,
    $g,
    $b,
    $a = NULL,
    $type = self::RGB,
    $round = TRUE
  ) {
    $ca = array($r, $g, $b);
    if($a !== NULL)
      array_push($ca, $a);
    return self::rgbaArrayToType($ca, $type, $round);
  }

  public static function rgbaArrayToType(
    $ca,
    $type = self::RGB,
    $round = TRUE
  )	{
    $ret = NULL;
    if(is_array($ca) && count($ca) >= 3) {
      switch($type) {
        case BINARY:    $ret = self::rgbToBinary($ca); break;
        case RGB:
        case RGBA:      $ret = self::rgbaToArray($ca, NULL, NULL, NULL, NULL, $round); break;
        case HSL:
        case HSLA:      $ret = self::rgbaToHsla($ca, NULL, NULL, NULL, NULL, $round); break;
        case HEX:
        case HEXA:      $ret = self::rgbaToHex($ca); break;
        case CSS_HEX:
        case CSS_HEXA:  $ret = self::rgbaToCssHexa($ca); break;
        case CSS_NAME:  $ret = self::rgbToCssName($ca); break;
        case CSS_RGB:
        case CSS_RGBA:  $ret = self::rgbaToCssRgba($ca); break;
        case CSS_HSL:
        case CSS_HSLA:  $ret = self::rgbaToCssHsla($ca); break;
        default:        $ret = self::rgbaToArray($ca, NULL, NULL, NULL, NULL, $round); break;
      }
    }

    return $ret;
  }

  public static function colorToArray($color, $forcealpha = FALSE, $round = TRUE)
  {
    return self::detectColor($color, self::RGBA, $forcealpha, $round);
  }

  public static function colorToObject($color, $forcealpha = FALSE, $round = TRUE)
  {
    return (object)self::detectColor($color, self::RGBA, $forcealpha, $round);
  }

  public static function colorToBinary($color, $forcealpha = FALSE)
  {
    return self::detectColor($color, self::BINARY, $forcealpha);
  }

  public static function colorToRgb($color, $round = TRUE)
  {
    return self::detectColor($color, self::RGB, FALSE, $round);
  }

  public static function colorToRgba($color, $forcealpha = FALSE, $round = TRUE)
  {
    return self::detectColor($color, self::RGBA, $forcealpha, $round);
  }

  public static function colorToHex($color)
  {
    return self::detectColor($color, self::HEX);
  }

  public static function colorToHexa($color, $forcealpha = FALSE)
  {
    return self::detectColor($color, self::HEXA, $forcealpha);
  }

  public static function colorToCssHex($color)
  {
    return self::detectColor($color, self::CSS_HEX);
  }

  public static function colorToCssHexa($color, $forcealpha = FALSE)
  {
    return self::detectColor($color, self::CSS_HEXA, $forcealpha);
  }

  public static function colorToCssRgb($color)
  {
    return self::detectColor($color, self::CSS_RGB);
  }

  public static function colorToCssRgba($color, $forcealpha = FALSE)
  {
    return self::detectColor($color, self::CSS_RGBA, $forcealpha);
  }

  public static function colorToCssHsl($color)
  {
    return self::detectColor($color, self::CSS_HSL);
  }

  public static function colorToCssHsla($color, $forcealpha = FALSE)
  {
    return self::detectColor($color, self::CSS_HSLA, $forcealpha);
  }

  public static function colorToCssName($color)
  {
    return self::detectColor($color, self::CSS_NAME);
  }


  // Conversions ------------------------------------------------

  // RGBa > Binary
  public static function rgbToBinary($r, $g = 0, $b = 0)
  {
    return self::rgbaToBinary($r, $g, $b, NULL);
  }

  public static function rgbaToBinary($r, $g = 0, $b = 0, $a = NULL)
  {
    if(is_array($r)) {
      if(count($r) == 3)
        list($r, $g, $b) = array_values($r);
      elseif(count($r) == 4)
        list($r, $g, $b, $a) = array_values($r);
    }

    $r = intval($r);
    $g = intval($g);
    $b = intval($b);

    if($a !== NULL) {
      $a = intval($a);
      return $a << 24 | $r << 16 | $g << 8 | $b;
    }
    else
      return $r << 16 | $g << 8 | $b;
  }

  // RGBa > Object
  public static function rgbToObject($r, $g, $b, $keys = '', $round = FALSE)
  {
    return (object)self::rgbaToArray($r, $g, $b, NULL, $keys, $round);
  }

  public static function rgbaToObject($r, $g, $b, $a = NULL, $keys = '', $round = FALSE)
  {
    return (object)self::rgbaToArray($r, $g, $b, $a, $keys, $round);
  }

  // RGBa > Array
  public static function rgbToArray($r, $g, $b, $keys = '', $round = FALSE)
  {
    return self::rgbaToArray($r, $g, $b, NULL, $keys, $round);
  }

  public static function rgbaToArray($r, $g, $b, $a = NULL, $keys = '', $round = FALSE)
  {
    if(is_array($r)) {
      if(count($r) == 3)
        list($r, $g, $b) = array_values($r);
      elseif(count($r) == 4)
        list($r, $g, $b, $a) = array_values($r);
    }

    $hasalpha = $a !== NULL;
    $defkeys = 'r,g,b';
    if($hasalpha)
      $defkeys .= ',a';

    if(empty($keys))
      $keys_a = explode(',', $defkeys);
    else {
      $keys_a = explode(',', $keys);
      if(count($keys_a) != ($hasalpha ? 4 : 3))
        $keys_a = explode(',', $defkeys);
    }
    
    $r = $round && $r !== NULL ? round($r) : $r;
    $g = $round && $g !== NULL ? round($g) : $g;
    $b = $round && $b !== NULL ? round($b) : $b;

    $values = array($r, $g, $b);
    
    if($hasalpha)
      array_push($values, $a);

    return array_combine($keys_a, $values);
  }

  // RGBa > Hex
  public static function rgbToHex($r, $g = 0, $b = 0)
  {
    return self::rgbaToHexa($r, $g, $b, NULL);
  }

  public static function rgbaToHexa($r, $g = 0, $b = 0, $a = NULL)
  {
    if(is_array($r)) {
      if(count($r) == 3)
        list($r, $g, $b) = $r;
      elseif(count($r) == 4)
        list($r, $g, $b, $a) = $r;
    }

    $r = (int)round($r);
    $g = (int)round($g);
    $b = (int)round($b);
    
    if($a !== NULL) $a = intval($a);
    
    $r = self::between($r, self::RGB_MIN, self::RGB_MAX);
    $g = self::between($g, self::RGB_MIN, self::RGB_MAX);
    $b = self::between($b, self::RGB_MIN, self::RGB_MAX);
    if($a !== NULL) $a = self::between($a, self::ALPHA_MIN, self::ALPHA_MAX);

    $color = sprintf('%02X', $r).
          sprintf('%02X', $g).
          sprintf('%02X', $b);
    
    if($a !== NULL) $color = sprintf('%02X', $a).$color;
    return $color;
  }

  // RGBa > Css hex
  public static function rgbToCssHex($r, $g = 0, $b = 0)
  {
    return self::rgbaToHexa($r, $g, $b, NULL);
  }

  public static function rgbaToCssHexa($r, $g = 0, $b = 0, $a = NULL)
  {
    return self::CSS_HEX_SIGN.self::rgbaToHexa($r, $g, $b, $a);
  }

  // RGBa > Css rgb function
  public static function rgbToCssRgb($r, $g = 0, $b = 0)
  {
    return self::rgbaToCssRgba($r, $g, $b, NULL);
  }

  public static function rgbaToCssRgba($r, $g = 0, $b = 0, $a = NULL)
  {
    if(is_array($r)) {
      if(count($r) == 3)
        list($r, $g, $b) = array_values($r);
      elseif(count($r) == 4)
        list($r, $g, $b, $a) = array_values($r);
    }
    
    $ret = sprintf("(%s, %s, %s", 
      (int)round($r),
      (int)round($g),
      (int)round($b));
    
    if($a !== NULL) {
      $ret = 'rgba'.$ret.sprintf(", %.2f", floatval($a) / self::ALPHA_MAX);
      if(substr($ret, -1) == '0')
        $ret = substr($ret, 0, -1);
    } else
      $ret = 'rgb'.$ret;
    
    return $ret.")";
  }

  // RGBa > Css hsl function
  public static function rgbToCssHsl($r, $g = 0, $b = 0)
  {
    return self::rgbaToCssHsla($r, $g, $b, NULL);
  }

  public static function rgbaToCssHsla($r, $g = 0, $b = 0, $a = NULL)
  {
    if(is_array($r)) {
      if(count($r) == 3)
        list($r, $g, $b) = array_values($r);
      elseif(count($r) == 4)
        list($r, $g, $b, $a) = array_values($r);
    }

    $hsl = (object)self::rgbToHsl(
      round($r),
      round($g),
      round($b));
      
    return self::hslaToCssHsla($hsl->h, $hsl->s, $hsl->l, $a);
  }

  // RGBa > Css name
  public static function rgbToCssName($r, $g = 0, $b = 0, $a = NULL, $ifnotfound = self::CSS_HEX)
  {
    if(func_num_args() == 2 && is_array($r))
      $ifnotfound = func_get_arg(1);

    if(is_array($r)) {
      if(count($r) >= 3)
        list($r, $g, $b) = array_values($r);
    }

    $hex = self::rgbToHex($r, $g, $b);
    $name = self::hexToName($hex);
    
    if($name !== FALSE)
      return $name;
    else {
      switch($ifnotfound) {
        case CSS_RGB:
        case CSS_RGBA:
          return self::rgbaToCssRgba($r, $g, $b, $a);
        case CSS_HSL:
        case CSS_HSLA:
          return self::rgbaToCssHsla($r, $g, $b, $a);
        default: // CSS_HEX
          return self::rgbToCssHex($r, $g, $b, $a);
      }
    }
  }

  // HSLa > Array
  public static function hslToArray($h, $s, $l, $keys = '', $round = FALSE)
  {
    return self::hslaToArray($h, $s, $l, NULL, $keys, $round);
  }

  public static function hslaToArray($h, $s, $l, $a = NULL, $keys = '', $round = FALSE)
  {
    $hasalpha = $a !== NULL;
    $defkeys = 'h,s,l';
    if($hasalpha)
      $defkeys .= ',a';

    if(empty($keys))
      $keys = $defkeys;
    else {
      $keys_a = explode(',', $keys);
      if(count($keys_a) != ($hasalpha ? 4 : 3))
        $keys = $defkeys;
    }

    return self::rgbaToArray($h, $s, $l, $a, $keys, $round);
  }

  // HSLa > Css hsl function
  public static function hslToCssHsl($h, $s = 0, $l = 0)
  {
    return self::hslaToCssHsla($h, $s, $l);
  }

  public static function hslaToCssHsla($h, $s = 0, $l = 0, $a = NULL)
  {
    if(is_array($h)) {
      if(count($h) == 3)
        list($h, $s, $l) = array_values($h);
      elseif(count($h) == 4)
        list($h, $s, $l, $a) = array_values($h);
    }
    
    $ret = sprintf("(%s, %s, %s", intval(round($h)),
      Quota::quotaToString(intval(round($s)), Quota::PERCENT),
      Quota::quotaToString(intval(round($l)), Quota::PERCENT));
    
    if($a !== NULL) {
      $ret = 'hsla'.$ret.sprintf(", %.2f", floatval($a) / self::ALPHA_MAX);
      if(substr($ret, -1) == '0')
        $ret = substr($ret, 0, -1);
    } else
      $ret = 'hsl'.$ret;
    
    return $ret.")";
  }

  public static function cssToRgb($color)
  {
    return self::cssToRgba($color);
  }

  public static function cssToRgba($color)
  {
    $color = trim(strtolower($color));
    $ret = array();
    if(self::isCssTransparent($color))
      return self::rgbaToArray(NULL, NULL, NULL, self::ALPHA_MIN);
    elseif(self::isCssRgb($color, $ret))
      return $ret;
    elseif(self::isCssRgba($color, $ret))
      return $ret;
    elseif(self::isCssHsl($color, $ret))
      return self::hslToRgb($ret);
    elseif(self::isCssHsla($color, $ret))
      return self::hslToRgb($ret);
    else
      return self::hexToRgb($color);
  }

  public static function isCssTransparent($color)
  {
    return preg_match(self::CSS_TRANSPARENT_PATTENR, trim($color)) != 0;
  }

  public static function isHex($color)
  {
    return preg_match(self::HEX_PATTERN, trim($color)) != 0;
  }

  public static function isCssRgb($color, &$out = NULL)
  {
    $ret = preg_match(self::CSS_RGB_PATTERN, trim($color), $colsext = null) > 0;
    
    if($ret && $out !== NULL)
      $out = self::parseCssRgb($colsext[1], $colsext[2], $colsext[3]);
    
    return $ret;
  }

  public static function isCssRgba($color, &$out = NULL)
  {
    $ret = preg_match(self::CSS_RGBA_PATTERN, trim($color), $colsext = null) > 0;

    if($ret && $out !== NULL)
      $out = self::parseCssRgba($colsext[1], $colsext[2], $colsext[3], $colsext[4]);
    
    return $ret;
  }

  public static function parseCssRgb($r, $g, $b)
  {
    return self::parseCssRgba($r, $g, $b);
  }

  public static function parseCssRgba($r, $g, $b, $a = NULL)
  {
    if($r !== NULL)
      $r = Quota::signOrNumber($r,
        self::RGB_MAX,
        self::RGB_MIN,
        Quota::PERCENT,
        Quota::BOUNDS_IN);
    else
      $r = self::RGB_MIN;
    
    if($g !== NULL)
      $g = Quota::signOrNumber(
        $g,
        self::RGB_MAX,
        self::RGB_MIN,
        Quota::PERCENT,
        Quota::BOUNDS_IN);
    else
      $g = self::RGB_MIN;
    
    if($b !== NULL)
      $b = Quota::signOrNumber(
        $b,
        self::RGB_MAX,
        self::RGB_MIN,
        Quota::PERCENT,
        Quota::BOUNDS_IN);
    else
      $b = self::RGB_MIN;
    
    if($a !== NULL) {
      if(Quota::isValidFraction($a, Quota::BOUNDS_IN))
        $a = Quota::translateQuota(
          $a,
          self::ALPHA_MAX,
          Quota::FRACTION,
          Quota::BOUNDS_IN);
      elseif(Quota::isValidPercent($a, Quota::BOUNDS_IN))
        $a = Quota::translateQuota(
          $a,
          self::ALPHA_MAX,
          Quota::PERCENT,
          Quota::BOUNDS_IN);
      else
        $a = NULL;
    }

    if($a !== NULL)
      return self::rgbaToArray($r, $g, $b, $a);
    else
      return self::rgbToArray($r, $g, $b);
  }

  public static function isCssHsl($color, &$out = NULL)
  {
    $ret = preg_match(self::CSS_HSL_PATTERN, trim($color), $colsext = null) > 0;

    if($ret && $out !== NULL)
      $out = self::parseCssHsl($colsext[1], $colsext[2], $colsext[3]);
    
    return $ret;
  }

  public static function isCssHsla($color, &$out = NULL)
  {
    $ret = preg_match(self::CSS_HSLA_PATTERN, trim($color), $colsext = null) > 0;

    if($ret && $out !== NULL)
      $out = self::parseCssHsla($colsext[1], $colsext[2], $colsext[3], $colsext[4]);
    
    return $ret;
  }

  public static function parseCssHsl($h, $s, $l)
  {
    return self::parseCssHsla($h, $s, $l);
  }

  public static function parseCssHsla($h, $s, $l, $a = NULL)
  {	
    if($h !== NULL)
      $h = Quota::fractionOrSignOrNumber(
        $h,
        self::HUE_MAX,
        self::HUE_MIN,
        Quota::PERCENT,
        Quota::BOUNDS_IN);
    else
      $h = self::HUE_MIN;
    
    if($s !== NULL)
      $s = Quota::fractionOrSignOrNumber(
        $s,
        self::SLA_MAX,
        self::SLA_MIN,
        Quota::PERCENT,
        Quota::BOUNDS_IN);
    else
      $s = self::SLA_MIN;
    
    if($l !== NULL)
      $l = Quota::fractionOrSignOrNumber(
        $l,
        self::SLA_MAX,
        self::SLA_MIN,
        Quota::PERCENT,
        Quota::BOUNDS_IN);
    else
      $l = self::SLA_MIN;
    
    if($a !== NULL) {
      if(Quota::isValidFraction($a, Quota::BOUNDS_IN))
        $a = Quota::translateQuota(
          $a,
          self::SLA_MAX,
          Quota::FRACTION,
          Quota::BOUNDS_IN);
      elseif(Quota::isValidPercent($a, Quota::BOUNDS_IN))
        $a = Quota::translateQuota(
          $a,
          self::SLA_MAX,
          Quota::PERCENT,
          Quota::BOUNDS_IN);
      else
        $a = NULL;
    }
    
    if($a !== NULL)
      return self::hslaToArray($h, $s, $l, $a);
    else
      return self::hslToArray($h, $s, $l);
  }

  public static function rgbToHsl($r, $g = 0, $b = 0, $a = NULL)
  {
    return self::rgbaToHsla($r, $g, $b);
  }

  public static function rgbaToHsla($r, $g = 0, $b = 0, $a = NULL)
  {
    if(is_array($r)) {
      if(count($r) == 3)
        list($r, $g, $b) = $r;
      elseif(count($r) == 4)
        list($r, $g, $b, $a) = $r;
    }

    $r /= self::RGB_MAX;
    $g /= self::RGB_MAX;
    $b /= self::RGB_MAX;

    $hsl = self::rgbfToHslf($r, $g, $b);

    $hsl['h'] *= self::HUE_MAX;
    $hsl['s'] *= self::SLA_MAX;
    $hsl['l'] *= self::SLA_MAX;

    if(!empty($a))
      $hsl['a'] = Quota::translateQuota(
        $a / self::ALPHA_MAX,
        self::SLA_MAX,
        Quota::PERCENT,
        Quota::BOUNDS_IN);
    
    return $hsl;
  }

  public static function hslToRgb($h, $s = 0, $l = 0, $a = NULL)
  {
    if(is_array($h)) {
      if(count($h) == 3)
        list($h, $s, $l) = array_values($h);
      elseif(count($h) == 4)
        list($h, $s, $l, $a) = array_values($h);
    }

    $h /= self::HUE_MAX;
    $s /= self::SLA_MAX;
    $l /= self::SLA_MAX;

    $rgb = self::hslfToRgbf($h, $s, $l);

    $rgb['r'] *= self::RGB_MAX;
    $rgb['g'] *= self::RGB_MAX;
    $rgb['b'] *= self::RGB_MAX;
    
    if(!empty($a))
      $rgb['a'] = Quota::translateQuota(
        $a,
        self::ALPHA_MAX,
        Quota::PERCENT,
        Quota::BOUNDS_IN);
    return $rgb;
  }

  public static function rgbfToHslf($r, $g, $b)
  {
    $cmin = min($r, $g, $b);
    $cmax = max($r, $g, $b);
    $dmax = $cmax - $cmin;
    
    $l = ($cmax + $cmin) / 2;
    
    if($dmax == 0) {
      $h = 0;
      $s = 0;
    } else {
      if($l < .5)
        $s = $dmax / ($cmax + $cmin);
      else
        $s = $dmax / (2 - $cmax - $cmin);
      
      $dr = ((($cmax - $r) / 6) + ($dmax / 2)) / $dmax;
      $dg = ((($cmax - $g) / 6) + ($dmax / 2)) / $dmax;
      $db = ((($cmax - $b) / 6) + ($dmax / 2)) / $dmax;
      
      if($r == $cmax)
        $h = $db - $dg;
      elseif($g == $cmax)
        $h = (1 / 3) + $dr - $db;
      elseif($b == $cmax)
        $h = (2 / 3) + $dg - $dr;
      
      if($h < 0) $h += 1;
      if($h > 1) $h -= 1;
    }
    
    return [
      'h' => $h,
      's' => $s,
      'l' => $l
    ];
  }

  public static function hslfToRgbf($h, $s, $l)
  {
    if($s == 0)
      $r = $g = $b = $l;
    else {
      if($l < .5)
        $v2 = $l * (1 + $s);
      else
        $v2 = ($l + $s) - ($s * $l);
      
      $v1 = 2 * $l - $v2;
      $r = self::hue_to_rgb($v1, $v2, $h + (1 / 3));
      $g = self::hue_to_rgb($v1, $v2, $h);
      $b = self::hue_to_rgb($v1, $v2, $h - (1 / 3));
    }
    
    return [
      'r' => $r,
      'g' => $g,
      'b' => $b
    ];
  }

  private static function hue_to_rgb($v1, $v2, $vh)
  {
    if($vh < 0) $vh += 1;
    if($vh > 1) $vh -= 1;
    
    if((6 * $vh) < 1)
      return $v1 + ($v2 - $v1) * 6 * $vh;
    elseif((2 * $vh) < 1)
      return $v2;
    elseif((3 * $vh) < 2)
      return $v1 + ($v2 - $v1) * ((2 / 3 - $vh) * 6);
    else
      return $v1;
  }

  public static function binaryToRgb($color)
  {
    return self::binaryToRgba($color);
  }

  public static function binaryToRgba($color)
  {
    $col = 0;
    if(is_numeric($color))
      $col = intval($color);
    
    if($col > 0x00FFFFFF)
      $a = ($col >> 24) & self::ALPHA_MAX;
    else
      $a = NULL;

    $r = ($col >> 16) & self::RGB_MAX;
    $g = ($col >> 8) & self::RGB_MAX;
    $b = ($col) & self::RGB_MAX;
    
    if($a !== NULL)
      return self::rgbaToArray($r, $g, $b, $a);
    else
      return self::rgbToArray($r, $g, $b);
  }

  public static function hexToRgb($hexcolor)
  {
    return self::hexaToRgba($hexcolor);
  }

  public static function hexaToRgba($hexcolor)
  {
    $r = self::RGB_MIN;
    $g = self::RGB_MIN;
    $b = self::RGB_MIN;
    $a = NULL;

    $hexcolor = self::hexColorClearSign($hexcolor);
    $hexcolor = self::hexColorExpand($hexcolor);
    
    if(strlen($hexcolor) == 6)
      list($r, $g, $b) = str_split($hexcolor, 2);
    elseif(strlen($hexcolor) == 8)
      list($a, $r, $g, $b) = str_split($hexcolor, 2);

    $r = hexdec($r);
    $g = hexdec($g);
    $b = hexdec($b);
    if($a !== NULL) $a = hexdec($a);

    if($a !== NULL)
      return self::rgbaToArray($r, $g, $b, $a);
    else
      return self::rgbToArray($r, $g, $b);
  }

  public static function hexColorExpand($hexcolor)
  {
    $len = strlen($hexcolor);
    if($len == 1)
      return str_repeat($hexcolor, 6);
    elseif($len == 2) // grey scale
      return str_repeat($hexcolor, 3);
    elseif($len == 3)
      return str_repeat($hexcolor[0], 2).str_repeat($hexcolor[1], 2).str_repeat($hexcolor[2], 2);
    else
      return $hexcolor;
  }

  public static function hexColorClearSign($hexcolor)
  {
    if ($hexcolor[0] == '#')
      return substr($hexcolor, 1);
    else
      return $hexcolor;
  }

  public static function alphaToOpacity($alpha)
  {
    return $alpha / self::ALPHA_MAX;
  }

  public static function opacityToAlpha($opacity)
  {
    return $opacity * self::ALPHA_MAX;
  }

  public static function alphaToPhpAlpha($alpha)
  {
    return self::PHP_ALPHA_MAX - ($alpha >> 1);
  }

  public static function phpAlphaToAlpha($phpalpha)
  {		
    return ((self::PHP_ALPHA_MAX - $phpalpha) / self::PHP_ALPHA_MAX) * self::ALPHA_MAX;
  }

  private static function between($value, $min, $max)
  {
    $ret = $value;

    if($ret < $min)
      $ret = $min;
    elseif($ret > $max)
      $ret = $max;
    
    return $ret;
  }
}
