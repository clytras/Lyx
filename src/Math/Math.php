<?php

namespace Lyx\Math;

class Math
{
	public static function getProgress($min, $max, $value, $retObject = true)
	{
		$pd = array();
		$pd['min'] = $min;
		$pd['max'] = $max;
		$pd['value'] = $value;
		$pd['total'] = $pd['max'] - $pd['min'];
		$pd['factor'] = $pd['value'] / $pd['total'];
		$pd['percent'] = $pd['factor'] * 100;

		if($retObject)
			return (object)$pd;
		else
			return $pd;
	}
}
