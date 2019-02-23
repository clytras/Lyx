<?php

if(defined('LYX'))
  return;

define('LYX', TRUE);
defined('DOCROOT') or define('DOCROOT', $_SERVER['DOCUMENT_ROOT']);

// For formating constants inside a string like "{$_CONST('CONST')}"
$_CONST = 'constant';

// Core functions

global $LYX_CONFIG;
$LYX_CONFIG = [
  'include_dir' => '',
  'lyx' => require(__DIR__.'/config.php'),
  'user_config' => [],
  'apps_config' => [],

  'debug_print' => '_lyx_defprint',
  'debug_colors' => [
    'debug' => [
      'back'=>'#09F',
      'fore'=>'#FFF',
    ],
    'alert' => [
      'back'=>'#F00',
      'fore'=>'#FFF',
    ],
    'success' => [
      'back'=>'#3C3',
      'fore'=>'#FFF',
    ],
    'code' => [
      'back'=>'#FFF',
      'fore'=>'#666'
    ]
  ],
  'debug_block_expanded' => true
];

define('LYX_DEBUG_TAB_LENGTH', 4);

define('LYX_DEBUG_BLOCK_BEGIN_TAG', '<pre>');
define('LYX_DEBUG_BLOCK_END_TAG', '</pre>');
define('LYX_DEBUG_BLOCK_BEGIN', "{");
define('LYX_DEBUG_BLOCK_END', "}");

// lyx_config_set('debug_print', '_lxdefprint');
// lyx_config_set('debug_colors', [
//     'debug' => [
//         'back'=>'#09F',
//         'fore'=>'#FFF',
//     ],
//     'alert' => [
//             'back'=>'#F00',
//             'fore'=>'#FFF',
//     ],
//     'success' => [
//             'back'=>'#3C3',
//             'fore'=>'#FFF',
//     ],
//     'code' => [
//         'back'=>'#FFF',
//         'fore'=>'#666'
//     ]
// ]);
// lyx_config_set('debug_block_expanded', true);

//spl_autoload_register('lyx_import');

function lyx_config_get(string $name = null, $default = '')
{
  global $LYX_CONFIG;
  if(empty($name))
    return $LYX_CONFIG;
  if(isset($LYX_CONFIG[$name]))
    return $LYX_CONFIG[$name];
  else
    return $default;
}

function lyx_config_set(string $name, $value)
{
  global $LYX_CONFIG;
  $LYX_CONFIG[$name] = $value;
}

function lyx_config_unset(string $name)
{
  global $LYX_CONFIG;
  unset($LYX_CONFIG[$name]);
}

function lyx_config_merge(array $arr, string $merge_to = 'user_config')
{
  global $LYX_CONFIG;
  $LYX_CONFIG[$merge_to] = array_merge($LYX_CONFIG[$merge_to], $arr);
}

function lyx_app_config_merge(array $arr)
{
  lyx_config_merge($arr, 'apps_config');
}

function lyx_load_php(string $file, $incl, $func, bool $force_config = false)
{
  if(!is_array($incl))
    $incl = array($incl);
  
  $dir = $file ? dirname($file) : lyx_config_get('include_dir');
  $dir = lyx_slash_dir($dir);

  foreach($incl as $inc) {
    $is_config = false;
    $f = $inc;

    if(substr($inc, -4) != '.php')
      $f = $dir.$inc.'.php';
    
    if(!$force_config)
      if(!file_exists($f) && substr($inc, -6) !== '.class')
        $f = $dir.$inc.'.class.php';

    if(!file_exists($f)) {
      if(substr($inc, -11) !== '.config.php')
        $f = $dir.$inc.'.config.php';
      else
        $f = $dir.$inc;

      $is_config = true;
    } else {
      $f = realpath($f);
      $is_config = substr($inc, -11) === '.config.php';
    }

    $res = $func($f);

    if($is_config)
      lyx_config_merge($res, substr($inc, 0, 4) == 'app.' || substr($inc, 0, 5) == 'apps.' ? 'apps_config' : 'user_config');
  }
}

function lyx_req(string $file)
{
  return require($file);
}

function lyx_req_once(string $file)
{
  return require_once($file);
}

function lyx_inc(string $file)
{
  return include($file);
}

function lyx_inc_once(string $file)
{
  return include_once($file);
}

function lyx_slash_dir(string $dir)
{
  if(!empty($dir))
    if(substr($dir, -1) != DIRECTORY_SEPARATOR)
      $dir .= DIRECTORY_SEPARATOR;

  return $dir;
}

function lyx_dircat()
{
  $result = '';
  $argsv = func_get_args();
  $argsn = func_num_args();
  
  if($argsn > 0)
    foreach($argsv as $part)
      $result .= lyx_slash_dir($part);
  
  return $result;
}

function lyx_has_flags(int $value, int $flags)
{
  return ($value & $flags) === $flags;
}

function lyx_include($a1, $a2 = null)
{
  if(is_null($a2))
    list($a2, $a1) = array($a1, $a2);

  lyx_load_php($a1, $a2, 'lyx_inc_once');
}

function lyx_require($a1, $a2 = null)
{
  if(is_null($a2))
    list($a2, $a1) = array($a1, $a2);

  lyx_load_php($a1, $a2, 'lyx_req_once');
}

function lyx_configf($a1, $a2 = null)
{
  if(is_null($a2))
    list($a2, $a1) = array($a1, $a2);

  lyx_load_php($a1, $a2, 'lyx_req_once', true);
}

function lyx_import($imports)
{
  if(!is_array($imports))
    $imports = [$imports];
  
  foreach($imports as $class) {
    $class = strtolower($class);
  
    if(substr($class, 0, 4) == 'lyx.' ||
       substr($class, 0, 4) == 'lyx\\')
      $class = substr($class, 4);
    
    if(substr($class, -4) == '.php')
      $class = substr($class, 0, -4);
    
    if(substr($class, -6) == '.class')
      $class = substr($class, 0, -6);
    
    $impall = FALSE;
    if(substr($class, -2) == '.*') {
      $class = substr($class, 0, -2);
      $impall = TRUE;
    }
  
    $class = str_replace('\\', DIRECTORY_SEPARATOR, $class);
    $class = str_replace('.', DIRECTORY_SEPARATOR, $class);
    $class = dirname(__FILE__).DIRECTORY_SEPARATOR.$class;
    
    if($impall) {
      if($handle = opendir($class)) {
        while(($file = readdir($handle)) !== false) {
          $filepath = $class.DIRECTORY_SEPARATOR.$file;
          if(is_file($filepath))
            if(pathinfo($file, PATHINFO_EXTENSION) == 'php')
              require_once($filepath);
        }
        closedir($handle);
      }
    } else {
      if(file_exists($class.'.class.php'))
        $class .= '.class.php';
      elseif(file_exists($class.'.php'))
        $class .= '.php';
    
      if(file_exists($class))
        require_once($class);
      else 
        _lyx_autoloader($imports);
    }
  }
}

function lyx_millitime()
{
  $mt = explode(' ', microtime());
  return intval($mt[1]) * 1000 + (int)round($mt[0] * 1000);
}

function lyx_msleep(int $milliseconds)
{
  usleep($milliseconds * 1000);
}

function _lyx_autoloader(array $imports) {
  foreach($imports as $class) {
    if(substr(strtolower($class), 0, 4) == 'lyx\\')
      $class = substr($class, 4);
      
    $class = str_replace('\\', DIRECTORY_SEPARATOR, $class);
    $class = dirname(__FILE__).DIRECTORY_SEPARATOR.$class;
      
    if(file_exists($class.'.class.php'))
      $class .= '.class.php';
    elseif(file_exists($class.'.php'))
      $class .= '.php';

    if(file_exists($class))
      require_once($class);
    else {
      $class = strtolower(dirname($class)).'/'.basename($class);
        
      if(file_exists($class.'.class.php'))
        $class .= '.class.php';
      elseif(file_exists($class.'.php'))
        $class .= '.php';
      
      if(file_exists($class))
          require_once($class);
    }
  }
}

//require_once 'debug.php';
//require_once 'lyx.class.php';

// Debug function

function lyx_set_html_utf8()
{
  print '<meta http-equiv="content-type" content="text/html; charset=utf-8" />';
}

function lyx_pre_r(array $arr)
{
  lyx_set_html_utf8();
  print(LYX_DEBUG_BLOCK_BEGIN_TAG.PHP_EOL);
  print_r($arr);
  print(LYX_DEBUG_BLOCK_END_TAG.PHP_EOL);
}

function lyx_pre_rx(array $arr)
{
  lyx_pre_r($arr);
  exit();
}

function lyx_block_r($arr, string $title = '', string $type = 'debug', $expanded = null)
{
  $colors = lyx_config_get('debug_colors');
  if($expanded === null)
    $expanded = lyx_config_get('debug_block_expanded');

  lyx_set_html_utf8();

  print('<div class="__lyx_debug_block__" style="white-space: normal; border: 1px solid '.$colors[$type]['fore'].';background-color:'.$colors[$type]['back'].';color:'.$colors[$type]['fore'].'">'.PHP_EOL);
  print('<div class="__lyx_debug_block_header__" onclick="this.nextElementSibling.style.display = (this.nextElementSibling.style.display == \'none\') ? \'block\' : \'none\';" style="font-family: courier; padding: 6px 4px;color:'.$colors[$type]['fore'].'">'.(strlen($title) > 0 ? $title : 'Debug Block').'</div>'.PHP_EOL);
  print('<div class="__lyx_debug_block_body__" style="display:'.($expanded ? 'block' : 'none').';padding:2px;overflow-x:auto;border: 1px solid '.$colors[$type]['back'].';background-color:'.$colors['code']['back'].';color:'.$colors['code']['fore'].'">'.PHP_EOL);
  print('<pre>'.PHP_EOL);
  if(is_array($arr))
    print_r($arr);
  elseif(is_string($arr))
    print($arr);
  else 
    var_dump($arr);
  print('</pre>'.PHP_EOL);
  print('</div>'.PHP_EOL);
  print('</div>'.PHP_EOL);
}

function lyx_var_dump_return($var)
{
  ob_start();
  var_dump($var);
  return ob_get_clean();
}

function lyx_block_dump($var, string $title = '', string $type = 'debug', $expanded = null)
{
  // ob_start();
  // var_dump($var);
  // $dump = ob_get_clean();
  lyx_block_r(lyx_var_dump_return($var), $title, $type, $expanded);
}

function lyx_dbg($arr, string $title = 'Debug', string $in_fn = '', string $on_line = '')
{
  if(strlen($in_fn) > 0) $in_fn = 'in "'.$in_fn.'"';
  if(strlen($on_line) > 0) $on_line = 'on "'.$on_line.'"';
  
  $t = implode(' ', array($title, $in_fn, $on_line));
  lyx_block_r($arr, $t);
}

function lyx_dbgx($arr, string $title = 'Debug', string $in_fn = '', string $on_line = '')
{
  lyx_dbg($arr, $title, $in_fn, $on_line);
  exit;
}

function lyx_dbg_dump($var, string $title = '', string $in_fn = '', string $on_line = '')
{
  lyx_dbg(lyx_var_dump_return($var), $title, $in_fn, $on_line);
}

function lyx_dbg_dumpx($var, string $title = '', string $in_fn = '', string $on_line = '')
{
  lyx_dbg_dump($var, $title, $in_fn, $on_line);
  exit;
}

function lyx_debug()
{
  call_user_func_array('lyx_debug_print_r', func_get_args());
}

function lyx_debugx()
{
  call_user_func_array('lyx_debug_print_r', func_get_args());
  exit();
}

function lyx_debug_print_r()
{
  // $res = lxdebug_block_begin();
  // $prdata = lxdebug_print_r_return(func_get_args());
  // _lxconfprint($prdata);
  // $res .= $prdata.lxdebug_block_end();
  // return $res;
  
  lyx_debug_block_begin();
  lyx_debug_print_r_return(func_get_args());
  lyx_debug_block_end();
}

function lyx_debug_print_r_return($a, $insize = 0)
{
  $block = '';
  if(!is_array($a))
    $a = (array)$a;


  $ac = count($a);
  $tab =  lxdebug_tab();
  $block = str_repeat($tab, $insize).LYX_DEBUG_BLOCK_BEGIN.PHP_EOL;
  _lyx_confprint($block);
  $counter = 0;

  foreach($a as $key => $el) {
    $info = NULL;
    $sub_block = NULL;
    
    if(is_array($el)) {
      $info = sprintf("(array) [%d]:\n", sizeof($el));
      /*$sub_block =*/ //lxdebug_print_r_return($el, $insize + 1);
      $sub_block = true;
    } elseif(is_object($el)) {
      $info = sprintf("(object): [%s]\n", get_class($el));
      /*$sub_block =*/ //lxdebug_print_r_return($el, $insize + 1);
      $sub_block = true;
    } elseif(is_resource($el))
      $info = sprintf("(resource): [%s]\n", get_resource_type($el));
    elseif(is_null($el))
      $info = sprintf("(null): NULL\n");
    elseif(is_int($el))
      $info = sprintf("(int): %d\n", $el);
    elseif(is_float($el))
      $info = sprintf("(float): %F\n", $el);
    elseif(is_bool($el))
      $info = sprintf("(bool): %s\n", $el ? 'TRUE' : 'FALSE');
    elseif(is_string($el))
      $info = sprintf("(string) [%d]: \"%s\"\n", strlen($el), $el);
    
    if($info !== NULL) {
      $info = sprintf("[%".strlen(strval($ac))."d] [%s] => ", $counter, is_string($key) ? "\"{$key}\"" : $key).$info;
      $block = str_repeat($tab, $insize + 1).$info;
      _lyx_confprint($block);
      $block = '';
    }
    
    if($sub_block !== NULL)
      //$block .= $sub_block;
      lyx_debug_print_r_return($el, $insize + 1);
    
    $counter++;
  }
  
  /*$block .= str_repeat($tab, $insize).LYX_DEBUG_BLOCK_END.PHP_EOL;*/
  _lyx_confprint(str_repeat($tab, $insize).LYX_DEBUG_BLOCK_END.PHP_EOL);
  
  return; // $block;
}

function lyx_debug_block_begin()
{
  _lyx_confprint($res = (LYX_DEBUG_BLOCK_BEGIN_TAG.PHP_EOL));
  return $res;
}

function lyx_debug_block_end()
{
  _lyx_confprint($res = (LYX_DEBUG_BLOCK_END_TAG.PHP_EOL));
  return $res;
}

function lyx_debug_block($inblock)
{
  $res = lyx_debug_block_start();
  _lyx_confprint($inblock);
  $res .= $inblock.lyx_debug_block_end();
  return $res;
}

function lyx_debug_var_dump()
{
  lyx_debug_block_begin();
  var_dump(func_get_args());
  lyx_debug_block_end();
}

function lyx_debugx_var_dump()
{
  // lxdebug_block_begin();
  // var_dump(func_get_args());
  // lxdebug_block_end();
  call_user_func_array('lyx_debug_var_dump', func_get_args());
  exit();
}

function lyx_debug_tab(int $length = LYX_DEBUG_TAB_LENGTH)
{
  if($length > 0)
    return str_repeat(" ", $length);
  else
    return "\t";
}

function lyx_print()
{
  return _lyx_print(false, func_get_args());
}

function lyx_println()
{
  return _lyx_print(PHP_EOL, func_get_args());
}

function lyx_printbr()
{
  return _lyx_print('<br/>' . PHP_EOL, func_get_args());
}

// function lxsprintf() {
//   if(func_num_args() > 1 && is_string(func_get_arg(0)))
//     $result = \Lyx\String\Str::format()
// }

function _lyx_print($add_at_eol, array $args)
{
  $res = '';
  $aa = $args;
  $an = count($args);

  foreach($aa as $val) {
    $res .= $val;

    if(substr($val, -1) != ':' && substr($val, -2) != ': ')
      $res .= ', ';
  }

  $res = substr($res, 0, -2);
  if(!empty($add_at_eol))
    $res .= $add_at_eol;

  _lyx_confprint($res);

  return $res;
}

function _lyx_confprint()
{
  $dbg_print = lyx_config_get('debug_print');
  if(is_string($dbg_print) || is_array($dbg_print))
    call_user_func_array($dbg_print, func_get_args());
}

function _lyx_defprint()
{
  $aa = func_get_args();

  foreach($aa as $arg)
    print $arg;
}
