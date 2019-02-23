<?php

namespace Lyx\System;

class Terminal
{
  private static $attributes = [
    'set' => [
      'bold' => 1,
      'dim' => 2,
      'underline' => 4,
      'blink' => 5,
      'reverse' => 7,
      'hidden' => 8
    ],
    'reset' => [
      'all' => 0,
      'bold' => 21,
      'dim' => 22,
      'underline' => 24,
      'blink' => 25,
      'reverse' => 27,
      'hidden' => 28,
      'unset_all' => [21, 22, 24, 25, 27, 28]
    ]
  ];

  private static $fg_colors = [
    'default' => 39,
    'black' => 30,
    'red' => 31,
    'green' => 32,
    'yellow' => 33,
    'blue' => 34,
    'magenta' => 35,
    'cyan' => 36,
    'light_gray' => 37,
    'dark_gray' => 90,
    'light_red' => 91,
    'light_green' => 92,
    'light_yellow' => 93,
    'light_blue' => 94,
    'light_magenta' => 95,
    'light_cyan' => 96,
    'white' => 97
  ];

  private static $bg_colors = [
    'default' => 49,
    'black' => 40,
    'red' => 41,
    'green' => 42,
    'yellow' => 43,
    'blue' => 44,
    'magenta' => 45,
    'cyan' => 46,
    'light_gray' => 47,
    'dark_gray' => 100,
    'light_red' => 101,
    'light_green' => 102,
    'light_yellow' => 103,
    'light_blue' => 104,
    'light_magenta' => 105,
    'light_cyan' => 106,
    'white' => 107
  ];

  public static function print($str)
  {
    print(self::_processFormated($str));
  }

  public static function println($str)
  {
    print(self::_processFormated($str).PHP_EOL);
  }

  public static function printRaw($str)
  {
    print(self::_processFormated($str, true));
  }

  public static function printlnRaw($str)
  {
    print(self::_processFormated($str, true).PHP_EOL);
  }

  public static function printTmpl($str, $tmpl)
  {
    $defaults = $tmpl['defaults'] ??  [
      'a' => [0],
      'f' => [self::$fg_colors['default']],
      'b' => [self::$bg_colors['default']]
    ];

    foreach($tmpl as $regex => $props) {
      $str = preg_replace_callback($regex, function($m) use($props, $defaults) {
        $props = self::_parseProps($props);
        return self::buildEscapeSequence($props, $m[0], $defaults);
      }, $str);
    }

    print($str);
  }

  public static function buildEscapeSequence(
    $pre_props,
    $text = null,
    $post_props = []
  ) {
    foreach(['a', 'f', 'b'] as $p) {
      if(!isset($pre_props[$p])) $pre_props[$p] = [];
      if(!isset($post_props[$p])) $post_props[$p] = [];
    }

    $result = implode(';', array_merge($pre_props['a'], $pre_props['f'], $pre_props['b']));

    if(!empty($result))
      $result = "\033[{$result}m";

    if($text !== null)
      $result .= $text;
    
    if($post_props !== null) {
      $post_esc = implode(';', array_merge($post_props['a'], $post_props['f'], $post_props['b']));
      if(!empty($post_esc)) {
        $result .= "\033[{$post_esc}m";
      }
    }

    return $result;
  }

  private static function _select_color($type, $attr)
  {
    switch($attr) {
      case 'reset':
      case 'def':
        $attr = 'default';
        break;
    }

    $attr = str_replace('-', '_', $attr);

    if($type == 'f')
      return self::$fg_colors[$attr];
    elseif($type == 'b')
      return self::$bg_colors[$attr];
  }

  private static function _select_attr($attrs)
  {
    $res = [];

    if(!is_array($attrs)) $attrs = [$attrs];

    foreach($attrs as $attr) {
      $rm = $attr[0] == '-';
      $operation = $rm ? 'reset' : 'set';
      if($rm) $attr = substr($attr, 1);

      switch($attr) {
        case 'reset':
        case 'def':
        case 'default':
          return [0]; //self::$attributes['reset']['unset_all'];
        case 'bright':
          $attr = 'bold';
          break;
      }

      $res[] = self::$attributes[$operation][$attr];
    }

    return $res;
  }

  private static function _parseProps($props)
  {
    if(is_array($props)) return $props;
    $escape_cmd = ['a' => [], 'f' => [], 'b' => []];
    $cmds = explode(',', $props);

    if(!empty($cmds) && !empty($cmds[0])) {
      foreach($cmds as $cmd) {
        $cmd_parts = explode(':', $cmd);
        $cmd_type = $cmd_parts[0];
        $has_args = count($cmd_parts) > 1;

        switch($cmd_type) {
          case 'f':
          case 'b':
            $escape_cmd[$cmd_type][] = self::_select_color($cmd_type, $cmd_parts[1]);;
            break;
          case 'a':
            array_shift($cmd_parts);
            $escape_cmd[$cmd_type][] = self::_select_attr($cmd_parts);
            break;
        }
      }
    }

    return $escape_cmd;
  }

  private static function _processFormated(
    $str, 
    $return_raw = false,
    $defaults = null
  ) {
    $term_stack = empty($defaults) ? [
      'f' => [self::$fg_colors['default']],
      'b' => [self::$bg_colors['default']]
    ] : $defaults;

    if(!is_array($term_stack))
      $term_stack = self::_parseProps($term_stack);

    $defaults = $term_stack;

    $result = preg_replace_callback('|<([a-zA-Z:,-\\\/]*?)>|', function($m) use (&$term_stack, $return_raw, $defaults) {
      if($return_raw) return '';

      $escape_cmd = ['a' => [], 'f' => [], 'b' => []];
      $cmds = explode(',', $m[1]);

      if(!empty($cmds) && !empty($cmds[0])) {
        foreach($cmds as $cmd) {
          $cmd_parts = explode(':', $cmd);
          $cmd_type = $cmd_parts[0];
          $has_args = count($cmd_parts) > 1;

          switch($cmd_type) {
            case 'f':
            case 'b':
              $stack_length = count($term_stack[$cmd_type]);
              if(!$has_args) {
                if($stack_length > 1) {
                  array_pop($term_stack[$cmd_type]);
                  $escape_cmd[$cmd_type][] = $term_stack[$cmd_type][$stack_length - 2];
                } else {
                  $escape_cmd[$cmd_type][] = $term_stack[$cmd_type][0];
                }
              } else {
                $fcolor = self::_select_color($cmd_type, $cmd_parts[1]);
                $term_stack[$cmd_type][] = $fcolor;
                $escape_cmd[$cmd_type][] = $fcolor;
              }
              break;
            case 'a':
              if(!$has_args) {
                $escape_cmd[$cmd_type] = self::$attributes['reset']['unset_all'];
              } else {
                array_shift($cmd_parts);
                $escape_cmd[$cmd_type] = self::_select_attr($cmd_parts);
              }
              break;
          }
        }
      } else {
        $term_stack = $defaults;
        return "\033[0m";
      }

      return self::buildEscapeSequence($escape_cmd);
    }, $str);

    return  "{$result}\033[".self::$attributes['reset']['all']."m";
  }

  public static function getCXY()
  {
    if(strtoupper(substr(PHP_OS, 0, 3)) === 'WIN') {
      preg_match('/CON.*:(\n[^|]+?){3}(?<cols>\d+)/', `mode con /status`, $matches = null);
      return [
        'cols' => intval($matches['cols']),
        'rows' => intval(-1)
      ];
    } else {
      $size = explode(' ', `stty size`);
      return [
        'cols' => $size[1],
        'rows' => $size[0]
      ];
    }
  }

  public static function clrscr()
  {
    echo "\x1b[2J";
  }

  public static function beep()
  {
    echo "\x07";
  }

  public static function getch(/*&$chars*/)
  {
    //$chars = [];
    ////$term = `stty -g`;
    ////system("stty -icanon -echo");

    //$chars[] = fgetc(STDIN);
    $char = fgetc(STDIN);
    
    //$c = strtolower( trim( `bash -c "read -n 1 -t 10 ANS ; echo \\\$ANS"` ) );
    //$c = `bash -c "read -s -r -n 1 ANS; echo \\\$ANS"`;

    /*if(ord($chars[0]) == 27) {
      $chars[] = fgetc(STDIN);
      $chars[] = fgetc(STDIN);
    }*/
    
    ////system("stty {$term}");
    //return $chars[0];
    return $char;
  }

  public static function lineTitle($title, $fill = '_')
  {
    $cxy = self::getCXY();
    echo self::_processFormated($title).str_repeat($fill, $cxy['cols'] - strlen($title)).PHP_EOL;
  }

  public static function underlineTitle($title, $fill = '-')
  {
    $cxy = self::getCXY();
    echo self::_processFormated($title).PHP_EOL;
    echo str_repeat($fill, $cxy['cols']).PHP_EOL;
  }

  public static function uolineTitle($title, $fill = '-')
  {
    $cxy = self::getCXY();
    echo str_repeat($fill, $cxy['cols']).PHP_EOL;
    echo self::_processFormated($title).PHP_EOL;
    echo str_repeat($fill, $cxy['cols']).PHP_EOL;
  }
}
