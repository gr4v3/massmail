<?php
/* 
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */
class Status_model extends active {

	var $table = 'status';
	var $columns = array(
		'status_id' => null ,
		'name' => '' 
	);

	function Status_model()
    {
        parent::Model();
		$this->load->database();
		$this->load->helper('array');
    }
	function getAll()
	{
		$filter = array('status' => array('status_id' => 'value','name' => 'text'));
		//get($where = false,$filter = false,$limit = false,$order = false,$join = false)
		return parent::get(false,$filter,false,array('status_id','asc'));
	}
}