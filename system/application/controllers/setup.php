<?php
class Setup extends Controller {

    function Setup(){
         parent::Controller();
         $this->load->helper('array');
		 $this->load->library('log');
         $this->load->library('Send');
         $this->load->library('bounce');
	     $this->load->model('logins_model');
		 $this->load->model('crawler_model');
		 $this->load->model('transition_model');
    }
    function index() {}
	// set an ip active
	// parameter: ip type xxx.xxx.xxx.xxx:string
	function activeip($ip = NULL, $domain = NULL) {
		if ($ip) {
			$result = $this->logins_model->checkip($ip, $domain);
			die($result);
		}
	}
	// get one ip inactive
    function inactiveip() {
		$result = $this->logins_model->uncheckedip();
		if ($result) {
			die($result['ip']);
		}
	}
	function balancers($owner = false){
		if($owner === false ) $owner = $_SERVER['REMOTE_ADDR'];
		//select concat('\nBalancerMember http://',ips.ip,':80 www',@rownum:=@rownum+1) _row_ from available_ips ips, (SELECT @rownum:=0) counter
		$query = $this->emails_model->db->query("select concat('\nBalancerMember http://',ips.ip,':80 route=www',@rownum:=@rownum+1) `data` from available_ips ips, (SELECT @rownum:=0) counter  where owner = '$owner'");
		$result = $query->result_array();
		foreach($result as $value){
			echo $value['data'];
		}
	}
	function proxies($owner = false){
		if($owner === false ) $owner = $_SERVER['SERVER_ADDR'];
		//select CONCAT('\n\racl ip', @rownum:=@rownum+1, ' myip ', ips.ip ,'\n\rtcp_outgoing_address ', ips.ip, ' ip',@rownum) `row` from available_ips ips, (SELECT @rownum:=0) counter
		$query = $this->emails_model->db->query("select CONCAT('\nacl ip', @rownum:=@rownum+1, ' myip ', ips.ip ,'\ntcp_outgoing_address ', ips.ip, ' ip',@rownum) `data` from available_ips ips, (SELECT @rownum:=0) counter  where owner = '$owner'");
		$result = $query->result_array();
		foreach($result as $value){
			echo $value['data'];
		}

	}
	function jsproxies($owner = false){
		if($owner === false ) $owner = $_SERVER['SERVER_ADDR'];
		//select CONCAT('\n\racl ip', @rownum:=@rownum+1, ' myip ', ips.ip ,'\n\rtcp_outgoing_address ', ips.ip, ' ip',@rownum) `row` from available_ips ips, (SELECT @rownum:=0) counter
		$query = $this->emails_model->db->query("select CONCAT('\n\"tc:t0tal@',ips.ip ,':8213\",') `data` from available_ips ips where owner = '$owner'");
		$result = $query->result_array();
		foreach($result as $value){
			echo $value['data'];
		}

	}
	function ips($from = false,$to = false,$country = false,$owner = false){
		preg_match_all( '/\b(?:(?:25[0-5]|2[0-4][0-9]|[01]?[0-9][0-9]?)\.){3}(?:25[0-5]|2[0-4][0-9]|[01]?[0-9][0-9]?)\b/i', file_get_contents('/home/webuser/ipsuteis.csv'), $list_of_ips);
		$owner = '91.121.53.114';
		foreach(current($list_of_ips) as $ip) {
			$this->logins_model->db->set('owner', $owner);
			$this->logins_model->db->set('ip', $ip);
			$this->logins_model->db->insert('available_ips');
			echo $this->logins_model->db->last_query();
		}
	}
	function emails() {
		$data = $this->input->post('data');
		$method = $this->input->post('method');
		$result = array('success' => false);
		if(!$data) {
			$this->load->view('ajax',$result);
			return false;
		}

		$user = $_COOKIE['mailserver_username'];
		$password = $_COOKIE['mailserver_password'];

		if($method != 'base64') $data = json_decode($data,true);
		else $data = json_decode(base64_decode($data),true);

		if(isset($data['trigger'])) $data = array($data);
		foreach($data as $item){
			$item['subject'] = urldecode($item['subject']);
			$item['message'] = urldecode($item['message']);
			$this->emails_model->set($item);
		}

		$result = array('success' => true);
		$this->load->view('ajax',$result);

	}
	function hosts() {
		$json = $this->input->post('data');
		$result = array('success' => false);
		if(!$json) {
			$this->load->view('ajax',$result);
			return false;
		}

		$data = json_decode($json,true);

		foreach($data as $item)
		{
			$id = $this->hosts_model->set($item);
			if(isset($item['logins'])) $logins = array_populate_index($item['logins'],array('host_id' => $id));
			if(isset($item['rules'])) $rules = array_populate_index($item['rules'],array('host_id' => $id));
			if(isset($item['settings'])) $settings = array_populate_index($item['settings'],array('host_id' => $id));

			$this->logins($logins);
			$this->rules($rules);
			$this->settings($settings);
		}

		$result = array('success' => true);
		$this->load->view('ajax',$result);

	}
	function settings($data = false) {
		if(!$data)
		{
			$json = $this->input->post('data');
			$result = array('success' => false);
			if(!$json) {
				$this->load->view('ajax',$result);
				return false;
			}
			$data = json_decode($json,true);
		}

		foreach($data as $item)
		{
			$this->settings_model->set($item);
		}

		$result = array('success' => true);
		$this->load->view('ajax',$result);
	}
	function rules($data = false) {
		if(!$data)
		{
			$json = $this->input->post('data');
			$result = array('success' => false);
			if(!$json) {
				$this->load->view('ajax',$result);
				return false;
			}

			$data = json_decode($json,true);
		}

		foreach($data as $item)
		{
			$this->rules_model->set($item);
		}

		$result = array('success' => true);
		$this->load->view('ajax',$result);

	}
	function logins($data = false) {
		if(!$data)
		{
			$json = $this->input->post('data');
			$result = array('success' => false);
			if(!$json) {
				$this->load->view('ajax',$result);
				return false;
			}

			$data = json_decode($json,true);
		}

		foreach($data as $item)
		{
			$this->logins_model->set($item);

		}

		$result = array('success' => true);
		$this->load->view('ajax',$result);

	}
	function recipients() {
		$json = $this->input->post('data');
		$result = array('success' => false);
		if(!$json) {
			$this->load->view('ajax',$result);
			return false;
		}

		$data = json_decode($json,true);

		foreach($data as $item)
		{
			$this->recipients_model->set($item);
		}

		$result = array('success' => true);
		$this->load->view('ajax',$result);

	}
	function parseips() {

		$db = $this->emails_model->db;

		$db->select('id,ip');
		$query = $db->get('available_ips');

		$result = $query->result_array();
		foreach($result as $value){
			$ip = $value['ip'];


		}

	}
	function export_ips() {
		$db = $this->emails_model->db;

		$db->select('ip,domain');
		$query = $db->get('available_ips');
		$result = $query->result_array();
		$js = "var domains = [];";
		foreach($result as $value){
			$ip = $value['ip'];
			$domain = $value['domain'];
			$js.= "\ndomains.push({ip:'$ip',domains:'$domain'});";
		}
		die($js);
	}
	function range($ip = FALSE,$range = FALSE) {

		$result = array($ip);
		$ip = explode(".",$ip);

		do {
			$ip[3] = $ip[3] + 1;
			$result[] = implode(".",$ip);
			$range=$range-1;

		} while($range > 0);

		return $result;
	}
	function update_countries($ip = FALSE,$range = FALSE,$country = FALSE) {
		$ips = $this->range($ip,$range);
		$db = $this->emails_model->db;
		foreach($ips as $ip) {

			$db->where('ip',$ip);
			$db->update('available_ips',array(
				'country' => $country
			));
		}
	}
	function uncheckedip() {

            $this->load->model('logins_model');
            $row = $this->logins_model->uncheckedip();
            die(implode(",",$row));
        }
	function checkip($ip = FALSE,$state = FALSE) {

            $this->load->model('logins_model');
            if($ip) $this->logins_model->set_ip_state($ip,$state);
            else $this->logins_model->set_ip_state($ip,$state);
            return $ip;

        }
	function redirect_ip_proxy() {
		$db = $this->emails_model->db;
		$db->select('id,ip,domain');
		$db->where('owner','94.23.10.31');
		$db->where('active',0);
		$db->limit(1);
		$query = $db->get('available_ips');
		if ($query && $query->num_rows > 0) {
			$row = $query->row_array();
			$db->set('active',1);
			$db->where('ip',$row['ip']);
			$db->update('available_ips');
			die($row['domain']);
		} else die('false');
	}
	function populate_emails_queue() {
		$this->transition_model->populate_emails_queue();
	}
	function export_accounts() {
		$login = $this->logins_model->random();
		// valid_accounts
		if (empty($login)) return FALSE;
		$settings = $this->settings_model->get(array('host_id' => $login['host_id']));
		//login and host settings to be used in current smtp session
		$params = array(
			'Hostname'   => $login['domain'],
			'SMTPSecure' => $settings['smtp']['service_flags']['value'],
			'Host'       => $settings['smtp']['host']['value'],
			'Port'       => $settings['smtp']['port']['value'],
			'Timeout'    => $settings['smtp']['timeout']['value'],
			'Username'   => $login['login'],
			'Password'   => $login['pass'],
			'From'       => $login['email'],
			'Name'       => $login['name'],
			'Proxy'      => $login['ip'],
			'Mail'       => 'fmenezes.tc@hotmail.com'
		);
		$this->send->smtp_connection = FALSE;
		$result = $this->send->verify($params, array(
			'domain' => $login['domain'],
			'ip'     => $login['ip']
		));
		if ($result) {
			Debug('valid account!');
			$db = $this->logins_model->db;
			$db->set('email', $login['email']);
			$db->set('pass', $login['pass']);
			$db->insert('valid_accounts');
		}
	}
}
