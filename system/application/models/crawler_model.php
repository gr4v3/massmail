<?php
class Crawler_model extends CI_model {
	function __construct(){
		parent::__construct();
		$this->load->helper('array');
		$this->load->config('mailserver');
		$this->load->library('TCLogger');
    }
	function set_stats_data($mailing_id = NULL, $template_id = NULL, $webmaster_id = NULL, $date = NULL, $state = NULL) {
		if ($mailing_id && $template_id && $webmaster_id) {
			$field = key($state);
			$value = current($state);
			$date = date('Y-m-d',strtotime($date));
			$query = "INSERT DELAYED INTO stats (mailing, template, webmaster, datetime, $field) VALUES ($mailing_id, $template_id, $webmaster_id, '$date', $value)";
			$query .= " ON DUPLICATE KEY UPDATE $field = $field + $value";
			return $this->db->query($query);
		}
	}
	function parse_email_item($item = NULL, $state = NULL) {
		if (empty($item)) return FALSE;
		$mailing_id   = $item->mailing_group_id;
		$template_id  = $item->mailing_template_id;
		$webmaster_id = $item->webmaster_id;
		$date         = date('Y-m-d H:i:s',mktime());
		$this->set_stats_data($mailing_id,$template_id,$webmaster_id,$date,$state);
	}
	function parse_bounce_item($item = NULL, $data = NULL) {
		if (empty($item)) return FALSE;
		$emails_address_id = $item->emails_address_id;
		$this->db->where('emails_marked.status_id', 5);
		$this->db->where('emails_address.emails_address_id', $emails_address_id);
		$this->db->join('emails_data', 'emails_data.emails_data_id = emails_marked.emails_data_id');
		$this->db->join('emails_address', 'emails_data.emails_address_id = emails_address.emails_address_id');
		$query = $this->db->get('emails_marked');
		if ($query && $query->num_rows > 0) return FALSE;
		else $this->parse_email_item($item, $data);
	}
	function get_email_item($emails_data_id = NULL) {
		if (empty($emails_data_id)) return FALSE;
		$DEFAULT_DB = $this->load->database('default', TRUE);
		$DEFAULT_DB->where('emails_data_id',$emails_data_id);
		$query = $DEFAULT_DB->get('emails_data');
		if ($query && $query->num_rows > 0) return $query->row(); else return FALSE;
	}
	function crawl_logins($limit = 1000) {
		$logins_fetcher = $this->get_stored_var('logins_fetcher');
		if ( ! $logins_fetcher) $logins_fetcher = 0;
		$this->db->where('login_id >', $logins_fetcher);
		$this->db->order_by('login_id', 'asc');
		$this->db->limit($limit);
		$query = $this->db->get('login');
		if ($query && $query->num_rows > 0 ) {
			$result = $query->result();
			$first_row = end($result);
			$this->set_stored_var('logins_fetcher', $first_row->login_id);
			return $result;
		}
		return FALSE;
	}
	function crawl_domains($limit = 1000) {
		$domains_fetcher = $this->get_stored_var('domains_fetcher');
		if ( ! $domains_fetcher) $domains_fetcher = 0;
		$this->db->where('emails_domain_id >', $domains_fetcher);
		$this->db->order_by('emails_domain_id', 'asc');
		$this->db->limit($limit);
		$query = $this->db->get('emails_domain');
		if ($query && $query->num_rows > 0 ) {
			$result = $query->result();
			$first_row = end($result);
			$this->set_stored_var('domains_fetcher', $first_row->emails_domain_id);
			return $result;
		}
		return FALSE;
	}
	function crawl_proxies($limit = 1) {
		$config_params		  = $this->config->item('params');
		$country_crawler_host = $config_params['country_crawler_host'];
		$proxies_fetcher_index = 'proxies_fetcher_' .  $country_crawler_host;
		$proxies_fetcher = $this->get_stored_var($proxies_fetcher_index);
		if ( $proxies_fetcher === FALSE ) $proxies_fetcher = 0;
		$this->db->where('id >', $proxies_fetcher);
		$this->db->where('active', 1);
		$this->db->where_in('status_id', array(1,5));
		$this->db->where('owner', $_SERVER['SERVER_ADDR']);
		$this->db->order_by('id', 'asc');
		$this->db->limit($limit);
		$query = $this->db->get('available_ips');
		if ($query && $query->num_rows > 0 ) {
			if ($limit == 1) {
				$row = $query->row();
				$this->set_stored_var($proxies_fetcher_index, $row->id);
				return $row;
			}
			$result = $query->result();
			$first_row = end($result);
			$this->set_stored_var($proxies_fetcher_index, $first_row->id);
			return $result;
		} else if ($proxies_fetcher > 0) {
			$this->set_stored_var($proxies_fetcher_index, 0);
			return $this->crawl_proxies($limit);
		}
		return FALSE;
	}
	// custom method for the migration of the old mailserver db structure to the new structure
	function populate_emails_new_structure() {
		$old_mailserver = $this->load->database('old_mailserver', TRUE);
		$crawl_emails_limit = 20000;
		$last_emails_id = $this->get_stored_var('old_emails_import');
		if ( ! $last_emails_id) $last_emails_id = 0;
		echo "emails_new_structure starting in key#$last_emails_id<br />";
		//collect by steps of 5000 each cron call
		$old_mailserver->where('emails_id >',$last_emails_id);
		$old_mailserver->limit($crawl_emails_limit);
		$old_mailserver->order_by('emails_id','asc');
		$query = $old_mailserver->get('emails');
		if($query && $query->num_rows > 0) {
			$emails = $query->result_array();
			$query->free_result();
			$last_emails_row = end($emails);
			$last_emails_id = $last_emails_row['emails_id'];
			reset($emails);
			$this->load->database('default');
			foreach($emails as $item) {
				$emails_id = $item['emails_id'];
				$trigger   = date('Y-m-d h:i:s', $item['trigger']);
				$status_id = $item['status_id'];
				$email     = $item['email'];
				$email_collected_id = $item['email_collected_id'];
				$webmaster_id = $item['webmaster_id'];
				$niche_id = $item['niche_id'];
				$mailing_group_id = $item['mailing_group_id'];
				$mailing_template_id = $item['mailing_template_id'];
				$lang_iso = $item['lang_iso'];
				//first insert into email_address if does not exists yet
				$emails_address_id = FALSE;
				$this->db->where('address', $email);
				$query = $this->db->get('emails_address');
				if ($query && $query->num_rows > 0) {
					//exists allready so i will fetch his primary key
					$emails_address_id = $query->row()->emails_address_id;
				} else {
					//inserting the address for the first time
					$this->db->set('address', $email);
					$query = $this->db->insert('emails_address');
					if ($query) $emails_address_id = $this->db->insert_id();
				}
				//check if emails_address_id do really exist
				if ($emails_address_id) {
					//lets insert his data to emails_data first and then emails_queue or emails_marked
					$this->db->set('emails_address_id', $emails_address_id);
					$this->db->set('email_collected_id', $email_collected_id);
					$this->db->set('webmaster_id', $webmaster_id);
					$this->db->set('niche_id', $niche_id);
					$this->db->set('mailing_group_id', $mailing_group_id);
					$this->db->set('mailing_template_id', $mailing_template_id);
					$this->db->set('lang_iso', $lang_iso);
					$query = $this->db->insert('emails_data');
					if ($query) {
						$emails_data_id = $this->db->insert_id();
						$status_id = $item['status_id'];
						if ( in_array($status_id, array(1,2)) ) {
							//email not sent yet
							//the email goes to emails_queue
							$this->db->set('emails_data_id', $emails_data_id);
							//2011-11-08 00:00:00
							$this->db->set('timeline', $trigger);
							$this->db->set('status_id', $status_id);
							$this->db->insert('emails_queue');
						} elseif ( $status_id == 4 ) {
							//the emails was sent
							$this->db->set('emails_data_id', $emails_data_id);
							$this->db->set('status_id', $status_id);
							$this->db->insert('emails_sent');
							//the email have an message-id (the real header in fact is the `references`)
							$track_key = trim($item['track_key'] ,'<>');
							$this->db->set('emails_data_id', $emails_data_id);
							$this->db->set('track_key', $status_id);
							$this->db->insert('emails_bounce');

						} elseif ( in_array($status_id, array(3,5,9,12,13)) ) {
							//insert into emails_marked if bounced,invalid,unsubscribed or made trial
							//the emails was sent
							$this->db->set('emails_data_id', $emails_data_id);
							$this->db->set('status_id', $status_id);
							$this->db->insert('emails_marked');
						}
					}
				}
			}
			echo "emails_new_structure ending in key#$last_emails_id";
			$this->set_stored_var('old_emails_import',$last_emails_id);
		}
	}
	function process_blacklist_new_structure() {
		$old_mailserver = $this->load->database('old_mailserver', TRUE);
		$crawl_blacklist_limit = 5000;
		$last_blacklist_id = $this->get_stored_var('old_blacklist_import');
		if ( ! $last_blacklist_id) $last_blacklist_id = 0;
		echo "blacklist_new_structure starting in key#$last_blacklist_id";
		$old_mailserver->where('blacklist_id >',$last_blacklist_id);
		$old_mailserver->limit($crawl_blacklist_limit);
		$old_mailserver->order_by('blacklist_id','asc');
		$query = $old_mailserver->get('blacklist');
		if($query && $query->num_rows > 0) {
			$blacklist_result = $query->result_array();
			$query->free_result();
			$last_blacklist_row = end($blacklist_result);
			$last_blacklist_id = $last_blacklist_row['blacklist_id'];
			reset($blacklist_result);
			$this->load->database('default');
			foreach($blacklist_result as $item) {
				$address = $item['email'];
				$mailing_group_id = $item['mailing_group_id'];
				$status_id = $item['status_id'];
				//check if the address allready exists in emails_address
				$emails_address_id = FALSE;
				$this->db->where('address', $address);
				$query = $this->db->get('emails_address');
				if ($query && $query->num_rows > 0) {
					//exists allready so i will fetch his primary key
					$emails_address_id = $query->row()->emails_address_id;
				} else {
					//inserting the address for the first time
					$this->db->set('address', $address);
					$query = $this->db->insert('emails_address');
					if ($query) $emails_address_id = $this->db->insert_id();
				}
				if ($emails_address_id) {
					$sql = 'INSERT IGNORE INTO emails_blacklist (emails_address_id,mailing_group_id,status_id) VALUES ('.$emails_address_id.','.$mailing_group_id.','.$status_id.')';
					$query = $this->db->simple_query($sql);
				}
			}
			echo "blacklist_new_structure ending in key#$last_blacklist_id";
			$this->set_stored_var('old_blacklist_import',$last_blacklist_id);
		}
	}
	function process_stack_new_structure() {
		$old_mailserver = $this->load->database('old_mailserver', TRUE);
		$crawl_stack_limit = 5000;
		$last_stack_id = $this->get_stored_var('old_stack_import');
		if ( ! $last_stack_id) $last_stack_id = 9973887;
		echo "stack_new_structure starting in key#$last_stack_id";
		$old_mailserver->where('emails_id >',$last_stack_id);
		$old_mailserver->limit($crawl_stack_limit);
		$old_mailserver->order_by('emails_id','asc');
		$query = $old_mailserver->get('emails_stack');
		echo $old_mailserver->last_query();
		if($query && $query->num_rows > 0) {
			$stack_result = $query->result_array();
			$query->free_result();
			$last_stack_row = end($stack_result);
			$last_stack_id = $last_stack_row['emails_id'];
			reset($stack_result);
			$this->load->database('default');
			foreach($stack_result as $item) {
				$webmaster_id = $item['webmaster_id'];
				$niche_id = $item['niche_id'];
				$lang_iso = $item['lang_iso'];
				$address = $item['email'];
				$email_collected_id = $item['email_collected_id'];
				//check if the address allready exists in emails_address
				$emails_address_id = FALSE;
				$this->db->where('address', $address);
				$query = $this->db->get('emails_address');
				if ($query && $query->num_rows > 0) {
					//exists allready so i will fetch his primary key
					$emails_address_id = $query->row()->emails_address_id;
				} else {
					//inserting the address for the first time
					$this->db->set('address', $address);
					$query = $this->db->insert('emails_address');
					if ($query) $emails_address_id = $this->db->insert_id();
				}
				if ($emails_address_id) {
					$sql = 'INSERT IGNORE INTO emails_stack (emails_address_id,email_collected_id,webmaster_id,niche_id,lang_iso) VALUES ('.$emails_address_id.','.$email_collected_id.','.$webmaster_id.','.$niche_id.',\''.$lang_iso.'\')';
					$query = $this->db->simple_query($sql);
					echo $sql;
				}
			}
			echo "stack_new_structure ending in key#$last_stack_id";
			$this->set_stored_var('old_stack_import',$last_stack_id);
		}
	}
	function process_login_new_accounts() {
		$old_mailserver = $this->load->database('old_mailserver', TRUE);
		$crawl_stack_limit = 5000;
		$last_stack_id = $this->get_stored_var('old_logins_import');
		if ( ! $last_stack_id) $last_stack_id = 0;
		echo "login_new_structure starting in key#$last_stack_id";
		$old_mailserver->where('login_id >',$last_stack_id);
		$old_mailserver->limit($crawl_stack_limit);
		$old_mailserver->order_by('login_id','asc');
		$query = $old_mailserver->get('login');
		if($query && $query->num_rows > 0) {
			$stack_result = $query->result_array();
			$query->free_result();
			$last_stack_row = end($stack_result);
			$last_stack_id = $last_stack_row['login_id'];
			reset($stack_result);
			$this->load->database('default');
			$imported = 0;
			foreach($stack_result as $item) {
				$login_id = $item['login_id'];
				//test if it allready exist in the new structure
				$this->db->where('login_id', $login_id);
				$query = $this->db->get('login');
				if ($query && $query->num_rows > 0) continue;
				else {
					$this->db->set($item);
					$this->db->insert('login');
					$imported+= 1;
				}

			}
			echo "imported $imported logins in ".count($stack_result). " in total";
			echo "login_new_structure ending in key#$last_stack_id";
			$this->set_stored_var('old_logins_import',$last_stack_id);
		}
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

	function crawl_redirect($limit = 30) {
		//connects to the old mailserver database
		$old_mailserver = $this->load->database('old_mailserver', TRUE);

		$redirect_fetcher = $this->get_stored_var('redirect_fetcher');
		if ( ! $redirect_fetcher) $redirect_fetcher = 0;
		$old_mailserver->select('redirect_id, emails_id');
		$old_mailserver->where('redirect_id >', $redirect_fetcher);
		$old_mailserver->order_by('redirect_id', 'asc');
		$old_mailserver->limit($limit);
		$query = $old_mailserver->get('redirect');
		if ($query && $query->num_rows > 0 ) {
			$result = $query->result();
			$first_row = end($result);
			$this->set_stored_var('redirect_fetcher', $first_row->redirect_id);
			return $result;
		}
		return FALSE;
	}
}
