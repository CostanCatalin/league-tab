<?php
namespace LeagueTab;

use Dotenv\Dotenv;
use PDO;

class DatabaseConnection {
    static private $connection;

    function __construct(){
        $dotenv = new Dotenv(__DIR__ . '/..');
        $dotenv->load();

        $host     = getenv('DB_HOST');
        $database = getenv('DB_DATABASE');
        $port     = getenv('DB_PORT');
        $username = getenv('DB_USERNAME');
        $password = getenv('DB_PASSWORD');

        try{
            self::$connection = new PDO("mysql:host=$host;dbname=$database;port=$port", $username, $password);
        }catch (\Exception $e){
            echo 'Connection failed: '. $e->getMessage();
        }
    }

    protected function queryList($sql, $args, $one = false){
        $stmt = self::$connection->prepare($sql);

        $stmt->execute($args);

        if ($one) {
            return $stmt->fetch(PDO::FETCH_ASSOC);
        }

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}