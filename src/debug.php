<?php

// Debug config

define('LYX_DEBUG_TAB_LENGTH', 4);

define('LYX_DEBUG_BLOCK_BEGIN_TAG', '<pre>');
define('LYX_DEBUG_BLOCK_END_TAG', '</pre>');
define('LYX_DEBUG_BLOCK_BEGIN', "{");
define('LYX_DEBUG_BLOCK_END', "}");

lyx_config_set('debug_print', '_lxdefprint');
lyx_config_set('debug_colors', [
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
]);
lyx_config_set('debug_block_expanded', true);

// Debug function


function set_html_utf8()
{
  print '<meta http-equiv="content-type" content="text/html; charset=utf-8" />';
}

function pre_r($ar)
{
  set_html_utf8();
  print(LYX_DEBUG_BLOCK_BEGIN_TAG.PHP_EOL);
  print_r($ar);
  print(LYX_DEBUG_BLOCK_END_TAG.PHP_EOL);
}

function pre_rx($ar)
{
  pre_r($ar);
  exit();
}

function block_r($arr, $title='', $type='debug', $expanded = null)
{
  $colors = lyx_config_get('debug_colors');
  if($expanded === null)
    $expanded = lyx_config_get('debug_block_expanded');

  set_html_utf8();

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

function var_dump_return($var)
{
  ob_start();
  var_dump($var);
  return ob_get_clean();
}

function block_dump($var, $title='', $type='debug', $expanded = null)
{
  // ob_start();
  // var_dump($var);
  // $dump = ob_get_clean();
  block_r(var_dump_return($var), $title, $type, $expanded);
}

function dbg($arr, $title = 'Debug', $in_fn = '', $on_line = '')
{
  if(strlen($in_fn) > 0) $in_fn = 'in "'.$in_fn.'"';
  if(strlen($on_line) > 0) $on_line = 'on "'.$on_line.'"';
  
  $t = implode(' ', array($title, $in_fn, $on_line));
  block_r($arr, $t);
}

function dbgx($arr, $title = 'Debug', $in_fn = '', $on_line = '')
{
  dbg($arr, $title, $in_fn, $on_line);
  exit;
}

function dbg_dump($var, $title = '', $in_fn = '', $on_line = '')
{
  dbg(var_dump_return($var), $title, $in_fn, $on_line);
}

function dbg_dumpx($var, $title = '', $in_fn = '', $on_line = '')
{
  dbg_dump($var, $title, $in_fn, $on_line);
  exit;
}

function lxdebug()
{
  call_user_func_array('lxdebug_print_r', func_get_args());
}

function lxdebugx()
{
  call_user_func_array('lxdebug_print_r', func_get_args());
  exit();
}

function lxdebug_print_r()
{
  // $res = lxdebug_block_begin();
  // $prdata = lxdebug_print_r_return(func_get_args());
  // _lxconfprint($prdata);
  // $res .= $prdata.lxdebug_block_end();
  // return $res;
  
  lxdebug_block_begin();
  lxdebug_print_r_return(func_get_args());
  lxdebug_block_end();
}

function lxdebug_print_r_return($a, $insize = 0)
{
  $block = '';
  if(!is_array($a))
    $a = (array)$a;


  $ac = count($a);
  $tab =  lxdebug_tab();
  $block = str_repeat($tab, $insize).LYX_DEBUG_BLOCK_BEGIN.PHP_EOL;
  _lxconfprint($block);
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
      _lxconfprint($block);
      $block = '';
    }
    
    if($sub_block !== NULL)
      //$block .= $sub_block;
      lxdebug_print_r_return($el, $insize + 1);
    
    $counter++;
  }
  
  /*$block .= str_repeat($tab, $insize).LYX_DEBUG_BLOCK_END.PHP_EOL;*/
  _lxconfprint(str_repeat($tab, $insize).LYX_DEBUG_BLOCK_END.PHP_EOL);
  
  return; // $block;
}

function lxdebug_block_begin()
{
  _lxconfprint($res = (LYX_DEBUG_BLOCK_BEGIN_TAG.PHP_EOL));
  return $res;
}

function lxdebug_block_end()
{
  _lxconfprint($res = (LYX_DEBUG_BLOCK_END_TAG.PHP_EOL));
  return $res;
}

function lxdebug_block($inblock)
{
  $res = lxdebug_block_start();
  _lxconfprint($inblock);
  $res .= $inblock.lxdebug_block_end();
  return $res;
}

function lxdebug_var_dump()
{
  lxdebug_block_begin();
  var_dump(func_get_args());
  lxdebug_block_end();
}

function lxdebugx_var_dump()
{
  // lxdebug_block_begin();
  // var_dump(func_get_args());
  // lxdebug_block_end();
  call_user_func_array('lxdebug_var_dump', func_get_args());
  exit();
}

function lxdebug_tab($length = LYX_DEBUG_TAB_LENGTH)
{
  if($length > 0)
    return str_repeat(" ", $length);
  else
    return "\t";
}

function lxprint()
{
  return _lxprint(false, func_get_args());
}

function lxprintln()
{
  return _lxprint(PHP_EOL, func_get_args());
}

function lxprintbr()
{
  return _lxprint('<br/>'.PHP_EOL, func_get_args());
}

// function lxsprintf() {
//   if(func_num_args() > 1 && is_string(func_get_arg(0)))
//     $result = \Lyx\String\Str::format()
// }

function _lxprint($add_at_eol, $args)
{
  $res = '';
  $aa = $args;
  $an = count($args);

  foreach($aa as $val) {
    $res .= $val;

    if(substr($val, -1) != ':' && substr($val, -2) != ': ')
      $res .= ', ';
  }

  $res = rtrim($res, ', ');
  if(!empty($add_at_eol))
    $res .= $add_at_eol;

  _lxconfprint($res);

  return $res;
}

function _lxconfprint()
{
  $dbg_print = lyx_config_get('debug_print');
  if(is_string($dbg_print) || is_array($dbg_print))
    call_user_func_array($dbg_print, func_get_args());
}

function _lxdefprint()
{
  $aa = func_get_args();

  foreach($aa as $arg)
    print $arg;
}
