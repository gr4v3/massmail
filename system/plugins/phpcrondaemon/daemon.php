<?php
/**
 *  This file is released under the GPL, any version you like
 * 
 * 
 * PHP CronDaemon v1.0
 * 
 * By Robin Speekenbrink
 * Kingsquare Information Services, 1-10-2007
 * 
 * 
 * 
 * 
 * 
 * Based on the original PHP_FORK class by Luca Mariano
 * This program is free software. You can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation; either version 2 of the License.
 * @author Robin Speekenbrink <info@kingsquare.nl>
 * @version 1.0
 * 
 * I juse ADODB classes from www.phplens.com/adodb but for clearity reasons i use the db_functions from this file.
 * Changing to another DB shouldn't pose any problems.
 */

require './lib/miniDb.php';
require './lib/phpFork.php';
require './lib/worker.php';
require './lib/cron/tab.php';
require './lib/cron/daemon.php';
require './lib/cron/job.php';
require './lib/cron/parser.php';

$db = null;
$fp = $debug = false;

define ('MAX_WORKERS',		15);
define ('MAX_IDLE',			180);
define ('SLEEP_INTERVAL',	10);
define ('FS_START',			dirname(__FILE__).'/');
define ('FS_LOG',			FS_START.'log/cron.log');
define ('FS_KILL',			FS_START.'kill/daemon');
define ('DB_HOST',			'localhost');
define ('DB_USER',			'root');
define ('DB_PASS',			'');
define ('DB_NAME',			'mail');
define ('TABLE_CRONTAB',	'crontab');
define ('TABLE_CRONJOB',	'cronJob');

/**
 * A central function for each sub process to create a new instance of the DB handle.
 * My version uses ADODB
 *
 * @return miniDb
 */
function newDb() {
	$db = & new mysqldb();
	$db->pconnect(DB_HOST, DB_USER, DB_PASS, DB_NAME);
	return $db;
}

/**
 * Write the data to the specified log file
 *
 * @param string $data
 */
function writeLog($data = '') {
	global $fp;
	if (empty($data)) return;
	if (!$fp)  $fp = fopen(FS_LOG, 'a+');
	$data = date('d-m-Y H:i:s')."\r\n".$data;
	fputs($fp, $data, strlen($data));
    fputs($fp, "\r\n----\r\n");
    if ($debug) print $data;
}
       
$controller = new cronDaemon('cronDaemon', SLEEP_INTERVAL, MAX_IDLE);
$controller->start();
writeLog('starting deamon: '.$controller->getName().' ('.$controller->getPid().')');
?>