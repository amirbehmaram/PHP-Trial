<?php
namespace NobleStudios\php_trials\classes;

define('SERVER', '127.0.0.1');
define('USERNAME', 'user');
define('PASSWORD', 'password');

/*
 * DB Connect singleton to access the db connection from anywhere is user management
*/
class DBConnect
{
    private static $instance;
    private $connection;

    private function __construct()
    {
        $this->connection = new \mysqli(SERVER,USERNAME,PASSWORD);
    }

    public static function init()
    {
        if(is_null(self::$instance)) {
            self::$instance = new DBConnect();
        }

        return self::$instance;
    }

    public function __call($name, $args)
    {
        if(method_exists($this->connection, $name)){
             return call_user_func_array(array($this->connection, $name), $args);
        } else {
             trigger_error('Unknown Method ' . $name . '()', E_USER_WARNING);
             return false;
        }
    }
}
