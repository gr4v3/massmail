<?php
class Account extends Controller {
	function Account(){
		parent::Controller();
		$this->load->library('log');
		$this->load->helper('array');
        $this->load->model('logins_model');
	}
	function index(){}
	function yahoo($login_id = FALSE,$block = false){
		// fetch one account not checked yet or check onyl yesterday
                if($login_id) {
                    if($block == 1) $this->logins_model->lock($login_id,9);
                    //else if($block == 2) $this->logins_model->delete($login_id);
                    else $this->logins_model->check($login_id);
                    die("true");
                } else {
                    $login = $this->logins_model->notchecked(array(18,19,21,22,23,27,2));
                    die(implode(",",$login));
                }

	}
	function sapo($host_id,$info,$status_id,$created,$name) {

		$result = $this->logins_model->get_login(array(
			'host_id' => $host_id,
			'info' => $info,
			'status_id' => $status_id,
			'created' => $created,
			'name' => $name
		));

	}
	function change($login_id = FALSE,$domain = FALSE) {

		$db = $this->logins_model->db;

		$domain_exploded = explode('.',$domain);
		$server = current($domain_exploded);
		$region = end($domain_exploded);

		$db->like('hostname',$server.' '.$region);
		$query = $db->get('host');
		if($query->num_rows() > 0) {
			
			$row = $query->row_array();
			$db->where('login_id',$login_id);
			$db->set('email',"concat(login,'@','$domain')",false);
			$db->set('host_id',$row['host_id']);
			$db->update('login');
			die('true');
		}
		die('false');
	}
	function mail() {
		$this->load->library('Send');
		$logins = $this->logins_model->get_accounts_to_verify(32);
		if ($logins) {
			$template = $this->get_template_parsed(5,array('emails_id' => 0,'webmaster_id' => 2,'niche_id' => 19,'mailing_group_id' => 2,'email' => 'fmenezes.tc@hotmail.com'));
			foreach($logins as $item) {
				$params = array(
					'Hostname'   => $item['domain'],
					'SMTPSecure' => 'ssl',
					'Host'       => 'smtp.mail.com',
					'Port'       => 465,
					'Username'   => $item['email'],
					'Password'   => $item['pass'],
					'Name'       => $item['name'],
					'Proxy'      => $item['ip']
				);
				$result = $this->send->mail($params,'fmenezes.tc@hotmail.com',$template);
				if ($result) $this->logins_model->free($item['login_id']);
				else $this->logins_model->lock($item['login_id'],21);
			}
		}
	}
	function yahoo_com() {
		$this->load->library('Send');
		$logins = $this->logins_model->get_accounts_to_verify(2,100,1);
		if ($logins) {
			//$template = $this->get_template_parsed(5,array('emails_id' => 0,'webmaster_id' => 2,'niche_id' => 19,'mailing_group_id' => 2,'email' => 'fmenezes.tc@hotmail.com'));
			foreach($logins as $item) {
				$params = array(
					'Hostname'   => $item['domain'],
					'SMTPSecure' => 'ssl',
					'Host'       => 'smtp.mail.yahoo.com',
					'Port'       => 465,
					'Username'   => $item['email'],
					'Password'   => $item['pass'],
					'Name'       => $item['name'],
					'Proxy'      => FALSE
				);
				$result = $this->send->verify($params);
				var_dump($result);
				if ($result) $this->logins_model->free($item['login_id']);
				else $this->logins_model->lock($item['login_id'],9);
			}
		}
	}
	function get_template_parsed($template_id = FALSE,$additional_fields = FALSE) {
		$mailing = $this->load->database('mailing', TRUE);
		$mailing->where('mailing_template_id',$template_id);
		$query = $mailing->get('mailing_template');
		$template = FALSE;

		if($query && $query->num_rows > 0) {
			$result = $query->row_array();
			$template = $this->template($result,true,$additional_fields);
		}
		return $template;
	}
	function template($template = array(),$return_obj = FALSE,$additional_fields = FALSE){
			$this->load->library('parser');
			$rules = unserialize(utf8_decode($template['rules']));
			$tracker = '<img src="http://mail.xctrl.net/framework/tracker.jpg" alt="unsubscribe" />';
			$subject = $template['subject'];
			$body = $template['html'].$tracker;
			$keywords = FALSE;

			if(!is_array($rules)) {
				if($return_obj) {
					$content = array('subject' => $subject,'body' => $body);
					return $content;
				} else {
					$html = '<table>';
					$html.= '<tr>';
						$html.= '<td style="vertical-align:top;">Subject:</td>';
						$html.= '<td style="vertical-align:top;">'.$subject.'</td>';
					$html.= '<tr>';
					$html.= '<tr>';
						$html.= '<td style="vertical-align:top;">body:</td>';
						$html.= '<td style="vertical-align:top;">'.$body.'</td>';
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

			if($keywords) {
				$keywords = json_decode(utf8_decode($keywords),true);
				if (is_array($keywords)) {
					$words_to_switch = array();
					foreach($keywords as $word => $synonymes){
						$synonymes[] = array('active' => TRUE,'data' => $word);
						$random_index = array_rand($synonymes);
						$substitue = $word;
						if($synonymes[$random_index]['active']) $substitue = $synonymes[$random_index]['data'];
						$substitue = str_replace('?','é',$substitue);
						$words_to_switch[utf8_decode($word)] = $substitue;
					}
					foreach($words_to_switch as $index => $value) {
						$subject = str_replace($index,$value,$subject);
						$body = str_replace($index,$value,$body);
					}	
				}
			}

			$parsed_rules = $rules;
			foreach($rules as $row) {
				foreach($parsed_rules as &$sub_row){
					$sub_row['value'] = $this->parser->parse_generic($sub_row['value'], array($row['field'] => $row['value']), TRUE);
				}
			}
			foreach($parsed_rules as $row){
				$subject = $this->parser->parse_generic($subject, array($row['field'] => $row['value']), TRUE);
				$body = $this->parser->parse_generic($body, array($row['field'] => $row['value']), TRUE);
			}

			if($additional_fields) {
				$subject = $this->parser->parse_generic($subject, $additional_fields, TRUE);
				$body = $this->parser->parse_generic($body, $additional_fields, TRUE);
			};


			if($return_obj) {

				$content = array(
					'subject' => $subject,
					'body' => $body
				);
				return $content;
			}

			$html = '<table>';
			$html.= '<tr>';
				$html.= '<td style="vertical-align:top;">Subject:</td>';
				$html.= '<td style="vertical-align:top;">'.$subject.'</td>';
			$html.= '<tr>';
			$html.= '<tr>';
				$html.= '<td style="vertical-align:top;">body:</td>';
				$html.= '<td style="vertical-align:top;">'.$body.'</td>';
			$html.= '<tr>';
			$html.= '</table>';
			return $html;
    }

}
