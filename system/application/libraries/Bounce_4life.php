<?php  if (!defined('BASEPATH')) exit('No direct script access allowed');
class Bounce_4life extends Bounce {
    var $CI;
    function Bounce_4life() {
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
                $bounce_item = new stdClass;
                $bounce_item->link = FALSE;
                $bounce_item->complaint = FALSE;
                $bounce_item->true_bounce = FALSE;
                $bounce_item->recipient = FALSE;
                $bounce_item->message_id = FALSE;
                $bounce_item->references = FALSE;
                $bounce_item->subject = FALSE;
                $bounce_item->senderaddress = FALSE;
                //the regexp expression `Status` belongs to the bounce reports of amazo
                if ( preg_match('/http:\/\/pt\.beruby\.com\/activate\/(.*)\" target/', $raw_body, $preg_match_result)) {
                        //if the flow goes through here then in means that the bounce message didn't result in rfc822smtp message compatible
                        $match_smtp_result = str_replace('" target', '', current($preg_match_result));
                        $system->log->write_log('BOUNCE','found a beruby link: ' .$match_smtp_result);
                        $bounce_item->link = $match_smtp_result;
                }
                if ( preg_match('/Diagnostic-Code:(.*)(2|4|5)[0-9]{2}/', $raw_body, $preg_match_result)) {
                        //if the flow goes through here then in means that the bounce message didn't result in rfc822smtp message compatible
                        $match_smtp_result = current($preg_match_result);
                        if ( preg_match('/5[0-9]{2}/', $match_smtp_result)) {
                                $system->log->write_log('BOUNCE','found a hard bounce with code: ' .$match_smtp_result);
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
                imap_setflag_full($stream, $msg_num_id, "Deleted Flagged");
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
            $inbox = imap_search($stream, 'SUBJECT "Benvindo a beruby"');
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
    function parse($login = NULL, $settings = NULL) {
        if (empty($login) || empty($settings)) return FALSE;
        $system = $this->CI;
        $system->log->write_log('BOUNCE','************** bounce procedure begin *********************');
        $system->log->write_log('BOUNCE','server '.$login->hostname.' selected to detect bounce mails.');
        $system->log->write_log('BOUNCE','user login#'.$login->login_id.' selected to auth procedure.');
        if ( isset($settings['imap'])) $bounce_items = $this->imap($login, $settings);
        else if ( isset($settings['pop'])) $bounce_items = $this->pop($login, $settings);
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
            if ( ! empty($bounce_item->references)) {
                    $emails_data_id = $this->parse_references_header($bounce_item->references);
                    $system->log->write_log('BOUNCE','got the mail item by the reference header: ' . $emails_data_id);
            } else if ( ! empty($bounce_item->message_id)) {
                    $emails_data_id = $this->parse_references_header($bounce_item->message_id);
                    $system->log->write_log('BOUNCE','got the mail item by the message-id header: ' . $emails_data_id);
            }
            var_dump($bounce_item);
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
}
