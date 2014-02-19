<?php
/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 * DROP TABLE IF EXISTS `mailserver`.`rules`;
 */
class Rules_model extends CI_Model {
	var $table = 'rules';
	function __construct(){
		parent::__construct();
		$this->load->helper('array');
    }
	function set($params = NULL) {
		if (empty($params)) return FALSE;
		if (! empty($params->host_id)) {
			$host_id = $params->host_id;
			//check first if there is rules already inserted
			$this->db->where('host_id', $host_id);
			$query = $this->db->get('rules');
			if ($query && $query->num_rows > 0 ) {
				unset($params->host_id);
				$this->db->where('host_id', $host_id);
				$this->db->update('rules',$params);
				$this->log->write_log('OFFICE','rules_model:'.$this->db->last_query());
			} else {
				$params->created = date('Y-m-d H:i:s',mktime());
				$result =  $this->db->insert('rules',$params);
				if ($result) return $this->db->insert_id();
				$this->log->write_log('OFFICE','rules_model:'.$this->db->last_query());
			}
		} else return FALSE;
	}
	function get($host_id = NULL) {
		if (empty($host_id)) return FALSE;
		$this->db->where('host_id', $host_id);
		$query = $this->db->get('rules');
		if($query && $query->num_rows > 0) return $query->row();
		else return FALSE;
	}
}