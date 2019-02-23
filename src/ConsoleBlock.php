
<?php

namespace Lyx;

class ConsoleBlock
{
  private $blocks = [];
  private	$name = NULL;
  private	$value = NULL;
  private	$parent_block = NULL;
  private	$display_type = FALSE;

  public function __construct($name = NULL, $value = NULL, $display_type = FALSE, $parent_block = NULL)
  {
    $this->name = $name;
    $this->value = $value;
    $this->parent_block = $parent_block;
    $this->display_type = $display_type;
  }
    
  public function getParent()
  {
    return $this->parent_block;
  }

  public function setParent($parent_block)
  {
    $this->parent_block = $parent_block;
  }

  public function setName($name)
  {
    $this->name = $name;
  }

  public function getName()
  {
    return $this->name;
  }

  public function setValue($value)
  {
    $this->value = $value;
  }

  public function getValue()
  {
    return $this->value;
  }

  public function valueToString()
  {
    if(is_bool($this->value))
      return $this->value ? 'TRUE' : 'FALSE';
    elseif(is_array($this->value))
      return "[array][".count($this->value)."]";
    elseif(is_object($this->value))
      return "[object][".get_class($this->valye)."]";
    elseif(is_null($this->value))
      return "NULL";
    else
      return $this->value;
  }

  public function addBlock()
  {
    $ret = NULL;
    $aa = func_get_args();
    $ac = func_num_args();
    
    if($ac == 1) {
      if(is_object($aa[0]) && get_class($aa[0]) == 'ConsoleBlock') {
        $this->blocks[] = $aa[0];
        $ret =& $aa[0];
      }
    }
  }

  public function flushHeader($insize = 0)
  {
    $line = str_repeat(wdebug_tab(), $insize).$this->name;
    if($this->value !== NULL) {
      if($this->display_type)
        $line .= sprintf(" (%s)", gettype($this->value));

      $line .= ": ".$this->valueToString();
    }

    print($line."\n");
  }

  public function hasChildren()
  {
    return count($this->blocks) > 0;
  }
}
