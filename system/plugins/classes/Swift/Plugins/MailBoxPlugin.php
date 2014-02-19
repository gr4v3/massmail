<?php
/* 
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */

class Swift_Plugins_MailBoxPlugin
	implements Swift_Events_SendListener
{


	public function __construct()
	{
		
	}


	public function beforeSendPerformed(Swift_Events_SendEvent $evt)
	{
	}


	public function sendPerformed(Swift_Events_SendEvent $evt)
	{
		$transport = $evt->getTransport();
		Debug($evt->failedRecipients);
		//$transport->stop();
	}
}
