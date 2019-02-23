<?php 

class Lyx
{
  public static $logger = null;
  public static function Logger()
  {
    if(static::$logger === null)
      static::$logger = new \Lyx\Utils\Logger();
    return static::$logger;
  }
  
  public static function import()
  {
    call_user_func_array('lyx_import', func_get_args());
  }
  
  public static function configFile()
  {
    call_user_func_array('lyx_configf', func_get_args());
  }

  public static function requireFile()
  {
    call_user_func_array('lyx_require', func_get_args());
  }

  public static function includeFile()
  {
    call_user_func_array('lyx_include', func_get_args());
  }
  
  public static function lyxConfig()
  {
    if(func_num_args() == 2)
        lyx_config_set(func_get_arg(0), func_get_arg(1));
    else
        return lyx_config_get(func_get_arg(0));
  }
  
  public static function appsConfigMerge($arr)
  {
    lyx_app_config_merge($arr);
  }
  
  public static function userConfigMerge($arr)
  {
    lyx_config_merge($arr);
  }
  
  public static function addIncludePath($path)
  {
    set_include_path(get_include_path().PATH_SEPARATOR.$path);
  }
  
  public static function pre_r()
  {
    call_user_func_array('lyx_pre_r', func_get_args());
  }
  
  public static function pre_rx()
  {
    call_user_func_array('lyx_pre_rx', func_get_args());
  }
  
  public static function block_r()
  {
    call_user_func_array('lyx_block_r', func_get_args());
  }
  
  public static function block_dump()
  {
    call_user_func_array('lyx_block_dump', func_get_args());
  }
  
  public static function dbg()
  {
    call_user_func_array('lyx_dbg', func_get_args());
  }
  
  public static function dbgx()
  {
    call_user_func_array('lyx_dbgx', func_get_args());
  }
  
  public static function dbg_dump()
  {
    call_user_func_array('lyx_dbg_dump', func_get_args());
  }
  
  public static function dbg_dumpx()
  {
    call_user_func_array('lyx_dbg_dumpx', func_get_args());
  }

  public static function debug()
  {
    call_user_func_array('lyx_debug', func_get_args());
  }
  
  public static function debugx()
  {
    call_user_func_array('lyx_debugx', func_get_args());
  }

  public static function print()
  {
    return call_user_func_array('lyx_print', func_get_args());
  }
  
  public static function println()
  {
    return call_user_func_array('lyx_println', func_get_args());
  }
  
  public static function printbr()
  {
    return call_user_func_array('lyx_printbr', func_get_args());
  }
  
  public static function redirect($location = null)
  {
    if(empty($location))
      $location = $_SERVER['REQUEST_URI'];

    header("Location: {$location}");
    exit();
  }
}
