<?php
/* 
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */

/*
 * DROP TABLE IF EXISTS `mailserver`.`sent`;
CREATE TABLE  `mailserver`.`sent` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `host_id` int(10) unsigned NOT NULL DEFAULT '0',
  `login_id` int(10) unsigned NOT NULL DEFAULT '0',
  `emails_id` int(10) unsigned NOT NULL DEFAULT '0',
  `status` tinyint(3) unsigned NOT NULL DEFAULT '1',
  `created` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `modified` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00',
  `deleted` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;
*/
class Sent_model extends active {

	var $table = 'sent';
	var $columns = array(
		'sent_id' => '' ,
		'host_id' => 0 ,
		'login_id' => 0 ,
		'emails_id' => 0 ,
		'status_id' => 1 ,
		'created' => null ,
		'modified' => '0000-00-00 00:00:00' ,
		'deleted' => '0000-00-00 00:00:00' ,
		'bounce' => '' ,
		'track_key' => null ,
		'alive' => null
	);

	function Sent_model()
    {
        parent::Model();
		$this->load->database();
		$this->load->helper('array');
    }
	function getTrackers($id = false){

		$this->db->select('*');
		$this->db->where('login_id',$id);
		$this->db->where('status_id',1);
		$query = $this->db->get('emails');
		
		$result = $query->result_array();
		return array_arrange($result,'track_key');
		
	}
	function view($limit = false,$order = false)
	{
		$filter = array(	
			'host' => array('hostname' => 'hostname'),
		);
		//get($where = false,$filter = false,$limit = false,$order = false,$join = false)
		$result = $this->get(false,$filter,$limit,$order);
		return $result;
	}

}