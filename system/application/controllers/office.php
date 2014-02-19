<?php
class Office extends CI_Controller {
    function __construct() {
        parent::__construct();
        $this->load->config('freakauth_light');
        $this->load->library('log');
        $this->load->model('hosts_model');
        $this->load->model('logins_model');
        $this->load->model('settings_model');
        $this->load->model('rules_model');
        $this->load->model('emails_model');
        $this->load->model('office_model');
        $this->load->model('mailing_model');
        $this->load->model('setup_model');
        $this->load->helper('form');
        $this->load->helper('url');
        $this->load->helper('array');
        $this->load->helper('freakauth_light');
        $this->load->library('form_validation');
        $this->load->library('Send');
        $this->load->library('Freakauth_light');
        $this->load->language('form_validation');
    }
    function index() {
        $this->freakauth_light->check();
        $lang = $this->_get_browser_langs();
        $this->load->library('TCTemplate');
        $this->tctemplate->set_template('templates/default');
        $this->tctemplate->include_js_file('mootools-core-1.4.5.js?'.mktime());
        $this->tctemplate->include_js_file('mootools-more-1.4.0.1.js?'.mktime());
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
    // office user-interface flow control
    function input() {
            $user_id = $this->freakauth_light->getUserProperty('id');
            if(!$user_id) $this->follow(false,'Your session has expired. Please login again. ','home');
            $this->form_validation->set_rules('task', 'task', 'required|alpha_dash');
            if ($this->form_validation->run()) {
                    $task = $this->input->post('task');
                    if($this->get_rights($task) == FALSE) $this->follow(false,'access denied.'.$task,'home');
                    else if(method_exists('Office',$task)) $this->{$task}();
                    else $this->follow();
            } else $this->follow(false,'access denied. task not valid.','home');
    }
    function follow($data = '',$info = '',$task = false) {
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
    function new_server() {
            $this->db_session->set_userdata('office_server_id','');
            $this->form_validation->set_rules('server', 'server', 'required|is_natural_no_zero');
            if ($this->form_validation->run()) {
                    $host_id = $this->input->post('server');
                    $host = $this->hosts_model->get($host_id);
                    $settings = $this->settings_model->get($host_id);
                    $rules = $this->rules_model->get($host_id);
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
    function active_servers() {
            $data = $this->get_servers(false);
            $this->follow($data);
    }
    function all_servers() {
            $data = $this->get_all_servers();
            $this->follow($data);
    }
    function add_server() {
            $this->form_validation->set_rules('hostname', 'hostname', 'required|alpha_dash');
            $is_update = FALSE;
            if ($this->form_validation->run()) {
                    $host_id = $this->input->post('host_id');
                    if ( ! empty($host_id)) $is_update = TRUE;
                    $hostname = $this->input->post('hostname');
                    $params = new stdClass;
                    $params->hostname = $hostname;
                    $params->host_id = $host_id;
                    $host_id = $this->hosts_model->set($params);
            } else $this->follow(false,'access denied. post values not valid.','home');
            /********************************************/
            $accounts_host = $this->input->post('ACCOUNTS_HOST');
            $accounts_ip_host = $this->input->post('ACCOUNTS_IP_HOST');
            $accounts_ip = $this->input->post('ACCOUNTS_IP');
            $accounts_interval = $this->input->post('ACCOUNTS_INTERVAL');
            $mass_sender = $this->input->post('MASS_SENDER');
            $whitelist = $this->input->post('WHITELIST');
            $recipients_cycle = $this->input->post('RECIPIENTS_CYCLE');
            $recipients_ratio = $this->input->post('RECIPIENTS_RATIO');
            $use_proxy = $this->input->post('USE_PROXY');
            $spam_report_type = $this->input->post('SPAM_REPORT_TYPE');
            $spam_report_plugin = $this->input->post('SPAM_REPORT_PLUGIN');
            $export = $this->input->post('export');
            $import = $this->input->post('import');
            $flood_refresh = $this->input->post('flood_refresh');
            $flood_interval = $this->input->post('flood_interval');
            $flood_sleep = $this->input->post('flood_sleep');
            $bounce_interval = $this->input->post('bounce_interval');
            $send_interval = $this->input->post('send_interval');
            $send_limit = $this->input->post('send_limit');
            $country = $this->input->post('country');
            /********************************************/
            $this->db_session->set_userdata('office_server_id', $host_id);
            $this->db_session->set_userdata('office_server_name', $hostname);
            $this->setup_model->set_accounts_host($host_id, $accounts_host);
            $this->setup_model->set_accounts_ip_host($host_id, $accounts_ip_host);
            $this->setup_model->set_accounts_ip($host_id, $accounts_ip);
            $this->setup_model->set_accounts_interval($host_id, $accounts_interval);
            $this->setup_model->set_mass_sender($host_id, $mass_sender);
            $this->setup_model->set_whitelist($host_id, $whitelist);
            $this->setup_model->set_recipients_cycle($host_id, $recipients_cycle);
            $this->setup_model->set_recipients_ratio($host_id, $recipients_ratio);
            $this->setup_model->set_use_proxy($host_id, $use_proxy);
            $this->setup_model->set_spam_report_type($host_id, $spam_report_type);
            $this->setup_model->set_spam_report_plugin($host_id, $spam_report_plugin);
            // smtp settings
            $params = new stdClass;
            $params->host_id = $host_id;
            $params->host = $export['host'];
            $params->type = 'smtp';
            $params->port = $export['port'];
            $params->timeout = $export['timeout'];
            $params->headers = $export['headers'];
            $params->service_flags = $export['service_flags'];
            $this->settings_model->set($params);
            // imap/pop settings
            $params = new stdClass;
            $params->host_id = $host_id;
            $params->host = $import['host'];
            $params->type = $import['type'];
            $params->port = $import['port'];
            $params->service_flags = $import['service_flags'];
            $params->inbox = $import['inbox'];
            $params->timeout = $import['timeout'];
            $this->settings_model->set($params);
            // accounts behaviour rules
            $params = new stdClass;
            $params->host_id = $host_id;
            $params->flood_refresh = $flood_refresh;
            $params->flood_interval = $flood_interval;
            $params->flood_sleep = $flood_sleep;
            $params->bounce_interval = $bounce_interval;
            $params->send_interval = $send_interval;
            $params->send_limit = $send_limit;
            $params->country = $country;
            $this->rules_model->set($params);
            if ($is_update) $this->follow(false,'new mail server updated.','home');
            else $this->follow(false,'new mail server added.','home');
    }
    function update_server() {
            $name = $this->input->post('hostname');
            $host_id = $this->input->post('host_id');
            $export = $this->input->post('export');
            $import = $this->input->post('import');
            $params = new stdClass;
            $params->hostname = $name;
            $params->host_id = $host_id;
            $this->hosts_model->set($params);
            /********************************************/
            $accounts_host = $this->input->post('ACCOUNTS_HOST');
            $accounts_ip_host = $this->input->post('ACCOUNTS_IP_HOST');
            $accounts_ip = $this->input->post('ACCOUNTS_IP');
            $accounts_interval = $this->input->post('ACCOUNTS_INTERVAL');
            $mass_sender = $this->input->post('MASS_SENDER');
            $whitelist = $this->input->post('WHITELIST');
            $recipients_cycle = $this->input->post('RECIPIENTS_CYCLE');
            $recipients_ratio = $this->input->post('RECIPIENTS_RATIO');
            $use_proxy = $this->input->post('USE_PROXY');
            $export = $this->input->post('export');
            $import = $this->input->post('import');
            $flood_refresh = $this->input->post('flood_refresh');
            $flood_interval = $this->input->post('flood_interval');
            $flood_sleep = $this->input->post('flood_sleep');
            $bounce_interval = $this->input->post('bounce_interval');
            $send_interval = $this->input->post('send_interval');
            $send_limit = $this->input->post('send_limit');
            $country = $this->input->post('country');
            /********************************************/
            $this->db_session->set_userdata('office_server_id', $host_id);
            $this->db_session->set_userdata('office_server_name', $name);
            $this->setup_model->set_accounts_host($host_id, $accounts_host);
            $this->setup_model->set_accounts_ip_host($host_id, $accounts_ip_host);
            $this->setup_model->set_accounts_ip($host_id, $accounts_ip);
            $this->setup_model->set_accounts_interval($host_id, $accounts_interval);
            $this->setup_model->set_mass_sender($host_id, $mass_sender);
            $this->setup_model->set_whitelist($host_id, $whitelist);
            $this->setup_model->set_recipients_cycle($host_id, $recipients_cycle);
            $this->setup_model->set_recipients_ratio($host_id, $recipients_ratio);
            $this->setup_model->set_use_proxy($host_id, $use_proxy);
            $params = new stdClass;
            $params->host_id = $host_id;
            $params->host = $export['host'];
            $params->type = 'smtp';
            $params->port = $export['port'];
            $params->timeout = $export['timeout'];
            $params->service_flags = $export['service_flags'];
            $params->handler = $export['handler'];
            $params->headers = $export['headers'];
            $this->settings_model->set($params);
            $params = new stdClass;
            $params->host_id = $host_id;
            $params->host = $export['host'];
            $params->type = $import['type'];
            $params->port = $import['port'];
            $params->service_flags = $import['service_flags'];
            $params->inbox = $import['inbox'];
            $params->timeout = $import['timeout'];
            $this->settings_model->set($params);
            $params = new stdClass;
            $params->host_id = $host_id;
            $params->flood_refresh = $this->input->post('flood_refresh');
            $params->flood_interval = $this->input->post('flood_interval');
            $params->flood_sleep = $this->input->post('flood_sleep');
            $params->bounce_interval = $this->input->post('bounce_interval');
            $params->send_interval = $this->input->post('send_interval');
            $params->send_limit = $this->input->post('send_limit');
            $params->country = $this->input->post('country');
            $this->rules_model->set($params);
            $this->follow(false,'mail server updated.','home');
    }
    function del_server() {
            if($this->input->post('server')) $this->hosts_model->lock($this->input->post('server'));
            $this->follow(false,'mail server provider deleted.','home');
    }
    function lockunlock_server() {
            if($this->input->post('server')) {
                    $host_id = $this->input->post('server');
                    $host_item = $this->hosts_model->get($host_id);
                    if ($host_item) {
                            switch ($host_item->status_id) {
                                    case 1:
                                            $this->hosts_model->lock($host_id);
                                            $this->follow(false,'mail server provider locked.','home');
                                    case 8:
                                            $this->hosts_model->unlock($host_id);
                                            $this->follow(false,'mail server provider unlocked.','home');
                            }
                    } else $this->follow(false,'database error.','server');
            } else $this->follow(false,'select first a mail server provider.','server');
    }
    /**************************************** END SERVER **********************************/

    /**************************************** BEGIN ACCOUNT **********************************/
    function new_account() {
            $this->db_session->set_userdata('office_account_id','');
            if($this->input->post('account')) {
                    $account_id = $this->input->post('account');
                    $result = $this->logins_model->get_one($account_id);
                    $result->country = $this->office_model->get_country_from_ip($result->ip);
                    $avaialable_ips = $this->office_model->get_available_ips(false,$result->host_id,$account_id);
                    $result->avaialable_ips = $avaialable_ips;
                    $this->follow($result);
            }
            $this->follow();
    }
    function active_accounts() {
            $data = $this->get_accounts(false);
            $this->follow($data);
    }
    function add_account() {
            $id = $this->input->post('login_id');
            $this->form_validation->set_rules('firstname', 'Firstname', 'trim|required|min_length[3]');
            $this->form_validation->set_rules('lastname', 'Lastname', 'trim|required|min_length[3]');
            $this->form_validation->set_rules('pass', 'Password', 'trim|required|min_length[6]|alpha_numeric');
            $this->form_validation->set_rules('email', 'Email', 'trim|required|valid_email');
            $this->form_validation->set_rules('login', 'Login', 'trim|required');
            if ($this->form_validation->run() == FALSE)
            {
                    $this->follow(FALSE,validation_errors(),'home');
            } else {
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
                    $params = new stdClass;
                    $params->name = $name;
                    $params->email = $email;
                    $params->pass = $pass;
                    $params->login = $login;
                    $params->host_id = $host_id;
                    $params->ip = $ip;
                    $params->info = $email_creator;
                    $params->status_id = 20;
                    $this->logins_model->set($params);
                    $this->office_model->increase_account_counter($email_creator, $ip);
                    $this->follow(false,'new mail account added.','home');
            }
    }
    function update_account() {

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
    function del_account() {
            if($this->input->post('account')) {
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
    function select_ip() {
            $host_id = $this->input->post('server');
            $this->db_session->set_userdata('office_server_id',$host_id);
            $email_creator = $this->freakauth_light->getUserProperty('id');
            $data = $this->office_model->fetch_new_ips($email_creator, $_SERVER['SERVER_ADDR'], $host_id);
            $this->follow($data);
    }
    function choose_server() {
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
            $this->office_model->block_ip($ip_2_block);
            //$this->select_ip();
            //$this->follow($result,"ip:$ip_2_block blocked. A new ip fetched.","select_ip");
            $this->follow(false,'ip skiped.','active_servers');
    }
    function choose_ip() {
            $ip = $this->input->post('ip');
            $this->form_validation->set_rules('ip', 'ip', 'valid_ip');
            if ($this->form_validation->run()) {
                    $this->db_session->set_userdata('office_account_ip',$ip);
                    $email_creator   = $this->freakauth_light->getUserProperty('email');
                    $first_name      = $this->logins_model->random_name(0);
                    $last_name       = $this->logins_model->random_name(1);
                    $num_of_accounts = $this->logins_model->accounts_created($this->db_session->userdata('office_server_id'),$this->freakauth_light->getUserProperty('id'));
                    $data = array(
                            'ip' => $ip,
                            'tunnel' => $this->office_model->get_tunnel_from_ip($ip),
                            'host_id' => $this->db_session->userdata('office_server_id'),
                            'servername' => $this->get_selected_server_name(false),
                            'country' => $this->office_model->get_country_from_ip($ip),
                            //'avaialable_ips' => $this->get_available_ips(false,$this->db_session->userdata('office_server_id')),
                            'altemail' => $email_creator,
                            'firstname' => $first_name,
                            'lastname' => $last_name,
                            'num_accounts' => $num_of_accounts
                    );
                    $servername = $data['servername'];
                    $data = json_encode($data);
                    die('{"task":"new_account","data":'.$data.',"info":"You have created '.$num_of_accounts.' account(s) in this server `'.$servername.'` so far today!"}');
            } else $this->follow(FALSE,validation_errors(),'home');
    }
    function edit_account() {
            $result = $this->logins_model->get();
            $result = json_encode($result);
            die('{"task":"list_accounts","data":'.$result.'}');
    }
    function get_selected_ip($die = true) {
            if($die) die($this->db_session->userdata('office_account_ip'));
            else return $this->db_session->userdata('office_account_ip');
    }
    function get_selected_server($die = true) {
            if($die) die($this->db_session->userdata('office_server_id'));
            else return $this->db_session->userdata('office_server_id');
    }
    function get_selected_server_name($die = true) {
            $row = $this->hosts_model->get($this->db_session->userdata('office_server_id'));
            if ($row) {
                    $this->db_session->set_userdata('office_server_name', $row->hostname);
                    if($die) die($this->db_session->userdata('office_server_name'));
                    else return $this->db_session->userdata('office_server_name');
            }

    }
    function get_servers($die = true) {
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
    function get_all_servers() {
            $all_hosts = $this->hosts_model->all_hosts();
            if ($all_hosts) return json_encode($all_hosts);
    }
    function get_accounts($die = true) {
            $result = $this->logins_model->get_one();
            if($die) die(json_encode($result));
            else return json_encode($result);
    }
    /*********************************** MAILING SECTION *****************************************/
    function sendmail() {
            $email = $this->input->post('email');
            $subject = $this->input->post('subject');
            $message = $this->input->post('message');
            $template = new stdClass;
            $template->subject = $subject;
            $template->html = $message;
            $template->mailing_template_id = 1;
            $this->send->fakeinbox($email, $template);
            $this->follow(false,'','mailing');
    }
    function random_login_details() {
            $this->load->library('anagram');
            $this->anagram->insert_word( "biscuit" );
    }
    function get_rights($level = FALSE) {
            $user_id = $this->freakauth_light->getUserProperty('id');
            $result = $this->office_model->get_rights($user_id,$level);
            return $result;
    }
    /***********************************************************************************************/
    function mailing_system_campaigns() { 
        $data = array();
        $result = $this->mailing_model->all_mailings();
        if ($result) {
            foreach($result as $row) {
                $data[] = $row;
            }
            $data = json_encode($data);
        }
        $this->follow($data);
    }
    function mailing_system_campaigns_edit() {
        
        
        $group_id = $this->input->post('content');
        $execute  = $this->input->post('execute');
        
        $result = $this->mailing_model->select_campaign($group_id);
        
        $this->follow($result);
    }
}