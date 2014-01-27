<?php 
/******************************************************************
 * 
 * Projectname:   PHP MySQL Wrapper Class 
 * Version:       1.5
 * Author:        Radovan Janjic <rade@it-radionica.com>
 * Last modified: 14 01 2014
 * Copyright (C): 2012 IT-radionica.com, All Rights Reserved
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
	var $version = '1.5';
	
	/** MySQL Host name  
	 * @var string
	 */
	var $server = NULL;
	
	/** MySQL User
	 * @var string
	 */
	var $username = NULL;
	
	/** MySQL Password
	 * @var string
	 */
	var $password = NULL;
	
	/** MySQL Database
	 * @var string
	 */
	var $database = NULL;
	
	/** Connection Character Set (Default: UTF-8)
	 * @var string
	 */
	var $character = 'utf8';
	
	/** Error Description 
	 * @var string
	 * */
	var $error = NULL;
	
	/** Error Number 
	 * @var integer
	 */
	var $errorNo = 0;
	
	/** Display Errors (Default: TRUE)
	 * @var boolean
	 */
	var $displayError = TRUE;
	
	/** Link
	 * @var resurse
	 */
	var $link = 0;
	
	/** Query
	 * @var resurse
	 */
	var $query = 0;
	
	/** Affected Rows 
	 * @var integer
	 */
	var $affected = 0;
	
	/** Log Queries to file (Default: FALSE)
	 * @var boolean
	 */
	var $logQueries = FALSE;
	
	/** Log Errors to file (Default: FALSE)
	 * @var boolean
	 */
	var $logErrors = FALSE;
	
	/** Stop script execution on error (Default: FALSE)
	 * @var boolean
	 */
	var $dieOnError = FALSE;
	
	/** E-mail errors (Default: FALSE)
	 * @var boolean
	 */
	var $emailErrors = FALSE;
	
	/** E-mail errors to (array with emails)
	 * @var array
	 */
	var $emailErrorsTo = array();
	
	/** E-mail errors subject
	 * @var string
	 */
	var $emailErrorsSubject = 'MySQL ERROR ON SERVER: %s';
	
	/** Log Date Format (Default: Y-m-d H:i:s)
	 * @var string
	 */
	var $dateFormat = 'Y-m-d H:i:s';
	
	/** Log File Path (Default: log-mysql.txt)
	 * @var string
	 */
	var $logFilePath = 'log-mysql.txt';
	
	/** Reserved words for array to ( insert / update )
	 * @var array
	 */
	var $reserved = array('null', 'now()', 'current_timestamp', 'curtime()', 'localtime()', 'localtime', 'utc_date()', 'utc_time()', 'utc_timestamp()');
	
	/** Start of MySQL statement for array to ( insert / update )
	 * @var string
	 */
	var $statementStart = 'sql::';
	
	/** REGEX
	 * @var array
	 */
	var $REGEX = array('LIMIT' => '/limit[\s]+([\d]+[\s]*,[\s]*[\d]+[\s]*|[\d]+[\s]*)$/i', 'COLUMN' => '/^[a-z0-9_\-\s]+$/i');
	 
	/** Constructor
	 * @param 	string 		$server		- MySQL Host name 
	 * @param 	string 		$username 	- MySQL User
	 * @param 	string 		$password 	- MySQL Password
	 * @param 	string 		$database 	- MySQL Database
	 */
	function MySQL_wrapper($server = NULL, $username = NULL, $password = NULL, $database = NULL) {
		$this->server = $server;
		$this->username = $username;
		$this->password = $password;
		$this->database = $database;
	}
	
	/** Connect 
	 * @param 	string 		$server		- MySQL Host name 
	 * @param 	string 		$username 	- MySQL User
	 * @param 	string 		$password 	- MySQL Password
	 * @param 	string 		$database 	- MySQL Database
	 * @param 	boolean		$newLink	- New link
	 * @return 	boolean 
	 */
	function connect($server = NULL, $username = NULL, $password = NULL, $database = NULL, $newLink = FALSE) {
		if ($server !== NULL && $username !== NULL && $database !== NULL) {
			$this->server = $server;
			$this->username = $username;
			$this->password = $password;
			$this->database = $database;
		}
		$this->link = @mysql_connect($this->server, $this->username, $this->password, $newLink) or $this->error("Couldn't connect to server: {$this->server}.");
		if ($this->link) {
			$this->setCharacter($this->character);	
			@mysql_select_db($this->database, $this->link) or $this->error("Could not open database: {$this->database}.");
			return TRUE;
		} else {
			return FALSE;	
		}
	}
	
	/** Sets the default character set for the current connection.
	 * @param 	string 		$character 	- A valid character set name ( If not defined $this->character whill be used)
	 * @return	boolean
	 */
	function setCharacter($character) {
		$this->character = $character ? $character : $this->character;
		if ($this->link && $this->character)
			if (function_exists('mysql_set_charset')) {
				return mysql_set_charset($this->character);
			} else {
				$this->query("SET NAMES '{$this->character}';");
				$this->query("SET CHARACTER SET '{$this->character}';");
				$this->query("SET character_set_results = '{$this->character}', character_set_client = '{$this->character}', character_set_connection = '{$this->character}', character_set_database = '{$this->character}', character_set_server = '{$this->character}';");
				return TRUE;
			}
		else return FALSE;
	}
	
	/** Checks whether or not the connection to the server is working.
	 * @param 	void
	 * @return 	boolean 
	 */
	function ping() {
		return mysql_ping($this->link);
	}
	
	/** Reconnect to the server.
	 * @param 	void
	 * @return 	boolean 
	 */
	function reconnect() {
		$this->close();
		return $this->connect();
	}
	
	/** Close Connection on the server that's associated with the specified link (identifier).
	 * @param 	void
	 */
	function close() {
		@mysql_close($this->link) or $this->error("Connection close failed.");
	}
	
	/** Execute a unique query (multiple queries are not supported) to the currently active database on the server that's associated with the specified link (identifier).
	 * @param 	string 		$sql 		- MySQL Query
	 * @param 	mixed 		- array of params to be escaped or one param
	 * @param 	mixed 		- param
	 * @param 	mixed 		- ...
	 * @return 	resource or false
	 */
	function query($sql) {
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
					$p['replace'][] = "'{$this->escape($v)}'";
				}
			}
			$sql = str_replace($p['search'], $p['replace'], $sql);
			unset($l, $p);
		}
		if($this->logQueries) $start = $this->getMicrotime();
		$this->query = @mysql_query($sql, $this->link) or $this->error("Query fail: {$sql}");
		$this->affected = @mysql_affected_rows($this->link);
		if ($this->query && $this->logQueries) $this->log('QUERY', "EXEC -> " . number_format($this->getMicrotime() - $start, 8) . " -> " . $sql);
		return $this->query ? $this->query : FALSE;
	}
	
	/** Get number of fields in result
	 * @param 	resource 	$query 		- MySQL Query Result
	 * @return 	integer 	- Retrieves the number of fields from a query
	 */
	function numFields($query = 0) {
		return intval(@mysql_num_fields($query ? $query : $this->query));
	}
	
	/** Get number of rows in result
	 * @param 	resource 	$query 		- MySQL Query Result
	 * @return 	integer 	- Retrieves the number of rows from a result set
	 */
	function numRows($query = 0) {
		return intval(mysql_num_rows($query ? $query : $this->query));
	}
	
	/** Get number of rows in result
	 * @param 	resource 	$query 		- Result resource that is being evaluated ( Query Result )
	 * @return 	bool
	 */
	function freeResult($query = 0) {
		$this->query = $query ? $query : $this->query;
		@mysql_free_result($this->query) or $this->error("Result ID: {$this->query} could not be freed.");
	}
	
	/** Get Columns names into array
	 * @param 	string 		$table 		- Table name
	 * @return 	array 		$columns 	- Names of Fields
	 */
	function getColumns($table) {
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
	function fetchArray($query = 0) {
		$this->query = $query ? $query : $this->query;
		if ($this->query) {
			return @mysql_fetch_assoc($this->query);
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
	function fetchQueryToArray($sql, $fetchFirst = FALSE) {
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
			while ($row = $this->fetchArray($q)) $array[] = $row;
		}
		$this->freeResult($q);
		return $array;
	}
	
	/** Escapes special characters in a string for use in an SQL statement.
	 * @param 	string 		$string - unescaped string
	 * @return 	string
	 */
	function escape($string) {
		return (version_compare(PHP_VERSION, '5.4.0') >= 0) ? @mysql_real_escape_string($string, $this->link) : @mysql_real_escape_string(get_magic_quotes_gpc() ? stripslashes($string) : $string, $this->link);
	}
	
	/** Creates an sql string from an associate array
	 * @param 	string 		$table 	- Table name
	 * @param 	array 		$data 	- Data array Eg. $data['column'] = 'val';
	 * @param 	string 		$where 	- MySQL WHERE Clause
	 * @param 	integer 	$limit 	- Limit offset
	 * @return 	number of updated rows or false
	 */
	function arrayToUpdate($table, $data, $where = NULL, $limit = 0) {
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
	function arrayToInsert($table, $data, $ignore = FALSE, $duplicateupdate = NULL) {
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
		return (!empty($data)) ? $this->query("INSERT" . ($ignore ? " IGNORE" : NULL) . " INTO `{$table}` ( `{$c}` ) VALUES {$v}" . ($duplicateupdate ? " ON DUPLICATE KEY UPDATE {$duplicateupdate}" : NULL) . ";") ? ($multirow ? TRUE : $this->insertId()) : FALSE : FALSE;
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
	function importCSV2Table($file, $table, $delimiter = ',', $enclosure = '"', $escape = '\\', $ignore = 1, $update = array(), $getColumnsFrom = 'file', $newLine = FALSE) {
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
				foreach ($columns as $c) preg_match($this->REGEX['COLUMN'], $c) or $this->error("ERROR", "Invalid Column Name: {$c} in CSV file: {$file}. Data can not be loaded into table: {$table}.");
			}
			
			foreach ($columns as &$c) $c = (in_array($c, array_keys($update))) ? '@' . $c : "`{$c}`";
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
	function importUpdateCSV2Table($file, $table, $delimiter = ',', $enclosure = '"', $escape = '\\', $ignore = 1, $update = array(), $getColumnsFrom = 'file', $newLine = FALSE) {		
		$tmp_name = "{$table}_tmp_" . rand();
		
		// Create tmp table
		$this->query("CREATE TEMPORARY TABLE `{$tmp_name}` LIKE `{$table}`;");
		
		// Remove auto_increment if exists
		$change = array();
		$this->query("SHOW COLUMNS FROM `{$tmp_name}` WHERE `Key` NOT LIKE '';");
		if($this->affected > 0){
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
	function exportTable2CSV($table, $file, $columns = '*', $where = NULL, $limit = 0, $delimiter = ',', $enclosure = '"', $escape = '\\', $newLine = '\n', $showColumns = TRUE) {
		$fh = fopen($file, 'w') or $this->error("ERROR", "Can't create CSV file: {$file}");
		if (!$fh) {
			return FALSE;
		}
		fclose($fh);
		$file = realpath($file);
		unlink($file);
		
		// Put columns into array if not *
		if($columns != '*' && !is_array($columns)){
			$stringColumns = $columns;
			$columns = array();
			foreach (explode(',', $stringColumns) as $c) {
				$columns[] = trim(str_replace(array("'", "`", "\""), NULL, $c));
			}
		}
		
		// Prepare SQL for column names
		if ($showColumns) {
			$tableColumnsArr = array();
			if ($columns == '*'){
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
	function query2CSV($sql, $file, $delimiter = ',', $enclosure = '"', $escape = '\\', $newLine = '\n', $showColumns = TRUE) {
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
	function createTableFromCSV($file, $table, $delimiter = ',', $enclosure = '"', $escape = '\\', $ignore = 1, $update = array(), $getColumnsFrom = 'file', $newLine = FALSE) {
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
			
			$this->query("CREATE TABLE `{$table}` ( " . implode(', ', $columns) . " ) ENGINE=InnoDB DEFAULT CHARSET={$this->character};");
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
	function renameTable($table) {
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
	function copyTable($table, $new_table, $data = TRUE) {
		$r = $this->query("CREATE TABLE `{$new_table}` LIKE `{$table}`;");
		return ($r && $data) ? $this->query("INSERT INTO `{$new_table}` SELECT * FROM `{$table}`;") : $r;
	}

	/** Truncate table
	 * @param 	string 		$table 		- Table name
	 * @return 	resource or false
	 */
	function truncateTable($table) {
		return $this->query("TRUNCATE TABLE `" . $table . "`;"); 
	}
	
	/** Drop table(s)
	 * @param 	array 		$table 		- Names of the tables eg -> array('table1', 'table2')
	 * @param 	boolean		$if_exists	- Use IF EXISTS to prevent an error from occurring for tables that do not exist.
	 * @return 	resource or false
	 */
	function dropTable($table, $if_exists = TRUE) {
		return $this->query("DROP TABLE " . ($if_exists ? "IF EXISTS " : NULL) . "`" . (is_array($table) ? implode('`, `', $table) : $table) . "`;"); 
	}
	
	/** Data Base size in B / KB / MB / GB / TB
	 * @param 	string	 	$sizeIn		- Size in B / KB / MB / GB / TB
	 * @param 	string	 	$sizeIn		- Size in B / KB / MB / GB / TB
	 * @param 	integer	 	$round		- Round on decimals
	 * @return 	- Size in B / KB / MB / GB / TB
	 */
	function getDataBaseSize($sizeIn = 'MB', $round = 2) {
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
	function insertId() {
		return $this->link ? mysql_insert_id($this->link) : FALSE;
	}
	
	/** Retrieves the number of rows from table based on certain conditions.
	 * @param 	string 		$table 	- Table name
	 * @param 	string 		$where 	- WHERE Clause
	 * @return 	integer or false
	 */
	function countRows($table, $where = NULL) {
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
	function nextAutoIncrement($table) {
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
	function deleteRow($table, $where = NULL, $limit = 0) {
		return $this->query("DELETE FROM `{$table}`" . ($where ? " WHERE {$where}" : NULL) . ($limit ? " LIMIT {$limit}" : NULL) . ";") ? $this->affected : FALSE;
	}
	
	/** Begin Transaction
	 * @param 	void
	 */
	function begin() { 
		$this->query("START TRANSACTION"); 
		return $this->query("BEGIN"); 
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
	function strReplace($table, $columns, $search, $replace, $where = NULL, $limit = 0) {
		// Replace in whole DB
		if ($table == '*') {
			if (!is_array($columns)){
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
		if (!is_array($columns)){
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
	
	/** Commit
	 * @param 	void
	 */
	function commit() { 
		return $this->query("COMMIT"); 
	} 
	
	/** Rollback
	 * @param 	void
	 */
	function rollback() { 
		return $this->query("ROLLBACK"); 
	} 
	
	/** Transaction
	 * @param 	array		$qarr	- Array with Queries
	 * @link	http://dev.mysql.com/doc/refman/5.0/en/commit.html
	 */
	function transaction($qarr = array()) { 
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
	
	/** Prints error message
	 * @param 	string		$msg	- Message
	 * @param 	boolean 	$web 	- HTML (TRUE) or Plaint text
	 */
	function error($msg, $web = FALSE) {
		if ($this->displayError || $this->logErrors || $this->emailErrors) {
			if ($this->link) {
				$this->error = @mysql_error($this->link);
				$this->errorNo = @mysql_errno($this->link);
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
	function log($type, $log) {
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
	function backtrace() {
		foreach (debug_backtrace() as $t) {
			if ($t['file'] != __FILE__) {
				return "Function {$t['function']} in {$t['file']} on line {$t['line']}";
			}
		}
	}
	
	/** Get Microtime
	 * @return 	float 		- Current time
	 */
	function getMicrotime() {
		list($usec, $sec) = explode(" ", microtime());
		return ((float) $usec + (float) $sec);
	}
	
	/** Detect EOL from file
	 * @param	string		- File path
	 * @retrun	- EOL chr
	 */
	function detectEOL($file) {
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
