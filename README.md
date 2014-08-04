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
include "MySQL_wrapper.class.php";

// Set your connectivity settings here
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

```
