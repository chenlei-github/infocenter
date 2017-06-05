<?php
class MysqlDb
{
    private $db;

    public function __construct($host, $user, $passwd, $dbname, $port = 3306)
    {
        $this->db = new mysqli($host, $user, $passwd, $dbname, $port);
    }

    public function errno()
    {
        return $this->db->connect_errno;
    }

    public function getError()
    {
        return $this->db->connect_error;
    }

    public function query($sql)
    {
        $result = null;
        try {
            $result = $this->db->query($sql);
        } catch (Exception $e) {

        }

        return $result;
    }

    public function fetchAll($sql)
    {
        $arr    = [];
        $result = $this->db->query($sql);
        if (!$result) {
            return null ;
        }
        while ($row = $result->fetch_assoc()) {
            $arr[] = $row;
        }

        return $arr;
    }

    public function fetchOne($sql)
    {
        $arr = null;
        try {
            $result = $this->db->query($sql);
            if ($result) {
                $arr = $result->fetch_assoc();
            }
        } catch (Exception $e) {
        }
        return $arr;
    }
}
