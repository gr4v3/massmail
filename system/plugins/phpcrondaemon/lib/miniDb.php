<?php
/**
 * simple ADODB wrapper
 *
 * @author Casper Langemeijer
 * @copyright Kingsquare Information Services
 */

function NewADOConnection() {
	return new mysqldb();
}
function ADONewConnection() {
	return new mysqldb();
}

class mysqldb {
	var $_link;
	
	function connect($host, $user, $pass, $database) {
		$this->_link = mysql_connect($host, $user, $pass);
		mysql_select_db($database, $this->_link);
	}

	function pconnect($host, $user, $pass, $database) {
		$this->_link = mysql_pconnect($host, $user, $pass);
		mysql_select_db($database, $this->_link);
	}
	
	function close() {
		if (mysql_close($this->_link)) {
			$this->_link = false;
			return true;
		} else return false;
	}
	
	function doSql ($sql) {
		if (!$res = mysql_query($sql, $this->_link)) {
			$errorStack = debug_backtrace();
			$error = $errorStack[1];			
			print '<B>MySQL error:</B> '.$this->ErrorNo().' - '.$this->ErrorMsg().' full Query: "'.$sql.'"';
			print 'calling db library function <B>'.$error['function'].'</B> in <B>'.$error['file'].'</B> on <B>'.$error['line'].'</B><br>';
			trigger_error('see error above', E_USER_ERROR);
		}
		return $res;
	}
	
	function quote($var) {
		return "'".mysql_escape_string($var)."'";
	}

	function getRow($sql) {
		return mysql_fetch_assoc($this->doSql($sql));
	}

	function getOne($sql) {
		$row = mysql_fetch_row($this->doSql($sql));
		return $row[0];
	}
	
	function getCol($sql) {
		$res = $this->doSql($sql);
		$result = array();
		while ($row = mysql_fetch_row($res)) {
			$result[] = $row[0];
		}
		return $result;
	}
	
	function GetAssoc($sql) {
		$res = $this->doSql($sql);
		$result = array();
		while ($row = mysql_fetch_row($res)) {
			$result[$row[0]] = array_slice($row, 1);
		}
		return $result;
	}
	
	function GetAll($sql) {
		$res = $this->doSql($sql);
		$result = array();
		while ($row = mysql_fetch_assoc($res)) {
			$result[] = $row;
		}
		return $result;
	}

	function ErrorNo() {
		return mysql_errno($this->_link);
	}
	
	function ErrorMsg() {
		return mysql_error($this->_link);
	}

	function insert_ID() {
		return mysql_insert_id($this->_link);
	}
	
	function execute($sql) {
		$resource = $this->doSql($sql);
		if ($resource === false) return false;
		if ($resource === true) return true;
		
			$errorStack = debug_backtrace();
			$error = $errorStack[0];
			print '<B>MySQL error:</B> '.$this->ErrorNo().' - '.$this->ErrorMsg().' full Query: "'.$sql.'"';
			print 'calling db library function <B>'.$error['function'].'</B> in <B>'.$error['file'].'</B> on <B>'.$error['line'].'</B><br>';
			trigger_error('see error above', E_USER_ERROR);
		
		return true;
	}
}
?>