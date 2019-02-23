<?php

namespace Lyx\Web;

class Dom
{
  public $document;
  public $result;
  
  public function __construct(&$document_or_html = null)
  {
    if(is_string($document_or_html)) {
      $this->document = new \DOMDocument();
      $this->loadHTML($document_or_html);
    } elseif($document_or_html !== null)
      $this->document = $document_or_html;
    else 
      $this->document = new \DOMDocument();
  }
  
  public function loadHTML($html)
  {
    $error_level = error_reporting();
    error_reporting($error_level & ~(E_NOTICE | E_WARNING));
    $this->document->loadHTML($html);
    error_reporting($error_level);
  }
  
  public function htmlToElements($html)
  {
    if(empty($html))
        return [];

    $doc = new \DOMDocument();
    $doc->loadHTML($html);

    $nodes = $this->document->createElement('nodes');

    foreach ($doc->getElementsByTagName('body')->item(0)->childNodes as $node) {
      $node = $this->document->importNode($node, true);
      $nodes->appendChild($node);
    }
    
    return $nodes->childNodes;
  }
  
  public function importNode($node, $clone = false, $deep = true)
  {
    $element = $clone ? clone $node : $node;
    $imported = $this->document->importNode($element, $deep);
    return $imported;
  }
  
  public function append($what, $where)
  {
    if($where instanceof \DOMNodeList) {
      foreach($where as $node) {
        if($what instanceof \DOMNodeList) {
          foreach($what as $el)
            if(is_object($el))
              $node->appendChild(clone $el);
        } elseif(is_object($what))
          $node->appendChild(clone $what);
      }
    } else {
      if($what instanceof \DOMNodeList) {
        foreach($what as $el)
          $where->appendChild($el);
      } else {
        $where->appendChild($what);
      }
    }
  }
  
  public function prepend($what, $where)
  {
    if($where instanceof \DOMNodeList) {
      foreach($where as $node) {
        if($what instanceof \DOMNodeList) {
          foreach($what as $el)
              $node->insertBefore(clone $el, $node->firstChild);
        } else 
          $node->insertBefore(clone $what, $node->firstChild);
      }
    } else {
      if($what instanceof \DOMNodeList) {
        foreach($what as $el)
          $where->insertBefore($el, $node->firstChild);
      } else
        $where->insertBefore($what, $where->firstChild);
    }
  }
  
  public function insertBefore($what, $where)
  {
    if($where instanceof \DOMNodeList) {
      foreach($where as $node) {
        if($what instanceof \DOMNodeList) {
          foreach($what as $el)
            if(is_object($el))
                $node->parentNode->insertBefore(clone $el, $node);
        } elseif(is_object($what))
          $node->parentNode->insertBefore(clone $what, $node);
      }
    } else {
      if($what instanceof \DOMNodeList) {
        foreach($what as $el)
          $where->parentNode->insertBefore($el, $where);
      } else
        $where->parentNode->insertBefore($what, $where);
    }
  }
  
  public function insertAfter($what, $where)
  {
    if($where instanceof \DOMNodeList) {
      foreach($where as $node) {
        if($what instanceof \DOMNodeList) {
          foreach($what as $el) {
            if(is_object($el))
              $node->parentNode->insertBefore(clone $el, $node->nextSibling);
          }
        } elseif(is_object($what))
          $node->parentNode->insertBefore(clone $what, $node->nextSibling);
      }
    } else {
      if($what instanceof \DOMNodeList) {
        $counter = 0;
        foreach($what as $el) {
          $counter++;
          echo "$counter<br>";
          $where->parentNode->insertBefore($el, $where->nextSibling);
        }
      } else
        $where->parentNode->insertBefore($what, $where->nextSibling);
    }
  }
  
  public function remove($selector)
  {
    if(gettype($selector) == 'string')
      $selector = $this->find($selector);

    if($selector instanceof \DOMNodeList) {
      foreach($selector as $node)
          $node->parentNode->removeChild($node);
    } elseif($selector instanceof \DOMNode)
      $selector->parentNode->removeChild($selector);
  }
  
  public function exists($selector, \DOMNode $contextnode = null)
  {
    $this->find($selector, $contextnode);
    return $this->result->length > 0;
  }
  
  public function find($selector, \DOMNode $contextnode = null)
  {
    return $this->xpath(static::cssToXpath($selector), $contextnode);
  }
  
  public function q($selector, \DOMNode $contextnode = null)
  {
    return $this->find($selector, $contextnode);
  }

  public function getElementsByClassName($tagName, $className, &$parentNode = null)
  {
    if(is_null($parentNode))
      $parentNode = $this->document;
    else if(is_string($parentNode)) {
      $xpathResult = $this->xpath($parentNode);
      if($xpathResult->length > 0)
        $parentNode = $xpathResult->item(0);
    }
    
    $nodes = [];

    $childNodeList = $parentNode->getElementsByTagName($tagName);
    for($i = 0; $i < $childNodeList->length; $i++) {
      $temp = $childNodeList->item($i);
      if(stripos($temp->getAttribute('class'), $className) !== false)
        $nodes[]=$temp;
    }

    return $nodes;
  }
  
  public function xpath($xpath_expression, \DOMNode $contextnode = null)
  {
    if(is_null($contextnode))
      $contextnode = $this->document->documentElement;
    elseif($xpath_expression[0] != '.')
      $xpath_expression = ".{$xpath_expression}";
    
    $xpath = new \DOMXPath($this->document);
    $this->result =  $xpath->query($xpath_expression, $contextnode);
    return $this->result;
  }
  
  public function writeHtml()
  {
    $html = $this->document->saveHTML();
    $out = mb_convert_encoding($this->fixUrls($html), 'UTF-8', "HTML-ENTITIES");
    print($out);
    return $this;
  }
  
  public function injectJs($js, $xpathAppendTo = '//body', $type = 'text/javascript', $id = null)
  {
    $appendTo = $this->xpath($xpathAppendTo);
    if($appendTo->length > 0) {
      $script = $this->document->createElement('script');
      $script->nodeValue = $this->_escapeHtmlForScript($js);
      if(!empty($id))
        $script->setAttribute('id', $id);
      $script->setAttribute('type', $type);
      $appendTo->item(0)->appendChild($script);
    }
    return $this;
  }
  
  private function _escapeHtmlForScript($code)
  {
    return preg_replace("/(&&)/", "&&&", $code);
  }
  
  public function injectJsTemplate($tmpl_html, $id, $xpathAppendTo = '//body')
  {
    return $this->injectJs($tmpl_html, $xpathAppendTo, 'text/x-custom-template', $id);
  }
  
  public function injectJsFile($js_file, $xpathAppendTo = '//body')
  {
    $appendTo = $this->xpath($xpathAppendTo);
    if($appendTo->length > 0) {
      $script = $this->document->createElement('script');
      $script->setAttribute('type', 'text/javascript');
      $script->setAttribute('src', $js_file);
      $appendTo->item(0)->appendChild($script);
    }
    return $this;
  }
  
  public function injectJsFiles($js_files)
  {
    if(!is_array($js_files))
      $js_files = [$js_files];

    if(count($js_files) > 0)
      foreach($js_files as $js_file)
        $this->injectJsFile($js_file);

    return $this;
  }
  
  public function injectCss($css, $xpathAppendTo = '//head')
  {
    $appendTo = $this->xpath($xpathAppendTo);
    if($appendTo->length > 0) {
      $style = $this->document->createElement('style', $css);
      $style->setAttribute('type', 'text/css');
      $appendTo->item(0)->appendChild($style);
    }
    return $this;
  }
  
  public function injectCssFile($css_file, $xpathAppendTo = '//head')
  {
    $appendTo = $this->xpath($xpathAppendTo);
    if($appendTo->length > 0) {
      $link = $this->document->createElement('link');
      $link->setAttribute('rel', 'stylesheet');
      $link->setAttribute('media', 'all');
      $link->setAttribute('href', $css_file);
      $appendTo->item(0)->appendChild($link);
    }
    return $this;
  }
  
  public function injectCssFiles($css_files)
  {
    if(!is_array($css_files))
      $css_files = [$css_files];

    if(count($css_files) > 0)
      foreach($css_files as $css_file)
          $this->injectCssFile($css_file);

    return $this;
  }
  
  /**
   * Transform CSS expression to XPath
   *
   * @param  string $path
   * @return string
   */
  public static function cssToXpath($path)
  {
    $path = (string) $path;

    if(strstr($path, ',')) {
      $paths       = explode(',', $path);
      $expressions = [];
      foreach($paths as $path) {
        $xpath = static::cssToXpath(trim($path));
        if(is_string($xpath)) {
          $expressions[] = $xpath;
        } elseif(is_array($xpath)) {
          $expressions = array_merge($expressions, $xpath);
        }
      }
      return implode('|', $expressions);
    }

    $paths    = ['//'];
    $path     = preg_replace('|\s+>\s+|', '>', $path);
    $segments = preg_split('/\s+/', $path);

    foreach($segments as $key => $segment) {
      $pathSegment = static::_tokenize($segment);
      if($key == 0) {
        if(strpos($pathSegment, '[contains(') === 0) {
          $paths[0] .= '*' . ltrim($pathSegment, '*');
        } else {
          $paths[0] .= $pathSegment;
        }
        continue;
      }

      if(strpos($pathSegment, '[contains(') === 0) {
        foreach($paths as $pathKey => $xpath) {
          $paths[$pathKey] .= '//*' . ltrim($pathSegment, '*');
          $paths[]      = $xpath . $pathSegment;
        }
      } else {
        foreach($paths as $pathKey => $xpath) {
          $paths[$pathKey] .= '//' . $pathSegment;
        }
      }
    }

    if(count($paths) == 1)
      return $paths[0];

    return implode('|', $paths);
  }
  /**
   * Tokenize CSS expressions to XPath
   *
   * @param  string $expression
   * @return string
   */
  protected static function _tokenize($expression)
  {
    // Child selectors
    $expression = str_replace('>', '/', $expression);
    // IDs
    $expression = preg_replace('|#([a-z][a-z0-9_-]*)|i', '[@id=\'$1\']', $expression);
    $expression = preg_replace('|(?<![a-z0-9_-])(\[@id=)|i', '*$1', $expression);
    // arbitrary attribute strict equality
    $expression = preg_replace_callback(
      '|\[@?([a-z0-9_-]+)=[\'"]([^\'"]+)[\'"]\]|i',
      function($matches) {
        return '[@' . strtolower($matches[1]) . "='" . $matches[2] . "']";
      },
      $expression
    );
    // arbitrary attribute contains full word
    $expression = preg_replace_callback(
      '|\[([a-z0-9_-]+)~=[\'"]([^\'"]+)[\'"]\]|i',
      function($matches) {
        return "[contains(concat(' ', normalize-space(@" . strtolower($matches[1]) . "), ' '), ' "
          . $matches[2] . " ')]";
      },
      $expression
    );
    // arbitrary attribute contains specified content
    $expression = preg_replace_callback(
      '|\[([a-z0-9_-]+)\*=[\'"]([^\'"]+)[\'"]\]|i',
      function($matches) {
        return "[contains(@" . strtolower($matches[1]) . ", '"
          . $matches[2] . "')]";
      },
      $expression
    );

    // Classes
    if(strpos($expression, "[@") === false) {
      $expression = preg_replace(
        '|\.([a-z][a-z0-9_-]*)|i',
        "[contains(concat(' ', normalize-space(@class), ' '), ' \$1 ')]",
        $expression
      );
    }
    /** ZF-9764 -- remove double asterisk */
    $expression = str_replace('**', '*', $expression);
    return $expression;
  }
}
