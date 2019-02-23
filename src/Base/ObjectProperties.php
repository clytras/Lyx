<?php

namespace Lyx\Base;

class ObjectProperties
{
	public function __get($name)
	{
		$getter = 'get' . $name;
		if(method_exists($this, $getter))
			return $this->$getter();
		else if(isset($this->_m[$name]))
			return $this->_m[$name];
	}

	public function __set($name, $value)
	{
		$setter = 'set' . $name;
		if(method_exists($this, $setter))
			return $this->$setter($value);
	}

	public function __isset($name)
	{
		$getter='get' . $name;
		return method_exists($this, $getter);
	}

	public function hasProperty($name)
	{
		return method_exists($this, 'get' . $name) || method_exists($this, 'set' . $name);
	}

	public function canGetProperty($name)
	{
		return method_exists($this, 'get' . $name);
	}

	public function canSetProperty($name)
	{
		return method_exists($this, 'set' . $name);
	}
}
