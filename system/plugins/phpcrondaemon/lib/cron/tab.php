<?php

class Crontab {
	/**
	 * identifier
	 *
	 * @var integer
	 */
	var $id 				= 0;
	
	/**
	 * small title for the crontab
	 *
	 * @var string (255)
	 */
	var $title 				= '';
	
	/**
	 * Extended description for longer descriptions
	 *
	 * @var string
	 */
	var $description 		= '';
	
	/**
	 * the actual code (PHP)
	 *
	 * @var string
	 */
	var $code 				= '<?php ?>';
	
	/**
	 * Can this script run along side an already running script for this implmementation / environment?
	 *
	 * @var boolean
	 */
	var $concurrent 		= false;
	
	/**
	 * The implementation / environment identifier
	 *
	 * @var integer
	 */
	var $implementationId	= 0;
	
	/**
	 * The actual cron statement as used by the original cronDaemon on *NIX
	 *
	 * @var string
	 */
	var $cronDefinition		= '';
	
	/**
	 * UNIX Timestamp of the last actual run for this cronTab
	 *
	 * @var timestamp
	 */
	var $lastActualTimestamp= 0;
	
	/**
	 * a small var to keep messages generated on update / setters etc
	 *
	 * @var string
	 */
	var $_message = '';
	/**
	 * Constructor for this package
	 *
	 * @param integer $id
	 * @return Crontab
	 */
	function Crontab($id = false) {
		$this->id = $id;
		if ($this->id) $this->_setData();
	}
	
	/**
	 * update the DB
	 *
	 * @return boolean
	 */
	function update() {
		global $db;
		$sql = 'REPLACE `'.DB_NAME.'`.`'.TABLE_CRONTAB.'` SET 
				`id`				= '.$db->Quote($this->id).',
				`title`				= '.$db->Quote($this->title).',
				`description`		= '.$db->Quote($this->description).',
				`code`				= '.$db->Quote($this->code).',
				`concurrent`		= '.$db->Quote($this->concurrent).',
				`implementationId`	= '.$db->Quote($this->implementationId).',
				`cronDefinition`	= '.$db->Quote($this->cronDefinition).',
				`lastActualTimestamp`	= FROM_UNIXTIME('.$db->Quote($this->startTimestamp).')';
		if (!$db->Execute($sql)) {
			if (function_exists('writeLog')) writeLog($db->ErrorMsg());
			return false;
		}
		if (!$this->id)  $this->id = $db->Insert_ID();
		return true;
	}
	
	/**
	 * Create a cronParser and check to see if we need to create new jobs
	 *
	 * @return void
	 */
	function process() {
		global $db;
		$cronParser =& new CronParser($this->cronDefinition);
		if ($this->getId() && ($cronParser->getLastRanUnix() > $this->getLastActualTimestamp())) {
			if (function_exists('writeLog')) writeLog('creating job because '.date('d-m-Y H:i', $cronParser->getLastRanUnix()).' > '.date('d-m-Y H:i', $this->getLastActualTimestamp()).' ('.$cronParser->getLastRanUnix().' > '.$this->getLastActualTimestamp().')');
			$this->lastActualTimestamp = time();
			$sql = 'UPDATE `'.DB_NAME.'`.`'.TABLE_CRONTAB.'` SET `lastActualTimestamp`= '.$db->Quote($this->lastActualTimestamp).' WHERE `id`= '.$db->Quote($this->getId());
			$db->Execute($sql);
			$job = new CronJob();
			$job->createFromCrontab(& $this);
			$job->update();
		}
		return;
	}

	/***********************
	 * PRIVATE FUNCTIONS
	 */
	
	/**
	 * get all the data from the DB
	 *
	 */
	function _setData() {
		global $db;
		$sql = 'SELECT * FROM `'.DB_NAME.'`.`'.TABLE_CRONTAB.'` WHERE `id` = '.$db->Quote($this->id);
		$objVars = get_object_vars($this);
		foreach((array)$db->getRow($sql) as $key => $value){
			if (!(empty($key) || $value == '0000-00-00 00:00:00') && in_array($key,array_keys($objVars))){
				if (strpos($key, 'Timestamp')!== false) $value = strtotime($value);
				$this->$key = $value;
			}
		}
	}
	
	/***********************
	 * SETTERS
	 */
	
	/**
	 * simple setter of titles
	 *
	 * @param string $var
	 * @return boolean
	 */
	function setTitle($var) {
		$var = trim(strip_tags($var));
		if ($var) {
			$this->title = $var;
			return true;
		} else {
			$this->_message .= ' Title is mandatory!';
			return false;
		}
	}
	
	/**
	 * Enter description here...
	 *
	 * @param unknown_type $var
	 * @return boolean
	 */
	function setDescription($var = false) {
		$description = strip_tags(trim($var));
		if (empty($description)){
			$this->_message .= 'Description was left empty';
			return false;
		} else {
			$this->description = $description;
			return true;
		}
	}

	/**
	 * Set the actual code that shoudl be executed. PHP tags will be striped to be on the safe side
	 *
	 * @param string $var
	 * @return void
	 */
	function setCode($var='') {
		$var = preg_replace('!^\s*\<\?(php)?!i','',$var); // strip start of php tag
		$var = preg_replace('!\?>\s*$!i','',$var); // strip trailing php closing tag
		$this->cronJobCode = $var;
		return true;
	}
	
	/**
	 * Set the concurrency
	 *
	 * @param boolean $var
	 * @return void
	 */
	function setConcurrent($var = true) {
		$this->concurrent = (boolean) ($var);
		return true;
	}
	
	/**
	 * Set the environment / implementation Identifier
	 *
	 * @param integer $var
	 * @return void
	 */
	function setImplementationId($var = 0) {
		$this->implementationId = (int) $var;
		return true;
	}
	
	/**
	 * The cron definition
	 * currently supported is comma seperated per field. No dividers (* / 5 instead use 5,10,15 etc)
	 *
	 * @param string $var
	 * @return void
	 */
	function setCronDefinition($var) {
		$this->cronDefinition = trim(strip_tags($var));
		return true;
	}
	
	/**
	 * Last run UNIX Timestamp
	 *
	 * @param integer $var
	 * @return void
	 */
	function setLastActualTimestamp($var = 0) {
		$this->lastActualTimestamp = (int) $var;
		return true;
	}
	
	/***********************
	 * GETTERS
	 */
	
	/**
	 * Simple getter
	 *
	 * @return string
	 */
	function getTitle() { return $this->title; }
	
	/**
	 * Simple getter
	 *
	 * @return string
	 */
	function getDescription() { return $this->description; }
	
	/**
	 * Simple getter
	 *
	 * @return string
	 */
	function getCode() { return $this->cronJobCode; }
	
	/**
	 * Simple getter
	 *
	 * @return string
	 */
	function getImplementationId() { return $this->implementationId; }
	
	/**
	 * Simple getter
	 *
	 * @return boolean
	 */
	function getConcurrent() { return (boolean) $this->concurrent; }
	
	/**
	 * Simple getter
	 *
	 * @return string
	 */
	function getCronDefinition() { return $this->cronDefinition; }
	
	/**
	 * Get the last run timestamp
	 *
	 * @param string $format
	 * @return string
	 */
	function getLastActualTimestamp($format = false) { 
		if (!$format) return $this->lastActualTimestamp;
		else return date($format, $this->lastActualTimestamp);
	}
}
?>