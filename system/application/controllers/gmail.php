<?php
class Gmail extends Controller {


	function Gmail()
	{
		parent::Controller();
		$this->load->library('log');
		$this->load->helper('array');
		$this->load->helper('debug');
		$this->load->helper('url');

		$this->load->library('TCTemplate');
		$this->tctemplate->set_template('templates/default');
		$this->tctemplate->include_js_file('mootools_core.js');
		$this->tctemplate->include_js_file('mootools_more.js');
		$this->tctemplate->set_view_folder('gmail');
	}
	
	function index()
	{
	}
	function output($value = false)
	{
		$html = $this->get_url('http://mail.google.com/mail/signup');

		$vars = array();
		$vars['data'] = htmlentities($html[0]);
 		$this->tctemplate->show('Index', 'form',$vars);
	}
	function get_url( $url,  $javascript_loop = 0, $timeout = 5 )
	{
		$url = str_replace( "&amp;", "&", urldecode(trim($url)) );

		$cookie = tempnam ("/tmp", "CURLCOOKIE");
		$ch = curl_init();
		curl_setopt( $ch, CURLOPT_USERAGENT, "Mozilla/5.0 (Windows; U; Windows NT 5.1; rv:1.7.3) Gecko/20041001 Firefox/0.10.1" );
		curl_setopt( $ch, CURLOPT_URL, $url );
		curl_setopt( $ch, CURLOPT_COOKIEJAR, $cookie );
		curl_setopt( $ch, CURLOPT_FOLLOWLOCATION, true );
		curl_setopt( $ch, CURLOPT_ENCODING, "" );
		curl_setopt( $ch, CURLOPT_RETURNTRANSFER, true );
		curl_setopt( $ch, CURLOPT_AUTOREFERER, true );
		curl_setopt( $ch, CURLOPT_SSL_VERIFYPEER, false );    # required for https urls
		curl_setopt( $ch, CURLOPT_CONNECTTIMEOUT, $timeout );
		curl_setopt( $ch, CURLOPT_TIMEOUT, $timeout );
		curl_setopt( $ch, CURLOPT_MAXREDIRS, 10 );
		$content = curl_exec( $ch );
		$response = curl_getinfo( $ch );
		curl_close ( $ch );
		
		if ($response['http_code'] == 301 || $response['http_code'] == 302)
		{
			ini_set("user_agent", "Mozilla/5.0 (Windows; U; Windows NT 5.1; rv:1.7.3) Gecko/20041001 Firefox/0.10.1");

			if ( $headers = get_headers($response['url']) )
			{
				
				foreach( $headers as $value )
				{
					if ( substr( strtolower($value), 0, 9 ) == "location:" )
						return get_url( trim( substr( $value, 9, strlen($value) ) ) );
				}
				
			}
		}

		if (    ( preg_match("/>[[:space:]]+window\.location\.replace\('(.*)'\)/i", $content, $value) || preg_match("/>[[:space:]]+window\.location\=\"(.*)\"/i", $content, $value) ) &&
				$javascript_loop < 5
		) {
			return get_url( $value[1], $javascript_loop+1 );
		}
		else
		{
			return array( $content, $response );
		}
		
	}
}
