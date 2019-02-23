<?php

namespace Lyx\Web;

class Html
{
  public static function prependBaseTag($html, $baseUrl)
  {
    return preg_replace('/\<head\>/', "<head><base href=\"{$baseUrl}\">", $html);
  }

  public static function prependHead($html, $prepend)
  {
    return preg_replace('/\<head\>/', '<head>'.$prepend, $html);
  }

  public static function appendHead($html, $append)
  {
    return preg_replace('/\<\/head\>/', $append.'</head>', $html);
  }

  public static function appendCssFile($html, $css_file)
  {
    return preg_replace('/\<\/head\>/', '<link href="'.$css_file.'" rel="stylesheet" type="text/css"></head>', $html);
  }

  public static function appendJsFile($html, $js_file)
  {
    return preg_replace('/\<\/head\>/', '<script type="text/javascript" src="'.$js_file.'"></script></head>', $html);
  }
}
