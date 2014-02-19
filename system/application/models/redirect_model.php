<?php
/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.

CREATE TABLE IF NOT EXISTS `redirect` (
  `redirect_id` int(11) NOT NULL AUTO_INCREMENT,
  `url` varchar(250) DEFAULT NULL,
  `code` varchar(250) DEFAULT NULL,
  `status_id` int(11) NOT NULL DEFAULT '1',
  `emails_id` int(11) NOT NULL,
  `created` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `modified` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00',
  `deleted` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00',
  PRIMARY KEY (`redirect_id`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1 AUTO_INCREMENT=1 ;
 */
class Redirect_model extends CI_Model {

	var $table = 'redirect';
	function __construct(){
		parent::__construct();
		$this->load->helper('array');
    }
	function get_real_url($code = NULL) {
		if (empty($code)) return FALSE;
		$old_mailserver = $this->load->database('old_mailserver', TRUE);
		//get the link and emails_id by the hash code
		$old_mailserver->select('url,emails_id');
		$old_mailserver->where('code', $code);
		$query = $old_mailserver->get('redirect');
		if ($query && $query->num_rows > 0 ) return $query->row(); else return FALSE;
	}
	function set_fake_url($url = NULL,$item = NULL){
		if ( empty($url) || empty($item) ) return FALSE;
		$mailing_template_id = $item->mailing_template_id;
		$email_collected_id = $item->email_collected_id;
		$mailing_group_id = $item->mailing_group_id;
		$emails_id = $item->emails_id;
		$fake = md5($url.$emails_id);
		$this->db->where('code',$fake);
		$query = $this->db->get('redirect');
		if($query && $query->num_rows == 0) {
			$this->db->set(array(
				   'url' => $url,
				   'code' => $fake,
				   'emails_id' => $emails_id,
				   'email_collected_id' => $email_collected_id,
				   'mailing_group_id' => $mailing_group_id,
				   'mailing_template_id' => $mailing_template_id
		        ));
		        $this->db->insert('redirect');
		} else return $fake;
		return $fake;
	}
	function get_emails_id($redirect_code = NULL) {
		if (empty($redirect_code)) return FALSE;
		$old_mailserver = $this->load->database('old_mailserver', TRUE);
		$old_mailserver->select('emails_id');
		$old_mailserver->where('code',$redirect_code);
		$query = $old_mailserver->get('redirect');
		if ($query && $query->num_rows > 0 ) {
			$row = $query->row_array();
			return $row['emails_id'];
		} else return FALSE;
	}
}