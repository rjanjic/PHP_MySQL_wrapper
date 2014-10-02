<?php 
/*

	PHP MySQL Wrapper Exmaples
	
	PHP version required (PHP 5)
	
*/

require "MySQL_wrapper.class.php";


// set your connectivity settings here
define('MySQL_HOST', 'localhost');
define('MySQL_USER', 'root');
define('MySQL_PASS', '');
define('MySQL_DB', 'test');

/*
$db = MySQL_wrapper::getInstance(MySQL_HOST, MySQL_USER, MySQL_PASS, MySQL_DB);

// Connect
$db->connect(); 

// Test table sql for examples
$db->query("CREATE TABLE IF NOT EXISTS `table` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `firstname` varchar(250) NOT NULL,
  `surname` varchar(250) NOT NULL,
  `email` varchar(500) NOT NULL,
  `date` date NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 AUTO_INCREMENT=11 ;");


// Some dummy data, uncomment to insert
if (!$db->countRows('table') > 0)
$db->query("INSERT INTO `table` (`id`, `firstname`, `surname`, `email`, `date`) VALUES
(1, 'Radovan', 'Janjic', '', '2012-11-04'),
(2, 'Radovan', 'Janjic', 'rade@it-radionica.com', '2012-11-04'),
(3, 'Radovan', 'Janjic''', 'rade@it-radionica.com', '2012-11-04'),
(4, 'Radovan', 'Janjic', 'rade@it-radionica.com', '2012-11-04'),
(5, 'Radovan', 'Janjic', 'rade@it-radionica.com', '2012-11-04'),
(6, 'Radovan', 'Janjic', 'rade@it-radionica.com', '2012-11-04'),
(7, 'Radovan', 'Janjic', 'rade@it-radionica.com', '2012-11-04'),
(8, 'Radovan', 'Janjic', 'rade@it-radionica.com', '2012-11-04'),
(9, 'Radovan', 'Janjic', 'rade@it-radionica.com', '2012-11-04'),
(10, 'Radovan', 'Janjic', 'rade@it-radionica.com', '2012-11-04');");

// Close connection
$db->close();
*/

///////////////////////////////////////////////////////////////////////////////////////////

// Example 1
// Connection example
///////////////////////////////////////////////////////////////////////////////////////////
$db = MySQL_wrapper::getInstance(MySQL_HOST, MySQL_USER, MySQL_PASS, MySQL_DB);

// Connect
$db->connect(); 

//
// ... do queries
//

// Close connection
$db->close();
///////////////////////////////////////////////////////////////////////////////////////////

// Example 2
// Connection example
///////////////////////////////////////////////////////////////////////////////////////////
$db = MySQL_wrapper::getInstance();

// connect 1
$db->connect(MySQL_HOST, MySQL_USER, MySQL_PASS, MySQL_DB); // You can use connection info here as well 
//
// Connection 1 queries
//
// Close connection 1
$db->close();

// Connect 2
$db->connect(MySQL_HOST, MySQL_USER, MySQL_PASS, MySQL_DB); 
//
// Connection 2 queries
//
// Close connection 2
$db->close();

// Connect with new link
$db->connect(TRUE);
//
// Connection 3 queries
//
// Close connection 3
$db->close();

// Example 3
// Connection example multi host, db manipulation
///////////////////////////////////////////////////////////////////////////////////////////

// Host 1 instance
$db1 = MySQL_wrapper::getInstance(MySQL_HOST, MySQL_USER, MySQL_PASS, MySQL_DB);

// Host 2 instance (MySQL_HOST, MySQL_USER, MySQL_PASS, MySQL_DB) -> use another connection info
$db2 = MySQL_wrapper::getInstance(MySQL_HOST, MySQL_USER, MySQL_PASS, MySQL_DB);

// Connect host 1
$db1->connect();

// Connect host 2
$db2->connect();

//
// ... do queries of cennection 1 or connection 2
//

// Close connection host 1
$db1->close();

// Close connection host 2
$db2->close();
///////////////////////////////////////////////////////////////////////////////////////////

// Example 4
// Select example with fetch result
///////////////////////////////////////////////////////////////////////////////////////////
$db = MySQL_wrapper::getInstance(MySQL_HOST, MySQL_USER, MySQL_PASS, MySQL_DB);
$db->connect();

// MySQL query
$db->query('SELECT * FROM `table`;');

// Int affected rows
if ($db->affected) {
	echo "<hr /><strong>Example 4 ( fetch row - array)</strong><pre>";
	while ($row = $db->fetchArray()) {
		print_r($row);
	}
	echo "</pre>";
}
$db->freeResult();

// Escape string
$var = '\'';
$db->query("SELECT * FROM `table` WHERE `firstname` LIKE '{$db->escape($var)}';");

// Param to be escaped
$db->query("SELECT * FROM `table` WHERE `firstname` LIKE '@1%' OR `surname` LIKE '%@1%';", 'rado');

// Params as args
$db->query("SELECT * FROM `table` WHERE `firstname` LIKE '@1%' AND `surname` LIKE '%@2%' OR id = @3;", 'rado', 'janjic', 3 /* , ... */);

// Array of params
$params = array();
$params['id'] = 1;
$params['name'] = 'rado';
$params['lname'] = 'janjic';
$params['limit'] = 5;
$db->query("SELECT * FROM `table` WHERE `firstname` LIKE '@name%' AND `surname` LIKE '%@lname%' OR `id` = @id LIMIT @limit;", $params);

// Int affected rows
if ($db->affected) {
	echo "<hr /><strong>Example 4 ( fetch row - array)</strong><pre>";
	while ($row = $db->fetchArray()) {
		print_r($row);
	}
	echo "</pre>";
}

$db->freeResult();
$db->close();
///////////////////////////////////////////////////////////////////////////////////////////

// Example 5
// Prepared statements (works only with MySQLi!) 
///////////////////////////////////////////////////////////////////////////////////////////
$db = MySQL_wrapper::getInstance(MySQL_HOST, MySQL_USER, MySQL_PASS, MySQL_DB);

// Connect
$db->connect();

$name = 'Radovan';

$stmt = $db->call('prepare', 'SELECT * FROM `table` WHERE `firstname` = ?;');
$stmt->bind_param('s', $name);

$stmt->execute();

$result = $stmt->get_result();
while ($row = $result->fetch_assoc()) {
    // do something
	// print_r($row);
	// ...
}

// Close connection
$db->close();
///////////////////////////////////////////////////////////////////////////////////////////

// Example 5
// Faster select exmaple (fetch query to array)
///////////////////////////////////////////////////////////////////////////////////////////
$db = MySQL_wrapper::getInstance(MySQL_HOST, MySQL_USER, MySQL_PASS, MySQL_DB);
$db->connect();
echo "<hr /><strong>Example 5 (fetch query to array)</strong><pre>";
print_r($db->fetchQueryToArray('SELECT * FROM `table`'));

// Returns only first row
print_r($db->fetchQueryToArray('SELECT * FROM `table`', TRUE));
echo "</pre>";
$db->close();
///////////////////////////////////////////////////////////////////////////////////////////

// Exmaple 6
// Multi results
///////////////////////////////////////////////////////////////////////////////////////////
$db = MySQL_wrapper::getInstance(MySQL_HOST, MySQL_USER, MySQL_PASS, MySQL_DB);
$db->connect();

// Result 1
$r1 = $db->query('SELECT * FROM `table`');
// Result 2
$r2 = $db->query('SELECT * FROM `table` LIMIT 2');

// Result 1 data
echo "<hr /><strong>Example 6 (multi results)</strong><br> Result 1:<pre>";
if ($db->numRows($r1)) {
	while ($row = $db->fetchArray($r1)) {
		print_r($row);
	}
}
echo "</pre>\nResult 2:\n<pre>";
// Result 2 data
if ($db->numRows($r2)) {
	while ($row = $db->fetchArray($r2)) {
		print_r($row);
	}
}
echo "</pre>";

// Free relust 1
$db->freeResult($r1);
// Free relust 2
$db->freeResult($r2);

$db->close();
///////////////////////////////////////////////////////////////////////////////////////////

// Example 7
// Rows, Cols num
///////////////////////////////////////////////////////////////////////////////////////////
$db = MySQL_wrapper::getInstance(MySQL_HOST, MySQL_USER, MySQL_PASS, MySQL_DB);
$db->connect();

$db->query('SELECT * FROM `table`;');

$cols = $db->numFields();
$rows = $db->numRows();

echo "<hr /><strong>Example 7 (num rows, cols)</strong><br />Cols: {$cols}, Rows: {$rows}<br />";

$db->freeResult();

$db->close();
///////////////////////////////////////////////////////////////////////////////////////////

// Example 8
// Count rows
///////////////////////////////////////////////////////////////////////////////////////////
$db = MySQL_wrapper::getInstance(MySQL_HOST, MySQL_USER, MySQL_PASS, MySQL_DB);
$db->connect();

// Count all
$count = $db->countRows('table');

// Count with condition
$count2 = $db->countRows('table', "`date` = '".date("Y-m-d")."'");

echo "<hr /><strong>Example 8 (count rows)</strong><br />Count all: {$count}, Count today: {$count2}<br />";
// More info
/** Retrieves the number of rows from table based on certain conditions.
 * @param 	string 		$table 	- Table name
 * @param 	string 		$where 	- WHERE Clause
 * @return 	integer or false
 */
// $db->countRows($table, $where = NULL)
$db->close();
///////////////////////////////////////////////////////////////////////////////////////////

// Example 9
// Array to insert
///////////////////////////////////////////////////////////////////////////////////////////
$db = MySQL_wrapper::getInstance(MySQL_HOST, MySQL_USER, MySQL_PASS, MySQL_DB);
$db->connect();

// Array data
// [fealdname] = feald value
$data = array();
$data['firstname'] = 'Radovan';
$data['surname'] = 'Janjic';
$data['email'] = 'rade@it-radionica.com';
// reserved values 'null', 'now()', 'curtime()', 'localtime()', 'localtime', 'utc_date()', 'utc_time()', 'utc_timestamp()'
$data['date'] = 'now()';

// $db->arrayToInsert( ... ) returns insert id
$insert_id = $db->arrayToInsert('table', $data);
echo "<hr /><strong>Example 9 (array to insert)</strong><br />Last insert id is: {$insert_id}<br />";

// Array data
// [fealdname] = feald value
$data = array();
$data['firstname'] = 'Radovan';
$data['surname'] = 'Janjic';
$data['email'] = 'rade@it-radionica.com';
$data['date'] = 'now()';

// [fealdname] = feald value
$data2 = array();
$data2['firstname'] = 'Radovan';
$data2['surname'] = 'Janjic';
$data2['email'] = 'rade@it-radionica.com';
$data2['date'] = 'now()';

// $db->arrayToInsert( ... ) multirow returns TRUE on success
$db->arrayToInsert('table', array($data, $data2 /*, $data3 .... */ ));


// More options
/** Creates an sql string from an associate array
 * @param 	string 		$table 	- Table name
 * @param 	array 		$data 	- Data array Eg. $data['column'] = 'val';
 * @param 	boolean		$ingore	- INSERT IGNORE (row won't actually be inserted if it results in a duplicate key)
 * @param 	string 		$duplicateupdate 	- ON DUPLICATE KEY UPDATE (The ON DUPLICATE KEY UPDATE clause can contain multiple column assignments, separated by commas.)
 * @return 	insert id or false
 */
// $db->arrayToInsert($table, $data, $ignore = FALSE, $duplicateupdate = NULL)
$db->close();
///////////////////////////////////////////////////////////////////////////////////////////

// Example 10
// Next AutoIncrement
///////////////////////////////////////////////////////////////////////////////////////////
$db = MySQL_wrapper::getInstance(MySQL_HOST, MySQL_USER, MySQL_PASS, MySQL_DB);
$db->connect();

// Returns next auto increment value
$auto_increment = $db->nextAutoIncrement('table');

echo "<hr /><strong>Example 10 (next auto increment)</strong><br>Next auto increment id is: {$auto_increment}<br />";

$db->close();
///////////////////////////////////////////////////////////////////////////////////////////

// Example 11
// Array to update
///////////////////////////////////////////////////////////////////////////////////////////
$db = MySQL_wrapper::getInstance(MySQL_HOST, MySQL_USER, MySQL_PASS, MySQL_DB);
$db->connect();

// Array data
// [fealdname] = feald value
$data = array();
$data['firstname'] = 'Radovan';
$data['surname'] = 'Janjic';
// Reserved values: null, now(), curtime(), localtime(), localtime, utc_date(), utc_time(), utc_timestamp()
$data['email'] = 'null';
$data['date'] = 'now()';

$db->arrayToUpdate('table', $data, "`id` = {$insert_id}");
if ($db->affected) {
	echo "<hr /><strong>Example 11 (array to update)</strong><br />Updated: {$db->affected} row(s).<br />";
}

// Array data
// [fealdname] = feald value
$data = array();
$data['id'] = 1; // key
$data['firstname'] = 'foo';
$data['surname'] = 'bar';
$data['email'] = 'rade@it-radionica.com';
$data['date'] = 'now()';

// [fealdname] = feald value
$data2 = array();
$data2['id'] = 2; // key 
$data2['firstname'] = 'Radovana';
$data2['surname'] = 'Janjic';
$data2['email'] = 'rade@it-radionica.com';
$data2['date'] = 'now()';

// $db->arrayToUpdate( ... ) multirow returns TRUE on success
$db->arrayToUpdate('table', array($data, $data2 /*, $data3 .... */ ));

// More options
/** Creates an sql string from an associate array
 * @param 	string 		$table 	- Table name
 * @param 	array 		$data 	- Data array Eg. $data['column'] = 'val';
 * @param 	string 		$where 	- MySQL WHERE Clause
 * @param 	integer 	$limit 	- Limit offset
 * @param 	resource 	$link 	- link identifier
 * @return 	number of updated rows or false
 */
// $db->arrayToUpdate($table, $data, $where = NULL, $limit = 0, $link = 0)
$db->close();
///////////////////////////////////////////////////////////////////////////////////////////

// Example 12
// Delete row
///////////////////////////////////////////////////////////////////////////////////////////
$db = MySQL_wrapper::getInstance(MySQL_HOST, MySQL_USER, MySQL_PASS, MySQL_DB);
$db->connect();

$db->deleteRow('table', "`id` = {$insert_id}");
if ($db->affected) {
	echo "<hr><strong>Example 12 (delete row)</strong><br />Deleted: {$db->affected} row(s).<br />";
}
// More options
/** Delete row(s) from table based on certain conditions.
 * @param 	string 		$table 	- Table name
 * @param 	string 		$where 	- WHERE Clause
 * @param 	integer 	$limit 	- Limit offset
 * @param 	resource 	$link 	- link identifier
 * @return 	number of deleted rows or false
 */
// $db->deleteRow($table, $where = NULL, $limit = 0, $link = 0)
$db->close();
///////////////////////////////////////////////////////////////////////////////////////////

// Example 13
// Get table columns
///////////////////////////////////////////////////////////////////////////////////////////
$db = MySQL_wrapper::getInstance(MySQL_HOST, MySQL_USER, MySQL_PASS, MySQL_DB);
// Connect
$db->connect();
echo "<hr /><strong>Example 13 (get table columns)</strong><br />Table columns are:<br />";
print_r($db->getColumns('table'));
// Close connection
$db->close();
///////////////////////////////////////////////////////////////////////////////////////////

// Example 14
// Basic Table Operation
///////////////////////////////////////////////////////////////////////////////////////////
$db = MySQL_wrapper::getInstance(MySQL_HOST, MySQL_USER, MySQL_PASS, MySQL_DB);
// Connect
$db->connect();

// Copy table (with data included)
$db->copyTable('table', 'table_copy');

// Copy table (with data included)
$db->copyTable('table', 'table_copy4');

// Copy table structure
$db->copyTable('table', 'table_copy2', FALSE);

// Rename table
$db->renameTable(array('table_copy' => 'table_copy3'));
// Swap table names
$db->renameTable(array('table_copy3' => 'tmp_table', 'table_copy2' => 'table_copy3', 'tmp_table' => 'table_copy3'));



// Truncate table (empty)
$db->truncateTable('table_copy2');

// Drop one table
$db->dropTable('table_copy4');
// Drop multiple tables
$db->dropTable(array('table_copy3', 'table_copy2'));

// Close connection
$db->close();
///////////////////////////////////////////////////////////////////////////////////////////

// Example 15
// Get database size
///////////////////////////////////////////////////////////////////////////////////////////
$db = MySQL_wrapper::getInstance(MySQL_HOST, MySQL_USER, MySQL_PASS, MySQL_DB);

// Connect
$db->connect();
/** Data Base size in B / KB / MB / GB / TB
 * @param 	string	 	$sizeIn		- Size in B / KB / MB / GB / TB
 * @param 	integer	 	$round		- Round on decimals
 * @param 	resource 	$link 		- Link identifier
 * @return 	- Size in B / KB / MB / GB / TB
 */
// function getDataBaseSize($sizeIn = 'MB', $round = 2, $link = 0)
echo '<hr /><pre>Database size is: ', $db->getDataBaseSize('mb', 2), ' MB</pre>';
// Close connection
$db->close();
///////////////////////////////////////////////////////////////////////////////////////////

// Example 16
// Loging queries and errors
///////////////////////////////////////////////////////////////////////////////////////////
$db = MySQL_wrapper::getInstance(MySQL_HOST, MySQL_USER, MySQL_PASS, MySQL_DB);
$db->connect();
$db->logQueries = TRUE; // Default is FALSE, use TRUE only for debuging
$db->logErrors = TRUE; // This is useful to be TRUE!
$db->displayError = TRUE; // Default is FALSE, use TRUE only for debuging (security reasons!)
$db->dateFormat	= "Y-m-d H:i:s"; // Date / Time format for log
$db->logFilePath = 'log-mysql.txt'; // Log file
echo "<hr /><strong>Example 14 </strong><br>Loging queries and errors.<br />";
// Query for this function will be logged
$db->getColumns('table');
// This query has error

$db->query('SELECT * FROM `table` asfd!@#$');
$db->close();
///////////////////////////////////////////////////////////////////////////////////////////

// Example 17
// Export Table to CSV
///////////////////////////////////////////////////////////////////////////////////////////
$db = MySQL_wrapper::getInstance(MySQL_HOST, MySQL_USER, MySQL_PASS, MySQL_DB);
// Connect
$db->connect();
// Export all data
$db->exportTable2CSV('table', 'test_files/test-1.txt');
// Export two or more columns
$db->exportTable2CSV('table', 'test_files/test-2.txt', 'firstname, surname');
// Export two or more columns using array
$db->exportTable2CSV('table', 'test_files/test-3.txt', array('firstname', 'surname', 'date'));
// Export all columns where id < 8 and limit 1, 5
$db->exportTable2CSV('table', 'test_files/test-4.txt', '*', 'id < 8', '1,5');
// More options
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
 * @return 	number of inserted rows or false
 */
// $db->exportTable2CSV($table, $file, $columns = '*', $where = NULL, $limit = 0, $delimiter = ',', $enclosure = '"', $escape = '\\', $newLine = '\n', $showColumns = TRUE);
// Close connection
$db->close();
///////////////////////////////////////////////////////////////////////////////////////////

// Example 18
// Query to CSV
///////////////////////////////////////////////////////////////////////////////////////////
$db = MySQL_wrapper::getInstance(MySQL_HOST, MySQL_USER, MySQL_PASS, MySQL_DB);

// Connect
$db->connect();
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
// function query2CSV($sql, $file, $delimiter = ',', $enclosure = '"', $escape = '\\', $newLine = '\n', $showColumns = TRUE)
$path = $db->query2CSV('select * from `table` limit 10', 'test_files/test-query2csv.csv');
echo '<hr /><pre>Query exported to CSV file: ', $path, '</pre>';

// example 2
$path = $db->query2CSV('select * from `table` limit 2,2', 'test_files/test-query2csv.csv');
// Close connection
$db->close();
///////////////////////////////////////////////////////////////////////////////////////////

// Example 19
// Import CSV to Table
///////////////////////////////////////////////////////////////////////////////////////////
$db = MySQL_wrapper::getInstance(MySQL_HOST, MySQL_USER, MySQL_PASS, MySQL_DB);
// Connect
$db->connect();
// Import all data
$db->importCSV2Table('test_files/test-1.txt', 'table');
// More options
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
 * @return 	number of inserted rows or false
 */
// $db->importCSV2Table($file, $table, $delimiter = ',', $enclosure = '"', $escape = '\\', $ignore = 1, $update = array(), $getColumnsFrom = 'file', $newLine = '\n') 
// Close connection
$db->close();
///////////////////////////////////////////////////////////////////////////////////////////

// Example 20
// Create table from CSV file
///////////////////////////////////////////////////////////////////////////////////////////
$db = MySQL_wrapper::getInstance(MySQL_HOST, MySQL_USER, MySQL_PASS, MySQL_DB);
$db->connect(); 

$db->dropTable('csv_to_table_test');
$db->createTableFromCSV('test_files/countrylist.csv', 'csv_to_table_test');

$db->dropTable('csv_to_table_test_no_column_names');
$db->createTableFromCSV('test_files/countrylist1.csv', 'csv_to_table_test_no_column_names', ',', '"', '\\', 0, array(), 'generate', '\r\n');

/** Create table from CSV file and imports CSV data to Table with possibility to update rows while import.
 * @param 	string		$file			- CSV File path
 * @param 	string 		$table 			- Table name
 * @param	string		$delimiter		- COLUMNS TERMINATED BY (Default: ',')
 * @param	string 		$enclosure		- OPTIONALLY ENCLOSED BY (Default: '"')
 * @param 	string		$escape 		- ESCAPED BY (Default: '\')
 * @param 	integer 	$ignore 		- Number of ignored rows (Default: 1)
 * @param 	array		$update 		- If row fields needed to be updated eg date format or increment (SQL format only @FIELD is variable with content of that field in CSV row) $update = array('SOME_DATE' => 'STR_TO_DATE(@SOME_DATE, "%d/%m/%Y")', 'SOME_INCREMENT' => '@SOME_INCREMENT + 1')
 * @param 	string 		$getColumnsFrom	- Get Columns Names from (file or generate) - this is important if there is update while inserting (Default: file)
 * @param 	string 		$newLine		- New line delimiter (Default: \n)
 * @return 	number of inserted rows or false
 */
// function createTableFromCSV($file, $table, $delimiter = ',', $enclosure = '"', $escape = '\\', $ignore = 1, $update = array(), $getColumnsFrom = 'file', $newLine = '\r\n')

$db->close();
///////////////////////////////////////////////////////////////////////////////////////////

// Example 21
// Import CSV to Table
///////////////////////////////////////////////////////////////////////////////////////////
$db = MySQL_wrapper::getInstance(MySQL_HOST, MySQL_USER, MySQL_PASS, MySQL_DB);
// Connect
$db->connect();

// Import and update all data
$db->importUpdateCSV2Table('test_files/countrylist.csv', 'csv_to_table_test');

// Import and update all data
$db->importUpdateCSV2Table('test_files/countrylist.csv', 'csv_to_table_test', ',', '"', '\\', 1, array(), 'file', '\r\n');
// More options
/** Imports (ON DUPLICATE KEY UPDATE) CSV data in Table with possibility to update rows while import.
 * @param 	string		$file			- CSV File path
 * @param 	string 		$table 			- Table name
 * @param	string		$delimiter		- COLUMNS TERMINATED BY (Default: ',')
 * @param	string 		$enclosure		- OPTIONALLY ENCLOSED BY (Default: '"')
 * @param 	string		$escape 		- ESCAPED BY (Defaul: '\')
 * @param 	integer 	$ignore 		- Number of ignored rows (Default: 1)
 * @param 	array		$update 		- If row fields needed to be updated eg date format or increment (SQL format only @FIELD is variable with content of that field in CSV row) $update = array('SOME_DATE' => 'STR_TO_DATE(@SOME_DATE, "%d/%m/%Y")', 'SOME_INCREMENT' => '@SOME_INCREMENT + 1')
 * @param 	string 		$getColumnsFrom	- Get Columns Names from (file or table) - this is important if there is update while inserting (Default: file)
 * @param 	string 		$newLine		- New line detelimiter (Default: \n)
 * @return 	number of inserted rows or false
 */
// $db->importUpdateCSV2Table($file, $table, $delimiter = ',', $enclosure = '"', $escape = '\\', $ignore = 1, $update = array(), $getColumnsFrom = 'file', $newLine = '\n') 
// Close connection
$db->close();
///////////////////////////////////////////////////////////////////////////////////////////

// Example 22
// Transactions
///////////////////////////////////////////////////////////////////////////////////////////
$db = MySQL_wrapper::getInstance(MySQL_HOST, MySQL_USER, MySQL_PASS, MySQL_DB);
// Connect
$db->connect();
$queries = array();
$queries[] = 'SELECT ...';
$queries[] = 'INSERT ...';
$queries[] = 'DELETE ...';
$queries[] = '...';
//$db->transaction($queries);
// Get more info on: http://dev.mysql.com/doc/refman/5.0/en/commit.html
/** Transaction
 * @param 	array		$qarr	- Array with Queries
 * @link	http://dev.mysql.com/doc/refman/5.0/en/commit.html
 */
// function transaction($qarr = array())
// Close connection
$db->close();
///////////////////////////////////////////////////////////////////////////////////////////

// Example 23
// String Search and Replace in all or defined Table Columns
///////////////////////////////////////////////////////////////////////////////////////////
$db = MySQL_wrapper::getInstance(MySQL_HOST, MySQL_USER, MySQL_PASS, MySQL_DB);
// Connect
$db->connect();
// Simple
$db->strReplace('table', 'firstname', 'search', 'replace');
// Search array & Replace string
$db->strReplace('table', 'firstname', array('search1', 'search2'), 'replace');
// Search array & Replace array
$db->strReplace('table', 'firstname', array('search1', 'search2'), array('replace1', 'replace2'));
// Search array of columns (Search array & Replace array) return count of updated fielsd
$count = $db->strReplace('table', array('firstname', 'surname'), array('search1', 'search2'), array('replace1', 'replace2'));
// String multiple columns
$db->strReplace('table', 'firstname, surname', 'search', 'replace');
// You can set all columns in table as well
$db->strReplace('table', '*', 'search', 'replace');
// More options
/** Replace all occurrences of the search string with the replacement string in MySQL Table Column(s).
 * @param 	string		$table 	 - Table name
 * @param 	mixed 		$columns - Search & Replace affected Table columns. An array may be used to designate multiple replacements.
 * @param 	mixed 		$search  - The value being searched for, otherwise known as the needle. An array may be used to designate multiple needles.
 * @param 	mixed 		$replace - The replacement value that replaces found search values. An array may be used to designate multiple replacements.
 * @param 	string 		$where 	 - WHERE Clause
 * @param 	integer 	$limit 	 - Limit offset
 * @return  integer 	- Affected rows
 */
// function strReplace($table, $columns, $search, $replace, $where = NULL, $limit = 0)
// Close connection
$db->close();
///////////////////////////////////////////////////////////////////////////////////////////

// Example 24
// E-mail on error / die on error
///////////////////////////////////////////////////////////////////////////////////////////
$db = MySQL_wrapper::getInstance(MySQL_HOST, MySQL_USER, MySQL_PASS, MySQL_DB);

// Connect
$db->connect(); 
$db->emailErrors = TRUE;
$db->dieOnError = TRUE;
$db->emailErrorsTo = array('rade@it-radionica.com');

$db->query("select * from asdf");
$db->query("select * from asdf2"); // this one will not be executed because dieOnError = TRUE
// Close connection
$db->close();