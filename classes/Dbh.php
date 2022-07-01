<?php

class Dbh 
{
    private $host = "127.0.0.1";
    private $user = "root";
    private $pass = "";
    private $db = "zuriphp";

    protected function connect()
    {   
        $conn = mysqli_connect($this->host, $this->user, $this->pass, $this->db);
        
        if(!$conn){
        echo "<h1 style='color: red'>Error connecting to the database</h1>";
        } 
        // else {
        //     echo "<h1 style='color: green'>Connected Successfully</h1>";
        // }
        return $conn;
    }
}

?>