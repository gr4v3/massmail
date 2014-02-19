<?php
/**
 * class Worker is the actual processor of jobs.
 *
 */
class Worker extends PHP_Fork {
	/**
	 * jobIdentifier
	 *
	 * @var integer
	 * @access public
	 */
	var $jobId 				= 0;
    
	/**
	 * the actual code that should be run
	 *
	 * @var string
	 * @access public
	 */
	var $code 				= '';
    
	/**
	 * concurrency control
	 *
	 * @var boolean
	 * @access public
	 */
	var $concurrent			= false;
    
	/**
	 * implementation / environment identifier
	 *
	 * @var integer
	 * @access public
	 */
	var $implementationId	= 0;
    
	/**
	 * jobIdentifier
	 *
	 * @var boolean
	 * @access private
	 */
    var $_finished			= false;
	
    /**
     * construct a new worker
     *
     * @param string $name
     * @return worker
     */
    function worker($name) {
    	$this->PHP_Fork($name);
    }

    /**
     * Enter description here...
     *
     */
    function run() {
    	global $db;
    	$db = newDb();
        while (true) {
            $this->execute();
			$this->setAlive();
			sleep(1);
        }
    }

    /**
     * Get the local finished var
     *
     * @return boolean
     * @access public
     */
    function getFinished(){
    	return $this->getVariable('_kill');
    }
    
    /**
     * Set this worker to finish. Tells the daemon that the work is done!
     *
     * @return boolean
     */
    function setFinished(){
    	global $db;
    	writeLog($this->getName().': finished executing... '.$this->jobId);
		$this->_finished = true;
    	$this->setVariable('_kill', 1);
    	$db->close();
    	return true;
    }
    
    /**
     * The actual execution happens right here
     *
     * @return void
     */
    function execute() {
    	global $db;
    	if (!$this->_finished) {
	    	if (!empty($this->code)) {
				ob_start();
				eval($this->code);
				$results = ob_get_contents();
				ob_end_clean();
			}
			$job =& new CronJob($this->jobId);
			$job->endTimestamp = time();
			if (!empty($results)) $job->results = $results;
			else $job->results = 'results were empty';
			$job->update();
			$this->setFinished();
    	}
	}
}
?>