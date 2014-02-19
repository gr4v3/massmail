<?php
/*
	This SQL query will create the table to store your object.

	CREATE TABLE `queue` (
	`queueid` int(11) NOT NULL auto_increment,
	`rcptid` INT NOT NULL,
	`mailid` INT NOT NULL,
	`sent` TINYINT NOT NULL,
	`state` TINYINT NOT NULL,
	`created` TIMESTAMP NOT NULL,
	`modified` TIMESTAMP NOT NULL,
	`deleted` TIMESTAMP NOT NULL, PRIMARY KEY  (`queueid`)) ENGINE=MyISAM;
*/

/**
* <b>queue</b> class with integrated CRUD methods.
* @author Php Object Generator
* @version POG 3.0e / PHP5.1 MYSQL
* @see http://www.phpobjectgenerator.com/plog/tutorials/45/pdo-mysql
* @copyright Free for personal & commercial use. (Offered under the BSD license)
* @link http://www.phpobjectgenerator.com/?language=php5.1&wrapper=pdo&pdoDriver=mysql&objectName=queue&attributeList=array+%28%0A++0+%3D%3E+%27rcptid%27%2C%0A++1+%3D%3E+%27mailid%27%2C%0A++2+%3D%3E+%27sent%27%2C%0A++3+%3D%3E+%27state%27%2C%0A++4+%3D%3E+%27created%27%2C%0A++5+%3D%3E+%27modified%27%2C%0A++6+%3D%3E+%27deleted%27%2C%0A%29&typeList=array%2B%2528%250A%2B%2B0%2B%253D%253E%2B%2527INT%2527%252C%250A%2B%2B1%2B%253D%253E%2B%2527INT%2527%252C%250A%2B%2B2%2B%253D%253E%2B%2527TINYINT%2527%252C%250A%2B%2B3%2B%253D%253E%2B%2527TINYINT%2527%252C%250A%2B%2B4%2B%253D%253E%2B%2527TIMESTAMP%2527%252C%250A%2B%2B5%2B%253D%253E%2B%2527TIMESTAMP%2527%252C%250A%2B%2B6%2B%253D%253E%2B%2527TIMESTAMP%2527%252C%250A%2529
*/
include_once('class.pog_base.php');
class queue extends POG_Base
{
	public $queueId = '';

	/**
	 * @var INT
	 */
	public $rcptid;
	
	/**
	 * @var INT
	 */
	public $mailid;
	
	/**
	 * @var TINYINT
	 */
	public $sent;
	
	/**
	 * @var TINYINT
	 */
	public $state;
	
	/**
	 * @var TIMESTAMP
	 */
	public $created;
	
	/**
	 * @var TIMESTAMP
	 */
	public $modified;
	
	/**
	 * @var TIMESTAMP
	 */
	public $deleted;
	
	public $pog_attribute_type = array(
		"queueId" => array('db_attributes' => array("NUMERIC", "INT")),
		"rcptid" => array('db_attributes' => array("NUMERIC", "INT")),
		"mailid" => array('db_attributes' => array("NUMERIC", "INT")),
		"sent" => array('db_attributes' => array("NUMERIC", "TINYINT")),
		"state" => array('db_attributes' => array("NUMERIC", "TINYINT")),
		"created" => array('db_attributes' => array("NUMERIC", "TIMESTAMP")),
		"modified" => array('db_attributes' => array("NUMERIC", "TIMESTAMP")),
		"deleted" => array('db_attributes' => array("NUMERIC", "TIMESTAMP")),
		);
	public $pog_query;
	
	function  __construct() {
		//0000-00-00 00:00:00
		$this->modified = date('Y-m-d H:i:s',mktime());
	}
	/**
	* Getter for some private attributes
	* @return mixed $attribute
	*/
	public function __get($attribute)
	{
		if (isset($this->{"_".$attribute}))
		{
			return $this->{"_".$attribute};
		}
		else
		{
			return false;
		}
	}
	
	function queue($rcptid='', $mailid='', $sent='', $state='', $created='', $modified='', $deleted='')
	{
		$this->rcptid = $rcptid;
		$this->mailid = $mailid;
		$this->sent = $sent;
		$this->state = $state;
		$this->created = $created;
		$this->modified = $modified;
		$this->deleted = $deleted;
	}
	
	
	/**
	* Gets object from database
	* @param integer $queueId 
	* @return object $queue
	*/
	function Get($queueId)
	{
		$connection = Database::Connect();
		$this->pog_query = "select * from `queue` where `queueid`='".intval($queueId)."' LIMIT 1";
		$cursor = Database::Reader($this->pog_query, $connection);
		while ($row = Database::Read($cursor))
		{
			$this->queueId = $row['queueid'];
			$this->rcptid = $this->Unescape($row['rcptid']);
			$this->mailid = $this->Unescape($row['mailid']);
			$this->sent = $this->Unescape($row['sent']);
			$this->state = $this->Unescape($row['state']);
			$this->created = $row['created'];
			$this->modified = $row['modified'];
			$this->deleted = $row['deleted'];
		}
		return $this;
	}
	
	
	/**
	* Returns a sorted array of objects that match given conditions
	* @param multidimensional array {("field", "comparator", "value"), ("field", "comparator", "value"), ...} 
	* @param string $sortBy 
	* @param boolean $ascending 
	* @param int limit 
	* @return array $queueList
	*/
	function GetList($fcv_array = array(), $sortBy='', $ascending=true, $limit='')
	{
		$connection = Database::Connect();
		$sqlLimit = ($limit != '' ? "LIMIT $limit" : '');
		$this->pog_query = "select * from `queue` ";
		$queueList = Array();
		if (sizeof($fcv_array) > 0)
		{
			$this->pog_query .= " where ";
			for ($i=0, $c=sizeof($fcv_array); $i<$c; $i++)
			{
				if (sizeof($fcv_array[$i]) == 1)
				{
					$this->pog_query .= " ".$fcv_array[$i][0]." ";
					continue;
				}
				else
				{
					if ($i > 0 && sizeof($fcv_array[$i-1]) != 1)
					{
						$this->pog_query .= " AND ";
					}
					if (isset($this->pog_attribute_type[$fcv_array[$i][0]]['db_attributes']) && $this->pog_attribute_type[$fcv_array[$i][0]]['db_attributes'][0] != 'NUMERIC' && $this->pog_attribute_type[$fcv_array[$i][0]]['db_attributes'][0] != 'SET')
					{
						if ($GLOBALS['configuration']['db_encoding'] == 1)
						{
							$value = POG_Base::IsColumn($fcv_array[$i][2]) ? "BASE64_DECODE(".$fcv_array[$i][2].")" : "'".$fcv_array[$i][2]."'";
							$this->pog_query .= "BASE64_DECODE(`".$fcv_array[$i][0]."`) ".$fcv_array[$i][1]." ".$value;
						}
						else
						{
							$value =  POG_Base::IsColumn($fcv_array[$i][2]) ? $fcv_array[$i][2] : "'".$this->Escape($fcv_array[$i][2])."'";
							$this->pog_query .= "`".$fcv_array[$i][0]."` ".$fcv_array[$i][1]." ".$value;
						}
					}
					else
					{
						$value = POG_Base::IsColumn($fcv_array[$i][2]) ? $fcv_array[$i][2] : "'".$fcv_array[$i][2]."'";
						$this->pog_query .= "`".$fcv_array[$i][0]."` ".$fcv_array[$i][1]." ".$value;
					}
				}
			}
		}
		if ($sortBy != '')
		{
			if (isset($this->pog_attribute_type[$sortBy]['db_attributes']) && $this->pog_attribute_type[$sortBy]['db_attributes'][0] != 'NUMERIC' && $this->pog_attribute_type[$sortBy]['db_attributes'][0] != 'SET')
			{
				if ($GLOBALS['configuration']['db_encoding'] == 1)
				{
					$sortBy = "BASE64_DECODE($sortBy) ";
				}
				else
				{
					$sortBy = "$sortBy ";
				}
			}
			else
			{
				$sortBy = "$sortBy ";
			}
		}
		else
		{
			$sortBy = "queueid";
		}
		$this->pog_query .= " order by ".$sortBy." ".($ascending ? "asc" : "desc")." $sqlLimit";
		$thisObjectName = get_class($this);
		$cursor = Database::Reader($this->pog_query, $connection);
		while ($row = Database::Read($cursor))
		{
			$queue = new $thisObjectName();
			$queue->queueId = $row['queueid'];
			$queue->rcptid = $this->Unescape($row['rcptid']);
			$queue->mailid = $this->Unescape($row['mailid']);
			$queue->sent = $this->Unescape($row['sent']);
			$queue->state = $this->Unescape($row['state']);
			$queue->created = $row['created'];
			$queue->modified = $row['modified'];
			$queue->deleted = $row['deleted'];
			$queueList[] = $queue;
		}
		return $queueList;
	}
	
	
	/**
	* Saves the object to the database
	* @return integer $queueId
	*/
	function Save()
	{
		$connection = Database::Connect();
		$this->pog_query = "select `queueid` from `queue` where `queueid`='".$this->queueId."' LIMIT 1";
		$rows = Database::Query($this->pog_query, $connection);
		if ($rows > 0)
		{
			$this->pog_query = "update `queue` set 
			`rcptid`='".$this->Escape($this->rcptid)."', 
			`mailid`='".$this->Escape($this->mailid)."', 
			`sent`='".$this->Escape($this->sent)."', 
			`state`='".$this->Escape($this->state)."', 
			`created`='".$this->created."', 
			`modified`='".$this->modified."', 
			`deleted`='".$this->deleted."' where `queueid`='".$this->queueId."'";
		}
		else
		{
			$this->pog_query = "insert into `queue` (`rcptid`, `mailid`, `sent`, `state`, `created`, `modified`, `deleted` ) values (
			'".$this->Escape($this->rcptid)."', 
			'".$this->Escape($this->mailid)."', 
			'".$this->Escape($this->sent)."', 
			'".$this->Escape($this->state)."', 
			'".$this->created."', 
			'".$this->modified."', 
			'".$this->deleted."' )";
		}
		$insertId = Database::InsertOrUpdate($this->pog_query, $connection);
		if ($this->queueId == "")
		{
			$this->queueId = $insertId;
		}
		return $this->queueId;
	}
	
	
	/**
	* Clones the object and saves it to the database
	* @return integer $queueId
	*/
	function SaveNew()
	{
		$this->queueId = '';
		$this->created = date('Y-m-d H:i:s',mktime());
		return $this->Save();
	}
	
	
	/**
	* Deletes the object from the database
	* @return boolean
	*/
	function Delete()
	{
		$connection = Database::Connect();
		$this->pog_query = "delete from `queue` where `queueid`='".$this->queueId."'";
		return Database::NonQuery($this->pog_query, $connection);
	}
	
	
	/**
	* Deletes a list of objects that match given conditions
	* @param multidimensional array {("field", "comparator", "value"), ("field", "comparator", "value"), ...} 
	* @param bool $deep 
	* @return 
	*/
	function DeleteList($fcv_array)
	{
		if (sizeof($fcv_array) > 0)
		{
			$connection = Database::Connect();
			$pog_query = "delete from `queue` where ";
			for ($i=0, $c=sizeof($fcv_array); $i<$c; $i++)
			{
				if (sizeof($fcv_array[$i]) == 1)
				{
					$pog_query .= " ".$fcv_array[$i][0]." ";
					continue;
				}
				else
				{
					if ($i > 0 && sizeof($fcv_array[$i-1]) !== 1)
					{
						$pog_query .= " AND ";
					}
					if (isset($this->pog_attribute_type[$fcv_array[$i][0]]['db_attributes']) && $this->pog_attribute_type[$fcv_array[$i][0]]['db_attributes'][0] != 'NUMERIC' && $this->pog_attribute_type[$fcv_array[$i][0]]['db_attributes'][0] != 'SET')
					{
						$pog_query .= "`".$fcv_array[$i][0]."` ".$fcv_array[$i][1]." '".$this->Escape($fcv_array[$i][2])."'";
					}
					else
					{
						$pog_query .= "`".$fcv_array[$i][0]."` ".$fcv_array[$i][1]." '".$fcv_array[$i][2]."'";
					}
				}
			}
			return Database::NonQuery($pog_query, $connection);
		}
	}
	function Inactive()
	{
		$this->state = 2;
		$this->deleted = date('Y-m-d H:i:s',mktime());
		$this->Save();
	}
}
?>