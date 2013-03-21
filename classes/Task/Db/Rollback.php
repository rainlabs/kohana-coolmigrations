    
<?php

defined('SYSPATH') or die('No direct script access.');

class Task_Db_Rollback extends Minion_Task {

    protected $_options = array(
        'db' => 'default',
        'step' => 1
    );

    /**
     * Task to rollback last executed migration
     *
     * @return null
     */
    protected function _execute(array $params) {
        $migrations = new Coolmigrations(TRUE);
        Database::$default = $params['db'];
        try {
            $model = ORM::factory('Migration');
        } catch (Database_Exception $a) {
            Minion_CLI::write('Cool Migrations is not installed. Please Run the migrations.sql script in your mysql server');
            exit();
        }

        $messages = $migrations->rollback($params['db'], $params['step']);

        if (empty($messages)) {
            Minion_CLI::write("There's no migration to rollback");
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