<?php
class active extends CI_Model {
	// set to state 0 any register in tables logins,emails_queue,emails_marked,emails_blacklist,emails_sent,queue
	function reset($id = NULL){
		if (empty($id)) return FALSE;
		$this->db->set('status_id', 0);
		return $this->db->update($this->table);
	}
	// gets a value for a stored var
	function get_stored_var($var_key, $select_extra = FALSE) {
		$this->db->select("var_value as $var_key");
		if ($select_extra) $this->db->select($select_extra);
		$this->db->where('var_key', $var_key);
		$result = $this->db->get('stored_vars');
		if ($result && $result->num_rows() == 1)
			return $select_extra ? $result->row() : $result->row()->$var_key;
		return FALSE;
	}
	// sets a value for a stored var (record is updated if exists, otherwise is inserted)
	function set_stored_var($var_key, $var_value) {
		// inserts the record if it does not exists
		$query = 'INSERT INTO stored_vars (var_key, var_value) VALUES (?, ?)';
		// updates if already exists - note that we force last_update otherwise mysql does not update when value is the same as before
		$query .= ' ON DUPLICATE KEY UPDATE var_value = ?, last_update = NOW()';
		return $this->db->query($query, array($var_key, $var_value, $var_value));
	}
	// get an inactive ip to be checked
	function uncheckedip() {
		$this->db->where('active', 0);
		$this->db->or_where('active', NULL);
		$this->db->where('owner',$_SERVER['SERVER_ADDR']);
		$this->db->limit(1);
		$query = $this->db->get('available_ips');
		if ($query && $query->num_rows > 0 ) return $query->row();
		else return FALSE;
	}
	// sets an ip as active
	function checkip($ip = NULL, $domain = NULL) {
		if (empty($ip)) return FALSE;
		$this->db->where('ip', $ip);
		$this->db->set('active', 1);
		$this->db->set('status_id', 1);
		if ( ! empty($domain)) $this->db->set('domain', $domain);
		return $this->db->update('available_ips');
	}
}