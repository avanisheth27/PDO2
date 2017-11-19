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
                 echo "<b>Connected successfully</b>";
                 echo "<br><hr>";
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
?>