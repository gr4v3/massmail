<?php
class Webservice extends CI_Controller {
    function __construct() {
		parent::__construct();
		$this->load->helper('array');
		$this->load->helper('email');
		$this->load->library('log');
    }
    // show available webservice methods
    function index() {
            $this->load->view('webservice/index');
    }
    // procedure to attempt to check real existence of an email address
    function real_address($address = NULL) {
            if (empty($address)) return FALSE;
            $this->load->library('Send');
            $this->load->model('crawler_model');
            $proxy = $this->crawler_model->crawl_proxies(1);
            $result = $this->send->test_recipient($address, $proxy->ip);
            $this->log->write_log('WEBSERVICE','************************************');
            $this->log->write_log('WEBSERVICE','webservice `real_address` invoked from ' . $_SERVER['SERVER_ADDR']);
            $this->log->write_log('WEBSERVICE','parameters:');
            $this->log->write_log('WEBSERVICE','  address:' . $address);
            $this->log->write_log('WEBSERVICE','webservice resulted in:' . $result);
            $this->log->write_log('WEBSERVICE','************************************');
            // check if this procedure is called by cli or by http
            if ($this->input->is_cli_request()) return $result;
            // this procedure was called through http
            if ($result) die('TRUE'); else die('FALSE');
    }
    // procedure that recieves posts from sources outside mailserver
    function mark_content_spam($email = NULL, $report = NULL) {
            if (empty($email)) $email = $this->input->post('email');
            if (empty($report)) $report = $this->input->post('report');
            $this->log->write_log('SPAM','******************************');
            $this->log->write_log('SPAM','spam report');
            $this->log->write_log('SPAM','email: ' . $email);
            $this->log->write_log('SPAM','report: ' . print_r($report, TRUE));
            $this->log->write_log('SPAM','******************************');
            $this->log->write_log('WEBSERVICE','************************************');
            $this->log->write_log('WEBSERVICE','webservice `mark_content_spam` invoked from ' . $_SERVER['SERVER_ADDR']);
            $this->log->write_log('WEBSERVICE','parameters:');
            $this->log->write_log('WEBSERVICE','    email:' . $email);
            $this->log->write_log('WEBSERVICE','   report:' . $report);
            $this->log->write_log('WEBSERVICE','webservice resulted in:' . NULL);
            $this->log->write_log('WEBSERVICE','************************************');
    }
    // procedure to add manually an email address into emails_stack (usually this is for test modes only)
    function add_emails_stack($address = NULL, $email_collected_id = NULL, $webmaster_id = NULL, $niche_id = NULL, $lang_iso = NULL) {
            if (empty($address)) return FALSE;
            if ( ! valid_email($address)) die('invalid email address');
            $this->load->model('mailing_model');
            $client = new stdClass;
            $client->address = $address;
            if (empty($email_collected_id)) $client->email_collected_id = 1; else $client->email_collected_id = $email_collected_id;
            if (empty($webmaster_id)) $client->webmaster_id = 2; else $client->webmaster_id = $webmaster_id;
            if (empty($niche_id)) $client->niche_id = 19; else $client->niche_id = $niche_id;
            if (empty($lang_iso)) $client->lang_iso = 'fr'; else $client->lang_iso = $lang_iso;
            $result = $this->mailing_model->import_emails_stack($client);
            $this->log->write_log('WEBSERVICE','************************************');
            $this->log->write_log('WEBSERVICE','webservice `add_emails_stack` invoked from ' . $_SERVER['SERVER_ADDR']);
            $this->log->write_log('WEBSERVICE','parameters:');
            $this->log->write_log('WEBSERVICE','              address:' . $address);
            $this->log->write_log('WEBSERVICE','   email_collected_id:' . $email_collected_id);
            $this->log->write_log('WEBSERVICE','         webmaster_id:' . $webmaster_id);
            $this->log->write_log('WEBSERVICE','             niche_id:' . $niche_id);
            $this->log->write_log('WEBSERVICE','             lang_iso:' . $lang_iso);
            $this->log->write_log('WEBSERVICE','webservice resulted in:' . $result);
            $this->log->write_log('WEBSERVICE','************************************');
            if ($this->input->is_cli_request()) return $result;
            // this procedure was called through http
            if ($result) $this->index(); else die('FALSE');
    }
}