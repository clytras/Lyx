<?php

namespace Lyx\Utils;

class OpenSSLEncrypter
{
  const DefaultCypher = 'aes-256-cbc';
  public $cypher;
  public $iv = '';
  public $key = null;
  public $base64 = true;
  public $withIV = true;
  
  private static $functionsCheck = [
    'openssl_random_pseudo_bytes',
    'openssl_cipher_iv_length',
    'openssl_encrypt',
    'openssl_decrypt'
  ];
  
  function __construct($key = '', $cypher = self::DefaultCypher)
  {
    $this->cypher = $cypher;
    $this->key = $key;
  }
  
  public static function Supported()
  {
    foreach(static::$functionsCheck as $function)
      if(!function_exists($function))
        return false;
    return true;
  }
  
  public function encrypt($plainText)
  {
    $this->iv = openssl_random_pseudo_bytes(openssl_cipher_iv_length($this->cypher));
    $cryptText = openssl_encrypt($plainText, $this->cypher, $this->key, OPENSSL_RAW_DATA, $this->iv);
    $result = $this->withIV ? $cryptText.$this->iv : $cryptText;
    return $this->base64 ? base64_encode($result) : $result;
  }
  
  public function decrypt($cryptText)
  {
    $plainText = '';
    if($this->base64)
      $cryptText = base64_decode($cryptText);

    if($this->withIV) {
      $ivSize = openssl_cipher_iv_length($this->cypher);
      $this->iv = substr($cryptText, -$ivSize);
      $cryptText = substr($cryptText, 0, -$ivSize);
    }

    return openssl_decrypt($cryptText, $this->cypher, $this->key, OPENSSL_RAW_DATA, $this->iv);
  }
}
