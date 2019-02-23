<?php

namespace Lyx\Utils\Conversions;

class Base64
{
  public static function base64UrlEncode($input)
  {
    $result = strtr(base64_encode($input), '+/', '-_');
    return str_replace('=', '', $result);
  }

  public static function base64UrlDecode($input)
  {
    return base64_decode(strtr($input, '-_', '+/'));
  }
}
