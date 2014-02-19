<?php
class Setup_model extends CI_Model {
	var $table = 'setup';
	function __construct(){
		parent::__construct();
    }
	// maximum of accounts for a mailcreator in a month for a specific host
	function max_accounts_per_month() {
		$this->db->where('var','ACCOUNTS_MONTH');
		$query = $this->db->get('setup');
		if($query->num_rows() == 0) return 0;
		return $query->row()->value;
	}
    // maximum of accounts for a mailcreator
	function max_accounts_per_host($host_id = NULL) {
		if (empty($host_id)) return FALSE;
		$this->db->where('host_id',$host_id);
		$this->db->where('var','ACCOUNTS_IP');
		$query = $this->db->get('setup');
		if($query->num_rows == 0) return 0;
		return $query->row()->value;
	}
	function max_ip_per_host($host_id = NULL) {
		if (empty($host_id)) return FALSE;
		$this->db->where('host_id',$host_id);
		$this->db->where('var','ACCOUNTS_IP_HOST');
		$query = $this->db->get('setup');
		if($query->num_rows() == 0) return 0;
		return $query->row()->value;
	}
	function accounts_interval($host_id = NULL) {
		if (empty($host_id)) return FALSE;
		$this->db->where('host_id',$host_id);
		$this->db->where('var','ACCOUNTS_INTERVAL');
		$query = $this->db->get('setup');
		if ($query && $query->num_rows > 0) return $query->row()->value;
		else return 86400;
	}
	function accounts_rules($host_id = NULL) {
		if (empty($host_id)) return FALSE;
		$this->db->select('var,value');
		$this->db->where('host_id',$host_id);
		$query = $this->db->get('setup');
		if ($query && $query->num_rows > 0 ) {
			$fields = new stdClass;
			foreach($query->result() as $row) {
				$fields->{$row->var} = $row->value;
			}
			return $fields;
		} else return FALSE;
	}
	// setter methods
	// set the limit of accounts for this mailprovider per month
	function set_accounts_host($host_id = NULL, $accounts_host = NULL) {
		if ( ! isset($host_id) || ! isset($accounts_host)) return FALSE;
		$query = "INSERT INTO setup (var, value, host_id) VALUES ('ACCOUNTS_HOST', ?, ?)";
		$query .= " ON DUPLICATE KEY UPDATE var = 'ACCOUNTS_HOST', value = ?";
		$this->db->query($query, array($accounts_host, $host_id, $accounts_host));
	}
	function set_accounts_ip_host($host_id = NULL, $accounts_ip_host = NULL) {
		if ( ! isset($host_id) || ! isset($accounts_ip_host)) return FALSE;
		// set the limit of ips for this mailprovider per day
		$query = "INSERT INTO setup (var, value, host_id) VALUES ('ACCOUNTS_IP_HOST', ?, ?)";
		$query .= " ON DUPLICATE KEY UPDATE var = 'ACCOUNTS_IP_HOST', value = ?";
		$this->db->query($query, array($accounts_ip_host, $host_id, $accounts_ip_host));
	}
	// set the limit of accounts per ip for this mailprovider
	function set_accounts_ip($host_id = NULL, $accounts_ip = NULL) {
		if ( ! isset($host_id) || ! isset($accounts_ip)) return FALSE;
		$query = "INSERT INTO setup (var, value, host_id) VALUES ('ACCOUNTS_IP', ?, ?)";
		$query .= " ON DUPLICATE KEY UPDATE var = 'ACCOUNTS_IP', value = ?";
		$this->db->query($query, array($accounts_ip, $host_id, $accounts_ip));
	}
	// set the release interval for the ips locked by this mailprovider
	function set_accounts_interval($host_id = NULL, $accounts_interval = NULL) {
		if ( ! isset($host_id) || ! isset($accounts_interval)) return FALSE;
		$query = "INSERT INTO setup (var, value, host_id) VALUES ('ACCOUNTS_INTERVAL', ?, ?)";
		$query .= " ON DUPLICATE KEY UPDATE var = 'ACCOUNTS_INTERVAL', value = ?";
		$this->db->query($query, array($accounts_interval, $host_id, $accounts_interval));
	}
	// set the account as mass_sender ( like amazon,sendgrid,smtp,etc )
	function set_mass_sender($host_id = NULL, $mass_sender = NULL) {
		if ( ! isset($host_id) || ! isset($mass_sender)) return FALSE;
		$query = "INSERT INTO setup (var, value, host_id) VALUES ('MASS_SENDER', ?, ?)";
		$query .= " ON DUPLICATE KEY UPDATE var = 'MASS_SENDER', value = ?";
		$this->db->query($query, array($mass_sender, $host_id, $mass_sender));
		$this->log->write_log('OFFICE','set_mass_sender:'.$this->db->last_query());
	}
	// set the account to warmup the ip origin of the postfix server (whitelist)
	function set_whitelist($host_id = NULL, $whitelist = NULL) {
		if ( ! isset($host_id) || ! isset($whitelist)) return FALSE;
		// set the release interval for the ips locked by this mailprovider
		$query = "INSERT INTO setup (var, value, host_id) VALUES ('WHITELIST', ?, ?)";
		$query .= " ON DUPLICATE KEY UPDATE var = 'WHITELIST', value = ?";
		$this->db->query($query, array($whitelist, $host_id, $whitelist));
	}
	// set a time based flag for the dummy recipients (this is related to the mass_senders)
	function set_recipients_cycle($host_id = NULL, $recipients_cycle = NULL) {
		if ( ! isset($host_id) || ! isset($recipients_cycle)) return FALSE;
		// set the release interval for the ips locked by this mailprovider
		$query = "INSERT INTO setup (var, value, host_id) VALUES ('RECIPIENTS_CYCLE', ?, ?)";
		$query .= " ON DUPLICATE KEY UPDATE var = 'RECIPIENTS_CYCLE', value = ?";
		$this->db->query($query, array($recipients_cycle, $host_id, $recipients_cycle));
	}
	// set the ratio between real recipients and dummy recipients
	function set_recipients_ratio($host_id = NULL, $recipients_ratio = NULL) {
		if ( ! isset($host_id) || ! isset($recipients_ratio)) return FALSE;
		// set the release interval for the ips locked by this mailprovider
		$query = "INSERT INTO setup (var, value, host_id) VALUES ('RECIPIENTS_RATIO', ?, ?)";
		$query .= " ON DUPLICATE KEY UPDATE var = 'RECIPIENTS_RATIO', value = ?";
		$this->db->query($query, array($recipients_ratio, $host_id, $recipients_ratio));
	}
	// set the flag to use proxy or not
	function set_use_proxy($host_id = NULL, $use_proxy = NULL) {
		if ( ! isset($host_id) || ! isset($use_proxy)) return FALSE;
		// set the release interval for the ips locked by this mailprovider
		$query = "INSERT INTO setup (var, value, host_id) VALUES ('USE_PROXY', ?, ?)";
		$query .= " ON DUPLICATE KEY UPDATE var = 'USE_PROXY', value = ?";
		$this->db->query($query, array($use_proxy, $host_id, $use_proxy));
	}
	// set the flag to use
	function set_spam_report_type($host_id = NULL, $type = NULL) {
		if ( ! isset($host_id) || ! isset($type)) return FALSE;
		// set the release interval for the ips locked by this mailprovider
		$query = "INSERT INTO setup (var, value, host_id) VALUES ('SPAM_REPORT_TYPE', ?, ?)";
		$query .= " ON DUPLICATE KEY UPDATE var = 'SPAM_REPORT_TYPE', value = ?";
		$this->db->query($query, array($type, $host_id, $type));
	}
	// set the flag to use
	function set_spam_report_plugin($host_id = NULL, $plugin = NULL) {
		if ( ! isset($host_id) || ! isset($plugin)) return FALSE;
		// set the release interval for the ips locked by this mailprovider
		$query = "INSERT INTO setup (var, value, host_id) VALUES ('SPAM_REPORT_PLUGIN', ?, ?)";
		$query .= " ON DUPLICATE KEY UPDATE var = 'SPAM_REPORT_PLUGIN', value = ?";
		$this->db->query($query, array($plugin, $host_id, $plugin));
	}
}
