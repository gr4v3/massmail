<?php
/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */
class Office_model extends CI_Model {
	function __construct(){
		parent::__construct();
        $this->load->helper('array');
		$this->load->config('mailserver');
		$this->load->model('setup_model');
	}
	function get_rights($user_id = NULL, $level = NULL) {
		if ( empty($user_id) || empty($level)) return FALSE;
		$this->db->select("*");
		$this->db->where("user_id", $user_id);
		$this->db->or_where("user_id",0);
		$this->db->where("var", $level);
		$query = $this->db->get("user_rights");
		if ($query && $query->num_rows > 0 ) {
			$row = $query->row();
			if (isset($row->value) && $row->value == 1) return TRUE; else return FALSE;
		} else return FALSE;
	}
	function get_num_accounts_created($host_id = NULL, $user_id = NULL) {
		if (empty($host_id) || empty($user_id)) return FALSE;
		$this->db->where('host_id',$host_id);
		$this->db->where('status_id',20);
		$this->db->where('yearweek(created) = yearweek(current_timestamp)');
		$this->db->where('day(created) = day(current_timestamp)');
		$this->db->where('info',$user_id);
		$this->db->from('login');
		return $this->db->count_all_results();
	}
	/********************************************
	*********************************************/
	function block_ip($ip = NULL) {
		if (empty($ip)) return FALSE;
		$this->db->set('access', 'current_timestamp', FALSE);
		$this->db->where('ip', $ip);
		$this->db->update('available_ips');
		// delete from ip_access
		$this->db->where('ip', $ip);
		$this->db->delete('ip_access');
	}
	function get_available_ips($die = TRUE,$host_id = NULL) {
		if (empty($host_id)) $host_id = $this->input->post('server');
		if( ! $host_id) return FALSE;
		$this->db_session->set_userdata('office_server_id',$host_id);
		$owner = $_SERVER['SERVER_ADDR'];
		$email_creator_id = $this->freakauth_light->getUserProperty('id');

		/******************** rules for the host selected ***************************/
		$max_ip_per_day = $this->setup_model->max_ip_per_day($host_id);                                 //int
		$max_accounts_per_ip_per_host = $this->setup_model->max_accounts_per_ip_per_host($host_id);     //int
		$accounts_creation_interval = $this->setup_model->accounts_interval($host_id);                  //unix_timestamp format
		/****************************************************************************/

		// first see if the creator have allready ips reserved

		$this->db->set('user_id',null);
		$this->db->where('available_ips.user_id', $email_creator_id);
		$this->db->where('available_ips.owner', $owner);
		$this->db->where('available_ips.active', 1);
		$this->db->where('TIMESTAMPDIFF(SECOND,available_ips.access,CURRENT_TIMESTAMP) >',$accounts_creation_interval);
		$this->db->update('available_ips');

		$this->db->where('available_ips.user_id',$email_creator_id);
		$this->db->where('available_ips.owner', $owner);
		$this->db->where('available_ips.active', 1);
		$this->db->where('TIMESTAMPDIFF(SECOND,available_ips.access,CURRENT_TIMESTAMP) <',$accounts_creation_interval);
		$query = $this->db->get('available_ips');

		$office_reserved_ips = array();
		if ($query && $query->num_rows() > 0) {
			$result = $query->result();
			foreach ($query->result() as $row) {
				$office_reserved_ips[] = $row->id;
			}
			$office_reserved_condition = " or ( available_ips.id in (".implode(',',$office_reserved_ips).") and TIMESTAMPDIFF(SECOND,available_ips.access,CURRENT_TIMESTAMP) < $accounts_creation_interval ) ";
		} else {
			$office_reserved_condition = " and ( TIMESTAMPDIFF(SECOND,available_ips.access,CURRENT_TIMESTAMP) >= $accounts_creation_interval or available_ips.access is NULL )";
		}

		$sql = "SET @num_ips=0";
		$this->db->simple_query($sql);

		$sql = "SELECT

		available_ips.id,
		available_ips.ip,
		available_ips.tunel,
		SUM(IF(login.login_id is not null, 1, 0)) as nums,
		concat(@num_ips := @num_ips+1,'. ',available_ips.ip,'...',available_ips.country) as text

		from `available_ips`
		left join login on (
			login.ip = available_ips.ip and login.host_id = $host_id
			and login.status_id = 20
			and yearweek(login.created) = yearweek(current_timestamp)
			and day(login.created) = day(current_timestamp)
		)
		where owner = '$owner'
		and active = 1
		$office_reserved_condition
		group by available_ips.ip
		limit $max_ip_per_day";

		$query = $this->db->query($sql);
		if($query->num_rows() == 0) {
			$max_accounts = $this->get_num_accounts_created($host_id,$email_creator_id);
			$this->follow(false,"you have reached the maximum of accounts for today. $max_accounts created.","home");
		}

		$result = $query->result_array();

		$export = array();
		$exclude = array();
		foreach($result as $value){
			if($value['nums'] < $max_accounts_per_ip_per_host) $export[] = $value;
			else $exclude[] = $value;
		}

		if(!empty($export)) {
			$ids = array();
			foreach($export as $value){$ids[] = $value['id'];}
			$ids = implode(',',$ids);
			$this->db->query("update available_ips set user_id = $email_creator_id,access = current_timestamp where id in ($ids)");
		} else {
			$max_accounts = $this->get_num_accounts_created($host_id,$email_creator_id);
			$this->follow(false,"you have reached the maximum of accounts for today. $max_accounts created.","home");
		}

		if($die) die(json_encode($export));
		else return json_encode($export);
	}
	function get_country_from_ip($ip = NULL) {
		if (empty($ip)) return FALSE;
		$this->db->select('country');
		$this->db->where('ip',$ip);
		$query = $this->db->get('available_ips');
		if ($query && $query->num_rows > 0 ) {
			$row = $query->row();
			return $row->country;
		} else return FALSE;
	}
	function get_tunnel_from_ip($ip = NULL) {
		if (empty($ip)) return FALSE;
		$this->db->select('tunel');
		$this->db->where('ip',$ip);
		$query = $this->db->get('available_ips');
		if ($query && $query->num_rows > 0 ) {
			$result = $query->row();
			return $result->tunel;
		} else return FALSE;
	}
	function fetch_previous_ips($user_id = NULL, $owner = NULL, $host_id = NULL) {
		if ( empty($user_id) || empty($owner) || empty($host_id) ) return FALSE;
		$this->db->where('ip_access.user_id', $user_id);
		$this->db->where('ip_access.host_id', $host_id);
		$this->db->where('ip_access.blocked', 0);
		$this->db->where('available_ips.owner', $owner);
		$this->db->where('available_ips.active', 1);
		$this->db->join('available_ips','available_ips.ip = ip_access.ip','inner');
		$query = $this->db->get('ip_access');
		if ($query && $query->num_rows > 0 ) return $query->result(); else return FALSE;
	}
	function count_accounts_per_ip($user_id = NULL,$ip = NULL, $host_id = NULL) {
		if ( empty($user_id) || empty($ip) || empty($host_id) ) return FALSE;
		$this->db->where('user_id',$user_id);
		$this->db->where('host_id', $host_id);
		$this->db->where('ip', $ip);
		$query = $this->db->get('ip_access');
		if ($query && $query->num_rows > 0 ) {
			$row = $query->row();
			return $row->counter;
		} else return FALSE;
	}
	function fetch_new_ips($user_id = NULL, $owner = NULL, $host_id = NULL) {
		if (empty($user_id) || empty($owner) || empty($host_id)) return FALSE;
		$max_accounts_per_host = $this->setup_model->max_accounts_per_host($host_id);
		$max_ip_per_host = $this->setup_model->max_ip_per_host($host_id);
		$accounts_creation_interval = $this->setup_model->accounts_interval($host_id);
		$this->log->write_log('OFFICE', '**************************');
		$this->log->write_log('OFFICE', '            host_id:' . $host_id);
		$this->log->write_log('OFFICE', 'max_accounts_per_ip:' . $max_accounts_per_host);
		$this->log->write_log('OFFICE', '    max_ips_per_day:' . $max_ip_per_host);
		$this->release_reversed_ip($user_id, $max_accounts_per_host);
		$this->reserve_ips_by_country($user_id, $owner, $max_ip_per_host, $accounts_creation_interval);
		$select = array(
			'available_ips.ip',
			'available_ips.country',
			'ip_access.user_id',
			'available_ips.ip as text',
			'ip_access.counter'
		);
		$this->db->select(implode(",",$select),FALSE);
		$this->db->where('available_ips.active', 1);
		$this->db->where('available_ips.status_id', 1);
		$this->db->where('ip_access.user_id', $user_id);
		$this->db->join('available_ips','ip_access.ip = available_ips.ip','inner');
		$query = $this->db->get('ip_access');
		if ($query && $query->num_rows() > 0 ) {
			$result = $query->result_array();
			//{"ip_access_id":"1","ip":"46.105.185.193","user_id":"4","datelock":null,"host_id":null,"counter":"0","blocked":"0"}
			//{"ip":"178.33.138.254","country":"BE","owner":"94.23.75.102","user_id":"4","text":"178.33.138.254...BE done"}
			foreach($result as &$row) {
				$row['text'] = $row['country'] . ' => ' . $row['ip'] . ' => ' . $row['counter'];
				$row['host_id'] = $host_id;
			}
			return json_encode($result);
		} else return json_encode(array());
	}
	function release_reversed_ip($user_id = NULL, $ip_accounts_limit = NULL) {
		if (empty($user_id) || empty($ip_accounts_limit)) return FALSE;
		$this->db->where('user_id', $user_id);
		$this->db->where('counter', $ip_accounts_limit);
		$this->db->limit(1);
		$query = $this->db->get('ip_access');
		if ($query && $query->num_rows() > 0 ) {
			$row = $query->row();
			// set in available_ips the date of the last account created
			$this->db->set('access', 'current_timestamp', FALSE);
			$this->db->where('ip', $row->ip);
			$this->db->update('available_ips');
			// delete from ip_access
			$this->db->where('ip', $row->ip);
			$this->db->delete('ip_access');
			return TRUE;
		} else return FALSE;
	}
	function reserve_ips_by_country($user_id = NULL,$owner = NULL, $max_ip_per_day = NULL, $accounts_creation_interval = NULL) {
		if (empty($user_id) || empty($owner) || empty($max_ip_per_day) || empty($accounts_creation_interval)) return FALSE;
		$limit = $max_ip_per_day;
		$this->db->where('user_id', $user_id);
		$query = $this->db->get('ip_access');
		if ($query && $query->num_rows() > 0) {
			$limit = $max_ip_per_day - $query->num_rows;
		}
		$this->log->write_log('OFFICE', '    limit:' . $limit);
		if ($limit > 0 ) {
			// get the number of ips to reach the limit of ips per day
			$this->db->select('ip,country,owner');
			$this->db->where('owner', $owner);
			$this->db->where('active', 1);
			$this->db->where('status_id', 1);
			$this->db->where('DATEDIFF(CURRENT_TIMESTAMP,access) >', $accounts_creation_interval, FALSE);
			$this->db->order_by('access','asc');
			//$this->db->group_by('country');
			$this->db->limit($limit);
			$query = $this->db->get('available_ips');
			$this->log->write_log('OFFICE', $this->db->last_query());
			if ($query && $query->num_rows() > 0 ) {
				$result = $query->result();
				// insert in ip_access for the user_id to use
				foreach($result as $row) {
					// set current date in access to avoid being pick again
					$this->db->set('access', 'current_timestamp', FALSE);
					$this->db->where('ip', $row->ip);
					$this->db->update('available_ips');
					// now pick the ip and `move` it to ip_access
					$this->db->set('user_id', $user_id);
					$this->db->set('ip', $row->ip);
					// $this->db->set('host_id', $host_id); // temporary.
					$this->db->insert('ip_access');
				}
			}
		}
	}
	function increase_account_counter($email_creator = NULL, $ip = NULL) {
		if (empty($email_creator) || empty($ip)) return FALSE;
		$this->db->set('counter','counter + 1', FALSE);
		$this->db->where('user_id',$email_creator);
		$this->db->where('ip',$ip);
		return $this->db->update('ip_access');
	}
	/******************************************
	******************************************/
	function get_available_hosts($user_id = NULL) {
		if (empty($user_id)) return FALSE;
		$this->db->select($user_id.', COUNT( login.login_id ) AS done, host.host_id AS host_id', FALSE);
		$this->db->join('login', 'host.host_id = login.host_id AND `info` = '.$user_id.' AND MONTH( login.created ) = MONTH( CURRENT_TIMESTAMP ) AND YEAR(login.created) = YEAR(CURRENT_TIMESTAMP)', 'left');
		$this->db->where('host.status_id', 1);
		//$this->db->where('MONTH( login.created ) = MONTH( CURRENT_TIMESTAMP )', FALSE, FALSE);
		$this->db->group_by('host_id');
		$query = $this->db->get('host');
		//Debug($this->db->last_query());
		if ($query && $query->num_rows > 0 ) {
			$result = $query->result_array();
			return $result;
		} else return FALSE;
	}
	/********************************************
	*********************************************/
}