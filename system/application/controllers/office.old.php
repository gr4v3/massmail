<?php
class Office extends CI_Controller {
	function __construct(){
		parent::__construct();
		$this->load->config('freakauth_light');
		$this->load->library('log');
		$this->load->model('hosts_model');
		$this->load->model('logins_model');
		$this->load->model('settings_model');
		$this->load->model('rules_model');
		$this->load->model('emails_model');
		$this->load->model('office_model');
		$this->load->helper('form');
		$this->load->helper('url');
		$this->load->helper('array');
		$this->load->helper('freakauth_light');
		$this->load->library('form_validation');
		$this->load->library('Freakauth_light');
		$this->load->language('form_validation');
	}
	function index() {
		$this->freakauth_light->check();
		$lang = $this->_get_browser_langs();

		$this->load->library('TCTemplate');
		$this->tctemplate->set_template('templates/default');
		$this->tctemplate->include_js_file('mootools_core.js?'.mktime());
		$this->tctemplate->include_js_file('mootools_more.js?'.mktime());
		$this->tctemplate->include_js_code("window.SITELANG = '".$lang[0]."';",'domready');
		$this->tctemplate->include_js_file('MessageClass/mootools-Dependencies.js?'.mktime());
		$this->tctemplate->include_js_file('MessageClass/message.js?'.mktime());
		$this->tctemplate->include_js_file('office_rules.js?'.mktime());
		$this->tctemplate->include_js_file('office.js?'.mktime());
		$this->tctemplate->include_css_file('index.css?'.mktime());
		$this->tctemplate->include_css_file('win.css?'.mktime());
		$this->tctemplate->include_css_file('office.css?'.mktime());
		$this->tctemplate->include_css_file('message.css?'.mktime());
		$this->tctemplate->set_view_folder('office');
		$this->tctemplate->show('Index', 'index');
	}
	function _get_browser_langs() {
		$browser_langs = $this->input->server('HTTP_ACCEPT_LANGUAGE');
		if ( !$browser_langs ) return FALSE;
		$browser_langs = explode(',',$browser_langs);
		if (empty($browser_langs)) return FALSE;
		foreach ($browser_langs as $index => $blang) {
			$limiter = strpos($blang, ';');
			if ($limiter) $browser_langs[$index] = substr($blang, 0, $limiter);
		}
		return $browser_langs;
	}
	function input(){
		$task = $this->input->post('task');

		if($this->get_rights($task) == FALSE) $this->follow(false,'access denied.'.$task,'home');
		else if(method_exists('Office',$task)) $this->{$task}();
		else $this->follow();
	}
	function export($die = true){
		$table = $this->input->post('table');
		$query = $this->db->get($table);

		$result = false;
		if ($query->num_rows() == 1) $result = json_encode($query->row_array());
		else if ($query->num_rows() > 1) $result = json_encode($query->result_array());
		else return false;

		if($die) die($result); else return $result;
	}
	function follow($data = '',$info = '',$task = false){
		$task = $task?$task:$this->input->post('task');
		$follow = $this->input->post('follow');
		$noundo = $this->input->post('noundo');

		$link = array(
			'task' => $task,
			'data' => $data,
			'info' => $info,
			'follow' => $follow,
			'noundo' => $noundo
		);

		if(!$follow or $follow == 'undefined') unset($link['follow']);
		if(!$task or $task == 'undefined') $link['task'] = 'home';

		die(json_encode($link));
	}

	/**************************************** BEGIN SERVER **********************************/
	function new_server(){
		$this->db_session->set_userdata('office_server_id','');
		if($this->input->post('server'))
		{

			$host_id = $this->input->post('server');
			$host = $this->hosts_model->get(array('host_id' => $host_id),array('host' => array('hostname' => 'hostname','host_id' => 'host_id')));
			$settings = $this->settings_model->get(array('host_id' => $host_id));
			$rules = $this->rules_model->get(array('host_id' => $host_id));
			$accounts_rules = $this->setup_model->accounts_rules($host_id);
			$result = array(
				'host' => $host,
				'settings' => $settings,
				'rules' => $rules,
				'accounts_rules' => $accounts_rules
			);
			$this->db_session->set_userdata('office_server_id',$host_id);
			$this->follow($result);
		}
		$this->follow();
	}
	function active_servers(){
		$user_id = $this->freakauth_light->getUserProperty('id');
		if(!$user_id) $this->follow(false,'Your session has expired. Please login again. ','home');
		/*
		$current_month = $this->logins_model->get_login(array(
			'info' => $user_id,
			'YEAR(created) = YEAR(current_timestamp)' => NULL,
			'MONTH(created) = MONTH(current_timestamp)' => NULL
		),TRUE);
		$max_accounts = $this->office_model->max_accounts_per_month();

		if($current_month >= $max_accounts) {
			$this->follow(false,"you have reached the objective for this month. $max_accounts created.","home");
		} else {
			$data = $this->get_servers(false);
			$this->follow($data);
		}
		 *
		*/
		$data = $this->get_servers(false);
		$this->follow($data);
	}
	function add_server(){
		$id = $this->input->post('host_id');
		$name = $this->input->post('hostname');
		if(!$name) $this->follow(false,'No host name provided.','new_server');

		$db = $this->hosts_model->db;

		$ACCOUNTS_HOST = $this->input->post('ACCOUNTS_HOST');
		$ACCOUNTS_IP_HOST = $this->input->post('ACCOUNTS_IP_HOST');
		$ACCOUNTS_IP = $this->input->post('ACCOUNTS_IP');
		$ACCOUNTS_INTERVAL = $this->input->post('ACCOUNTS_INTERVAL');

		$query = "INSERT INTO setup (var, value, host_id) VALUES ('ACCOUNTS_HOST', ?, ?)";
		$query .= " ON DUPLICATE KEY UPDATE var = 'ACCOUNTS_HOST', value = ?";
		$db->query($query, array($ACCOUNTS_HOST, $id, $ACCOUNTS_HOST));

		$query = "INSERT INTO setup (var, value, host_id) VALUES ('ACCOUNTS_IP_HOST', ?, ?)";
		$query .= " ON DUPLICATE KEY UPDATE var = 'ACCOUNTS_IP_HOST', value = ?";
		$db->query($query, array($ACCOUNTS_IP_HOST, $id, $ACCOUNTS_IP_HOST));

		$query = "INSERT INTO setup (var, value, host_id) VALUES ('ACCOUNTS_IP', ?, ?)";
		$query .= " ON DUPLICATE KEY UPDATE var = 'ACCOUNTS_IP', value = ?";
		$db->query($query, array($ACCOUNTS_IP, $id, $ACCOUNTS_IP));

		$query = "INSERT INTO setup (var, value, host_id) VALUES ('ACCOUNTS_INTERVAL', ?, ?)";
		$query .= " ON DUPLICATE KEY UPDATE var = 'ACCOUNTS_INTERVAL', value = ?";
		$db->query($query, array($ACCOUNTS_INTERVAL, $id, $ACCOUNTS_INTERVAL));


		if(!empty($id)) $this->update_server();

		$host_id = $this->hosts_model->set(array('hostname' => $name));

		$export = $this->input->post('export');
		$import = $this->input->post('import');

		$this->db_session->set_userdata('office_server_id', $host_id);
		$this->db_session->set_userdata('office_server_name', $name);

		$this->settings_model->set(array(
			'host' => $export['host'],
			'type' => 'smtp',
			'port' => $export['port'],
			'timeout' => $export['timeout'],
			'service_flags' => $export['service_flags'],
			'headers' => $export['headers'],
			'host_id' => $host_id
		));

		$this->settings_model->set(array(
			'host' => $import['host'],
			'type' => $import['type'],
			'port' => $import['port'],
			'service_flags' => $import['service_flags'],
			'inbox' => $import['inbox'],
			'timeout' => $import['timeout'],
			'host_id' => $host_id
		));

		$this->rules_model->set(array(
			'host_id' => $host_id,
			'flood_refresh' => $this->input->post('flood_refresh'),
			'flood_interval' => $this->input->post('flood_interval'),
			'flood_sleep' => $this->input->post('flood_sleep'),
			//'throttler_amount' => $this->input->post('throttler_amount'),
			//'throttler_mode' => $this->input->post('throttler_mode'),
			'bounce_interval' => $this->input->post('bounce_interval'),
			'send_interval' => $this->input->post('send_interval'),
			'send_limit' => $this->input->post('send_limit'),
			'country' => $this->input->post('country')
		));

		$this->follow(false,'new mail server added.','home');
	}
	function update_server(){
		$name = $this->input->post('hostname');
		$host_id = $this->input->post('host_id');

		$export = $this->input->post('export');
		$import = $this->input->post('import');

		$this->hosts_model->set(array(
			'hostname' => $name,
			'host_id' => $host_id
		));

		$this->settings_model->update(array(
			'host' => $export['host'],
			'type' => 'smtp',
			'port' => $export['port'],
			'timeout' => $export['timeout'],
			'service_flags' => $export['service_flags'],
			'handler' => $export['handler'],
			'headers' => $export['headers'],
			'host_id' => $host_id
		));
		$this->settings_model->update(array(
			'host' => $import['host'],
			'type' => $import['type'],
			'port' => $import['port'],
			'service_flags' => $import['service_flags'],
			'inbox' => $import['inbox'],
			'timeout' => $import['timeout'],
			'host_id' => $host_id
		));

		$this->rules_model->update(array(
			'host_id' => $host_id,
			'flood_refresh' => $this->input->post('flood_refresh'),
			'flood_interval' => $this->input->post('flood_interval'),
			'flood_sleep' => $this->input->post('flood_sleep'),
			//'throttler_amount' => $this->input->post('throttler_amount'),
			//'throttler_mode' => $this->input->post('throttler_mode'),
			'bounce_interval' => $this->input->post('bounce_interval'),
			'send_interval' => $this->input->post('send_interval'),
			'send_limit' => $this->input->post('send_limit'),
			'country' => $this->input->post('country')
		));

		$this->follow(false,'mail server updated.','home');
	}
	function del_server(){
		if($this->input->post('server'))
		{
			$this->hosts_model->lock($this->input->post('server'));
		}
		$this->follow(false,'mail server deleted.','home');
	}
	/**************************************** END SERVER **********************************/


	/**************************************** BEGIN ACCOUNT **********************************/
	function new_account(){
		$this->db_session->set_userdata('office_account_id','');
		if($this->input->post('account'))
		{

			$account_id = $this->input->post('account');
			$result = $this->logins_model->get_one($account_id);
			$result['country'] = $this->office_model->get_country_from_ip($result['ip']);
			$avaialable_ips = $this->office_model->get_available_ips(false,$result['host_id'],$account_id);
			$result['avaialable_ips'] = $avaialable_ips;
			$this->follow($result);
		}
		$this->follow();
	}
	function active_accounts(){
		$data = $this->get_accounts(false);
		$this->follow($data);
	}
	function add_account(){
		$id = $this->input->post('login_id');

		$this->form_validation->set_rules('firstname', 'Firstname', 'trim|required|min_length[3]');
		$this->form_validation->set_rules('lastname', 'Lastname', 'trim|required|min_length[3]');
		$this->form_validation->set_rules('pass', 'Password', 'trim|required|min_length[6]|alpha_numeric');
		$this->form_validation->set_rules('email', 'Email', 'trim|required|valid_email');
		$this->form_validation->set_rules('login', 'Login', 'trim|required');

		if ($this->form_validation->run() == FALSE)
		{
			$this->follow(FALSE,validation_errors(),'home');
		}
		else
		{
			if(!empty($id)) $this->update_account();

			$name = $this->input->post('firstname')." ".$this->input->post('lastname');
			$email = $this->input->post('email');
			$login = $this->input->post('login');
			$pass = $this->input->post('pass');

			$email_creator = $this->freakauth_light->getUserProperty('id');

			//$ip = $this->db_session->userdata('office_account_ip');
			$ip = $this->input->post('ip');
			//$host_id = $this->db_session->userdata('office_server_id');
			$host_id = $this->input->post('host_id');
			if(!$host_id or !$ip) die('first_step');

			if($host_id == 7) $login = $email;

			$this->logins_model->set(array(
				'name' => $name,
				'email' => $email,
				'pass' => $pass,
				'login' => $login,
				'host_id' => $host_id,
				'ip' => $ip,
				'info' => $email_creator,
				'status_id' => 20
			));

			$this->follow(false,'new mail account added.','home');
		}

	}
	function update_account(){

		$id = $this->input->post('login_id');
		$ip = $this->input->post('ip');
		$host_id = $this->input->post('host_id');
		$name = $this->input->post('firstname')." ".$this->input->post('lastname');
		$email = $this->input->post('email');
		$login = $this->input->post('login');
		$pass = $this->input->post('pass');

		$email_creator = $this->freakauth_light->getUserProperty('id');

		$this->logins_model->set(array(
			'login_id' => $id,
			'name' => $name,
			'email' => $email,
			'pass' => $pass,
			'login' => $login,
			'ip' => $ip,
			'host_id' => $host_id,
			'info' => $email_creator,
			'status_id' => 1
		));

		$this->follow(false,'mail account updated.','home');
	}
	function del_account(){
		if($this->input->post('account'))
		{
			$this->logins_model->delete($this->input->post('account'));
		}
		$this->follow(false,'mail account deleted.','home');
	}
	function num_accounts() {

		$user_id = $this->freakauth_light->getUserProperty('id');

		$current_day = $this->logins_model->get_login(array(
			'info' => $user_id,
			'YEARWEEK(created) = YEARWEEK(current_timestamp)' => NULL,
			'DAY(created) = DAY(current_timestamp)' => NULL
		),TRUE);

		$current_week = $this->logins_model->get_login(array(
			'info' => $user_id,
			'YEARWEEK(created) = YEARWEEK(current_timestamp)' => NULL
		),TRUE);

		$current_month = $this->logins_model->get_login(array(
			'info' => $user_id,
			'YEAR(created) = YEAR(current_timestamp)' => NULL,
			'MONTH(created) = MONTH(current_timestamp)' => NULL
		),TRUE);


		$stats = " You have done so far $current_month accounts in this current month.<br />";
		$stats.= " * $current_week accounts in this week.<br />";
		$stats.= " * $current_day accounts in this day.<br />";


		$this->follow(false,$stats,'account');
	}
	/**************************************** END ACCOUNT **********************************/
	function select_ip(){

		$host_id = $this->input->post('server');
		$this->db_session->set_userdata('office_server_id',$host_id);
		$email_creator = $this->freakauth_light->getUserProperty('id');
		//$max_accounts_per_host = $this->office_model->max_accounts_per_host($host_id);
		//$data = $this->get_available_ips(false,$host_id);
		$data = $this->office_model->fetch_new_ips($email_creator,$_SERVER['SERVER_ADDR'],$host_id);
		$this->follow($data);
	}
	function choose_server(){
		$this->db_session->set_userdata('office_server_id',$this->input->post('server'));
		$data = $this->get_available_ips(false,$this->input->post('server'));
		die('{"task":"select_ip","data":'.$data.'}');
	}
	function get_available_ips($die = true,$host_id = false,$login_id = false) {
		$result = $this->office_model->get_available_ips($die, $host_id, $login_id);
		return $result;
	}
	function block_ip() {
		$ip_2_block = $this->input->post('ip');
		$host_id = $this->db_session->userdata('office_server_id');
		//$result = $this->office_model->block_ip($ip_2_block, $host_id);
		//$this->select_ip();
		//$this->follow($result,"ip:$ip_2_block blocked. A new ip fetched.","select_ip");
		$this->select_ip();
	}
	function choose_ip(){
		$ip = $this->input->post('ip');
		if(!$ip) {
			$host_id = $this->db_session->userdata('office_server_id');
			//$data = $this->get_available_ips(false,$host_id);
			//$this->follow($data,"You have to choose an ip first!","select_ip");
		}

		$this->db_session->set_userdata('office_account_ip',$ip);
		$email_creator = $this->freakauth_light->getUserProperty('email');
		$primeiro = $this->logins_model->random_nome(0);
		$ultimo = $this->logins_model->random_nome(1);
		$numero_de_contas = $this->logins_model->accounts_created($this->db_session->userdata('office_server_id'),$this->freakauth_light->getUserProperty('id'));

		$data = array(
			'ip' => $ip,
			'tunnel' => $this->office_model->get_tunnel_from_ip($ip),
			'host_id' => $this->db_session->userdata('office_server_id'),
			'servername' => $this->get_selected_server_name(false),
			'country' => $this->office_model->get_country_from_ip($ip),
			//'avaialable_ips' => $this->get_available_ips(false,$this->db_session->userdata('office_server_id')),
			'altemail' => $email_creator,
			'firstname' => $primeiro,
			'lastname' => $ultimo,
			'num_accounts' => $numero_de_contas
		);
		$servername = $data['servername'];
		$data = json_encode($data);
		die('{"task":"new_account","data":'.$data.',"info":"You have created '.$numero_de_contas.' account(s) in this server `'.$servername.'` so far today!"}');
	}
	function edit_account(){
		$result = $this->logins_model->get();
		$result = json_encode($result);
		die('{"task":"list_accounts","data":'.$result.'}');
	}
	function get_selected_ip($die = true){
		if($die) die($this->db_session->userdata('office_account_ip'));
		else return $this->db_session->userdata('office_account_ip');
	}
	function get_selected_server($die = true){
		if($die) die($this->db_session->userdata('office_server_id'));
		else return $this->db_session->userdata('office_server_id');
	}
	function get_selected_server_name($die = true){
		$name = $this->hosts_model->get(array('host_id' => $this->db_session->userdata('office_server_id')));
		$this->db_session->set_userdata('office_server_name', $name['hostname']);
		if($die) die($this->db_session->userdata('office_server_name'));
		else return $this->db_session->userdata('office_server_name');
	}
	function get_servers($die = true){
		$email_creator_id = $this->freakauth_light->getUserProperty('id');
		if(array_search($email_creator_id,array(2,1,4,9))) {
			$db = $this->hosts_model->db;
			$db->where('status_id',1);
			$query = $db->get('host');
			$result = $query->result_array();
			if($die) die(json_encode($result));
			else return json_encode($result);
		}
		$result = $this->office_model->get_available_hosts($email_creator_id);
		$valid_hosts = array();
		if ($result) {
			foreach($result as $row) {$valid_hosts[] = $row['host_id'];}
			if(empty($valid_hosts)) die('{"task":"home","info":"Limit of accounts per month reached. ( 2000 accounts per day )"}');
			$sql = "
				SELECT count( * ) AS done, host . * , setup . *
				FROM host
				LEFT JOIN login ON ( login.host_id = host.host_id
				AND login.info = $email_creator_id
				AND login.info NOT
				IN ( 2, 1, 4, 9 )
				AND WEEK( login.created, 1 ) = WEEK( login.created, 1 )
				AND login.status_id = 1 )
				LEFT JOIN setup ON setup.host_id = host.host_id
				WHERE host.status_id = 1
				AND host.host_id in (".implode(",",$valid_hosts).")
				GROUP BY host.host_id, login.info
				ORDER BY setup.priority

			";
			$db = $this->hosts_model->db;
			$query = $db->query($sql);

			$host = $query->result_array();

			if($die) die(json_encode($host));
			else return json_encode($host);
		} else return array();
	}
	function get_accounts($die = true){
		$result = $this->logins_model->get_one();
		if($die) die(json_encode($result));
		else return json_encode($result);
	}
	/*********************************** MAILING SECTION *****************************************/
	function current_sent(){
		$result = $this->emails_model->get();
		$this->follow($result);
	}
	function sendmail(){
		$email = $this->input->post('email');
		$subject = $this->input->post('subject');
		$message = $this->input->post('message');
		$this->emails_model->set(array(
			'email' => $email,
			'subject' => $subject,
			'message' => $message,
			'trigger' => 1
		));
		$this->follow(false,'','mailing');
	}
	function mailing_system_recipients(){
		$method = $this->input->post('method');
		$this->follow();
	}
	function random_login_details(){
		$this->load->library('anagram');
		$this->anagram->insert_word( "biscuit" );
	}
	function get_rights($level = FALSE){
		$user_id = $this->freakauth_light->getUserProperty('id');
		$result = $this->office_model->get_rights($user_id,$level);
		return $result;
	}
	/***********************************************************************************************/
}