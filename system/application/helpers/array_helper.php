<?php  if ( ! defined('BASEPATH')) exit('No direct script access allowed');
/**
 
/**
 * CodeIgniter ARRAY Helpers
 *
 * @package		CodeIgniter
 * @subpackage	Helpers
 * @category	Helpers
 * @author		TotalControl Dev Team

 */

// ------------------------------------------------------------------------

if ( ! function_exists('element'))
{
	function element($item, $array, $default = FALSE)
	{
		if ( ! isset($array[$item]) OR $array[$item] == "")
		{
			return $default;
		}

		return $array[$item];
	}
}



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

if ( ! function_exists('array_singlechild'))
{
	function array_singlechild($data = array())
	{

		foreach($data as $index => &$value)
		{
			$num = count($value);
			if($num == 1) $value = $value[0];
		}
		return $data;
	}
}


if ( ! function_exists('array_copy'))
{
	function array_copy($data = array())
	{
		return array_merge(array(),$data);
	}
}


if ( ! function_exists('array_populate_index'))
{
	function array_populate_index($data = array(),$insert = array())
	{
		foreach($data as &$value)
		{
			$value = array_merge($value,$insert);
		}
		return $data;
	}
}


if ( ! function_exists('array_valid_index'))
{
	function array_valid_index($data = array(),$valid = array())
	{
		foreach($data as $index => $value)
		{
			if(!array_key_exists($index,$valid)) unset($data[$index]);
		}
		return $data;
	}
}




if ( ! function_exists('array_parse_index'))
{
	function array_parse_index($data = array(),$filter = array())
	{
		$result = array();
		
		foreach($filter as $index => $value)
		{
			$result[$index] = isset($data[$index])?$data[$index]:$value;
		}

		return $result;
	}
}


if ( ! function_exists('array_remove_index'))
{
	function array_remove_index($array = array(),$indexes = array())
	{
		$result = array();

		foreach($indexes as $value)
		{
			if(!isset($array[$value])) $result[$value] = $array[$value];
		}

		return $result;
	}
}

if ( ! function_exists('array_index'))
{
	function array_index($arr = FALSE,$index = FALSE)
	{
		if($arr === FALSE or $index === FALSE) return $arr;
		$result = array();

		foreach($arr as $value)
		{
			$result[] = $value[$index];
		}

		return $result;
	}
}

if ( ! function_exists('array_absolute'))
{
	function array_absolute(&$item,$level = false)
	{
		if($level)
		{
			foreach($item as &$level)
			{
				$level = array_absolute($level);
			}
		}
		if(is_object($item)) $item = get_object_vars($item);
		return $item;
	}
}

if ( ! function_exists('array_arrange'))
{
	function array_arrange($arr = FALSE,$index = FALSE,$level = false)
	{
		if($arr === FALSE or $index === FALSE) return $arr;
		$result = array();
		
		foreach($arr as $i=> &$value)
		{
			if($level) {
				$result[$i] = array_singlechild(array_arrange($value,$index));
			}
			else {
				$j = $value[$index];
				unset($value[$index]);
				$result[$j][] = $value;
				
			}
		}

		return $result;
	}
}

if ( ! function_exists('array_flatten'))
{
	function array_flatten($array, $flat = false, $prefix = false)
	{
		if (!is_array($array) || empty($array)) return '';
		if (empty($flat)) $flat = array();

		foreach ($array as $key => $val) {
		  if (is_array($val)) $flat = array_flatten($val, $flat);
		  else $flat[] = $prefix?$prefix.$val:$val;
		}

		return $flat;
	}
}

if ( ! function_exists('multi_implode'))
{
	function multi_implode($glue, $pieces)
	{
		$string='';

		if(is_array($pieces))
		{
			reset($pieces);
			while(list($key,$value)=each($pieces))
			{
				$string.=$glue.multi_implode($glue, $value);
			}
		}
		else
		{
			return $pieces;
		}

		return trim($string, $glue);
	}
}

if ( ! function_exists('array_walk_prefix'))
{
	function array_walk_prefix(&$value, $index, $prefix)
	{
		$value = $prefix . $value;
	}
}

if ( ! function_exists('array_normalize'))
{
	function array_normalize(&$value = false)
	{
		if(!isset($value[0])) $value = array($value);
	}
}




/**
 * Site URL
 *
 * Create a local URL based on your basepath. Segments can be passed via the
 * first parameter either as a string or an array.
 *
 * @access	public
 * @param	string
 * @return	string
 */
/* End of file url_helper.php */
/* Location: ./system/helpers/array_helper.php */
