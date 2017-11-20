<?php
ini_set('display_errors', 'On');
error_reporting(E_ALL);
define('DATABASE', 'avs44');
define('USERNAME', 'avs44');
define('PASSWORD', 'DNcqRhRg5');
define('CONNECTION', 'sql2.njit.edu');
class dbConn
{
       protected static $db;
       private function __construct()
         {
            try
              {
                self::$db = new PDO('mysql:host=' . CONNECTION .';dbname=' .DATABASE, USERNAME, PASSWORD );

                self::$db->setAttribute( PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION );
                
              }
            catch (PDOException $e)
              {
                
                echo "Connection Error: " . $e->getMessage();
              }
         }
       public static function getConnection()
         {
            if (!self::$db)
             {
              new dbConn();
             }
             return self::$db;
         } 
}
abstract class collection {
    protected $html;
    // Function to create model
    static public function create() {
        $model = new static::$modelName;
        return $model;
    }
    // Function to find all records
    static public function findAll() {
        $db = dbConn::getConnection();
        $tableName = get_called_class();
        $sql = 'SELECT * FROM ' . $tableName;
        $stmt = $db->prepare($sql);
        $stmt->execute();
        $class = static::$modelName;
        $stmt->setFetchMode(PDO::FETCH_CLASS, $class);
        $record =  $stmt->fetchAll();
        return $record;
    }

?>