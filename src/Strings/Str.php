<?php

namespace Lyx\Strings;

use Lyx\Utils\Config;

class Str
{
  const PADSTR_RIGHT = 1;
  const PADSTR_LEFT = 2;
  
  const ALPHA_UPPER = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ';
  const ALPHA_LOWER = 'abcdefghijklmnopqrstuvwxyz';
  const ALPHA = self::ALPHA_UPPER.self::ALPHA_LOWER;
  const NUMERIC = '0123456789';

  const DEFAULT_DEC_POINT = '.';
  const DEFAULT_THOUSANDS_SEP = ',';
  
  public static function _format($text, $params)
  {
    $ret = $text;
  
    foreach($params as $k => $v)
      $ret = preg_replace('/\{'.trim($k, '{}').'\}/i', $v, $ret);
  
    return $ret;
  }

  // public static function formatc($text, $params, $ignore_case = false)
  // {
  //     return static::format($text, $params)
  // }
  
  // public static function format($text, $params = [], $ignore_case = false, $use_constants = true)
  // {
  //   $ret = $text;
  //   if($ignore_case) {
  //     $params_lcase = [];
  //     foreach($params as $k => $v)
  //       $param_lcase[strtolower($k)] = $v;
  //   }
        
  //   // foreach($params as $k => $v)
  //   //   $ret = preg_replace('/\{'.trim($k, '{}').'\}/i', $v, $ret);
    
  //   if(preg_match_all('/\{(.*?)\}/', $text, $args)) {
  //     foreach($args[1] as $index => $arg) {
  //       $parts = explode(',', $arg);
  //       $key = $parts[0];
        
  //       if(isset($params[$key]))
  //         $value = $params[$key];
  //       elseif($ignore_case && isset($params_lcase[strtolower($key)]))
  //         $value = $params_lcase[strtolower($key)];
  //       else {
  //         $value = @constant($key);
  //         if($value === NULL)
  //           $value = "{{$key}}";
  //       }
                
  //       if(count($parts) > 1)
  //         $value = self::formatNumber($value, $parts[1]);
        
  //       $ret = str_replace($args[0][$index], $value, $ret);
  //     }
  //   }
  
  //   return $ret;
  // }

  public static function format(
    $text,
    $params = [],
    $options = false
  ) {
    $exp = '/\{(.*?)\}/';
    $dec_point = self::DEFAULT_DEC_POINT;
    $thousands_sep = self::DEFAULT_THOUSANDS_SEP;

    if(is_bool($options))
      $ignore_case = $options;
    elseif(is_array($options)) {
      $ignore_case = isset($options['ignore_case']) ? $options['ignore_case'] : false;

      if(isset($options['exp']))
        $exp = $options['exp'];

      if(isset($options['dec_point']))
        $dec_point = $options['dec_point'];

      if(isset($options['thousands_sep']))
        $thousands_sep = $options['thousands_sep'];
    }
    
    $ret = $text;
    if($ignore_case) {
      $params_lcase = [];
      foreach($params as $k => $v)
        $param_lcase[strtolower($k)] = $v;
      $param_lcase = new Config($param_lcase);
    }
      
    if(!($params instanceof Config))
      $params = new Config($params);
        
    //foreach($params as $k => $v)
    //  $ret = preg_replace('/\{'.trim($k, '{}').'\}/i', $v, $ret);

    $args = null;
    
    if(preg_match_all($exp, $text, $args)) {
      foreach($args[1] as $index => $arg) {
        $parts = explode(',', $arg);
        $key = $parts[0];

        $key_mods = explode(':', $key);
        if(count($key_mods) > 1) {
          $key = $key_mods[0];
          $converter = $key_mods[1];
        } else {
          $converter = null;
        }
        
        if(isset($params[$key]))
          $value = $params[$key];
        elseif($ignore_case && isset($params_lcase[strtolower($key)]))
          $value = $params_lcase[strtolower($key)];
        else {
          $value = @constant($key);
          if($value === NULL)
            $value = "{{$key}}";
        }

        if($converter)
          $value = self::_applyConverter($value, $converter, $parts[1] ?? null);
        elseif(count($parts) > 1)
          $value = self::formatNumber($value, $parts[1], $dec_point, $thousands_sep);
        
        $ret = str_replace($args[0][$index], $value, $ret);
      }
    }
  
    return $ret;
  }

  private static function _applyConverter($value, $converter, $args = null)
  {
    if(!$converter)
      return $value;

    if(is_numeric($value))
      $value = intval($value);
    elseif(strlen($value) == 1)
      $value = ord($value);
    else
      return $value;
    
    $argOptions = $args ? explode('.', $args) : [];
    $modifier = 'zero-left-padding';

    switch(strtoupper($converter)) {
      case 'X':
        $value = dechex($value);
        if($converter == 'X')
          $value = strtoupper($value);
        break;
      case 'O':
        $value = decoct($value);
        break;
      case 'B':
        $value = decbin($value);
        break;
      case 'C':
        $value = chr($value);
        break;
    }

    if(!empty($argOptions)) {
      switch($modifier) {
        case 'zero-left-padding':
          $paddingLength = intval($argOptions[0]);
          $value = self::pad($value, $paddingLength, '0', self::PADSTR_LEFT);
          break;
      }
    }

    return $value;
  }
  
  public static function formatNumber(
    $number,
    $format,
    $dec_point = self::DEFAULT_DEC_POINT,
    $thousands_sep = self::DEFAULT_THOUSANDS_SEP
  ) {
    $value = $number;
    $digL = -1;
    $digR = -1;
    $argOptions = explode('.', $format);
    $thousands_sep_use = '';

    if(substr($argOptions[0], -1) == '/') {
      $thousands_sep_use = $thousands_sep;
      $argOptions[0] = substr($argOptions[0], 0, -1);
    }

    if(count($argOptions) > 1) {
      if(is_numeric($argOptions[0]))
        $digL = intval($argOptions[0]);
      
      if(is_numeric($argOptions[1]))
        $digR = intval($argOptions[1]);
    } else {
      if(is_numeric($argOptions[0]))
        $digL = intval($argOptions[0]);
    }
    
    if(is_numeric($value)) {
      if($digR != -1)
        $value = number_format($value, $digR, $dec_point, $thousands_sep_use);
      
      if($digL != -1) {
        $numParts = explode($dec_point, $value);
        $numParts[0] = self::pad($numParts[0], $digL, '0', self::PADSTR_LEFT);
        $value = count($numParts) > 1 ? $numParts[0].$dec_point.$numParts[1] : $numParts[0];
      }
    }
    return $value;
  }
  
  /*
   * Fixes string expressions line 'Sample text', (Sample text)
   * by removing the container chars '()[]{}
   * Container has to be supplied each time.
   * 
   * */
  
  public static function removeContainers($text, $containers = "'")
  {
    if(!is_array($containers))
      $containers = [$containers];
    
    $textlen = strlen($text);
  
    foreach($containers as $container) {
      $containerlen = strlen($container);
      if($textlen < $containerlen)
          continue;

      $l = $r = false;
      if($containerlen == 1)
        $l = $r = $container;
      elseif($containerlen == 2) {
        $l = $container[0];
        $r = $container[1];
      }
      
      if($l && $r && $text[0] == $l && $text[$textlen - 1] == $r) {
        $text = substr($text, 1, -1);
        $textlen = strlen($text);
      }
    }
    
    return $text;
  }
  
  public static function parseFunctionParameters($expression, $params = [])
  {
    $process_double_quote = isset($params['process_double_quotes']) ? $params['process_double_quotes'] : true;
    $process_single_quote = isset($params['process_single_quotes']) ? $params['process_single_quotes'] : true;
    $no_parenthesis = isset($params['no_parenthesis']) ? $params['no_parenthesis'] : false;
    $expression = trim($expression);
    $expression_length = mb_strlen($expression);
    $expression_chars = preg_split('//u', $expression, -1, PREG_SPLIT_NO_EMPTY);
    $start_parenthesis = $no_parenthesis ? -1 : mb_strpos($expression, '(');
    $in_single_quotes = false;
    $in_double_quotes = false;
    $result = [
      'expression_directives' => $start_parenthesis > 0 ? trim(mb_substr($expression, 0, $start_parenthesis)) : $expression,
      'params_raw' => '',
      'params' => [],
      'params_type' => [],
      'post_expression_directives' => ''
    ];
    $bc = '';
    $buff = '';
    $buff_has_alpha = $buff_has_digit = $buff_has_dot = $buff_has_brackets = $buff_has_operator = false;
    
    if($no_parenthesis || $start_parenthesis === 0 || $start_parenthesis > 0) {
      for($i = $start_parenthesis + 1; $i < $expression_length; $i++) {
        $c = $expression_chars[$i];
        $buff_has_alpha = $buff_has_alpha || ctype_alpha($c) || $c == '_';
        $buff_has_digit = $buff_has_digit || ctype_digit($c);
        $buff_has_dot = $buff_has_dot || $c == '.';
        $buff_has_brackets = $buff_has_brackets || $c == '[' || $c == ']';
        $buff_has_operator = $buff_has_operator || (strpos('=+-*/%.|&~<>^@', $c) !== false);
        
        switch($c) {
          case '"':
            if(!$in_single_quotes && $process_double_quote) {
              if($in_double_quotes) {
                if($bc != '\\') {
                  $in_double_quotes = false;
                  $result['params_raw'] .= "\"{$buff}\"";
                  $result['params'][] = str_replace('\\"', '"', $buff);
                  $result['params_type'][] = 'string';
                  $buff_has_alpha = $buff_has_digit = $buff_has_dot = $buff_has_brackets = $buff_has_operator = false;
                  $buff = '';
                } else 
                  $buff .= $c;
              } else {
                $in_double_quotes = true;
                $result['params_raw'] .= $buff;
                $buff = '';
              }
            }
            break;
          case "'":
            if(!$in_double_quotes && $process_single_quote) {
              if($in_single_quotes) {
                if($bc != '\\') {
                  $in_single_quotes = false;
                  $result['params_raw'] .= "'{$buff}'";
                  $result['params'][] = str_replace('\\\'', '\'', $buff);
                  $result['params_type'][] = 'string';
                  $buff_has_alpha = $buff_has_digit = $buff_has_dot = $buff_has_brackets = $buff_has_operator = false;
                  $buff = '';
                } else 
                  $buff .= $c;
              } else {
                $in_single_quotes = true;
                $result['params_raw'] .= $buff;
                $buff = '';
              }
            }
            break;
          case ',':
          case ')':
            if(!$in_double_quotes && !$in_single_quotes)
              break;
          default:
            $buff .= $c;
        }
        
        if(!$in_double_quotes && !$in_single_quotes && ($c == ',' || $c == ')' || ($i == $expression_length - 1))) {
          $result['params_raw'] .= $buff.($c == ')' || $no_parenthesis ? '' : $c);
          $buff = trim($buff);
          $type = '';
          $value = '';
          
          if(!empty($buff)) {
            $result['params'][] = $buff;
            if(!$buff_has_alpha)
              if($buff_has_digit)
                $type = $buff_has_dot ? 'float' : 'int';
            
            if(empty($type)) {
              if($buff_has_operator)
                $type = $buff_has_alpha || $buff_has_digit ? 'exp' : 'op';
              elseif($buff_has_brackets)
                $type = 'array';
              elseif($buff[0] == '$')
                $type = 'var';
              elseif($buff_has_alpha)
                $type = 'const';
            }

            $result['params_type'][] = $type; 
          }
          
          $buff_has_alpha = $buff_has_digit = $buff_has_dot = $buff_has_brackets = $buff_has_operator = false;

          if($c == ')')
              break;

          $buff = '';
        }
        
        $bc = $c;
      }
      
      if($i < $expression_length)
        $result['post_expression_directives'] = trim(mb_substr($expression, $i + 1));
    }
    
    $result['params_count'] = count($result['params']);
    return $result;
  }
  
  public static function replace($text, $arg1, $arg2 = null)
  {
    $ret = $text;
    $keys = null;
    $values = null;

    if(is_null($arg2)) {
      if(is_array($arg1)) {
        $keys = array_keys($arg1);
        $values = array_values($arg1);
      } else {
        $keys = (string)$arg1;
        $values = '';
      }
    } else {
      if(is_string($arg1)) {
        $keys = $arg1;
        $values = (string)$arg2;
      } elseif(is_array($arg1)) {
        $keys = $arg1;
        if(is_array($arg2))
          $values = $arg2;
        else
          $values = array_fill(0, count($arg1), (string)$arg2);
      }
    }
    
    if($keys && $values)
      $ret = str_replace($keys, $values, $ret);
    return $ret;
  }

  public static function hidePassword($pwd, $doubleRep = true, $passwordChar = '*')
  {
    $ret = str_repeat($passwordChar, strlen($pwd));
    if($doubleRep) $ret .= $ret;
    return $ret;
  }

  public static function singleQuote($str)
  {
    return "'".$str."'";
  }
  
  public static function doubleQuote($str)
  {
    return '"'.$str.'"';
  }
  
  public static function sandwich($str, $starts = null, $ends = null, $case = true)
  {
    $ret = $str;
    if(!self::startsWith($ret, $starts, $case))
      $ret = $starts.$ret;
    
    if(!self::endsWith($ret, $ends, $case))
      $ret .= $ends;

    return $ret;
  }

  public static function startsWith($haystack, $needle, $case = true, &$rest = null)
  {
    if(gettype($haystack) != "string")
      return false;
    
    if($case)
      $pos = strpos($haystack, $needle, 0);
    else
      $pos = stripos($haystack, $needle, 0);

    if($pos === 0)
    {
      if($rest !== null)
        $rest = substr($haystack, strlen($needle));
      return true;
    }
    else
      return false;
  }
  
  public static function endsWith($haystack, $needle, $case = true, &$rest = null)
  {
    if(gettype($haystack) != "string")
      return false;

    $expectedPosition = strlen($haystack) - strlen($needle);
  
    if($case)
      $pos = strrpos($haystack, $needle, 0);
    else
      $pos = strripos($haystack, $needle, 0);
  
    if($pos === $expectedPosition)
    {
      if($rest !== null)
        $rest = substr($haystack, 0, $expectedPosition);
      return true;
    }
    else
      return false;
  }

  public static function unicodeDecode($string)
  {      
    return preg_replace_callback('#\\\\u([0-9a-f]{4})#ism', function($matches) {
      return mb_convert_encoding(pack("H*", $matches[1]), "UTF-8", "UCS-2BE");
    }, $string);
  }
  
  public static function correctEncoding($string)
  {
    $current_encoding = mb_detect_encoding($string, 'auto');
    return iconv($current_encoding, 'UTF-8', $string);
  }

  public static function shortText($text, $chars = 100, $append = '...')
  {
    if(function_exists('mb_strlen'))
      $textLength = mb_strlen($text);
    else
      $textLength = strlen($text);
  
    if($textLength > $chars)
    {
      $matches = null;
      preg_match('/^.{0,'.$chars.'}(?:.*?)\b/iu', $text, $matches);
      return $matches[0].$append;
    }
    else
      return $text;
  }
  
  public static function utf8ToHtmlEntities($string)
  {
    return mb_convert_encoding($string, 'HTML-ENTITIES', 'UTF-8');
  }
  
  
  public static function untone($string)
  {
    $search  = json_decode('["\u0386", "\u0388", "\u0389", "\u038A", "\u03AA", "\u038C", "\u038E", "\u03AB", "\u038F", "\u03AC", "\u03AD", "\u03AE", "\u03AF", "\u0390", "\u03CA", "\u03CC", "\u03CD", "\u03CB", "\u03B0", "\u03CE"]');
    $replace = json_decode('["\u0391", "\u0395", "\u0397", "\u0399", "\u0399", "\u039F", "\u03A5", "\u03A5", "\u03A9", "\u03B1", "\u03B5", "\u03B7", "\u03B9", "\u03B9", "\u03B9", "\u03BF", "\u03C5", "\u03C5", "\u03C5", "\u03C9"]');
    return str_replace($search, $replace, $string);
  }
  
  public static function removeAccent($string, $overrides = null)
  {
    $chars = (array)json_decode('{
      "\u03BF\u03C5": "ou", "\u03BF\u03CD": "ou", "\u03B1\u03C5": "av", "\u03AC\u03C5": "av", "\u03B5\u03C5": "ef", "\u03AD\u03C5": "ef",
      "\u039F\u03A5": "OU", "\u039F\u038E": "OU", "\u0391\u03A5": "AV", "\u0386\u03A5": "AV", "\u0395\u03A5": "EF", "\u0388\u03A5": "EF",
      "\u039F\u03C5": "Ou", "\u039F\u03CD": "Ou", "\u0391\u03C5": "Av", "\u0386\u03C5": "Av", "\u0395\u03C5": "Ef", "\u0388\u03C5": "Ef",
      "\u0391": "A", "\u0386": "A", "\u0392": "B", "\u0393": "G", "\u0394": "D", "\u0395": "E", "\u0388": "E", "\u0396": "Z", "\u0397": "H", "\u0389": "H", "\u0398": "TH", "\u0399": "I", "\u038A": "I", "\u03AA": "I", "\u039A": "K", "\u039B": "L", "\u039C": "M", "\u039D": "N", "\u039E": "KS", "\u039F": "O", "\u038C": "O", "\u03A0": "P", "\u03A1": "R", "\u03A3": "S", "\u03A4": "T", "\u03A5": "Y", "\u038E": "Y", "\u03AB": "Y", "\u03A6": "F", "\u03A7": "X", "\u03A8": "PS", "\u03A9": "W", "\u038F": "W", "\u03B1": "a", "\u03AC": "a", "\u03B2": "b", "\u03B3": "g", "\u03B4": "d", "\u03B5": "e", "\u03AD": "e", "\u03B6": "z", "\u03B7": "h", "\u03AE": "h", "\u03B8": "th", "\u03B9": "i", "\u03AF": "i", "\u03CA": "i", "\u0390": "i", "\u03BA": "k", "\u03BB": "l", "\u03BC": "m", "\u03BD": "n", "\u03BE": "ks", "\u03BF": "o", "\u03CC": "o", "\u03C0": "p", "\u03C1": "r", "\u03C3": "s", "\u03C2": "s", "\u03C4": "t", "\u03C5": "y", "\u03CD": "y", "\u03CB": "y", "\u03C6": "f", "\u03C7": "x", "\u03C8": "ps", "\u03C9": "w", "\u03CE": "w", "\u00C0": "A", "\u00C1": "A", "\u00C2": "A", "\u00C3": "A", "\u00C4": "A", "\u00C5": "A", "\u00C6": "AE", "\u00C7": "C", "\u00C8": "E", "\u00C9": "E", "\u00CA": "E", "\u00CB": "E", "\u00CC": "I", "\u00CD": "I", "\u00CE": "I", "\u00CF": "I", "\u00D0": "D", "\u00D1": "N", "\u00D2": "O", "\u00D3": "O", "\u00D4": "O", "\u00D5": "O", "\u00D6": "O", "\u00D8": "O", "\u00D9": "U", "\u00DA": "U", "\u00DB": "U", "\u00DC": "U", "\u00DD": "Y", "\u00DF": "s", "\u00E0": "a", "\u00E1": "a", "\u00E2": "a", "\u00E3": "a", "\u00E4": "a", "\u00E5": "a", "\u00E6": "ae", "\u00E7": "c", "\u00E8": "e", "\u00E9": "e", "\u00EA": "e", "\u00EB": "e", "\u00EC": "i", "\u00ED": "i", "\u00EE": "i", "\u00EF": "i", "\u00F1": "n", "\u00F2": "o", "\u00F3": "o", "\u00F4": "o", "\u00F5": "o", "\u00F6": "o", "\u00F8": "o", "\u00F9": "u", "\u00FA": "u", "\u00FB": "u", "\u00FC": "u", "\u00FD": "y", "\u00FF": "y", "\u0100": "A", "\u0101": "a", "\u0102": "A", "\u0103": "a", "\u0104": "A", "\u0105": "a", "\u0106": "C", "\u0107": "c", "\u0108": "C", "\u0109": "c", "\u010A": "C", "\u010B": "c", "\u010C": "C", "\u010D": "c", "\u010E": "D", "\u010F": "d", "\u0110": "D", "\u0111": "d", "\u0112": "E", "\u0113": "e", "\u0114": "E", "\u0115": "e", "\u0116": "E", "\u0117": "e", "\u0118": "E", "\u0119": "e", "\u011A": "E", "\u011B": "e", "\u011C": "G", "\u011D": "g", "\u011E": "G", "\u011F": "g", "\u0120": "G", "\u0121": "g", "\u0122": "G", "\u0123": "g", "\u0124": "H", "\u0125": "h", "\u0126": "H", "\u0127": "h", "\u0128": "I", "\u0129": "i", "\u012A": "I", "\u012B": "i", "\u012C": "I", "\u012D": "i", "\u012E": "I", "\u012F": "i", "\u0130": "I", "\u0131": "i", "\u0132": "IJ", "\u0133": "ij", "\u0134": "J", "\u0135": "j", "\u0136": "K", "\u0137": "k", "\u0139": "L", "\u013A": "l", "\u013B": "L", "\u013C": "l", "\u013D": "L", "\u013E": "l", "\u013F": "L", "\u0140": "l", "\u0141": "l", "\u0142": "l", "\u0143": "N", "\u0144": "n", "\u0145": "N", "\u0146": "n", "\u0147": "N", "\u0148": "n", "\u0149": "n", "\u014C": "O", "\u014D": "o", "\u014E": "O", "\u014F": "o", "\u0150": "O", "\u0151": "o", "\u0152": "OE", "\u0153": "oe", "\u0154": "R", "\u0155": "r", "\u0156": "R", "\u0157": "r", "\u0158": "R", "\u0159": "r", "\u015A": "S", "\u015B": "s", "\u015C": "S", "\u015D": "s", "\u015E": "S", "\u015F": "s", "\u0160": "S", "\u0161": "s", "\u0162": "T", "\u0163": "t", "\u0164": "T", "\u0165": "t", "\u0166": "T", "\u0167": "t", "\u0168": "U", "\u0169": "u", "\u016A": "U", "\u016B": "u", "\u016C": "U", "\u016D": "u", "\u016E": "U", "\u016F": "u", "\u0170": "U", "\u0171": "u", "\u0172": "U", "\u0173": "u", "\u0174": "W", "\u0175": "w", "\u0176": "Y", "\u0177": "y", "\u0178": "Y", "\u0179": "Z", "\u017A": "z", "\u017B": "Z", "\u017C": "z", "\u017D": "Z", "\u017E": "z", "\u017F": "s", "\u0192": "f", "\u01A0": "O", "\u01A1": "o", "\u01AF": "U", "\u01B0": "u", "\u01CD": "A", "\u01CE": "a", "\u01CF": "I", "\u01D0": "i", "\u01D1": "O", "\u01D2": "o", "\u01D3": "U", "\u01D4": "u", "\u01D5": "U", "\u01D6": "u", "\u01D7": "U", "\u01D8": "u", "\u01D9": "U", "\u01DA": "u", "\u01DB": "U", "\u01DC": "u", "\u01FA": "A", "\u01FB": "a", "\u01FC": "AE", "\u01FD": "ae", "\u01FE": "O", "\u01FF": "o"
    }');
    
    if(!empty($overrides))
      $chars = array_merge($chars, $overrides);

    return str_replace(array_keys($chars), array_values($chars), $string);
  }

  public static function postSlug($string)
  {
    return strtolower(
      preg_replace(
        ['/[^a-zA-Z0-9 -]/', '/[ -]+/', '/^-|-$/'],
        ['', '-', ''], 
        self::removeAccent($string)
      )
    );
  }
  
  public static function pad($string, $pad_length, $pad_char = ' ', $pad_option = self::PADSTR_RIGHT)
  {
    if(strlen($string) < $pad_length) {
      $padc = str_repeat($pad_char, $pad_length - strlen($string));
      if($pad_option == self::PADSTR_RIGHT)
        return $string.$padc;
      else
        return $padc.$string;
    } else {
      if($pad_option == self::PADSTR_RIGHT)
        return substr($string, 0, $pad_length);
      else
        return substr($string, -$pad_length);
    }
  }

  public static function padRight($string, $pad_length, $pad_char = ' ')
  {
    return self::pad($string, $pad_length, $pad_char, self::PADSTR_RIGHT);
  }

  public static function padLeft($string, $pad_length, $pad_char = ' ')
  {
    return self::pad($string, $pad_length, $pad_char, self::PADSTR_LEFT);
  }
  
  public static function random($length = 16)
  {
    $result = '';

    if (function_exists('openssl_random_pseudo_bytes')) {
      $bytes = openssl_random_pseudo_bytes($length * 2);
  
      if ($bytes !== false) {
        $result = substr(str_replace(array('/', '+', '='), '', base64_encode($bytes)), 0, $length);
      }
    }

    if(empty($result))
      $result = self::quickRandom($length);

    return $result;
  }
  
  /**
   * Generate a "random" alpha-numeric string.
   *
   * Should not be considered sufficient for cryptography, etc.
   *
   * @param  int  $length
   * @return string
   */
  public static function quickRandom($length = 16)
  {
    $pool = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
    return substr(str_shuffle(str_repeat($pool, $length)), 0, $length);
  }
  
  public static function matchWithWildcard($source, $pattern)
  {
    $pattern = preg_quote($pattern, '/');
    $pattern = str_replace('\*' , '.*', $pattern);
    return preg_match('/^'.$pattern.'$/i', $source);
  }
}
