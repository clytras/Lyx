<?php

namespace Lyx\System;

class Path
{
  public static function documentRoot($addition = '', $slashed = FALSE)
  {
    $docRoot = $_SERVER["DOCUMENT_ROOT"];
    
    if(strlen($addition) > 0) {
      if(strpos($addition, $docRoot) === FALSE) {
        if($addition[0] == DIRECTORY_SEPARATOR)
          $addition = substr($addition, 1);

        $docRoot = lyx_slash_dir($docRoot).$addition;
      }
    }
    
    return $slashed ? lyx_slash_dir($docRoot) : $docRoot;
  }
  
  public static function siteRoot($path)
  {
    $docRoot = $_SERVER["DOCUMENT_ROOT"];
    
    if(strlen($path) > 0) {
      if(strpos($path, $docRoot) !== FALSE)
        $path = substr($path, strlen($docRoot));
    }
    
    if(strlen($path) == 0)
      $path .= '/';
    
    return $path;
  }
  
  public static function homeDir($append = '')
  {
    if(isset($_SERVER['HOME']))
      $result = $_SERVER['HOME'];
    else
      $result = getenv("HOME");
        
    if(empty($result) && function_exists('exec')) {
      if(strncasecmp(PHP_OS, 'WIN', 3) === 0) {
        $result = exec("echo %userprofile%");
      } else {
        $result = exec("echo ~");
      }
    }
    
    if(!empty($append) && !empty($result))
      $result = static::compose($result, $append);
        
    return $result;
  }
  
  public static function compose()
  {
    $result = '';
    $argc = func_num_args();
    $argv = func_get_args();
    
    foreach($argv as $arg) {
      if(is_array($arg))
        $arg = self::compose($arg);
      
      if(substr($arg, -1) !== DIRECTORY_SEPARATOR)
        $arg .= DIRECTORY_SEPARATOR;
      
      //if($arg[0] === DIRECTORY_SEPARATOR)
      //  $arg = substr($arg, 1);

      $result .= $arg;
    }
    
    if(substr($result, -1) === DIRECTORY_SEPARATOR)
      $result = substr($result, 0, -1);
    
    return $result;
  }
  
  public static function real()
  {
    return realpath(call_user_func_array('static::compose', func_get_args()));
  }
}
