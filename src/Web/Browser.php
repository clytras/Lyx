<?php

namespace Lyx\Web;

class Browser
{
  public static function acceptedLanguages()
  {
    $accept_language = $_SERVER["HTTP_ACCEPT_LANGUAGE"];
    $accept_language_length = strlen($accept_language);
    $in_lang = true;
    $in_q = false;
    $current_langs = [];
    $current_lang = '';
    $current_q = '';
    $languages = [];

    for($i = 0; $i < $accept_language_length; $i++) {
      $c = $accept_language[$i];
      $last = ($i == ($accept_language_length - 1));
      
      if($last) {
        if($in_lang)
          $current_lang .= $c;
        elseif($in_q)
          $current_q .= $c;
      }
      
      if($c == ',' || $c == ';' || $last) {
        if($in_lang) {
          $current_langs[] = $current_lang;
          $current_lang = '';
          if($c == ';')
            $in_lang = !($in_q = $in_lang);
        } elseif($in_q) {
          $q = explode('=', $current_q);
          foreach($current_langs as $language)
            $languages[$language] = floatval($q[1]);
          $current_langs = [];
          $current_q = '';
          $in_lang = !($in_q = $in_lang);
        }
      } elseif($in_lang)
        $current_lang .= $c;
      elseif($in_q)
        $current_q .= $c;
    }

    return $languages;
  }

  public static function selectClientLanguage($priorities = ['en'], $default = 'en')
  {
    $accepted_languages = self::acceptedLanguages();
    $result = $default;
    foreach($priorities as $language) {
      if(array_key_exists($language, $accepted_languages)) {
        $result = $language;
        break;
      }
    }
    return $result;
  }
}
