<?php 
/******************************************************************
 * 
 * Projectname:   PHP MySQL Wrapper Class 
 * Version:       1.4
 * Author:        Radovan Janjic <rade@it-radionica.com>
 * Last modified: 20 05 2013
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
	var $version = '1.4';
	
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
	var $displayError = FALSE;
	
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
	
	/** Log Date Format (Default: Y-m-d H:i:s)
	 * @var string
	 */
	var $dateFormat	= 'Y-m-d H:i:s';
	
	/** Log File Path (Default: log-mysql.txt)
	 * @var string
	 */
	var $logFilePath = 'log-mysql.txt';
	
	/** Reserved words for array to ( insert / update )
	 * @var array
	 */
	var $reserved = array('null', 'now()', 'curtime()', 'localtime()', 'localtime', 'utc_date()', 'utc_time()', 'utc_timestamp()');
	
	/** Constructor
	 * @param 	string 		$server		- MySQL Host name 
	 * @param 	string 		$username 	- MySQL User
	 * @param 	string 		$password 	- MySQL Password
	 * @param 	string 		$database 	- MySQL Database
	 */
	function MySQL_wrapper($server = NULL, $username = NULL, $password = NULL, $database = NULL){
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
	 * @param 	resource 	$link 		- link identifier
	 * @return	boolean
	 */
	function setCharacter($character, $link = 0) {
		$this->link = $link ? $link : $this->link;
		$this->character = $character ? $character : $this->character;
		if ($this->link && $this->character)
			if (function_exists('mysql_set_charset')) {
				return mysql_set_charset($this->character, $this->link);
			} else {
				$this->query("SET NAMES '{$this->character}';", $this->link);
				$this->query("SET CHARACTER SET '{$this->character}';", $this->link);
				$this->query("SET character_set_results = '{$this->character}', character_set_client = '{$this->character}', character_set_connection = '{$this->character}', character_set_database = '{$this->character}', character_set_server = '{$this->character}';", $this->link);
				return TRUE;
			}
		else return FALSE;
	}
	
	/** Close Connection on the server that's associated with the specified link (identifier).
	 * @param 	resurse 	$link 		- link identifier
	 */
	function close($link = 0) {
		@mysql_close($link ? $link : $this->link) or $this->error("Connection close failed.");
	}
	
	/** Execute a unique query (multiple queries are not supported) to the currently active database on the server that's associated with the specified link (identifier).
	 * @param 	string 		$sql 		- MySQL Query
	 * @param 	resource 	$link 		- Link identifier
	 * @return 	resource or false
	 */
	function query($sql, $link = 0) {
		$this->link = $link ? $link : $this->link;
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
	 * @param 	resource 	$link 		- Link identifier
	 * @return 	array 		$columns 	- Names of Fields
	 */
	function getColumns($table, $link = 0) {
		$this->link = $link ? $link : $this->link;
		$q = $this->query("SHOW COLUMNS FROM `{$table}`;", $this->link);
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
	 * @param 	resource 	$link 		- Link identifier
	 * @return 	array
	 */
	function fetchQueryToArray($sql, $link = 0) {
		$this->link = $link ? $link : $this->link;
		$q = $this->query($sql, $this->link);
		$array = array();
		while ($row = $this->fetchArray($q)) $array[] = $row;
		$this->freeResult($q);
		return $array;
	}
	
	/** Escapes special characters in a string for use in an SQL statement.
	 * @param 	string 		$string - unescaped string
	 * @param 	resource 	$link 	- link identifier
	 * @return 	string
	 */
	function escape($string, $link = 0) {
		$this->link = $link ? $link : $this->link;
		return (version_compare(PHP_VERSION, '5.4.0') >= 0) ? @mysql_real_escape_string($string, $this->link) : @mysql_real_escape_string(get_magic_quotes_gpc() ? stripslashes($string) : $string, $this->link);
	}
	
	/** Creates an sql string from an associate array
	 * @param 	string 		$table 	- Table name
	 * @param 	array 		$data 	- Data array Eg. $data['column'] = 'val';
	 * @param 	string 		$where 	- MySQL WHERE Clause
	 * @param 	integer 	$limit 	- Limit offset
	 * @param 	resource 	$link 	- link identifier
	 * @return 	number of updated rows or false
	 */
	function arrayToUpdate($table, $data, $where = NULL, $limit = 0, $link = 0) {
		$this->link = $link ? $link : $this->link;
		$fields = array();
		foreach ($data as $key => $val) $fields[] = (in_array(strtolower($val), $this->reserved)) ? "`$key` = " . strtoupper($val) : "`$key` = '" . $this->escape($val) . "'";
		return (!empty($fields)) ? $this->query("UPDATE `{$table}` SET " . implode(', ', $fields) . ($where ? " WHERE {$where}" : NULL) . ($limit ? " LIMIT {$limit}" : NULL) . ";", $this->link) ? $this->affected : FALSE : FALSE;
	}
	
	/** Creates an sql string from an associate array
	 * @param 	string 		$table 	- Table name
	 * @param 	array 		$data 	- Data array Eg. $data['column'] = 'val';
	 * @param 	boolean		$ingore	- INSERT IGNORE (row won't actually be inserted if it results in a duplicate key)
	 * @param 	string 		$duplicateupdate 	- ON DUPLICATE KEY UPDATE (The ON DUPLICATE KEY UPDATE clause can contain multiple column assignments, separated by commas.)
	 * @param 	resource 	$link 	- link identifier
	 * @return 	insert id or false
	 */
	function arrayToInsert($table, $data, $ignore = FALSE, $duplicateupdate = NULL, $link = 0) {
		$this->link = $link ? $link : $this->link;
		foreach ($data as &$val) $val = (in_array(strtolower($val), $this->reserved)) ? strtoupper($val) : "'" . $this->escape($val) . "'";
		return (!empty($data)) ? $this->query("INSERT" . ($ignore ? " IGNORE" : NULL) . " INTO `{$table}` ( `" . implode('`, `', array_keys($data)) . "` ) VALUES ( " . implode(', ', array_values($data)) . " )" . ($duplicateupdate ? " ON DUPLICATE KEY UPDATE {$duplicateupdate}" : NULL) . ";") ? $this->insertId($this->link) : FALSE : FALSE;
	}
	
	/** Imports CSV data to Table with possibility to update rows while import.
	 * @param 	string		$file			- CSV File path
	 * @param 	string 		$table 			- Table name
	 * @param	string		$delimiter		- COLUMNS TERMINATED BY (Default: ',')
	 * @param	string 		$enclosure		- OPTIONALLY ENCLOSED BY (Default: '"')
	 * @param 	string		$escape 		- ESCAPED BY (Defaul: '\')
	 * @param 	integer 	$ignore 		- Number of ignored rows (Default: 1)
	 * @param 	array		$update 		- If row fields needed to be updated eg date format or increment (SQL format only @FIELD is variable with content of that field in CSV row) $update = array('SOME_DATE' => 'STR_TO_DATE(@SOME_DATE, "%d/%m/%Y")', 'SOME_INCREMENT' => '@SOME_INCREMENT + 1')
	 * @param 	string 		$getColumnsFrom	- Get Columns Names from (file or table) - this is important if there is update while inserting (Default: file)
	 * @param 	string 		$newLine		- New line detelimiter (Default: \n)
	 * @param 	resource 	$link 			- link identifier
	 * @return 	number of inserted rows or false
	 */
	function importCSV2Table($file, $table, $delimiter = ',', $enclosure = '"', $escape = '\\', $ignore = 1, $update = array(), $getColumnsFrom = 'file', $newLine = '\n', $link = 0) {
		$this->link = $link ? $link : $this->link;
		$file = file_exists($file) ? realpath($file) : NULL;
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
				foreach ($columns as $c) preg_match('/^[A-Za-z][A-Za-z0-9_]*$/i', $c) or ($this->logErrors) ? $this->log("ERROR", "Invalid Column Name: {$c} in CSV file: {$file}. Data can not be loaded into table: {$table}.") : FALSE;
			}
			
			foreach ($columns as &$c) $c = (in_array($c, array_keys($update))) ? '@' . $c : "`{$c}`";
			$sql .= " (" . implode(', ', $columns) .  ") ";
			
			$fields = array();
			foreach ($update as $key => $val) $fields[] = "`{$key}` = {$val}";
			$sql .= "SET " . implode(', ', $fields);
		}
		$sql .= ";";
		return ($this->query($sql, $this->link)) ? $this->affected : FALSE;
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
	 * @param 	string 		$newLine		- New line detelimiter (Default: \n)
	 * @param 	boolean		$showColumns 	- Columns names in first line
	 * @param 	resource 	$link 			- link identifier
	 * @return 	number of inserted rows or false
	 */
	function exportTable2CSV($table, $file, $columns = '*', $where = NULL, $limit = 0, $delimiter = ',', $enclosure = '"', $escape = '\\', $newLine = '\n', $showColumns = TRUE, $link = 0){
		$this->link = $link ? $link : $this->link;
		$fh = fopen($file, 'w') or ($this->logErrors) ? $this->log("ERROR", "Can't create CSV file.") : FALSE;
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
		
		// Prepere SQL for column names
		if ($showColumns) {
			$tableColumnsArr = array();
			if ($columns == '*'){
				foreach ($this->getColumns($table) as $c)
					$tableColumnsArr[] = "'{$c}' AS `{$c}`";
			} elseif (is_array($columns)) {
				foreach ($columns as $c)
					$tableColumnsArr[] = "'{$c}' AS `{$c}`";
			}
			$columnsSQL = "SELECT " . implode(', ', $tableColumnsArr) . " UNION ALL ";
		}
			
		$sql = (($showColumns) ? $columnsSQL : NULL) .
			   "SELECT " . (is_array($columns) ? '`' . implode('`, `', $columns) . '`' : $columns) . " " . 
			   "INTO OUTFILE '{$this->escape($file)}' " . 
			   "FIELDS TERMINATED BY '{$delimiter}' " .
			   "OPTIONALLY ENCLOSED BY '{$enclosure}' " .
			   "ESCAPED BY '{$this->escape($escape)}' " .
			   "LINES TERMINATED BY '{$newLine}' " .
			   "FROM `{$table}`" . ($where ? " WHERE {$where}" : NULL) . ($limit ? " LIMIT {$limit}" : NULL) . ";";
		return ($this->query($sql, $this->link)) ? TRUE : FALSE;
	}
	
	/** Retrieves the ID generated for an AUTO_INCREMENT column by the previous query.
	 * @param 	resource 	$link 	- link identifier
	 * @return 	integer
	 */
	function insertId($link = 0) {
		$this->link = $link ? $link : $this->link;
		return $this->link ? mysql_insert_id($this->link) : FALSE;
	}
	
	/** Retrieves the number of rows from table based on certain conditions.
	 * @param 	string 		$table 	- Table name
	 * @param 	string 		$where 	- WHERE Clause
	 * @param 	resource 	$link 	- link identifier
	 * @return 	integer or false
	 */
	function countRows($table, $where = NULL, $link = 0) {
		$this->link = $link ? $link : $this->link;
		$r = $this->query("SELECT COUNT( * ) AS count FROM `{$table}` " . ($where ? " WHERE {$where}" : NULL) . ";", $this->link);
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
	 * @param 	resource 	$link 	- link identifier
	 * @return 	integer or false
	 */
	function nextAutoIncrement($table, $link = 0) {
		$this->link = $link ? $link : $this->link;
		$r = $this->query("SHOW TABLE STATUS LIKE '{$table}';", $this->link);  
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
	 * @param 	resource 	$link 	- link identifier
	 * @return 	number of deleted rows or false
	 */
	function deleteRow($table, $where = NULL, $limit = 0, $link = 0) {
		$this->link = $link ? $link : $this->link;
		return $this->query("DELETE FROM `{$table}`" . ($where ? " WHERE {$where}" : NULL) . ($limit ? " LIMIT {$limit}" : NULL) . ";", $this->link) ? $this->affected : FALSE;
	}
	
	/** Begin Transaction
	 * @param 	resource 	$link 	- Link identifier
	 */
	function begin($link = 0) { 
		$this->link = $link ? $link : $this->link;
		$this->query("START TRANSACTION", $this->link); 
		return $this->query("BEGIN", $this->link); 
	}
	
	/** Replace all occurrences of the search string with the replacement string in MySQL Table Column(s).
	 * @param 	string		$table 	 - Table name
	 * @param 	mixed 		$columns - Search & Replace affected Table columns. An array may be used to designate multiple replacements.
	 * @param 	mixed 		$search  - The value being searched for, otherwise known as the needle. An array may be used to designate multiple needles.
	 * @param 	mixed 		$replace - The replacement value that replaces found search values. An array may be used to designate multiple replacements.
	 * @param 	string 		$where 	 - WHERE Clause
	 * @param 	integer 	$limit 	 - Limit offset
	 * @param 	resource 	$link 	 - Link identifier
	 * @return  integer 	- Affected rows
	 */
	function strReplace($table, $columns, $search, $replace, $where = NULL, $limit = 0, $link = 0) {
		$this->link = $link ? $link : $this->link;
		// Columns
		if (!is_array($columns)){
			$stringColumns = $columns;
			$columns = array();
			if ($stringColumns == '*') {
				$columns = $this->getColumns($table, $this->link);
			} elseif (preg_match('/^[A-Za-z][A-Za-z0-9_]*$/i', $stringColumns)) {
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
					if (is_array($replace)) {
						$update[] = "`{$col}` = REPLACE(`{$col}`, '{$this->escape($s)}', '{$this->escape($replace[$k])}')";
					} else {
						$update[] = "`{$col}` = REPLACE(`{$col}`, '{$this->escape($s)}', '{$this->escape($replace)}')";
					}
				}
			} else {
				$update[] = "`{$col}` = REPLACE(`{$col}`, '{$this->escape($search)}', '{$this->escape($replace)}')";
			}
		}
		$this->query("UPDATE `{$table}` SET " . implode(', ', $update) . ($where ? " WHERE {$where}" : NULL) . ($limit ? " LIMIT {$limit}" : NULL) . ";", $this->link);
		return $this->affected;
	}
	
	/** Commit
	 * @param 	resource 	$link 	- Link identifier
	 */
	function commit($link = 0) { 
		$this->link = $link ? $link : $this->link;
		return $this->query("COMMIT", $this->link); 
	} 
	
	/** Rollback
	 * @param 	resource 	$link 	- Link identifier
	 */
	function rollback($link = 0) { 
		$this->link = $link ? $link : $this->link;
		return $this->query("ROLLBACK", $this->link); 
	} 
	
	/** Transaction
	 * @param 	array		$qarr	- Array with Queries
	 * @param 	resource 	$link 	- Link identifier
	 * @link	http://dev.mysql.com/doc/refman/5.0/en/commit.html
	 */
	function transaction($qarr = array(), $link = 0) { 
		$this->link = $link ? $link : $this->link;
		$commit = TRUE;
		$this->begin(); 
		foreach ($qarr as $q) { 
			$this->query($q, $this->link);
			if ($this->affected == 0) $commit = FALSE;
		}
		if ($commit == FALSE) {
			$this->rollback($this->link);
			return FALSE;
		} else {
			$this->commit($this->link);
			return TRUE;
		}
	}
	
	/** Prints error message
	 * @param 	string		$mas	- Messange
	 * @param 	boolean 	$web 	- HTML (TRUE) or Plaint text
	 */
	function error($msg, $web = FALSE) {
		if ($this->displayError || $this->logErrors) {
			if ($this->link) {
				$this->error = mysql_error($this->link);
				$this->errorNo = mysql_errno($this->link);
			}
			$nl 	= empty($_SERVER['REMOTE_ADDR']) ? PHP_EOL : "<br>" . PHP_EOL;
			$web 	= empty($_SERVER['REMOTE_ADDR']) ? FALSE : $web;
			$error 	= ($web ? "{$nl} - Error No: <a href=\"http://search.oracle.com/search/search?q={$this->errorNo}&amp;group=MySQL\">{$this->errorNo}</a>{$nl} - Error: {$this->error}" : "{$nl} - Error No: {$this->errorNo}{$nl} - Error: {$this->error}") . PHP_EOL;
			if ($this->logErrors) 
				$this->log('ERROR', "NO -> {$this->errorNo} - DESC -> {$this->error}");
			if ($this->displayError) 
				echo $msg, $this->link ? $error : NULL;
		}
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
	
	/** Get Microtime
	 * @return 	float 		- Current time
	 */
	function getMicrotime() {
		list($usec, $sec) = explode(" ", microtime());
		return ((float) $usec + (float) $sec);
    }
}

