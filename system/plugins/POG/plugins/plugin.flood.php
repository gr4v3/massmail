<?php
/* 
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */

class Swift_Plugins_FloodPlugin
	implements Swift_Events_SendListener, Swift_Plugins_Sleeper
{
	public function sendPerformed(Swift_Events_SendEvent $evt)
	{
		echo "send performed";
		//$transport = $evt->getTransport();
		//$transport->stop();
	}
}
