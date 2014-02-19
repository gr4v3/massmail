<?php  if (!defined('BASEPATH')) exit('No direct script access allowed');
class Bounce {
    var $CI;
    function Bounce(){
        $this->CI = & get_instance();
        $this->CI->load->library('imap');
        $this->CI->load->library('vador');
        $this->CI->load->library('spam');
        //library to send the bounce email_collected ids
        $this->CI->load->model('emails_model');
        $this->CI->load->model('settings_model');
        $this->CI->load->model('logins_model');
        $this->CI->load->model('queue_model');
        $this->CI->load->model('rate_model');
        $this->CI->load->model('crawler_model');
        $this->CI->load->model('redirect_model');
        $this->CI->load->model('setup_model');
        // main mailserver config file - common to all mailserver instances
        $this->CI->load->config('mailserver');
        // config file containing the url to send the request from the Vador library
        $this->CI->load->config('vador');
        $this->CI->load->helper('email');
    }
    function execute($logins_id = NULL) {
        $system = $this->CI;
        $login = $system->logins_model->getloginforbounce(NULL, $logins_id);
        if ($login) {
                $system->queue_model->set($login);
                $system->logins_model->lock($login->login_id);
                $settings = $system->settings_model->get($login->host_id);
                $this->parse($login, $settings);
                $system->queue_model->set($login);
        } else $system->log->write_log('BOUNCE','************** Could not find an available login **************');
    }
    function parse_mailbox_item($stream, $items) {
        $emails = array();
        $system = $this->CI;
        if ( ! imap_ping($stream)) {
                $system->log->write_log('BOUNCE','connection to imap server timeout');
                return FALSE;
        }
        $system->log->write_log('BOUNCE','found #'.count($items).' unflagged emails.');
        foreach($items as $msg_num_id) {
            $raw_body = imap_body($stream, $msg_num_id);
            $raw_header = imap_header($stream, $msg_num_id);
            $headers = imap_rfc822_parse_headers($raw_body);
            //var_dump($raw_header);
            //var_dump($raw_body);
            $bounce_item = new stdClass;
            $bounce_item->complaint = FALSE;
            $bounce_item->true_bounce = FALSE;
            $bounce_item->recipient = FALSE;
            $bounce_item->message_id = FALSE;
            $bounce_item->references = FALSE;
            $bounce_item->subject = FALSE;
            $bounce_item->senderaddress = FALSE;
            //the regexp expression `Status` belongs to the bounce reports of amazon
            if ( preg_match('/Diagnostic-Code:(.*)(2|4|5)[0-9]{2}/', $raw_body, $preg_match_result)) {
                    //if the flow goes through here then in means that the bounce message didn't result in rfc822smtp message compatible
                    $match_smtp_result = current($preg_match_result);
                    if ( preg_match('/5[0-9]{2}/', $match_smtp_result)) {
                            $system->log->write_log('BOUNCE','found a hard bounce with code: "' .$match_smtp_result. '"');
                            $bounce_item->true_bounce = TRUE;
                    }
            }
            if (isset($headers->toaddress)) {
                    $address_array = current($headers->to);
                    $bounce_item->recipient = $address_array->mailbox.'@'.$address_array->host;
            }
            if (isset($headers->senderaddress)) {
                    $address_array = current($headers->sender);
                    $bounce_item->senderaddress = $address_array->mailbox.'@'.$address_array->host;
                    $pos = strpos($bounce_item->senderaddress, 'complaint');
                    if ($pos !== FALSE) {
                            $system->log->write_log('BOUNCE','found a complain from a mass-sender.');
                            $bounce_item->complaint = TRUE;
                            $bounce_item->true_bounce = FALSE;
                    }
            }
            if (isset($headers->references)) $bounce_item->references = $headers->references;
            else if (isset($raw_header->references)) $bounce_item->references = $raw_header->references;
            if (isset($headers->message_id)) $bounce_item->message_id = $headers->message_id;
            if (isset($raw_header->subject)) $bounce_item->subject = $raw_header->subject;
            else if (isset($headers->subject)) $bounce_item->subject = $headers->subject;

            $pos = strpos(trim($bounce_item->subject), 'unsubscribe');
            if ($pos !== FALSE) {
                    $system->log->write_log('BOUNCE','found an unsubscription through list-unsubscribe.');
                    $bounce_item->message_id = trim($raw_body);
                    $bounce_item->complaint = FALSE;
                    $bounce_item->true_bounce = TRUE;
                    $bounce_item->recipient = trim($raw_header->fromaddress, '<>');
            }
            $pos = strpos(trim($bounce_item->subject), 'complaint');
            if ($pos !== FALSE) {
                    $system->log->write_log('BOUNCE','found a complaint.');
                    $bounce_item->complaint = TRUE;
                    $bounce_item->true_bounce = FALSE;
            }
            $emails[] = $bounce_item;
            //imap_delete($stream, $msg_num_id);
            $status = imap_setflag_full($stream, $msg_num_id, "\\Seen \\Flagged");
        }
        //imap_expunge($stream);
        return $emails;
    }
    function unflagged($stream = NULL) {
        $system = $this->CI;
        if ($stream) {
                $system->log->write_log('BOUNCE',' Bounce_model::unflagged');
                $emails = array();
                if ( ! $stream) return $emails;
                // Inbox MailBox (yahoo stuff)
                $inbox = imap_search($stream, 'UNSEEN');
                //$inbox = imap_search($stream, 'SUBJECT "Benvindo a beruby"');
                if ( is_array($inbox)) {
                        $result = $this->parse_mailbox_item($stream, $inbox);
                        if ($result) $emails = $emails + $result;
                }
                // Bulk MailBox (yahoo stuff)
                $list = imap_getmailboxes($stream, '{'.$system->config->item('imap_server').'}', "*");
                if (is_array($list)) {
                        foreach ($list as $val) {
                                $mailbox_name = imap_utf7_decode($val->name);
                                if (strpos($mailbox_name, 'Bulk Mail')) {
                                        $system->log->write_log('BOUNCE',' Bulk Mail mailbox detected. This is probably a yahoo account.');
                                        $mbox_reopen = imap_reopen($stream, "$mailbox_name");
                                        if ($mbox_reopen) {
                                                $inbox = imap_search($stream, 'UNSEEN');
                                                if ( is_array($inbox)) {
                                                        $result = $this->parse_mailbox_item($stream, $inbox);
                                                        if ($result) $emails = $emails + $result;
                                                }
                                        }
                                        break;
                                }
                        }
                } else {
                        echo "imap_getmailboxes failed: " . imap_last_error() . "\n";
                }
                imap_close($stream);
                if ( empty($emails)) $system->log->write_log('BOUNCE',' No unflagged emails to process.');
                return $emails;
        }
    }
    function imap($login = NULL, $settings = NULL) {
        if (empty($login) || empty($settings)) return FALSE;
        $system = $this->CI;
        $imap_settings = $settings['imap'];
        $system->log->write_log('BOUNCE',' Bounce_model::imap');
        $system->log->write_log('BOUNCE',' connecting to '.$login->hostname.'.');
        //login data for the imap server
        $system->config->set_item('imap_server', $imap_settings['host']['value']);
        $system->config->set_item('imap_flags', $imap_settings['service_flags']['value']);
        $system->config->set_item('imap_port', $imap_settings['port']['value']);
        $system->config->set_item('imap_mailbox', TRUE);
        if ($system->imap->connect($login->login, $login->pass, $imap_settings['host']['value'])) {
                $imap_errors = imap_errors();
                if ($imap_errors) {
                        $system->log->write_log('ERROR','errors occurred: '.print_r($imap_errors, TRUE));
                        $imap_error_state = $this->parse_imap_errors($imap_errors);
                        //if TRUE then the connection to the imap server is denied.
                        if ($imap_error_state) {
                                $system->rate_model->bounce_fail($login->login_id);
                                $system->logins_model->lock($login->login_id, 9);
                                $system->log->write_log('BOUNCE',' login:'.$login->login_id. ' blocked');
                                return FALSE;
                        }
                }
                $system->log->write_log('BOUNCE','connection to IMAP server success.');
                $system->rate_model->bounce_success($login->login_id);
                $emails = $this->unflagged($system->imap->stream);
                return $emails;
        } else return FALSE;
    }
    function pop($login = NULL, $settings = NULL) {
        if (empty($login) || empty($settings)) return FALSE;
        $system = $this->CI;
        $system->log->write_log('BOUNCE','connection to POP server success.');
    }
    function verify($login_item = NULL) {
        if (empty($login_item)) return FALSE;
        $system = $this->CI;
        $login_id = $login_item->login_id;
        $host_id  = $login_item->host_id;
        //get the imap settings for the host
        $settings = $system->settings_model->get($host_id);
        if ( isset($settings['imap'])) {
                $settings = $settings['imap'];
                //split the email of the user for the IMAP string connection
                $system->config->set_item('imap_server', $settings['host']['value']);
                $system->config->set_item('imap_flags', $settings['service_flags']['value']);
                $system->config->set_item('imap_port', $settings['port']['value']);
                $system->config->set_item('imap_mailbox', TRUE);
                //$user = NULL, $pass = NULL, $server = NULL
                $connected = $system->imap->connect($login_item->login, $login_item->pass, $settings['host']['value']);
                if ($connected) {
                        $imap_errors = imap_errors();
                        if ($imap_errors) {
                                $system->log->write_log('CHECK','errors occurred: '.print_r($imap_errors, TRUE));
                                $system->rate_model->bounce_fail($login_id);
                                $system->logins_model->lock($login_id, 9);
                                $system->log->write_log('CHECK',' login:'.$login_id. 'blocked');
                                return FALSE;
                        } else {
                                $system->log->write_log('CHECK','connection to IMAP success for login:'.$login_id);
                                $inbox_emails = imap_search($system->imap->stream, 'UNSEEN');
                                if ( is_array($inbox_emails)) {
                                        $amazon_dummy_emails = 0;
                                        foreach($inbox_emails as $msg_num_id) {
                                                $raw_header = imap_header($system->imap->stream, $msg_num_id);
                                                if ( isset($raw_header->references)) {
                                                        $emails_data_id = $this->parse_references_header($raw_header->references);
                                                        //$emails_data_id is still valid with value 0. these are the dummy emails that the accounts recieve from amazon
                                                        if ($emails_data_id == 0) {
                                                                //this is a dummy email from amazon so lets delete it from mailbox
                                                                $amazon_dummy_emails+= 1;
                                                                imap_delete($system->imap->stream, $msg_num_id);
                                                        }
                                                }
                                        }
                                        $system->log->write_log('CHECK','detected '.$amazon_dummy_emails.' dummy emails from amazon.');
                                        //lets see if its a yahoo account
                                        // Bulk MailBox (yahoo stuff)
                                        $list = imap_getmailboxes($system->imap->stream, '{'.$system->config->item('imap_server').'}', "*");
                                        if (is_array($list)) {
                                                foreach ($list as $key => $val) {
                                                        $mailbox_name = imap_utf7_decode($val->name);
                                                        if (strpos($mailbox_name, 'Bulk Mail')) {
                                                                $system->log->write_log('CHECK',' Bulk Mail mailbox detected. This is probably a yahoo account.');
                                                                $mbox_reopen = imap_reopen($system->imap->stream, "$mailbox_name");
                                                                if ($mbox_reopen) {
                                                                        $inbox_emails = imap_search($system->imap->stream, 'UNSEEN');
                                                                        if ( is_array($inbox_emails)) {
                                                                                $amazon_dummy_emails = 0;
                                                                                foreach($inbox_emails as $msg_num_id) {
                                                                                        $raw_header = imap_header($system->imap->stream, $msg_num_id);
                                                                                        if ( isset($raw_header->references)) {
                                                                                                $emails_data_id = $this->parse_references_header($raw_header->references);
                                                                                                //$emails_data_id is still valid with value 0. these are the dummy emails that the accounts recieve from amazon
                                                                                                if ($emails_data_id == 0) {
                                                                                                        //this is a dummy email from amazon so lets delete it from mailbox
                                                                                                        $amazon_dummy_emails+= 1;
                                                                                                        imap_delete($system->imap->stream, $msg_num_id);
                                                                                                }
                                                                                        }
                                                                                }
                                                                                $system->log->write_log('CHECK','detected '.$amazon_dummy_emails.' dummy emails from mass_sender accounts.');
                                                                        }
                                                                }
                                                                break;
                                                        }
                                                }
                                        } else echo "imap_getmailboxes failed: " . imap_last_error() . "\n";
                                }
                                return imap_close($system->imap->stream, CL_EXPUNGE);
                        }
                } else return FALSE;
        } else return FALSE;
    }
    function parse($login = NULL, $settings = NULL){
        if (empty($login) || empty($settings)) return FALSE;
        $system = $this->CI;
        $system->log->write_log('BOUNCE','************** bounce procedure begin *********************');
        $system->log->write_log('BOUNCE','server '.$login->hostname.' selected to detect bounce mails.');
        $system->log->write_log('BOUNCE','user login#'.$login->login_id.' selected to auth procedure.');
        if ( isset($settings['imap'])) $bounce_items = $this->imap($login, $settings);
        else if ( isset($settings['pop'])) $bounce_items = $this->pop($login, $settings);
        // check if this account have spam reports to recieve
        $accounts_rules = $system->setup_model->accounts_rules($login->host_id);
        if ($accounts_rules && ! empty($accounts_rules->SPAM_REPORT_TYPE) && $accounts_rules->SPAM_REPORT_TYPE == 1) {
                $system->spam->plugin($accounts_rules->SPAM_REPORT_PLUGIN);
                // get a list of spam reports for today ?
                // send in parameter the account details so the plugin can see where to look for spam reports
                $spam_reports_list = $system->spam->view((array) $login);
                if ( ! empty($spam_reports_list)) {
                        $system->log->write_log('BOUNCE','found ' . count($spam_reports_list) . ' spam reports. ');
                        foreach($spam_reports_list as $spam_item) {
                                $address = $spam_item->email;
                                $client_address_id = $system->emails_model->get_email_address_id($address);
                                if ($client_address_id) {
                                        // get all the emails_data items from this emails_address_id
                                        $emails_data_items = $system->emails_model->get_email_items_by_address_id($client_address_id);
                                        if ($emails_data_items) {
                                                // just get the first item to set it as bounce
                                                $first_emails_data_item = current($emails_data_items);
                                                $system->log->write_log('BOUNCE','client: ' . $first_emails_data_item->emails_data_id);
                                                if ($first_emails_data_item) {
                                                        // set the complaint state into stats
                                                        $system->crawler_model->parse_email_item($first_emails_data_item,array('complaints' => 1));
                                                        $system->emails_model->set_email_bounce($first_emails_data_item->emails_data_id);
                                                        $system->emails_model->remove_bounces($first_emails_data_item->emails_data_id);
                                                        $login->email = $address;
                                                        $system->spam->delete((array) $login);
                                                }
                                        }
                                }
                        }
                } else $system->log->write_log('BOUNCE','didn\'t found any spam reports. ');
        }
        // no bounce mails found but the rules for the bounce procedure still goes!
        if ( empty($bounce_items)) {
                $system->logins_model->unlock($login->login_id);
                $system->log->write_log('BOUNCE','************** bounce procedure end *********************');
                return FALSE;
        }
        //array for the vador bounced email_collected ids
        $email_collected_bounce = array();
        foreach ($bounce_items as $bounce_item) {
                /*
                        $bounce_item->complaint = FALSE;
                        $bounce_item->true_bounce = FALSE;
                        $bounce_item->recipient = FALSE;
                        $bounce_item->message_id = FALSE;
                        $bounce_item->references = FALSE;
                        $bounce_item->subject = FALSE;
                        $bounce_item->senderaddress = FALSE;
                        $emails_data_id    = FALSE;
                */
                $emails_data_id = FALSE;
                //check first if the bounce is an email from mass_sender to mailserver account
                if ($bounce_item->recipient && valid_email($bounce_item->recipient, TRUE)) {
                        $login_item = $system->logins_model->get_login_by_email($bounce_item->recipient);
                        if ($login_item) {
                                $system->log->write_log('BOUNCE','got a whitelist bounce with login_id:' . $login_item->login_id );
                                //1st param: the login_id of the account
                                //2nd param: the type pf time-based flag in queue table
                                //3rd param: the status_id to set (1:active, 2:busy, 9:inactive)
                                $system->queue_model->lock_by_login_id($login_item->login_id, 'fake', 9);
                                continue; // lets pass to the next $bounce_item cause this one is already processed.
                        }
                }
                // try to get the emails_data_id of the bounce
                // first by the header called References
                // and then by the header Message-ID
                if ( ! empty($bounce_item->references)) {
                        $emails_data_id = $this->parse_references_header($bounce_item->references);
                        $system->log->write_log('BOUNCE','got the mail item by the reference header: ' . $emails_data_id);
                } else if ( ! empty($bounce_item->message_id)) {
                        $emails_data_id = $this->parse_references_header($bounce_item->message_id);
                        $system->log->write_log('BOUNCE','got the mail item by the message-id header: ' . $emails_data_id);
                }
                if ($emails_data_id) {
                        $emails_item = $system->crawler_model->get_email_item($emails_data_id);
                        if ($emails_item) {
                                //lets fill the array with email_collected_ids that belongs to bounced emails
                                //dont pass the soft bounces and also the dummy email address (is the ones with email_collected_id = 1)
                                if ($emails_item->email_collected_id > 1 && $bounce_item->true_bounce && ! in_array($emails_item->email_collected_id, $email_collected_bounce)) {
                                        $email_collected_bounce[] = $emails_item->email_collected_id;
                                }
                                //set the bounce counter for the stats
                                $system->crawler_model->parse_bounce_item($emails_item,array('bounce' => 1));
                                //set this email address as bounced in table emails
                                $system->emails_model->set_email_bounce($emails_data_id);
                                $system->emails_model->remove_bounces($emails_data_id);
                                $system->log->write_log('BOUNCE','Email#'.$emails_data_id.' bounced.');
                                //if $complaint = TRUE then this bounce is a report from amazon with a complain
                                //i will index this complain by mailing,template,webmaster and date
                                if ($bounce_item->complaint) {
                                        $system->log->write_log('BOUNCE','Email#'.$emails_data_id.' complaint.');
                                        $system->crawler_model->parse_email_item($emails_item,array('complaints' => 1));
                                }
                        }
                }
        }
        //attempt to send the email_collected_ids corresponding to the bounced emails
        if ( ! empty($email_collected_bounce))  {
                $system->vador->execute($email_collected_bounce);
        }
        $system->logins_model->unlock($login->login_id);
        $system->log->write_log('BOUNCE',' all operations successfull! ');
        $system->log->write_log('BOUNCE','************** bounce procedure end *********************');
        return TRUE;
    }
    //get the references email header and fetch for the emails_data_id
    function parse_references_header($references_header = NULL) {
        if (empty($references_header)) return FALSE;
        $references_parsed = trim($references_header, '<>');
        $references_splitted = explode('@', $references_parsed);
        if (isset($references_splitted[0])) $references_emails_data_id = $references_splitted[0];
        else $references_emails_data_id = FALSE;
        return $references_emails_data_id;
    }
    //parse IMAP connection errors
    function parse_imap_errors($errors = NULL) {
        //set to TRUE if an imap error occured
        if (empty($errors)) return FALSE;
        $system = $this->CI;
        $imap_errors = $system->config->item('imap_errors');
        $imap_error_state = FALSE;
        foreach($imap_errors as $error_value => $error_permission) {
                if (strpos($error_value, current($errors))) {
                        $imap_error_state = TRUE;
                        break;
                }
        }
        return $imap_error_state;
    }
}