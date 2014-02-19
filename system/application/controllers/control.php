<?php
class Control extends CI_Controller {
    function __construct() {
        parent::__construct();
        if ( ! $this->input->is_cli_request()) {
                //die('security warning! mailserver not running in CLI mode!');
        }
        $this->load->helper('array');
        $this->load->library('log');
    }
	// procedure to check for bounces in mailboxes
    function bounce($login_id = NULL) {
        $this->load->library('bounce');
        $this->bounce->execute($login_id);
    }
	// procedure to send emails
    function execute($host_id = NULL,$emails_data_id = NULL ){
        $this->load->library('Send');
        var_dump('control::execute pass!');
        $this->send->execute($host_id, $emails_data_id);
    }
	// procedure to test templates (sending a custom template to an address predefined by the user)
    function fakeinbox($address = NULL, $template = NULL) {
        $this->load->library('Send');
        $this->send->fakeinbox($address, $template);
    }
    // procedure to validate accounts recently created by mailcreators
    function check() {
        $this->load->library('bounce');
        $this->load->model('logins_model');
        $fresh_logins = $this->logins_model->get_accounts_to_verify();
        var_dump($fresh_logins);
        if ($fresh_logins) {
                $this->log->write_log('CHECK','got '.count($fresh_logins). ' accounts');
                $valid = 0;
                $failed = 0;
                foreach($fresh_logins as $login) {
                        $login_id = $login->login_id;
                        $pass = $this->bounce->verify($login);
                        if ($pass) {
                                $this->logins_model->lock($login_id,1);
                                $login->type = 'send';
                                $this->queue_model->set($login);
                                $login->type = 'get';
                                $this->queue_model->set($login);
                                $login->type = 'fake';
                                $this->queue_model->set($login);
                                $valid+= 1;
                        } else {
                                $this->logins_model->lock($login_id,9);
                                $failed+= 1;
                        }
                }
                $this->log->write_log('CHECK',"$valid valid accounts");
                $this->log->write_log('CHECK',"$failed invalid accounts");
        }
    }
    // procedure to fetch for new rss templates from remote seeders
    function rss() {
        $this->load->config('rss');
        $this->load->helper('feed');
        $this->load->helper('html2text');
        $rss_cache_file = $this->config->item('rss_cache_file');
        $rss_cache_limit = $this->config->item('rss_cache_limit');
        $rss_feeds = $this->config->item('rss_feeds');
        $this->log->write_log('RSS','*********************************************');
        $this->log->write_log('RSS',' checking for new templates from rss feeders');
        $rss = Feed::loadRss($rss_feeds[mt_rand(0,count($rss_feeds)-1)]);
        if ($rss) {
                $subject = $rss->item->title;
                $html = $rss->item->description;
                if ( ! empty($subject) || ! empty($html)) {
                        $template = array(
                                'subject' => utf8_decode(html2text(current($rss->item->title))),
                                'html' => utf8_decode(html2text(current($rss->item->description)))
                        );
                        if ( ! empty($template['html']) || ! empty($template['subject'])) {
                                $rss_cache_file_content = file_get_contents($rss_cache_file);
                                if ( ! empty($rss_cache_file_content)) $template_files_decoded = json_decode($rss_cache_file_content);
                                else $template_files_decoded = array();
                                // if the length of the cache is higher then the limit from config file then just shift
                                // and the cache will be refresh one-by-one
                                if (count($template_files_decoded) > $rss_cache_limit) array_shift($template_files_decoded);
                                // insert a new generated template to the cache array
                                $template_files_decoded[] = $template;
                                // convert the cache array to json
                                $template_files_encoded = json_encode($template_files_decoded);
                                $result = file_put_contents($rss_cache_file, $template_files_encoded);
                                if ($result) $this->log->write_log('RSS',' new template added in cache file');
                        } else $this->log->write_log('RSS',' the rss request didn\'t returned a valid html content');
                } else $this->log->write_log('RSS',' the http request for and rss xml resulted in error. No template fetched');
        } else $this->log->write_log('RSS',' the http request for and rss xml resulted in error. No template fetched');
        $this->log->write_log('RSS','*********************************************');
    }
    // procedure to validate the recipient domains when they enter the mailserver system
    function domains() {
        $this->load->helper('email');
        $this->load->model('crawler_model');
        $this->load->model('emails_model');
        $this->log->write_log('DOMAIN','******************************');
        $domains = $this->crawler_model->crawl_domains();
        $valid = 0;
        $invalid = 0;
        if ($domains) {
            $this->log->write_log('DOMAIN','caught '.count($domains). ' domains to validate.');
            foreach($domains as $item) {
                if (isDomainResolves($item->domain) && get_host($item->domain, TRUE)) {
                    $valid+= 1;
                    $this->emails_model->set_domain_active($item->emails_domain_id);
                } else {
                    // last check if the domain is really valid
                    $domain_alive = $this->emails_model->check_domain_alive($item->emails_domain_id);
                    if ($domain_alive) {
                            $valid+= 1;
                            $this->emails_model->set_domain_active($item->emails_domain_id);
                            continue;
                    }
                    $invalid+= 1;
                    $this->log->write_log('DOMAIN','domain:'.$item->domain. ' invalid ');
                    $this->emails_model->set_domain_inactive($item->emails_domain_id);
                }
            }
        }
        $this->log->write_log('DOMAIN',$valid.' valid domains.');
        $this->log->write_log('DOMAIN',$invalid.' invalid domains.');
        $this->log->write_log('DOMAIN','******************************');
    }
    // alternative procedure to get the beruby links
    function beruby() {
        include getcwd() . '/system/application/libraries/Bounce.php';
        $this->load->library('emails_recover');
        $this->emails_recover->execute();
    }
    // alternative procedure to get the 4life answers to the first email wave
    function bounce4life() {
        include getcwd() . '/system/application/libraries/Bounce.php';
        $this->load->library('bounce_4life');
        $this->bounce_4life->execute();
    }
    //include accounts 
    function accounts() {
        $this->load->model('emails_model');
        if (($handle = fopen("F:\www\macros\pt_ips.csv", "r")) !== FALSE) {
            while (($data = fgetcsv($handle, 1000, ",")) !== FALSE) {
                //echo "{ip:'".$data[3]."',name:'".utf8_decode($data[0])."',email:'".$data[1]."',pass:'".$data[2]."'},\n";
                $params = new stdClass;
                $params->host_id = 2;
                $params->name = utf8_decode($data[0]);
                $params->email = $data[1];
                $params->login = $data[1];
                $params->pass = $data[2];
                $params->ip = '127.0.0.1';
                $this->emails_model->db->insert('login', $params);
            }
            fclose($handle);
        }
    }
}