<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');
/**
 * TCLogger - manages logging of data with CodeIgniter (created 2008-12-16 eurico)
 *
 * @copyright	Copyright (c) TotalCenter
 * @version		1.01 2010-06-25 eurico
**/

class TCLogger {

	function log($log_file, $event_description, $log_vars = FALSE) {
		if ( ! $log_file)
			die('A prefix for the log filename must be specified!');
		$filename = BASEPATH . 'logs/' . date('Ymd') . '_' . $log_file . '.log';
		// parse log vars
		$title = '[ ' . date('H:i:s') . ' ] - ' . $event_description;
		$border = str_repeat('-', strlen($title));
		$str = chr(10) . $border . chr(10);
		$str .= $title . chr(10) . $border . chr(10);
		if ( ! empty($log_vars)) {
			$str .= chr(10);
			if (is_scalar($log_vars)) $log_vars = array($log_vars);
			foreach ($log_vars as $var_key => $var_value)
				$str .=  $this->print_vars($var_key, $var_value);
		}
		// write to file
		$f = fopen($filename, 'ab');
		if ( ! $f) die('Failed opening Log file ' . $filename);
		fwrite($f, $str);
		fclose($f);
	}
	
	function print_vars($var_key, $var_value, $indent = 0) {
		$indent_str = '';
		if ($indent > 0)
			$indent_str = str_repeat(chr(9),$indent);
		$output = $indent_str . '[' . $var_key . '] => ';
		$var_type = gettype($var_value);
		if ($var_type == 'array' || $var_type == 'object') {
			$output .= $var_type . ' (' . chr(10);
			++$indent;
			foreach ($var_value as $sub_key => $sub_value)
				$output .= $this->print_vars($sub_key, $sub_value, $indent);
			$output .= $indent_str . ')' . chr(10);
		} else
			$output .= var_export($var_value,true) . chr(10);
		return $output;
	}
	
}

// ------------------------------------------------------------------------
/* End of file TCLogger.php */