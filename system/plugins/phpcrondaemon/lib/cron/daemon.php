<?php
/**
*
*       CronDaemon, v1.0
*
*       By Robin Speekenbrink
*
*       Kingsquare Information Services, 1-10-2007
*
*  Based on the original PHP_FORK class by Luca Mariano
* 
*  This program is free software. You can redistribute it and/or modify
*  it under the terms of the GNU General Public License as published by
*  the Free Software Foundation; either version 2 of the License.
* @author Robin Speekenbrink <info@kingsquare.nl>
* @version 1.0
*/

class CronDaemon extends PHP_Fork {
	/**
     * The time (seconds) the process checks for new jobs or finished workers
     *
     * @var string
     * @access private
     */
    var $_sleepInt = 60;

    /**
     * The time (seconds) a worker can be left `idle`
     *
     * @var integer
     * @access private
     */
    var $_maxIdleTime = 60;
	
    /**
	 * An array of workers currently executing code
	 *
	 * @var array
     * @access private
	 */
	var $_workers = array();
    
    /**
     * create a new Daemon
     *
     * @param string $name
     * @param integer $sleepInterval
     * @param integer $maxIdletime
     */
    function cronDaemon($name, $sleepInterval, $maxIdletime) {
    	$this->PHP_Fork($name);
        $this->_sleepInt = $sleepInterval;
        $this->_maxIdleTime = $maxIdletime;
    }

    /**
     * The actual code that runs continously
     *
     */
    function run() {
    	global $db;
    	while (true) {
			$db = newDb();
     		$this->_processCronTabs();
       		$this->_startWorkers();
            $this->_detectDeadWorkers();
			$this->_kill();
			$db->close();
		    sleep($this->_sleepInt);
        }
    }
    
    /**
     * kill the deaemon itself. Remove any open pointer it can find
     *
     */
    function stop() {
    	global $fp;
    	if (is_resource($fp)) fclose($fp);
    }
    
    /**
     * Kill all the running workers we can find
     *
     */
    function stopAllWorkers() {
        writeLog($this->getName().': stopping workers...');
        foreach($this->_workers as $implementationId=>$workers) {
        	foreach($workers as $worker) {
        		$worker->stop();
        		writeLog($worker->getName().' ('.$worker->getPid().'): stopped');
        	}
        }
        unset($this->_workers);
        $this->_workers = array();
        writeLog($this->getName().': All workers stopped');
        
    }

	/**
	 * Retrieve all the jobs that have to be executed
	 *
	 * @return array CronJob
	 */
	function _getJobs() {
    	global $db;
    	$jobs = array();
		$sql = 'SELECT `id` FROM `'.DB_NAME.'`.`'.TABLE_CRONJOB.'` WHERE `pid` = '.$db->Quote(0).' AND `endTimestamp` = '.$db->Quote('0000-00-00 00:00:00');
		$rows = $db->GetCol($sql);
		if ($db->ErrorMsg()) {
			writeLog('MySQL error: '.$db->ErrorMsg().' when _getJobs is called '.$sql);
			$this->_kill(true);
		}
		foreach((array)$rows as $cronJobId)  $jobs[] =& new CronJob($cronJobId);
		if (!empty($jobs)) writeLog('found jobs ('.count($jobs).'): '.implode(',',$rows));
		return $jobs;
	}

    /**
     * get all the crontabs and process them
     *
     * @access private
     */
    function _processCronTabs() {
    	$sql = 'SELECT `id` FROM `'.DB_NAME.'`.`'.TABLE_CRONTAB.'`';
    	foreach($db->GetCol($sql) as $crontabId) {
    		$crontab = new Crontab($crontabId);
    		if (is_a($crontab, 'Crontab')) $crontab->process();
    	}
    }
 
    /**
     * Start workers for the jobs found
     * 
     * @access private
     */
    function _startWorkers() {
    	if(count($this->_workers) <= MAX_WORKERS) {
    		writeLog('checking for jobs');
   			$jobs = $this->_getJobs();
    		foreach($jobs as $job) {
    			if ((key_exists($job->implementationId, $this->_workers) && $job->concurrent) ||  (!key_exists($job->implementationId, $this->_workers))) {
					if ($job->concurrent) {
						foreach((array)$this->_workers[$job->implementationId] as $worker) {
							if (!$worker->concurrent) break 2;
						}
					}
    				$newWorker =& new worker ('worker implementation '.$job->implementationId.' job '.$job->id);
					if ($newWorker->_ipc_is_ok) {
    					$newWorker->jobId = $job->id;
						$newWorker->code = $job->code;
						$newWorker->concurrent = $job->concurrent;
						$newWorker->implementationId = $job->implementationId;
						$newWorker->start();
	                	$job->pid = $newWorker->getPid();
						$job->update();
	                	$this->_workers[$job->implementationId][] =& $newWorker;
						writeLog('started new Woker '.$newWorker->getName().' ('. $newWorker->getPid().')');
	                } else  writeLog($newWorker->getName().': Unable to create IPC segment...');
    			} else writeLog('Unable to start job due to concurrency');
   			}
    	}
    }

	/**
	 * Detect dead / finished workers and kill them
	 *
	 * @access private
	 */
	function _detectDeadWorkers() {
		foreach (array_keys($this->_workers) as $implementationId) {
			foreach(array_keys($this->_workers[$implementationId]) as $workerKey) {
				$worker =& $this->_workers[$implementationId][$workerKey];
				writeLog('getKill '.$worker->getVariable('_kill').' : '.$worker->getPid());
				if ($worker->getVariable('_kill')) {
					writeLog($worker->getName().' ('.$worker->getPid().') seems to be finished...');
					$worker->stop();
					array_splice($this->_workers[$implementationId],$workerKey,1);
					break;
				} else writeLog(print_r($worker,true));
			}
			if (empty($this->_workers[$implementationId])) unset($this->_workers[$implementationId]);
		}
	}
    
	/**
	 * The ultimate kill command for the deaemon / workers
	 * If force is called the existance of the real FS_KILL-file is ignored (but cleaned if available)
	 * 
	 * @param boolean $force
	 */
	function _kill($force = false) {
		if (is_readable(FS_KILL) || $force) {
			$contents = ((is_readable(FS_KILL))? file_get_contents(FS_KILL): 1 );
			if (!empty($contents)) {
				if ($force) writeLog($this->getName().' was killed due to error or otherwise forced');
				else  writeLog($this->getName().' found kill file and contents');
				$this->stopAllWorkers();
				if (is_file(FS_KILL)) unlink(FS_KILL);
				writeLog($this->getName().' fully stopped');
				$this->stop();
				exit;
			} else writeLog($this->getName().' is still alive muhaha');
		}
	}
}
?>