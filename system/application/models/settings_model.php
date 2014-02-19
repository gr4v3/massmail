<?php
/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */

class Settings_model extends CI_Model {
	var $table = 'settings';
	function __construct() {
		parent::__construct();
		$this->load->helper('array');
    }
	// insert or updates rows depending if the parameter comes with the table_key or not
	function set($params = NULL) {
		if (empty($params)) return FALSE;
		$host_id = $params->host_id;
		$type    = $params->type;
		if ( ! empty($host_id)) {
			unset($params->host_id);
			unset($params->type);
			// it will be an update
			$this->db->where('host_id', $host_id);
			$this->db->where('type', $type);
			$query = $this->db->get('settings');
			if ($query && $query->num_rows > 0 ) {
				foreach(get_object_vars($params) as $field => $value) {
					$this->db->where('field', $field);
					$this->db->where('host_id', $host_id);
					$this->db->where('type', $type);
					$this->db->set('value', $value);
					$this->db->update('settings');
					$this->log->write_log('OFFICE','settings_model:'.$this->db->last_query());
				}
			} else {
				// it will be an insert
				foreach(get_object_vars($params) as $field => $value) {
					$this->db->set('host_id', $host_id);
					$this->db->set('type', $type);
					$this->db->set('field', $field);
					$this->db->set('value', $value);
					$this->db->insert('settings');
					$this->log->write_log('OFFICE','settings_model:'.$this->db->last_query());
				}
			}
		}
	}
	function get($host_id = NULL, $type = NULL) {
		if (empty($host_id)) return FALSE;
		$this->db->where('status_id', 1);
		$this->db->where('host_id', $host_id);
		$query = $this->db->get('settings');
		if ($query && $query->num_rows > 0) $result = $query->result_array();
		else return FALSE;
		$result = array_arrange(array_absolute($result,true),'type');
		return array_arrange($result,'field',true);
	}
}