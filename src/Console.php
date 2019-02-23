<?php

namespace Lyx;

class Console
{
  private $blocks;
  private $current_block;

  public function __construct()
  {
    $this->clear();
  }

  public function clear()
  {
    $this->blocks = array();
    $this->current_block = NULL;
  }

  public function beginBlock()
  {
    $aa = func_get_args();
    $ac = func_num_args();

    $name = NULL;
    $value = NULL;
    $display_type = FALSE;

    if($ac >= 1) $name = $aa[0];
    if($ac >= 2) $value = $aa[1];
    if($ac >= 3) $display_type = $aa[2];

    $new_block = new ConsoleBlock($name, $value, $display_type, $this->current_block);
    
    if($this->current_block === NULL)
      $this->blocks[] = $new_block;
    else
      $this->current_block->addBlock($new_block);
      
    $this->current_block =& $new_block;
  }

  public function endBlock()
  {
    $this->_endBlock();
  }

  public function writeLine($line)
  {
    $this->beginBlock($line);
    $this->_endBlock();
  }

  public function writeValue($name, $value, $display_type = FALSE)
  {
    $this->beginBlock($name, $value, $display_type);
    $this->_endBlock();
  }

  public function flush()
  {
    $this->_endAllBlocks();
    lyx_debug_block_begin();
    $this->_flushBlocks($this->blocks);
    lyx_debug_block_end();
  }

  private function _endAllBlocks()
  {
    while($this->current_block !== NULL)
      $this->current_block = $this->current_block->getParent();
  }

  private function _endBlock()
  {
    if($this->current_block !== NULL)
      $this->current_block = $this->current_block->getParent();
  }

  private function _flushBlocks($blocks, $insize = 0)
  {
    $tabs = str_repeat(wdebug_tab(), $insize);

    foreach($blocks as $block) {
      $block->flushHeader($insize);
      if($block->hasChildren()) {
        print("{$tabs}" . WIZ_DEBUG_BLOCK_BEGIN . "\n");
        $this->_flushBlocks($block->blocks, $insize + 1);
        print("{$tabs}" . WIZ_DEBUG_BLOCK_END . "\n");
      }
    }
  }
}

// ConsoleBlock class

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
