<?php
/* 
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */
class MailImage {
	function MailImage() {}
	function isValid($url = FALSE) {
		if($url === FALSE) return false;
		$bool = $this->first_step_filter($url);
		if($bool == TRUE) return TRUE;
		else {
			$bool = $this->second_step_filter($url);
			if($bool == TRUE) return TRUE;
			else return FALSE;
		}
	}
	function first_step_filter($url = FALSE) {
		if($url === FALSE) return false;

		$pos = strrpos( $url, ".");
		if ($pos === false) return false;
		$ext = strtolower(trim(substr( $url, $pos)));
		$imgExts = array(".gif", ".jpg", ".jpeg", ".png", ".tiff", ".tif"); // this is far from complete but that's always going to be the case...
		if ( in_array($ext, $imgExts) )  return true;
		return false;
	}
	function second_step_filter($url = FALSE) {
		if($url === FALSE) return false;

		$params = array('http' => array('method' => 'HEAD'));
		$ctx = stream_context_create($params);
		$fp = @fopen($url, 'rb', false, $ctx);
		if (!$fp) return false;  // Problem with url

		$meta = stream_get_meta_data($fp);
		if ($meta === false)
		{
			fclose($fp);
			return false;  // Problem reading data from url
		}

		$wrapper_data = $meta["wrapper_data"];
		if(is_array($wrapper_data)){
		  foreach(array_keys($wrapper_data) as $hh){
			  if (substr($wrapper_data[$hh], 0, 19) == "Content-Type: image") // strlen("Content-Type: image") == 19
			  {
				fclose($fp);
				return true;
			  }
		  }
		}

		fclose($fp);
		return false;
	}

}
