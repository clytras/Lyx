<?php

namespace Lyx\Web;

class Http
{
  private $url;
  private $urlRequest;
  private $params;
  private $postParams;
  private $postParamsRaw;
  private $response;
  private $responseHeaders;
  private $responseHeadersRaw;
  private $responseHeadersAll;
  private $responseBody;
  private $curlHandle;
  private $cookies;
  private $location;
  private $preserveCookies;
  private $curlOptions;
  private $httpStatusCode;
  private $headers;
  private $headersCompiled;
  private $redirs;
  
  public function __construct($url, $params = [], $postParams = [])
  {
    $this->reset($url, $params, $postParams);
  }
  
  public function reset($url, $params = [], $postParams = [])
  {
    $this->url = $url;
    $this->redirs = 0;
    $this->params = $params;
    $this->postParams = $postParams;
    $this->postParamsRaw = '';
    $this->response = '';
    $this->responseHeaders = [];
    $this->responseHeadersRaw = '';
    $this->responseBody = '';
    $this->location = '';
    $this->curlHandle = null;
    $this->cookies = '';
    $this->preserveCookies = false;
    $this->curlOptions = [
      CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
      CURLOPT_HEADER => 1, // return HTTP headers with response
      CURLOPT_RETURNTRANSFER => 1, // return the response rather than output it
      CURLOPT_FOLLOWLOCATION => 0, // follow any "Location: " header that the server sends as part of the HTTP header
      //CURLOPT_USERAGENT => 'Mozilla/5.0 (Windows NT 10.0; WOW64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/44.0.2403.157 Safari/537.36'
    ];
    $this->httpStatusCode = 0;
    $this->headers = [
      //'User-Agent' => 'Mozilla/5.0 (Windows NT 10.0; WOW64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/44.0.2403.157 Safari/537.36',
      'Accept' => 'text/html,application/xhtml+xml,application/xml;q=0.9,*/*;q=0.8',
      'Accept-Language' => 'en-US;q=0.5,en;q=0.3',
      'Connection' => 'keep-alive'
    ];
  }
  
  public function setFollowLocation($value = true)
  {
    $this->curlOptions[CURLOPT_FOLLOWLOCATION] = $value ? 1 : 0;
  }
  
  public function getFollowLocation()
  {
    return (bool)$this->curlOptions[CURLOPT_FOLLOWLOCATION];
  }
  
  public function addHeaders($headers)
  {
    $this->headers = array_merge($this->headers, $headers);
  }
  
  public function addHeader($name, $value)
  {
    $this->headers[$name] = $value;
  }
  
  public function removeHeader($name)
  {
    if(isset($this->headers[$name]))
        unset($this->headers[$name]);
  }
  
  public function setPostParams($postParams)
  {
    $this->postParams = $postParams; 
  }
  
  public function setPostParamsRaw($postParamsRaw)
  {
    $this->postParamsRaw = $postParamsRaw; 
  }
  
  public function addPostParam($name, $value)
  {
    $this->postParams[$name] = $value;
  }
  
  public function addParams($params)
  {
    $this->params = array_merge($this->params, $params);
  }
  
  public function addParam($name, $value)
  {
    $this->params[$name] = $value;
  }
  
  public function removeParam($name)
  {
    if(isset($this->params[$name]))
        unset($this->params[$name]);
  }
  
  public function getResponse()
  {
    return $this->response;
  }
  
  public function getResponseBody()
  {
    return $this->responseBody;
  }
  
  public function getResponseHeaders()
  {
    return $this->responseHeaders;
  }
  
  public function getResponseHeadersAll()
  {
    return $this->responseHeadersAll;
  }
  
  public function hasResponseHeader($name)
  {
    return isset($this->responseHeaders[$name]);
  }
  
  public function getResponseHeader($name)
  {
    return $this->responseHeaders[$name];
  }
  
  public function getCookies()
  {
    return $this->cookies;
  }
  
  public function setPreserveCookies($value)
  {
    $this->preserveCookies = $value;
  }
  
  public function setCookies($value)
  {
    $this->cookies = $value;
  }
  
  public function getPreserveCookies()
  {
    return $this->preserveCookies;
  }
  
  public function getHttpStatusCode()
  {
    return $this->httpStatusCode;
  }
  
  private function _compileHeaders($appendHeaders = [])
  {
    $this->headersCompiled = [];
    $headersUse = array_merge($this->headers, $appendHeaders);

    foreach($headersUse as $header => $value) {
      if($this->hasLocation() && $this->getFollowLocation() && ($header == 'Content-Type' || $header == 'Content-Length'))
        continue;
      $this->headersCompiled[] = "{$header}: $value";
    }

    // if($this->hasLocation() && $this->getFollowLocation())
    //   exit();

    return $this->headersCompiled;
  }
  
  public static function scanHeaderGlue($response, &$firstLine = null)
  {
    $result = "\r\n";
    $pos = false;
    
    $pos_r = strpos($response, "\r");
    $pos_n = strpos($response, "\n");

    if($pos_r !== false)
        $pos = $pos_r;
    
    if($pos_n !== false && $pos > $pos_n)
        $pos = $pos_n;

    if($pos !== false) {
      if(func_num_args() == 2)
        $firstLine = substr($response, 0, $pos);
      $result = '';
      do {
        $result .= $response[$pos];
        $pos++;
      } while($response[$pos] == "\r" || $response[$pos] == "\n");
    }
    
    return $result;
  }

  private function _parseResponse()
  {
    $headerGlue = static::scanHeaderGlue($this->response, $httpStatus);
    
    if(strpos($httpStatus, ' 100 Continue') !== false) {
      $this->response = substr($this->response, strlen($httpStatus) + strlen($headerGlue));
      $headerGlue = static::scanHeaderGlue($this->response);
    }

    $this->responseHeaders = [];
    list($this->responseHeadersRaw, $this->responseBody) = explode("{$headerGlue}{$headerGlue}", $this->response, 2);

    $responseHeaders = explode($headerGlue, $this->responseHeadersRaw);
    $this->responseHeadersAll = [];
    foreach($responseHeaders as $header)
    {
      $this->responseHeadersAll[] = $header;

      if(substr($header, 0, 4) == 'HTTP')
        $this->responseHeaders['HTTP'] = $header;
      else {
        if(($pos = strpos($header, ':')) !== false) {
          $name = substr($header, 0, $pos);
          $value = substr($header, $pos + 1);
          $this->responseHeaders[$name] = trim($value);
          
          if($name == 'Set-Cookie') {
            $set_cookie = explode(';', $value);
            $this->cookies = trim($set_cookie[0]);
          } elseif($name == 'Location')
            $this->location = trim($value);
        }
      }
    }

    if(isset($this->responseHeaders['Content-Type'])) {
      if(preg_match('/(.*);charset=(.*)|(.*);?/', $this->responseHeaders['Content-Type'], $matches)) {
        if(count($matches) >= 3) {
          $charset = strtoupper($matches[2]);
          if(!empty($charset) && $charset != 'UTF-8') {
            $this->responseBody = mb_convert_encoding($this->responseBody, 'UTF-8', $charset);
          }
        }
      }
    }
    
    //if($this->hasLocation() && $this->getFollowLocation())
    //  exit();
  }
  
  public function hasLocation()
  {
    return !empty($this->location);
  }
  
  public function getLocation() 
  {
    return $this->location;
  }
  
  private function _buildGetUrl()
  {
    if($this->hasLocation() && $this->getFollowLocation())
      $this->urlRequest = $this->location;
    else {
      $queryString = '';

      if(count($this->params) > 0) {
        // $args = [];
        // foreach($this->params as $param => $value)
        // {
        //   $args[] = $param.'='.urlencode($value);
        // }
        
        // $queryString = implode('&', $args);
        
        $queryString = http_build_query($this->params);
        
        if(!empty($queryString)) {
          if(strpos($this->url, '?') === false)
            $queryString = '?'.$queryString;
          elseif(substr($this->url, -1) != '&')
            $queryString = '&'.$queryString;
        }
      }

      $this->urlRequest = $this->url.$queryString;
    }

    return $this->urlRequest;
  }
  
  public function setCurlOptions($curlOptions)
  {
    $this->curlOptions = array_merge($this->curlOptions, $curlOptions);
  }
  
  public function setCurlOption($option, $value)
  {
    $this->curlOptions[$option] = $value;
  }
  
  public function removeCurlOption($name)
  {
    if(isset($this->curlOptions[$name]))
        unset($this->curlOptions[$name]);
  }
  
  private function _prepareRequest()
  {
    $this->_buildGetUrl();

    if($this->preserveCookies)
      $this->headers['Cookie'] = $this->cookies;
    
    if(!isset($this->headers['Host']) || $this->hasLocation())
      $this->headers['Host'] = parse_url($this->urlRequest, PHP_URL_HOST);
    
    if(!isset($this->headers['Referer'])) // || $this->hasLocation())
      $this->headers['Referer'] = $this->urlRequest;
    
    //$this->location = '';
  }
  
  private function _applyCurlOptions()
  {
    curl_setopt($this->curlHandle, CURLOPT_URL, $this->urlRequest);
    $this->_compileHeaders();
    
    curl_setopt($this->curlHandle, CURLOPT_HTTPHEADER, $this->headersCompiled);
    curl_setopt($this->curlHandle, CURLOPT_SSL_VERIFYPEER, FALSE);
    
    foreach($this->curlOptions as $curlOption => $curlValue)
    {
      if(is_bool($curlValue))
        $curlValue = $curlValue ? 1 : 0;

      // if($curlOption == 'headers') {
      //   echo "Headers\n";
      //   print_r($this->_compileHeaders($curlValue));
      //   curl_setopt($this->curlHandle, CURLOPT_HTTPHEADER, $this->_compileHeaders($curlValue));
      // }
      // else
          curl_setopt($this->curlHandle, $curlOption, $curlValue);
    }
    
    if($this->getFollowLocation() && !$this->supportsCurlFollowLocation())
        curl_setopt($this->curlHandle, CURLOPT_FOLLOWLOCATION, false);
  }
  
  public function supportsCurlFollowLocation()
  {
    return empty(ini_get('open_basedir')) && ini_get('safe_mode') == false;
  }
  
  public function get()
  {
    //wprintln('GET ______________________');
    
    $this->curlHandle = curl_init();
    $this->_prepareRequest();
    $this->_applyCurlOptions();

    // curl_setopt($this->curlHandle, CURLOPT_REDIR_PROTOCOLS, CURLPROTO_HTTP|CURLPROTO_HTTPS);
    // curl_setopt($this->curlHandle, CURLOPT_MAXREDIRS, 100); 
    // curl_setopt($ch, CURLOPT_HTTP_VERSION, CURL_HTTP_VERSION_1_1);
    // curl_setopt($ch, CURLOPT_HEADER, 1); // return HTTP headers with response
    // curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
    // curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1); // return the response rather than output it
    // $this->response = curl_exec_follow($this->curlHandle);
    $this->response = curl_exec($this->curlHandle);
    $this->httpStatusCode = curl_getinfo($this->curlHandle, CURLINFO_HTTP_CODE);

    $this->_parseResponse();
    curl_close($this->curlHandle);
    $this->_handleRedirect();
    
    return $this->httpStatusCode;
  }
  
  public function post()
  {
    $this->curlHandle = curl_init($this->urlRequest);
    $this->_prepareRequest();

    //if($this->preserveCookies)
    //    $this->headers['Cookie'] = $this->cookies;
    
    // $headers = array(
    //   'Host: www.worldvaluessurvey.org',
    //   'User-Agent: Mozilla/5.0 (Windows NT 10.0; WOW64; rv:50.0) Gecko/20100101 Firefox/50.0',
    //   'Accept: * /*',
    //   'Accept-Language: el-GR,el;q=0.8,en-US;q=0.5,en;q=0.3',
    //   'Accept-Encoding: gzip, deflate',
    //   'Connection: keep-alive',
    //   'X-Requested-With: XMLHttpRequest',
    //   'Referer: http://www.worldvaluessurvey.org/AJDocumentation.jsp?CndWAVE=-1',
    //   'Cookie: '.$cookie
    // );
    
    if(!empty($this->postParams))
    {
      $postDataRaw = http_build_query($this->postParams);
      // $this->headers['Content-Length'] = strlen($postDataRaw);
      // $this->curlOptions[CURLOPT_POST] = 1;
      // $this->curlOptions[CURLOPT_POSTFIELDS] = $postDataRaw;
    } elseif(!empty($this->postParamsRaw)) {
      $postDataRaw = $this->postParamsRaw;
    } else 
      $postDataRaw = '';
    
    if(!empty($postDataRaw)) {
      $this->headers['Content-Length'] = strlen($postDataRaw);
      $this->curlOptions[CURLOPT_POST] = 1;
      $this->curlOptions[CURLOPT_POSTFIELDS] = $postDataRaw;
    }
    
    $this->_applyCurlOptions();
    
    // foreach($this->curlOptions as $curlOption => $curlValue)
    // {
    //   if(is_bool($curlValue))
    //     $curlValue = $curlValue ? 1 : 0;

    //   if($curlOption == 'headers')
    //     curl_setopt($this->curlHandle, CURLOPT_HTTPHEADER, $this->_compileHeaders($curlValue));
    //   else
    //     curl_setopt($this->curlHandle, $curlOption, $curlValue);
    // }

    // curl_setopt($this->curlHandle, CURLOPT_POST, 1);
    // curl_setopt($this->curlHandle, CURLOPT_POSTFIELDS, $postDataRaw);
    
    // curl_setopt($ch, CURLOPT_HTTP_VERSION, CURL_HTTP_VERSION_1_1);
    // curl_setopt($ch, CURLOPT_HEADER, 1); // return HTTP headers with response
    // curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
    // curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1); // return the response rather than output it
    // $this->response = curl_exec($this->curlHandle);
    $this->response = curl_exec($this->curlHandle);
    $this->httpStatusCode = curl_getinfo($this->curlHandle, CURLINFO_HTTP_CODE);
    
    $this->_parseResponse();
    curl_close($this->curlHandle);
    
    // if($this->httpStatusCode == 301 || $this->httpStatusCode == 302) {
    //   $this->removeCurlOption(CURLOPT_POST);
    //   $this->removeCurlOption(CURLOPT_POSTFIELDS);
    //   $this->removeHeader('Content-Length');
      
      
    //   wprintln('Status in:', $this->httpStatusCode);
    //   var_dump($this->hasLocation());
    //   var_dump($this->getFollowLocation());
    //   if($this->hasLocation() && $this->getFollowLocation()) {
    //     $this->redirs++;
    //     wprintln('Has location:', $this->location);
        
    //     if($this->redirs == 10)
    //       wprintln('Max redirs reached!');
    //     else
    //       $this->httpStatusCode = $this->get();
    //     $this->location = '';
    //   }
    // }
            
    $this->_handleRedirect();
    return $this->httpStatusCode;
  }
  
  private function _handleRedirect()
  {
    if($this->httpStatusCode == 301 || $this->httpStatusCode == 302)
    {
      $this->removeCurlOption(CURLOPT_POST);
      $this->removeCurlOption(CURLOPT_POSTFIELDS);
      $this->removeHeader('Content-Length');

      if($this->hasLocation() && $this->getFollowLocation()) {         
          if(++$this->redirs < 10)
              $this->httpStatusCode = $this->get();

          return;

          $this->location = '';
      }
    }
  }
}

if(!function_exists('curl_exec_follow')) {
function curl_exec_follow($ch, &$maxredirect = null) {
  
  // we emulate a browser here since some websites detect
  // us as a bot and don't let us do our job
  // $user_agent = "Mozilla/5.0 (Windows; U; Windows NT 5.1; en-US; rv:1.7.5)".
  //              " Gecko/20041107 Firefox/1.0";
  // curl_setopt($ch, CURLOPT_USERAGENT, $user_agent );

  $mr = $maxredirect === null ? 5 : intval($maxredirect);

  if(empty(ini_get('open_basedir')) && ini_get('safe_mode') == false) {
    curl_setopt($ch, CURLOPT_FOLLOWLOCATION, $mr > 0);
    curl_setopt($ch, CURLOPT_MAXREDIRS, $mr);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
  } else {
    curl_setopt($ch, CURLOPT_FOLLOWLOCATION, false);

    if($mr > 0) {
      $original_url = curl_getinfo($ch, CURLINFO_EFFECTIVE_URL);
      $newurl = $original_url;
      
      $rch = curl_copy_handle($ch);
      
      curl_setopt($rch, CURLOPT_HEADER, true);
      curl_setopt($rch, CURLOPT_NOBODY, true);
      curl_setopt($rch, CURLOPT_FORBID_REUSE, false);
      do {
        curl_setopt($rch, CURLOPT_URL, $newurl);
        $header = curl_exec($rch);
        if (curl_errno($rch)) {
          $code = 0;
        } else {
          $code = curl_getinfo($rch, CURLINFO_HTTP_CODE);
          if ($code == 301 || $code == 302) {
            preg_match('/Location:(.*?)\n/i', $header, $matches);
            $newurl = trim(array_pop($matches));
            
            // if no scheme is present then the new url is a
            // relative path and thus needs some extra care
            if(!preg_match("/^https?:/i", $newurl)){
              $newurl = $original_url . $newurl;
            }   
          } else {
            $code = 0;
          }
        }
      } while ($code && --$mr);
      
      curl_close($rch);
      
      if(!$mr) {
        if($maxredirect === null)
          trigger_error('Too many redirects.', E_USER_WARNING);
        else
          $maxredirect = 0;

        return false;
      }
      curl_setopt($ch, CURLOPT_URL, $newurl);
    }
  }
  return curl_exec($ch);
}
}