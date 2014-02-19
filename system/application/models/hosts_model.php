<?php
/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */
class Hosts_model extends CI_model {

	var $table = 'host';
	var $columns = array(
		'host_id' => '' ,
		'hostname' => 'localhost' ,
		'status_id' => 1 ,
		'created' => null ,
		'modified' => '0000-00-00 00:00:00' ,
		'deleted' => '0000-00-00 00:00:00'
	);
	function __construct(){
		parent::__construct();
		$this->load->helper('array');
    }
	// inserts or updates a host
	function set($params = NULL) {
		if (empty($params)) return FALSE;
		if ( ! empty($params->host_id)) {
			$host_id = $params->host_id;
			unset($params->host_id);
			$params->modified = date('Y-m-d H:i:s',mktime());
			$this->db->where('host_id', $host_id);
			$this->db->update('host',$params);
			return $host_id;
		} else {
			$params->created = date('Y-m-d H:i:s',mktime());
			$result = $this->db->insert('host',$params);
			if ($result) return $this->db->insert_id();
		}
		return FALSE;
	}
	// get one or a list of hosts The parameter of this method is simply the query filters for active records
	function get($host_id = NULL) {
		if (empty($host_id)) return FALSE;
		$this->db->where('host_id', $host_id);
		$query = $this->db->get('host');
		if ($query && $query->num_rows > 0) return $query->row(); else return FALSE;
	}
	// get the active hosts. The active hosts are the ones with status_id = 1
	function active_hosts() {
		$this->db->select('host_id');
		$this->db->where('status_id',1);
		$query = $this->db->get('host');
		if ($query && $query->num_rows > 0) {
			$active_hosts = array();
			foreach ($query->result() as $row) {$active_hosts[] = $row->host_id;}
			return $active_hosts;
		} else return FALSE;
	}
	// get all the available and busy hosts. It is the ones with status 1 and 2
	function all_hosts() {
		$this->db->where_in('status_id', array(1,8));
		$query = $this->db->get('host');
		if ($query && $query->num_rows > 0 ) {
			return $query->result();
		} else return FALSE;
	}
	// add new mailprovider/smtp host
	function add($hostname = NULL) {
		if (empty($hostname)) return FALSE;
		$this->db->where('hostname', $hostname);
		$query = $this->db->get('host');
		if ($query && $query->num_rows > 0) {
			$row = $query->row();
			return $row->host_id;
		} else {
			$this->db->set('hostname', $hostname);
			$result = $this->db->insert('host');
			if ($result) return $this->db->insert_id();
		}
		return FALSE;
	}
	// lock the host
	function lock($host_id = NULL) {
		if (empty($host_id)) return FALSE;
		$this->db->set('status_id', 2);
		$this->db->where('host_id', $host_id);
		return $this->db->update('host');
	}
	// unlock the host
	function unlock($host_id = NULL) {
		if (empty($host_id)) return FALSE;
		$this->db->set('status_id', 1);
		$this->db->where('host_id', $host_id);
		return $this->db->update('host');
	}
}