<?php
class Stats extends Controller {


	function Stats()
	{
		parent::Controller();
		$this->load->library('log');
		$this->load->helper('array');
		$this->load->model('hosts_model');
		$this->load->model('logins_model');
		$this->load->model('settings_model');
		$this->load->model('rules_model');
		$this->load->model('crawler_model');
	}
	function crawl($emails_id = FALSE) {
		$this->crawler_model->crawl_emails($emails_id);
	}
	function populate_offset_table() {
		$this->rules_model->populate_offset_emails();
	}
	function populate_emails_mem() {
		$this->rules_model->populate_emails_mem();
	}
	function populate_stats($start = FALSE,$end = FALSE) {
		$this->db->select('emails_id');
		$this->db->where('status_id',4);
		$this->db->where('alive is not null');
		if( !$start && !$end) {
			$this->db->where('YEARWEEK( created ) = YEARWEEK(CURRENT_TIMESTAMP )');
			$this->db->where('DAY( created ) = DAY(CURRENT_TIMESTAMP )');
		}
		if ($start) $this->db->where('created >=',$start);
		if ($end) $this->db->where('created <=',$end);
		$query = $this->db->get('emails');
		$return = array(
			'timestamp' => mktime(),
			'emails' => false
		);
		if($query && $query->num_rows > 0) {
			$result = $query->result_array();
			$emails = array();
			foreach($result as $item) {
				$emails[] = $item['emails_id'];
			}
			$return['emails'] = $emails;
		}
		die(json_encode($return));
	}
	function fetch_orphan_emails() {
		$this->crawler_model->fetch_orphan_emails();
	}
	function set_email_trial($emails_id = FALSE) {
		$trials = $this->input->post();
		foreach($trials as $emails_id) {
			$emails_item = $this->crawler_model->get_email_item($emails_id);
			$this->crawler_model->parse_email_item($emails_item,array('trial' => 1));
		}
	}
	function set_email_membership($emails_id = FALSE) {
		$memberships = $this->input->post();
		foreach($memberships as $emails_id) {
			$emails_item = $this->crawler_model->get_email_item($emails_id);
			$this->crawler_model->parse_email_item($emails_item,array('membership' => 1));
		}
	}
}