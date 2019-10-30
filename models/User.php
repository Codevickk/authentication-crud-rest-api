<?php
class User
{
    private $conn;
    private $table_name = 'users';
    
    public $user_id;
    public $username;
    public $password;

    public function __construct($db)
    {
        $this->conn = $db;
    }

    public function checkLogin()
    {
        $sql = "select * from ".$this->table_name." where username=:username and password=:password";

        $stmt = $this->conn->prepare($sql);

        $this->username =  htmlspecialchars(strip_tags($this->username));
        $this->password = sha1(htmlspecialchars(strip_tags($this->password)));

        $stmt->bindParam(":username", $this->username);
        $stmt->bindParam(":password", $this->password);
        
        if ($stmt->execute()) {
            if ($stmt->rowCount() > 0) {
                $row = $stmt->fetch(PDO::FETCH_ASSOC);
                $this->user_id = $row['user_id'];
                return true;
            } else {
                return false;
            }
        }
    }

    public function register()
    {
        $sql ="INSERT into ".$this->table_name." (username,password) VALUES(:username,:password)";
        $stmt = $this->conn->prepare($sql);

        $this->username =  htmlspecialchars(strip_tags($this->username));
        $this->password = sha1(htmlspecialchars(strip_tags($this->password)));

        $stmt->bindParam(':username', $this->username);
        $stmt->bindParam(':password', $this->password);

        if ($stmt->execute()) {
            return true;
        } else {
            return false;
        }
    }


    public function username_exists($username)
    {
        $sql = "select * from ".$this->table_name." where username = :username";

        $stmt  = $this->conn->prepare($sql);

        $username = htmlspecialchars(strip_tags($username));

        $stmt->bindParam(":username", $username);

        if ($stmt->execute()) {
            if ($stmt->rowCount() > 0) {
                return true;
            } else {
                return false;
            }
        }
    }
}
