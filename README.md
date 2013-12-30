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
