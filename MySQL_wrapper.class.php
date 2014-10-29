<?php 
/******************************************************************
 * 
 * Projectname:   PHP MySQL Wrapper Class 
 * Version:       1.6.1
 * Author:        Radovan Janjic <hi@radovanjanjic.com>
 * Link:          https://github.com/uzi88/PHP_MySQL_wrapper
 * Last modified: 29 10 2014
 * Copyright (C): 2008-2014 IT-radionica.com, All Rights Reserved
 * 
 * GNU General Public License (Version 2, June 1991)
 *
 * This program is free software; you can redistribute
 * it and/or modify it under the terms of the GNU
 * General Public License as published by the Free
 * Software Foundation; either version 2 of the License,
 * or (at your option) any later version.
 *
 * This program is distributed in the hope that it will
 * be useful, but WITHOUT ANY WARRANTY; without even the
 * implied warranty of MERCHANTABILITY or FITNESS FOR A
 * PARTICULAR PURPOSE. See the GNU General Public License
 * for more details.
 * 
 ******************************************************************/

/** Execute MySQL queries defined programmatically.
 * @param string 	$server 	- MySQL Host name or ( host:port )
 * @param string 	$username 	- MySQL User
 * @param string 	$password 	- MySQL Password
 * @param string 	$database 	- MySQL Database
 */
class MySQL_wrapper {
	
	/** Class Version 
	 * @var float 
	 */
	private $version = '1.6.2';
	
	/** Store the single instance
	 * @var array
	 */
	private static $instance = array();
	
	/** MySQL Host name  
	 * @var string
	 */
	private $server = NULL;
	
	/** MySQL User
	 * @var string
	 */
	private $username = NULL;
	
	/** MySQL Password
	 * @var string
	 */
	private $password = NULL;
	
	/** MySQL Database
	 * @var string
	 */
	private $database = NULL;
	
	/** mysql / mysqli
	 * @var string
	 */
	public $extension = 'mysqli';
	 
	/** Connection Charset (Default: UTF-8)
	 * @var string
	 */
	public $charset = 'utf8';
	
	/** Error Description 
	 * @var string
	 * */
	public $error = NULL;
	
	/** Error Number 
	 * @var integer
	 */
	public $errorNo = 0;
	
	/** Display Errors (Default: TRUE)
	 * @var boolean
	 */
	public $displayError = TRUE;
	
	/** Link
	 * @var resource
	 */
	public $link = 0;
	
	/** Query
	 * @var resource
	 */
	public $query = 0;
	
	/** Affected Rows 
	 * @var integer
	 */
	public $affected = 0;
	
	/** Previous query
	 * @var string
	 */
	public $prevQuery = NULL;

	/** Log Queries to file (Default: FALSE)
	 * @var boolean
	 */
	public $logQueries = FALSE;
	
	/** Log Errors to file (Default: FALSE)
	 * @var boolean
	 */
	public $logErrors = FALSE;
	
	/** Stop script execution on error (Default: FALSE)
	 * @var boolean
	 */
	public $dieOnError = FALSE;
	
	/** E-mail errors (Default: FALSE)
	 * @var boolean
	 */
	public $emailErrors = FALSE;
	
	/** E-mail errors to (array with emails)
	 * @var array
	 */
	public $emailErrorsTo = array();
	
	/** E-mail errors subject
	 * @var string
	 */
	public $emailErrorsSubject = 'MySQL ERROR ON SERVER: %s';
	
	/** Log Date Format (Default: Y-m-d H:i:s)
	 * @var string
	 */
	public $dateFormat = 'Y-m-d H:i:s';
	
	/** Log File Path (Default: log-mysql.txt)
	 * @var string
	 */
	public $logFilePath = 'log-mysql.txt';
	
	/** Reserved words for array to ( insert / update )
	 * @var array
	 */
	public $reserved = array('null', 'now()', 'current_timestamp', 'curtime()', 'localtime()', 'localtime', 'utc_date()', 'utc_time()', 'utc_timestamp()');
	
	/** Start of MySQL statement for array to ( insert / update )
	 * @var string
	 */
	public $statementStart = 'sql::';
	
	/** REGEX
	 * @var array
	 */
	private $REGEX = array('LIMIT' => '/limit[\s]+([\d]+[\s]*,[\s]*[\d]+[\s]*|[\d]+[\s]*)$/i', 'COLUMN' => '/^[a-z0-9_\-\s]+$/i');
	
	/** Use MySQL SELECT ... INTO OUTFILE (Default: TRUE)
	 * @var boolean
	 */
	private $attachment = FALSE;

	/** Use MySQL SELECT ... INTO OUTFILE (Default: TRUE)
	 * @var boolean
	 */
	public $mysqlOutFile = TRUE;

	/** Singleton declaration
	 * @param 	string 		$server		- MySQL Host name 
	 * @param 	string 		$username 	- MySQL User
	 * @param 	string 		$password 	- MySQL Password
	 * @param 	string 		$database 	- MySQL Database
	 * @return	- singleton instance
	 */
	public static function getInstance($server = NULL, $username = NULL, $password = NULL, $database = NULL) {
		$md5 = md5(implode('|', array($server, $username, $password, $database)));
		if (empty(self::$instance[$md5])) {
			self::$instance[$md5] = new MySQL_wrapper($server, $username, $password, $database);  
		}
		return self::$instance[$md5];
	}
	
	/** Protected constructor to prevent creating a new instance of the MySQL_wrapper via the `new` operator from outside of this class.
	 * @param 	string 		$server		- MySQL Host name 
	 * @param 	string 		$username 	- MySQL User
	 * @param 	string 		$password 	- MySQL Password
	 * @param 	string 		$database 	- MySQL Database
	 */
	protected function __construct($server = NULL, $username = NULL, $password = NULL, $database = NULL) {
		$this->server = $server;
		$this->username = $username;
		$this->password = $password;
		$this->database = $database;
	}
	
	/** Private clone method to prevent cloning of the MySQL_wrapper instance.
	 * @return void
	 */
	private function __clone() {
		// ... void
	}
	
	/** Private unserialize method to prevent unserializing of the MySQL_wrapper instance.
	 * @return void
	 */
	private function __wakeup() {
		// ... void
	}
	
	/** Call function
	 * @param 	string 		$func		- function name
	 * @param 	string 		$params 	- MySQL User
	 * @param 	return
	 */
	public function call($func) {
		// Functions without link parameter
		$l = array('free_result', 'fetch_assoc', 'num_rows', 'num_fields', 'fetch_object', 'fetch_field_direct');
		// Add return value
		$r = array('free_result' => TRUE);
		// Params
		if (func_num_args() >= 2) {
			$params = func_get_args();
			unset($params[0]);
			if ($this->extension == 'mysql') {
				$params = in_array($func, $l) ? $params : array_merge($params, array($this->link));
			} elseif ($this->extension == 'mysqli') {
				$params = in_array($func, $l) ? $params : array_merge(array($this->link), $params);
			}
		} else {
			$params = array($this->link);
		}
		// Return
		if (in_array($func, array_keys($r)) && $this->extension == 'mysqli') {
			call_user_func_array("{$this->extension}_{$func}", $params);
			return $r[$func];
		} else {
			return call_user_func_array("{$this->extension}_{$func}", $params);
		}
	}
	
	/** Connect 
	 * @param 	string 		$server		- MySQL Host name 
	 * @param 	string 		$username 	- MySQL User
	 * @param 	string 		$password 	- MySQL Password
	 * @param 	string 		$database 	- MySQL Database
	 * @param 	boolean		$newLink	- New link
	 * @return 	boolean 
	 */
	public function connect($server = NULL, $username = NULL, $password = NULL, $database = NULL, $newLink = FALSE) {
		if ($server !== NULL && $username !== NULL && $database !== NULL) {
			$this->server = $server;
			$this->username = $username;
			$this->password = $password;
			$this->database = $database;
		}
		
		if ($this->extension == 'mysql') {
			$this->link = @mysql_connect($this->server, $this->username, $this->password, $newLink) or $this->error("Couldn't connect to server: {$this->server}.");
			if ($this->link) {
				$this->setCharset();
				@mysql_select_db($this->database, $this->link) or $this->error("Could not open database: {$this->database}.");
				return TRUE;
			} else {
				return FALSE;	
			}
		} elseif ($this->extension == 'mysqli') {
			$this->link = mysqli_connect($this->server, $this->username, $this->password, $this->database);
			// Check connection
			if (mysqli_connect_errno($this->link)) {
				$this->error("Failed to connect to MySQL: " . mysqli_connect_error());
				return FALSE;
			} else {
				$this->setCharset();
				return TRUE;
			}
		}
	}
	
	/** Sets the default charset for the current connection.
	 * @param 	string 		$charset 	- A valid charset name ( If not defined $this->charset whill be used)
	 * @return	boolean
	 */
	public function setCharset($charset = NULL) {
		$this->charset = $charset ? $charset : $this->charset;
		$this->call('set_charset', $this->charset) or $this->error("Error loading character set {$this->charset}");
	}
	
	/** Checks whether or not the connection to the server is working.
	 * @param 	void
	 * @return 	boolean 
	 */
	public function ping() {
		return $this->call('ping');
	}
	
	/** Reconnect to the server.
	 * @param 	void
	 * @return 	boolean 
	 */
	public function reconnect() {
		$this->close();
		return $this->connect();
	}
	
	/** Close Connection on the server that's associated with the specified link (identifier).
	 * @param 	void
	 */
	public function close() {
		$this->call('close') or $this->error("Connection close failed.");
	}
	
	/** Execute a unique query (multiple queries are not supported) to the currently active database on the server that's associated with the specified link (identifier).
	 * @param 	string 		$sql 		- MySQL Query
	 * @param 	mixed 		- array of params to be escaped or one param
	 * @param 	mixed 		- param
	 * @param 	mixed 		- ...
	 * @return 	resource or false
	 */
	public function query($sql) {
		if (func_num_args() >= 2) {
			$l = func_get_args();
			unset($l[0]);
			$p = array();
			if (is_array($l[1])) {
				$l = $l[1];
			}
			foreach ($l as $k => $v) {
				$p['search'][] = "@{$k}";
				if (preg_match('/^' . preg_quote($this->statementStart) . '/i', $v)) {
					$p['replace'][] = preg_replace('/^' . preg_quote($this->statementStart) . '/i', NULL, $v);
				} else {
					$p['replace'][] = $this->escape($v);
				}
			}
			$sql = str_replace($p['search'], $p['replace'], $sql);
			unset($l, $p);
		}
		if ($this->logQueries) {
			$start = $this->getMicrotime();
		}
		$this->prevQuery = $sql;
		$this->query = $this->call('query', $sql) or $this->error("Query fail: " . $sql);
		$this->affected = $this->call('affected_rows');
		if ($this->query && $this->logQueries) {
			$this->log('QUERY', "EXEC -> " . number_format($this->getMicrotime() - $start, 8) . " -> " . $sql);
		}
		return $this->query ? $this->query : FALSE;
	}
	
	/** Get number of fields in result
	 * @param 	resource 	$query 		- MySQL Query Result
	 * @return 	integer 	- Retrieves the number of fields from a query
	 */
	public function numFields($query = 0) {
		return intval($this->call('num_fields', $query ? $query : $this->query));
	}
	
	/** Get number of rows in result
	 * @param 	resource 	$query 		- MySQL Query Result
	 * @return 	integer 	- Retrieves the number of rows from a result set
	 */
	public function numRows($query = 0) {
		return intval($this->call('num_rows', $query ? $query : $this->query));
	}
	
	/** Get number of rows in result
	 * @param 	resource 	$query 		- Result resource that is being evaluated ( Query Result )
	 * @return 	bool
	 */
	public function freeResult($query = 0) {
		$this->call('free_result', $query ? $query : $this->query) or $this->error("Result could not be freed.");
	}
	
	/** Get Columns names into array
	 * @param 	string 		$table 		- Table name
	 * @return 	array 		$columns 	- Names of Fields
	 */
	public function getColumns($table) {
		$q = $this->query("SHOW COLUMNS FROM `{$table}`;");
		$columns = array();
		while ($row = $this->fetchArray($q)) $columns[] = $row['Field'];
		$this->freeResult($q);
		return $columns;
	}
	
	/** Returns an associative array that corresponds to the fetched row and moves the internal data pointer ahead.
	 * @param 	resource 	$query 		- MySQL Query Result
	 * @return 	array or false
	 */
	public function fetchArray($query = 0) {
		$this->query = $query ? $query : $this->query;
		if ($this->query) {
			return $this->call('fetch_assoc', $this->query);
		} else {
			$this->error("Invalid Query ID: {$this->query}. Records could not be fetched.");
			return FALSE;
		}
	}
	
	/** Returns array with fetched associative rows.
	 * @param 	string 		$sql 		- MySQL Query
	 * @param 	string 		$fetchFirst	- Fetch only first row
	 * @return 	array
	 */
	public function fetchQueryToArray($sql, $fetchFirst = FALSE) {
		if ($fetchFirst) {
			$sql = rtrim(trim($sql), ';');
			$sql = preg_replace($this->REGEX['LIMIT'], 'LIMIT 1;', $sql);
			if (substr($sql, -strlen('LIMIT 1;')) !== 'LIMIT 1;') {
				$sql .= ' LIMIT 1;';
			}
		}
		$q = $this->query($sql);
		$array = array();
		if ($fetchFirst && $this->affected > 0) {
			$array = $this->fetchArray($q);
		} else {
			while ($row = $this->fetchArray($q)) {
				$array[] = $row;
			}
		}
		$this->freeResult($q);
		return $array;
	}
	
	/** Escapes special characters in a string for use in an SQL statement.
	 * @param 	string 		$string - unescaped string
	 * @return 	string
	 */
	public function escape($string) {
		if (!version_compare(PHP_VERSION, '5.4.0') >= 0) {
			$string = get_magic_quotes_gpc() ? stripslashes($string) : $string;
		}
		return $this->call('real_escape_string', $string);
	}
	
	/** Creates an sql string from an associate array
	 * @param 	string 		$table 	- Table name
	 * @param 	array 		$data 	- Data array Eg. $data['column'] = 'val';
	 * @param 	string 		$where 	- MySQL WHERE Clause
	 * @param 	integer 	$limit 	- Limit offset
	 * @return 	number of updated rows or false
	 */
	public function arrayToUpdate($table, $data, $where = NULL, $limit = 0) {
		if (is_array(reset($data))) {
			$cols = array();
			foreach (array_keys($data[0]) as $c) {
				$cols[] = "`{$c}` = VALUES(`{$c}`)";
			}
			return $this->arrayToInsert($table, $data, TRUE, implode(', ', $cols));
		}
		$fields = array();
		foreach ($data as $key => $val) {
			if (in_array(strtolower($val), $this->reserved)) {
				$fields[] = "`{$key}` = " . strtoupper($val);
			} elseif (preg_match('/^' . preg_quote($this->statementStart) . '/i', $val)) {
				$fields[] = "`{$key}` = " . preg_replace('/^' . preg_quote($this->statementStart) . '/i', NULL, $val);
			} else {
				$fields[] = "`{$key}` = '{$this->escape($val)}'";
			}
		}
		return (!empty($fields)) ? $this->query("UPDATE `{$table}` SET " . implode(', ', $fields) . ($where ? " WHERE {$where}" : NULL) . ($limit ? " LIMIT {$limit}" : NULL) . ";") ? $this->affected : FALSE : FALSE;
	}
	
	/** Creates an sql string from an associate array
	 * @param 	string 		$table 	- Table name
	 * @param 	array 		$data 	- Data array Eg. array('column' => 'val') or multirows array(array('column' => 'val'), array('column' => 'val2'))
	 * @param 	boolean		$ingore	- INSERT IGNORE (row won't actually be inserted if it results in a duplicate key)
	 * @param 	string 		$duplicateupdate 	- ON DUPLICATE KEY UPDATE (The ON DUPLICATE KEY UPDATE clause can contain multiple column assignments, separated by commas.)
	 * @return 	insert id or false
	 */
	public function arrayToInsert($table, $data, $ignore = FALSE, $duplicateupdate = NULL) {
		$multirow = is_array(reset($data));
		if ($multirow) {
			$c = implode('`, `', array_keys($data[0]));
			$dat = array();
			foreach ($data as &$val) {
				foreach ($val as &$v) {
					if (in_array(strtolower($v), $this->reserved)) {
						$v = strtoupper($v);
					} elseif (preg_match('/^' . preg_quote($this->statementStart) . '/i', $v)) {
						$v = preg_replace('/^' . preg_quote($this->statementStart) . '/i', NULL, $v);
					} else {
						$v = "'{$this->escape($v)}'";
					}
				}
				$dat[] = "( " . implode(', ', $val) . " )";
			}
			$v = implode(', ', $dat);
		} else {
			$c = implode('`, `', array_keys($data));
			foreach ($data as &$val) {
				if (in_array(strtolower($val), $this->reserved)) {
					$val = strtoupper($val);
				} elseif (preg_match('/^' . preg_quote($this->statementStart) . '/i', $val)) {
					$val = preg_replace('/^' . preg_quote($this->statementStart) . '/i', NULL, $val);
				} else {
					$val = "'{$this->escape($val)}'";
				}
			}
			$v = "( " . implode(', ', $data) . " )";
		}
		return (!empty($data)) ? $this->query("INSERT" . ($ignore ? " IGNORE" : NULL) . " INTO `{$table}` ( `{$c}` ) VALUES {$v}" . ($duplicateupdate ? " ON DUPLICATE KEY UPDATE {$duplicateupdate}" : NULL) . ";") ? ($multirow ? TRUE : $this->insertID()) : FALSE : FALSE;
	}
	
	/** Imports CSV data to Table with possibility to update rows while import.
	 * @param 	string		$file			- CSV File path
	 * @param 	string 		$table 			- Table name
	 * @param	string		$delimiter		- COLUMNS TERMINATED BY (Default: ',')
	 * @param	string 		$enclosure		- OPTIONALLY ENCLOSED BY (Default: '"')
	 * @param 	string		$escape 		- ESCAPED BY (Default: '\')
	 * @param 	integer 	$ignore 		- Number of ignored rows (Default: 1)
	 * @param 	array		$update 		- If row fields needed to be updated eg date format or increment (SQL format only @FIELD is variable with content of that field in CSV row) $update = array('SOME_DATE' => 'STR_TO_DATE(@SOME_DATE, "%d/%m/%Y")', 'SOME_INCREMENT' => '@SOME_INCREMENT + 1')
	 * @param 	string 		$getColumnsFrom	- Get Columns Names from (file or table) - this is important if there is update while inserting (Default: file)
	 * @param 	string 		$newLine		- New line delimiter (Default: auto detection use \n, \r\n ...)
	 * @return 	number of inserted rows or false
	 */
	public function importCSV2Table($file, $table, $delimiter = ',', $enclosure = '"', $escape = '\\', $ignore = 1, $update = array(), $getColumnsFrom = 'file', $newLine = FALSE) {
		$file = file_exists($file) ? realpath($file) : NULL;
		$file = realpath($file);
		if (!file_exists($file)) {
			$this->error('ERROR', "Import CSV to Table - File: {$file} doesn't exist.");
			return FALSE;
		}
		
		if ($newLine === FALSE) {
			$newLine = $this->detectEOL($file);
		}
		
		$sql = "LOAD DATA LOCAL INFILE '{$this->escape($file)}' " . 
			   "INTO TABLE `{$table}` " .
			   "COLUMNS TERMINATED BY '{$delimiter}' " .
			   "OPTIONALLY ENCLOSED BY '{$enclosure}' " . 
			   "ESCAPED BY '{$this->escape($escape)}' " .
			   "LINES TERMINATED BY '{$newLine}' " .
			   ($ignore ? "IGNORE {$ignore} LINES" : NULL);
		
		if (!empty($update)) {
			if ($getColumnsFrom == 'table') {
				$columns = $this->getColumns($table);
			} elseif ($getColumnsFrom == 'file') {
				$f = fopen($file, 'r');
				$line = fgets($f);
				fclose($f);
				$columns = explode($delimiter, str_replace($enclosure, NULL, trim($line)));
				foreach ($columns as $c) {
					preg_match($this->REGEX['COLUMN'], $c) or $this->error("ERROR", "Invalid Column Name: {$c} in CSV file: {$file}. Data can not be loaded into table: {$table}.");
				}
			}
			
			foreach ($columns as &$c) {
				$c = (in_array($c, array_keys($update))) ? '@' . $c : "`{$c}`";
			}
			$sql .= " (" . implode(', ', $columns) .  ") ";
			
			$fields = array();
			foreach ($update as $key => $val) $fields[] = "`{$key}` = {$val}";
			$sql .= "SET " . implode(', ', $fields);
		}
		$sql .= ";";
		return ($this->query($sql)) ? $this->affected : FALSE;
	}
	
	/** Imports (ON DUPLICATE KEY UPDATE) CSV data in Table with possibility to update rows while import.
	 * @param 	string		$file			- CSV File path
	 * @param 	string 		$table 			- Table name
	 * @param	string		$delimiter		- COLUMNS TERMINATED BY (Default: ',')
	 * @param	string 		$enclosure		- OPTIONALLY ENCLOSED BY (Default: '"')
	 * @param 	string		$escape 		- ESCAPED BY (Default: '\')
	 * @param 	integer 	$ignore 		- Number of ignored rows (Default: 1)
	 * @param 	array		$update 		- If row fields needed to be updated eg date format or increment (SQL format only @FIELD is variable with content of that field in CSV row) $update = array('SOME_DATE' => 'STR_TO_DATE(@SOME_DATE, "%d/%m/%Y")', 'SOME_INCREMENT' => '@SOME_INCREMENT + 1')
	 * @param 	string 		$getColumnsFrom	- Get Columns Names from (file or table) - this is important if there is update while inserting (Default: file)
	 * @param 	string 		$newLine		- New line delimiter (Default: auto detection use \n, \r\n ...)
	 * @return 	number of inserted rows or false
	 */
	public function importUpdateCSV2Table($file, $table, $delimiter = ',', $enclosure = '"', $escape = '\\', $ignore = 1, $update = array(), $getColumnsFrom = 'file', $newLine = FALSE) {		
		$tmp_name = "{$table}_tmp_" . rand();
		
		// Create tmp table
		$this->query("CREATE TEMPORARY TABLE `{$tmp_name}` LIKE `{$table}`;");
		
		// Remove auto_increment if exists
		$change = array();
		$this->query("SHOW COLUMNS FROM `{$tmp_name}` WHERE `Key` NOT LIKE '';");
		if ($this->affected > 0) {
			while ($row = $this->fetchArray()) {
				$change[$row['Field']] = "CHANGE `{$row['Field']}` `{$row['Field']}` {$row['Type']}";
			}
			$this->freeResult();
		}
		
		if ($getColumnsFrom == 'file') {
			// Get first line of file
			$f = fopen($file, 'r');
			$line = fgets($f);
			fclose($f);
			
			$columns = explode($delimiter, str_replace($enclosure, NULL, trim($line)));
			
			foreach ($columns as $c) {
				preg_match($this->REGEX['COLUMN'], $c) or $this->error("ERROR", "Invalid Column Name: {$c} in CSV file: {$file}. Data can not be loaded into table: {$table}.");
			}
			
			// Drop columns that are not in CSV file
			foreach ($this->getColumns($table) as $c) {
				if (!in_array($c, $columns, TRUE)) {
					$change[$c] = "DROP COLUMN `{$c}`";
				}
			}
		}
		
		if (count($change) > 0) {
			$this->query("ALTER TABLE `{$tmp_name}` " . implode(', ', $change) . ";");
		}
		
		// Import to tmp
		$this->importCSV2Table($file, $tmp_name, $delimiter, $enclosure, $escape, $ignore, $update, $getColumnsFrom, $newLine);
		
		// Copy data
		$cols = array();
		if ($getColumnsFrom == 'table') {
			$columns = $this->getColumns($tmp_name);
		}
		
		foreach ($columns as $c) {
			$cols[] = "`{$c}` = VALUES(`{$c}`)";
		}
		
		$this->query("INSERT INTO `{$table}` ( `" . implode('`, `', $columns) . "` ) SELECT * FROM `{$tmp_name}` ON DUPLICATE KEY UPDATE " . implode(', ', $cols) . ";");
		$i = $this->affected;
		
		// Drop tmp table
		$this->query("DROP TEMPORARY TABLE `{$tmp_name}`;");
		
		return $i;
	}
	
	/** Export table data to CSV file.
	 * @param 	string 		$table 			- Table name
	 * @param 	string		$file			- CSV File path
	 * @param 	mixed 		$columns 		- SQL ( * or column names or array with column names)
	 * @param 	string 		$where 			- MySQL WHERE Clause
	 * @param 	integer 	$limit 			- Limit offset
	 * @param	string		$delimiter		- COLUMNS TERMINATED BY (Default: ',')
	 * @param	string 		$enclosure		- OPTIONALLY ENCLOSED BY (Default: '"')
	 * @param 	string		$escape 		- ESCAPED BY (Default: '\')
	 * @param 	string 		$newLine		- New line delimiter (Default: \n)
	 * @param 	boolean		$showColumns 	- Columns names in first line
	 * @return 	- File path
	 */
	public function exportTable2CSV($table, $file, $columns = '*', $where = NULL, $limit = 0, $delimiter = ',', $enclosure = '"', $escape = '\\', $newLine = '\n', $showColumns = TRUE) {
		// Without OUTFILE or as attachment
		if ($this->attachment || !$this->mysqlOutFile) {
			return $this->query2CSV("SELECT * FROM `$table`" . ($where ? " WHERE {$where}" : NULL) . ($limit ? " LIMIT {$limit}" : NULL), $file, $delimiter, $enclosure, $escape, $newLine, $showColumns);
		}
		
		$fh = fopen($file, 'w') or $this->error("ERROR", "Can't create CSV file: {$file}");
		if (!$fh) {
			return FALSE;
		}
		fclose($fh);
		$file = realpath($file);
		unlink($file);
		
		// Put columns into array if not *
		if ($columns != '*' && !is_array($columns)) {
			$stringColumns = $columns;
			$columns = array();
			foreach (explode(',', $stringColumns) as $c) {
				$columns[] = trim(str_replace(array("'", "`", "\""), NULL, $c));
			}
		}
		
		// Prepare SQL for column names
		if ($showColumns) {
			$tableColumnsArr = array();
			if ($columns == '*') {
				foreach ($this->getColumns($table) as $c)
					$tableColumnsArr[] = "'{$c}' AS `{$c}`";
			} elseif (is_array($columns)) {
				foreach ($columns as $c)
					$tableColumnsArr[] = "'{$c}' AS `{$c}`";
			}
			$columnsSQL = "SELECT " . implode(', ', $tableColumnsArr);
		}
		
		$sql = "SELECT " . (is_array($columns) ? '`' . implode('`, `', $columns) . '`' : $columns) . " FROM `{$table}`" . ($where ? " WHERE {$where}" : NULL) . ($limit ? " LIMIT {$limit}" : NULL);
		$sql = (($showColumns) ? "SELECT * FROM ( ( " . $columnsSQL . " ) UNION ALL ( {$sql} ) ) `a` " : "{$sql} ") .
			   "INTO OUTFILE '{$this->escape($file)}' " . 
			   "FIELDS TERMINATED BY '{$delimiter}' " .
			   "OPTIONALLY ENCLOSED BY '{$enclosure}' " .
			   "ESCAPED BY '{$this->escape($escape)}' " .
			   "LINES TERMINATED BY '{$newLine}';";
		return ($this->query($sql)) ? $file : FALSE;
	}
	
	/** Set attachment var and return object.
	 * @param 	void
	 * @return 	- obj
	 */
	function attachment() {
		$this->attachment = TRUE;
		return $this;
	}
	
	/** Export query to CSV file.
	 * @param 	string 		$sql 			- MySQL Query
	 * @param 	string		$file			- CSV File path
	 * @param	string		$delimiter		- COLUMNS TERMINATED BY (Default: ',')
	 * @param	string 		$enclosure		- OPTIONALLY ENCLOSED BY (Default: '"')
	 * @param 	string		$escape 		- ESCAPED BY (Default: '\')
	 * @param 	string 		$newLine		- New line delimiter (Default: \n)
	 * @param 	boolean		$showColumns 	- Columns names in first line
	 * @return 	- File path
	 */
	public function query2CSV($sql, $file, $delimiter = ',', $enclosure = '"', $escape = '\\', $newLine = '\n', $showColumns = TRUE) {
		// Without OUTFILE or as attachment
		if ($this->attachment || !$this->mysqlOutFile) {
			// Do query
			$this->query($sql);
			if ($this->affected > 0) {
				$fh = fopen($this->attachment ? 'php://output' : $file, 'w') or $this->error("ERROR", "Can't create CSV file: {$file}");
				if ($fh) {
					if ($this->attachment) {
						// Send response headers
						header('Content-Type: text/csv');
						header('Content-Disposition: attachment; filename="' . basename($file));
						header('Pragma: no-cache');
						header('Expires: 0');
						$this->attachment = FALSE;
					}
					$header = FALSE;
					while ($row = $this->fetchArray()) {
						// CSV header / field names
						if ($showColumns && !$header) {
							fputcsv($fh, array_keys($row), $delimiter, $enclosure);
							$header = TRUE;
						}
						fputcsv($fh, array_values($row), $delimiter, $enclosure);
					}
					fclose($fh);
					return $this->affected;
				} else {
					$this->attachment = FALSE;
					return FALSE;
				}
			} else {
				$this->attachment = FALSE;
				// No records
				return 0;
			}
		}
		
		// Check if location is writable and unlink
		$fh = fopen($file, 'w') or $this->error("ERROR", "Can't create CSV file: {$file}");
		if (!$fh) {
			return FALSE;
		}
		fclose($fh);
		$file = realpath($file);
		unlink($file);
		
		// Remove ; from end of query
		$sql = trim(rtrim(trim($sql), ';'));
		// Prepare SQL for column names
		if ($showColumns) {
			$r = $this->query((preg_match($this->REGEX['LIMIT'], $sql)) ? preg_replace($this->REGEX['LIMIT'], 'LIMIT 1;', $sql) : $sql . ' LIMIT 1;');
			if ($r !== FALSE && $this->affected > 0) {
				$columns = $this->fetchArray($r);
				$this->freeResult($r);
				$tableColumnsArr = array();
				foreach ($columns as $k => $v) {
					$tableColumnsArr[] = "'{$k}' AS `{$k}`";
				}
				$columnsSQL = "SELECT " . implode(', ', $tableColumnsArr);
			} else {
				// No results for this query
				return 0;
			}
		}
		// Final query
		$sql = (($showColumns && isset($columnsSQL)) ? "SELECT * FROM ( ( " . $columnsSQL . " ) UNION ALL ( {$sql} ) ) `a` " : "{$sql} ") .
			   "INTO OUTFILE '{$this->escape($file)}' " .
			   "FIELDS TERMINATED BY '{$delimiter}' " .
			   "OPTIONALLY ENCLOSED BY '{$enclosure}' " .
			   "ESCAPED BY '{$this->escape($escape)}' " .
			   "LINES TERMINATED BY '{$newLine}';";
		return ($this->query($sql)) ? $file : FALSE;
	}
	
	/** Export query to XML file or return as XML string
	 * @param	string		$query				- mysql query
	 * @param	string		$rootElementName	- root element name
	 * @param	string		$childElementName	- child element name
	 * @return	string		- XML
	 */
	public function query2XML($query, $rootElementName, $childElementName, $file = NULL) {
		// Save to file or attachment
		if ($this->attachment || !empty($file)) { //echo $file; exit;
			$fh = fopen($this->attachment ? 'php://output' : $file, 'w') or $this->error("ERROR", "Can't create XML file: {$file}");
			if (!$fh) {
				return FALSE;
			} elseif ($this->attachment) {
				// Send response headers
				header('Content-Type: text/xml');
				header('Content-Disposition: attachment; filename="' . basename($file));
				header('Pragma: no-cache');
				header('Expires: 0');
				$this->attachment = FALSE;
			} else {
				$file = realpath($file);
			}
			$saveToFile = TRUE;
		} else {
			$saveToFile = FALSE;
		}
		
		// Do query
		$r = $this->query($query);
		
		// XML header
		if ($saveToFile) {
			fputs($fh, "<?xml version=\"1.0\" encoding=\"" . strtoupper($this->charset) . "\" ?>" . PHP_EOL . "<{$rootElementName}>" . PHP_EOL);
		} else {
			$xml = "<?xml version=\"1.0\" encoding=\"" . strtoupper($this->charset) . "\" ?>" . PHP_EOL;
			$xml .= "<{$rootElementName}>" . PHP_EOL;
		}
		
		// Query rows
		while ($row = $this->call('fetch_object', $r)) {
			// Create the first child element
			$record = "\t<{$childElementName}>" . PHP_EOL;
			for ($i = 0; $i < $this->call('num_fields', $r); $i++) {
				// Different methods of getting field name for mysql and mysqli
				if ($this->extension == 'mysql') {
					$fieldName = $this->call('field_name', $r, $i);
				} elseif ($this->extension == 'mysqli') {
					$colObj = $this->call('fetch_field_direct', $r, $i);                            
					$fieldName = $colObj->name;
				}
				// The child will take the name of the result column name
				$record .= "\t\t<{$fieldName}>";
				// Set empty columns with NULL and escape XML entities
				if (!empty($row->$fieldName)) {
					$record .= htmlspecialchars($row->$fieldName, ENT_XML1);
				} else {
					$record .= NULL; 
				}
				$record .= "</{$fieldName}>" . PHP_EOL;
			}
			$record .= "\t</{$childElementName}>" . PHP_EOL;
			if ($saveToFile) {
				fputs($fh, $record);
			} else {
				$xml .= $record;
			}
		}
		
		// Output
		if ($saveToFile) {
			fputs($fh, "</{$rootElementName}>" . PHP_EOL);
			fclose($fh);
			return TRUE;
		} else {
			$xml .= "</{$rootElementName}>" . PHP_EOL;
			return $xml;
		}
	}
	
	/** Create table from CSV file and imports CSV data to Table with possibility to update rows while import.
	 * @param 	string		$file			- CSV File path
	 * @param 	string 		$table 			- Table name
	 * @param	string		$delimiter		- COLUMNS TERMINATED BY (Default: ',')
	 * @param	string 		$enclosure		- OPTIONALLY ENCLOSED BY (Default: '"')
	 * @param 	string		$escape 		- ESCAPED BY (Default: '\')
	 * @param 	integer 	$ignore 		- Number of ignored rows (Default: 1)
	 * @param 	array		$update 		- If row fields needed to be updated eg date format or increment (SQL format only @FIELD is variable with content of that field in CSV row) $update = array('SOME_DATE' => 'STR_TO_DATE(@SOME_DATE, "%d/%m/%Y")', 'SOME_INCREMENT' => '@SOME_INCREMENT + 1')
	 * @param 	string 		$getColumnsFrom	- Get Columns Names from (file or generate) - this is important if there is update while inserting (Default: file)
	 * @param 	string 		$newLine		- New line delimiter (Default: auto detection use \n, \r\n ...)
	 * @return 	number of inserted rows or false
	 */
	public function createTableFromCSV($file, $table, $delimiter = ',', $enclosure = '"', $escape = '\\', $ignore = 1, $update = array(), $getColumnsFrom = 'file', $newLine = FALSE) {
		$file = file_exists($file) ? realpath($file) : NULL;
		if ($file === NULL) {
			$this->error('ERROR', "Create Table form CSV - File: {$file} doesn't exist.");
			return FALSE;
		} else {
			$f = fopen($file, 'r');
			$line = fgets($f);
			fclose($f);
			$data = explode($delimiter, str_replace($enclosure, NULL, trim($line)));
			$columns = array();
			$i = 0;
			
			foreach ($data as $c) {
				if ($getColumnsFrom == 'generate') {
					$c = 'column_' . $i++;
				}
				if (preg_match($this->REGEX['COLUMN'], $c)) {
					$columns[] = "`{$c}` BLOB NULL";
				} else {
					$this->error('ERROR', "Invalid column name: {$c} in file: {$file}");
					return FALSE;
				}
			}
			
			$this->query("CREATE TABLE `{$table}` ( " . implode(', ', $columns) . " ) ENGINE=InnoDB DEFAULT CHARSET={$this->charset};");
			if ($this->importCSV2Table($file, $table, $delimiter, $enclosure, $escape, $ignore, $update, ($getColumnsFrom == 'generate') ? 'table' : 'file', $newLine) > 0) {
				$columns = $this->fetchQueryToArray("SELECT * FROM `{$table}` PROCEDURE ANALYSE ( 10, 30 );", FALSE);
				$change = array();
				foreach ($columns as $c) {
					$c['Field_name'] = implode('`.`', explode('.', $c['Field_name']));
					$change[] = "CHANGE `{$c['Field_name']}` `{$c['Field_name']}` {$c['Optimal_fieldtype']}";
				}
				$this->query("ALTER TABLE `{$table}` " . implode(', ', $change) . ";");
			}
		}
	}
	
	/** Rename table(s)
	 * @param 	array 		$table 	- Names of the tables eg -> array('old_table' => 'new_table') or array('table1' => 'tmp_table', 'table2' => 'table1', 'tmp_table' => 'table1')
	 * @return 	resource or false
	 */
	public function renameTable($table) {
		$rename = array();
		foreach ($table as $old => $new) {
			$rename[] = "`{$old}` TO `{$new}`";
		}
		return $this->query("RENAME TABLE " . implode(', ', $rename) . ";"); 
	}

	/** Copy table structure or structure and data.
	 * @param 	string 		$table 		- Table name
	 * @param 	string 		$new_table 	- New table name
	 * @param 	boolean		$data 		- Copy table data
	 * @return 	resource or false
	 */
	public function copyTable($table, $new_table, $data = TRUE) {
		$r = $this->query("CREATE TABLE `{$new_table}` LIKE `{$table}`;");
		return ($r && $data) ? $this->query("INSERT INTO `{$new_table}` SELECT * FROM `{$table}`;") : $r;
	}

	/** Truncate table
	 * @param 	string 		$table 		- Table name
	 * @return 	resource or false
	 */
	public function truncateTable($table) {
		return $this->query("TRUNCATE TABLE `{$table}`;"); 
	}
	
	/** Drop table(s)
	 * @param 	array 		$table 		- Names of the tables eg -> array('table1', 'table2')
	 * @param 	boolean		$if_exists	- Use IF EXISTS to prevent an error from occurring for tables that do not exist.
	 * @return 	resource or false
	 */
	public function dropTable($table, $if_exists = TRUE) {
		return $this->query("DROP TABLE " . ($if_exists ? "IF EXISTS " : NULL) . "`" . (is_array($table) ? implode('`, `', $table) : $table) . "`;"); 
	}
	
	/** Data Base size in B / KB / MB / GB / TB
	 * @param 	string	 	$sizeIn		- Size in B / KB / MB / GB / TB
	 * @param 	integer	 	$round		- Round on decimals
	 * @return 	- Size in B / KB / MB / GB / TB
	 */
	public function getDataBaseSize($sizeIn = 'MB', $round = 2) {
		$r = $this->query("SELECT ROUND( SUM( `data_length` + `index_length` ) " . str_repeat('/ 1024 ', array_search(strtoupper($sizeIn), array('B', 'KB', 'MB', 'GB', 'TB'))) . ", {$round} ) `size` FROM `information_schema`.`TABLES` WHERE `table_schema` LIKE '{$this->database}' GROUP BY `table_schema`;");
		if ($r !== FALSE) {
			$row = $this->fetchArray($r);
			$this->freeResult($r);
			return $row['size'];
		} else {
			return FALSE;
		}
	}
	
	/** Retrieves the ID generated for an AUTO_INCREMENT column by the previous query.
	 * @param 	void
	 * @return 	integer
	 */
	public function insertID() {
		return $this->call('insert_id');
	}
	
	/** Retrieves the number of rows from table based on certain conditions.
	 * @param 	string 		$table 	- Table name
	 * @param 	string 		$where 	- WHERE Clause
	 * @return 	integer or false
	 */
	public function countRows($table, $where = NULL) {
		$r = $this->query("SELECT COUNT( * ) AS count FROM `{$table}` " . ($where ? " WHERE {$where}" : NULL) . ";");
		if ($r !== FALSE) {
			$row = $this->fetchArray($r);
			$this->freeResult($r);
			return $row['count'];
		} else {
			return FALSE;
		}
	}
	
	/** Retrieves next auto increment value.
	 * @param 	string 		$table 	- Table name
	 * @return 	integer or false
	 */
	public function nextAutoIncrement($table) {
		$r = $this->query("SHOW TABLE STATUS LIKE '{$table}';");  
		if ($r !== FALSE) {
			$row = $this->fetchArray();  
			$this->freeResult($r);
			return $row['Auto_increment'];  
		} else {
			return FALSE;
		}
	}
	
	/** Delete row(s) from table based on certain conditions.
	 * @param 	string 		$table 	- Table name
	 * @param 	string 		$where 	- WHERE Clause
	 * @param 	integer 	$limit 	- Limit offset
	 * @return 	number of deleted rows or false
	 */
	public function deleteRow($table, $where = NULL, $limit = 0) {
		return $this->query("DELETE FROM `{$table}`" . ($where ? " WHERE {$where}" : NULL) . ($limit ? " LIMIT {$limit}" : NULL) . ";") ? $this->affected : FALSE;
	}
		
	/** Replace all occurrences of the search string with the replacement string in MySQL Table Column(s).
	 * @param 	string		$table 	 - Table name or "*" to replace in whole db
	 * @param 	mixed 		$columns - Search & Replace affected Table columns. An array may be used to designate multiple replacements.
	 * @param 	mixed 		$search  - The value being searched for, otherwise known as the needle. An array may be used to designate multiple needles.
	 * @param 	mixed 		$replace - The replacement value that replaces found search values. An array may be used to designate multiple replacements.
	 * @param 	string 		$where 	 - WHERE Clause
	 * @param 	integer 	$limit 	 - Limit offset
	 * @return  integer 	- Affected rows
	 */
	public function strReplace($table, $columns, $search, $replace, $where = NULL, $limit = 0) {
		// Replace in whole DB
		if ($table == '*') {
			if (!is_array($columns)) {
				$stringColumns = $columns;
				if ($stringColumns != '*') {
					// Put columns into array
					$columns = array();
					if (preg_match($this->REGEX['COLUMN'], $stringColumns)) {
						$columns[] = $stringColumns;
					} else {
						foreach (explode(',', $stringColumns) as $c) {
							$columns[] = trim(str_replace(array("'", "`", "\""), NULL, $c));
						}
					}
					if (empty($columns)) {
						return FALSE;
					}
				}
			}
			$q = $this->query(
				"SELECT DISTINCT `table_name` AS `table`, GROUP_CONCAT(DISTINCT `column_name` ORDER BY `column_name`) AS `columns` FROM `information_schema`.`columns` " .
				"WHERE (`data_type` LIKE '%char%' OR `data_type` LIKE '%text' OR `data_type` LIKE '%binary')" . (($stringColumns != '*') ? " AND `column_name` IN('" . implode("', '", $columns) . "')" : NULL) . " AND `table_schema` = '{$this->database}' " .
				"GROUP BY `table_name` ORDER BY `table_name`;"
			);
			$affected = 0;
			if ($this->affected > 0) {
				while ($row = $this->fetchArray($q)) {
					$affected += $this->strReplace($row['table'], $row['columns'], $search, $replace, $where, $limit);
				}
			}
			$this->freeResult($q);
			return $affected;
		}
		
		// Columns
		if (!is_array($columns)) {
			$stringColumns = $columns;
			$columns = array();
			if ($stringColumns == '*') {
				$columns = $this->getColumns($table);
			} elseif (preg_match($this->REGEX['COLUMN'], $stringColumns)) {
				$columns[] = $stringColumns;
			} else {
				// Put columns into array if not *
				$columns = array();
				foreach (explode(',', $stringColumns) as $c) {
					$columns[] = trim(str_replace(array("'", "`", "\""), NULL, $c));
				}
			}
		}
		
		// Update
		$update = array();
		foreach ($columns as $col) {
			if (is_array($search)) {
				foreach ($search as $k => $s) {
					$update[] = "`{$col}` = REPLACE(`{$col}`, '{$this->escape($s)}', '{$this->escape(is_array($replace) ? $replace[$k] : $replace)}')";
				}
			} else {
				$update[] = "`{$col}` = REPLACE(`{$col}`, '{$this->escape($search)}', '{$this->escape($replace)}')";
			}
		}
		$this->query("UPDATE `{$table}` SET " . implode(', ', $update) . ($where ? " WHERE {$where}" : NULL) . ($limit ? " LIMIT {$limit}" : NULL) . ";");
		return $this->affected;
	}

	/** Begin Transaction
	 * @param 	void
	 */
	public function begin() { 
		$this->query("START TRANSACTION;"); 
		return $this->query("BEGIN;"); 
	}
	
	/** Commit
	 * @param 	void
	 */
	public function commit() { 
		return $this->query("COMMIT;"); 
	} 
	
	/** Rollback
	 * @param 	void
	 */
	public function rollback() { 
		return $this->query("ROLLBACK;"); 
	} 
	
	/** Transaction
	 * @param 	array		$qarr	- Array with Queries
	 * @link	http://dev.mysql.com/doc/refman/5.0/en/commit.html
	 */
	public function transaction($qarr = array()) { 
		$commit = TRUE;
		$this->begin(); 
		foreach ($qarr as $q) { 
			$this->query($q);
			if ($this->affected == 0) $commit = FALSE;
		}
		if ($commit == FALSE) {
			$this->rollback();
			return FALSE;
		} else {
			$this->commit();
			return TRUE;
		}
	}
	
	/** Init table revision
	 * @param 	string		$table	- Table name
	 */
	public function initTableRevision($table) {
		// Revision table name
		$rev_table = "{$table}_revision";
		
		// Create tmp table
		$this->query("CREATE TABLE `{$rev_table}` LIKE `{$table}`;");
		
		// Remove auto_increment if exists
		$change = array();
		$this->query("SHOW COLUMNS FROM `{$rev_table}` WHERE `Key` NOT LIKE '' OR `Default` IS NOT NULL;");
		if ($this->affected > 0) {
			while ($row = $this->fetchArray()) {
				$change[$row['Field']] = "CHANGE `{$row['Field']}` `{$row['Field']}` {$row['Type']} DEFAULT " . (($row['Extra']) ? 0 : 'NULL');
			}
			$this->freeResult();
		}
		// Alter revision table
		$this->query("ALTER TABLE `{$rev_table}` " . implode(', ', $change) . ";");
		
		
		// Remove indexes from revision table
		$this->query("SHOW INDEXES FROM `{$rev_table}`;");
		$drop = array();
		if ($this->affected > 0) {
			while ($row = $this->fetchArray()) {
				$drop[] = "DROP INDEX `{$row['Key_name']}`";
			}
			$this->freeResult();
		}
		$this->query("ALTER TABLE `{$rev_table}` " . implode(', ', $drop) . ";");
		
		$change = array();
		// Add revision fields
		$change['revision_timestamp'] = "ADD `revision_timestamp` TIMESTAMP ON UPDATE CURRENT_TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP FIRST";
		$change['revision_action'] = "ADD `revision_action` enum('INSERT', 'UPDATE', 'DELETE') DEFAULT NULL FIRST";
		$change['revision_user'] = "ADD `revision_user` CHAR( 256 ) NOT NULL FIRST";
		$change['revision_id'] = "ADD `revision_id` INT NOT NULL AUTO_INCREMENT FIRST";

		// Add keys
		$change[] = "ADD KEY (`revision_action`, `revision_timestamp`)";
		$change[] = "ADD KEY `revision_timestamp` (`revision_timestamp`)";
		$change[] = "ADD PRIMARY KEY `revision_id` (`revision_id`)";
		// Alter revision table
		$this->query("ALTER TABLE `{$rev_table}` " . implode(', ', $change) . ";");
		
		$columns = $this->getColumns($table);
		
		// Insert trigger
		$this->query(
			"CREATE TRIGGER `{$table}_revision_insert` AFTER INSERT ON `{$table}` " .
			"FOR EACH ROW " .
			"BEGIN " .
				"INSERT INTO `{$rev_table}` (`revision_action`, `revision_timestamp`, `revision_user`, `" . implode('`, `', $columns) . "`) VALUES ('INSERT', NOW(), USER(),  NEW.`" . implode('`, NEW.`', $columns) . "`); " .
			"END;"
		);
		
		// Update trigger
		$this->query(
			"CREATE TRIGGER `{$table}_revision_update` AFTER UPDATE ON `{$table}` " .
			"FOR EACH ROW " .
			"BEGIN " .
				"INSERT INTO `{$rev_table}` (`revision_action`, `revision_timestamp`, `revision_user`, `" . implode('`, `', $columns) . "`) VALUES ('UPDATE', NOW(), USER(), NEW.`" . implode('`, NEW.`', $columns) . "`); " .
			"END;" 
		);
		
		// Delete trigger
		$this->query(
			"CREATE TRIGGER `{$table}_revision_delete` AFTER DELETE ON `{$table}` " .
			"FOR EACH ROW " .
			"BEGIN " .
				"INSERT INTO `{$rev_table}` (`revision_action`, `revision_timestamp`, `revision_user`, `" . implode('`, `', $columns) . "`) VALUES ('DELETE', NOW(), USER(), OLD.`" . implode('`, OLD.`', $columns) . "`); " .
			"END;"
		);
		
		// Insert existing data into revision table
		$this->query(
			"INSERT INTO `{$rev_table}` (`revision_action`, `revision_timestamp`, `revision_user`, `" . implode('`, `', $columns) . "`) " .
			"SELECT 'INSERT' AS `revision_action`, NOW() AS `revision_timestamp`, USER() AS `revision_user`, `{$table}`.* FROM `{$table}`;"
		);
	}
	
	/** Create table from current revision time
	 * @param 	string		$table		- New table name
	 * @param	string 		$rev_table	- Revision table (origin table)
	 * @param	string 		$id_field	- Unique field name
	 * @param	datetime	- Revision time
	 */
	public function createTableFromRevisionTime($table, $rev_table, $id_field, $time) {
		$time = strtotime($time);
		$columns = $this->getColumns($rev_table);
		
		// Status at the time, use for update
		$this->query(
			"CREATE TABLE `{$table}` " .
			"SELECT `" . implode('`, `', $columns) . "` " .
			"FROM (" .
					"SELECT `" . implode('`, `', $columns) . "` " .
					"FROM `{$rev_table}_revision` " .
					"WHERE `revision_timestamp` <= STR_TO_DATE('" . date('Y-m-d H:i:s', $time) . "', '%Y-%m-%d %H:%i:%s') " .
					"ORDER BY `revision_timestamp` DESC".
				") AS `b` " .
			"WHERE `{$id_field}` NOT IN(" .
				"SELECT `{$id_field}` " .
				"FROM `{$rev_table}_revision` " .
				"WHERE `revision_timestamp` <= STR_TO_DATE('" . date('Y-m-d H:i:s', $time) . "', '%Y-%m-%d %H:%i:%s') AND `revision_action` LIKE 'DELETE'" .
			") GROUP BY `{$id_field}`;"
		);
	}
	
	/** Restore table from current revision time
	 * @param 	string		$table		- New table name
	 * @param	string 		$id_field	- Unique field name
	 * @param	datetime	- Revision time
	 */
	public function restoreTableFromRevisionTime($table, $id_field, $time) {
		$time = strtotime($time);
		$columns = $this->getColumns($table);
		$cols = array();
		foreach ($columns as $c) {
			$cols[] = "`{$c}` = VALUES(`{$c}`)";
		}
		
		// Remove added items after defined time
		$this->query(
			"DELETE FROM `{$table}` " .
			"WHERE `{$id_field}` IN(" .
				"SELECT `{$id_field}` " .
				"FROM `{$table}_revision` " .
				"WHERE `revision_action` = 'INSERT' AND `revision_timestamp` > STR_TO_DATE('" . date('Y-m-d H:i:s', $time) . "', '%Y-%m-%d %H:%i:%s') " .
				"GROUP BY `{$id_field}`" .
			");"
		);
		
		// Update
		$this->query(
			"INSERT INTO `{$table}` (`" . implode('`, `', $columns) . "`) " .
				"SELECT `" . implode('`, `', $columns) . "` " .
				"FROM (" .
						"SELECT `" . implode('`, `', $columns) . "` " .
						"FROM `{$table}_revision` " .
						"WHERE `revision_timestamp` <= STR_TO_DATE('" . date('Y-m-d H:i:s', $time) . "', '%Y-%m-%d %H:%i:%s') " .
						"ORDER BY `revision_timestamp` DESC" .
					") AS `b` 
				WHERE `{$id_field}` NOT IN(" .
					"SELECT `{$id_field}` " .
					"FROM `{$table}_revision` " .
					"WHERE `revision_timestamp` <= STR_TO_DATE('" . date('Y-m-d H:i:s', $time) . "', '%Y-%m-%d %H:%i:%s') AND `revision_action` LIKE 'DELETE'" .
				") GROUP BY `{$id_field}` " .
			"ON DUPLICATE KEY UPDATE " . implode(', ', $cols) . ";"
		);
	}
	
	/** Prints error message
	 * @param 	string		$msg	- Message
	 * @param 	boolean 	$web 	- HTML (TRUE) or Plaint text
	 */
	private function error($msg, $web = FALSE) {
		if ($this->displayError || $this->logErrors || $this->emailErrors) {
			if ($this->link) {
				$this->error = $this->call('error');
				$this->errorNo = $this->call('errno');
			}
			$nl 	= empty($_SERVER['REMOTE_ADDR']) ? PHP_EOL : "<br>" . PHP_EOL;
			$web 	= empty($_SERVER['REMOTE_ADDR']) ? FALSE : $web;
			$error 	= ($web ? "{$nl} - Error No: <a href=\"http://search.oracle.com/search/search?q={$this->errorNo}&amp;group=MySQL\">{$this->errorNo}</a>{$nl} - Error: {$this->error}" : "{$nl} - Error No: {$this->errorNo}{$nl} - Error: {$this->error}{$nl} - Call: {$this->backtrace()}") . PHP_EOL;
			if ($this->logErrors) 
				$this->log('ERROR', "NO -> {$this->errorNo} - DESC -> {$this->error} - CALL -> {$this->backtrace()}");
			if ($this->displayError) 
				echo $msg, $this->link ? $error : NULL;
			if ($this->emailErrors) {
				$headers = array();
				$headers[] = "MIME-Version: 1.0";
				$headers[] = "Content-type: text/plain; charset=UTF-8";
				$headers[] = "From: MySQL ERROR REPORTING <no-reply@{$_SERVER['SERVER_ADDR']}>";
				$headers[] = "Reply-To: Recipient Name <no-reply@{$_SERVER['SERVER_ADDR']}>";
				$headers[] = "Subject: {$this->emailErrorsSubject}";
				$headers[] = "X-Mailer: PHP/" . phpversion();
				$m = array();
				$m['ENV']      = $_SERVER['SERVER_NAME'];
				$m['TIME']     = date($this->dateFormat);
				$m['SCRIPT']   = $_SERVER['PHP_SELF'];
				$m['CALL']     = $this->backtrace();
				$m['ERROR NO'] = $this->errorNo;
				$m['ERROR']    = $this->error;
				$m['MESSAGE']  = $msg;
				$message = array();
				foreach ($m as $k => $v) {
					$message[] = sprintf("%-10s%s", $k, $v);
				}
				mail(implode(', ', $this->emailErrorsTo), sprintf($this->emailErrorsSubject, $_SERVER['SERVER_NAME']), implode("\r\n", $message), implode("\r\n", $headers));
			}
		}
		!$this->dieOnError || die();
	}
	
	/** Logs queries or / and errors to file
	 * @param 	string		$type	- Log type
	 * @param 	string	 	$log 	- Message
	 */
	private function log($type, $log) {
		try {
			$fh = fopen($this->logFilePath, 'a');
			fwrite($fh, date($this->dateFormat) . " - {$type} -> {$log}" . PHP_EOL);
			fclose($fh);
		} catch(Exception $e) {
			$this->error($e->getMessage());
		}
	}
	
	/** Debug Backtrace
	 * @param	void
	 * @return	string 		- Backtrace
	 */
	private function backtrace() {
		foreach (debug_backtrace() as $t) {
			if ($t['file'] != __FILE__) {
				return "Function {$t['function']} in {$t['file']} on line {$t['line']}";
			}
		}
	}

	/** Draw table with explain 
	 * @param	string	$sql	- MySQL query
	 * @return	void
	 */
	public function explain($sql) {
		$data = $this->fetchQueryToArray('EXPLAIN ' . $sql);
		$this->drawTable($data, 'Explain MySQL Query');
	}

	/** Draw table with describe 
	 * @param	string	$table	- Table name
	 * @return	void
	 */
	public function describe($table) {
		$data = $this->fetchQueryToArray('DESCRIBE `' . $table . '`;');
		$this->drawTable($data, $table);
	}

	/** Draw ascii table
	 * @param	array	$data	- Multidimensional array of data
	 * @param	string	$title	- Table header
	 * @return	void
	 */
	public function drawTable($data, $title = NULL) {
		// No data
		if (empty($data)) {
			return FALSE;
		}
		// Use array keys for fild names
		$h = array_keys($data[0]);
		$header = array();
		foreach ($h as $name) {
			$header[$name] = $name;
		}
		// Prepend header
		array_unshift($data, $header);
		// Find max strlen
		$p = array();
		$l = array();
		foreach ($data as $elm) {
			foreach ($elm as $key => $val) {
				// Define index
				if (!isset($l[$key], $l[$key])) {
					$l[$key] = 0;
					$p[$key] = 0;
				}
				// Find max
				$l[$key] = strlen($val);
				if ($l[$key] > $p[$key]) {
					$p[$key] = $l[$key];
				}
			}
		}
		// Return data
		$ret = array();
		// Header
		if (!empty($title)) {
			$ret[] = '+-' . str_pad(NULL, array_sum($p) + ((count($p) -1) * 3 ), '-') . '-+';
			$ret[] = '| ' . str_pad($title, array_sum($p) + ((count($p) -1) * 3 ), ' ', STR_PAD_BOTH) . ' |';
		}
		// Line
		$r = array();
		foreach ($p as $k) {
			$r[] =  str_pad(NULL, $k, '-');
		}
		$line = '+-' . implode($r, '-+-') . '-+';
		// Before line
		$ret[] = $line;
		$header = 0;
		// Table values
		foreach ($data as $row) {
			// Data row
			$r = array();
			foreach ($row as $key => $val) {
				$r[] = str_pad($val, $p[$key], ' ', is_numeric($val) ? STR_PAD_LEFT : STR_PAD_RIGHT);
			}
			$ret[] = '| ' . implode($r, ' | ') . ' |';
			// Fields header
			if ($header == 0) {
				$ret[] = $line;
				$header = 1;
			}
		}
		// Last line
		$ret[] = $line;
		// Print table
		echo '<pre>', htmlspecialchars(implode($ret, PHP_EOL), ENT_QUOTES), '</pre>';
	}
	
	/** Get Microtime
	 * @return 	float 		- Current time
	 */
	private function getMicrotime() {
		list($usec, $sec) = explode(" ", microtime());
		return ((float) $usec + (float) $sec);
	}
	
	/** Detect EOL from file
	 * @param	string		- File path
	 * @retrun	- EOL chr
	 */
	private function detectEOL($file) {
		$f = fopen($file, 'r');
		$line = fgets($f);
		fclose($f);
		foreach (array("\r\n", "\r", "\n") as $eol) {
			if (substr_compare($line, $eol, -strlen($eol)) === 0) {
				return $eol;
			}
		}
		return FALSE;
	}
}