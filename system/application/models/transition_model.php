<?php
/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */
class Transition_model extends active {
	function __construct() {
        parent::__construct();
		$this->load->helper('array');
		$this->load->model('crawler_model');
    }
	function populate_emails_queue() {
		$crawl_emails_limit = 1000;
		$last_emails_id = $this->get_stored_var('emails_queue_import');
		if ( ! $last_emails_id) $last_emails_id = 0;
		echo "emails_queue emails starting in key#$last_emails_id";
		$this->db->select('*',FALSE);
		$this->db->where('emails_id >',$last_emails_id);
		$this->db->limit($crawl_emails_limit);
		$this->db->order_by('emails_id','asc');
		$query = $this->db->get('emails');
		if($query && $query->num_rows > 0) {
			$emails = $query->result_array();
			$last_one = end($emails);
			reset($emails);
			foreach($emails as $item) {
				$check = $this->check_webmaster_niche($item);
				if ($check) {
					$emails_address_id  = $this->insert_into_emails_address($check);
					$emails_data_id     = $this->insert_into_emails_data($check, $emails_address_id);
									      $this->insert_into_emails_queue($check, $emails_data_id);
										  $this->insert_into_emails_bounce($check, $emails_data_id);
				}
			}
			echo "emails_queue emails end in key#" . $last_one['emails_id'];
			$this->set_stored_var('emails_queue_import',$last_one['emails_id']);
		}
	}
	function populate_emails_domain() {
		$crawl_emails_limit = 10000;
		$last_emails_address_id = $this->get_stored_var('emails_domain_import');
		if ( ! $last_emails_address_id) $last_emails_address_id = 0;
		echo "emails_address emails starting in key#$last_emails_address_id";
		$this->db->select('*',FALSE);
		$this->db->where('emails_address_id >',$last_emails_address_id);
		$this->db->limit($crawl_emails_limit);
		$this->db->order_by('emails_address_id','asc');
		$query = $this->db->get('emails_address');
		if ($query && $query->num_rows > 0) {
			$emails = $query->result_array();
			$last_one = end($emails);
			reset($emails);
			foreach($emails as $item) {
				//populate table emails_domain
				//reset the var $emails_domain_id first
				$emails_domain_id = FALSE;
				//first get the domain of the email address
				$address = $item['address'];
				$emails_address_id = $item['emails_address_id'];
				$email_address_domain = end(explode('@', $address));
				//check first if the domain allready exist
				$this->db->select('emails_domain_id');
				$this->db->where('domain', $email_address_domain);
				$query = $this->db->get('emails_domain');
				if ($query && $query->num_rows > 0) {
					//the domain allready exists
					$row = $query->row();
					$emails_domain_id = $row->emails_domain_id;
				} else {
					//dont exists yet so insert and get the key of it
					$this->db->set('domain', $email_address_domain);
					$this->db->insert('emails_domain');
					$emails_domain_id = $this->db->insert_id();
				}

				if ($emails_domain_id) {
					//update the table emails_address with the right emails_domain_id
					$this->db->set('emails_domain_id' , $emails_domain_id);
					$this->db->where('emails_address_id' , $emails_address_id);
					$result = $this->db->update('emails_address');
					if ( ! $result) echo 'emails_address_id:'.$emails_address_id.' failed!';
				}
			}
			echo "emails_address emails end in key#" . $last_one['emails_address_id'];
			$this->set_stored_var('emails_domain_import',$last_one['emails_address_id']);
		}
	}
	function check_webmaster_niche($item = NULL) {
		if ($item) {
			$webmaster_id = $item['webmaster_id'];
			$niche_id     = $item['niche_id'];
			$emails_id    = $item['emails_id'];
			//in case of index:webmaster_id or index:niche_id being null fetch for them in VADOR DB by the email_collected_id
			if ( empty($webmaster_id) || empty($niche_id)) {
				//the parameter is the emails primary key
				//this allows the procedure to be faster. In crawler_model it fetcvhes the email_collected_id in emails by the emails_id associtated
				echo 'doesnt have webmaster_id or niche_id';
				$result = $this->crawler_model->fetch_single_orphan_email($emails_id);
				$item['webmaster_id'] = $result['webmaster_id'];
				$item['niche_id'] = $result['niche_id'];
			}
			return $item;
		} else return FALSE;
	}
	function insert_into_emails_address($item = NULL) {
		if ($item) {
			$email = $item['email'];
			$this->db->select('emails_address_id');
			$this->db->where('address', $email);
			$query = $this->db->get('emails_address');
			if ($query && $query->num_rows > 0 ) {
				 $row = $query->row();
				 $emails_address_id = $row->emails_address_id;
				 if ($emails_address_id) return $emails_address_id; else return NULL;
			} else {
				$this->db->set('address', $email);
				$result = $this->db->insert('emails_address');
				if ($result) {
					$this->db->select('emails_address_id');
					$this->db->where('address', $email);
					$query = $this->db->get('emails_address');
					if ($query && $query->num_rows > 0 ) {
						 $row = $query->row();
						 $emails_address_id = $row->emails_address_id;
						 if ($emails_address_id) return $emails_address_id; else return NULL;
					} else {
						echo $this->db->_error_message();
						return NULL;
					}
				} else {
					echo $this->db->_error_message();
					return NULL;
				}
			}
		} else return NULL;
	}
	function insert_into_emails_data($item = NULL, $emails_address_id = NULL) {
		if ($item && $emails_address_id) {
			$this->db->set('emails_address_id', $emails_address_id);
			$this->db->set('email_collected_id', $item['email_collected_id']);
			$this->db->set('webmaster_id', $item['webmaster_id']);
			$this->db->set('niche_id', $item['niche_id']);
			$this->db->set('mailing_group_id', $item['mailing_group_id']);
			$this->db->set('mailing_template_id', $item['mailing_template_id']);
			$this->db->set('count', $item['count']);
			$this->db->set('created', $item['created']);
			$this->db->set('modified', $item['modified']);
			$this->db->set('login_id', $item['login_id']);
			$this->db->set('alive', $item['alive']);
			$result = $this->db->insert('emails_data');
			if ($result) {
				$emails_data_id = $this->db->insert_id();
				return $emails_data_id;
			} else {
				echo $this->db->_error_message();
				return NULL;
			}
		} else return NULL;
	}
	function insert_into_emails_queue($item = NULL, $emails_data_id = NULL) {
		if ($item && $emails_data_id) {
			$this->db->set('emails_data_id', $emails_data_id);
			$this->db->set('timeline', $item['trigger']);
			$this->db->set('status_id', $item['status_id']);
			$result = $this->db->insert('emails_queue');
			if ( ! $result) {
				echo $this->db->_error_message();
				return NULL;
			}
		}
	}
	function insert_into_emails_bounce($item = NULL, $emails_data_id = NULL) {
		if ($item && $emails_data_id) {
			if ( ! empty($item['track_key'])) {
				$this->db->set('emails_data_id', $emails_data_id);
				$this->db->set('track_key', $item['track_key']);
				$result = $this->db->insert('emails_bounce');
				if ( ! $result) {
					echo $this->db->_error_message();
					return NULL;
				}
			}
		}
	}
}
