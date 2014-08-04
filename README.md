PHP_MySQL_wrapper
=================

This class implements a generic MySQL database access wrapper. 
It can: 
- Connect to a given MySQL server
- Set the connection character set encoding
- Execute arbitrary queries and return the results in arrays
- Retrieve the columns of a table
- Execute UPDATE or INSERT queries from parameters that define the tables, fields, field values and conditions
- Multiple INSERT / UPDATE
- Count the number of rows of a table that match a given condition
- Get the next value of an auto-incremented table field
- Delete table rows that match a given condition
- Export / Import table to/from CSV files
- Create table from CSV file
- Export query to SCV file
- Do str_replace in given table for defined columns
- Rename / Copy / Truncate / Drop table
- Get database size
- Log queries / errors
- Errors backtrace


```php
// Set your connectivity settings
define('MySQL_HOST', 'localhost');
define('MySQL_USER', 'root');
define('MySQL_PASS', '');
define('MySQL_DB', 'test');
```

*Connect to a given MySQL server*
```php

// Example 1
// Connection example
//

$db = new MySQL_wrapper(MySQL_HOST, MySQL_USER, MySQL_PASS, MySQL_DB);

// Connect
$db->connect(); 

// Close connection
$db->close();


//
// Example 2
// Connection example
//

$db = new MySQL_wrapper;

// Connect 1
$db->connect(MySQL_HOST, MySQL_USER, MySQL_PASS, MySQL_DB);

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

// Connect with new link
$db->connect(true);

//
// Connection 3 queries
//

// Close connection 3
$db->close();

// Example 3
// Connection example multi host, db manipulation
///////////////////////////////////////////////////////////////////////////////////////////

// Inst. 1
$db1 = new MySQL_wrapper(MySQL_HOST, MySQL_USER, MySQL_PASS, MySQL_DB);

// Inst. 2
$db2 = new MySQL_wrapper(MySQL_HOST, MySQL_USER, MySQL_PASS, MySQL_DB);

// Connect host 1
$db1->connect();

// Connect host 2
$db2->connect();

// Close connection host 1
$db1->close();

// Close connection host 2
$db2->close();
```

*Select example with fetch result*
```php
$db = new MySQL_wrapper(MySQL_HOST, MySQL_USER, MySQL_PASS, MySQL_DB);

// Connect to host
$db->connect();

// MySQL query
$db->query('SELECT * FROM `table`');

// Int affected rows
if($db->affected > 0){
	while($row = $db->fetchArray()){
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
if($db->affected > 0){
	while($row = $db->fetchArray()){
		// Print result row
		print_r($row);
	}
}

// Free result memory
$db->freeResult();

// Close connection
$db->close();
```

*Prepared statements (works only with MySQLi!)*
```php
$db = new MySQL_wrapper(MySQL_HOST, MySQL_USER, MySQL_PASS, MySQL_DB);

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

*Prepared statements (works only with MySQLi!)*
```php
$db = new MySQL_wrapper(MySQL_HOST, MySQL_USER, MySQL_PASS, MySQL_DB);

// Connect
$db->connect();

// Fetch query to array
$array = $db->fetchQueryToArray('SELECT * FROM `table`');

// Print array
print_r($array);

// Returns only first row
print_r($db->fetchQueryToArray('SELECT * FROM `table`', TRUE));

// Close connection
$db->close();
```

*Multi results*
```php
$db = new MySQL_wrapper(MySQL_HOST, MySQL_USER, MySQL_PASS, MySQL_DB);

// Connect to host
$db->connect();

// Result 1
$r1 = $db->query('SELECT * FROM `table`');

// Result 2
$r2 = $db->query('SELECT * FROM `table` LIMIT 2');

// Result 1 data
if($db->numRows($r1)){
	while($row = $db->fetchArray($r1)){
		// Print rows
		print_r($row);
	}
}

// Result 2 data
if($db->numRows($r2)){
	while($row = $db->fetchArray($r2)){
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

*Rows, Cols num*
```php
$db = new MySQL_wrapper(MySQL_HOST, MySQL_USER, MySQL_PASS, MySQL_DB);

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

*Count rows*
```php
$db = new MySQL_wrapper(MySQL_HOST, MySQL_USER, MySQL_PASS, MySQL_DB);

// Connect to host
$db->connect();

// Count all
$count = $db->countRows('table');

// Count with condition
$count2 = $db->countRows('table', "`date` = '".date("Y-m-d")."'");

// ...
echo "Count all: {$count}, Count today: {$count2}";

// More info
/** Retrieves the number of rows from table based on certain conditions.
 * @param 	string 		$table 	- Table name
 * @param 	string 		$where 	- WHERE Clause
 * @return 	integer or false
 */
// $db->countRows($table, $where = NULL)

// Close connection
$db->close();
```

*Array to insert*
```php
$db = new MySQL_wrapper(MySQL_HOST, MySQL_USER, MySQL_PASS, MySQL_DB);

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

// Close connection
$db->close();
```
*Next AutoIncrement*
```php
$db = new MySQL_wrapper(MySQL_HOST, MySQL_USER, MySQL_PASS, MySQL_DB);

// Connect to host
$db->connect();

// Returns next auto increment value
$auto_increment = $db->nextAutoIncrement('table');

echo "Next auto increment id is: {$auto_increment}";

// Close connection
$db->close();
```

*Array to update*
```php
$db = new MySQL_wrapper(MySQL_HOST, MySQL_USER, MySQL_PASS, MySQL_DB);

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
if($db->affected > 0){
	echo "Updated: {$db->affected} row(s).";
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
// $db->arrayToUpdate($table, $data, $where = NULL, $limit = 0, $link = 0);

// Close connection
$db->close();
```

*Delete row(s)*
```php
$db = new MySQL_wrapper(MySQL_HOST, MySQL_USER, MySQL_PASS, MySQL_DB);

// Connect to host
$db->connect();

// Delete row
$db->deleteRow('table', "`id` = {$insert_id}");

if($db->affected > 0) {
	echo "Deleted: {$db->affected} row(s).";
}
// More options
/** Delete row(s) from table based on certain conditions.
 * @param 	string 		$table 	- Table name
 * @param 	string 		$where 	- WHERE Clause
 * @param 	integer 	$limit 	- Limit offset
 * @param 	resource 	$link 	- link identifier
 * @return 	number of deleted rows or false
 */
// $db->deleteRow($table, $where = NULL, $limit = 0, $link = 0);

// Close connection
$db->close();
```

*Get table columns*
```php
$db = new MySQL_wrapper(MySQL_HOST, MySQL_USER, MySQL_PASS, MySQL_DB);

// Connect
$db->connect();

// Get table columns into array
$array = $db->getColumns('table');

print_r($array);

// Close connection
$db->close();
```

*Basic Table Operation*
```php
$db = new MySQL_wrapper(MySQL_HOST, MySQL_USER, MySQL_PASS, MySQL_DB);

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

*Get database size*
```php
$db = new MySQL_wrapper(MySQL_HOST, MySQL_USER, MySQL_PASS, MySQL_DB);

// Connect
$db->connect();

/** Data Base size in B / KB / MB / GB / TB
 * @param 	string	 	$sizeIn		- Size in B / KB / MB / GB / TB
 * @param 	integer	 	$round		- Round on decimals
 * @param 	resource 	$link 		- Link identifier
 * @return 	- Size in B / KB / MB / GB / TB
 */
// function getDataBaseSize($sizeIn = 'MB', $round = 2, $link = 0);

echo 'Database size is: ', $db->getDataBaseSize('mb', 2), ' MB';

// Close connection
$db->close();
```

*Loging queries and errors*
```php
$db = new MySQL_wrapper(MySQL_HOST, MySQL_USER, MySQL_PASS, MySQL_DB);

// Connect to host
$db->connect();

// Default is FALSE, use TRUE only for debuging
$db->logQueries = TRUE;

// This is useful to be TRUE!
$db->logErrors = TRUE;

// Default is FALSE, use TRUE only for debuging (security reasons!)
$db->displayError = TRUE;

// Date / Time format for log
$db->dateFormat	= "Y-m-d H:i:s"; 

// Log file
$db->logFilePath = 'log-mysql.txt';

// Query for this function will be logged
$db->getColumns('table');

// This query has error
$db->query('SELECT * FROM `table` asfd!@#$');

// Close connection
$db->close();
```
