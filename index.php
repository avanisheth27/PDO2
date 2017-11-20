<?php
define('DATABASE', 'avs44');
define('USERNAME', 'avs44');
define('PASSWORD', '7Y9hAplQW');
define('CONNECTION', 'sql2.njit.edu');
class Manage {
    public static function autoload($class) {
        include $class . '.php';
    }
}
spl_autoload_register(array('Manage', 'autoload'));
$obj=new displayHtml;
$obj=new main();
class dbConn{
    protected static $db;
    private function __construct() {
        try {
            self::$db = new PDO( 'mysql:host=' . CONNECTION .';dbname=' . DATABASE, USERNAME, PASSWORD );
            self::$db->setAttribute( PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION );
        } catch (PDOException $e) {
        
            echo "Connection Error: " . $e->getMessage();
        }
    }
   
    public static function getConnection() {
      
        if (!self::$db) {
            //new connection object.
            new dbConn();
        }
        return self::$db;
    }
}
// Abstract class that handles collections
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
        $records =  $stmt->fetchAll();
        return $records;
    }
    // Function to find one record 
    static public function findOne($id) {
        $db = dbConn::getConnection();
        $tableName = get_called_class();
        $sql = 'SELECT * FROM ' . $tableName . ' WHERE id =' . $id;
        $stmt = $db->prepare($sql);
        $stmt->execute();
        $class = static::$modelName;
        $stmt->setFetchMode(PDO::FETCH_CLASS, $class);
        $records =  $stmt->fetchAll();
        return $records[0];
    }
}
// Collection for accounts table 
class accounts extends collection {
    protected static $modelName = 'account';
}
// Collection for todos table
class todos extends collection {
    protected static $modelName = 'todo';
}
// Abstract class for data model operations  
abstract class model {
    protected $tableName;
    // Function to save record
    public function save(){
        if ($this->id != '') {
            $sql = $this->update();
        } else {
           $sql = $this->insert();
        }
        $db = dbConn::getConnection();
        $stmt = $db->prepare($sql);
        $array = get_object_vars($this);
        foreach (array_flip($array) as $key=>$value){
            $stmt->bindParam(":$value", $this->$value);
        }
        $stmt->execute();
        $id = $db->lastInsertId();
        return $id;
    }
    // Function to insert new record
    private function insert() {      
        $modelName=get_called_class();
        $tableName = $modelName::getTablename();
        $array = get_object_vars($this);
        $columnString = implode(',', array_flip($array));
        $valueString = ':'.implode(',:', array_flip($array));
        $sql =  'INSERT INTO '.$tableName.' ('.$columnString.') VALUES ('.$valueString.')';
        return $sql;
    }
    // Function to update an exisiting record
    private function update() {  
        $modelName=get_called_class();
        $tableName = $modelName::getTablename();
        $array = get_object_vars($this);
        $comma = " ";
        $sql = 'UPDATE '.$tableName.' SET ';
        foreach ($array as $key=>$value){
            if( ! empty($value)) {
                $sql .= $comma . $key . ' = "'. $value .'"';
                $comma = ", ";
                }
            }
            $sql .= ' WHERE id='.$this->id;
        return $sql;
    }
    // Function to delete a record
    public function delete() {
        $db = dbConn::getConnection();
        $modelName=get_called_class();
        $tableName = $modelName::getTablename();
        $sql = 'DELETE FROM '.$tableName.' WHERE id ='.$this->id;
        $stmt = $db->prepare($sql);
        $stmt->execute();
    }
}
// Accounts table model
class account extends model {
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
class todo extends model {
    public $id;
    public $owneremail;
    public $ownerid;
    public $createddate;
    public $duedate;
    public $message;
    public $isdone;
    public static function getTablename(){
        $tableName='todos';
        return $tableName;
    }
} 
// main handler for executing queries and displaying results 
class main{
   public function __construct(){
    // ************** ACCOUNTS TABLE ***************
    // Finding all Records
    $HTMLtag = '<html>';
    $HTMLtag .= '<link href="https://fonts.googleapis.com/css?family=Roboto" rel="stylesheet">';
    $HTMLtag .= '<link rel="stylesheet" href="styles.css">';
    $HTMLtag .= '<body>'; 
    $HTMLtag .= '<h1>Accounts table</h2>';
    $records = accounts::findAll();
    $html = displayHtml::displayTable($records);
    $HTMLtag .='<left>'.$html.'</left>'; 
    // Finding single record 
    $id = 2;
    $records = accounts::findOne($id);
    $html = displayHtml::displayTableAlternate($records);
    $HTMLtag .= '<h2> 1) Display One Record</h2>';
    $HTMLtag .="<h3>Record with the id - ".$id."</h3>";
    $HTMLtag .= '<left>'.$html.'</left>';
    
    $HTMLtag .="<h2> 2) Insert Function</h2>";
    $record = new account();
    $record->email="pp333@njit.edu";
    $record->fname="palak";
    $record->lname="patel";
    $record->phone="3333333";
    $record->birthday="10-1-2007";
    $record->gender="female";
    $record->password="5555";
    $lstId=$record->save();
    $records = accounts::findAll();
    $HTMLtag .="<h3> New record inserted - ".$lstId."</h3>";
    $html = displayHtml::displayTable($records);
    $HTMLtag .='<left>'.$html.'</left>';
    
    $HTMLtag .= "<h2> 3) Update Function</h2>";
    $records = accounts::findOne($lstId);
    $record = new account();
    $record->id=$records->id;
    $record->email="palakpatel@njit.edu";
    $record->fname="ppppp";
    $record->lname="p2";
    $record->gender="UPDATED";
    $record->save();
    $records = accounts::findAll();
    $HTMLtag .="<h3>Updating the record with the following id: ".$lstId."</h3>";
    $html = displayHtml::displayTable($records);
    $HTMLtag .='<left>'.$html.'</left>';
    
    $HTMLtag .= "<h2> 4) Delete a Record</h2>";
    $records = accounts::findOne($lstId);
    $record= new account();
    $record->id=$records->id;
    $records->delete();
    $HTMLtag .='<h3>Record with the id: '.$records->id.' has been deleted</h3>';
    $records = accounts::findAll();
    $html = displayHtml::displayTable($records);
    $HTMLtag .='<h3>After record has been deleteds</h3>';
    $HTMLtag .='<left>'.$html.'</left><br><hr>';
    // ************** TODOS TABLE ***************
    // Finding all records 
    $HTMLtag .= '<h1>Todos Table</h1>';
    $HTMLtag .= '<h3> 1) Display All Records</h3>';
    $records = todos::findAll();
    $html = displayHtml::displayTable($records); 
    $HTMLtag .='<left>'.$html.'</left><hr>';
    // Finding one record
    $id = 1;
    $records = todos::findOne($id);
    $html = displayHtml::displayTableAlternate($records);
    $HTMLtag .='<h2>2) Display one Record</h2>';
    $HTMLtag .='<h3> Record with the following id: '.$id.'</h3>';
    $HTMLtag .='<left>'.$html.'</left><hr>';
    // Inserting a record
    $HTMLtag .="<h2> 3) Insert Function</h2>";
    $record = new todo();
    $record->owneremail="nan@njit.edu";
    $record->ownerid=55;
    $record->createddate="1-05-1999";
    $record->duedate="5-06-2011";
    $record->message="DONE";
    $record->isdone=1;
    $lstId=$record->save();
    $records = todos::findAll();
    $HTMLtag .="<h3>Record inserted id - ".$lstId."</h3>";
    $html = displayHtml::displayTable($records);
    $HTMLtag .='<left>'.$html.'</left><hr>';
    // Updating a record
    $HTMLtag .="<h3> 4) Update Function</h3>";
    $records = todos::findOne($lstId);
    $record = new todo();
    $record->id=$records->id;
    $record->owneremail="Nandan@njit.edu";
    $record->message="CHANGED";
    $record->save();
    $records = todos::findAll();
    $HTMLtag .="<h3>Updating a record with the id: ".$lstId."</h3>";
    $html = displayHtml::displayTable($records);
    $HTMLtag .='<left>'.$html.'</left><hr>';
    // Delete a record
    $HTMLtag .= "<h2> 5) Delete Function</h2>";
    $records = todos::findOne($lstId);
    $record= new todo();
    $record->id=$records->id;
    $records->delete();
    $HTMLtag .='<h3>Record with the id: '.$records->id.' has been deleted</h3>';
    $records = todos::findAll();
    $html = displayHtml::displayTable($records);
    $HTMLtag .="<h4>The Row has been Deleted , Table :</h4>";
    $HTMLtag .='<left>'.$html.'</left><hr>';
    $HTMLtag .='</body></html>';
    print_r($HTMLtag);
    }
}
?>
