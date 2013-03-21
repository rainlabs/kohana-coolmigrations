    
<?php

defined('SYSPATH') or die('No direct script access.');

class Task_Db_Migrate extends Minion_Task {

    protected $_options = array(
        'db' => 'default',
        'step' => 'all'
    );
    protected $_time = 0;

    /**
     * Task to run pending migrations
     *
     * @return null
     */
    protected function _execute(array $params) {
        $migrations = new Coolmigrations(TRUE);
        Database::$default = $params['db'];
        $db_config = Kohana::$config->load('database');
        $this->db = Database::instance();

        try {
            $schema = '';
            if (isset($db_config[$params['db']]['schema']))
                $schema = $db_config[$params['db']]['schema'] . '.';
            $sql_check = "SELECT id FROM " . $schema . "migrations;";
            $this->db->query(Database::SELECT, $sql_check);
        } catch (Database_Exception $a) {
            /**
             * Get platform from database config
             */
            $platform = strtolower($db_config[$params['db']]['type']);

            /**
             * Get SQL from file for selected platform
             */
            $file = realpath(substr(__DIR__, 0, strlen(__DIR__) - 15) . 'sql/' . $platform . '.sql');
            $handle = fopen($file, 'rb');
            $sql_create = fread($handle, filesize($file));

            $this->db->query(0, $sql_create);
            $msg = Minion_CLI::color("-----------------------------\n", 'red');
            $msg .= Minion_CLI::color("| Migration table create!!! |\n", 'red');
            $msg .= Minion_CLI::color("-----------------------------\n", 'red');
            Minion_CLI::write($msg);
        }
        $model = ORM::factory('Migration');

        $messages = $migrations->migrate($params['db'], $params['step']);

        if (empty($messages)) {
            Minion_CLI::write("Nothing to migrate");
        } else {
            foreach ($messages as $message) {
                if (key($message) == 0) {
                    Minion_CLI::write($message[0]);
                } else {
                    Minion_CLI::write($message[key($message)]);
                    Minion_CLI::write("ERROR");
                }
            }
        }
    }

}