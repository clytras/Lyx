<?php

namespace Lyx\Web;

class Url
{
  const URL_SEPARATOR = '/';
  public static function compose()
  {
    $result = '';
    $argc = func_num_args();
    $argv = func_get_args();
    
    foreach($argv as $arg) {
      if(empty($arg))
        continue;

      if(is_array($arg))
        $arg = self::compose($arg);
      
      if(substr($arg, -1) !== self::URL_SEPARATOR)
        $arg .= self::URL_SEPARATOR;
      
      if($arg[0] === self::URL_SEPARATOR)
        $arg = substr($arg, 1);

      $result .= $arg;
    }
    
    if(substr($result, -1) === self::URL_SEPARATOR)
      $result = substr($result, 0, -1);
    
    return $result;
  }
  
  public static function uriQueryStringHasSeoUrl(&$param = null, $as = '')
  {
    foreach($_GET as $name => $value) {
      if(empty($value) && strpos($name, self::URL_SEPARATOR) !== false) {
        if(func_num_args() >= 1) {
          $name = trim($name, self::URL_SEPARATOR);
          switch($as) {
            case 'array':
              $param = explode(self::URL_SEPARATOR, $name);
              break;
            case 'pair':
              $param = array();
              $arr = explode(self::URL_SEPARATOR, $name);
              $len = count($arr);
              $nam = null;
              foreach($arr as $val) {
                if($nam === null)
                  $nam = $val;
                else {
                  $param[$nam] = $val;
                  $nam = null;
                }
              }
              
              if($nam !== null)
                $param[$nam] = '';
              break;
            default:
              $param = $name;
              break;
          }
        }
        return true;
      }
    }

    return false;
  }
}