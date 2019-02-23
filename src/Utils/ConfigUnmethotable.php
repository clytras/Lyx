<?php

namespace Lyx\Utils;

class ConfigUnmethotable extends Config
{
  public function __construct($params = null)
  {
    $this->_use_methods_for_params = false;
    return call_user_func_array('parent::__construct', func_get_args());
  }
}
