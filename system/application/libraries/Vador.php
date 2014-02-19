<?php  if (!defined('BASEPATH')) exit('No direct script access allowed');
class Vador {
	var $CI;
	function Vador(){
		$this->CI = & get_instance();
		$this->CI->load->config('mailserver');
		$this->CI->load->library('log');
		$this->CI->load->helper('tccurl');
	}
	function execute($emails_collected = NULL) {
		$system = $this->CI;
		$config_params = $system->config->item('params');
		$vador_active = $config_params['vador']['vador_active'];
		if ($vador_active) {
			if ($emails_collected && is_array($emails_collected) && ! empty($emails_collected)) {
				$vador_url_request = $config_params['vador']['vador_url_request'];
				$vador_key_request = $config_params['vador']['vador_key_request'];
				//send the request in POST mode
				$post_data = array('ec_ids' => implode(',', $emails_collected));
				$tccurl = new TCCurl(false);
				$vador_url_response = $tccurl->post($vador_url_request . $vador_key_request, $post_data);
				if ($vador_url_response) {
					$json_result = json_decode($vador_url_response);
					if ($json_result) {
						if ($json_result->success == 1) {
							//successfull in sending the request
							$system->log->write_log('VADOR','vador::execute success');
							$system->log->write_log('VADOR','the system processed with success my request');
							$system->log->write_log('VADOR','post data:' . $post_data['ec_ids']);
							$system->log->write_log('VADOR',$json_result->new_records . ' repeated email_collected_id');
							$system->log->write_log('VADOR','status:' . implode("##", $json_result->messages));
						} else {
							//failure in the request
							$system->log->write_log('VADOR','vador::execute failure');
							$system->log->write_log('VADOR','the system could not process my request');
							$system->log->write_log('VADOR','post data:' . $post_data['ec_ids']);
							$system->log->write_log('VADOR','status:' . implode("##", $json_result->messages));
						}
					} else {
						//failure in the request
						$system->log->write_log('VADOR','vador::execute failure');
						$system->log->write_log('VADOR','the result of the request didn\'t returned in a valid json format');
						$system->log->write_log('VADOR','post data:' . $post_data['ec_ids']);
						$system->log->write_log('VADOR','status:' . $vador_url_response);
					}
				} else {
					//curl resulted in a false state
					$system->log->write_log('VADOR','vador::execute failure');
					$system->log->write_log('VADOR','the request through tccurl resulted in a failure state');
					$system->log->write_log('VADOR','post data:' . $post_data['ec_ids']);
					$system->log->write_log('VADOR','status:' . $vador_url_response);
				}
			} else {
				//failure in the request
				$system->log->write_log('VADOR','vador::execute failure');
				$system->log->write_log('VADOR','an error happen in the bounce library or there is no bounces for now');
			}
		} else {
			//failure in the request
			$system->log->write_log('VADOR','vador::execute failure');
			$system->log->write_log('VADOR','the library is not active and so not sending requests');
			if ($emails_collected && is_array($emails_collected) && ! empty($emails_collected)) {
				$post_data = array('ec_ids' => implode(',', $emails_collected));
				$system->log->write_log('VADOR','post data:' . $post_data['ec_ids']);
			}
		}
	}
}