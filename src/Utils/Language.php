<?php

namespace Lyx\Utils;

use Lyx\System\Path,
    Lyx\System\FS,
    Lyx\Strings\Str;

class Language
{
  const LanguageDefault = 'en';
  
  private $_lang_selected_key = '';
  private $_lang_selected;
  private $_lang_selected_file;
  private $_lang_default;
  private $_lang_default_key = '';
  private $_lang_default_file;
  private $_lang_path;
  
  private $_convert_encoding_from = null;
  private $_convert_encoding_to = null;
  
  private $_config_class = ConfigUnmethotable::class;
  
  function __construct($selected = self::LanguageDefault, $default = self::LanguageDefault, $lang_path = '')
  {
    if(!empty($lang_path))
      $this->_lang_path = $lang_path;

    if(!empty($selected))
      $this->setLang($selected);
    else 
      $this->_lang_selected_key = '';
    
    if(!empty($default) && $default != $selected)
      $this->setLang($default, 'default');
    else 
      $this->_lang_default_key = '';
  }
      
  public function setLang($lang, $which = 'selected')
  {
    if(!property_exists ($this, "_lang_{$which}_key"))
      return;

    $this->{"_lang_{$which}_key"} = '';
    if(is_array($lang) || is_object($lang))
      $this->{"_lang_{$which}"} = new $this->_config_class($lang);
    else {
      $this->{"_lang_{$which}_file"} = Path::compose($this->_lang_path, "{$lang}.php");
      if(FS::fileExists($this->{"_lang_{$which}_file"})) {
        $this->{"_lang_{$which}_key"} = $lang;
        $this->{"_lang_{$which}"} = new $this->_config_class(include($this->{"_lang_{$which}_file"}));
      } else
        $this->{"_lang_{$which}"} = [];
    }
  }
  
  public function __get($name)
  {
    switch(strtolower($name)) {
      case 'selected': return $this->_lang_selected_key;
      case 'default': return $this->_lang_default_key;
    }
  }
  
  public function setConvertEncoding($to, $from = null)
  {
    $this->_convert_encoding_to = $to;
    $this->_convert_encoding_from = $from;
  }
  
  public function overrideFromPath($path)
  {
    if(!FS::dirExists($path))
      return;
    
    $what = ['selected', 'default'];
    $files = [];
    foreach($what as $lang) {
      $file = Path::compose($path, $this->{"_lang_{$lang}_key"}.'.php');
      if(FS::fileExists($file))
        $files[$lang] = Path::compose($path, $this->{"_lang_{$lang}_key"}.'.php');
    }
    
    if(!isset($files['selected']))
      $files['selected'] = Path::compose($path, 'lang.php');
    
    foreach($files as $key => $file) {
      //$file = Path::compose($path, $this->{"_lang_{$lang}_key"}.'.php');
      if(FS::fileExists($file) && $this->{"_lang_{$key}"} instanceof $this->_config_class) {
        $this->{"_lang_{$key}"}->override(include($file));
      }
    }
  }
  
  public function has($name)
  {
    $result = false;
    
    if($this->_lang_default instanceof $this->_config_class)
      $result |= $this->_lang_default->has($name);
    
    if($this->_lang_selected instanceof $this->_config_class)
      $result |= $this->_lang_selected->has($name);
    
    return $result;
  }
  
  public function get($name, $default = null, $selects = [])
  {
    $result = $default;
    
    if($this->_lang_default instanceof $this->_config_class && $this->_lang_default->has($name))
      $result = $this->_lang_default->get($name);
    
    if($this->_lang_selected instanceof $this->_config_class && $this->_lang_selected->has($name))
      $result = $this->_lang_selected->get($name);
    
    // if(is_null($default))
    //   if($this->_lang_default instanceof Config)
    //     $result = $this->_lang_default->get($name);

    // if($this->_lang_selected instanceof Config)
    //   $result = $this->_lang_selected->get($name, $result);
    
    if(is_null($result))
      $result = func_num_args() == 1 ? $name : $default;
    else {
      if(!empty($selects)) {
        $results = explode('|', $result);
        $result = $results[0];
        if(count($results) > 1) {
          for($i = 0; $i < count($selects); $i++) {
            if($selects[$i]) {
              $result = $results[$i];
              break;
            }
          }
        }
      }
    }
    
    return $this->_checkEncoding($this->_extendLang($name, $result));
  }
  
  private function _checkEncoding($text)
  {
    if(!empty($this->_convert_encoding_to)) {
      $encoding_from = empty($this->_convert_encoding_from) ? $this->_convert_encoding_from : mb_internal_encoding();
      return mb_convert_encoding($text, $this->_convert_encoding_to, $encoding_from);
    }
    return $text;
  }
  
  public function def($name, $default = null)
  {
    $result = $default;
    if($this->_lang_default instanceof $this->_config_class)
      $result = $this->_lang_default->get($name);
    return $result;
  }
  
  public function trans($name, $args = [], $selects = [])
  {
    $names = explode('|', $name);
    // if(count($names) > 1) {
    //   if(!empty($selects)) {
    //     for($i = 0; $i < count($selects); $i++) {
    //       if($selects[$i]) {
    //         $name_used = $names[$i];
    //         break;
    //       }
    //     }
    //   }
    // } else {
    //   $name_used = $names[0];
    // }
    
    $result = $this->get($name, $name, $selects);
    
    if(!empty($args) && preg_match_all('/\:(\w+)/', $result, $matches = null)) {
      foreach($matches[1] as $index => $key) {
        if(isset($args[$key]))
            $result = str_replace($matches[0][$index], $args[$key], $result);
      }
    }

    return $result;
  }
  
  private function _extendLang($name, $text)
  {
    $result = $text;

    if(preg_match_all('/\$\{(.*?)\}/', $text, $matches = null)) {
      foreach($matches[1] as $index => $key) {
        //$searchKeys = [];
        
        if(substr($key, 0, 2) == '_.') {
          $search_key = substr($key, 2);
          $search_keys[$name] = $search_key;
          $name_parts = explode('.', $name);
          
          if(count($name_parts) > 1) {
            $current_ns = '';
            for($i = 0; $i < count($name_parts) - 1; $i++) {
              $name_part = $name_parts[$i];
              $current_ns .= "{$name_part}.";
              //$search_keys[trim($current_ns, '.')] = "{$current_ns}{$search_key}";
              $search_keys[] = "{$current_ns}{$search_key}";
            }
            $search_keys = array_reverse($search_keys);
          }
        } else
          $search_keys[$name] = $key;
        
        $found = false;
        
        foreach($search_keys as $search_key) {
          if($this->_lang_selected instanceof $this->_config_class && $this->_lang_selected->has($search_key)) {
            $found = true;
            $current_name = $search_key;
            $value = $this->_lang_selected->get($search_key);
            break;
          } elseif($this->_lang_default instanceof $this->_config_class && $this->_lang_default->has($search_key)) {
            $found = true;
            $current_name = $search_key;
            $value = $this->_lang_default->get($search_key);
            break;
          }
          //else
          //  continue;
        }
        
        if($found) {
          $result = str_replace($matches[0][$index], $this->_extendLang($current_name, $value), $result);
        }
      }
    }

    return $result;
  }
  
  public function format($name, $params)
  {
    return Str::format($this->get($name), $params);
  }
}
