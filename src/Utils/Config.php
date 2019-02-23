<?php

namespace Lyx\Utils;

class Config implements \ArrayAccess, \Iterator
{
  const USER_CONFIG = -1;
  const APPS_CONFIG = -2;
  const WIZ = -1024;

  private $_params,
    $_mode = null,
    $_shortcut = '';

  public function __construct($params = null)
  {
    if($params instanceof self)
        $this->_params = $params->get();
    elseif(is_array($params) || is_object($params))
      $this->_params = (array)$params; //Types::arrayToObject($params);
    elseif($params === self::USER_CONFIG) {
      $this->_params = lyx_config_get('user_config'); //Types::arrayToObject(lyx_config_get('user_config'));
      $this->_mode = self::USER_CONFIG;
    } elseif($params === self::APPS_CONFIG) {
      $this->_params = lyx_config_get('apps_config');
      $this->_mode = self::APPS_CONFIG;
    } elseif($params === self::WIZ) {
      $this->_params = lyx_config_get('lyx');
      $this->_mode = self::WIZ;
    } else
      $this->_params = []; //new \stdClass;
    
    if(func_num_args() == 2)
    {
      $this->_mode = null;
      $this->_params = Types::getByPath(func_get_arg(1), $this->_params, $this->_params);
    }
  }
  
  public function override($params)
  {
    if(is_array($params) || is_object($params))
      $override = (array)$params;
    elseif($params === self::USER_CONFIG)
      $overridee = lyx_config_get('user_config');
    elseif($params === self::APPS_CONFIG)
      $override = lyx_config_get('apps_config');
    elseif($params === self::WIZ)
      $override = lyx_config_get('lyx');
    else
      $override = [$params];

    if(func_num_args() == 2)
      $override = Types::getByPath(func_get_arg(1), $override, $override);

    $this->_params = array_replace_recursive($this->_params, $override);
  }
  
  public function merge($params)
  {
    if(is_array($params) || is_object($params))
      $merge = (array)$params;
    elseif($params === self::USER_CONFIG)
      $merge = lyx_config_get('user_config');
    elseif($params === self::APPS_CONFIG)
      $merge = lyx_config_get('apps_config');
    elseif($params === self::WIZ)
      $merge = lyx_config_get('lyx');
    else
      $merge = [$params];

    if(func_num_args() == 2)
      $merge = Types::getByPath(func_get_arg(1), $merge, $merge);

    $this->_params = array_merge_recursive($this->_params, $merge);
  }
  
  public function get($name = null, $default = null)
  {
    if($name === null)
        return $this->_params;
      
    $name = $this->resolveName($name);
    $method = 'get'.$name;

    if(method_exists($this, $method))
      return $this->$method();
    elseif(isset($this->_params[$name]))
      return $this->_params[$name];
    else
      return Types::getByPath($name, $this->_params, $default);
  }
  
  public function getJSON($name = null, $default = null)
  {
    return json_encode($this->get($name, $default));
  }
  
  public function getConfig($name, $default = [])
  {
    return new self($this->get($name, $default));
  }
  
  public function isEmpty($name)
  {
    $value = $this->get($name);
    
    if(func_num_args() == 2)
      return empty($value) ? func_get_arg(1) : $value; 
    
    return empty($value);
  }
  
  public function set($name, $value)
  {
    $name = $this->resolveName($name);
    $method = 'set'.$name;

    if(method_exists($this, $method))
      $this->$method($name, $value);
    elseif(Types::isNamePath($name))
      Types::setByPath($name, $value, $this->_params);
    else
      $this->_params[$name] = $value;
  }
  
  public function uset($name)
  {
    $name = $this->resolveName($name);
    if(Types::isNamePath($name))
      Types::unsetByPath($name, $this->_params);
    else
      unset($this->_params[$name]);
  }
  
  public function __set($name, $value)
  {
    $this->set($name, $value);
  }
  
  public function &__get($name)
  {
    if(isset($this->_params[$name])) {
      $result =& $this->_params[$name];
      if(is_array($result))
        $result = (new self())->setParamsByRef($result);
      return $result;
    }
    
    $result = $this->get($name);

    return $result;
  }
  
  public function __unset($name)
  {
    $this->uset($name);
  }
  
  public function __isset($name)
  {
    return $this->has($name);
  }
  
  public function has($name)
  {
    $name = $this->resolveName($name);
    if(Types::isNamePath($name))
      return Types::issetByPath($name, $this->_params);
    else
      return isset($this->_params[$name]);
  }
  
  public function setParamsByRef(&$params)
  {
    $this->_params =& $params;
    return $this;
  }
  
  public function offsetSet($offset, $value)
  {
    if(!is_null($offset))
        $this->set($offset, $value);
  }

  public function offsetExists($offset)
  {
    return $this->has($offset);
  }

  public function offsetUnset($offset)
  {
    $this->uset($offset);
  }

  public function offsetGet($offset)
  {
    return $this->__get($offset);
  }
  
  public function rewind()
  {
    return reset($this->_params);
  }
  
  public function current()
  {
    return current($this->_params);
  }
  
  public function key()
  {
    return key($this->_params);
  }
  
  public function next()
  {
    return next($this->_params);
  }
  
  public function valid()
  {
    return key($this->_params) !== null;
  }
  
  public function setShortcut()
  {
    $argv = func_get_args();
    $parts = [];
    
    foreach($argv as $arg) {
      if(is_array($arg))
        $parts = array_merge($parts, $arg);
      else
        $parts[] = $arg;
    }
    
    foreach($parts as &$part)
      $part = trim($part, '.');
    
    $this->_shortcut = implode('.', $parts);
  }
  
  public function resolveName($name)
  {
    if(is_array($name))
        $name = implode('.', $name);

    $path = $name;

    if($name[0] == '/')
      $path = trim($this->_shortcut.'.'.substr($name, 1), '.');

    return $path;
  }
  
  public function clearShortcut()
  {
    $this->_shortcut = '';
  }
  
  public function getParameters()
  {
    return $this->_params;
  }
  
  public function toJSON()
  {
    return json_encode($this->_params);
  }
  
  public function setParameters($params)
  {
    $this->_params = (array)$params;
  }
  
  public function updateSysConfig()
  { 
    if($this->_mode == self::USER_CONFIG)
      lyx_config_set('user_config', $this->_params);
    elseif($this->_mode == self::APPS_CONFIG)
      lyx_config_set('apps_config', $this->_params);
    else
      return false;
    return true;
  }
  
  public function isSystemConfig()
  {
    return $this->_mode == self::USER_CONFIG || $this->_mode == self::APPS_CONFIG;
  }
}
