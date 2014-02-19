<?php
class Mailing extends CI_Controller {
	var $queue = array();
	function __construct() {
		parent::__construct();
		$this->load->library('TCLogger');
		$this->load->library('form_validation');
		$this->load->library('TCTemplate');
		$this->load->helper('language');
		$this->load->helper('array');
		$this->load->model('emails_model');
		$this->load->model('mailing_model');
		$this->load->model('crawler_model');
		$this->lang->load('french', 'french');
	}
	function index() {
		$this->tctemplate->set_template('templates/default');
		$this->tctemplate->set_view_folder('mailing');
		$this->tctemplate->show('Index', 'index');
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
			'info'             => FALSE,
			'reason'           => FALSE
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
				$this->emails_model->remove_subscribe($form_data['emails_data_id'],$form_data['mailing_group_id'],$client_ip);
				$email_item = $this->emails_model->get_email_item($form_data['emails_data_id']);
				$first_time = $this->emails_model->set_email_unsubscribe_preview($form_data['emails_data_id']);
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
			$client_ip = $this->input->ip_address();
			$emails_address_id = $this->emails_model->get_email_address_id($address);
			// if the mailing service is request from inside an email
			if ($form_data['emails_data_id'] > 0) {
				$emails_data_item  = $this->emails_model->get_email_item($form_data['emails_data_id']);
				//the POST var `execute` tells us that the client have submited the form
				if ($emails_data_item && $this->input->post('execute') && $emails_address_id == $emails_data_item->emails_address_id) {
					//the client email exists in our database
					if ($form_data['emails_data_id'] && $form_data['mailing_group_id']) {
						//the email address inserted in the form is the same present in database
						//and the first request came directly from the mailinbox
						$email_item = $this->emails_model->get_email_item($form_data['emails_data_id']);
						$first_time = $this->emails_model->set_email_unsubscribe_confirmed($form_data['emails_data_id'], $form_data['mailing_group_id']);
						// set in emails_blacklist the confirmation
						if (isset($form_data['reason'])) {
							if (is_array($form_data['reason'])) $form_data['reason'] = implode(',', $form_data['reason']);
							else $form_data['reason'] = $form_data['reason'];
						}
						$this->emails_model->unsubscribe_confirmed($form_data['emails_data_id'], $form_data['mailing_group_id'], $client_ip, $form_data['reason']);
						if ($first_time) $this->crawler_model->parse_email_item($email_item,array('unsubs_confirmed' => 1));
						$form_data['address'] = $address;
						$form_data['info'] = $this->lang->line('mailing_unsubscription_success');
						$this->tctemplate->show('Index', 'success', $form_data);
					} else {
						$form_data['info'] = $this->lang->line('mailing_unsubscription_success');
						$this->tctemplate->show('Index', 'success', $form_data);
					}
				} else {
					//the client submited the form but the address does not exist in database
					$form_data['info'] = $this->lang->line('mailing_unsubscription_fail');
					$this->tctemplate->show('Index', 'landpage', $form_data);
				}
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
					$this->emails_model->set_email_unsubscribe_confirmed($first_emails_data_row->emails_data_id, $first_emails_data_row->mailing_group_id);
					if (isset($form_data['reason'])) {
						if (is_array($form_data['reason'])) $form_data['reason'] = implode(',', $form_data['reason']);
						else $form_data['reason'] = $form_data['reason'];
					}
					$this->emails_model->unsubscribe_confirmed($first_emails_data_row->emails_data_id, $first_emails_data_row->mailing_group_id, $client_ip, $form_data['reason']);
					foreach($emails_data_result as $item) {
						$this->emails_model->remove_queue($item->emails_data_id);
					}
				}
				$form_data['info'] = $this->lang->line('mailing_unsubscription_success');
				$this->tctemplate->show('Index', 'success', $form_data);
			}

		}
	}
	function execute($mailing_group_id = FALSE) {
            
                
            
		if ( ! $this->input->is_cli_request()) {
			//die('security warning! mailserver not running in CLI mode!');
		}
		//countrycode
		$this->load->config('mailserver');
		$countrycode = $this->config->item('countrycode');
		//get the next active mailing.
		$auto_mailing_result = $this->mailing_model->get_mailings();
		$status = array();
		if ( ! empty($auto_mailing_result)) {
			//get the real servertime from mailserver.
			$now = $this->mailing_model->get_remote_timestamp();
			$auto_mailing = $auto_mailing_result;
			$webmasters = false;
			$mailing_group_id = $auto_mailing->mailing_group_id;
			$status[] = "mailing #$mailing_group_id selected";
			// see if this mailing sets the templates to all webmaster or to some
			if( isset($auto_mailing->webmasters) && ! empty($auto_mailing->webmasters) && $auto_mailing->allwebmasters != 1 ) $webmasters = explode(",",$auto_mailing->webmasters);
			// fetch new emails from emails_collected to send to mailing
			$fetched_mails = $this->mailing_model->fetchmails($mailing_group_id,$webmasters);
			if( ! $webmasters) $status[] = "sending emails to all webmasters";
			else $status[] = "sending emails to webmasters -> ".implode(',',$webmasters);
			if (!empty($fetched_mails)) {
				$diference = $now;
				// fetch the templates associated to mailing_group previously selected
				$templates = $this->mailing_model->get_templates($mailing_group_id);
				$templates_processed = 0;
				if ($templates && ! empty($templates)) {
					foreach($fetched_mails as $email) {
						// assign the right templates according to the webmaster and niche associated with the email
						foreach($templates as $template) {
							$templates_associated = $this->mailing_model->assign_template_to_mail($template, $email);
							if ($templates_associated) {
								//lets see if there is a template for this client
								if (isset($countrycode) && isset($countrycode[$email->lang_iso])) $email->lang_iso = $countrycode[$email->lang_iso];
								$exist_template = $this->mailing_model->exist_template($template->mailing_template_id, $email->lang_iso);
								if ($exist_template) {
									if( ! empty($auto_mailing->start)) {
										$trigger = ($template->time - $auto_mailing->start) + $diference;
									} else $trigger = $template->time + $diference;
									$this->parse_templates($mailing_group_id,$templates_associated,$email,$trigger);
									$templates_processed+= 1;
								} else continue;
							}
						}
					}
				}
				$status[] = count($templates).' templates processed';
				$status[] = count($fetched_mails)." emails fetched for mailing#".$mailing_group_id;
			} else $status[] = 'client emails unavailable to send to mailing#'.$mailing_group_id;
		} else $status[] = 'mailing group unavailable or all of them inactive.';
		$mailing_automailing_index = $this->mailing_model->get_stored_var('mailserver_automailing');
		$status[] = 'mailing crawler ended in index ' . $mailing_automailing_index;
                $status[] = $_SERVER['SERVER_ADDR'];
		$this->tclogger->log('mailing','automailing',$status);
	}
	function parse_templates($mailing_group_id,$template,$email,$trigger) {
		$email->mailing_group_id = $mailing_group_id;
		$email->mailing_template_id = $template->mailing_template_id;
		date_default_timezone_set('Europe/Lisbon'); // this is only for test server 192.168.2.140!!! DO NOT SEND TO PRODUCTION MODE WTHOUT COMMENTING THIS!!!!!
		$email->timeline = date("Y-m-d H:i:s", $trigger);
		$email->status_id = 1;
                var_dump($email);
		$this->mailing_model->send_mailserver_procedure($email);
	}
}