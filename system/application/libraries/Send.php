<?php
class Send {
    var $smtp_connection = FALSE;
    var $CI;
    function Send(){
        $this->CI = & get_instance();
        $this->CI->load->model('settings_model');
        $this->CI->load->model('setup_model');
        $this->CI->load->model('logins_model');
        $this->CI->load->model('hosts_model');
        $this->CI->load->model('emails_model');
        $this->CI->load->model('queue_model');
        $this->CI->load->model('rate_model');
        $this->CI->load->model('crawler_model');
        // common config files
        $this->CI->load->helper('smtp');
        $this->CI->load->helper('html2text');
        $this->CI->load->helper('debug');
        $this->CI->load->helper('array');
        $this->CI->load->helper('string');
        $this->CI->load->helper('email');
        $this->CI->load->library('parser');
        $this->CI->load->config('mailserver');
        $this->CI->load->config('rss');
    }
    //normal way to send emails
    function execute($host_id = FALSE, $emails_data_id = FALSE) {
        $system = $this->CI;
        $login = $system->logins_model->random($host_id);
        
        var_dump('send::execute pass!');
        
        if (empty($login)) {
                $system->log->write_log('INFO','************** Could not find an available login **************');
                return FALSE;
        }
        $system->log->write_log('INFO','************** send procedure begin *********************');
        $system->log->write_log('INFO','server '.$login->host_id.' selected for sending mails.');
        $system->log->write_log('INFO','user '.$login->login_id.':'.$login->ip.' selected to auth procedure.');
        $system->logins_model->lock($login->login_id);
        // set start of sending activity
        $system->queue_model->set($login);
        // check if the account is a mass_sender
        $success = FALSE;
        $accounts_rules = $system->setup_model->accounts_rules($login->host_id);
        /*****************************************************************************************************/
        /**** this is the special process when sending emails to mailserver logins for whitelist purposes ****/
        /*****************************************************************************************************/
        if ($accounts_rules) {
                if (isset($accounts_rules->WHITELIST) && $accounts_rules->WHITELIST == 1) {
                        $system->log->write_log('INFO','whitelist mode active!');
                        $this->fakerecipients($login, $login->send_limit);
                        $success = TRUE;
                } else {
                        /*************************************************************************/
                        /**** this is the normal process when sending emails to real clientes ****/
                        /*************************************************************************/
                        $system->log->write_log('INFO','normal mode active!');
                        $emails = $system->emails_model->get_new_emails($login->send_limit, $emails_data_id);
                        /*****************************************************************************************************/
                        /**** this is the special process for sending emails to mailserver logins for reputation purposes ***/
                        /*****************************************************************************************************/
                        if (isset($accounts_rules->MASS_SENDER) && $accounts_rules->MASS_SENDER == 1) {
                                $system->log->write_log('INFO','reputation mode active!');
                                if ( ! $emails) $emails_count = $login->send_limit;
                                else $emails_count = count($emails);
                                $this->fakerecipients($login, $emails_count);
                        }
                        if ( ! $emails || empty($emails)) {
                                $system->logins_model->unlock($login->login_id);
                                $system->queue_model->set($login);
                                $system->log->write_log('INFO','No new emails to send.');
                                $system->log->write_log('INFO','************** send procedure end *********************');
                                return FALSE;
                        }
                        $settings = $system->settings_model->get($login->host_id);
                        $success = FALSE;
                        //get config parameters from config file mailserver.
                        $success = $this->mailer($emails, $login, $settings);
                }
        }
        $system->logins_model->unlock($login->login_id);
        $system->queue_model->set($login);
        if($success) {
                $system->log->write_log('INFO',' all operations successfull! ');
                $system->log->write_log('INFO','************** send procedure end *********************');
                $system->rate_model->send_success($login->login_id);
                $system->rate_model->max_send($login->login_id,$success);
        } else {
                $system->log->write_log('INFO',' sending procedure failed! ');
                $system->logins_model->lock($login->login_id,9);
                $system->rate_model->send_fail($login->login_id);
                $system->log->write_log('INFO','************** send procedure end *********************');
        }
    }
    //when this method is used the mailserver usually sends the emails by remote connection to remote smtps.
    function mailer($emails = NULL, $login = NULL, $settings = NULL) {
        if ( empty($emails) || empty($login) || empty($settings) ) return FALSE;
        $system = $this->CI;
        //login and host settings to be used in current smtp session
        $params = new stdClass;
        $params->hostname = $login->domain;
        $params->secure   = $settings['smtp']['service_flags']['value'];
        $params->host     = $settings['smtp']['host']['value'];
        $params->port     = $settings['smtp']['port']['value'];
        $params->timeout  = $settings['smtp']['timeout']['value'];
        $params->username = $login->login;
        $params->password = $login->pass;
        $params->from     = $login->email;
        $params->name     = $login->name;
        $params->proxy    = $login->ip;
        //without the second parameter this will reset the numbers of mails sent by this login for this smtp session
        $system->logins_model->mails_sent($login->login_id);
        //numbers of mails sent in each smtp session
        $emails_sent_routine = 0;
        $emails_success = array();
        $emails_failure = array();
        $emails_orphans = array();
        $emails_total_to_send = count($emails);
        //start of sending loop procedure
        $this->smtp_connection = FALSE;
        do {
                $email_item = array_pop($emails);
                $mailing_template_id = $email_item->mailing_template_id;
                $email_item->domain = $login->domain;
                $email_item->email = $email_item->address;
                $email_item->emails_id = $email_item->emails_data_id;	//compatibility for the old templates
                $template = $system->emails_model->get_template_parsed($mailing_template_id,$email_item);
                if ($template) {
                        $template->mailing_template_id = $mailing_template_id;
                        $template->html = $this->PhantomLinks($template->html, $email_item, $login->domain);
                        //numbers of mails allowed to send in each smtp session
                        $emails_sent_routine+= 1;
                        $sending_process = FALSE;
                        //these indexes are used in the method mail of this class
                        $params->emails_data_id   = $email_item->emails_data_id;
                        $params->mailing_group_id = $email_item->mailing_group_id;
                        $params->domain           = $email_item->domain;
                        if ( $emails_sent_routine == $login->flood_refresh ) {
                                //if the account sent more then the flood refresh rule then logoff/login
                                $emails_sent_routine = 0;
                                //send the mail and close/open the smtp connection.
                                $sending_process = $this->mail($params,$email_item->address,$template,TRUE);
                                $system->log->write_log('INFO','refreshing the account '.$login->name.'#'.$login->login_id);
                        } else if($emails_sent_routine == $emails_total_to_send) {
                                //if the account sent all the emails then send last and logoff
                                $sending_process = $this->mail($params,$email_item->address,$template,TRUE);
                                $system->log->write_log('INFO','shutdown the account '.$login->name.'#'.$login->login_id);
                        } else {
                                //keep sending in the same smtp session
                                $sending_process = $this->mail($params,$email_item->address,$template,FALSE);
                        }
                        //if not empty or not false then the mail was successfully sent
                        //possible return values
                        //1 -> connection failed
                        //2 -> authentication failed
                        //3 -> account error
                        //4 -> sending failed
                        //5 -> sending success
                        /*
                                $return->error  #type string
                                $return->status #type boolean
                                $return->code   #type integer
                        */
                        if ($sending_process->status && $sending_process->code == 5) {
                                $email_item->message_id = trim($sending_process->error,'<>'); //in case of success the propriety error carries the message_id of the email
                                //this condition is to split the dummy emails from the real client emails
                                if ($email_item->emails_data_id) {
                                        $emails_success[] = $email_item;
                                }
                        } else {
                                $system->log->write_log('INFO',$sending_process->error);
                                $email_item->smtp_error = $sending_process->error;
                                if ($email_item->emails_data_id) {
                                        $emails_failure[] = $email_item;
                                }
                                if ($sending_process->code == 2) break;
                        }
                        //wait just a few microseconds before sending another mail
                        usleep($login->flood_sleep);
                } else {
                        // for this client lang_iso there is no template or even translated template to send
                        // so we will set status 3 on this mail
                        $emails_orphans[] = $email_item;
                }
        } while( ! empty($emails));
        //end of sending loop procedure
        //procedure:process the emails with success state
        foreach($emails_success as $email_item) {
                //this will set the mail with status 4
                $system->emails_model->success($email_item->emails_data_id, $email_item->message_id);
                //this will increase a counter for the stats
                $system->crawler_model->parse_email_item($email_item,array('sent' => 1));
        }
        //procedure:process the emails with failure state
        if ( ! empty($emails_failure)) {
                $system->emails_model->unlock_emails_lost($emails_failure);
        }
        if ( ! empty($emails)) {
                $system->emails_model->unlock_emails_lost($emails);
        }
        //procedure:process the emails with orphan state
        if ( ! empty($emails_orphans)) {
                foreach($emails_orphans as $email_item) {
                        $system->emails_model->block($email_item->emails_data_id, 3);
                }
                $system->log->write_log('INFO',count($emails_orphans) . ' emails changed to state 3');
        }
        $system->log->write_log('INFO',count($emails_success).' emails sent.');
        //with the second parameter this will set the numbers of mails sent by this login for this smtp session
        $system->logins_model->mails_sent($login->login_id, count($emails_success));
        //return true if balance between mails success and failure is positive
        if ( empty($emails_success) && ! empty($emails_failure)) return FALSE;
        else {
                $this->close_mail_ghost();
                $fakeresponse = $this->fakeinbox($login->email, $template);
                if ($fakeresponse) $system->log->write_log('INFO','fakeinbox procedure success!');
                if ( empty($emails_success)) return TRUE;
                else return count($emails_success);
        }
        return true;
    }
    // method to detect links inside a mail template
    function parseLink($tag = NULL, $attribute = NULL, $string = NULL) {
        if (empty($tag) || empty($attribute) || empty($string)) return FALSE;
        $tag_links = array();
        $plain_links = array();
        preg_match_all("/\b(?:(?:https?|ftp):\/\/|www\.)[-a-z0-9+&@#\/%?=~_|!:,.;]*[-a-z0-9+&@#\/%=~_|]/i", $string, $plain_links);
        if(empty($plain_links)) preg_match_all("/(<".$tag." .*?".$attribute.".*?=.*?\")(.*?)(\".*?>)/", $string, $tag_links);
        if(!empty($tag_links)) return array_merge($tag_links[2],current($plain_links));
        else return current($plain_links);
    }
    //create redirection links with the proxy domains and a md5 key
    function PhantomLinks($text = NULL, $item = NULL, $domain = NULL){
        if (empty($text) || empty($item) || empty($domain)) return FALSE;
        $system = $this->CI;
        $text = $system->parser->parse_generic($text, $item, TRUE);
        $result = $this->parseLink('a','href',$text);
        foreach($result as $link) {
                //index.php/url/{base64}
                $url = "http://$domain/index.php/url/index/".safe_base64_encode($link);
                $text = str_replace('"'.$link.'"','"'.$url.'"',$text);
        }
        return $text;
    }
    //this method handles the remote connection to an external smtp.
    //usually this method is called the the method `mailer` in this library.
    function mail($params = NULL, $recipient = NULL, $template = array(), $terminate = TRUE, $secondatempt = FALSE) {
        if (empty($params) || empty($recipient)) return FALSE;
        $system = $this->CI;
        $config_params = $system->config->item('params');
        $smtp_error_wait = $config_params['smtp_error_wait'];
        $smtp_use_proxy = $config_params['smtp_use_proxy'];
        $smtp_rules_config = $system->config->item('smtp_rules');
        //sender settings
        var_dump('control::send pass!');
        
        $smtp_template_vars = array(
                // details of the account that will send the mail
                'sender_name' => $params->name,
                'sender_email' => $params->from,
                'sender_user' => $params->username,
                'sender_pass' => $params->password,
                // details of the smtp remote server that the account will use
                'server_name' => $params->hostname,
                'server_host' => $params->host,
                'server_port' => $params->name,
                // details of the recipient
                'emails_data_id' => $params->emails_data_id,
                'mailing_group_id' => $params->mailing_group_id,
                'domain'      => $params->domain,
                'recipient_email' => $recipient
        );
        if ($smtp_use_proxy) $server_proxy = $params->proxy; else $server_proxy = FALSE;
        // get the smtp rules by the sender email domain
        $sender_domain = current(explode('.',end(explode('@', $smtp_template_vars['sender_email']))));
        // but first check if their is a generic rules or a specific rules according to the domain of the sender
        $smtp_rules = $smtp_rules_config['*'];
        foreach($smtp_rules_config as $rule_index => $rule_value) {
                if($rule_index != '*' && preg_match($rule_index, $sender_domain)) {
                        $smtp_rules = $rule_value;
                        break;
                }
        }
        //mail content
        $subject = $template->subject;
        if (empty($template->html)) {
                $return = new stdClass;
                $return->status = FALSE;
                $return->code   = 2;
                $return->error  = 'empty body in template';
                return $return;
        }
        $subject = '=?utf-8?b?'.base64_encode($subject).'?=';
        //possible return values
        //1 -> connection failed
        //2 -> authentication failed
        //3 -> account error
        //4 -> sending failed
        //5 -> sending success
        $return = new stdClass;
        $return->status = FALSE;
        $return->code   = FALSE;
        $return->error  = 'nothing happen';
        $mail = new SMTP();
        $mail->do_debug = 0;
        // if ($params->proxy) $mail->PROXY_IP = $params->proxy;
        // if there is no active smtp remove connection then lets create a new one
        if ( ! $this->smtp_connection) {
                $connection_status = $mail->Connect($params->host, $params->port);
                if ( ! $connection_status) {
                        // remove connection not possible.
                        // cancel all the sending process and block the account
                        $return->status = FALSE;
                        $return->code   = 1;
                        $return->error  = 'smtp connection failed';
                        return $return;
                }
                $system->log->write_log('INFO','connection to remote smtp host success!');
                $mail->Hello($params->name);
                // If it is a TLS connection then we have to send the command starttls
                if ($params->secure == 'tls') $mail->StartTLS();
                // authentication always required
                $auth_status = $mail->Authenticate($params->username, $params->password);
                
                if ( ! $auth_status) {
                        // smtp authentication not possible
                        // cancel only this time. The reason can be only temporary offline resources.
                        $return->status = FALSE;
                        $return->code   = 2;
                        $return->error  = 'smtp `AUTH LOGIN` command failed';
                        return $return;
                }
                $this->smtp_connection = $mail->smtp_conn;
        } else {
                $mail->smtp_conn = $this->smtp_connection;
        }
        // smtp command always required after authentication
        // the parameters needs to be parsed by the smtp rules config.There are smtp hosts that handles diferent syntax
        // get from smtp rules config the right syntax according to the type of domain of the sender
        $mail_from_parsed = $system->parser->parse_generic($smtp_rules['mail_from'], $smtp_template_vars, TRUE);
        
        $response = $mail->Command($mail_from_parsed, TRUE);
        
        //check if MAIL command was successfull
        if ($response == 421) {
                // it is a temporary smtp error. When can try again to connect and resent the mail for this recipient.
                $mail->Close();
                $this->smtp_connection = FALSE;
                $system->log->write_log('INFO','error:resources temporary unavailable. Let\'s try again in 1 second');
                usleep($smtp_error_wait);
                $secondatempt = TRUE;
                $mail->Command('rset');
                return $this->mail($params, $recipient, $template, $terminate, $secondatempt);
        } else if ($response != 250) {
                // this is a temporary smtp error. Cancel only this time and try again later with this account.
                $system->log->write_log('INFO','error:resources banned! Try again 24 hours later (mail_from) code:'.$response);
                $return->status = FALSE;
                $return->code   = 2;
                $return->error  = 'smtp `MAIL FROM` command failed';
                return $return;
        }
        // smtp command always required after smtp command `MAIL FROM`
        // the parameters needs to be parsed by the smtp rules config.There are smtp hosts that handles diferent syntax
        // get from smtp rules config the right syntax according to the type of domain of the sender
        $rcpt_to_parsed = $system->parser->parse_generic($smtp_rules['rcpt_to'], $smtp_template_vars, TRUE);
        $response = $mail->Command($rcpt_to_parsed, TRUE);
        if ($response != 250) {
                // this is a temporary smtp error. Cancel only this time and try again later with this account.
                $system->log->write_log('INFO','error:resources banned! Try again 24 hours later (rcpt_to) code:'.$response);
                $return->status = FALSE;
                $return->code   = 2;
                $return->error  = 'smtp `RCPT TO` command failed';
                return $return;
        }
        //send the smtp DATA command
        $response = $mail->Command("DATA", TRUE);

        if ($response == 354) {
                $now_timestamp = date('D, j M Y G:i:s');
                $boundary = '----' . md5($now_timestamp);
                $recipient_exploded = explode('@', $recipient);
                //if it is amazon process
                $recipient_name_raw = current($recipient_exploded);
                $recipient_domain = current(explode('.',end($recipient_exploded)));
                $random_abreviation = array('Mr','Sir','Miss','Dear','Mrs');
                shuffle($random_abreviation);
                $recipient_name_redefined = preg_replace('/[^A-Za-z| ]/', '', preg_replace('/[_|.]/',' ',$recipient_name_raw));
                // get the recipients smtp host rules from config if their exist at all
                $recipient_smtp_rules = $smtp_rules['recipient_smtp_rules']['*'];
                foreach($smtp_rules['recipient_smtp_rules'] as $rule_index => $rule_value) {
                        if($rule_index != '*' && preg_match($rule_index, $recipient_domain)) {
                                $recipient_smtp_rules = $rule_value;
                                break;
                        }
                }
                /*
                if (empty($smtp_rules['recipient_smtp_rules'][$recipient_domain])) {
                        $recipient_smtp_rules = $smtp_rules['recipient_smtp_rules']['*'];
                } else $recipient_smtp_rules = $smtp_rules['recipient_smtp_rules'][$recipient_domain];
            */
                // set some more template vars
                $smtp_template_vars['boundary'] = $boundary;
                $smtp_template_vars['recipient_name'] = current($random_abreviation). ' '.$recipient_name_redefined;
                if ( isset($params->recipient_name)) $smtp_template_vars['recipient_name'] = $params->recipient_name;
                $smtp_template_vars['sender_name'] = '=?utf-8?b?'.base64_encode($params->name).'?=';
                $smtp_template_vars['subject'] = $subject;
                // parse the mail headers
                if ( ! empty($recipient_smtp_rules)) {
                        $headers = array();
                        foreach($recipient_smtp_rules['headers'] as $row) {
                                // get from smtp rules config the right syntax according to the type of domain of the sender
                                $header_command = $system->parser->parse_generic($row, $smtp_template_vars, TRUE);
                                //$mail->Command($header_command);
                                $headers[] = $header_command;
                        }

                        $result = $mail->Command(implode($mail->CRLF, $headers), FALSE);
                        
                        // check if there is costum content rules
                        // parse the mail content
                        if ( ! empty($recipient_smtp_rules['custom'])) $smtp_rules = array_merge($smtp_rules, $recipient_smtp_rules['custom']);
                }
                // parse the mail content
                $mail_content = array();
                // the parameter $only_textoverwrites smtp rule is_html

                if ($smtp_rules['is_html']) {
                        switch ($smtp_rules['enconding_text']) {
                                case 'quoted-printable':
                                        $body_text = EncodeQPphp(html2text($template->html));
                                        break;
                                case 'base64':
                                        $body_text = base64_encode(html2text($template->html));
                                        break;
                                case '8bit':
                                case '7bit':
                                case 'binary':
                                        $body_text = html2text($template->html);
                                        break;
                        }
                        switch ($smtp_rules['enconding_html']) {
                                case 'quoted-printable':
                                        $body_html = EncodeQPphp($template->html);
                                        break;
                                case 'base64':
                                        $body_html = base64_encode($template->html);
                                        break;
                                case '8bit':
                                case '7bit':
                                case 'binary':
                                        $body_html = $template->html;
                                        break;
                        }
                        $mail_content[] = '--'.$boundary;
                        $mail_content[] = 'Content-Type: text/plain; charset='.$smtp_rules['charset_text'];
                        $mail_content[] = 'Content-Transfer-Encoding: '.$smtp_rules['enconding_text'];
                        $mail_content[] = '';
                        $mail_content[] = $body_text;
                        $mail_content[] = '';
                        $mail_content[] = '--'.$boundary;
                        $mail_content[] = 'Content-Type: text/html; charset='.$smtp_rules['charset_html'];
                        $mail_content[] = 'Content-Transfer-Encoding: '.$smtp_rules['enconding_html'];
                        $mail_content[] = '';
                        $mail_content[] = $body_html;
                        $mail_content[] = '';
                        $mail_content[] = '--'.$boundary.'--';
                        $mail_content[] = '';
                } else if ($smtp_rules['only_html']) {
                        switch ($smtp_rules['enconding_html']) {
                                case 'quoted-printable':
                                        $body_html = EncodeQPphp($template->html);
                                        break;
                                case 'base64':
                                        $body_html = base64_encode($template->html);
                                        break;
                                case '8bit':
                                case '7bit':
                                case 'binary':
                                        $body_html = $template->html;
                                        break;
                        }
                        $mail_content[] = "";
                        $mail_content[] = $body_html;
                        $mail_content[] = "";
                } else {
                        switch ($smtp_rules['enconding_text']) {
                                case 'quoted-printable':
                                        $body_text = EncodeQPphp(html2text($template->html));
                                        break;
                                case 'base64':
                                        $body_text = base64_encode(html2text($template->html));
                                        break;
                                case '8bit':
                                case '7bit':
                                case 'binary':
                                        $body_text = html2text($template->html);
                                        break;
                        }
                        $mail_content[] = "";
                        $mail_content[] = $body_text;
                        $mail_content[] = "";
                }
                $mail->Command(implode($mail->CRLF, $mail_content), FALSE);
                // send at last the dot to tell the remote smtp to send the email
                $response = $mail->Command(".", TRUE);
                if ($response == '250') {
                        if ($terminate) {
                                $mail->Close();
                                $this->smtp_connection = FALSE;
                        }
                        $return->status = TRUE;
                        $return->code   = 5;
                        $return->error  = $params->emails_data_id.'@'.$params->hostname;
                        return $return;
                } else {
                        if ($terminate) {
                                $mail->Close();
                                $this->smtp_connection = FALSE;
                        }
                        $return->status = FALSE;
                        $return->code   = 4;
                        $return->error  = $response;
                        return $return;
                }
        } else {
                $mail->Close();
                $return->status = FALSE;
                $return->code   = 4;
                $return->error  = 'smtp `DATA` command failed';
                return $return;
        }
        return $return;
    }
    //this method closes the smtp connection if active and avalaiable on the class property called $this->smtp_connection
    //this can be necessary in some cases like when activated the whitelist mode
    function close_mail_ghost() {
        $system = $this->CI;
        if ($this->smtp_connection) {
                $mail = new SMTP();
                $mail->do_debug = 0;
                $mail->smtp_conn = $this->smtp_connection;
                $mail->Close();
                $this->smtp_connection = FALSE;
                $system->log->write_log('INFO','connection to remote smtp host closed.');
        }
    }
    //procedure to fetch mailserver accounts to recieve fake emails to increase mass_sender reputation
    function fakerecipients($login = NULL, $num_emails_fetched = NULL) {
        if (empty($login) || empty($num_emails_fetched)) return FALSE;
        $system = $this->CI;
        $accounts_rules = $system->setup_model->accounts_rules($login->host_id);
        $recipients_cycle = $accounts_rules->RECIPIENTS_CYCLE;
        $recipients_ratio = $accounts_rules->RECIPIENTS_RATIO;
        $ratio = $recipients_ratio / 100;
        $limit = round($num_emails_fetched * $ratio);
        $system->log->write_log('INFO',$limit.' fake recipients to fetch!.');
        $system->log->write_log('INFO',' recipients_cycle rule set to ' .$recipients_cycle);
        $system->log->write_log('INFO',' recipients_ratio rule set to ' .$recipients_ratio);
        $whitelist_emails = $system->emails_model->get_whitelist_emails($limit, $recipients_cycle);
        if ( empty($whitelist_emails)) {
                $system->log->write_log('INFO','failed in fetching mailserver accounts to recieve mass_senders mails.');
                return FALSE;
        }
        $this->smtp_connection = FALSE;
        foreach($whitelist_emails as $recipient) {
                $random_template = $this->faketext();
                $param_template  = new stdClass;
                $param_template->mailing_template_id = 1;
                if ($random_template) {
                        if (empty($random_template->subject)) $random_template->subject = 'Good Morning client#' . $recipient->login_id;
                        if (empty($random_template->html)) $random_template->html = 'This is a dummy content for the fakerecipients';
                        $param_template->subject = $random_template->subject;
                        $param_template->html = 'Hi '.$recipient->name.'<br />' . $random_template->html;
                } else {
                        $param_template->subject = 'Good Morning client#' . $recipient->login_id;
                        $param_template->html = 'Hi ' . $recipient->name . '<br />This is a dummy content for the fakerecipients';
                }
                // the 3rd is an optional parameters
                $this->fakeinbox((array) $recipient, $param_template, $login);
        }
        $this->close_mail_ghost();
        return TRUE;
    }
    //fetch for valid text to send in the whitelist mails
    function faketext() {
        $system = $this->CI;
        $rss_cache_file = $system->config->item('rss_cache_file');
        $rss_cache_file_content = file_get_contents($rss_cache_file);
        if ( ! empty($rss_cache_file_content)) {
                $template_files_decoded = json_decode($rss_cache_file_content);
                $random_template = $template_files_decoded[mt_rand(0,count($template_files_decoded)-1)];
                return (object) $random_template;
        }
        return FALSE;
    }
    //when set active this method sends a fake email for the sender in the end of his process
    function fakeinbox($recipient = NULL, $template = NULL, $login = NULL){
        $system = $this->CI;
        if ( empty($login) ) $login = $system->logins_model->random(FALSE, TRUE, TRUE);
        if ( ! empty($login) ) {
                $settings = $system->settings_model->get($login->host_id);
                $params = new stdClass;
                $params->hostname = $login->domain;
                $params->secure   = $settings['smtp']['service_flags']['value'];
                $params->host     = $settings['smtp']['host']['value'];
                $params->port     = $settings['smtp']['port']['value'];
                $params->timeout  = $settings['smtp']['timeout']['value'];
                $params->username = $login->login;
                $params->password = $login->pass;
                $params->from     = $login->email;
                $params->name     = $login->name;
                $params->proxy    = FALSE;
                $params->domain   = $login->domain;
                if ($template) {
                        $params->emails_data_id   = 0;
                        $params->mailing_group_id = $template->mailing_template_id;
                        if (is_array($recipient)) {
                                $params->recipient_name = $recipient['name'];
                                $recipient = $recipient['email'];
                        }
                        $response = $this->mail($params, $recipient, $template, FALSE);
                        if ($response) return TRUE; else {
                                $system->logins_model->lock($login->login_id,9);
                                $system->rate_model->send_fail($login->login_id);
                                $system->log->write_log('INFO','fakeinbox procedure failure!');
                        }
                } else $system->log->write_log('INFO','fakeinbox procedure failure! no template');
        } else $system->log->write_log('INFO',' No valid sender avaiable this time for fakeinbox. ');
    }
    //method to test a recipient by parsing the smtp command `rcpt_to` result
    function test_recipient($recipient = NULL) {
        if (empty($recipient)) return FALSE;
        return validate_email($recipient);
    }
}