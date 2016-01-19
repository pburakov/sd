<?php

/**
 * CodeIgniter DB Helper Trait
 * by P. Burakov @ SeamlessDocs
 */
trait DB_Helper
{

    protected $filters = array();                // field list WHERE clause is assembled from
    protected $joins = array();                  // array of JOIN statements
    protected $fields = array();                 // list of fields that follow SELECT statement
    protected $tables = array();                 // list of tables that follow FROM statement
    protected $values = array();                 // list of fields that follow UPDATE table SET statement

    protected $offset = 0;                       // Default OFFSET value
    protected $limit = null;                     // Default LIMIT value
    protected $order_by = array();


    protected function addValue($pair)
    {
        if (is_array($pair)) {
            $db = $this->db;

            foreach ($pair as $column => $value) {
                $this->values[$column] = $db->escape($value);
            }
        } else {
            throw new Exception ("Can't add value. Expected array pair(s) 'column' => 'value'");
        }
    }


    protected function resetValues()
    {
        $this->values = array();
    }


    protected function buildUpdateStatement()
    {
        if (!empty($this->tables) && count($this->tables) >= 1 || !empty($this->values)) {
            $output = "UPDATE " . $this->tables[0] . " SET ";

            $pairs = array();
            foreach ($this->values as $column => $value) $pairs[] = "$column = $value";

            $output .= implode(", ", $pairs);

            $this->resetValues();
            $this->resetTables();
            return $output;
        } else {
            throw new Exception ("Can't build SQL Update statement. Table error or missing values");
        }
    }


    protected function buildInsertStatement()
    {
        if (!empty($this->tables) && count($this->tables) == 1 || !empty($this->values)) {

            $output = "INSERT INTO " . $this->tables[0] .
                " (" . implode(', ', array_keys($this->values)) . ") VALUES (" . implode(', ', $this->values) . ")";

            $this->resetValues();
            $this->resetTables();
            return $output;
        } else {
            throw new Exception ("Can't build SQL Insert statement. Table error or missing values");
        }
    }


    protected function addField($field_array_or_string)
    {
        if (is_string($field_array_or_string)) {
            $this->fields[] = $field_array_or_string;
        } elseif (is_array($field_array_or_string)) {
            $this->fields = array_merge($this->fields, $field_array_or_string);
        }
    }


    protected function resetFields()
    {
        $this->fields = array();
    }


    protected function addTable($tables_array_or_string)
    {
        if (is_string($tables_array_or_string)) {
            $this->tables[] = $tables_array_or_string;
        } else if (is_array($tables_array_or_string)) {
            $this->tables = array_merge($this->tables, $tables_array_or_string);
        }
    }


    protected function resetTables()
    {
        $this->tables = array();
    }


    protected function buildSelectStatement()
    {
        if (!empty($this->tables)) {
            if (empty($this->fields)) $this->fields[] = '*';

            $output = "SELECT " . implode(", ", $this->fields) . " FROM " . implode(", ", $this->tables);
            $this->resetFields();
            $this->resetTables();
            return $output;
        } else {
            throw new Exception ("Can't build SQL statement. No selected table");
        }
    }


    protected function addJoin($table, $condition, $join_type = 'LEFT JOIN')
    {
        if ($table && $condition) {
            $this->joins[] = "$join_type $table ON $condition";
        } else {
            throw new Exception ("Can't add JOIN statement. Missing parameters");
        }
    }


    protected function resetJoins()
    {
        $this->joins = array();        // reinitialize clauses array;
    }


    protected function buildJoinStatement()
    {
        if (!empty($this->joins)) {
            $output = implode(" ", $this->joins);
            $this->resetJoins();
            return $output;
        } else {
            return '';
        }
    }


    protected function addFilter($field, $value, $clause = '=')
    {
        if ($field && isset($value) && $clause) {

            if (strtolower($value) == 'null' || strtolower($clause) == 'in' || strtolower($clause) == 'between') {
                $value = $value;
            } else {
                $db = $this->db;
                $value = "'" . $db->escape_str($value) . "'";
            }
            $this->filters[] = "$field $clause $value";
        } else {
            throw new Exception ("Can't add WHERE clause. Missing parameters");
        }
    }


    protected function addCustomFilter($custom_statement)
    {
        if ($custom_statement) {
            $this->filters[] = $custom_statement;
        } else {
            throw new Exception ("Can't add custom WHERE clause. Missing statement");
        }
    }


    protected function resetFilters()
    {
        $this->filters = array();        // reinitialize clauses array;
    }


    protected function buildWhereClause($logic = "AND")
    {
        switch (strtolower($logic)) {
            case "or":
                $logic = "OR";
                break;
            default:
                $logic = "AND";
        }

        if (!empty($this->filters)) {
            $output = "WHERE " . implode(" $logic ", $this->filters);
            $this->resetFilters();
            return $output;
        } else {
            return '';
        }
    }


    protected function addOrderBy($field, $order = null)
    {
        switch (strtolower($order)) {
            case 'desc':
                $order = 'DESC';
                break;
            case 'asc':
                $order = 'ASC';
                break;
            default:
                $order = '';
        }

        if ($field)
            $this->order_by[] = "$field $order";
    }


    protected function resetOrderBy()
    {
        $this->order_by = array();
    }


    protected function getOrderBy()
    {
        if (!empty($this->order_by)) {
            $output = "ORDER BY " . implode(', ', $this->order_by);
            $this->resetOrderBy();
            return $output;
        } else {
            return '';
        }
    }


    protected function setOffset($offset = 0)
    {
        $this->offset = intval($offset);
    }


    protected function resetOffset()
    {
        $this->offset = null;
    }


    protected function getOffset()
    {
        if ($this->offset) {
            $output = "OFFSET {$this -> offset}";
            $this->offset = 0;
            return $output;
        } else {
            return '';
        }
    }


    protected function setLimit($limit = 20)
    {
        $this->limit = intval($limit);
    }


    protected function resetLimit()
    {
        $this->limit = null;
    }


    protected function getLimit()
    {
        if ($this->limit) {
            $output = "LIMIT {$this -> limit}";
            $this->resetLimit();
            return $output;
        } else {
            return '';
        }
    }


    protected function convertConditionToOperand($condition, $value)
    {
        $db = $this->db;

        switch ($condition) {
            case 'contains':
                $value = "%" . $db->escape_str($value) . "%";
                $operand = 'ILIKE';
                break;
            case 'does not contain':
                $value = "%" . $db->escape_str($value) . "%";
                $operand = 'NOT LIKE';
                break;
            case 'equals':
            case 'is':
                $value = $db->escape_str($value);
                $operand = '=';
                break;
            case 'does not equal':
            case 'is not':
                $value = $db->escape_str($value);
                $operand = '!=';
                break;
            case 'is greater than':
            case 'is greater':
            case 'greater than':
                $value = $db->escape_str($value);
                $operand = '>';
                break;
            case 'is less than':
            case 'is less':
            case 'less than':
                $value = $db->escape_str($value);
                $operand = '<';
                break;
            case 'begins with':
                $value = $db->escape_str($value) . "%";
                $operand = 'ILIKE';
                break;
            case 'ends with':
                $value = "%" . $db->escape_str($value);
                $operand = 'ILIKE';
                break;
            case 'is null':
                $value = 'NULL';
                $operand = 'IS';
                break;
            case 'is not null':
                $value = 'NULL';
                $operand = 'IS NOT';
                break;

            case 'is between':
                if (is_array($value) && count($value) == 2) {
                    sort($value); // low to high
                    $value = "'" . $db->escape_str($value[0]) . "' AND '" . $db->escape_str($value[1]) . "'";
                    $operand = 'BETWEEN';
                } else {
                    throw new Exception ("A pair of values (array) is expected for 'is between' filter condition");
                }
                break;

            case 'is in':
                if (is_array($value) && count($value) > 0) {
                    $values_array = array();
                    foreach ($value as $element) $values_array[] = "'" . $db->escape_str($element) . "'";
                    $value = "(" . implode(',', $values_array) . ")";
                    $operand = 'IN';
                } else {
                    throw new Exception ("An array of values (array) is expected for 'is in' filter condition");
                }
                break;

            default:
                throw new Exception ("Can't recognize '$condition' as a filter condition");
        }

        if (is_array($value)) throw new Exception ("Value of type 'array' encountered for '$condition' condition");

        return ['operand' => $operand, 'value' => $value];
    }


    protected function quickInsert()
    {
        $db = $this->db;

        $sql = $this->buildInsertStatement();
        $result = $db->query($sql);

        return $result;
    }


    protected function quickUpdate($where_operator = 'AND')
    {
        $db = $this->db;

        $update_statement = $this->buildUpdateStatement();
        $where_clause = $this->buildWhereClause($where_operator);

        $sql = "$update_statement $where_clause";
        $result = $db->query($sql);

        return $result;
    }


    protected function quickSelect($where_operator = 'AND')
    {
        $db = $this->db;

        // Building query
        $select_statement = $this->buildSelectStatement();
        $join_statement = $this->buildJoinStatement();
        $where_clause = $this->buildWhereClause($where_operator);

        $order_by = $this->getOrderBy();
        $offset = $this->getOffset();
        $limit = $this->getLimit();

        $sql = "$select_statement $join_statement $where_clause $order_by $offset $limit";
        $query = $db->query($sql);
        $output = $query->result();

        return $output;
    }


    protected function buildQuery($where_operator = 'AND')
    {
        // Building query
        $select_statement = $this->buildSelectStatement();
        $join_statement = $this->buildJoinStatement();
        $where_clause = $this->buildWhereClause($where_operator);

        $order_by = $this->getOrderBy();
        $offset = $this->getOffset();
        $limit = $this->getLimit();

        $sql = "$select_statement $join_statement $where_clause $order_by $offset $limit";
        return $sql;
    }


    protected function validateTimestampOperand($operand)
    {
        switch ($operand) {
            case 'equals':
            case 'is':
            case 'does not equal':
            case 'is not':
            case 'is greater than':
            case 'is greater':
            case 'greater than':
            case 'is less than':
            case 'is less':
            case 'less than':
            case 'is null':
            case 'is not null':
            case 'is in':
            case 'is between':
                return $operand;
                break;

            default:
                throw new Exception("Operand '$operand' is incompatible with timestamps");
        }
    }

    protected function validateTimestamp($value)
    {
        if (is_array($value)) {
            $formatted_date = array(); // Init new array

            foreach ($value as $key => $timestamp) {
                if (!strtotime($timestamp))
                    throw new Exception ("Couldn't validate '$timestamp' as a timestamp format");

                $formatted_date[$key] = date("Y-m-d H:i:s", strtotime($timestamp));
            }
        } else {
            $formatted_date = date("Y-m-d H:i:s", strtotime($value));

            if (!$formatted_date)
                throw new Exception ("Couldn't validate '$value' as timestamp");
        }

        $value = $formatted_date;

        return $value;
    }


    protected function addTableDataFilter($field, $condition, $value)
    {
        if (!isset($field) || !isset($condition))
            throw new Exception ("Can't apply TableDataFilter. Missing parameters.");

        $filter = $this->convertConditionToOperand($condition, $value);

        $this->addFilter($field, $filter['value'], $filter['operand']);
    }


    protected function prepareJoins(array $joins_array)
    {
        foreach ((array)$joins_array as $table => $condition) {
            $this->addJoin($table, $condition);
        }
    }


}