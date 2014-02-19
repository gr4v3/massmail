<?php
/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */
class Access_model extends CI_Model
{
	function __construct(){
		parent::__construct();
	}
	function verify_user($params = FALSE) {

		$this->db->select('*');
		if($params) $this->db->where($params);
		$query = $this->db->get('ip_access');
		return $query;
	}
	function ips_allready_reserved($user_id = FALSE) {
		$this->db->where('user_id',$user_id);
		$this->db->where('datelock IS NULL');
		$query = $this->db->get('ip_access');
		if ($query !== FALSE and $query->num_rows() > 0) return $query->result_array();
		else return FALSE;
	}
	function reserve_ips($ip = FALSE,$user_id = FALSE,$host_id = FALSE) {
		$sql = "insert into ip_access (ip,user_id,host_id) select '$ip',$user_id,host_id from host where status_id = 1 ";
		if($host_id) $sql.= " and host_id = $host_id ";
		$sql.= " on duplicate key update datelock = '0000-00-00 00:00:00'";
		return $this->db->query($sql);
	}
	function lock_ips($ip = FALSE,$user_id = FALSE,$host_id = FALSE) {

		$now = date('Y-m-d');
		$sql = "insert into ip_access (ip,user_id,host_id,datelock) select '$ip',$user_id,host_id,'$now' from host where status_id = 1 ";
		if($host_id) $sql.= " and host_id = $host_id ";
		$sql.= " on duplicate key update datelock = '$now'";
		return $this->db->query($sql);
	}
	function unlock_ips($ip = FALSE,$user_id = FALSE,$host_id = FALSE) {

		$this->db->where('datelock < ', date('Y-m-d', strtotime('-2 DAY')));
		$this->db->delete('ip_access');
	}
	function increment_account_limit($ip = FALSE,$user_id = FALSE,$host_id = FALSE) {

		$this->db->select('counter');
		$this->db->where('ip',$ip);
		$this->db->where('user_id',$user_id);
		$this->db->where('host_id',$host_id);
		$query = $this->db->get('ip_access');
		$row = 0;

		if ($query !== FALSE and $query->num_rows() > 0)
		{
		   $row_result = $query->row_array();
		   $row =  $row_result['counter'];
		}

		$this->db->where('ip',$ip);
		$this->db->where('user_id',$user_id);
		$this->db->where('host_id',$host_id);
		$this->db->update('ip_access',array('counter' => $row + 1));

	}
	function use_one_ip($user_id = FALSE,$host_id = FALSE) {

		$this->db->select("ip_access_id as id,ip,counter as nums,ip as text",FALSE);
		$this->db->where('user_id',$user_id);
		$this->db->where('host_id',$host_id);
		$this->db->where('counter < 20');
		$this->db->limit(5);
		$query = $this->db->get('ip_access');
		if ($query !== FALSE and $query->num_rows() > 0)
		{
		   return $query->result_array();
		} else return false;
	}
	function select_free_ips($user_id = FALSE,$host_id = FALSE,$limit = 20) {

		/*
		 * check for ip's not present in table ip_access. They will be inserted for a specific user,host and ip.
		*/
		$access = $this->ips_allready_reserved($user_id);
		$ip = $_SERVER['SERVER_ADDR'];
		if($access) return $access;

		$sql = " select * from available_ips ";
		$sql.= " left join ip_access using(ip) ";
		$sql.= " where ip_access.ip_access_id is null";
		$sql.= " and available_ips.owner = '$ip'";
		$sql.= " limit $limit";

		$query = $this->db->query($sql);
		$result = $query->result_array();

		foreach($result as $row) {
			$this->reserve_ips($row['ip'],$user_id,$host_id);
		}

	}
	function valid_remote_address($ip = FALSE,$user_id = FALSE) {
		$this->db->where('ip',$ip);
		$this->db->where('user_id',$user_id);
		$query = $this->db->get('ip_access');
		if ($query !== FALSE and $query->num_rows() > 0) return TRUE; else return FALSE;
	}
}
