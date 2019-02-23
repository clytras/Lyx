<?php

namespace Lyx\Web;

class HtmlTag
{
  public static function tag($tag_name, $content, $attributes = [])
  {
    $tag_attrs = [];
    $attrs = '';

    if(!empty($attributes)) {
      foreach($attributes as $attr_name => $attr)
        $tag_attrs[] = "{$attr_name}=\"".htmlspecialchars($attr)."\"";
      
      if(count($tag_attrs) > 0)
        $attrs = ' '.implode(' ', $tag_attrs); 
    }

    return "<{$tag_name}{$attrs}>{$content}</{$tag_name}>";
  }
  
  public static function strong($content, $attributes = [])
  {
    return self::tag(__FUNCTION__, $content, $attributes);
  }
  
  public static function i($content, $attributes = [])
  {
    return self::tag(__FUNCTION__, $content, $attributes);
  }
}
