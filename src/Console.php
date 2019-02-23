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
