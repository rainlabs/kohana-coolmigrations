<?php

defined('SYSPATH') or die('No direct script access.');

/**
 * Migrations
 *
 * An open source utility for s/Code Igniter/Kohana inspired by Ruby on Rails
 *
 * Note: This is a work in progress. Merely a wrapper for all the currently
 * existing DBUtil class, and a CI adaptation of all the RoR conterparts.
 * many of the methods in this helper might not function properly in some DB
 * engines and other are not yet finished developing.
 * This helper is being released as a complement for the Migrations utility.
 *
 * Reworked for Kohana by Jamie Madill
 *
 * @package		Migrations
 * @author      Vladimir Zyablitskiy
 * @author		Matías Montes
 * @author      Jamie Madill
 */
class Migration {

    protected $driver;
    protected $db;
    // if use change method is true
    protected $change_exists = false;
    // Override these two parameters to set behaviour of your migration
    private $group = 'default';

    public function __construct($group = 'default', $change_exists = false) {
        $this->db = Database::instance($group);
        $this->change_exists = $change_exists;
        $db_config = Kohana::$config->load('database');

        // if need call driver with specific name
        $platform = strtolower($db_config[$group]['type']);
        switch ($platform) {
            case 'mysql':
            case 'mysqli':
                $platform = 'MySQL';
                break;
            case 'postgresql':
                $platform = 'PostgreSQL';
                break;
        }

        // Set driver name
        $driver = 'Drivers_' . $platform;

        $this->driver = new $driver($group, $this->db);
        $this->group = $group;
    }

    public function up() {
        if ($this->change_exists) {
            $this->change_exists = !$this->change_exists;
            return $this->change();
        }
        throw new Kohana_Exception('migrations.abstract');
    }

    public function down() {
        if ($this->change_exists)
            return $this->change();
        throw new Kohana_Exception('migrations.abstract');
    }

    /**
     * up/down function if possible
     * @throws Kohana_Exception
     */
    public function change() {
        throw new Kohana_Exception('migrations.abstract');
    }

    private function change_exception() {
        try {
            $this->change_exists;
        } catch (Database_Exception $a) {
            Minion_CLI::write('Method "change" not supported for this action');
            exit();
        }
    }

    /**
     * Create Table
     *
     * Creates a new table
     *
     * $fields:
     *
     * 		Associative array containing the name of the field as a key and the
     * 		value could be either a string indicating the type of the field, or an
     * 		array containing the field type at the first position and any optional
     * 		arguments the field might require in the remaining positions.
     * 		Refer to the TYPES function for valid type arguments.
     * 		Refer to the FIELD_ARGUMENTS function for valid optional arguments for a
     * 		field.
     *
     * @example
     *
     * 		create_table (
     * 			'blog',
     * 			array (
     * 				'title' => array ( 'string[50]', default => "The blog's title." ),
     * 				'date' => 'date',
     * 				'content' => 'text'
     * 			),
     * 		)
     *
     * @param	string   Name of the table to be created
     * @param	array
     * @param	mixed    Primary key, false if not desired, not specified sets to 'id' column.
     *                   Will be set to auto_increment, serial, etc.
     * @return	boolean
     */
    public function create_table($table_name, $fields, $primary_key = TRUE) {
        if (!$this->change_exists)
            $ret = $this->driver->create_table($table_name, $fields, $primary_key);
        else {
            $this->change_exists = !$this->change_exists;
            $ret = $this->drop_table($table_name);
        }
        return $ret;
    }

    /**
     * Drop a table
     *
     * @param string    Name of the table
     * @return boolean
     */
    public function drop_table($table_name) {
        $this->change_exception();
        $ret = $this->driver->drop_table($table_name);
        return $ret;
    }

    /**
     * Rename a table
     *
     * @param   string    Old table name
     * @param   string    New name
     * @return  boolean
     */
    public function rename_table($old_name, $new_name) {
        if (!$this->change_exists)
            $ret = $this->driver->rename_table($old_name, $new_name);
        else {
            $this->change_exists = !$this->change_exists;
            $ret = $this->driver->rename_table($new_name, $old_name);
        }
        return $ret;
    }

    /**
     * Add a column to a table
     *
     * @example add_column ( "the_table", "the_field", array('string', 'limit[25]', 'not_null') );
     * @example add_coumnn ( "the_table", "int_field", "integer" );
     *
     * @param   string  Name of the table
     * @param   string  Name of the column
     * @param   array   Column arguments array
     * @return  bool
     */
    public function add_column($table_name, $column_name, $params) {
        if (!$this->change_exists)
            $ret = $this->driver->add_column($table_name, $column_name, $params);
        else {
            $this->change_exists = !$this->change_exists;
            $ret = $this->driver->remove_column($table_name, $column_name);
        }
        return $ret;
    }

    /**
     * Rename a column
     *
     * @param   string  Name of the table
     * @param   string  Name of the column
     * @param   string  New name
     * @return  bool
     */
    public function rename_column($table_name, $column_name, $new_column_name, $params = NULL) {
        if (!$this->change_exists)
            $ret = $this->driver->rename_column($table_name, $column_name, $new_column_name, $params);
        else {
            $this->change_exists = !$this->change_exists;
            $ret = $this->driver->rename_column($table_name, $new_column_name, $column_name, $params);
        }
        return $ret;
    }

    /**
     * Alter a column
     *
     * @param   string  Table name
     * @param   string  Columnn ame
     * @param   array   Column arguments
     * @return  bool
     */
    public function change_column($table_name, $column_name, $params) {
        $this->change_exception();
        $ret = $this->driver->change_column($table_name, $column_name, $params);
        return $ret;
    }

    /**
     * Remove a column from a table
     *
     * @param   string  Name of the table
     * @param   string  Name of the column
     * @return  bool
     */
    public function remove_column($table_name, $column_name) {
        $this->change_exception();
        $ret = $this->driver->remove_column($table_name, $column_name);
        return $ret;
    }

    /**
     * Add an index
     *
     * @param   string  Name of the table
     * @param   string  Name of the index
     * @param   string|array  Name(s) of the column(s)
     * @param   string  Type of the index (unique/normal/primary)
     * @return  bool
     */
    public function add_index($table_name, $index_name, $columns, $index_type = 'normal') {
        if (!$this->change_exists)
            $ret = $this->driver->add_index($table_name, $index_name, $columns, $index_type);
        else {
            $this->change_exists = !$this->change_exists;
            $ret = $this->driver->remove_index($table_name, $index_name);
        }
        return $ret;
    }

    /**
     * Remove an index
     *
     * @param   string  Name of the table
     * @param   string  Name of the index
     * @return  bool
     */
    public function remove_index($table_name, $index_name = NULL) {
        $this->change_exception();
        $ret = $this->driver->remove_index($table_name, $index_name);
        return $ret;
    }

    /**
     * Add foreign key
     * 
     * @param string $from_table
     * @param string $to_table
     * @param string $to_column if NULL then is primary_key
     * @param string $from_column if NULL then is fk_#{$to_table}_#{$to_column}
     * @return bool
     */
    public function belongs_to($from_table, $to_table, $to_column = NULL, $from_column = NULL) {
        if (!$this->change_exists)
            $ret = $this->driver->belongs_to($from_table, $to_table, $to_column, $from_column);
        else {
            $constraint = 'fk_' . $to_table . '_' . $from_column;
            $ret = $this->driver->remove_index($from_table, $constraint);
        }
        return $ret;
    }

    /**
     * Add foreign key - reversive belongs_to
     * 
     * @param string $from_table
     * @param string $to_table
     * @param string $from_column
     * @param string $to_column
     * @return bool
     */
    public function has_one($from_table, $to_table, $from_column = NULL, $to_column = NULL) {
        $ret = $this->driver->has_one($from_table, $to_table, $from_column, $to_column);
        return $ret;
    }

    /**
     * Add foreign key trough table
     * 
     * @param string $from_table
     * @param string $to_table
     * @param string $trough_table
     * @return bool
     */
    public function has_one_trough($from_table, $to_table, $trough_table) {
        $this->change_exception();
        $ret = $this->driver->has_one_trough($from_table, $to_table, $trough_table);
        return $ret;
        // remove columns $from_table and $trough_table
    }

    /**
     * Add foreign keys, Multi-Multi trough $trough_table
     * 
     * @param string $from_table
     * @param string $to_table
     * @param string $trough_table
     * @return bool
     */
    public function has_many_trough($from_table, $to_table, $trough_table) {
        $this->change_exception();
        $ret = $this->driver->has_many_trough($from_table, $to_table, $trough_table);
        return $ret;
        // remove columns $trough_table
    }

    /**
     * Add foreign keys, Multi-Multi trough new table, will be created
     * 
     * @param string $from_table
     * @param string $to_table
     * @return bool
     */
    public function has_many($from_table, $to_table) {
        $this->change_exception();
        $ret = $this->driver->has_many($from_table, $to_table);
        return $ret;
        // drop table
    }

    /**
     * Run SQL query
     * 
     * @param string SQL-string
     * @return bool
     */
    public function sql($query) {
        return $this->driver->run_query($query);
    }

    public function commit() {
        $this->driver->commit();
    }

}