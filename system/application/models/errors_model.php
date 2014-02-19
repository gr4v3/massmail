<?php
/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */
class Errors_model extends active {

	var $table = 'errors';
	var $columns = array(
		'errors_id' =>  NULL,
		'table' => NULL,
		'row' => NULL,
		'value' => NULL,
		'created' =>  NULL,
		'modified' => '0000-00-00 00:00:00',
		'deleted' => '0000-00-00 00:00:00'
	);
	function __construct(){
		parent::__construct();
		$this->load->helper('array');
	}
	function set($table,$row_id,$error = false) {
		$this->db->where('`table`', $table);
		$this->db->where('row',$row_id);
		$query = $this->db->get('errors');
		if($query && $query->num_rows > 0 ) {
			$result = $query->row_array();
			$this->db->where('row',$row_id);
			$this->db->set('count', $result['count'] + 1);
			$this->db->update('errors');
		} else {
			$this->db->set('`table`', $table);
			$this->db->set('row', $row_id, false);
			$this->db->set('value', strip_tags($error));
			$now =  date('Y-m-d H:i:s',mktime());
			$this->db->set('created', $now);
			$this->db->set('count', 0);
			$this->db->insert('errors');

		}
	}
}