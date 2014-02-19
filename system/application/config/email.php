<?php  if ( ! defined('BASEPATH')) exit('No direct script access allowed');
/* 
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */
$config['useragent'] = 'Gmail Mail Server';
$config['protocol'] = 'smtp';					// mail, sendmail, or smtp
$config['mailpath'] = '/usr/sbin/sendmail';		// server path to sendmail
$config['smtp_host'] = 'ssl://smtp.gmail.com';
$config['smtp_user'] = 'gr4v3m4n@gmail.com';
$config['smtp_pass'] = 'Dofasol123';
$config['smtp_port'] = 465;
$config['smtp_timeout'] = 5;
$config['wordwrap'] = TRUE;
$config['wrapchars'] = 76;
$config['mailtype'] = 'text';					// text or html
$config['charset'] = 'utf-8';
$config['validate'] = FALSE;					// validate email address
$config['priority'] = 3;						// priority: 1 - highest; 5 - lowest; 3 - normal
$config['crlf'] = "\n";
$config['newline'] = "\r\n";
$config['bcc_batch_mode'] = FALSE;
$config['bcc_batch_size'] = 200;




$config['mail_per_server'] = 7;
