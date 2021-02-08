<?php
namespace App\Helpers;
use PDO;

class Database extends PDO
{
    protected static $instances = array();

    /**
     * Static Method 
     * @param array $config
     * @return Database
     */
    public static function get($config)
    {
        //set variables for database connection
        $type = $config['db_type'];
        $host = $config['db_host'];
        $name = $config['db_name'];
        $user = $config['db_username'];
        $pass = $config['db_password'];

        //ID for database based on the config information
        $id = "$type.$host.$name.$user.$pass";

        //Check if we have already in instance
        if(isset(self::$instances[$id])) {
            return self::$instances[$id];
        }

        $instance = new Database("$type:host=$host;dbname=$name;charset=utf8", $user, $pass);
        $instance->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

        //Adding Database $instance to $instances to avoid duplication
        self::$instances[$id] = $instance;
        // return PDO instance
        return $instance;
    }

    /**
     * run rwa sql query
     * @param string $sql sql query
     */
    public function raw($sql)
    {
        return $this->query($sql);
    }

    /**
     * Method For selecting records from a database
     * @param string $sql sql command
     * @param array params for the statment
     * @param object $fetchMode
     * @param string $class class name
     * @return array an array of records
     */
    public function select($sql, $array = array(), $fetchMode = PDO::FETCH_OBJ, $class = '')
    {
        //Append a select if it doesn't exist
        if(strtolower(substr($sql, 0, 7)) !== 'select ') {
            $sql = "SELECT ".$sql;
        }

        $stmt = $this->prepare($sql);
        foreach($array as $key => $value) {
            if(is_int($value)) {
                $stmt->bindValue("$key", $value, PDO::PARAM_INT);
            }else{
                $stmt->bindValue("$key", $value);
            }
        }

        $stmt->execute();

        if($fetchMode === PDO::FETCH_CLASS) {
            return $stmt->fetchAll($fetchMode, $class);
        } else {
            return $stmt->fetchAll($fetchMode);
        }
    }

    /**
     * Insert Method
     * @param string $table table name
     * @param array $data array of columns and values
     */
    public function insert($table, $data) 
    {
        ksort($data);
        $fieldNames = implode(',',array_keys($data));
        $fieldValues = ':'.implode(', :', array_keys($data));
        
        $stmt = $this->prepare("INSERT INTO $table ($fieldNames) VALUES ($fieldValues)");

        foreach($data as $key => $value) {
            $stmt->bindValue(":$key", $value);
        }

        $stmt->execute();
        return $this->lastInsertId();

    }

    /**
     * update method
     * @param  string $table table name
     * @param  array $data  array of columns and values
     * @param  array $where array of columns and values
     */
    public function update($table, $data, $where)
    {
        ksort($data);

        $fieldDetails = null;
        foreach ($data as $key => $value) {
            $fieldDetails .= "$key = :$key,";
        }
        $fieldDetails = rtrim($fieldDetails, ',');

        $whereDetails = null;
        $i = 0;
        foreach ($where as $key => $value) {
            if ($i == 0) {
                $whereDetails .= "$key = :$key";
            } else {
                $whereDetails .= " AND $key = :$key";
            }
            $i++;
        }
        $whereDetails = ltrim($whereDetails, ' AND ');

        $stmt = $this->prepare("UPDATE $table SET $fieldDetails WHERE $whereDetails");

        foreach ($data as $key => $value) {
            $stmt->bindValue(":$key", $value);
        }

        foreach ($where as $key => $value) {
            $stmt->bindValue(":$key", $value);
        }

        $stmt->execute();
        return $stmt->rowCount();
    }

    /**
     * Delete method
     * @param  string $table table name
     * @param  array $data  array of columns and values
     * @param  array $where array of columns and values
     * @param  integer $limit limit number of records
     */
    public function delete($table, $where, $limit = 1)
    {
        ksort($where);

        $whereDetails = null;
        $i = 0;
        foreach ($where as $key => $value) {
            if ($i == 0) {
                $whereDetails .= "$key = :$key";
            } else {
                $whereDetails .= " AND $key = :$key";
            }
            $i++;
        }
        $whereDetails = ltrim($whereDetails, ' AND ');

        //if limit is a number use a limit on the query
        if (is_numeric($limit)) {
            $uselimit = "LIMIT $limit";
        }

        $stmt = $this->prepare("DELETE FROM $table WHERE $whereDetails $uselimit");

        foreach ($where as $key => $value) {
            $stmt->bindValue(":$key", $value);
        }

        $stmt->execute();
        return $stmt->rowCount();
    }

    /**
     * truncate table
     * @param  string $table table name
     */
    public function truncate($table)
    {
        return $this->exec("TRUNCATE TABLE $table");
    }
}