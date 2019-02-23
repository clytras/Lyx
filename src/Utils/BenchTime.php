<?php

namespace Lyx\Utils;

use Lyx\Strings\Str;

class BenchTime
{
  // private $last_update = 0;
  // private $ms_interval = 0;
  // private $every = 0;
  // private $_fn_update = null;
  
  private $start_time;
  private $stop_time;
  private $is_running;
  
  private $checkpoints = [];
  private $seconds_format_precision = 3;

  public function __construct($doStart = false)
  {
    $this->reset();
    if($doStart) $this->start();
  }
  
  public function reset()
  {
    $this->start_time = 0;
    $this->stop_time = 0;
    $this->is_running = false;
  }
  
  public function start()
  {
    $this->checkpoints = [];
    $this->start_time = microtime(true);
    $this->is_running = true;
  }
  
  public function stop()
  {
    $this->stop_time = microtime(true);
    $this->is_running = false;
  }
  
  public function checkpoint($name)
  {
    $this->checkpoints[] = [
      'name' => $name,
      'time' => microtime(true)
    ];
  }
  
  public function resetCheckpoints()
  {
    $this->checkpoints = [];
  }
  
  public function elapsed()
  {
    if($this->is_running)
        return microtime(true) - $this->start_time;
    return $this->stop_time - $this->start_time;
  }

  public function __toString()
  {
    return $this->toString();
  }
  
  public function toString($format = null)
  {
    $result = '';
    $checkpoints = '';

    if(!empty($this->checkpoints)) {
      $lastCheckpointTime = $this->start_time;
      foreach($this->checkpoints as $checkpoint) {
        $checkpoints .= Str::format("  {name}: {time,.{$this->seconds_format_precision}}s ({fulltime,.{$this->seconds_format_precision}}s)\n", [
          'name' => $checkpoint['name'],
          'time' => $checkpoint['time'] - $lastCheckpointTime,
          'fulltime' => $checkpoint['time'] - $this->start_time
        ]);
        $lastCheckpointTime = $checkpoint['time'];
      }
    }
    
    if(empty($format)) {
      if($this->is_running)
        $result = Str::format("Time elapsed {0,.{$this->seconds_format_precision}}s", [$this->elapsed()]);
      else {
        $result = Str::format("Start {0,.{$this->seconds_format_precision}}s\nStop {1,.{$this->seconds_format_precision}}s\nElasped {2,.{$this->seconds_format_precision}}s", [
          $this->start_time,
          $this->stop_time,
          $this->elapsed()
        ]);
      }
    } else {
      $result = Str::format($format, [
        'start' => $this->start_time,
        'stop' => $this->stop_time,
        'elapsed' => $this->elapsed()
      ]);
    }
    
    if(!empty($checkpoints))
      $result .= "\nCheckpoints:\n".$checkpoints;
    
    return $result;
  }
}
