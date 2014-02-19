<?php
/**
*
*       CronJob, v1.0
*
*       By Robin Speekenbrink
*
*       Kingsquare Information Services, 1-10-2007
*
*       More info, bugs or requests, contact info@kingsquare.nl
* @author Robin Speekenbrink <info@kingsquare.nl>
* @version 1.0
*/

class CronJob {
	/**
	* The identifier for this job
	*
	* @var integer
	* @access public
	*/
	var $id = 0;
	
	/**
	* The identifier for the creator of this job (the crontab definition)
	*
	* @var integer
	* @access public
	*/
	var $crontabId = 0;

	/**
	* The actual UNIX timestamp of the start of this job
	*
	* @var integer
	* @access public
	*/
	var $startTimestamp = 0;

	/**
	* The UNIX timestamp for when this job claimed to be finished
	*
	* @var integer
	* @access public
	*/
	var $endTimestamp = 0;

	/**
	* The actual code that should be executed on running this job
	*
	* @var integer
	* @access public
	*/
	var $code = '';

	/**
	* Can a job run along side another running job (together with a similar implementationId)
	*
	* @var integer
	* @access public
	*/
	var $concurrent = 0;

	/**
	* The identifier for concurrent environments
	*
	* @var integer
	* @access public
	*/
	var $implementationId = 0;

	/**
	* The final output returned by the system
	*
	* @var integer
	* @access public
	*/
	var $results = '';

	/**
	* The PID as given by the OS to the job as its being handeld.
	*
	* @var integer
	* @access public
	*/
	var $pid = 0;
	
	/**
	 * Create a new blank (id=false) or existing cronjob as found in DB
	 *
	 * @param integer $id
	 * @return CronJob
	 */
	function CronJob($id = false) {
		$this->id = $id;
		if ($this->id) $this->_setData();
	}
		
	/**
	 * A method to easily create a job from a crontab definition
	 *
	 * @param Crontab $crontab
	 */
	function createFromCrontab(& $crontab) {
		$this->crontabId = $crontab->getId();
		$this->startTimestamp = time();
		$this->code = $crontab->getCronJobCode();
		$this->concurrent = $crontab->getConcurrent();
		$this->implementationId = $crontab->getImplementationId();
	}
	
	/**
	 * Update the record in the DB
	 *
	 * @return boolean
	 */
	function update() {
		global $db;
		$sql = 'REPLACE `'.DB_NAME.'`.`'.TABLE_CRONJOB.'` SET 
				`id`				= '.$db->Quote($this->id).',
				`crontabId`			= '.$db->Quote($this->crontabId).',
				`startTimestamp`	= FROM_UNIXTIME('.$db->Quote($this->startTimestamp).'),
				`endTimestamp`		= FROM_UNIXTIME('.$db->Quote($this->endTimestamp).'),
				`code`				= '.$db->Quote($this->code).',
				`concurrent`		= '.$db->Quote($this->concurrent).',
				`implementationId`	= '.$db->Quote($this->implementationId).',
				`results`			= '.$db->Quote($this->results).',
				`pid`				= '.$db->Quote($this->pid);
		if (!$db->Execute($sql)) {
			if (function_exists('writeLog')) writeLog($db->ErrorMsg());
			return false;
		}
		if (!$this->id) {
			$this->id = $db->Insert_ID();
			$action = 'created';
		} else $action = 'updated';
		if (function_exists('writeLog')) writeLog(ucfirst($action).' job with ID: '.$this->id);
		return true;
	}
	
	/**
	 * retrieve thea actual data from the DB
	 *
	 * @access private
	 */
	function _setData() {
		global $db;
		$sql = 'SELECT * FROM `'.DB_NAME.'`.`'.TABLE_CRONJOB.'` WHERE `id` = '.$db->Quote($this->id);
		$objVars = get_object_vars($this);
		foreach((array)$db->getRow($sql) as $key=>$value){
			if (!(empty($key) || $value == '0000-00-00 00:00:00') && in_array($key,array_keys($objVars))){
				if (strpos($key, 'Timestamp')!== false) $value = strtotime($value);
				$this->$key = $value;
			}
		}
	}
}
?>