<?php

class Database
{
    private $host = 'localhost';
    private $username = 'root';
    private $password = '';
    private $db_name = 'contact';

    private $conn;

    public function getConnection()
    {
        $this->conn = null;
        try {
            $this->conn = new PDO("mysql:host=" .$this->host. ";dbname=".$this->db_name, $this->username, $this->password);
            $this->conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        } catch (PDOException $e) {
            echo "Connection Error: ".$e->getMessage();
        }
        return $this->conn;
    }
}
