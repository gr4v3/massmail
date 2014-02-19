<?php
/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */
class Import extends CI_Controller
{
	function __construct() {
		parent::__construct();
		$this->load->library('form_validation');
		$this->load->library('TCTemplate');
		$this->load->library('upload');
		$this->load->model('hosts_model');
		$this->load->model('emails_model');
		$this->load->model('logins_model');
		$this->load->model('rules_model');
                $this->load->model('mailing_model');
		$this->load->model('settings_model');
		$this->load->helper('array');
    }
	function index(){}
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

		//188.165.116.192 / 26
		if($owner === false ) $owner = $_SERVER['SERVER_ADDR'];

		if($from and $to) {

			// generate ip addrs
			$arry1 = explode(".",$from);
			$arry2 = explode(".",$to);


			$a1 = $arry1[0]; $b1 = $arry1[1];	$c1 = $arry1[2]; $d1 = $arry1[3];
			$a2 = $arry2[0]; $b2 = $arry2[1];	$c2 = $arry2[2]; $d2 = $arry2[3];
			while( $d2 >= $d1 || $c2 > $c1 || $b2 > $b1 || $a2 > $a1){
				if($d1 > 255){
					$d1 = 1;
					$c1 ++;
				}
				if($c1 > 255){
					$c1 = 1;
					$b1 ++;
				}
				if($b1 > 255){
					$b1 = 1;
					$a1 ++;
				}
				$ip = "$a1.$b1.$c1.$d1";
				$result = $this->emails_model->db->simple_query("insert into available_ips (ip,country,owner) values('$ip','$country','$owner');");
				$d1 ++;
				Debug("insert into available_ips (ip,country,owner) values('$ip','$country','$owner');");
			}

		} else if($from) $this->emails_model->db->simple_query("insert into available_ips (ip,country,owner) values('$ip','$country','$owner');");

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
    function uncheckedip(){

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
	function emails_stack_csv() {
		$this->load->config('upload');
		$this->tctemplate->set_template('import/default');
		$this->tctemplate->set_view_folder('import');
		$form_data = array();
		$task = $this->input->post('task');
		if ($task == 'upload_file' && ! empty($_FILES)) {
			if (move_uploaded_file($_FILES["userfile"]["tmp_name"], $this->config->item('upload_path').$_FILES["userfile"]["name"])) {
				$file_name = $_FILES["userfile"]["name"];
				$file_path = $this->config->item('upload_path').$_FILES["userfile"]["name"];
				$type = $this->input->post('type');
				if ($type == 'csv') {
					$handle = fopen($file_path, "r");
					if ($handle) {
						$select_fields_form = fgetcsv($handle);
						$form_structure = array(
							'file_name' => $file_name,
							'file_path' => $file_path,
							'fields'    => $select_fields_form
						);
						fclose($handle);
						$this->tctemplate->show('Index', 'file_uploaded', $form_structure);
					}
				}
			}
		} elseif ($task == 'select_fields') {
			$form_structure = $_POST;
			$file_name = $form_structure['file_name'];
			$file_path = $form_structure['file_path'];
			$fields_selected = $form_structure['field'];
			$fields_2_rename = $form_structure['rename'];
			$select_fields = array();
			foreach($fields_selected as $index) {$select_fields[$fields_2_rename[$index]] = $index;}
			$form_structure['data'] = $select_fields;
			$this->tctemplate->show('Index', 'fields_selected', $form_structure);
		} elseif ($task == 'parse_file') {
			$form_structure = $_POST;
			$webmaster_id = $form_structure['webmaster_id'];
			$email_collected_id = $form_structure['email_collected_id'];
                        $niche_id = $form_structure['niche_id'];
			$lang_iso = $form_structure['lang_iso'];
			$table_structure = $form_structure['data'];
			$handle = fopen($form_structure['file_path'], "r");
			if ($handle) {
				$table_fields = fgetcsv($handle); // just to pop the first line. not needed for the mysql table
				while (($data = fgetcsv($handle)) !== FALSE) {
					$row = array(
						'webmaster_id' => $webmaster_id,
						'email_collected_id' => $email_collected_id,
                                                'niche_id' => $niche_id,
						'lang_iso' => $lang_iso
					);
					foreach($table_structure as $table_field => $index) {
						$row[$table_field] = $data[$index];
					}
					$this->mailing_model->import_emails_stack((object) $row);
				}
				fclose($handle);
				$this->tctemplate->add_module('csv_file_success', 'top');
			}
			$this->tctemplate->show('Index', 'emails_stack_csv', $form_structure);
		} else $this->tctemplate->show('Index', 'emails_stack_csv', $form_data);

	}
}
