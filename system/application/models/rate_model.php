<?php
/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */
class Rate_model extends CI_Model {
	var $table = 'account_rate';
	function __construct(){
		parent::__construct();
		$this->load->helper('array');
    }
    function send_success($login_id = NULL,$amount = 1) {
		if (empty($login_id)) return FALSE;
        $query = 'INSERT INTO account_rate (login_id, send_success) VALUES (?, ?)';
        $query .= ' ON DUPLICATE KEY UPDATE send_success = send_success + ?';
        return $this->db->query($query, array($login_id, $amount, $amount));
    }
    function send_fail($login_id = NULL,$amount = 1) {
        if (empty($login_id)) return FALSE;
		$query = 'INSERT INTO account_rate (login_id, send_fail) VALUES (?, ?)';
        $query .= ' ON DUPLICATE KEY UPDATE send_fail = send_fail + ?';
        return $this->db->query($query, array($login_id, $amount, $amount));
    }
    function bounce_success($login_id = NULL,$amount = 1) {
        if (empty($login_id)) return FALSE;
		$query = 'INSERT INTO account_rate (login_id, bounce_success) VALUES (?, ?)';
        $query .= ' ON DUPLICATE KEY UPDATE bounce_success = bounce_success + ?';
        return $this->db->query($query, array($login_id, $amount, $amount));
    }
    function bounce_fail($login_id = NULL,$amount = 1) {
        if (empty($login_id)) return FALSE;
		$query = 'INSERT INTO account_rate (login_id, bounce_fail) VALUES (?, ?)';
        $query .= ' ON DUPLICATE KEY UPDATE bounce_fail = bounce_fail + ?';
        return $this->db->query($query, array($login_id, $amount, $amount));
    }
    function max_send($login_id = NULL,$amount = 1) {
        if (empty($login_id)) return FALSE;
		$query = 'INSERT INTO account_rate (login_id, diff) VALUES (?, ?)';
        $query .= " ON DUPLICATE KEY UPDATE diff = ? - max_send";
        $this->db->query($query, array($login_id, $amount, $amount));
        $query = 'INSERT INTO account_rate (login_id, max_send) VALUES (?, ?)';
        $query .= ' ON DUPLICATE KEY UPDATE max_send = ?';
        return $this->db->query($query, array($login_id, $amount, $amount));
    }
    function errors($login_id = NULL,$amount = 1) {
		if (empty($login_id)) return FALSE;
        $query = 'INSERT INTO account_rate (login_id, max_send) VALUES (?, ?)';
        $query .= ' ON DUPLICATE KEY UPDATE max_send = ?';
        return $this->db->query($query, array($login_id, $amount, $amount));
    }
}