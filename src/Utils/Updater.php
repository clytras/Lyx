<?php

namespace Lyx\Utils;

class Updater
{
  private $last_update = 0;
  private $ms_interval = 0;
  private $ticks_interval = 0;
  private $every = 0;
  private $_fn_update = null;

  public function __construct($fnUpdate = null)
  {
    $this->_fn_update = $fnUpdate;
    return $this;
  }
  
  private function _timemilliseconds() {
    $mt = explode(' ', microtime());
    return ((int)$mt[1]) * 1000 + ((int)round($mt[0] * 1000));
  }
  
  public function start()
  {
    $this->last_update = $this->ticks_interval != 0 ? $this->ticks_interval : $this->_timemilliseconds();
  }
  
  public function stop()
  {
    $this->last_update = 0;
  }
  
  public function every($every)
  {
    $this->every = $every;
    return $this;
  }
  
  public function minutes()
  {
    $this->ms_interval = intval($this->every * 60 * 1000);
    $this->ticks_interval = 0;
    return $this;
  }
  
  public function seconds()
  {
    $this->ms_interval = intval($this->every * 1000);
    $this->ticks_interval = 0;
    return $this;
  }
  
  public function ticks()
  {
      $this->ticks_interval = $this->every;
      $this->ms_interval = 0;
      return $this;
  }
  
  public function milliseconds()
  {
    $this->ms_interval = $this->every;
    return $this;
  }
  
  public function needsUpdate()
  {
    if($this->last_update) {
      if($this->ms_interval) {
        return $this->_timemilliseconds() > ($this->last_update + $this->ms_interval);
      } elseif($this->ticks_interval) {
        return ++$this->last_update >= $this->ticks_interval;
      }
    }
    return false;
  }
  
  public function finish()
  {
    call_user_func_array([$this, 'update'], func_get_args());
    $this->stop();
  }
  
  public function checkUpdate()
  {
    if($this->needsUpdate())
      call_user_func_array([$this, 'update'], func_get_args());
    return $this;
  }
  
  public function update()
  {
    if($this->last_update > 0) {
      if(is_callable($this->_fn_update))
        call_user_func_array($this->_fn_update, func_get_args());
      $this->last_update = $this->ticks_interval != 0 ? $this->ticks_interval : $this->_timemilliseconds();
    }
    return $this;
  }
}
