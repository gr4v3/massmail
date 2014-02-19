<?php
class Url extends CI_controller {
    function __construct() {
		parent::__construct();
		$this->load->library('log');
		$this->load->helper('array');
		$this->load->model('crawler_model');
		$this->load->model('emails_model');
	}
	function index($url_base64 = null)	{
		if (empty($url_base64)) return FALSE;
		$request   = $_SERVER["REQUEST_URI"];
		$referer = isset($_SERVER["HTTP_REFERER"])?$_SERVER["HTTP_REFERER"]:false;
		$client_ip = $this->input->ip_address();
		$client_browser = $this->input->user_agent();
		$real_url = filter_var(safe_base64_decode($url_base64), FILTER_VALIDATE_URL);
		if ($real_url && strpos($real_url, 'thumbs2.vador.com') === FALSE) {
			$emails_data_id = $this->find_emailsid_mailcatch($real_url);
			if ($emails_data_id) {
				$email_item = $this->crawler_model->get_email_item($emails_data_id);
				$first_time = $this->emails_model->set_email_open($emails_data_id);
				if (stripos($real_url,'tracker') && $first_time) {
					if ($first_time) $this->crawler_model->parse_email_item($email_item,array('open' => 1));
				} elseif (stripos($real_url,'mailcatch')) {
					$vador_link = $this->emails_model->set_email_click($emails_data_id);
					if ($vador_link) $this->crawler_model->parse_email_item($email_item,array('click' => 1));
				}
			}
			$this->log->write_log('CLIENTS',"userip:$client_ip referer:$referer client:$client_browser request:$request response:$real_url");
		}
		if ($real_url) redirect($real_url);
	}
	function find_emailsid_mailcatch($real_url = NULL) {
		if ($real_url) {
			$emails_id = FALSE;
			$real_url_pieces = explode('/', $real_url);
			if ($emailsid_index = array_search('emailsid', $real_url_pieces)) {
				$emailsvalue_index = $emailsid_index + 1;
				$emails_id = $real_url_pieces[$emailsvalue_index];
			} else if ($emailsid_index = array_search('unsubscribe', $real_url_pieces)) {
				$emailsvalue_index = $emailsid_index + 1;
				$emails_id = $real_url_pieces[$emailsvalue_index];
			} else if ( strpos($real_url, 'tracker.jpg')) {
				$real_url_pieces = explode('=', $real_url);
				if ( count($real_url_pieces) != 2) {
					$this->log->write_log('CLIENTS','this is an old redirect link from some old email. No emails_id avaialable.');
					$emails_id = FALSE;
				} else $emails_id = end($real_url_pieces);
			}
			return $emails_id;
		}
	}
	function redirect($code = FALSE) {
		//$url -> md5 type
		$this->load->model('redirect_model');
		$client_ip = $this->input->ip_address();
		$client_browser = $this->input->user_agent();
		$result = $this->redirect_model->get_real_url($code);
		if ($result) {
			$request   = $_SERVER["REQUEST_URI"];
			$response  = $result->url;
			//$emails_id = $result->emails_id;
			//detect click activity by the type of request
			/*
			if ($emails_id) {
				if (stripos($response,'tracker')) {
					$email_thumbs_item = $this->crawler_model->get_email_item($emails_id);
					$this->crawler_model->parse_email_item($email_thumbs_item,array('open' => 1));
				} else if (stripos($response,'ultimatesexe')) {
					$email_thumbs_item = $this->crawler_model->get_email_item($emails_id);
					$this->crawler_model->parse_email_item($email_thumbs_item,array('click' => 1));
				} else if (stripos($response,'mailcatch')) {
					$email_item = $this->crawler_model->get_email_item($emails_id);
					//$this->crawler_model->parse_email_item($email_item,array('open' => 1));
					$this->crawler_model->parse_email_item($email_item,array('click' => 1));
				}
			}
			*
			*/
			$referer = isset($_SERVER["HTTP_REFERER"])?$_SERVER["HTTP_REFERER"]:false;
			$this->log->write_log('CLIENTS',"userip:$client_ip referer:$referer client:$client_browser request:$request response:$response");
			redirect($response);
		}

	}
	function track($emails_data_id = NULL){
		if (empty($emails_data_id)) return FALSE;
		$this->load->database('default');
		$email_item = $this->crawler_model->get_email_item($emails_data_id);
		$first_time = $this->emails_model->set_email_open($emails_data_id);
		if ($first_time) $this->crawler_model->parse_email_item($email_item,array('open' => 1));
    }
}