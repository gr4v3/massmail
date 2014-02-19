<?php
/**
 * Description of MY_Email
 *
 * @author eurico@totalcenter
 * @@version 1.0 2009-11-05
 */
class MY_Email extends CI_Email {


	// returns the message id set in email header
	function get_tracking_key() {
		return isset($this->_headers['Message-ID']) ? $this->_headers['Message-ID'] : FALSE;
	}

	/*
	function send($params = false)
	{
		$result = parent::send($params);
		if($result) return $this->get_tracking_key();
		else return FALSE;
	}
	*/
	function connect()
	{
		
		return parent::_smtp_connect();
	}

	function return_path($name = "",$value)
	{
		$this->_set_header('Return-Path', $name.' <'.$value.'>');
	}
}
?>
