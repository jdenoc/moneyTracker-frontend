<?php
/**
 * Author: Denis O'Connor
 * Last Modified: 13-OCT-2012
 */
class pdo_connection{

    private $_db;
    private $_debug;
    private $_error = "DB_ERROR:";

    public function __construct($db_name, $debug=false){
        $config_file = __DIR__ . '/config.db.php';
        if(!file_exists($config_file)){
            error_log($this->_error.' Config file not found', 0);
            return 0;
        }
        $db_config = @include_once($config_file);
        $this->_db = new PDO(
            "mysql:host=".$db_config['db_host'].";dbname=".$db_name,
            $db_config['db_username'],
            $db_config['db_password']
        );
        $this->_db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        $this->_db->setAttribute(PDO::ATTR_EMULATE_PREPARES, false);
        $this->_debug = $debug;
        return 1;
    }

    public function exec($stmt){
        $this->debugMode($stmt, array());
        return $this->_db->exec($stmt);
    }

    public function getAllRows($stmt, $bind=array()){
        $query = $this->_db->prepare($stmt);
        foreach($bind as $key=>$item){
            $query->bindValue(':'.$key, $item, PDO::PARAM_STR);
        }

        $this->debugMode($stmt, $bind);
        $query->execute();
        return $query->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * @param $stmt string      SQL statement
     * @param array $bind       Array of values to bind to SQL statement
     * @return string|false     The value result from the SQL statement | FALSE if nothing found
     */
    public function getValue($stmt, $bind=array()){
        $query = $this->_db->prepare($stmt);
        /*** bind the paramaters ***/
        foreach($bind as $key=>$item){
            $query->bindParam(':'.$key, $item, PDO::PARAM_STR);
        }

        $this->debugMode($stmt, $bind);
        $query->execute();
        return $query->fetchColumn();
    }

    public function getAllValues($stmt, $bind=array()){
        $query = $this->_db->prepare($stmt);
        $array = array();
        /*** bind the paramaters ***/
        foreach($bind as $key=>$item){
            $query->bindParam(':'.$key, $item, PDO::PARAM_STR);
        }
        $this->debugMode($stmt, $bind);
        $query->execute();

        $rows = $query->rowCount();
        for($i=0; $i<$rows; $i++){
            $array[] = $query->fetchColumn();
        }
        return $array;
    }

    public function getRow($stmt, $bind=array()){
        $query = $this->_db->prepare($stmt);

        /*** bind the paramaters ***/
        foreach($bind as $key=>$item){
            $query->bindValue(':'.$key, $item, PDO::PARAM_STR);
        }

        $this->debugMode($stmt, $bind);
        $query->execute();
        return $query->fetch(PDO::FETCH_ASSOC);
    }

    /**
     * @param string $tbl_name      SQL table
     * @param array $array          Array of key value pairs to insert, where the key is the column name
     * @return int                  id of the most recent insert
     */
    public function insert($tbl_name, $array=array()){
        $values = '';
        foreach($array as $key=>$value){
            $value = (get_magic_quotes_gpc())? $value : addslashes($value);
            $values .= " $key='$value',";
        }
        $values = substr($values, 0, strlen($values)-1);
        $stmt = "INSERT INTO $tbl_name SET ".$values;

        $this->debugMode($stmt, array());
        $this->_db->exec($stmt);
        return $this->_db->lastInsertId();
    }

    public function update($tbl_name, $array=array(), $whereString, $whereArray=array()){
        $values = '';
        foreach($array as $key=>$value){
            $value = (get_magic_quotes_gpc())? $value : addslashes($value);
            $values .= " $key='$value',";
        }
        $values = substr($values, 0, strlen($values)-1);

        $stmt = "UPDATE ".$tbl_name." SET ".$values." WHERE ".$whereString;
        $query = $this->_db->prepare($stmt);

        /*** bind the paramaters ***/
        foreach($whereArray as $key=>$item){
            $query->bindValue(':'.$key, $item, PDO::PARAM_STR);
        }

        $this->debugMode($stmt, $whereArray);
        $result = $query->execute();
        return $result;
    }

    public function delete($tbl_name, $whereString, $whereArray=array()){
        $stmt = "DELETE FROM ".$tbl_name." WHERE ".$whereString;
        $query = $this->_db->prepare($stmt);

        /*** bind the paramaters ***/
        foreach($whereArray as $key=>$item){
            $query->bindValue(':'.$key, $item, PDO::PARAM_STR);
        }

        $this->debugMode($stmt, $whereArray);
        $result = $query->execute();
        return $result;
    }

    /**
     * @param string $tbl_name
     * @param string $whereString
     * @param array $whereArray
     * @return int
     */
    public function count($tbl_name, $whereString, $whereArray=array()){
        $stmt = "SELECT * FROM ".$tbl_name." WHERE ".$whereString;
        $query = $this->_db->prepare($stmt);

        /*** bind the paramaters ***/
        foreach($whereArray as $key=>$item){
            $query->bindValue(':'.$key, $item, PDO::PARAM_STR);
        }

        $this->debugMode($stmt, $whereArray);
        $query->execute();
        return $query->rowCount();
    }

    /**
     * @param string $stmt             SQL statement
     * @param array $whereArray
     */
    public function debugMode($stmt, $whereArray){
        if($this->_debug){
            echo "[statement:]".$stmt."<br/>\r\n<pre>";
            var_dump($whereArray);
            echo "</pre>";
            echo "[example_query:]".str_replace(':', '', str_replace(array_keys($whereArray), $whereArray, $stmt));
        }
    }

    /**
     * Looks up database columns of tables that are either of type "enum" or type "set". It then returns those pre-defined values as an array, for you to do with as you wish.
     * @param string $table         Name of table that enum/set is in
     * @param string $field_name    the column name
     * @param string $type          default = "enum". Should be set to either "enum" or "set"
     * @return array
     */
    public function getEnumOrSetArray($table, $field_name, $type='enum'){
        $col_details = $this->getRow("SHOW COLUMNS FROM ".$table." WHERE Field=:field_name", array('field_name'=>$field_name));
        $field_values = explode(",", str_replace("'", "", substr($col_details['Type'], strlen($type)+1, (strlen($col_details['Type'])-(strlen($type)+2)))));
        return $field_values;
    }

    public function closeConnection(){
        $this->_db = null;
        $this->_debug = null;
    }
}