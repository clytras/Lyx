<?php

namespace Lyx\System;

class Semaphore
{
  public $key = -1,
    $shm_id,
    $memsize = 10000,
    $perm = 0666;

  private $_vars = [];

  public function __construct($pathname, $proj)
  {
    $this->key = ftok($pathname, $proj);
    $this->shm_id = shm_attach($this->key);
  }

  public function hasValidKey()
  {
    return $this->key !== -1;
  }

  public function set($name, $value)
  {
    $this->_vars[$name] = $value;
  }

  public function setop($n, $op, $val = null)
  {
    $v =& $this->_vars;
    if($op == '++')
      $v[$n]++;
    elseif($op == '--')
      $v[$n]--;
    elseif($op == '.=')
      $v[$n] .= $val;
    elseif($op == '+=')
      $v[$n] += $val;
    elseif($op == '-=')
      $v[$n] -= $val;
    elseif($op == '*=')
      $v[$n] *= $val;
    elseif($op == '/=')
      $v[$n] /= $val;
    elseif($op == '%=')
      $v[$n] %= $val;
    elseif($op == '^=')
      $v[$n] ^= $val;
    elseif($op == '<<=')
      $v[$n] <<= $val;
    elseif($op == '>>=')
      $v[$n] >>= $val;
    elseif($op == '&=')
      $v[$n] &= $val;
    elseif($op == '|=')
      $v[$n] |= $val;
  }

  public function get($name, $default = '')
  {
    if(isset($this->_vars[$name]))
      return $this->_vars[$name];
    else
      return $default;    	
  }

  public function uset($name)
  {
    unset($this->_vars[$name]);
  }

  public function getVariables()
  {
    $this->_vars = shm_get_var($this->shm_id, 1);
  }

  public function putVariables()
  {
    return shm_put_var($this->shm_id, 1, $this->_vars);
  }
}
