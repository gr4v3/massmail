<?php
/*
	This SQL query will create the table to store your object.

	CREATE TABLE `rules` (
	`rulesid` int(11) NOT NULL auto_increment,
	`serversid` INT NOT NULL,
	`flood_refresh` INT NOT NULL,
	`flood_interval` INT NOT NULL,
	`throttler_amount` INT NOT NULL,
	`throttler_mode` VARCHAR(255) NOT NULL,
	`logger_active` TINYINT NOT NULL,
	`logger_mode` VARCHAR(255) NOT NULL,
	`decorator_active` TINYINT NOT NULL, PRIMARY KEY  (`rulesid`)) ENGINE=MyISAM;
*/

/**
* <b>rules</b> class with integrated CRUD methods.
* @author Php Object Generator
* @version POG 3.0e / PHP5.1 MYSQL
* @see http://www.phpobjectgenerator.com/plog/tutorials/45/pdo-mysql
* @copyright Free for personal & commercial use. (Offered under the BSD license)
* @link http://www.phpobjectgenerator.com/?language=php5.1&wrapper=pdo&pdoDriver=mysql&objectName=rules&attributeList=array+%28%0A++0+%3D%3E+%27serversid%27%2C%0A++1+%3D%3E+%27flood_refresh%27%2C%0A++2+%3D%3E+%27flood_interval%27%2C%0A++3+%3D%3E+%27throttler_amount%27%2C%0A++4+%3D%3E+%27throttler_mode%27%2C%0A++5+%3D%3E+%27logger_active%27%2C%0A++6+%3D%3E+%27logger_mode%27%2C%0A++7+%3D%3E+%27decorator_active%27%2C%0A%29&typeList=array%2B%2528%250A%2B%2B0%2B%253D%253E%2B%2527INT%2527%252C%250A%2B%2B1%2B%253D%253E%2B%2527INT%2527%252C%250A%2B%2B2%2B%253D%253E%2B%2527INT%2527%252C%250A%2B%2B3%2B%253D%253E%2B%2527INT%2527%252C%250A%2B%2B4%2B%253D%253E%2B%2527VARCHAR%2528255%2529%2527%252C%250A%2B%2B5%2B%253D%253E%2B%2527TINYINT%2527%252C%250A%2B%2B6%2B%253D%253E%2B%2527VARCHAR%2528255%2529%2527%252C%250A%2B%2B7%2B%253D%253E%2B%2527TINYINT%2527%252C%250A%2529
*/
include_once('class.pog_base.php');
class rules extends POG_Base
{
	public $rulesId = '';

	/**
	 * @var INT
	 */
	public $serversid;
	
	/**
	 * @var INT
	 */
	public $flood_refresh;
	
	/**
	 * @var INT
	 */
	public $flood_interval;
	
	/**
	 * @var INT
	 */
	public $throttler_amount;
	
	/**
	 * @var VARCHAR(255)
	 */
	public $throttler_mode;
	
	/**
	 * @var TINYINT
	 */
	public $logger_active;
	
	/**
	 * @var VARCHAR(255)
	 */
	public $logger_mode;
	
	/**
	 * @var TINYINT
	 */
	public $decorator_active;
	
	public $pog_attribute_type = array(
		"rulesId" => array('db_attributes' => array("NUMERIC", "INT")),
		"serversid" => array('db_attributes' => array("NUMERIC", "INT")),
		"flood_refresh" => array('db_attributes' => array("NUMERIC", "INT")),
		"flood_interval" => array('db_attributes' => array("NUMERIC", "INT")),
		"throttler_amount" => array('db_attributes' => array("NUMERIC", "INT")),
		"throttler_mode" => array('db_attributes' => array("TEXT", "VARCHAR", "255")),
		"logger_active" => array('db_attributes' => array("NUMERIC", "TINYINT")),
		"logger_mode" => array('db_attributes' => array("TEXT", "VARCHAR", "255")),
		"decorator_active" => array('db_attributes' => array("NUMERIC", "TINYINT")),
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
	
	function rules($serversid='', $flood_refresh='', $flood_interval='', $throttler_amount='', $throttler_mode='', $logger_active='', $logger_mode='', $decorator_active='')
	{
		$this->serversid = $serversid;
		$this->flood_refresh = $flood_refresh;
		$this->flood_interval = $flood_interval;
		$this->throttler_amount = $throttler_amount;
		$this->throttler_mode = $throttler_mode;
		$this->logger_active = $logger_active;
		$this->logger_mode = $logger_mode;
		$this->decorator_active = $decorator_active;
	}
	
	
	/**
	* Gets object from database
	* @param integer $rulesId 
	* @return object $rules
	*/
	function Get($rulesId)
	{
		$connection = Database::Connect();
		$this->pog_query = "select * from `rules` where `rulesid`='".intval($rulesId)."' LIMIT 1";
		$cursor = Database::Reader($this->pog_query, $connection);
		while ($row = Database::Read($cursor))
		{
			$this->rulesId = $row['rulesid'];
			$this->serversid = $this->Unescape($row['serversid']);
			$this->flood_refresh = $this->Unescape($row['flood_refresh']);
			$this->flood_interval = $this->Unescape($row['flood_interval']);
			$this->throttler_amount = $this->Unescape($row['throttler_amount']);
			$this->throttler_mode = $this->Unescape($row['throttler_mode']);
			$this->logger_active = $this->Unescape($row['logger_active']);
			$this->logger_mode = $this->Unescape($row['logger_mode']);
			$this->decorator_active = $this->Unescape($row['decorator_active']);
		}
		return $this;
	}
	
	
	/**
	* Returns a sorted array of objects that match given conditions
	* @param multidimensional array {("field", "comparator", "value"), ("field", "comparator", "value"), ...} 
	* @param string $sortBy 
	* @param boolean $ascending 
	* @param int limit 
	* @return array $rulesList
	*/
	function GetList($fcv_array = array(), $sortBy='', $ascending=true, $limit='')
	{
		$connection = Database::Connect();
		$sqlLimit = ($limit != '' ? "LIMIT $limit" : '');
		$this->pog_query = "select * from `rules` ";
		$rulesList = Array();
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
			$sortBy = "rulesid";
		}
		$this->pog_query .= " order by ".$sortBy." ".($ascending ? "asc" : "desc")." $sqlLimit";
		$thisObjectName = get_class($this);
		$cursor = Database::Reader($this->pog_query, $connection);
		while ($row = Database::Read($cursor))
		{
			$rules = new $thisObjectName();
			$rules->rulesId = $row['rulesid'];
			$rules->serversid = $this->Unescape($row['serversid']);
			$rules->flood_refresh = $this->Unescape($row['flood_refresh']);
			$rules->flood_interval = $this->Unescape($row['flood_interval']);
			$rules->throttler_amount = $this->Unescape($row['throttler_amount']);
			$rules->throttler_mode = $this->Unescape($row['throttler_mode']);
			$rules->logger_active = $this->Unescape($row['logger_active']);
			$rules->logger_mode = $this->Unescape($row['logger_mode']);
			$rules->decorator_active = $this->Unescape($row['decorator_active']);
			$rulesList[] = $rules;
		}
		return $rulesList;
	}
	
	
	/**
	* Saves the object to the database
	* @return integer $rulesId
	*/
	function Save()
	{
		$connection = Database::Connect();
		$this->pog_query = "select `rulesid` from `rules` where `rulesid`='".$this->rulesId."' LIMIT 1";
		$rows = Database::Query($this->pog_query, $connection);
		if ($rows > 0)
		{
			$this->pog_query = "update `rules` set 
			`serversid`='".$this->Escape($this->serversid)."', 
			`flood_refresh`='".$this->Escape($this->flood_refresh)."', 
			`flood_interval`='".$this->Escape($this->flood_interval)."', 
			`throttler_amount`='".$this->Escape($this->throttler_amount)."', 
			`throttler_mode`='".$this->Escape($this->throttler_mode)."', 
			`logger_active`='".$this->Escape($this->logger_active)."', 
			`logger_mode`='".$this->Escape($this->logger_mode)."', 
			`decorator_active`='".$this->Escape($this->decorator_active)."' where `rulesid`='".$this->rulesId."'";
		}
		else
		{
			$this->pog_query = "insert into `rules` (`serversid`, `flood_refresh`, `flood_interval`, `throttler_amount`, `throttler_mode`, `logger_active`, `logger_mode`, `decorator_active` ) values (
			'".$this->Escape($this->serversid)."', 
			'".$this->Escape($this->flood_refresh)."', 
			'".$this->Escape($this->flood_interval)."', 
			'".$this->Escape($this->throttler_amount)."', 
			'".$this->Escape($this->throttler_mode)."', 
			'".$this->Escape($this->logger_active)."', 
			'".$this->Escape($this->logger_mode)."', 
			'".$this->Escape($this->decorator_active)."' )";
		}
		$insertId = Database::InsertOrUpdate($this->pog_query, $connection);
		if ($this->rulesId == "")
		{
			$this->rulesId = $insertId;
		}
		return $this->rulesId;
	}
	
	
	/**
	* Clones the object and saves it to the database
	* @return integer $rulesId
	*/
	function SaveNew()
	{
		$this->rulesId = '';
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
		$this->pog_query = "delete from `rules` where `rulesid`='".$this->rulesId."'";
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
			$pog_query = "delete from `rules` where ";
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