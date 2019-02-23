<?php

namespace Lyx\Utils;

class Logger
{
  const DefaultDataFileJson = __DIR__.'/../~dbg/logger/data.json';
  const DefaultDataType = 'json';
  
  const Log = 'log';
  const Success = 'success';
  const Warning = 'warning';
  const Notice = 'notice';
  const Error = 'error';
  
  public $options = array();
  public $do_log = true;
  
  function __construct($options = null)
  {
    if(empty($options)) {
      $options = [
        'type' => self::DefaultDataType,
        'file' => self::DefaultDataFileJson,
        'id' => null
      ];
    } else {
      if(!is_array($options))
        $options = array();

      if(!isset($options['type']))
        $options['type'] = self::DefaultDataType;

      if(!isset($options['file']))
        $options['file'] = self::DefaultDataFileJson;

      if(isset($options['id']))
        $this->setId($options['id']);
    }
    $this->options = $options;
  }
  
  public function setId($id) {
    $this->options['id'] = $id;
    switch($this->options['type']) {
      case 'json':
        if(!isset($this->options['file']) || empty($this->options['file']))
          $this->options['file'] = self::DefaultDataFileJson;
        $this->options['file'] = dirname($this->options['file']).'/'.basename($this->options['file'], '.json')."_{$id}.json";
        break;
    }
  }
  
  public function logIf($expression) {
    $this->do_log = $expression;
  }
  
  public function getFilename()
  {
    return basename($this->options['file']);
  }
  
  public function log($title, $data = null, $type = self::Log)
  {
    if(!$this->do_log) return;
    if(func_num_args() == 1)
      $data = $title;

    $this->_write($title, $data, $type);
  }
  
  public function log_var_dump($title, $data, $type = self::Log)
  {
    if(!$this->do_log) return;
    $this->_write($title, var_dump_return($data), $type);
  }
  
  public function log_r($title, $data, $type = self::Log)
  {
    if(!$this->do_log) return;
    $this->_write($title, print_r($data, true), $type);
  }
  
  public function log_exception($title, $exception, $include_exception = true, $type = self::Error) {
    if(!$this->do_log) return;
    $data = [];
    
    if(method_exists($exception, 'getCode'))
      $data['code'] = $exception->getCode();
    
    if(method_exists($exception, 'getData'))
      $data['data'] = $exception->getData();
    
    if(method_exists($exception, 'getMessage'))
      $data['message'] = $exception->getMessage();
    
    if($include_exception)
      $data['exception'] = $exception;
    
    $this->log_r("Exception in <code>{$title}</code>", $data, $type);
  }

  public function clear()
  {
    switch($this->options['type']) {
      case 'json':
        $this->_clearJSON();
        break;
    }
  }
  
  public function delete($id)
  {
    switch($this->options['type']) {
      case 'json':
        return $this->_deleteJSON($id);
    }
  }
  
  private function _write($title, $data = null, $type = self::Log)
  {
    switch($this->options['type']) {
      case 'json':
        $this->_writeJSON($title, $data, $type);
        break;
    }
  }
  
  private function _deleteJSON($id)
  {
    $deleted = 0;
    if(file_exists($this->options['file'])) {
      $jsonData = @json_decode(file_get_contents($this->options['file']), JSON_OBJECT_AS_ARRAY);
      $deleteIndex = -1;
      
      if(isset($jsonData['records'])) {
        foreach($jsonData['records'] as $index => $record) {
          if($record['id'] == $id) {
            $deleteIndex = $index;
            break;
          }
        }
        
        if($deleteIndex >= 0) {
          array_splice($jsonData['records'], $deleteIndex, 1);
          file_put_contents($this->options['file'], json_encode($jsonData, JSON_UNESCAPED_SLASHES|JSON_PRETTY_PRINT));
          $deleted++;
        }
      }
    }
    return $deleted;
  }
  
  private function _clearJSON()
  {
    if(file_exists($this->options['file']))
      file_put_contents($this->options['file'], json_encode(array()));
  }
  
  private function _writeJSON($title, $data, $type)
  {
    if(file_exists($this->options['file']))
      $jsonData = @json_decode(file_get_contents($this->options['file']), JSON_OBJECT_AS_ARRAY);
    else
      $jsonData = array();
    
    $jsonData['records'][] = [
      'id' => dechex(millitime()).dechex(rand(0x1000, 0xffff)),
      'title' => $title,
      'type' => strtolower(trim($type)),
      'time' => \DateTime::createFromFormat('U.u', sprintf('%.f', microtime(true)))->setTimezone(new \DateTimeZone('Europe/Athens'))->format('d/m H:i:s.u'),
      'data' => $data
    ];
    
    file_put_contents($this->options['file'], json_encode($jsonData, JSON_UNESCAPED_SLASHES|JSON_PRETTY_PRINT));
  }
  
  public static function SuccessOrError($exp) {
    return $exp ? self::Success : self::Error;
  }
  
  public static function SuccessOrNotice($exp) {
    return $exp ? self::Success : self::Notice;
  }
}