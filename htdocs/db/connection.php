<?php

class DBConnectionSingleton
{
    private static $con = null;
    public static function get_connection()
    {
        if (!self::$con) {
            try {
                require('../config.php');
                self::$con = new PDO(
                    sprintf(
                        'mysql:host=%s;dbname=%s;charset=utf8',
                        $config['db_host'],
                        $config['db_database']
                    ),
                    $config['db_username'],
                    $config['db_password'],
                    array(
                        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                        PDO::ATTR_PERSISTENT => false
                    )
                );
            } catch (PDOException $e) {
                echo $e->getMessage();
            }
        }
        return self::$con;
    }
};
