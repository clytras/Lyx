<?php

namespace Lyx\Utils;

class JsonResult
{
  const Success = 'success';
  const Fail = 'fail';
  
  protected $message;
  protected $result;
  protected $data;
  protected $_result;
  
  function __construct($options = null)
  {
    $this->_result = [];
    $this->custom = [];
    $this->data = [];
  }
  
  public function data()
  {
    if(func_num_args() == 1) {
      $arg = func_get_arg(0);
      if(is_array($arg)) {
        foreach($arg as $name => $value) {
          $this->data[$name] = $value;
        }
      } else {
        $this->data = $arg;
      }
    } elseif(func_num_args() > 0)
      $this->data[func_get_arg(0)] = func_get_arg(1);

    return $this;
  }
  
  public function custom($key, $value)
  {
    $this->_result[$key] = $value;
    return $this;
  }
  
  public function message($message)
  {
    $this->message = $message;
    return $this;
  }
  
  public function result($result)
  {
    $this->result = $result;
    return $this;
  }
  
  public function success()
  {
    if(func_num_args() == 1)
      $this->message = func_get_arg(0);
    $this->result = self::Success;
    $this->exit();
  }
  
  public function fail()
  {
    if(func_num_args() == 1)
      $this->message = func_get_arg(0);
    $this->result = self::Fail;
    $this->exit();
  }
  
  public function exit()
  {
    header('Content-Type: application/json');
    $this->_result['result'] = $this->result;
    $this->_result['message'] = $this->message;
    $this->_result['data'] = $this->data;
    echo json_encode($this->_result, JSON_UNESCAPED_SLASHES);
    exit(0);
  }
}