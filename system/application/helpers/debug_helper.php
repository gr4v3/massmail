<?php  if ( ! defined('BASEPATH')) exit('No direct script access allowed');
/* 
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */

if ( ! function_exists('Debug'))
{
	function Debug($value,$die = false)
	{
		if(!isset($value)) return false;
		echo "<pre>";print_r($value);echo "</pre>";
		if($die) die();

	}
}


if ( ! function_exists('timeparser'))
{
	function timeparser($value,$die = false)
	{
		

	}
}
