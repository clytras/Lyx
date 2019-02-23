<?php

namespace Lyx\Utils;

class EncryptedJsonResult extends JsonResult
{
  const Success = 'success';
  const Fail = 'fail';
  
  private $key;
  
  function __construct($key = '')
  {
      parent::__contruct();
      $this->key = $key;
  }

  public static function Supported()
  {
    return class_exists('OpenSSLEncrypted');
  }
  
  public function exit()
  {
    header('Content-Type: application/json');
    $this->_result['result'] = $this->result;
    $this->_result['message'] = $this->message;
    $this->_result['data'] = $this->data;
    
    $encrypter = new OpenSSLEncrypted($this->key);
    $data = ['com' => $encrypter->encrypt(json_encode($this->_result, JSON_UNESCAPED_SLASHES))];

    echo json_encode($data, JSON_UNESCAPED_SLASHES);
    exit(0);
  }
}