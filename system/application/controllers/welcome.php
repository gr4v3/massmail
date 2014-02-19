<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Welcome extends CI_Controller {

	/**
	 * Index Page for this controller.
	 *
	 * Maps to the following URL
	 * 		http://example.com/index.php/welcome
	 *	- or -
	 * 		http://example.com/index.php/welcome/index
	 *	- or -
	 * Since this controller is set as the default controller in
	 * config/routes.php, it's displayed at http://example.com/
	 *
	 * So any other public methods not prefixed with an underscore will
	 * map to /index.php/welcome/<method_name>
	 * @see http://codeigniter.com/user_guide/general/urls.html
	 */
    function __construct(){
		parent::__construct();
		$this->load->library('form_validation');
		$this->load->library('TCTemplate');
		$this->load->helper('language');
		$this->lang->load('french', 'french');
		$this->load->model('emails_model');
		$this->load->model('crawler_model');
	}
	function index() {
		$this->tctemplate->set_template('templates/default');
		$this->tctemplate->set_view_folder('mailing');
		$this->tctemplate->show('Index', 'landpage');
	}
	function unsubscribe($emails_data_id = FALSE, $mailing_group_id = FALSE) {
		$this->tctemplate->set_template('templates/default');
		$this->tctemplate->include_js_file('mootools_core.js');
		$this->tctemplate->include_js_file('common.js');
		$this->tctemplate->set_view_folder('mailing');
		$this->form_validation->set_rules('address', 'Email', 'trim|required|valid_email');
		// default form data
		// var `emails_data_id` can be provided by the link from mailinbox or by POST from the form
		// var `mailing_group_id` can be provided by the link from mailinbox or by POST from the form
		// when is provided by the link it indicates that the client clicked the unsubscription link in the mailbox
		$form_data = array(
			'address'          => FALSE,
			'emails_data_id'   => $emails_data_id|$this->input->post('emails_data_id'),
			'mailing_group_id' => $mailing_group_id|$this->input->post('mailing_group_id'),
			'info'             => FALSE
		);
		if ($this->form_validation->run() == FALSE) {
			// the form validation failed and the reasons can be two
			// 1. its the first time the link is requested
			// 2. the email address is not valid
			if ($form_data['emails_data_id']) {
				//the request came from the mailbox so we know allready the emails_data_id and mailing_group_id
				//the requests comes in the format http://{domain}/index.php/mailing/unsubscribe/{emails_data_id}/{mailing_group_id}
				//so we will set the unsubscribe preview for this client relative to the mailing he got
				$client_ip = $this->input->ip_address();
				$email_item = $this->emails_model->get_email_item($form_data['emails_data_id']);
				$first_time = $this->emails_model->set_email_unsubscribe_preview($form_data['emails_data_id']); // table: emails_hit
				$this->emails_model->remove_subscribe($form_data['emails_data_id'],$form_data['mailing_group_id'],$client_ip); // table:emails_blacklist
				if ($first_time) $this->crawler_model->parse_email_item($email_item,array('unsubs_preview' => 1));
			}
			//the client submited the form (we detect by the POST var called `execute`)
			if ($this->input->post('execute')) {
				//the client entered an email with an invalid format
				$form_data['info'] = $this->lang->line('mailing_unsubscription_invalid_email');
			}
			$this->tctemplate->show('Index', 'landpage', $form_data);
		} else {
			//the client submited a valid email address
			$address = $this->input->post('address');
			$emails_address_id = $this->emails_model->get_email_address_id($address);
			$emails_data_item  = $this->emails_model->get_email_item($form_data['emails_data_id']);
			//the POST var `execute` tells us that the client have submited the form
			if ($this->input->post('execute') && $emails_address_id == $emails_data_item->emails_address_id) {
				//the client email exists in our database
				if ($form_data['emails_data_id']) {
					//the email address inserted in the form is the same present in database
					//and the first request came directly from the mailinbox
					$client_ip = $this->input->ip_address();
					$email_item = $this->emails_model->get_email_item($form_data['emails_data_id']);
					$first_time = $this->emails_model->set_email_unsubscribe_confirmed($form_data['emails_data_id']); // table: emails_hit
					$this->emails_model->unsubscribe_confirmed($form_data['emails_data_id'],$form_data['mailing_group_id'], $client_ip); // table:emails_blacklist
					if ($first_time) $this->crawler_model->parse_email_item($email_item,array('unsubs_confirmed' => 1));
					$form_data['address'] = $address;
					$form_data['info'] = $this->lang->line('mailing_unsubscription_success');
					$this->tctemplate->show('Index', 'success', $form_data);
				} else {
					//the client confirmed the email insertion step
					//but the request came from an external link
					//so we will unsubscribe the client for all the mailings
					// ...code for that
					//1. with the emails_address_id get the first emails_data_id in emails_queue with status_id 1 and set it to status 1 in emails_hit|unsubscribe
					//2. get all the emails_data from emails_queue relative to the emails_address_id and delete them
					//3. his unsubscription request is oneshot
					$emails_data_result = $this->emails_model->get_email_items_by_address_id($emails_address_id);
					if ($emails_data_result) {
						$first_emails_data_row = current($emails_data_result);
						$first_emails_data_id = $first_emails_data_row->emails_data_id;
						$this->emails_model->set_email_unsubscribe_confirmed($first_emails_data_id);
						foreach($emails_data_result as $item) {
							$this->emails_model->remove_queue($item->emails_data_id);
						}
					}
					$form_data['info'] = $this->lang->line('mailing_unsubscription_success');
					$this->tctemplate->show('Index', 'success', $form_data);
				}

			} else {
				//the client submited the form but the address does not exist in database
				$form_data['info'] = $this->lang->line('mailing_unsubscription_fail');
				$this->tctemplate->show('Index', 'landpage', $form_data);
			}
		}
	}
}

/* End of file welcome.php */
/* Location: ./application/controllers/welcome.php */