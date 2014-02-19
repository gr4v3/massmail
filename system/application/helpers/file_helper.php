<?php
/* 
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */

if ( ! function_exists('array_clean'))
{
	function array_clean($array = array(), $remove_null_number = true)
	{
		$result = array();
		$null_exceptions = array();

		foreach ($array as $key => $value)
		{
			$value = trim($value);
			if($remove_null_number) $null_exceptions[] = '0';
			if(!in_array($value, $null_exceptions) && $value != "") $result[$key] = $value;
		}

		return $result;
	}
}

