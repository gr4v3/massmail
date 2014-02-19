<?php
/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */

class MY_Parser extends CI_Parser {
	function parse_generic($template, $data, $return = FALSE) {
		// this parses from a string instead of a view from codeignitor
		$CI =& get_instance();


		if ($template == '')
		{
			return FALSE;
		}

		foreach ($data as $key => $val)
		{

			if (is_array($val))
			{
				$template = $this->_parse_pair($key, $val, $template);
			}
			else
			{
				$val = (string)$val;
				if(strlen($val)>0) $template = $this->_parse_single($key, $val , $template);
			}
		}

		if ($return == FALSE)
		{
			$CI->output->append_output($template);
		}

		return $template;
	}
}