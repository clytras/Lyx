<?php

namespace Lyx\Utils;

class MCrypter
{
  public $cypher = 'rijndael-256';
  public $mode = 'cfb';
  public $key = null;
  
  public static $functionsCheck = [
    'mcrypt_module_open',
    'mcrypt_enc_get_iv_size',
    'mcrypt_create_iv',
    'mcrypt_generic_init',
    'mcrypt_generic',
    'mcrypt_generic_deinit'
  ];

  public static function Supported()
  {
    foreach ($this->functionsCheck as $function) {
      if(!function_exists($function)) {
        return false;
      }
    }
    return true;
  }
  
  public function init()
  {
    if(!$this->cypher || !$this->mode || !$this->key) {
      throw new \Exception('Encryption library called without proper config');
    }
    foreach ($this->functionsCheck as $function) {
      if(!function_exists($function)) {
        throw new \Exception('Encryption library called without function ' . $function);
      }
    }
    return parent::init();
  }
  
  public function encrypt($plaintext)
  {
    $td = mcrypt_module_open($this->cypher, '', $this->mode, '');
    $this->verifyModuleOpen($td);
    $iv = mcrypt_create_iv(mcrypt_enc_get_iv_size($td), MCRYPT_RAND);
    if($iv === false) {
      throw new \Exception('Problem opening via mcrypt_create_iv');
    }        
    $init = mcrypt_generic_init($td, $this->key, $iv);
    $this->verifyGenericInit($init);
    $crypttext = mcrypt_generic($td, $plaintext);
    mcrypt_generic_deinit($td);
    return $iv . $crypttext;
  }
  
  public function decrypt($crypttext)
  {
    $plaintext = '';
    set_error_handler(function() { /* ignore errors */ });
    
    $td = mcrypt_module_open($this->cypher, '', $this->mode, '');
    $this->verifyModuleOpen($td);
    $ivsize = mcrypt_enc_get_iv_size($td);
    $iv = substr($crypttext, 0, $ivsize);
    $crypttext = substr($crypttext, $ivsize);
    if($iv) {
      $init = mcrypt_generic_init($td, $this->key, $iv);
      $this->verifyGenericInit($init);
      $plaintext = mdecrypt_generic($td, $crypttext);
    }
    restore_error_handler();
    return $plaintext;
  }
  
  private function verifyModuleOpen($td)
  {
    if($td === false) {
      throw new \Exception('Problem opening via mcrypt_module_open, using cypher ' . $this->cypher . ' and mode ' . $this->mode);
    }        
  }
  
  private function verifyGenericInit($init)
  {
    if($init === false) {
      throw new \Exception('Incorrect parameters passed to mcrypt_generic_init');
    } else if ($init < 0) {
      switch ($init) {
        case -3:
          throw new \Exception('Incorrect key length passed to mcrypt_generic_init');
          break;
        case -4:
          throw new \Exception('Memory allocation problems using mcrypt_generic_init');
          break;
        default:
          throw new \Exception('Unknown error using mcrypt_generic_init');
          break;
      }
    }        
  }
}
