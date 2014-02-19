<?php

class Mailing_model extends CI_Model {
	function __construct(){
		parent::__construct();
		$this->load->model('crawler_model');
                $this->load->model('emails_model');
		$this->load->config('mailserver');
		$this->load->helper('array');
		$this->load->helper('email');
	}
        // get the mailing campaign
        function select_campaign($group_id = NULL) {
            if (empty($group_id)) return FALSE;
            $mailing = $this->load->database('mailing', TRUE);
            $query = $mailing->get_where('group', array('group_id' => $group_id));
            if ($query ) return $query->result();
            else return FALSE;
        }
        // insert/update new mailing campaign row
        function update_campaign($data = NULL) {
            if (empty($data)) return FALSE;
            
            $mailing = $this->load->database('mailing', TRUE);
            $result = $mailing->insert('group', $data); 
            
            var_dump($result);
        }
	// fetch the active groups to send emails
	function get_mailings($auto = 0,$mailing_group_id = NULL) {
		$mailing = $this->load->database('mailing', TRUE);
		$automailing_index = $this->get_stored_var('mailserver_automailing_index');
		//in case of var exist in stored_vars
		if ($automailing_index >= 0) {
			$now = mktime();
			$sql = "select * from mailing_group WHERE active = 1 ";
			if($auto === 0) $sql.= "AND `trigger` = $auto";
			$sql.= " AND (
			   (start <= $now AND end >= $now)
			OR (start IS NULL AND end >= $now)
			OR (start <= $now AND end IS NULL)
			OR (start IS NULL AND end IS NULL)
			)";
			if ($mailing_group_id) $sql.= " AND mailing_group_id = $mailing_group_id";
			else $sql.= " AND mailing_group_id > $automailing_index";
			$sql.= ' LIMIT 1';
			$query = $mailing->query($sql);
			if ($query && $query->num_rows > 0) {
				$row = $query->row();
				$automailing_index = $row->mailing_group_id;
				$this->set_stored_var('mailserver_automailing_index',$automailing_index);
				$query->free_result();
				return $row;
			} else {
				//there is no more mailing_group in the table
				//so i will return to the begin of the table
				$this->set_stored_var('mailserver_automailing_index',0);
				return $this->get_mailings($auto, $mailing_group_id);
			}
		} else {
			//in case there isnt no var in stored_vars
			$this->set_stored_var('mailserver_automailing_index',0);
			return $this->get_mailings($auto, $mailing_group_id);
		}
	}
	// get the templates associted to the given group passed in parameter
	function get_templates($mailing_group_id = FALSE,$template_id = FALSE) {
		$mailing = $this->load->database('mailing', TRUE);
		//get first the templates with the default lang (fr)
		$mailing->select('mailing_template.mailing_template_id as mailing_template_id,mailing_template.niche_id as niche_id,mailing_template.webmaster_id as webmaster_id,mailing_group_templates.trigger as time,mailing_group_templates.mailing_group_templates_id as queue',FALSE);
		$mailing->join("mailing_group_templates","mailing_group_templates.mailing_template_id = mailing_template.mailing_template_id","inner");
		$mailing->join("mailing_group","mailing_group_templates.mailing_group_id = mailing_group.mailing_group_id","inner");
		$mailing->where("mailing_group.mailing_group_id",$mailing_group_id);
                $mailing->where("mailing_template.active",1);
		if ($template_id) $mailing->where("mailing_template.mailing_template_id",$template_id);
                $mailing->order_by('mailing_group_templates.order ASC');
		$query = $mailing->get("mailing_template");
		$original_templates = array();
		if ($query && $query->num_rows > 0 ) {
			$original_templates = $query->result();
			$query->free_result();
		}
		foreach($original_templates as &$template) {
			$template_id = $template->mailing_template_id;
			$translated_templates = $this->get_translated_templates($template_id);
			if ($translated_templates) {
				foreach($translated_templates as $translated_template) {
					$template->translation->{$translated_template->iso_code} = $translated_template;
				}
			}
		}
		return $original_templates;
	}
	// get all the translated templates related to a original template
	function get_translated_templates($template_id = NULL) {
		if (empty($template_id)) return FALSE;
		$mailing = $this->load->database('mailing', TRUE);
		$mailing->select('mailing_lang.iso_code as iso_code,subject,html', FALSE);
		$mailing->where('mailing_translate.active', 1);
		$mailing->where('mailing_lang.active', 1);
		$mailing->where('mailing_translate.mailing_template_id', $template_id);
		$mailing->join('mailing_lang', 'mailing_translate.mailing_lang_id = mailing_lang.mailing_lang_id', 'inner');
		$query = $mailing->get('mailing_translate');
		if ($query && $query->num_rows > 0 ) return $query->result(); else return FALSE;
	}
	// returns templates associated with the email
	function assign_template_to_mail($template = NULL,$email = NULL) {
		if ( empty($template) || empty($email)) return FALSE;
		$template_webmaster_id = $template->webmaster_id;
		$template_niche_id = $template->niche_id;
		$client_webmaster_id = $email->webmaster_id;
		$client_niche_id = $email->niche_id;
		//conditions to assign template to client
		$template_without_webmaster = FALSE;
		$template_without_niche = FALSE;
		$same_webmaster = FALSE;
		$same_niche     = FALSE;
		//flag that allows or not to return the template
		$return_template = FALSE;
		if ($template_webmaster_id === $client_webmaster_id ) $same_webmaster = TRUE;
		if ($template_niche_id === $client_niche_id ) $same_niche = TRUE;
		if ( ! $template_webmaster_id || empty($template_webmaster_id))  $template_without_webmaster = TRUE;
		if ( ! $template_niche_id || empty($template_niche_id))  $template_without_niche = TRUE;
		// set the correct template and language
		if ($template_without_webmaster && $template_without_niche) $return_template = TRUE;
		elseif ($template_without_webmaster && ( $client_niche_id == 19 && $template_without_niche ) ) $return_template = TRUE;
		elseif ($template_without_webmaster && $same_niche) $return_template = TRUE;
		elseif ($template_without_webmaster && $template_niche_id == 19) $return_template = TRUE;
		elseif ($same_webmaster && $template_without_niche) $return_template = TRUE;
		elseif ($same_webmaster && ( $client_niche_id == 19 && $template_without_niche )) $return_template = TRUE;
		elseif ($same_webmaster && $same_niche) $return_template = TRUE;
		elseif ($same_webmaster && $template_niche_id == 19) $return_template = TRUE;
		if ($return_template) return $template; else return FALSE;
	}
	// get new free emails from table emails_collected
	function fetchmails($mailing_group_id = NULL,$only_webmasters = NULL) {
		if (empty($mailing_group_id)) return FALSE;
		$index = $this->get_stored_var('mailserver_automailing');
		if ( ! empty($index)) {
			$index = unserialize($index);
			if (isset($index[$mailing_group_id])) $this->db->where('emails_stack_id > ', $index[$mailing_group_id], false);
		} else $index = array();
		$this->db->select('*');
		$this->db->join('emails_address','emails_address.emails_address_id = emails_stack.emails_address_id','inner');
        if( ! empty($only_webmasters) && is_array($only_webmasters) ) $this->db->where_in('webmaster_id',$only_webmasters);
		$internal_limit = $this->config->item('emails_import_limit');
		$this->db->limit($internal_limit);
		$query = $this->db->get('emails_stack');
		var_dump($this->db->last_query());
		if ($query && $query->num_rows > 0 ) {
			$result = $query->result();
			$last = end($result);
			reset($result);
			$index[$mailing_group_id] = $last->emails_stack_id;
			$this->set_stored_var('mailserver_automailing',serialize($index));
			//valid_email
			$valid_domain_emails = array();
			$invalid_domain_emails_counter = 0;
			foreach($result as $row) {
				$address = $row->address;
				if (valid_email($address, TRUE)) $valid_domain_emails[] = $row;
				else $invalid_domain_emails_counter+= 1;
			}
			$this->log->write_log('MAILING','caught '.$invalid_domain_emails_counter.' invalid emails');
			return $valid_domain_emails;
		} else return FALSE;
	}
	// get current timestamp from the mailserver
	function get_remote_timestamp(){
		$query = $this->db->query("SELECT UNIX_TIMESTAMP() as remotetime");
		if ($query && $query->num_rows > 0 ) {
			return $query->row()->remotetime;
		} else return FALSE;
	}
	// insert to emails the mails parsed through the groups and templates
	function send_mailserver_procedure($emails_queued = array()) {
		if ( count($emails_queued) != 0 ) {
			//array of data to insert into email data
			$emails_data = new stdClass;
			$emails_data->emails_address_id = $emails_queued->emails_address_id;
			$emails_data->email_collected_id = $emails_queued->email_collected_id;
			$emails_data->webmaster_id = $emails_queued->webmaster_id;
			$emails_data->niche_id = $emails_queued->niche_id;
			$emails_data->mailing_group_id = $emails_queued->mailing_group_id;
			$emails_data->mailing_template_id = $emails_queued->mailing_template_id;
			$emails_data->lang_iso = $emails_queued->lang_iso;
			//array of data to insert into send queue
			$emails_queue = new stdClass;
			$emails_queue->emails_data_id = 'id from previous query';
			$emails_queue->timeline = $emails_queued->timeline;
			$emails_queue->status_id = $emails_queued->status_id;
			//insert data into emails data and get the row id
			if ($this->db->insert('emails_data',$emails_data)) {
				$emails_queue->emails_data_id = $this->db->insert_id();
                                var_dump($emails_queue->emails_data_id);
                                var_dump($emails_queue);
				//insert the needed data into sending queue
				$this->db->insert('emails_queue',$emails_queue);
			} else return FALSE;
		}
	}
	// get all niches active
	// get all niches active
	function all_niches() {
		$mailing = $this->load->database('mailing', TRUE);
		$query = $mailing->get('niche');
		if ($query && $query->num_rows > 0) return $query->result(); else return FALSE;
	}
	// get all mailing active
	function all_mailings($include_templates = FALSE) {
		$mailing = $this->load->database('mailing', TRUE);
		$mailing->where('active' , 1);
		$query = $mailing->get('group');
		if ($query && $query->num_rows > 0) {
			$result = $query->result();
			// get the templates for each mailing_group if TRUE
			if ($include_templates) {
				foreach($result as &$item) {
					$mailing_group_id = $item->mailing_group_id;
					$item->templates = $this->get_templates($mailing_group_id);
				}
			}
			return $result;
		} else return FALSE;
	}
	// get all mailing active
	function all_mailing_templates($mailing_group_id = NULL) {
		if ( empty($mailing_group_id)) return FALSE;
		$mailing = $this->load->database('mailing', TRUE);
		$mailing->select('mailing_template.*', FALSE);
		$mailing->join('mailing_template','mailing_template.mailing_template_id = mailing_group_templates.mailing_template_id','inner');
		$mailing->where('mailing_group_templates.mailing_group_id' , $mailing_group_id);
		$mailing->where('mailing_template.active' , 1);
		$query = $mailing->get('mailing_group_templates');
		if ($query && $query->num_rows > 0) return $query->result(); else return FALSE;
	}
    // get the mailing_lang_id with the iso_code
	function get_lang_id($iso_code = NULL) {
		if (empty($iso_code)) return FALSE;
		$mailing = $this->load->database('mailing', TRUE);
		$mailing->select('mailing_lang_id');
		$mailing->where('iso_code', $iso_code);
		$mailing->where('active', 1);
		$query = $mailing->get('mailing_lang');
		if ($query && $query->num_rows > 0 ) {
			$row = $query->row();
			return $row->mailing_lang_id;
		}
	}
	// verify if a template have translation or not
	function is_translation($mailing_template_id = NULL, $iso_code = NULL) {
		$mailing = $this->load->database('mailing', TRUE);
		// possible return values
		// 0 -> doesn't exists any template at all
		// 1 -> exists only the original template
		// 2 -> exists the translated template
		// 3 -> language is not active or doesn't exist
		// 4 -> the params are invalid
		if ($mailing_template_id && $iso_code) {
			$mailing->where('mailing_template_id', $mailing_template_id);
			$mailing->where('default_lang_iso', $iso_code);
			$query = $mailing->get('mailing_template');
			//if it exists in table `mailing_template` with this iso_code then is not a translation
			if ($query && $query->num_rows > 0 ) return 1;
			//so it is a translation and lets get the mailing_lang_id
			$mailing_lang_id = $this->get_lang_id($iso_code);
			if ($mailing_lang_id) {
				$mailing->select('mailing_translate_id');
				$mailing->where('mailing_template_id', $mailing_template_id);
				$mailing->where('mailing_lang_id', $mailing_lang_id);
				$query = $mailing->get('mailing_translate');
				if ($query && $query->num_rows > 0 ) return 2; else return 0;
			} else return 3;
		} else return 4;
	}
	//procedure to check if a template exists for a specifica language
	function exist_template($mailing_template_id = NULL,$iso_code = NULL) {
		$mailing = $this->load->database('mailing', TRUE);
		if ($mailing_template_id && $iso_code) {
			$mailing_lang_id = $this->get_lang_id($iso_code);
			if ($mailing_lang_id) {
				$mailing->select('mailing_translate_id');
				$mailing->where('mailing_template_id', $mailing_template_id);
				$mailing->where('mailing_lang_id', $mailing_lang_id);
				$query = $mailing->get('mailing_translate');
				//if there is a translate template then the client is foreign (IT,GB,ES,...)
				if ($query && $query->num_rows > 0 ) return TRUE;
				else {
					$mailing->select('mailing_template_id');
					$mailing->where('mailing_template_id', $mailing_template_id);
					$mailing->where('default_lang_iso', $iso_code);
					$query = $mailing->get('mailing_template');
					//if there is a template here then the client is native FR
					if ($query && $query->num_rows > 0 ) return TRUE;
					else return FALSE;
				}
			} else return FALSE;
			//there is no template at all with $mailing_template_id associated to the $iso_code
			return FALSE;
		} else return FALSE;
	}
	// get a template translation if exists or get the template with the default language
	function translate($mailing_template_id = NULL,$iso_code = NULL, $return_only = FALSE) {
		$mailing = $this->load->database('mailing', TRUE);
		// possible return values from method is_translation
		// 0 -> doesn't exists any template at all
		// 1 -> exists only the original template
		// 2 -> exists the translated template
		// 3 -> language is not active or doesn't exist
		// 4 -> the params are invalid
		if ($mailing_template_id && $iso_code) {
			$is_translation = $this->is_translation($mailing_template_id, $iso_code);
			if ( $is_translation == 1) {
				//is the original
				$mailing->where('mailing_template_id', $mailing_template_id);
				$query = $mailing->get('mailing_template');
				if ($query && $query->num_rows > 0 ) {
					//only return boolean FALSE the rest emails_model handles it
					if ($return_only) return FALSE;
					return $query->row();
				}
			} elseif ($is_translation == 2) {
				//then it is a translation
				$mailing_lang_id = $this->get_lang_id($iso_code);
				$mailing->where('mailing_template_id', $mailing_template_id);
				$mailing->where('mailing_lang_id', $mailing_lang_id);
				$query = $mailing->get('mailing_translate');
				if ($query && $query->num_rows > 0 ) return $query->row(); else return FALSE;
			} else return FALSE;
		} else return 4;
	}
	// insert into emails_stack data from external sources
	function import_emails_stack($params = NULL) {
		if ( empty($params)) return FALSE;
		$this->load->model('emails_model');
		$emails_address_id = $this->emails_model->add_address($params->address);
		if ($emails_address_id) {
			$this->db->set('email_collected_id', $params->email_collected_id);
			$this->db->set('webmaster_id', $params->webmaster_id);
			$this->db->set('niche_id', $params->niche_id);
			$this->db->set('lang_iso', $params->lang_iso);
			$this->db->set('emails_address_id', $emails_address_id);
			return $this->db->insert('emails_stack');
		} else return FALSE;
	}
	/***************************************************************/
	// obtains a stored var value (and extra fields if required)
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