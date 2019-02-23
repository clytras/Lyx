<?php

namespace Lyx\Utils;

class Session
{
  public function __construct($start = true)
  {
    if($start) {
        session_start();
        session_write_close();
    }
  }
  
  public function has($name)
  {
    return isset($_SESSION[$name]);
  }
  
  public function get($name, $default = null)
  {
    if(isset($_SESSION[$name]))
        return $_SESSION[$name];
    return $default;
  }
  
  public function set($name, $value)
  {
    session_start();
    $_SESSION[$name] = $value;
    session_write_close();
  }
  
  public function remove($name)
  {
    if(isset($_SESSION[$name])) {
      session_start();
      unset($_SESSION[$name]);
      session_write_close();
    }
  }
}
