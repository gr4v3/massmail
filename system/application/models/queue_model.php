<?php
/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */
class Queue_model extends CI_Model {
	var $table = 'queue';
	function __construct() {
		parent::__construct();
		$this->load->helper('array');
	}
	// set the time-based flag to the login to log his activity
	function set($params = NULL, $dont_update = FALSE) {
		if ( empty($params)) return FALSE;
		if ( ! isset($params->login_id) && ! isset($params->type) ) return FALSE;
		$this->db->where('login_id', $params->login_id);
		$this->db->where('type', $params->type);
		$query = $this->db->get('queue');
		if ($query && $query->num_rows() > 0) {
			$row = $query->row();
			if ($dont_update) return $row->queue_id;
			$this->db->where('type', $params->type);
			$this->db->where('login_id', $params->login_id);
			if (isset($params->access)) $this->db->set('access',$params->access);
			else $this->db->set('access', 'CURRENT_TIMESTAMP', FALSE);
			return $this->db->update('queue');
		} else {
			$insert = new stdClass;
			$insert->login_id = $params->login_id;
			$insert->type = $params->type;
			$insert->ip = $params->ip;
			$this->db->set('created','current_timestamp',FALSE);
			if (isset($params->access)) $this->db->set('access',$params->access);
			else $this->db->set('access', 'CURRENT_TIMESTAMP', FALSE);
			$this->db->insert('queue',$insert);
			return $this->db->insert_id();
		}
	}
	// lock a queue item by his table key
	function lock($queue_id = NULL,$custom = FALSE) {
		if (empty($queue_id)) return FALSE;
		if ($custom) $status_id = $custom; else $status_id = 2;
		$this->db->set('status_id', $status_id);
		$this->db->where('queue_id', $queue_id);
		return $this->db->update('queue');
	}
	// lock a queue item by the login_id index
	function lock_by_login_id($login_id = NULL, $type = NULL, $custom = FALSE) {
		if (empty($login_id) || empty($type)) return FALSE;
		if ($custom) $status_id = $custom; else $status_id = 2;
		$this->db->set('status_id', $status_id);
		$this->db->where('login_id', $login_id);
		$this->db->where('type', $type);
		return $this->db->update('queue');
	}
	// unlock a queue item by his table key
	function unlock($queue_id = NULL) {
		if (empty($queue_id)) return FALSE;
		$this->db->set('status_id', 1);
		$this->db->where('queue_id', $queue_id);
		return $this->db->update('queue');
	}
	// unlock a queue item by the login_id index
	function unlock_by_login_id($login_id = NULL, $type = NULL) {
		if (empty($login_id) || empty($type)) return FALSE;
		$this->db->set('status_id', 1);
		$this->db->where('login_id', $login_id);
		$this->db->where('type', $type);
		return $this->db->update('queue');
	}
}