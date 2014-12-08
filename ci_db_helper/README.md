## CI DB Helper Trait

Extendable query builder to be used with CodeIgniter 2.2+. Extend any CI controller and use a trait to build MySQL/PostgreSQL queries. Also contains custom filters collection that prevent any SQL queries exposure, i.e. when building API controller with filtering.

### Contributors

This trait was written by Paul Burakov at SeamlessDocs in 2014.

### Basic Usage

    class User extends CI_Controller
    {
        use DB_Helper;
        
        public function get($id) 
        {
            $this->addTable('users');
            $this->addFilter('id', $id);
            $this->setLimit(1);
            
            // Will generate a query:
            // "SELECT * FROM users WHERE id = '$id' LIMIT 1"
            
            return $this->quickSelect();
        }
    }
    
Values used in a query are escaped using CodeIgniter own DB class methods.
    
### Filters

This helper contains a set of filters that can be used in the URL queries of a server request, to avoid any exposure of a "real" SQL syntax i.e.:

    $this->addTable('users');
    
    foreach ($_GET['filters'] as $filter) {
        $this->addTableDataFilter($filter['column'], $filter['operand'], $filter['value']);
    }
    
    $this->setOffset($_GET['offset']);
    
will convert the request 

    GET http://your_api.com/user?filters[0][column]=address&filters[0][operand]=contains&filters[0][value]=Sunset Drive&offset=4
    
into query

    SELECT * FROM users WHERE address LIKE '%Sunset Drive%' OFFSET 4
    
Here are other filters example `contains` (LIKE), `is` (=), `is not` (!=), `is null`, `is greater than`, `is in` and so on. See method `convertConditionToOperand()` for full list of filters.

Use caution to avoid Exceptions. Incoming arguments require additional validation using if a filter can be used with a column data type and a check of the column exists. This example uses CI DB class to acomplish that:

    function clean($table, array $columns)
    {
        if (empty($data)) return [];
    
        $db = $this->db;
        $allowed_fields = $db->list_fields($table);
    
        foreach ((array)$data as $column) {
            if (in_array($column, $allowed_fields))
                $out[] = $column;
        }
    
        return $out;
    }

### Advanced usage

The helper provides methods for generation of JOIN queries, UPDATE and INSERT statements querying multiple tables, selecting columns, custom filters, ordering and sorting. Use `buildQuery()` method to return raw SQL statement, or its parts


    $this->addTable('users');
    $this->addField(['address', 'first_name', 'last_name']);
    $this->addOrderBy('last_name', 'ASC');
    $this->addOrderBy('first_name', 'ASC');
    $this->addFilter('address', '200 NW St');
    $this->addCustomFilter("(first_name != 'John' AND last_name != 'Doe')");
    $this->addJoin('groups', 'users.group_id = groups.group_id');
    
    $where_sql = $this->buildWhereClause('OR');
    
    echo $where_sql;
    // WHERE address = '200 NW St' AND (first_name != 'John' OR last_name != 'Doe')
    
    $this->addFilter('address', '2nd Ave');
    $full_sql = $this->buildQuery();
     
    echo $full_sql;
    // SELECT address, first_name, last_name FROM users LEFT JOIN groups ON users.group_id = groups.group_id WHERE address = '2nd Ave' ORDER BY last_name ASC, first_name ASC

**Note** that `WHERE...` query parts have been reset after being built and "dumped" using `buildWhereClause()` method. Same applies to `buildSelectStatement()`, `buildJoinStatement()` and other methods. Entire SQL statement is reset and cannot be built or executed twice after `buildQuery()` and `quickSelect()`, `quickUpdate()` and so on. 

Please refer to the code for more info.

### Disclaimer

Please, use this helper "as is", I give no warranty whatsoever that this helper can be safely used within your production environment. 