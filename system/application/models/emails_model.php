<?php
class Emails_model extends CI_model {
	var $table = 'emails_data';
	var $account_limit = FALSE;
	var $emails_locked = array();
	var $emails_redirected = array();
	var $emails_unique = array();
	var $process_recursion_limit = 5;
	var $process_recursion_counter = 0;
	function __construct(){
		parent::__construct();
		$this->load->helper('array');
		$this->load->helper('email');
		$this->load->model('crawler_model');
		$this->load->config('mailserver');
    }
	//fetch for new emails to send
	function get_new_emails( $limit = FALSE, $emails_data_id = NULL) {
		$config_params		   = $this->config->item('params');
		$emails_fetcher        = $this->get_stored_var('emails_fetcher'); //get information if the is a mailserver already checking the tables to send emails
		//if there is allready some mailserver fetching for new emails then we will wait for him to finish his task
		//intialize this variable first of all
		//this will be the emails to send and they need to be locked before sending
		$emails = array(); // email addresses that will reveice emails in this interaction
		//when the var $emails_data_id is set by parameter then the mailserver is in debug mode
		//and it will only send one email
		if ( ! empty($emails_data_id)) {
			$select = array(
				'emails_data_id',
				'emails_address.emails_address_id',
				'address',
				'email_collected_id',
				'webmaster_id',
				'emails_data.niche_id',
				'mailing_group_id',
				'mailing_template_id',
				'lang_iso',
				'niche_name',
				'CURRENT_TIMESTAMP AS  `timeline`'
			);
			$this->db->select(implode(",",$select),FALSE);
			$this->db->join('emails_address', 'emails_data.emails_address_id = emails_address.emails_address_id', 'inner');
			$this->db->join('mailing.niche', 'emails_data.niche_id = mailing.niche.niche_id', 'inner');
			$this->db->where('emails_data_id', $emails_data_id);
			$this->db->order_by('emails_data_id', 'desc');
			$query = $this->db->get('emails_data');
			//$this->log->write_log('INFO', $this->db->last_query());
			if ( $query && $query->num_rows > 0 ) {
				$row = $query->row();
				$row->email = $row->address;
				$row->emails_id = $row->emails_data_id;
				$emails[] = $row;
			}
			return $emails;
		}
		if ( ! $emails_fetcher) $emails_fetcher = 1;
		else if ($emails_fetcher == 1) {
			$this->log->write_log('INFO','another mailserver process is still active.');
			return FALSE;
		}
		$this->set_stored_var('emails_fetcher', 1);
		//this is the normal flow of method get_new_emails
		$this->account_limit = $limit;
		$this->emails_redirected = array(); //initialize the class propriety for the emails redirect to ohter mailservers brothers.
		$this->emails_unique = array();
		/***********************************************/
		//get the domains that the acocunt cannot send to
		$default_domains_deny  = $config_params['default_domains_deny']; //amazon wont send the emails with the domains included in this config var
		$deny_domains_id = array();
		if ( ! empty($default_domains_deny)) {
			$this->db->select('emails_domain_id');
			$this->db->where("domain REGEXP '".implode("|", $default_domains_deny)."'", FALSE, FALSE);
			$this->db->where('active', 1);
			$query = $this->db->get('emails_domain');
			if ( $query && $query->num_rows > 0 ) {
				$this->log->write_log('INFO','list of domains to deny: '. implode(',', $default_domains_deny));
				$default_domains_deny = array();
				$result = $query->result();
				foreach($result as $row) {
					$deny_domains_id[] = $row->emails_domain_id;
				}
			}
		}
		$emails = $this->process_emails_queue($limit, array(), $deny_domains_id);
		// allow others mailservers to consult the table queue
		$this->set_stored_var('emails_fetcher',0);
		if (empty($emails)) {
			$this->log->write_log('INFO','didnt collect any emails');
			return FALSE;
		} else return $emails;
	}
	//fetch for extra emails because the previous ones
	function process_emails_queue($limit = NULL,$emails_queued = array(), $deny_domains_id = array() , $first_today = NULL) {
		if (empty($limit)) return FALSE;
		$this->process_recursion_counter+= 1;
		if ($this->process_recursion_counter > $this->process_recursion_limit) {
			$this->log->write_log('INFO','process_emails_queue: interrupted by process recursion limit');
			return $emails_queued;
		}
		//get emails from queue that need to be send
		$emails_queued_keys = array();
		//sql query
		$select = array(
			'emails_queue.emails_data_id',
			'emails_address.emails_address_id',
			'emails_address.address',
			'email_collected_id',
			'webmaster_id',
			'emails_data.niche_id',
			'mailing_group_id',
			'mailing_template_id',
			'lang_iso',
			//'niche_name',
			'`timeline`'
		);
		$this->db->select(implode(",",$select),FALSE);
		$this->db->join('emails_data', 'emails_data.emails_data_id = emails_queue.emails_data_id', 'inner');
		$this->db->join('emails_address', 'emails_address.emails_address_id = emails_data.emails_address_id', 'inner');
		//$this->db->join('mailing.niche', 'emails_data.niche_id = mailing.niche.niche_id', 'inner');
		$this->db->where('emails_queue.status_id', 1);
		$this->db->where("emails_queue.timeline < now()", FALSE, FALSE);
		if ( ! empty($first_today)) {
			$this->log->write_log('INFO','process_emails_queue: sending first the emails fetched today.');
			$this->db->where('emails_queue.timeline BETWEEN CURDATE() AND NOW()', FALSE, FALSE);
		} else $this->log->write_log('INFO','process_emails_queue: sending all the emails fetched.');
		if ( ! empty($this->emails_unique)) $this->db->where_not_in('emails_address.emails_address_id',$this->emails_unique);
		if ( ! empty($deny_domains_id)) $this->db->where_not_in('emails_address.emails_domain_id',$deny_domains_id);
		$this->db->limit($limit);
		$query = $this->db->get('emails_queue');
                var_dump($this->db->last_query());
		if ( $query && $query->num_rows > 0 ) {
			//for the email verify if the account is trying to send two mails to the same email address
			//it should send only one mail to one email address at once
			foreach ($query->result() as $email) {
				$emails_data_id = $email->emails_data_id;
				$emails_address_id = $email->emails_address_id;
				if ( ! in_array($emails_address_id, $this->emails_unique)) {
					//not inside yet
					$this->emails_unique[] = $emails_address_id;
					$email->emails_id = $emails_data_id;
					$emails_queued[] = $email;
					$emails_queued_keys[] = $emails_data_id;
				}
			}
			$query->free_result();
			if (! empty($emails_queued_keys)) {
				//set all of them with status_id = 2 so the other mailservers cant pick them up again
				$this->db->where_in('emails_data_id', $emails_queued_keys);
				$this->db->update('emails_queue', array('status_id' => 2));
				//filter all the emails locked so the mailserver dont get blacklisted
				$this->log->write_log('INFO','process_emails_queue: ' . count($emails_queued_keys) . ' emails fetched and finished.');
				//check the emails for invalid domains
				$emails_queued = $this->verify_domain($emails_queued);
				//check the emails for previous bounces
				$emails_queued = $this->verify_bouncelist($emails_queued);
				//check the emails for previous unsubscriptions
				$emails_queued = $this->verify_blacklist($emails_queued);
				//return the remaining valid emails
				$emails_queued_counter = count($emails_queued);
				if (empty($emails_queued) || $emails_queued_counter < $this->account_limit) {
					$this->log->write_log('INFO','did not reached the limit. emails locked so far:' . $emails_queued_counter);
					$limit = $this->account_limit - $emails_queued_counter;
					$emails_queued = $this->process_emails_queue($limit, $emails_queued, $deny_domains_id, $first_today);
				}
				return $emails_queued;
			} else return $emails_queued;
		} else return $emails_queued;
	}
	//fetch for logins to recieve text emails from the mass_senders to increase whitelist status
	function get_whitelist_emails($limit = NULL, $recipients_cycle = NULL) {
		if (empty($limit) || empty($recipients_cycle)) return FALSE;
		$login_emails = array();
		$this->load->model('queue_model');
		$this->db->select('login.login_id,login.ip,login.name,login.email', FALSE);
		$this->db->where('TIMESTAMPDIFF(SECOND,queue.access,CURRENT_TIMESTAMP) >' . $recipients_cycle, FALSE, FALSE);
		$this->db->where('login.status_id', 1);
		$this->db->where('host.status_id', 1);
		$this->db->where('queue.type', 'fake');
		$this->db->where('queue.status_id', 1);
		$this->db->where('account_rate.send_success >', 0);
		$this->db->where('account_rate.bounce_fail', 0);
		//commented in test mode. Remove the comment chars for production mode
		$this->db->join('queue', 'login.login_id = queue.login_id', 'inner');
		$this->db->join('host', 'login.host_id = host.host_id', 'inner');
		$this->db->join('account_rate', 'login.login_id = account_rate.login_id', 'inner');
		$this->db->limit($limit);
		$query = $this->db->get('login');
		if ($query && $query->num_rows > 0) {
			$result = $query->result();
			$this->log->write_log('INFO','got ' . count($result) . ' logins with the time-based flag `fake` in time.');
			// check the time-based flag `fake` in queue table to control the login activity
			foreach($result as $row) {
				$row->type = 'fake';
				$this->queue_model->set($row);
				$login_emails[] = $row;
			}
		}
		return $login_emails;
	}
	//simply return the alive flag of the email item
	function email_alive($emails_data_id = NULL) {
		if (empty($emails_data_id)) return FALSE;
		$this->db->where('emails_data_id',$emails_data_id);
		$query = $this->db->get('emails_hit');
		if ($query && $query->num_rows > 0) {
			$row = $query->row();
			$alive = $row->tracker;
			return $alive;
		} else return FALSE;
	}
	//set the bounce for this client address
	//by emails_data_id
	function set_email_bounce($emails_data_id = NULL) {
		if (empty($emails_data_id)) return FALSE;
		$this->db->where('emails_data_id' , $emails_data_id);
		$result = $this->db->delete('emails_sent');
		if ($result) {
			//it is a bounce so lets remove from emails_sent and insert it into emails_marked
			$sql = 'INSERT INTO emails_marked (emails_data_id, status_id) VALUES (?, 5)';
			$sql .= ' ON DUPLICATE KEY UPDATE status_id = 5';
			$result = $this->db->query($sql, array($emails_data_id));
			return TRUE;
		}
	}
	//set the hit for this client address
	//by tracker
	function set_email_open($emails_data_id = NULL) {
		if (empty($emails_data_id)) return FALSE;
		$this->db->where('tracker' , 1);
		$this->db->where('emails_data_id' , $emails_data_id);
		$query = $this->db->get('emails_hit');
		if ($query && $query->num_rows > 0) return FALSE;
		else {
			//the client is opening the email for the first time or clicked a link inside
			$this->db->set('tracker', 1);
			$this->db->set('emails_data_id', $emails_data_id);
			$this->db->insert('emails_hit');
			return TRUE;
		}
	}
	//set the hit for this client address
	//by vador link
	function set_email_click($emails_data_id = NULL) {
		if (empty($emails_data_id)) return FALSE;
		//if this is the first hit then return number_of_hits else return 0
		$this->db->where('promotool' , 1);
		$this->db->where('emails_data_id' , $emails_data_id);
		$query = $this->db->get('emails_hit');
		if ($query && $query->num_rows > 0) return FALSE;
		else {
			$this->db->set('tracker', 1);
			$this->db->set('promotool', 1);
			$this->db->set('emails_data_id', $emails_data_id);
			if ($query->num_rows == 0) $this->db->insert('emails_hit'); else $this->db->update('emails_hit');
			return TRUE;
		}
		return FALSE;
	}
	//set the hit for this client address
	//by preview unsubscribe link
	function set_email_unsubscribe_preview($emails_data_id = NULL) {
		if (empty($emails_data_id)) return FALSE;
		//if this is the first hit then return number_of_hits else return 0
		$this->db->where_in('unsubscribe' , array(1,2));
		$this->db->where('emails_data_id' , $emails_data_id);
		$query = $this->db->get('emails_hit');
		if ($query && $query->num_rows > 0) return FALSE;
		else {
			$this->db->set('tracker', 1);
			$this->db->set('unsubscribe', 1);
			$tracked = $this->email_alive($emails_data_id);
			if ($tracked >= 1) {
				$this->db->where('emails_data_id', $emails_data_id);
				$this->db->update('emails_hit');
			} else {
				$this->db->set('emails_data_id', $emails_data_id);
				$this->db->insert('emails_hit');
			}
			return TRUE;
		}
		return FALSE;
	}
	//set the hit for this client address
	//by confirmed unsubscribe link
	function set_email_unsubscribe_confirmed($emails_data_id = NULL) {
		if (empty($emails_data_id)) return FALSE;
		//if this is the first hit then return number_of_hits else return 0
		$this->db->where('unsubscribe' , 2);
		$this->db->where('emails_data_id' , $emails_data_id);
		$query = $this->db->get('emails_hit');
		if ($query && $query->num_rows > 0) return FALSE;
		else {
			$this->db->set('tracker', 1);
			$this->db->set('unsubscribe', 2);
			$tracked = $this->email_alive($emails_data_id);
			if ($tracked >= 1) {
				$this->db->where('emails_data_id', $emails_data_id);
				$this->db->update('emails_hit');
			} else {
				$this->db->set('emails_data_id', $emails_data_id);
				$this->db->insert('emails_hit');
			}
			return TRUE;
		}
		return FALSE;
	}
	/**********************************************************************/
	/************************* BOUNCE SECTION *****************************/
	/**********************************************************************/
	//this tries to find the emails_data_id by the track_key supplied from the Library Send.
	function get_email_address_id($address = NULL) {
		//check if got message_id param
		if ( ! empty( $address )) {
			$this->db->like('address' , $address);
			$this->db->limit(1);
			$query = $this->db->get('emails_address');
			if ($query && $query->num_rows > 0) {
				//return the emails_data_id
				$row = $query->row();
				return $row->emails_address_id;
			} return FALSE;
		}
		return FALSE;
	}
	//get the emails_data row by the table key
	function get_email_item($emails_data_id = NULL) {
		if (empty($emails_data_id)) return FALSE;
		$this->db->where('emails_data_id',$emails_data_id);
		$query = $this->db->get('emails_data');
		if ($query && $query->num_rows > 0) return $query->row(); else return FALSE;
	}
	//get the emails_queue row by the table key
	function get_email_queue_item($emails_data_id = NULL) {
		if (empty($emails_data_id)) return FALSE;
		$this->db->where('emails_data_id',$emails_data_id);
		$query = $this->db->get('emails_queue');
		if ($query && $query->num_rows > 0) return $query->row(); else return FALSE;
	}
	//get the emails_data row by the table key
	function get_email_items_by_address_id($emails_address_id = NULL) {
		if (empty($emails_address_id)) return FALSE;
		$this->db->where('emails_address_id',$emails_address_id);
		$query = $this->db->get('emails_data');
		if ($query && $query->num_rows > 0) return $query->result(); else return FALSE;
	}
	//this tries to find the emails_data_id by the track_key supplied from the Library Send.
	function get_email_id_by_messageid($message_id = NULL) {
		//check if got message_id param
		if (empty($message_id)) return FALSE;
		$this->db->where('track_key' , $message_id);
		$query = $this->db->get('emails_bounce');
		//check if found message_id in database
		if ($query && $query->num_rows > 0) {
			//return the emails_data_id
			$row = $query->row();
			return $row->emails_data_id;
		}
		// if didn't receire message_id or couldn't find it return false
		return FALSE;
	}
	//add a new email address but checks first if it allready exists
	function add_address($address = NULL) {
		if (empty($address)) return FALSE;
		$address_domain = end(explode('@', $address));
		$emails_address_id = NULL;
		$emails_domain_id  = NULL;
		//check if this domain allready exists in BD
		$this->db->where('domain', $address_domain);
		$query = $this->db->get('emails_domain');
		if ($query && $query->num_rows > 0) {
			$row = $query->row();
			$emails_domain_id = $row->emails_domain_id;
		} else {
			//it does not exists yet so lets insert it
			$this->db->set('domain', $address_domain);
			$result = $this->db->insert('emails_domain');
			if ($result) $emails_domain_id = $this->db->insert_id();
		}
		if ( ! empty($emails_domain_id)) {
			//check if the email address allready exists
			$this->db->where('address', $address);
			$query = $this->db->get('emails_address');
			if ($query && $query->num_rows > 0) {
				$row = $query->row();
				return $row->emails_address_id;
			} else {
				$this->db->set('emails_domain_id', $emails_domain_id);
				$this->db->set('address', $address);
				$result = $this->db->insert('emails_address');
				if ($result) $emails_address_id = $this->db->insert_id();
			}
		}
		return $emails_address_id;
	}
	//remove an emails_data_id from emails_queue
	function remove_queue($emails_data_id = NULL) {
		if (empty($emails_data_id)) return FALSE;
		//if it is an array lets do where in clause in sql statement
		if (is_array($emails_data_id)) {
			$this->db->where_in('emails_data_id', $emails_data_id);
		} else $this->db->where('emails_data_id', $emails_data_id);
		$this->db->delete('emails_queue');
	}
	//this removes all the emails_data_id in emails_queue if the Library Bounce detects bounces in the senders mailbox.
	function remove_bounces($email_data_id = NULL) {
		$removed = FALSE;
		if ( ! empty($email_data_id)) {
			//use this function to detect previous bounces.If doesnt return boolean FALSE then it returns the emails_address_id
			$emails_address_id = $this->detect_bounces($email_data_id);
			if ($emails_address_id) {
				//insert into table emails_sent
				$this->db->select('emails_data_id');
				$this->db->where('emails_address_id', $emails_address_id);
				$query = $this->db->get('emails_data');
				if ($query && $query->num_rows > 0) {
					foreach ($query->result() as $row) {
						$emails_data_item_id = $row->emails_data_id;
						$sql = 'INSERT INTO emails_marked (emails_data_id, status_id) VALUES (?, 5)';
						$sql .= ' ON DUPLICATE KEY UPDATE status_id = 5';
						$result = $this->db->query($sql, array($emails_data_item_id));
						if ($result) $this->remove_queue($emails_data_item_id);
					}
					$removed = TRUE;
				}
			}
		}
		return $removed;
	}
	//with this emails_data_id i will get the address associated and seek for previous bounces in emails_marked
	//if there is a bounce from previous mailing then this funtion return the emails_address_id
	function remove_invalid_addresses_inqueue($email_data_id = NULL) {
		if ( ! empty($email_data_id)) {
			//get the email address id for the given emails_data_id
			$this->db->select('emails_address_id');
			$this->db->where('emails_data_id', $email_data_id);
			$query = $this->db->get('emails_data');
			if ($query && $query->num_rows > 0) {
				$row = $query->row();
				$emails_address_id = $row->emails_address_id;
				//search for previous invalid emails_data_id for this emails_address_id
				$this->db->select('emails_data.emails_data_id');
				$this->db->where('emails_data.emails_address_id', $emails_address_id);
				$this->db->join('emails_queue', 'emails_data.emails_data_id = emails_queue.emails_data_id', 'inner');
				$query = $this->db->get('emails_data');
				if ($query && $query->num_rows > 0) {
					foreach ($query->result() as $row)
					{
						$emails_data_id = $row->emails_data_id;
						$this->remove_queue($emails_data_id);
					}
				}
			}
		}
	}
	//remove a subscriber from a specific mailing
	function remove_subscribe($emails_data_id = NULL,$mailing_group_id = NULL,$client_ip = NULL) {
		if (empty($emails_data_id) && empty($mailing_group_id)) return FALSE;
		$this->db->where('emails_data_id',$emails_data_id);
		$query = $this->db->get('emails_data');
		if($query && $query->num_rows > 0) {
			$row = $query->row();
			//get the address from emails_address through emails_data_id
			$emails_address_id = $row->emails_address_id;
			$this->db->where('emails_address_id' , $emails_address_id);
			$query = $this->db->get('emails_address');
			if($query && $query->num_rows > 0) {
				$row = $query->row();
				$address = $row->address;
				$this->log->write_log('MAILING','************** unsubscribe procedure begin *********************');
				$request = $_SERVER["REQUEST_URI"];
				$this->log->write_log('MAILING','this request comes from the ip '.$client_ip.' with the uri:'.$request);
				$this->log->write_log('MAILING','client: '.$address.' requested to cancel the mailing #'.$mailing_group_id);
				$this->log->write_log('MAILING','************** unsubscribe procedure end *********************');
				//set as first try for unsubscription for this address in this mailing_group_id
				$query = 'INSERT INTO emails_blacklist (emails_address_id, status_id, mailing_group_id) VALUES (?, ?, ?)';
				$query .= ' ON DUPLICATE KEY UPDATE emails_address_id = ?, status_id = ?, mailing_group_id = ?';
				return $this->db->query($query, array($emails_address_id, 2, $mailing_group_id, $emails_address_id, 2, $mailing_group_id));
			} else return FALSE;
		} else return FALSE;
	}
	//set confirmed an unsubscription from a client related to a specific mailing_group
	function unsubscribe_confirmed($emails_data_id = NULL,$mailing_group_id = NULL,$client_ip = NULL, $reason = 0) {
		if (empty($emails_data_id) && empty($mailing_group_id)) return FALSE;
		$this->db->where('emails_data_id',$emails_data_id);
		$query = $this->db->get('emails_data');
		if($query && $query->num_rows > 0) {
			$row = $query->row();
			//get the address from emails_address through emails_data_id
			$emails_address_id = $row->emails_address_id;
			$this->db->where('emails_address_id' , $emails_address_id);
			$query = $this->db->get('emails_address');
			if ($query && $query->num_rows > 0) {
				$row = $query->row();
				$address = $row->address;
				$this->log->write_log('MAILING','************** unsubscribe procedure begin *********************');
				$request = $_SERVER["REQUEST_URI"];
				$this->log->write_log('MAILING','this request comes from the ip '.$client_ip.' with the uri:'.$request);
				$this->log->write_log('MAILING','client: '.$address.' confirmed the unsubscription of the mailing #'.$mailing_group_id);
				$this->log->write_log('MAILING','************** unsubscribe procedure end *********************');
				//set as first try for unsubscription for this address in this mailing_group_id
				$query = 'INSERT INTO emails_blacklist (emails_address_id, status_id, mailing_group_id, reason) VALUES (?, ?, ?, ?)';
				$query .= ' ON DUPLICATE KEY UPDATE emails_address_id = ?, status_id = ?, mailing_group_id = ?, reason = ?';
				return $this->db->query($query, array($emails_address_id, 1, $mailing_group_id, $reason, $emails_address_id, 1, $mailing_group_id, $reason));
			} else return FALSE;
		} else return FALSE;
	}
	//with this emails_data_id i will get the address associated and seek for previous bounces in emails_marked
	//if there is a bounce from previous mailing then this funtion return the emails_address_id
	function detect_bounces($email_data_id = NULL) {
		if (empty($email_data_id)) return FALSE;
		$is_bounced = FALSE;
		//get the email address id for the given emails_data_id
		$this->db->select('emails_address_id');
		$this->db->where('emails_data_id', $email_data_id);
		$query = $this->db->get('emails_data');
		if ($query && $query->num_rows > 0) {
			$row = $query->row();
			$emails_address_id = $row->emails_address_id;
			//search for previous bounces for this emails_address_id
			$this->db->where('emails_marked.status_id', 5);
			$this->db->where('emails_data.emails_address_id', $emails_address_id);
			$this->db->join('emails_data', 'emails_data.emails_data_id = emails_marked.emails_data_id', 'inner');
			$this->db->limit(1);
			$query = $this->db->get('emails_marked');
			if ($query && $query->num_rows > 0) $is_bounced = $emails_address_id;
		}
		return $is_bounced;
	}
	//when an emails was sent with success by the send library
	function success($emails_data_id = NULL, $message_id = NULL) {
		if (empty($emails_data_id) || empty($message_id)) return FALSE;
		//remove the email item from emails_queue
		//insert the track_key into table emails_bounce
		if ($emails_data_id) {
			$sql = "INSERT INTO emails_bounce (emails_data_id, track_key) VALUES (?,?)";
			$sql .= " ON DUPLICATE KEY UPDATE track_key = ?";
			$result = $this->db->query($sql, array($emails_data_id, $message_id, $message_id));
			if ( ! $result) {
				$this->log->write_log('ERROR','query_error:'. mysql_error($this->db->conn_id));		}
			//insert into table emails_sent
			$sql = "INSERT INTO emails_sent (emails_data_id, status_id) VALUES (?,?)";
			$sql .= " ON DUPLICATE KEY UPDATE status_id = ?";
			$result = $this->db->query($sql, array($emails_data_id, 4, 4));

			if ( ! $result) {
				$this->log->write_log('ERROR','query_error:'. $sql);
			}
			$this->remove_queue($emails_data_id);
		}
	}
	//sets the emails_data item to status 9. This is when the address domain generates fatal error
	function block($emails_data_id = NULL, $status_id = 9) {
		if( ! empty($emails_data_id) && $emails_data_id) {
			// insert the email in emails_marked table with status 9 => blocked
			$sql = 'INSERT INTO emails_marked (emails_data_id, status_id) VALUES (?, '.$status_id.')';
			$sql .= ' ON DUPLICATE KEY UPDATE status_id = '.$status_id;
			$result = $this->db->query($sql, array($emails_data_id));
			if ($result) {
				$this->remove_queue($emails_data_id);                      //remove the detected emails_data_id
				$this->remove_invalid_addresses_inqueue($emails_data_id);    //remove all the precedent emails_data_id associated to that emails_address_id
				$emails_item = $this->crawler_model->get_email_item($emails_data_id);
				if ($emails_item) $this->crawler_model->parse_email_item($emails_item, array('fail' => 1));
			}
		}
	}
	//this is when the mailserver accounts fails to send the locked emails in his session.
	//it resets the emails to status 1 for other accounts to send.
	function unlock_emails_lost($emails = FALSE) {
		$keys = array();
		foreach($emails as $item) {
			if ($item->emails_data_id) $keys[] = $item->emails_data_id;
		}
		$this->db->where_in('emails_data_id',$keys);
		$this->db->set('status_id', 1);   //this sets the email item to the ready state
		if ($this->db->update('emails_queue')) {
			$num = $this->db->affected_rows();
			$this->log->write_log('INFO','inverting '.$num.' emails states to 1.');
		}
	}
	//set as blacklisted
	function set_backlist_email($address = NULL,$mailing = NULL){
		if (empty($address) && empty($mailing)) return FALSE;
		//get the emails_address_id from the address in the table emails_address
		$this->db->where('address' , $address);
		$query = $this->db->get('emails_address');
		if ( $query && $query->num_rows > 0 ) {
			$row = $query->row();
			$emails_address_id = $row->emails_address_id;
			//set as confirmed unsubscribe for this address in this mailing_group_id
			$this->db->set('status_id',1);
			$this->db->set('mailing_group_id',$mailing);
			$this->db->where('emails_address_id',$emails_address_id);
			$this->db->update('emails_blacklist');
			return TRUE;
		} else return FALSE;
	}
	// set active or inactive emails domains (those that didnt pass the mx record test in process control::domains)
	function set_domain_active($emails_domain_id = NULL) {
		if (empty($emails_domain_id)) return FALSE;
		$this->db->where('emails_domain_id', $emails_domain_id);
		$this->db->set('active', 1);
		return $this->db->update('emails_domain');
	}
	function set_domain_inactive($emails_domain_id = NULL) {
		if (empty($emails_domain_id)) return FALSE;
		$this->db->where('emails_domain_id', $emails_domain_id);
		$this->db->set('active', 9);
		return $this->db->update('emails_domain');
	}
	function check_domain_alive($emails_domain_id = NULL) {
		if (empty($emails_domain_id)) return FALSE;
		$this->db->where('emails_domain.emails_domain_id', $emails_domain_id);
		$this->db->join('emails_address', 'emails_data.emails_address_id = emails_address.emails_address_id', 'inner');
		$this->db->join('emails_domain', 'emails_address.emails_domain_id = emails_domain.emails_domain_id', 'inner');
		$this->db->join('emails_hit', 'emails_data.emails_data_id = emails_hit.emails_data_id', 'inner');
		$query = $this->db->get('emails_data');
		if($query && $query->num_rows > 0) return TRUE; else return FALSE;
	}
	/**********************************************************************/
	/* methods to check for bounces/trials/blacklist items before sending */
	function verify_bouncelist($emails = NULL) {
		if (empty($emails)) return FALSE;
		$emails_list_result = array();
		$emails_bounced = 0;
		foreach($emails as $item) {
			$emails_data_id = $item->emails_data_id;
			$bounced = $this->remove_bounces($emails_data_id);
			//if bounced then dont include it to the list to return and set the $emails_bounced counter
			if ($bounced) $emails_bounced+= 1;
			else $emails_list_result[] = $item;
		}
		if ($emails_bounced) $this->log->write_log('INFO','bounce step mail filter - ' .$emails_bounced. ' emails selected.');
		return $emails_list_result;
	}
	function verify_blacklist($emails = NULL,$mailing_group_id = FALSE){
		if (empty($emails)) return FALSE;
		$emails_blacklisted = 0;
		$emails_list_result = array();
		foreach($emails as $item) {
			$emails_data_id    = $item->emails_data_id;
			$emails_address_id = $item->emails_address_id;
			$mailing_group_id  = $item->mailing_group_id;
			//see if this email have unsubscribed
			$this->db->where('emails_address_id', $emails_address_id);
			$this->db->where('mailing_group_id', $mailing_group_id);
			$this->db->where('status_id', 1); //status 1 means that the client confirmed the unsubscription
			$query = $this->db->get('emails_blacklist');
			//if blacklisted then dont include it to the list to return
			if($query && $query->num_rows > 0) {
				$sql = 'INSERT INTO emails_marked (emails_data_id, status_id) VALUES (?, 12)';
				$sql .= ' ON DUPLICATE KEY UPDATE status_id = 12';
				$result = $this->db->query($sql, array($emails_data_id));
				if ($result) {
					//get all the emails_data_id for this emails_address_id and mailing_group_id
					$this->db->select('emails_data_id');
					$this->db->where('emails_address_id', $emails_address_id);
					$this->db->where('mailing_group_id', $mailing_group_id);
					$query = $this->db->get('emails_data');
					if($query && $query->num_rows > 0) {
						foreach ($query->result() as $row) {
							$this->remove_queue($row->emails_data_id);
						}
					}
				}
				$emails_blacklisted+= 1;
			} else $emails_list_result[] = $item;
		}
		if ($emails_blacklisted) $this->log->write_log('INFO','blacklist step mail filter - ' .$emails_blacklisted. ' emails selected.');
		return $emails_list_result;
	}
	function verify_domain($emails = FALSE) {
		if ($emails === FALSE) return FALSE;
		$emails_to_deny = array();
		$emails_list_result = array();
		foreach($emails as $item) {
			$email = $item->address;
			$emails_data_id = $item->emails_data_id;
			$result = valid_email($email, TRUE);
			if ( ! $result) {
				//if domain is not valid
				$emails_to_deny[] = $emails_data_id;
			} else $emails_list_result[] = $item;
		}
		if ( ! empty($emails_to_deny)) {
			$this->log->write_log('INFO','domain step mail filter - ' .count($emails_to_deny). ' emails selected.');
			foreach($emails_to_deny as $emails_data_id){
				$this->block($emails_data_id);
			}
		}
		return $emails_list_result;
	}
	/*******************************************************************************/
	/* methods to render the templates associated with the webmaster_id & niche_id */
	function get_template_parsed($template_id = FALSE,$additional_fields = FALSE, $include_tracker = TRUE) {
		$mailing = $this->load->database('mailing', TRUE);
		$this->load->model('mailing_model');
		//check if theres is translation for the template
		$iso_code = $additional_fields->lang_iso;
		$is_translation = $this->mailing_model->is_translation($template_id, $iso_code);
		$mailing->where('mailing_template_id',$template_id);
		$query = $mailing->get('mailing_template');
		$template = FALSE;
		if($query && $query->num_rows > 0) {
			$result = $query->row();
			if (in_array($is_translation, array(1,2))) {
				//it is a translation or the original template
				if ($is_translation == 2) {
					//it is a translation
					$translated_template = $this->mailing_model->translate($template_id, $iso_code);
					if ($translated_template) {
						//there is a translation
						$result->subject = $translated_template->subject;
						$result->html = $translated_template->html;
					}
				}
				$template = $this->template($result, true, $additional_fields, $include_tracker);
			}
		}
		return $template;
	}
	function template($template = array(),$return_obj = FALSE,$additional_fields = FALSE, $include_tracker = TRUE){
			$config_params = $this->config->item('params');
			$hostname = $config_params['hostname'];
			$this->load->library('parser');
			$rules = unserialize(utf8_decode($template->rules));
			$subject = $template->subject;
			$html = $template->html;
			$keywords = FALSE;
			if( ! is_array($rules)) {
				if($return_obj) {
					return $template;
				} else {
					$html = '<table>';
					$html.= '<tr>';
						$html.= '<td style="vertical-align:top;">Subject:</td>';
						$html.= '<td style="vertical-align:top;">'.$subject.'</td>';
					$html.= '<tr>';
					$html.= '<tr>';
						$html.= '<td style="vertical-align:top;">body:</td>';
						$html.= '<td style="vertical-align:top;">'.$html.'</td>';
					$html.= '<tr>';
					$html.= '</table>';
					return $html;
				}
			}
			if($additional_fields === FALSE) {
				$additional_fields = array(
					'webmaster_id' => 2,
					'niche_id' => 19,
					'email' => 'preview@total.com',
					'mailing_group_id' => 0
				);
			}
			foreach($rules as $index => $item){
					if($item['field'] == 'keyword') {
							$keywords = $item['value'];
							unset($rules[$index]);
							continue;
					}
			}
			$keywords = str_replace('"', '#',$keywords);
			$keywords_encoded = utf8_encode($keywords);
			$keywords_encoded = str_replace('#', '"', $keywords_encoded);
			if($keywords_encoded) {
				$keywords = json_decode($keywords_encoded,true);
				if (is_array($keywords)) {
					$words_to_switch = array();
					foreach($keywords as $word => $synonymes){
						$word_decoded = $word;
						$synonymes[] = array('active' => TRUE,'data' => $word_decoded);
						$random_index = array_rand($synonymes);
						$substitue = $synonymes[$random_index]['data'];
						$words_to_switch[$word_decoded] = $substitue;
					}
					foreach($words_to_switch as $index => $value) {
						$subject = str_replace($index,$value,$subject);
						$html = str_replace($index,$value,$html);
					}
				}
			}
			//these template variables are those associated with a template that attributes constants like webmaster_id,niche_id,...
			//they are created in vador side.
			//this process sees if every single template variable have some relation with each other
			//example:
			//first variable
			//{promourl} => http://promo-test.vador.com
			//
			//second variable
			//{promourl}/index.php
			//
			//the result will be -> http://promo-test.vador.com/index.php
			$parsed_rules = $rules;
			foreach($rules as $row) {
				foreach($parsed_rules as &$sub_row){
					$sub_row['value'] = $this->parser->parse_generic($sub_row['value'], array($row['field'] => $row['value']), TRUE);
				}
			}
			//this is the final step for those template variables created in vador
			foreach($parsed_rules as $row){
				$subject = $this->parser->parse_generic($subject, array($row['field'] => $row['value']), TRUE);
				$html = $this->parser->parse_generic($html, array($row['field'] => $row['value']), TRUE);
			}
			//here we process the template variables with data associated to a client email
			//the information can be anything that comes in a row from table emails_data and emails_queue and emails_address
			if($additional_fields) {
				$subject = $this->parser->parse_generic($subject, $additional_fields, TRUE);
				$html = $this->parser->parse_generic($html, $additional_fields, TRUE);
			};
			//lets remove those keywords that couldn't be processed because their were already missing from  mailing vador
			$html = preg_replace('/{.*}/i', '', $html);
			if($return_obj) {
				if ($include_tracker) $html.= '<img src="http://'.$hostname.'/tracker.jpg?id='.$additional_fields->emails_data_id.'" alt="mailing unsubscription" width="5" height="5" />';
				$template = new stdClass;
				$template->subject = $subject;
				$template->html = $html;
				return $template;
			}
			$html = '<table>';
			$html.= '<tr>';
				$html.= '<td style="vertical-align:top;">Subject:</td>';
				$html.= '<td style="vertical-align:top;">'.$subject.'</td>';
			$html.= '<tr>';
			$html.= '<tr>';
				$html.= '<td style="vertical-align:top;">body:</td>';
				$html.= '<td style="vertical-align:top;">'.$html.'</td>';
			$html.= '<tr>';
			$html.= '</table>';
			return $html;
    }
	//function to handle table stored_var
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
}