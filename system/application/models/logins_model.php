<?php
/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */
class Logins_model extends CI_model {
	var $table = 'login';
	var $noip = FALSE;  // used to fetch accounts by his ip owner or not
	function __construct() {
		parent::__construct();
        $this->load->helper('array');
		$this->load->config('mailserver');
		$this->load->model('rules_model');
		$this->load->model('hosts_model');
		$this->noip = FALSE;
	}
	function anyone() {
		$ip = $_SERVER['SERVER_ADDR'];
		$result = $this->get(array(
			'ip' => $ip,
			'status_id' => 1
		));
		return $result;
	}
	function set($params = NULL) {
		if (empty($params)) return FALSE;
		if (!empty($params->login_id)) {
			$login_id = $params->login_id;
			unset($params->login_id);
			$params->modified = date('Y-m-d H:i:s',mktime());
			$this->db->where('login_id', $login_id);
			$this->db->update('login',$params);
		} else {
			$params->created = date('Y-m-d H:i:s',mktime());
			$this->db->insert('login',$params);
		}
	}
	function get_one($login_id = FALSE) {
		$active_hosts = $this->fetch_active_hosts();
		$select = array(
			"login.*",
			"host.hostname as servername"
		);
		$this->db->select(implode(",",$select),FALSE);
		$this->db->join('host','host.host_id = login.host_id','inner');
		if ($login_id) $this->db->where('login.login_id',$login_id);
		else {
			$this->db->join('available_ips','login.ip = available_ips.ip','inner');
			$this->db->where('available_ips.status_id',1);
			$this->db->where('available_ips.active',1);
			$this->db->where('available_ips.owner',$_SERVER['SERVER_ADDR']);
		}
		if ($active_hosts) $this->db->where_in('login.host_id',$active_hosts);
		$query = $this->db->get('login');
		if ($query) {
			if($query->num_rows > 1) {
				return $query->result();
			} elseif ($query->num_rows == 1) {
				return $query->row();
			} else return false;
		} else return false;
	}
	function get_login($filter = NULL,$count = FALSE) {
		if (empty($filter)) return FALSE;
		$this->db->where($filter);
		$query = $this->db->get('login');
		if ($query) {
			if ($count) return $query->num_rows();
			if ($query->num_rows > 0) {
				return $query->result();
			} return FALSE;
		} else return FALSE;
	}
	function getloginforbounce($host_id = NULL, $login_id = NULL, $type = 'get') {
		//get only a single predefined account
		if ( ! empty($login_id) || ! empty($host_id)) {
			$select = array(
				'login.*',
				'queue.queue_id',
				'queue.type'
			);
			$this->db->select(implode(",",$select),FALSE);
			$this->db->join('rules', 'rules.host_id = login.host_id', 'inner');
			$this->db->join('queue', 'queue.login_id = login.login_id AND queue.type = \'' . $type . '\' AND queue.status_id = 1', 'inner');
			if ($login_id) $this->db->where('login.login_id', $login_id);
			if ($host_id) $this->db->where('login.host_id', $host_id);
			$this->db->where('login.status_id', 1);
			$query = $this->db->get('login');
			if ($query && $query->num_rows > 0 ) {
				$this->log->write_log('BOUNCE','a specific account was fetched.');
				$login = $query->row();
				$login->type = 'get';
				$host = $this->hosts_model->get($login->host_id);
				$selected_account = (object) array_merge((array) $login, (array) $host);
				return $selected_account;
			}
		}
		//now lets go to the normal accounts resident in mailingx(1|2|3)
		$active_hosts = $this->fetch_active_hosts();
		$selected_account = FALSE;
		foreach($active_hosts as $host_id) {
			//get then the login to fetch again for bounces
			$select = array(
				'login.*',
				'queue.queue_id',
				'rules.*'
			);
			$this->db->select(implode(",",$select),FALSE);
			$this->db->join('rules', 'rules.host_id = login.host_id', 'inner');
			$this->db->join('available_ips', 'login.ip = available_ips.ip', 'inner');
			$this->db->join('queue', 'queue.login_id = login.login_id AND queue.type = \'' . $type . '\' AND queue.status_id = 1', 'inner');
			$this->db->where('TIMESTAMPDIFF(SECOND,queue.access,CURRENT_TIMESTAMP) > rules.bounce_interval', FALSE, FALSE);
			$this->db->where('login.host_id', $host_id);
			$this->db->where('login.status_id', 1);
			$this->db->where('available_ips.owner', $_SERVER['SERVER_ADDR']);
			$this->db->limit(1);
			$query = $this->db->get('login');
			if ($query && $query->num_rows > 0 ) {
				$this->log->write_log('BOUNCE','account fetched by queue.');
				$login = $query->row();
				$login->type = 'get';
				$host = $this->hosts_model->get($login->host_id);
				$selected_account = (object) array_merge((array) $login, (array) $host);
				return $selected_account;
				break;
			}
		}
		return $selected_account;
	}
	// methods to fetch an random accounbt each time the process control::execute is run
	// get_login_still_available -> get the account that have already been used but not reached his sending limit per day
	// get_login_by_rate         -> get the account with the best send/click rate and with the lower percentagem of bounce failure
	// get_login_by_queue        -> simply get the account by his sending queue interval
	function get_login_still_available() {
		$active_hosts = $this->fetch_active_hosts();
		$selected_ip = $this->get_next_country(); // indexes: ip,domain
		$result = FALSE;
		$select = array(
			'login.login_id',
			'login.name',
			'login.email',
			'login.login',
			'login.pass',
			'login.host_id',
			'login.ip',
			'queue.queue_id',
			'queue.`type`',
			'send_limit - mails_sent as send_limit'
		);
		$select_sql_expression = implode(',', $select);
		foreach($active_hosts as $active_host_id) {
			$this->db->select($select_sql_expression,FALSE);
			$this->db->join('rules', 'rules.host_id = login.host_id', 'inner');
			$this->db->join('queue', 'queue.login_id = login.login_id AND queue.type = \'send\' AND queue.status_id = 1', 'inner');
			$this->db->join('account_rate', 'account_rate.login_id = login.login_id', 'inner');
			$this->db->where('TIMESTAMPDIFF(SECOND,queue.access,CURRENT_TIMESTAMP) < rules.send_interval');
			$this->db->where('login.mails_sent > 0');
			$this->db->where('login.mails_sent < rules.send_limit');
			$this->db->where('login.status_id', 1);
			//i only need one at a time
			$this->db->where('account_rate.clicks IS NOT NULL');
			$this->db->where('(account_rate.clicks / account_rate.send_success ) > 0.5');
			if ( ! $this->noip) $this->db->where('login.ip',$selected_ip->ip);
			$this->db->where('login.host_id',$active_host_id);
			$this->db->limit(1);
			$query = $this->db->get('login');
			$result = FALSE;
			if($query && $query->num_rows() > 0) {
				$this->log->write_log('INFO','country ' . $selected_ip->country . ' selected');
				$result = $query->row();
				$result->ip = $selected_ip->ip;
				$result->domain = $selected_ip->domain;
				break;
			}
		}
		return $result;
	}
	function get_login_by_rate() {
		/*
		$active_hosts = $this->fetch_active_hosts();
		$selected_ip = $this->get_next_country(); // indexes: ip,domain
		$result = FALSE;
		$select = array(
			'login.login_id',
			'login.name',
			'login.email',
			'login.login',
			'login.pass',
			'login.host_id',
			'login.ip',
			'queue.queue_id',
			'queue.`type`',
			'mails_sent'
		);
		$select_sql_expression = implode(',', $select);
		foreach($active_hosts as $active_host_id) {
			$this->db->select($select_sql_expression, FALSE);
			$this->db->join('queue', 'queue.login_id = login.login_id AND queue.type = \'send\' AND queue.status_id = 1', 'inner');
			$this->db->join('rules', 'rules.host_id = login.host_id', 'inner');
			$this->db->join('account_rate', 'login.login_id = account_rate.login_id', 'inner');
			$this->db->where('TIMESTAMPDIFF(SECOND,queue.access,CURRENT_TIMESTAMP) > rules.send_interval');
			$this->db->where('login.status_id',1);
			$this->db->where('account_rate.clicks > account_rate.max_send', FALSE, FALSE);
			$this->db->where('account_rate.bounce_success > account_rate.bounce_fail', FALSE, FALSE);
			if ( ! $this->noip) $this->db->where('login.ip', $selected_ip->ip);
			$this->db->where('login.host_id',$active_host_id);
			//i only need one at a time
			$this->db->limit(1);
			$query = $this->db->get('login');
			$result = FALSE;
			if($query && $query->num_rows() > 0) {
				$result = $query->row();
				$result->domain = $selected_ip->domain;
				break;
			}
		}
		return $result;
		*/
		$result = FALSE;
		$login = $this->get_login_by_queue();
		// check if he exists in account_rate
		if ($login) {
			$this->db->where('login_id', $login->login_id);
			$query = $this->db->get('account_rate');
			if ($query && $query->num_rows > 0 ) {

			}
		}
		return $result;
	}
	function get_login_by_queue() {
		$active_hosts = $this->fetch_active_hosts();
		$selected_ip = $this->get_next_country(); // indexes: ip,domain
		$result = FALSE;
		$select = array(
			'login.login_id',
			'login.name',
			'login.email',
			'login.login',
			'login.pass',
			'login.host_id',
			'login.ip',
			'queue.queue_id',
			'queue.`type`',
			'mails_sent'
		);
		$select_sql_expression = implode(',', $select);
		foreach($active_hosts as $active_host_id) {
			$this->db->select($select_sql_expression, FALSE);
			$this->db->join('queue', 'queue.login_id = login.login_id AND queue.type = \'send\' AND queue.status_id = 1', 'inner');
			$this->db->join('rules', 'rules.host_id = login.host_id', 'inner');
			$this->db->where('TIMESTAMPDIFF(SECOND,queue.access,CURRENT_TIMESTAMP) > rules.send_interval');
			$this->db->where('login.status_id',1);
			if ( ! $this->noip) $this->db->where('login.ip', $selected_ip->ip);
			$this->db->where('login.host_id',$active_host_id);
			//i only need one at a time
			$this->db->limit(1);
			$query = $this->db->get('login');
                       
			$result = FALSE;
			if($query && $query->num_rows() > 0) {
				$result = $query->row();
				$result->domain = $selected_ip->domain;
				break;
			}
		}
		return $result;
	}
	// end of methods that fetches the accounts to send emails.
	function get_login_by_email($email = FALSE) {
		$this->db->where('email', $email);
		$query = $this->db->get('login');
		if ($query && $query->num_rows > 0 ) {
			return $query->row();
		} else return FALSE;
	}
	function fetch_active_hosts($host_id = NULL) {
		$active_hosts = array();
		$this->db->select('host_id');
		if ($host_id) $this->db->where('host_id', $host_id);
		$this->db->where('status_id',1);
		$query = $this->db->get('host');
		if ($query && $query->num_rows > 0 ) {
			foreach ($query->result() as $row) {
				$active_hosts[] = $row->host_id;
			}
			return $active_hosts;
		} else return FALSE;
	}
	function host_rules($host_id = NULL) {
		if (empty($host_id)) return FALSE;
		$select = array(
			'send_interval',
			'send_limit',
			'flood_refresh',
			'flood_interval',
			'flood_sleep'
		);
		$this->db->select(implode(",",$select),FALSE);
		$this->db->where('status_id', 1);
		$this->db->where('host_id', $host_id);
		$query = $this->db->get('rules');
		if ($query && $query->num_rows > 0 ) return $query->row();
		else return FALSE;
	}
        function random($noip = FALSE) {
		$this->noip = $noip;
		$result = $this->get_login_by_rate();
		if ($result) {
			$this->log->write_log('INFO',' login fetched by the best rate found.');
		} else {
			$result = $this->get_login_by_queue();
			if ($result) {
				$this->log->write_log('INFO',' login fetched by the queue procedure.');
			} else {
				$result = $this->get_login_still_available();
				if ($result) $this->log->write_log('INFO',' using again this account cause it didnt not reach his limits yet for today.');
			}
		}
		if ($result) {
			$result->type = 'send';
			$host_rules = $this->host_rules($result->host_id);
			return (object) array_merge((array) $result, (array) $host_rules);
		} else return FALSE;

	}
	function login($login_id = NULL) {
		if (empty($login_id)) return FALSE;
		$this->db->where('login_id',$login_id);
		$query = $this->db->get('login');
		if ($query && $query->num_rows > 0 ) return $query->row(); else return FALSE;
	}
	function free($login_id = NULL) {
		if( empty($login_id)) return FALSE;
		$this->db->set('status_id',1);
		$this->db->where('login_id',$login_id);
		return $this->db->update('login');
	}
	// get names for the office control
	// parameter:0 means first name
	// parameter:1 means last name
	function random_name($type = 0) {
		$this->db->select('value');
		$this->db->where('type',$type);
		$query = $this->db->get('names');
		if ($query && $query->num_rows > 0 ) {
			$result = $query->result();
			$result_index = array_rand($result,1);
			$random_name = $result[$result_index]->value;
			return $random_name;
		} else return FALSE;
	}
	function accounts_created($host_id = NULL,$user_id = NULL) {
		if ( empty($host_id) || empty($user_id)) return FALSE;
		$this->db->select('count(login_id) as num',FALSE);
		$this->db->where('info',$user_id);
		$this->db->where('host_id',$host_id);
		$this->db->where('created > curdate()');
		$query = $this->db->get('login');
		if ($query && $query->num_rows > 0 ) {
			$row = $query->row();
			return $row->num;
		} else return FALSE;
	}
	function get_blocked_logins($host_id = NULL) {
		if (empty($host_id)) return FALSE;
		$this->db->select('login.*,domain');
		$this->db->join('available_ips','available_ips.ip = login.ip','inner');
		$this->db->join('errors','errors.row = login.login_id','inner');
		$this->db->where('login.host_id',$host_id);
		$this->db->where('login.status_id',9);
		$this->db->where('available_ips.owner',$_SERVER['SERVER_ADDR']);
		$this->db->where('errors.table','login');
		$this->db->where('day(errors.created) < day(current_timestamp)');
		$this->db->where("(
		`errors`.`value` LIKE  '%Need to authenticate via SMTP-AUTH-Login%'
		OR  `errors`.`value` LIKE  '%User is over the limit for messages allowed to be sent in a single day%'
		OR  `errors`.`value` LIKE  '%The following From address failed%'
		)");
		$query = $this->db->get('login');
		if ($query && $query->num_rows > 0 ) return $query->result(); else return FALSE;
	}
	function mails_sent($login_id = NULL,$mails_sent = 0) {
		if (empty($login_id)) return FALSE;
		$this->db->where('login_id',$login_id);
		if($mails_sent == 0) $this->db->set('mails_sent',0);
		else $this->db->set('mails_sent',"mails_sent + $mails_sent",FALSE);
		if ($this->db->update('login')) return $this->get_mails_sent($login_id);
	}
	function get_mails_sent($login_id = NULL) {
		if (empty($login_id)) return FALSE;
		$this->db->select('mails_sent');
		$this->db->where('login_id',$login_id);
		$query = $this->db->get('login');
		if($query && $query->num_rows > 0) {
			$row = $query->row();
			return $row->mails_sent;
		} else return 0;
	}
	function set_ip_state($ip = NULL, $state = NULL) {
		if ( empty($ip) || empty($state) ) return FALSE;
		$this->db->where('ip',$ip);
		$this->db->set('active',$state);
		return $this->db->update('available_ips');
	}
	function get_accounts_to_verify($host_id = NULL, $limit = 100, $status_id = 20) {
		$this->db->select('login.*,available_ips.domain,available_ips.ip',FALSE);
		$this->db->join('available_ips','available_ips.ip = login.ip','inner');
		$this->db->join('host','login.host_id = host.host_id','inner');
		if ( ! empty($host_id)) $this->db->where('login.host_id',$host_id);
		$this->db->where('login.status_id', $status_id);
		$this->db->where('host.status_id', 1);
		//$this->db->where('login.status_id', 51); //eurico status
		$this->db->where('owner',$_SERVER['SERVER_ADDR']);
		$this->db->where('available_ips.active', 1);
		$this->db->where('available_ips.status_id', 1);
		$this->db->limit($limit);
		$query = $this->db->get('login');
		if ($query && $query->num_rows > 0 ) return $query->result(); else return FALSE;
	}
	// this method will create an associative array with the table_key of the first row of each country
	// the data is passed by parameter
	function next_country_select($last_country_fetched = NULL) {
		$new_country_fetched = NULL;
		$this->db->select('id,country');
		$this->db->where('owner',$_SERVER['SERVER_ADDR']);
		$this->db->where('active',1);
		$this->db->where('status_id',1);
		$this->db->group_by('country');
		$this->db->order_by('id','asc');
		$query = $this->db->get('available_ips');
		if ($query && $query->num_rows > 0 ) {
			$result = $query->result();
			$new_country_fetched = current($result);
			foreach($result as $row) {
				if ($row->id > $last_country_fetched) {
					$new_country_fetched = $row;
					break;
				}
			}
			return $new_country_fetched;
		}
		return $new_country_fetched;
	}
	function get_next_country() {
		$config_params		  = $this->config->item('params');
		$country_crawler_host = $config_params['country_crawler_host'];
		// default values to return in case of major fail
		$return_ip = (object) $config_params['ip_crawler_default'];
		// detect if some ip is allready being used
		$this->db->set('status_id',1);
		$this->db->where('status_id',5);
		$this->db->where('active',1);
		$this->db->where('owner',$_SERVER['SERVER_ADDR']);
		$this->db->update('available_ips');
		// get last country fetched and step to the next one
		$last_country_fetched = $this->get_stored_var($country_crawler_host);
		if (!$last_country_fetched) $last_country_fetched = 0;
		// get next country
		$next_country = $this->next_country_select($last_country_fetched);
		if ($next_country) {
			$this->set_stored_var($country_crawler_host,$next_country->id);
			// now it will select a random ip owned by the selected country and owner
			$this->db->where('owner', $_SERVER['SERVER_ADDR']);
			$this->db->where('active', 1);
			$this->db->where('status_id', 1);
			$this->db->where('country', $next_country->country);
			$query = $this->db->get('available_ips');
			if ($query && $query->num_rows > 0 ) {
				// fetch the random ip in the selected country range
				$result = $query->result();
				$random_index_selected = array_rand($result);
				$selected_row = $result[$random_index_selected];
				$this->db->set('status_id',5);
				$this->db->where('id',$selected_row->id);
				$this->db->update('available_ips');
				$return_ip = new stdClass;
				$return_ip->ip = $selected_row->ip;
				$return_ip->domain = $selected_row->domain;
				$return_ip->country = $selected_row->country;
			}
		}
		return $return_ip;
	}
	/*************** COMMON METHODS *******************/
	function lock($login_id = NULL,$custom = false) {
		if (empty($login_id)) return FALSE;
		if($custom) $status_id = $custom; else $status_id = 2;
		$this->db->set('status_id', $status_id);
		$this->db->where('login_id', $login_id);
		$this->db->update('login');
	}
	function unlock($login_id = NULL) {
		if (empty($login_id)) return FALSE;
		$this->db->set('status_id', 1);
		$this->db->where('login_id', $login_id);
		$this->db->update('login');
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