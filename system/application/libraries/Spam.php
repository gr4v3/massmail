<?php
class Spam {
	var $CI;
	function Spam(){
		$this->CI = & get_instance();
    }
	function plugin($plugin = NULL) {
		if (empty($plugin)) return FALSE;
		$system = $this->CI;
		$system->load->helper($plugin);
	}
	function view($params = NULL) {
		return spam_view($params);
	}
	function delete($params = NULL) {
		return spam_delete($params);
	}
}
