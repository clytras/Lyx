<?php

namespace Lyx\System;

class System
{
  public static function processExists($pid, &$info = null)
  {
    $dbgbt = debug_backtrace();
    return call_user_func_array('self::findProcessByPid', $dbgbt[0]['args']);
  }

  public static function findProcessByPid($pid, &$info = null)
  {
    $cmdfile = '/proc/'.$pid.'/cmdline';
    if($result = file_exists($cmdfile)) {
      if(func_num_args() == 2) {
        $result = self::findProcess('pid', $pid, $info) > 0;
        if($result && count($info) > 0)
          $info = $info[0];
      }
    }
    
    return $result;
  }

  public static function findProcessByName($pname, &$infos = null)
  {
    return self::findProcess('comm', $pname, $infos);
  }

  public static function findProcess($name, $value, &$infos)
  {
    $result = 0;
    $pattern = "/(\d{1,})\s{1,}(\d{1,})\s{1,}(\S{1,})\s{1,}(\S{1,})\s{1,}(.*)/";
    $ps = shell_exec ('ps ax --sort=pid -o pid= -o ppid= -o comm= -o user= -o command|grep -v grep|grep '.$value);
    $psl = explode(PHP_EOL, $ps);
    $infos = array();
    
    if($name == 'pid')
      $value = (int)$value;
    
    foreach($psl as $proc) {
      if(preg_match($pattern, $proc, $matches = null)) {
        $add = false;
        switch($name) {
          case 'pid':
            $add = (int)$matches[1] == $value;
            break;
          case 'comm':
          case 'name':
            $add = $matches[3] == $value;
            break;
        }
        
        if($add) {
          $infos[] = (object)[
            'pid' => (int)$matches[1],
            'ppid' => (int)$matches[2],
            'name' => $matches[3],
            'user' => $matches[4],
            'command' => $matches[5]
          ];
          $result++;
          if($name == 'pid')
            break;
        }
      }
    }
    
    return $result;
  }

  public static function command($cmd, $args_title = null)
  {
    $title = null;

    if(is_array($cmd)) {
      $cmd_exec = $cmd['exec'];
      if(isset($cmd['title']))
        $title = $cmd['title'];
    } else
      $cmd_exec = $cmd;
    
    if(is_array($args_title)) {
      if(isset($args_title['title']))
        $title = $args_title['title'];
    } elseif(is_string($args_title))
      $title = $args_title;
    
    if(!is_null($title))
      echo $title.PHP_EOL;
    
    passthru($cmd_exec);
  }

  public static function getServices()
  {
    return explode(PHP_EOL, `chkconfig --list | cut -f1 | sed 's/^[ \t]*//;s/[ \t]*$//'`);
  }

  public static function serviceExists($name)
  {
    return in_array($name, self::getServices());
  }
}
