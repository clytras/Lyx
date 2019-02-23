<?php

namespace Lyx\Utils;

class Hash
{
  public static function GUIDv4($trim = true)
  {
    // Windows
    if(function_exists('com_create_guid')) {
      $guidv4 = com_create_guid();
      return $trim ? trim($guidv4, '{}') : $guidv4;
    }

    // OSX/Linux
    if(function_exists('openssl_random_pseudo_bytes')) {
      $data = openssl_random_pseudo_bytes(16);
      $data[6] = chr(ord($data[6]) & 0x0f | 0x40); // set version to 0100
      $data[8] = chr(ord($data[8]) & 0x3f | 0x80); // set bits 6-7 to 10
      $guidv4 = vsprintf('%s%s-%s-%s-%s-%s%s%s', str_split(bin2hex($data), 4));
    } else {
      // Fallback (PHP 4.2+)
      mt_srand((double)microtime() * 10000);
      $charid = strtolower(md5(uniqid(rand(), true)));
      $hyphen = '-';
      $guidv4 = 
        substr($charid,  0,  8).$hyphen.
        substr($charid,  8,  4).$hyphen.
        substr($charid, 12,  4).$hyphen.
        substr($charid, 16,  4).$hyphen.
        substr($charid, 20, 12);
    }
        
    return $trim ? $guidv4 : "{{$guidv4}}";
  }
}

