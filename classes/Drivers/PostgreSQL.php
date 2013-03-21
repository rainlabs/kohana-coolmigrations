<?php

defined('SYSPATH') or die('No direct script access.');

class Drivers_PostgreSQL extends Drivers_Driver {

    protected $schema = 'public';

    public function __construct($group, $db) {
        parent::__construct($group, $db);

        $db_config = Kohana::$config->load('database');
        if (isset($db_config[$group]['schema']))
            $this->schema = $db_config[$group]['schema'];

        $this->begin();
    }

    public function __destruct() {
        $this->commit();
    }

    /**
     * Start transaction
     * 
     */
    public function begin() {
        $this->run_query('BEGIN');
    }

    /**
     * Set schema name or 'public' if $schema_name is null
     * @param type $schema_name
     */
    public function set_schema($schema_name = NULL) {
        if ($schema_name == NULL)
            $this->schema = 'public';
        else
            $this->schema = $schema_name;
    }

    /**
     * Create schema (Postgres function)
     * @param type $schema_name
     * @return boolean
     */
    public function create_schema($schema_name) {
        $sql = "CREATE SCHEMA $schema_name;";
        return $this->run_query($sql);
    }

    public function create_table($table_name, $fields, $primary_key = TRUE) {
        $sql = "CREATE TABLE $this->schema.$table_name (";

        // add a default id column if we don't say not to
        if ($primary_key === TRUE) {
            $fields = array_merge(array($this->primary_key => 'primary_key'), $fields);
        }

        foreach ($fields as $field_name => $params) {
            $params = (array) $params;

            $sql .= $this->compile_column($field_name, $params, 'PostgreSQL');
            $sql .= ",";
        }
        $sql = rtrim($sql, ',') . ")";

        return $this->run_query($sql);
    }

    public function drop_table($table_name) {
        return $this->run_query("DROP TABLE $this->schema.$table_name");
    }

    public function rename_table($old_name, $new_name) {
        $sql = "ALTER TABLE $this->schema.$old_name RENAME TO $new_name";
        return $this->run_query($sql);
    }

    public function add_column($table_name, $column_name, $params) {
        $sql = "ALTER TABLE $this->schema.$table_name ADD COLUMN " . $this->compile_column($column_name, $params, 'PostgreSQL');
        return $this->run_query($sql);
    }

    public function rename_column($table_name, $column_name, $new_column_name, $params = NULL) {
        $sql = "ALTER TABLE $this->schema.$table_name RENAME COLUMN $column_name TO $new_column_name";
        return $this->run_query($sql);
    }

    /**
     * !!!Crashed method!!!
     * TODO: rework!
     * 
     * @param type $table_name
     * @param type $column_name
     * @param type $params
     * @return type
     */
    public function change_column($table_name, $column_name, $params) {
        $sql = "ALTER TABLE $this->schema.$table_name ALTER COLUMN " . $this->compile_column($column_name, $params, 'PostgreSQL');
        return $this->run_query($sql);
    }

    public function remove_column($table_name, $column_name) {
        return $this->run_query("ALTER TABLE $this->schema.$table_name DROP COLUMN $column_name ;");
    }

    public function add_index($table_name, $index_name, $columns, $index_type = 'normal') {
        switch ($index_type) {
            case 'normal': $type = '';
                break;
            case 'unique': $type = 'UNIQUE';
                break;

            default: throw new Exception('migrations.bad_index_type', $index_type);
        }

        $sql = "CREATE $type INDEX $index_name ON $this->schema.$table_name (";

        foreach ((array) $columns as $column) {
            $sql .= " $column,";
        }

        $sql = rtrim($sql, ',');
        $sql .= ')';
        return $this->run_query($sql);
    }

    public function remove_index($table_name, $index_name) {
        return $this->run_query("DROP INDEX $index_name");
    }

    public function belongs_to($from_table, $to_table, $from_column = NULL, $to_column = NULL) {
        if ($to_column === NULL)
            $to_column = $this->primary_key;
        if ($from_column === NULL)
            $from_column = $to_table . '_' . $to_column;
        $constraint = 'fk_' . $from_column;
        // get column information from $to_column
        // add column
        $sql = "ALTER TABLE $from_table ADD CONSTRAINT $constraint FOREIGN KEY ($from_column) REFERENCES $to_table ($to_column) MATCH FULL;";
        return $this->run_query($sql);
    }

}