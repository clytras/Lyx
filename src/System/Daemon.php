<?php

namespace Lyx\System;

class Daemon
{
  /**
   * System is unusable (will throw a System_Daemon_Exception as well)
   */
  const LOG_EMERG = 0,
    LOG_ALERT = 1,
    LOG_CRIT = 2,
    LOG_ERR = 3,
    LOG_WARNING = 4,
    LOG_NOTICE = 5,
    LOG_INFO = 6,
    LOG_DEBUG = 7;

  static protected $_logLevels = [
    self::LOG_EMERG => 'emerg',
    self::LOG_ALERT => 'alert',
    self::LOG_CRIT => 'crit',
    self::LOG_ERR => 'err',
    self::LOG_WARNING => 'warning',
    self::LOG_NOTICE => 'notice',
    self::LOG_INFO => 'info',
    self::LOG_DEBUG => 'debug'
  ];

  public $logFile,
    $loops = 0,
    $closing = false,
    $args = [
      'no-daemon' => false,
      'help' => false,
      'write-initd' => false
    ];

  public function log($message, $level)
  {
    // Determine what process the log is originating from and forge a logline
    //$str_ident = '@'.substr(self::_whatIAm(), 0, 1).'-'.posix_getpid();
    $str_date  = '[' . date('M d H:i:s') . '; CL]';
    $str_level = str_pad(self::$_logLevels[$level] . '', 8, ' ', STR_PAD_LEFT);
    $log_line  = $str_date.' '.$str_level.': '.$message; // $str_ident
    
    return $this->logText($log_line);
  }

  public function logText($text, $add_eol = true)
  {
    // 'Touch' logfile
    if(!file_exists($this->logFile))
      file_put_contents($this->logFile, '');
    
    if($add_eol)
      $text .= PHP_EOL;
    
    // Not writable even after touch? Allowed to echo again!!
    if(!is_writable($this->logFile)) {
      echo $text . "\n";
      return false;
    } else
      // Append to logfile
      return file_put_contents($this->logFile, $text, FILE_APPEND) !== false;
  }

  public static function getLogLevelText($log_level)
  {
    return self::$_logLevels[$log_level];
  }

  public function isClosing()
  {
    return $this->closing;
  }

  public function parseCmdArgs()
  {
    global $argv;
    foreach($argv as $k => $arg) {
      if(substr($arg, 0, 2) == '--' && isset($this->args[substr($arg, 2)]))
        $this->args[substr($arg, 2)] = true;
    }
  }

  public function getArg($name, $default = '')
  {
    if(isset($this->args[$name]))
      return $this->args[$name];
    else
      return $default;
  }

  public function arg($name, $default = '')
  {
    return $this->getArg($name, $default);
  }

  public function writeInitD($log = true)
  {
    // With the runmode --write-initd, this program can automatically write a
    // system startup file called: 'init.d'
    // This will make sure your daemon will be started on reboot
    if(!$this->args['write-initd']) {
      if($log)
        \System_Daemon::info('Not writing an init.d script this time');
    } else {
      if(($initd_location = \System_Daemon::writeAutoRun()) === false) {
        if($log)
          \System_Daemon::notice('Unable to write init.d script');
      } else {
        if($log)
          \System_Daemon::info('Sucessfully written startup script: %s', $initd_location);
      }
    }
  }

  public function start()
  {
    $this->loops = 0;
    \System_Daemon::start();
  }

  public function startIfDaemon()
  {
    // This program can also be run in the forground with runmode --no-daemon
    if(!$this->args['no-daemon'])
      // Spawn Daemon
      \System_Daemon::start();
  }

  public function stop()
  {
    \System_Daemon::stop();
  }

  public function iterate($sleepSeconds = 0)
  {
    \System_Daemon::iterate($sleepSeconds);
    $this->loops++;
  }
}
