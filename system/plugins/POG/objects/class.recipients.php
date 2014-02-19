<?php
/*
	This SQL query will create the table to store your object.

	CREATE TABLE `recipients` (
	`recipientsid` int(11) NOT NULL auto_increment,
	`name` VARCHAR(255) NOT NULL,
	`email` VARCHAR(255) NOT NULL,
	`state` TINYINT NOT NULL,
	`created` TIMESTAMP NOT NULL,
	`modified` TIMESTAMP NOT NULL,
	`deleted` TIMESTAMP NOT NULL, PRIMARY KEY  (`recipientsid`)) ENGINE=MyISAM;
*/

/**
* <b>recipients</b> class with integrated CRUD methods.
* @author Php Object Generator
* @version POG 3.0e / PHP5.1 MYSQL
* @see http://www.phpobjectgenerator.com/plog/tutorials/45/pdo-mysql
* @copyright Free for personal & commercial use. (Offered under the BSD license)
* @link http://www.phpobjectgenerator.com/?language=php5.1&wrapper=pdo&pdoDriver=mysql&objectName=recipients&attributeList=array+%28%0A++0+%3D%3E+%27name%27%2C%0A++1+%3D%3E+%27email%27%2C%0A++2+%3D%3E+%27state%27%2C%0A++3+%3D%3E+%27created%27%2C%0A++4+%3D%3E+%27modified%27%2C%0A++5+%3D%3E+%27deleted%27%2C%0A%29&typeList=array%2B%2528%250A%2B%2B0%2B%253D%253E%2B%2527VARCHAR%2528255%2529%2527%252C%250A%2B%2B1%2B%253D%253E%2B%2527VARCHAR%2528255%2529%2527%252C%250A%2B%2B2%2B%253D%253E%2B%2527TINYINT%2527%252C%250A%2B%2B3%2B%253D%253E%2B%2527TIMESTAMP%2527%252C%250A%2B%2B4%2B%253D%253E%2B%2527TIMESTAMP%2527%252C%250A%2B%2B5%2B%253D%253E%2B%2527TIMESTAMP%2527%252C%250A%2529
*/
include_once('class.pog_base.php');
class recipients extends POG_Base
{
	public $recipientsId = '';

	/**
	 * @var VARCHAR(255)
	 */
	public $name;
	
	/**
	 * @var VARCHAR(255)
	 */
	public $email;
	
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
		"recipientsId" => array('db_attributes' => array("NUMERIC", "INT")),
		"name" => array('db_attributes' => array("TEXT", "VARCHAR", "255")),
		"email" => array('db_attributes' => array("TEXT", "VARCHAR", "255")),
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
	
	function recipients($name='', $email='', $state='', $created='', $modified='', $deleted='')
	{
		$this->name = $name;
		$this->email = $email;
		$this->state = $state;
		$this->created = $created;
		$this->modified = $modified;
		$this->deleted = $deleted;
	}
	
	
	/**
	* Gets object from database
	* @param integer $recipientsId 
	* @return object $recipients
	*/
	function Get($recipientsId)
	{
		$connection = Database::Connect();
		$this->pog_query = "select * from `recipients` where `recipientsid`='".intval($recipientsId)."' LIMIT 1";
		$cursor = Database::Reader($this->pog_query, $connection);
		while ($row = Database::Read($cursor))
		{
			$this->recipientsId = $row['recipientsid'];
			$this->name = $this->Unescape($row['name']);
			$this->email = $this->Unescape($row['email']);
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
	* @return array $recipientsList
	*/
	function GetList($fcv_array = array(), $sortBy='', $ascending=true, $limit='')
	{
		$connection = Database::Connect();
		$sqlLimit = ($limit != '' ? "LIMIT $limit" : '');
		$this->pog_query = "select * from `recipients` ";
		$recipientsList = Array();
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
			$sortBy = "recipientsid";
		}
		$this->pog_query .= " order by ".$sortBy." ".($ascending ? "asc" : "desc")." $sqlLimit";
		$thisObjectName = get_class($this);
		$cursor = Database::Reader($this->pog_query, $connection);
		while ($row = Database::Read($cursor))
		{
			$recipients = new $thisObjectName();
			$recipients->recipientsId = $row['recipientsid'];
			$recipients->name = $this->Unescape($row['name']);
			$recipients->email = $this->Unescape($row['email']);
			$recipients->state = $this->Unescape($row['state']);
			$recipients->created = $row['created'];
			$recipients->modified = $row['modified'];
			$recipients->deleted = $row['deleted'];
			$recipientsList[] = $recipients;
		}
		return $recipientsList;
	}
	
	
	/**
	* Saves the object to the database
	* @return integer $recipientsId
	*/
	function Save()
	{
		$connection = Database::Connect();
		$this->pog_query = "select `recipientsid` from `recipients` where `recipientsid`='".$this->recipientsId."' LIMIT 1";
		$rows = Database::Query($this->pog_query, $connection);
		if ($rows > 0)
		{
			$this->pog_query = "update `recipients` set 
			`name`='".$this->Escape($this->name)."', 
			`email`='".$this->Escape($this->email)."', 
			`state`='".$this->Escape($this->state)."', 
			`created`='".$this->created."', 
			`modified`='".$this->modified."', 
			`deleted`='".$this->deleted."' where `recipientsid`='".$this->recipientsId."'";
		}
		else
		{
			$this->pog_query = "insert into `recipients` (`name`, `email`, `state`, `created`, `modified`, `deleted` ) values (
			'".$this->Escape($this->name)."', 
			'".$this->Escape($this->email)."', 
			'".$this->Escape($this->state)."', 
			'".$this->created."', 
			'".$this->modified."', 
			'".$this->deleted."' )";
		}
		$insertId = Database::InsertOrUpdate($this->pog_query, $connection);
		if ($this->recipientsId == "")
		{
			$this->recipientsId = $insertId;
		}
		return $this->recipientsId;
	}
	
	
	/**
	* Clones the object and saves it to the database
	* @return integer $recipientsId
	*/
	function SaveNew()
	{
		$this->recipientsId = '';
		return $this->Save();
	}
	
	
	/**
	* Deletes the object from the database
	* @return boolean
	*/
	function Delete()
	{
		$connection = Database::Connect();
		$this->pog_query = "delete from `recipients` where `recipientsid`='".$this->recipientsId."'";
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
			$pog_query = "delete from `recipients` where ";
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