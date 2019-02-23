<?php

namespace Lyx\Web;

class HttpAsync
{
  const JSMode_AppendScript = 1;
  const JSMode_Eval = 2;
	
  private $_js;
  private $_content;
  private $_autoFlushJs = true;
  private $_jsMode;
  
  public function __construct($doInit = true)
  {
    if($doInit)
      $this->init();
  }
  
  public function init()
  {
    set_time_limit(0);
    
    header('Content-type: text/html; charset=utf-8');
    header('Content-Encoding: none;');
    header('X-Accel-Buffering: no');

    ini_set('display_errors', '1');
    ini_set('output_buffering', 'Off');
    ini_set('zlib.output_compression', 0);
    ini_set('implicit_flush',1);
    set_include_path(get_include_path() . PATH_SEPARATOR . "$_SERVER[DOCUMENT_ROOT]/wiznet/lib/");
    ob_implicit_flush(true);
    @ob_end_clean();
    @ob_end_flush();
    ob_start();
    
    $this->_js = [];
    $this->_jsMode = self::JSMode_AppendScript;
    $this->_content = [];
  }
  
  public function __get($name)
  {
    $name = "_{$name}";
    if(isset($this->{$name}))
      return $this->{$name};
    return null;
  }
  
  public function __set($name, $value)
  {
    $name = "_{$name}";
    if(isset($this->{$name}))
      $this->{$name} = $value;
  }
  
  public function write($content)
  {
    echo $content;
    ob_flush();
    flush();
    usleep(10000);
  }
  
  public function writeln($content)
  {
    $this->write("{$content}\n");
  }
  
  public function writebr($content)
  {
    $this->write("{$content}<br>");
  }
  
  public function flushJs()
  {
    $js = '';
    $jq = '';
    
    foreach($this->_js as $_js) {
      if($_js['type'] == 'js') {
        if(!empty($jq)) {
          $js .= "jQuery(function($) { {$jq} });\n";
          $jq = '';
        }
        $js .= $_js['code'].";\n";
      } elseif($_js['type'] == 'jq') {
        $jq .= $_js['code'].";\n";
      }
      
      //$js .= "{$_js};\n";
    }
    
    if(!empty($jq))
      $js .= "jQuery(function($) { {$jq} });\n";
    
    if(!empty($js)) {
      if($this->_jsMode == self::JSMode_AppendScript)
        $this->write("<script type='text/javascript'>{$js}</script>");
      elseif($this->_jsMode == self::JSMode_Eval)
        $this->write("eval:{$js}");
    }
    
    $this->_js = [];
    return $this;
  }
  
  public function js($js, $type = 'js')
  {
    $this->_js[] = [
      'type' => $type,
      'code' => $js
    ];
    //$this->write("<script type='text/javascript'>{$js}</script>");
    if($this->_autoFlushJs)
      $this->flushJs();
    return $this;
  }
  
  public function elementText($elSelector, $text)
  {
    return $this->js("document.querySelector('{$elSelector}').innerText = \"".$this->_addSlashes($text)."\"");
  }
  
  public function elementHTML($elSelector, $html)
  {
    return $this->js("document.querySelector('{$elSelector}').innerHTML = \"".addslashes(nl2br($html))."\"");
  }
  
  public function elementValue($elSelector, $value)
  {
    return $this->js("document.querySelector('{$elSelector}').innerHTML = \"".$this->_addSlashes($value)."\"");
  }
  
  public function console()
  {
    $args = json_encode(func_get_args());
    return $this->js("console.log.apply(console, {$args})");
  }
  
  public function jq($jq)
  {
    $this->js($jq, 'jq');
    if($this->_autoFlushJs)
      $this->flushJs();
    return $this;
  }
  
  private function _addSlashes($text) 
  {
    return str_replace(["\r\n", "\n", "\r", "\t"], ["\\r\\n", "\\n", "\\r", "\\t"], addslashes($text));
  }
}
