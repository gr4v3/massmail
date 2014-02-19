<?php  if ( ! defined('BASEPATH')) exit('No direct script access allowed');
//IMPORTANT:
//Rename this file to configuration.php after having inserted all the correct db information
//include 'system/application/config/database.php';


global $configuration;
$configuration['soap'] = "http://www.phpobjectgenerator.com/services/pog.wsdl";
$configuration['homepage'] = "http://www.phpobjectgenerator.com";
$configuration['revisionNumber'] = "";
$configuration['versionNumber'] = "3.0e";

$configuration['pdoDriver']	= 'mysql';
$configuration['setup_password'] = '';


// to enable automatic data encoding, run setup, go to the manage plugins tab and install the base64 plugin.
// then set db_encoding = 1 below.
// when enabled, db_encoding transparently encodes and decodes data to and from the database without any
// programmatic effort on your part.
$configuration['db_encoding'] = 0;

// edit the information below to match your database settings
$CI =& get_instance();


$configuration['db']	= 'email';		//	<- database name
$configuration['host'] 	= $CI->db->hostname;	//	<- database host
$configuration['user'] 	= $CI->db->username;		//	<- database user
$configuration['pass']	= $CI->db->password;		//	<- database password
$configuration['port']	= '3306';		//	<- database port


//proxy settings - if you are behnd a proxy, change the settings below
$configuration['proxy_host'] = false;
$configuration['proxy_port'] = false;
$configuration['proxy_username'] = false;
$configuration['proxy_password'] = false;


//plugin settings
$configuration['plugins_path'] = 'C:\www\Email\system\plugins\POG\plugins';  //absolute path to plugins folder, e.g c:/mycode/test/plugins or /home/phpobj/public_html/plugins




include 'system/plugins/POG/objects/class.database.php';
include 'system/plugins/POG/objects/class.bounce.php';
include 'system/plugins/POG/objects/class.fails.php';
include 'system/plugins/POG/objects/class.login.php';
include 'system/plugins/POG/objects/class.queue.php';
include 'system/plugins/POG/objects/class.recipients.php';
include 'system/plugins/POG/objects/class.rules.php';
include 'system/plugins/POG/objects/class.servers.php';
include 'system/plugins/POG/objects/class.status.php';
include 'system/plugins/POG/objects/class.mail.php';
include 'system/plugins/POG/objects/class.sent.php';