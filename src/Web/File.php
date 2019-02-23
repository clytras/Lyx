<?php

namespace Lyx\Web;

class File
{
  public static function webFileExists($url)
  {
    $ch = curl_init($url);

    curl_setopt($ch, CURLOPT_NOBODY, true);
    curl_exec($ch);
    $retcode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);

    // $retcode >= 404 -> not found, $retcode = 200, found.
    return $retcode == '200';
  }

  public static function exists($url)
  {
    return self::webFileExists($url);
  }

  public static function urlFetch($cfg)
  {
    $get_header = isset($cfg['getHeader']) ? (bool)$cfg['getHeader'] : false;
    
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $cfg['url']);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, isset($cfg['timeout']) ? $cfg['timeout'] : 30);
    curl_setopt($ch, CURLOPT_VERBOSE, true);
    
    if($get_header)
      curl_setopt($ch, CURLOPT_HEADER, true);
    
    $headers = array(
      'Host: '.self::getUrlDomain($cfg['url']),
      'Accept: text/html,application/xhtml+xml,application/xml;q=0.9,image/webp,*/*;q=0.8',
      'Accept-Language: el-GR,el;q=0.8,en;q=0.6',
      'User-Agent: Mozilla/5.0 (Windows NT 6.1; WOW64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/41.0.2272.76 Safari/537.36'
    );
    curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);

    if(isset($cfg['postData'])) {
      curl_setopt($ch, CURLOPT_POST, true);
      curl_setopt($ch, CURLOPT_POSTFIELDS, is_array($cfg['postData']) ? http_build_query($cfg['postData']) : $cfg['postData']);
    } else if(isset($cfg['method']) && $cfg['method'] != 'GET')
      curl_setopt( $curl, CURLOPT_CUSTOMREQUEST, $method );

    if(isset($cfg['cookies'])) {
      $cookies = array();
      foreach($cfg['cookies'] as $name => $value)
        $cookies[] = $name.'='.rawurlencode($value);
      
      curl_setopt($ch, CURLOPT_COOKIE, implode('; ', $cookies));
    }

    $data = curl_exec($ch);
    curl_close($ch);
    
    if($get_header) {
      list($headers, $content) = explode("\r\n\r\n", $data, 2);
      $data = [
        'headers' => $headers,
        'content' => $content
      ];
    }
    
    return $data;
  }

  public static function getUrlDomain($url)
  {
    $parsed_url = parse_url($url);

    if(isset($parsed_url['host']))
      $host = $parsed_url['host'];
    else
      $host = $url;

    $bits = explode('/', $host);
    if($bits[0]=='http:' || $bits[0]=='https:')
      $domainb= $bits[2];
    else
      $domainb= $bits[0];

    unset($bits);
    $bits = explode('.', $domainb);
    $idz = count($bits);
    $idz -= 3;

    if(strlen($bits[$idz + 2]) == 2)
      $url = $bits[$idz].'.'.$bits[$idz + 1].'.'.$bits[$idz + 2];
    elseif(strlen($bits[$idz + 2]) == 0)
      $url = $bits[$idz].'.'.$bits[$idz + 1];
    else
      $url = $bits[$idz + 1].'.'.$bits[$idz + 2];

    return $url;
  }
}
