<?php

class Index extends Controller {

	function __construct()
	{
		parent::Controller();
		$this->load->helper('url');
		$this->load->library('TCTemplate');

		

	}
	function Index()
	{
		$vars = array();

		$this->tctemplate->set_template('templates/index.php');
		$this->tctemplate->include_js_file('mootools_core.js');
		$this->tctemplate->include_js_file('mootools_more.js');
		$this->tctemplate->include_js_file('initialize.js');




		$this->tctemplate->show('Home','contents/home',$vars);
		
	}

}

/* End of file email.php */
/* Location: ./system/application/controllers/index.php */