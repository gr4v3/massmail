<?php
/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */
class Spam extends CI_Controller {
    var $smtp_connection = FALSE;
    function __construct(){
        parent::__construct();
        $this->load->library('log');
        $this->load->helper('array');
        $this->load->helper('smtp');
        $this->load->helper('html2text');
        $this->load->model('emails_model');
        $this->load->model('logins_model');
        $this->load->model('settings_model');
        $this->load->model('crawler_model');
        $this->load->model('queue_model');
    }
    function emails_recover() {
        include getcwd() . '/system/application/libraries/Bounce.php';
        $this->load->library('emails_recover');
        $this->emails_recover->execute();
    }
}
