<?php
/* 
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */
class Proxy extends Controller {
	function Proxy()
	{
		parent::Controller();
		$this->load->library('log');
		$this->load->helper('array');
		$this->load->plugin('PHPMailer/smtp');
		$this->load->library('TCCurl');
	}
	function send(){
		$ip = $_SERVER['SERVER_ADDR'];
		$smtp = new SMTP();
		//$smtp->PROXY_IP = $ip;
		$smtp->PROXY_IP = '68.109.178.52';
		$smtp->PROXY_PORT = '27977';
		$result = $smtp->Connect('smtp.mail.yahoo.com',465);
		$result = $smtp->Hello();
		$result = $smtp->Authenticate('matemediospx@yahoo.com','XszkVWBF5Z9t');
		$smtp->do_verp = true;
		$result = $smtp->Mail('matemediospx@yahoo.com');
		$result = $smtp->Recipient('fmenezes.tc@hotmail.com');
		$data = md5(mktime());
		$data.= '.'.$smtp->CRLF;
		$result = $smtp->Data($data);
		$smtp->Quit();
	}
	function rules(){
		$this->load->model('logins_model');
		$db = $this->logins_model->db;
		$sql = "SELECT message FROM  `emails` WHERE emails_id = 6327251 LIMIT 0 , 30";
		$query = $db->query($sql);
		$row = $query->row_array();
		echo gzdecode($row['message']);
	}
	function look_me_up($hostname){
        $records = dns_get_record( $hostname , DNS_A);
		$ip = false;
		foreach($records as $record){if ($record['type'] == 'A') $ip = $record['ip'];}
        return $ip;
    }
	function domains() {
		$this->load->model('logins_model');
		$db = $this->logins_model->db;
		$owner = $_SERVER['SERVER_ADDR'];
		$valid = array();
		$invalid = array();
		$db->where('owner',$owner);
		$db->where('active',1);
		$query = $db->get('available_ips');
		if($query->num_rows() > 0) {
			$result = $query->result_array();
			$total = count($result);
			foreach($result as $index => $value) {
				//$domain = gethostbyaddr($value['ip']);
				//if($domain == $value['domain']) $valid[] = $value;
				//else $invalid[] = $value;
				$ip = $this->look_me_up($value['domain']);
				if($ip == $value['ip']) {
					$db->set('active',1);
					$db->where('id',$value['id']);
					$db->update('available_ips');
				}
				else {
					$db->set('active',0);
					$db->where('id',$value['id']);
					$db->update('available_ips');
				}
				echo ((($index+1) / $total) * 100) . '%<br />';
			}
		}
	}
	function proxies() {
		$this->load->model('logins_model');
		$db = $this->logins_model->db;
		$owner = $_SERVER['SERVER_ADDR'];
		$valid = array();
		$invalid = array();
		$db->where('owner',$owner);
		$query = $db->get('available_ips');
		if($query->num_rows() > 0) {
			$result = $query->result_array();
			
			foreach($result as $value) {
				
				$ip = $value['ip'];
				$result = $this->tccurl->get(base_url(), 1, $ip);
				if(strpos($result,base_url())) {
					var_dump($result);
					$db->set('active',1);
					$db->where('id',$value['id']);
					$db->update('available_ips');
				} else {
					$db->set('active',0);
					$db->where('id',$value['id']);
					$db->update('available_ips');
					echo 'proxy:'.$ip.' dead!<br />';
				}
			}	
		}
		die();
	}
	function testetemplate() {
		$this->load->library('Send');
		$template = $this->get_template_parsed(5);
		$params = array(
			'Hostname'   => 'mailingxmanager',
			'SMTPSecure' => '',
			'Host'       => 'mailingxmanager.xctrl.net',
			'Port'       => 25,
			'Timeout'    => 10,
			'Username'   => 'webuser',
			'Password'   => 'Megabyt3s',
			'Name'       => 'fabio menezes',
			'Proxy'      => '94.23.75.109'
		);
		$this->send->smtp_connection = false;
		$this->send->mail($params,'fmenezes.tc@hotmail.com',$template);
		//$template = $this->get_template_parsed(54);
		//$this->send->mail($params,'fmenezes.tc@hotmail.com',$template);
	}
	function get_template_parsed($template_id = FALSE,$additional_fields = FALSE) {
		$mailing = $this->load->database('mailing', TRUE);
		$mailing->where('mailing_template_id',$template_id);
		$query = $mailing->get('mailing_template');
		$template = FALSE;

		if($query && $query->num_rows > 0) {
			$result = $query->row_array();
			$template = $this->emails_model->get_template_parsed($result['mailing_template_id'],$additional_fields);
		}
		return $template;
	}
	function manual_mailing($webmaster_id = FALSE, $niche_id = FALSE) {
		// the file is in /home/webuser/tmp and file is called manualmailing
		$this->load->model('logins_model');
		$db = $this->logins_model->db;
		$handle = fopen("/home/webuser/tmp/manualmailing", "r");
		if ($handle) {
			while (!feof($handle)) {
				$buffer = fgets($handle, 4096);
				// sql query -> INSERT DELAYED INTO emails_stack (email_collected_id,webmaster_id,niche_id,email) VALUES (xxx,xx,xx,'')
				$emails = trim($buffer);
				$insert_query = "INSERT DELAYED INTO emails_stack (email_collected_id,webmaster_id,niche_id,email) VALUES (1,$webmaster_id,$niche_id,'$emails')";
				$db->query($insert_query);
				usleep(10000);
			}
			fclose($handle);
		}
		
	}
}
