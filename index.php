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
abstract class collection 
{
    protected $html;
    
    static public function create() 
    {
        $model = new static::$modelName;
        return $model;
    }
    
    static public function findAll() 
    {
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

     static public function findOne($id) 
     {
        $db = dbConn::getConnection();
        $tableName = get_called_class();
        $sql = 'SELECT * FROM ' . $tableName . ' WHERE id =' . $id;
        $stmt = $db->prepare($sql);
        $stmt->execute();
        $class = static::$modelName;
        $stmt->setFetchMode(PDO::FETCH_CLASS, $class);
        $record =  $stmt->fetchAll();
        return $record[0];
    }
}
//accounts table
class accounts extends collection 
{
    protected static $modelName = 'account';
}
//todos table
class todos extends collection 
{
    protected static $modelName = 'todo';
}
abstract class model 
{
    protected $tableName;
    // Function to save record
   public function save()
    {
        if ($this->id != '') 
        {
            $sql = $this->update();
        } else
         {
           $sql = $this->insert();
         }
        $db = dbConn::getConnection();
        $stmt = $db->prepare($sql);
        $array = get_object_vars($this);
        foreach (array_flip($array) as $key=>$value)
        {
            $stmt->bindParam(":$value", $this->$value);
        }
        $stmt->execute();
        $id = $db->lastInsertId();
      return $id;
    }

    private function insert() 
    {      
        $modelName=get_called_class();
        $tableName = $modelName::getTablename();
        $array = get_object_vars($this);
        $columnString = implode(',', array_flip($array));
        $valueString = ':'.implode(',:', array_flip($array));
        $sql =  'INSERT INTO '.$tableName.' ('.$columnString.') VALUES ('.$valueString.')';
        return $sql;
    }
class account extends model 
{
    public $id;
    public $email;
    public $fname;
    public $lname;
    public $phone;
    public $birthday;
    public $gender;
    public $password;
    public static function getTablename(){
        $tableName='accounts';
        return $tableName;
    }
}
// Todos table model
class todo extends model 
{
    public $id;
    public $owneremail;
    public $ownerid;
    public $createddate;
    public $duedate;
    public $message;
    public $isdone;
    public static function getTablename()
    {
        $tableName='todos';
        return $tableName;
    }
} 

class main{
   public function __construct()
   {
    // ACCOUNTS TABLE 
    // Finding all Records
    $HTMLtag = '<html>';
    $HTMLtag .= '<link href="https://fonts.googleapis.com/css?family=Roboto" rel="stylesheet">';
    $HTMLtag .= '<link rel="stylesheet" href="styles.css">';
    $HTMLtag .= '<body>'; 
    $HTMLtag .= '<h1>Accounts table</h2>';
    $HTMLtag .= '<h2>1) Display All Records</h2>';
    $records = accounts::findAll();
    $html = displayHtml::displayTable($records);
    $HTMLtag .='<left>'.$html.'</left><hr>'; 
    // Finding single record 
    $id = 2;
    $records = accounts::findOne($id);
    $html = displayHtml::displayTableAlternate($records);
    $HTMLtag .= '<h2> 2) Display One Record</h2>';
    $HTMLtag .="<h3>Record fetched with the following id - ".$id."</h3>";
    $HTMLtag .= '<left>'.$html.'</left><hr>';
    print_r($HTMLtag);


  }
}
}
?>