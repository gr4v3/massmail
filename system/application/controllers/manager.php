<?php
class Manager extends CI_Controller {
    function __construct() {
        parent::__construct();
        $this->load->helper('array');
        $this->load->library('log');
    }
	// procedure to check for bounces in mailboxes
    function mailing($login_id = NULL) {
        $this->load->library('bounce');
        $this->bounce->execute($login_id);
    }
	// procedure to send emails
    function templates($host_id = NULL,$emails_data_id = NULL ){
        $this->load->library('Send');
        $this->send->execute($host_id, $emails_data_id);
    }
    
    
    function addmailing() {
        
    }
    function addtemplate() {
        
    }
}
?>
