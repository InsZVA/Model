<?php

/**
 * Created by PhpStorm.
 * User: inszva
 * Date: 15-11-3
 * Time: 下午9:31
 */

require_once dirname(__FILE__).'/../config/database.php';

class Model
{
    protected $db = [];
    protected $mysqli = null;
    protected $tableName = '';

    /**
     *构造函数，变量初始化，连接MySQL
     * @param $tableName
     */
    public function __construct($tableName)
    {
        global $db;
        $this->db = $db;
        $this->tableName = $tableName;
        $this->mysqli = new MySQLi($db['address'],$db['userName'],$db['passWord'],$db['dbName']);
        $this->mysqli->query("set names 'utf8'");
    }

    /**
     *析构函数，关闭MySQL连接
     */
    public function __destruct()
    {
        $this->mysqli->close();
    }

    /**
     * @param Array $array
     * @param bool $debug
     * @return bool $result
     */
    public function insert($array,$debug = false)
    {
        $result = $this->mysqli->query("show table status like '$this->tableName'");
        $row = $result->fetch_assoc();
        $newId = $row['Auto_increment'];
        $query = "insert into $this->tableName(";
        $i = 0;
        foreach($array as $key => $value)
        {
            if($i != 0)$query .= ",";
            $query .= "`$key`";
            $i++;
        }
        $query .= ") values (";
        $i = 0;
        foreach($array as $key => $value)
        {
            if($i != 0)$query .= ",";
            if(is_string($value))$query .= "'$value'";
            else $query .= "$value";
            $i++;
        }
        $query .= ")";
        if($debug)echo $query;
        $result = $this->mysqli->query($query);
        return $result ? $newId : $result;//boolean
    }

    /**
     * @param $fields
     * @param string $where
     * @param string $limit
     * @param string $order
     * @return array|bool
     */
    public function getList($fields,$where = '',$limit = '',$order = '',$debug = false)
    {
        $query = "select ";
        $i = 0;
        if(is_array($fields))
            foreach($fields as $value)
            {
                if($i != 0)$query .= ",";
                $query .= $value;
                $i++;
            }
        else $query .= $fields;
        $query .= " from $this->tableName";
        if($where != '')$query .=" where $where";
        if($order != '')$query .=" order by $order";
        if($limit != '')$query .=" limit $limit";
        if($debug)echo $query;
        $result = $this->mysqli->query($query);
        $rows = [];
        if($result)
        {
            while($row = $result->fetch_assoc())
                $rows[] = $row;
            return $rows;
        }
        else return false;
    }

    /**
     * @param string $where
     * @return mixed
     */
    public function getCount($where = '')
    {
        $query = "select count(*) from $this->tableName";
        if($where != '')$query .=" where $where";
        $result = $this->mysqli->query($query);
        $row = $result->fetch_array();
        return $row[0];
    }

    /**
     * @param string $where
     * @param $newData
     * @param bool $debug
     * @return bool|mysqli_result
     */
    public function edit($where = '',$newData,$debug = false)
    {
        $query = "update $this->tableName set ";
        $i = 0;
        if(count($newData) == 0 || !is_array($newData))return false;
        foreach($newData as $key => $value)
        {
            if($i != 0)$query .= ",";
            if(is_string($value))$query .= "`$key` = '$value'";
            else $query .= "`$key` = '$value'";
            $i++;
        }
        if($where != '')$query .=" where $where";
        if($debug)echo $query;
        $result = $this->mysqli->query($query);
        return $result;
    }

    public function remove($where)
    {
        $query = "delete from $this->tableName where $where";
        $result = $this->mysqli->query($query);
        return $result;
    }
}
