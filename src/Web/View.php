<?php

namespace Lyx\Web;

use Lyx\Strings\Str;

class View
{
  public $base_path;
  public $html;
  public $utf8_to_html_entities = true;
  
  public $view_file;
  
  public function __construct($view_file = null)
  {
    $this->view_file = $view_file;
  }
  
  public function setBasePath($path)
  {
    if(substr($path, -1) != DIRECTORY_SEPARATOR)
      $this->base_path = $path.DIRECTORY_SEPARATOR;
    else 
      $this->base_path = $path;
  }
  
  public function setConvertUTF8ToHtmlEntities($value)
  {
    $this->utf8_to_html_entities = $value;
  }
  
  public function load($view_file_or_html, $params = [])
  {
    if(is_array($view_file_or_html))
      $params = $view_file_or_html;
    else
      $this->view_file = file_exists($this->base_path.$view_file_or_html) ? $this->base_path.$view_file_or_html : $view_file_or_html;
    
    return $this->html = self::loadView($this->view_file, $params, $this->utf8_to_html_entities);
  }
  
  public static function loadView($view_file_or_html, $params = [], $utf8_to_html_entities = true)
  {
    $html = '';

    if(file_exists($view_file_or_html))
      $view_file = $view_file_or_html;
    else
      $html = $view_file_or_html;
    
    if(empty($html)) {
      $view_ext = substr($view_file, -4);
      if($view_ext == '.php') {
        extract($params);
        ob_start();
        include $view_file;
        $html = ob_get_contents();
        ob_end_clean();
      } else {
        $html = file_get_contents($view_file);
      }
    }

    $html = Str::format($html, $params);
    
    if($utf8_to_html_entities)
      $html = mb_convert_encoding($html, 'HTML-ENTITIES', 'UTF-8');
    
    return $html;
  }
}
