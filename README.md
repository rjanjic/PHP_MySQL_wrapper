PHP MySQL Wrapper Class
=======================

This class implements a generic MySQL database access wrapper. 

* [Connect to a given MySQL server](#connect-to-a-given-mysql-server)
 * [Connection examples](#connection-examples)
 * [Connection example multi host, db manipulation](#connection-example-multi-host-db-manipulation)
* [Set the connection character set encoding](#set-the-connection-character-set-encoding)
* [Execute arbitrary queries and return the results in arrays](#execute-arbitrary-queries-and-return-the-results-in-arrays)
 * [Select example with fetch result](#select-example-with-fetch-result)
 * [Prepared statements](#prepared-statements-works-only-with-mysqli)
 * [Prepared statements - mysqlnd driver not installed](#prepared-statements-works-only-with-mysqli---if-mysqlnd-driver-is-not-installed)
 * [Fetch query to array](#fetch-query-to-array)
 * [Multi results](#multi-results)
 * [Rows, Cols num](#rows-cols-num)
 * [Count rows](#count-rows)
* [Execute UPDATE or INSERT queries from parameters that define the tables, fields, field values and conditions](#execute-update-or-insert-queries-from-parameters-that-define-the-tables-fields-field-values-and-conditions)
 * [Array to insert](#array-to-insert)
 * [Multiple array to insert](#array-to-insert-multirow)
 * [Array to update](#array-to-update)
 * [Multiple array to update](#array-to-update-multirow)
* [Count the number of rows of a table that match a given condition](#count-rows)
* [Delete table rows that match a given condition](#delete-rows)
* [Operations with CSV files](#operations-with-csv-files)
 * [Export table to CSV](#export-table-to-csv)
 * [Export query to CSV](#export-query-to-csv)
 * [Export table / export query to CSV using fputcsv](#export-table--export-query-to-csv-using-fputcsv)
 * [Download CSV file from query](#download-csv-file-from-query)
 * [Import CSV to Table](#import-csv-to-table)
 * [Import and update CSV to Table](#import-and-update-csv-to-table)
 * [Create table from CSV file](#create-table-from-csv-file)
* [Operations with XML files](#operations-with-xml-files)
 * [Export query to XML](#export-query-to-xml)
 * [Download XML file from query](#download-xml-file-from-query)
* [Do str_replace in given database, table or defined columns in table](#string-search-and-replace-in-all-or-defined-table-columns)
 * [Search string & replace string](#string-search-and-replace-in-all-or-defined-table-columns)
 * [Search array & replace string](#string-search-and-replace-in-all-or-defined-table-columns)
 * [Search array & replace array](#string-search-and-replace-in-all-or-defined-table-columns)
 * [Search array of columns (search array & replace array) return count of updated](#string-search-and-replace-in-all-or-defined-table-columns)
 * [String multiple columns](#string-search-and-replace-in-all-or-defined-table-columns)
 * [All columns in table](#string-search-and-replace-in-all-or-defined-table-columns)
 * [Whole database](#string-search-and-replace-in-all-or-defined-table-columns)
* [Basic table operations](#basic-table-operation)
 * [Copy table (with data included)](#basic-table-operation)
 * [Copy table structure](#basic-table-operation)
 * [Rename table](#basic-table-operation)
 * [Swap table names](#basic-table-operation)
 * [Truncate table (empty)](#basic-table-operation)
 * [Drop one table](#basic-table-operation)
 * [Drop multiple tables](#basic-table-operation)
 * [Get table columns](#get-table-columns)
 * [Get database size](#get-database-size)
 * [Get the next value of an auto-incremented table field](#next-autoincrement)
 * [Table revision](#table-revision)
* [Logging / debug](#loging--debug)
 * [Logging errors](#logging-errors)
 * [Logging queries](#logging-queries)
 * [E-mail on error / die on error](#e-mail-on-error--die-on-error)
 * [Errors backtrace and debug](#errors-backtrace-and-debug)

### Connectivity settings
```php
// Set your connectivity settings
define('MySQL_HOST', 'localhost'); // localhost:3306
define('MySQL_USER', 'root');
define('MySQL_PASS', '');
define('MySQL_DB', 'test');
```

### Connect to a given MySQL server
```php
$db = MySQL_wrapper::getInstance(MySQL_HOST, MySQL_USER, MySQL_PASS, MySQL_DB);

// Connect
$db->connect(); 

//
// ... do queries
//

// Close connection
$db->close();
```

#### Connection examples
```php
$db = MySQL_wrapper::getInstance(MySQL_HOST, MySQL_USER, MySQL_PASS, MySQL_DB);

// Connect 1
$db->connect();

//
// Connection 1 queries ...
//

// Close connection 1
$db->close();

// Connect 2
$db->connect(MySQL_HOST, MySQL_USER, MySQL_PASS, MySQL_DB); 

//
// Connection 2 queries ...
//

// Close connection 2
$db->close();

// Connection 3
$db->connect();

//
// Connection 3 queries
//

// Close connection 3
$db->close();
```

#### Connection example multi host, db manipulation
```php
// Inst. 1
$db1 = MySQL_wrapper::getInstance('host1', MySQL_USER, MySQL_PASS, MySQL_DB);

// Inst. 2
$db2 = MySQL_wrapper::getInstance('host2', MySQL_USER, MySQL_PASS, MySQL_DB);

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
```

### Set the connection character set encoding

#### Example 1
```php
$db = MySQL_wrapper::getInstance(MySQL_HOST, MySQL_USER, MySQL_PASS, MySQL_DB);

// Connect
$db->connect(); 

// Set charset
$db->charset = 'utf8';;

// Close connection
$db->close();
```

#### Example 2
```php
$db = MySQL_wrapper::getInstance(MySQL_HOST, MySQL_USER, MySQL_PASS, MySQL_DB);

// Connect
$db->connect(); 

// Set charset
$db->setCharset('utf8');

// Close connection
$db->close();
```

### Execute arbitrary queries and return the results in arrays

#### Select example with fetch result
```php
$db = MySQL_wrapper::getInstance(MySQL_HOST, MySQL_USER, MySQL_PASS, MySQL_DB);

// Connect to host
$db->connect();

// MySQL query
$db->query('SELECT * FROM `table`');

// Int affected rows
if ($db->affected > 0) {
	while ($row = $db->fetchArray()) {
		// Result
		print_r($row);
	}
}

// Free result memory
$db->freeResult();

// Escape string
$var = '\'';

// Do query
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

// Exec query
$db->query("SELECT * FROM `table` WHERE `firstname` LIKE '@name%' AND `surname` LIKE '%@lname%' OR `id` = @id LIMIT @limit;", $params);

// Int affected rows
if ($db->affected > 0) {
	while ($row = $db->fetchArray()) {
		// Print result row
		print_r($row);
	}
}

// Free result memory
$db->freeResult();

// Close connection
$db->close();
```

#### Prepared statements (works only with MySQLi!)
```php
$db = MySQL_wrapper::getInstance(MySQL_HOST, MySQL_USER, MySQL_PASS, MySQL_DB);

// Works only with MySQLi!
$db->extension = 'mysqli';

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
```

#### Prepared statements (works only with MySQLi!) - if mysqlnd driver is not installed
```php
$db = MySQL_wrapper::getInstance(MySQL_HOST, MySQL_USER, MySQL_PASS, MySQL_DB);

// Connect
$db->connect();

$stmt = $db->call('prepare', 'SELECT `id`, `firstname`, `surname`, `email`  FROM `table` WHERE `level` = ?;');
$stmt->bind_param('i', $level);
$stmt->execute();

$stmt->bind_result($id, $firstname, $surname, $email);
$data = array();
while ($stmt->fetch()) {
	$data[] = array(
		'id' 		=> $id,
		'firstname' => $firstname,
		'surname' 	=> $surname,
		'email' 	=> $email
	);
}

// Print data
print_r($data);

// Close connection
$db->close();
```

#### Fetch query to array
```php
$db = MySQL_wrapper::getInstance(MySQL_HOST, MySQL_USER, MySQL_PASS, MySQL_DB);

// Connect
$db->connect();

// Fetch query to array
$array = $db->fetchQueryToArray('SELECT * FROM `table`');

// Print array
print_r($array);

// Returns only first row
$array = $db->fetchQueryToArray('SELECT * FROM `table`', TRUE);

// Print array
print_r($array);

// Close connection
$db->close();
```

#### Multi results
```php
$db = MySQL_wrapper::getInstance(MySQL_HOST, MySQL_USER, MySQL_PASS, MySQL_DB);

// Connect to host
$db->connect();

// Result 1
$r1 = $db->query('SELECT * FROM `table`');

// Result 2
$r2 = $db->query('SELECT * FROM `table` LIMIT 2');

// Result 1 data
if ($db->numRows($r1)) {
	while ($row = $db->fetchArray($r1)) {
		// Print rows
		print_r($row);
	}
}

// Result 2 data
if ($db->numRows($r2)) {
	while ($row = $db->fetchArray($r2)) {
		// Print rows
		print_r($row);
	}
}

// Free relust 1
$db->freeResult($r1);

// Free relust 2
$db->freeResult($r2);

// Close connection
$db->close();
```

#### Rows, Cols num
```php
$db = MySQL_wrapper::getInstance(MySQL_HOST, MySQL_USER, MySQL_PASS, MySQL_DB);

// Connect to host
$db->connect();

// Do query
$db->query('SELECT * FROM `table`');

$cols = $db->numFields();
$rows = $db->numRows();

// ...
echo "Cols: {$cols}, Rows: {$rows}";

// Free result memory
$db->freeResult();

// Close connection
$db->close();
```

#### Count rows
```php
$db = MySQL_wrapper::getInstance(MySQL_HOST, MySQL_USER, MySQL_PASS, MySQL_DB);

// Connect to host
$db->connect();

// Count all
$count = $db->countRows('table');

// Count with condition
$count2 = $db->countRows('table', "`date` = '" . date("Y-m-d") . "'");

// ...
echo "Count all: {$count}, Count today: {$count2}";

// More info
/** Retrieves the number of rows from table based on certain conditions.
 * @param 	string 		$table 	- Table name
 * @param 	string 		$where 	- WHERE Clause
 * @return 	integer or false
 *
 * function countRows($table, $where = NULL);
 */

// Close connection
$db->close();
```
### Execute UPDATE or INSERT queries from parameters that define the tables, fields, field values and conditions

#### Array to insert
```php
$db = MySQL_wrapper::getInstance(MySQL_HOST, MySQL_USER, MySQL_PASS, MySQL_DB);

// Connect to host
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
echo "Last insert id is: {$insert_id}";

// More options
/** Creates an sql string from an associate array
 * @param 	string 		$table 	- Table name
 * @param 	array 		$data 	- Data array Eg. $data['column'] = 'val';
 * @param 	boolean		$ingore	- INSERT IGNORE (row won't actually be inserted if it results in a duplicate key)
 * @param 	string 		$duplicateupdate 	- ON DUPLICATE KEY UPDATE (The ON DUPLICATE KEY UPDATE clause can contain multiple column assignments, separated by commas.)
 * @return 	insert id or false
 *
 * function arrayToInsert($table, $data, $ignore = FALSE, $duplicateupdate = NULL);
 */

// Close connection
$db->close();
```

#### Array to insert multirow
```php
$db = MySQL_wrapper::getInstance(MySQL_HOST, MySQL_USER, MySQL_PASS, MySQL_DB);

// Connect to host
$db->connect();
 
// Array data
// [fealdname] = feald value
$data = array();
 
// Data set 1
$data[] = array(
	'firstname' => 'foo',
	'surname' => 'bar',
	'email' => 'hi@radovanjanjic.com',
	'date' => 'now()'
);
 
// Data set 2
$data[] = array(
	'firstname' => 'baz',
	'surname' => 'qux',
	'email' => 'hi@radovanjanjic.com',
	'date' => 'now()'
);
 
// Data set ...
 
// $db->arrayToInsert( ... ) multirow returns TRUE on success
$db->arrayToInsert('table', $data);

// Close connection
$db->close();
```

#### Array to update
```php
$db = MySQL_wrapper::getInstance(MySQL_HOST, MySQL_USER, MySQL_PASS, MySQL_DB);

// Connect to host
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
if ($db->affected > 0) {
	echo "Updated: {$db->affected} row(s).";
}

// More options
/** Creates an sql string from an associate array
 * @param 	string 		$table 	- Table name
 * @param 	array 		$data 	- Data array Eg. $data['column'] = 'val';
 * @param 	string 		$where 	- MySQL WHERE Clause
 * @param 	integer 	$limit 	- Limit offset
 * @param 	resource 	$link 	- link identifier
 * @return 	number of updated rows or false
 *
 * function arrayToUpdate($table, $data, $where = NULL, $limit = 0, $link = 0);
 */

// Close connection
$db->close();
```

#### Array to update multirow
```php
$db = MySQL_wrapper::getInstance(MySQL_HOST, MySQL_USER, MySQL_PASS, MySQL_DB);

// Connect to host
$db->connect();

// Array data
// [fealdname] = feald value
$data = array();

// Data set 1
$data[] = array(
 
	// Condition 
	'id' => 1, // One of the fields has to be primary or unique key in order to update
	
	// Data to update
	'firstname' => 'foooo',
	'surname' => 'barrr'
	// ...
);

// Data set 2
$data[] = array(
 
	// Condition 
	'id' => 2, // One of the fields has to be primary or unique key in order to update
	
	// Data to update
	'firstname' => 'bazzz',
	'surname' => 'quxxx'
	// ...
);

// Data set ...

// $db->arrayToUpdate( ... ) multirow returns TRUE on success
$db->arrayToUpdate('table', $data);

// Close connection
$db->close();
```

### Delete row(s)
```php
$db = MySQL_wrapper::getInstance(MySQL_HOST, MySQL_USER, MySQL_PASS, MySQL_DB);

// Connect to host
$db->connect();

// Delete row
$db->deleteRow('table', "`id` = {$insert_id}");

if ($db->affected > 0) {
	echo "Deleted: {$db->affected} row(s).";
}
// More options
/** Delete row(s) from table based on certain conditions.
 * @param 	string 		$table 	- Table name
 * @param 	string 		$where 	- WHERE Clause
 * @param 	integer 	$limit 	- Limit offset
 * @param 	resource 	$link 	- link identifier
 * @return 	number of deleted rows or false
 *
 * function deleteRow($table, $where = NULL, $limit = 0, $link = 0);
 */

// Close connection
$db->close();
```

### Operations with CSV files

#### Export Table to CSV
```php
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
 *
 * function exportTable2CSV($table, $file, $columns = '*', $where = NULL, $limit = 0, $delimiter = ',', $enclosure = '"', $escape = '\\', $newLine = '\n', $showColumns = TRUE);
 */

// Close connection
$db->close();
```

#### Export query to CSV
```php
$db = MySQL_wrapper::getInstance(MySQL_HOST, MySQL_USER, MySQL_PASS, MySQL_DB);

// Connect
$db->connect();

$path = $db->query2CSV('select * from `table` limit 10', 'test_files/test-query2csv.csv');
echo 'Query exported to CSV file: ', $path;

// Example 2
$path = $db->query2CSV('select * from `table` limit 2,2', 'test_files/test-query2csv.csv');

/** Export query to CSV file.
 * @param 	string 		$sql 			- MySQL Query
 * @param 	string		$file			- CSV File path
 * @param	string		$delimiter		- COLUMNS TERMINATED BY (Default: ',')
 * @param	string 		$enclosure		- OPTIONALLY ENCLOSED BY (Default: '"')
 * @param 	string		$escape 		- ESCAPED BY (Default: '\')
 * @param 	string 		$newLine		- New line delimiter (Default: \n)
 * @param 	boolean		$showColumns 	- Columns names in first line
 * @return 	- File path
 *
 * function query2CSV($sql, $file, $delimiter = ',', $enclosure = '"', $escape = '\\', $newLine = '\n', $showColumns = TRUE);
 */

// Close connection
$db->close();
```

#### Export table / export query to CSV using fputcsv
```php
$db = MySQL_wrapper::getInstance(MySQL_HOST, MySQL_USER, MySQL_PASS, MySQL_DB);

// Connect
$db->connect();

// Don't use mysql outfile
$db->mysqlOutFile = FALSE;

// Table to CSV
$db->exportTable2CSV('table', 'test_files/test-1.txt');

// Query to CSV
$path = $db->query2CSV('select * from `table` limit 10', 'test_files/test-query2csv.csv');

// Close connection
$db->close();
```

#### Download CSV file from query
```php
$db = MySQL_wrapper::getInstance(MySQL_HOST, MySQL_USER, MySQL_PASS, MySQL_DB);

// Connect
$db->connect();

// Set as attachment and execute
$db->attachment()->query2CSV('select * from `table`', 'test.csv');

// Close connection
$db->close();
```

#### Import CSV to Table
```php
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
 *
 * function importCSV2Table($file, $table, $delimiter = ',', $enclosure = '"', $escape = '\\', $ignore = 1, $update = array(), $getColumnsFrom = 'file', $newLine = '\n');
 */

// Close connection
$db->close();
```

#### Import and update CSV to Table
```php
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
 *
 * function importUpdateCSV2Table($file, $table, $delimiter = ',', $enclosure = '"', $escape = '\\', $ignore = 1, $update = array(), $getColumnsFrom = 'file', $newLine = '\n');
 */

// Close connection
$db->close();
```

#### Create table from CSV file
```php
$db = MySQL_wrapper::getInstance(MySQL_HOST, MySQL_USER, MySQL_PASS, MySQL_DB);

// Connect to host
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
 *
 * function createTableFromCSV($file, $table, $delimiter = ',', $enclosure = '"', $escape = '\\', $ignore = 1, $update = array(), $getColumnsFrom = 'file', $newLine = '\r\n');
 */ 

// Close connection
$db->close();
```

### Operations with XML files

#### Export query to XML
```php
$db = MySQL_wrapper::getInstance(MySQL_HOST, MySQL_USER, MySQL_PASS, MySQL_DB);

// Connect
$db->connect();

// Save result as file
$db->query2XML('select * from `table` limit 10', 'items', 'item', 'test_files/test-query2xml.csv');

// Return result as XML
$xml = $db->query2XML('select * from `table` limit 10', 'items', 'item');

/** Export query to XML file or return as XML string
 * @param	string		$query			- mysql query
 * @param	string		$rootElementName	- root element name
 * @param	string		$childElementName	- child element name
 * @return	string		- XML
 *
 * function query2XML($query, $rootElementName, $childElementName, $file = NULL);
 */

// Close connection
$db->close();
```

#### Download XML file from query
```php
$db = MySQL_wrapper::getInstance(MySQL_HOST, MySQL_USER, MySQL_PASS, MySQL_DB);

// Connect
$db->connect();

// Set as attachment and execute
$db->attachment()->query2XML('select * from `table`', 'root', 'item', 'test.xml');

// Close connection
$db->close();
```

### Transactions
```php
$db = MySQL_wrapper::getInstance(MySQL_HOST, MySQL_USER, MySQL_PASS, MySQL_DB);

// Connect
$db->connect();

// Queries
$queries = array();
$queries[] = 'SELECT ...';
$queries[] = 'INSERT ...';
$queries[] = 'DELETE ...';
$queries[] = '...';

// Do Transaction
$db->transaction($queries);

// Get more info on: http://dev.mysql.com/doc/refman/5.0/en/commit.html
/** Transaction
 * @param 	array		$qarr	- Array with Queries
 * @link	http://dev.mysql.com/doc/refman/5.0/en/commit.html
 *
 * function transaction($qarr = array());
 */

// Close connection
$db->close();
```

### String Search and Replace in all or defined Table Columns
```php
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

// Whole database
$db->strReplace('*', '*', 'search', 'replace');

// More options
/** Replace all occurrences of the search string with the replacement string in MySQL Table Column(s).
 * @param 	string		$table 	 - Table name
 * @param 	mixed 		$columns - Search & Replace affected Table columns. An array may be used to designate multiple replacements.
 * @param 	mixed 		$search  - The value being searched for, otherwise known as the needle. An array may be used to designate multiple needles.
 * @param 	mixed 		$replace - The replacement value that replaces found search values. An array may be used to designate multiple replacements.
 * @param 	string 		$where 	 - WHERE Clause
 * @param 	integer 	$limit 	 - Limit offset
 * @return  integer 	- Affected rows
 *
 * function strReplace($table, $columns, $search, $replace, $where = NULL, $limit = 0);
 */

// Close connection
$db->close();
```

### Basic Table Operation
```php
$db = MySQL_wrapper::getInstance(MySQL_HOST, MySQL_USER, MySQL_PASS, MySQL_DB);

// Connect to host
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
```

#### Get table columns
```php
$db = MySQL_wrapper::getInstance(MySQL_HOST, MySQL_USER, MySQL_PASS, MySQL_DB);

// Connect
$db->connect();

// Get table columns into array
$array = $db->getColumns('table');

print_r($array);

// Close connection
$db->close();
```

#### Get database size
```php
$db = MySQL_wrapper::getInstance(MySQL_HOST, MySQL_USER, MySQL_PASS, MySQL_DB);

// Connect
$db->connect();

/** Data Base size in B / KB / MB / GB / TB
 * @param 	string	 	$sizeIn		- Size in B / KB / MB / GB / TB
 * @param 	integer	 	$round		- Round on decimals
 * @param 	resource 	$link 		- Link identifier
 * @return 	- Size in B / KB / MB / GB / TB
 *
 * function getDataBaseSize($sizeIn = 'MB', $round = 2, $link = 0);
 */

echo 'Database size is: ', $db->getDataBaseSize('mb', 2), ' MB';

// Close connection
$db->close();
```

#### Next AutoIncrement
```php
$db = MySQL_wrapper::getInstance(MySQL_HOST, MySQL_USER, MySQL_PASS, MySQL_DB);

// Connect to host
$db->connect();

// Returns next auto increment value
$auto_increment = $db->nextAutoIncrement('table');

echo "Next auto increment id is: {$auto_increment}";

// Close connection
$db->close();
```

#### Table revision
```php

$db = MySQL_wrapper::getInstance(MySQL_HOST, MySQL_USER, MySQL_PASS, MySQL_DB);

// Connect
$db->connect();

// Init table revision (do this only once!)
$db->initTableRevision('rev-table');

// Time to restore to ... 
$time = '2014-06-25 14:26:03';

/** Create table from current revision time
 * @param 	string		$table		- New table name
 * @param	string 		$rev_table	- Revision table (origin table)
 * @param	string 		$id_field	- Unique field name
 * @param	datetime	- Revision time
 *
 * function createTableFromRevisionTime($table, $rev_table, $id_field, $time);
 */
		
$db->createTableFromRevisionTime('rev-table' . '-' . $time, 'rev-table', 'id', $time);

/** Restore table from current revision time
 * @param 	string		$table		- New table name
 * @param	string 		$id_field	- Unique field name
 * @param	datetime	- Revision time
 *
 * function restoreTableFromRevisionTime($table, $id_field, $time);
 */

$db->restoreTableFromRevisionTime('rev-table', 'id', $time);

// Close connection
$db->close();
```

### Logging / debug

#### Logging errors
```php
$db = MySQL_wrapper::getInstance(MySQL_HOST, MySQL_USER, MySQL_PASS, MySQL_DB);

// Connect to host
$db->connect();

// This is useful to be TRUE!
$db->logErrors = TRUE;

// Default is FALSE, use TRUE only for debuging (security reasons!)
$db->displayError = TRUE;

// Date / Time format for log
$db->dateFormat	= "Y-m-d H:i:s"; 

// Log file
$db->logFilePath = 'log-mysql.txt';

// This query has error
$db->query('SELECT * FROM `table` asfd!@#$');

// Close connection
$db->close();
```

#### Logging queries
```php
$db = MySQL_wrapper::getInstance(MySQL_HOST, MySQL_USER, MySQL_PASS, MySQL_DB);

// Connect to host
$db->connect();

// Default is FALSE, use TRUE only for debuging
$db->logQueries = TRUE;

// Log file
$db->logFilePath = 'log-mysql.txt';

// Query for this function will be logged
$db->getColumns('table');

// Query will be logged as well ...
$db->query('SELECT * FROM `table`;');

// Close connection
$db->close();
```


#### E-mail on error / die on error
```php
$db = MySQL_wrapper::getInstance(MySQL_HOST, MySQL_USER, MySQL_PASS, MySQL_DB);

// Connect
$db->connect(); 

// Send mail on error
$db->emailErrors = TRUE;

// Die on errors
$db->dieOnError = TRUE;

// Array of emails
$db->emailErrorsTo = array('rade@it-radionica.com');

// Do first query
$db->query("select * from asdf");

// This one will not be executed if first query have error and dieOnError is TRUE
$db->query("select * from asdf2"); 

// Close connection
$db->close();
```

#### Errors backtrace and debug

```php
$db = MySQL_wrapper::getInstance(MySQL_HOST, MySQL_USER, MySQL_PASS, MySQL_DB);

// Connect to host
$db->connect();

// Default is FALSE, use TRUE only for debuging (security reasons!)
$db->displayError = TRUE;

// This query has error
$db->query('SELECT * FROM `table` asfd!@#$');

// Close connection
$db->close();

```

Display error example:

```
Query fail: SELECT * FROM `table` asfd!@#$
- Error No: 1064
- Error: You have an error in your SQL syntax; check the manual that corresponds to your MySQL server version for the right syntax to use near '!@#$' at line 1
- Call: Function query in C:\xampp\htdocs\Git\PHP_MySQL_wrapper\test.php on line 29
```

Draw result / describe table / explain query:

```php
$db = MySQL_wrapper::getInstance(MySQL_HOST, MySQL_USER, MySQL_PASS, MySQL_DB);

// Connect
$db->connect(); 

// Draw query result data in table
$array = $db->fetchQueryToArray('SELECT * FROM `table` LIMIT 2;');
$db->drawTable($array, 'Test table contents');

/** Draw ascii table
 * @param	array	$data	- Multidimensional array of data
 * @param	string	$title	- Table header
 * @return	void
 *
 * function drawTable($data, $title = NULL);
 */

// Draw query execution plan in table
$db->explain('SELECT * FROM `table`;');
 
// Draw information about the columns in a table
$db->describe('table');

// Close connection
$db->close(); 
```

Draw table output:

```
+---------------------------------------------------------------+
|                      Test table contents                      |
+----+-----------+---------+-----------------------+------------+
| id | firstname | surname | email                 | date       |
+----+-----------+---------+-----------------------+------------+
|  1 | foo       | bar     | rade@it-radionica.com | 2014-10-02 |
|  2 | Radovan   | Janjic  | rade@it-radionica.com | 2014-10-02 |
+----+-----------+---------+-----------------------+------------+
```

Explain output:

```
+--------------------------------------------------------------------------------------+
|                                 Explain MySQL Query                                  |
+----+-------------+-------+------+---------------+-----+---------+-----+------+-------+
| id | select_type | table | type | possible_keys | key | key_len | ref | rows | Extra |
+----+-------------+-------+------+---------------+-----+---------+-----+------+-------+
|  1 | SIMPLE      | table | ALL  |               |     |         |     |   98 |       |
+----+-------------+-------+------+---------------+-----+---------+-----+------+-------+
```

Describe output:

```
+------------------------------------------------------------------+
|                               test                               |
+-----------+--------------+------+-----+---------+----------------+
| Field     | Type         | Null | Key | Default | Extra          |
+-----------+--------------+------+-----+---------+----------------+
| id        | int(11)      | NO   | PRI |         | auto_increment |
| firstname | varchar(100) | NO   |     |         |                |
| surname   | varchar(100) | NO   |     |         |                |
+-----------+--------------+------+-----+---------+----------------+
``` 

