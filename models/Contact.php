<?php
class Contact
{
    private $conn;
    private $table_name = 'contacts';

    public $contact_id;
    public $user_id;
    public $name;
    public $email;
    public $phone_number;

    public function __construct($db, $user_id)
    {
        $this->conn = $db;
        $this->user_id = $user_id;
    }

    public function read()
    {
        $sql = "select * from ".$this->table_name." where user_id = :user_id";

        $stmt = $this->conn->prepare($sql);
        $stmt->bindParam(":user_id", $this->user_id);

        if ($stmt->execute()) {
            return $stmt;
        }
    }

    public function readOne()
    {
        $sql = "select * from ".$this->table_name." where user_id = :user_id and contact_id = :contact_id";

        $stmt = $this->conn->prepare($sql);
        $stmt->bindParam(":user_id", $this->user_id);
        $stmt->bindParam(":contact_id", $this->contact_id);

        if ($stmt->execute()) {
            return $stmt;
        }
    }

    public function contact_exists()
    {
        $count = $this->readOne()->rowCount();
        if ($count > 0) {
            return true;
        } else {
            return false;
        }
    }


    public function insert()
    {
        $sql = "INSERT into ".$this->table_name." (user_id,name,email,phone_number) VALUES(:user_id,:name,:email,:phone_number)";
        $stmt = $this->conn->prepare($sql);

        $this->name = htmlspecialchars(strip_tags($this->name));
        $this->email = htmlspecialchars(strip_tags($this->email));
        $this->phone_number = htmlspecialchars(strip_tags($this->phone_number));

        $stmt->bindParam(":user_id", $this->user_id);
        $stmt->bindParam(":name", $this->name);
        $stmt->bindParam(":email", $this->email);
        $stmt->bindParam(":phone_number", $this->phone_number);

        if ($stmt->execute()) {
            return true;
        } else {
            return false;
        }
    }

    public function delete()
    {
        $sql = "delete from ".$this->table_name. " where user_id = :user_id and contact_id = :contact_id";
        $stmt = $this->conn->prepare($sql);
        $stmt->bindParam(":user_id", $this->user_id);
        $stmt->bindParam(":contact_id", $this->contact_id);
        if ($stmt->execute()) {
            return true;
        } else {
            return false;
        }
    }

    public function update()
    {
        $sql = "update ".$this->table_name." set name=:name, email=:email, phone_number=:phone_number where user_id = :user_id and contact_id = :contact_id";

        $stmt = $this->conn->prepare($sql);
        $this->name = htmlspecialchars(strip_tags($this->name));
        $this->email = htmlspecialchars(strip_tags($this->email));
        $this->phone_number = htmlspecialchars(strip_tags($this->phone_number));

        $stmt->bindParam(":user_id", $this->user_id);
        $stmt->bindParam(":contact_id", $this->contact_id);
        $stmt->bindParam(":name", $this->name);
        $stmt->bindParam(":email", $this->email);
        $stmt->bindParam(":phone_number", $this->phone_number);


        if ($stmt->execute()) {
            return true;
        } else {
            return false;
        }
    }
}
