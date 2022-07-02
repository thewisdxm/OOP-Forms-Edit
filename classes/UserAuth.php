<?php
include_once 'Dbh.php';
session_start();

class UserAuth extends Dbh{
    private static $db;

    public function __construct(){
        $this->db = self::$db;
        // Increment 
        self::$db++;
    }

    public function register($fullname, $email, $password, $confirmPassword, $country, $gender){
        $conn = $this->db->connect();
        if ($this->getUserByUsername($email)) 
        { 
            header("Location: forms/login.php?login=registrationerror");
        }  
         else 
            {
                if($this->validatePassword($password, $confirmPassword)){
                    $hashed_password = password_hash($password, PASSWORD_DEFAULT);
                    $sql = "INSERT INTO Students (`full_names`, `email`, `password`, `country`, `gender`) VALUES ('$fullname','$email', '$hashed_password', '$country', '$gender')";
                    if($conn->query($sql)){
                        header("Location: forms/login.php?registrationsuccess");
                    } else {
                        echo "Opps". $conn->error;
                    }
                } else {
                    header("Location: forms/register.php?registration=passworderror");
                } 
            }
    }

    public function login($email, $password){
        $conn = $this->db->connect();
        if ($this->checkPassword($email, $password)) {
    
            $sql = "SELECT * FROM Students WHERE email='$email'";
            $result = $conn->query($sql);

        if($result->num_rows > 0){
            $sqlname = "SELECT full_names FROM Students WHERE email='$email'";
            $resultname = $conn->query($sqlname);
            $row = mysqli_fetch_assoc($resultname);
            $user_session = $row['full_names'];
            
            $_SESSION['email'] = $user_session;
            header("Location: dashboard.php");
        } 
        }  else {
            // login failed;
            header("Location: forms/login.php?login=passworderror");
        }
    }
        
    public function checkEmailExist($email){
        $conn = $this->db->connect();
        $sql = "SELECT * FROM Students WHERE email = '$email'";
        $result = $conn->query($sql);
        if($result->num_rows > 0){
            return $result->fetch_assoc();
        } else {
            return false;
        }
    }

    public function checkPassword($email, $password){
        $conn = $this->db->connect();
        $stmt = $conn->prepare("SELECT * FROM Students WHERE email = '$email'");
    //    $stmt->bind_param("ss", "$email", "$password");
        $stmt->execute();
        $user = $stmt->get_result()->fetch_assoc();

        if ($user && password_verify($password, $user['password']))
        {
            return TRUE;
        } else {
            return FALSE;
        }
    }

    public function getAllUsers(){
        $conn = $this->db->connect();
        if (!(isset($_SESSION['email']) && $_SESSION['email']!=''))
        {
            header("Location: forms/login.php?login=dashboarderror");
        } else {
            $sql = "SELECT * FROM Students";
        $result = $conn->query($sql);
        echo"<html>
        <head>
        <link rel='stylesheet' href='https://stackpath.bootstrapcdn.com/bootstrap/4.3.1/css/bootstrap.min.css' integrity='sha384-ggOyR0iXCbMQv3Xipma34MD+dH/1fQ784/j6cY/iJTQUOhcWr7x9JvoRxT2MZw1T' crossorigin='anonymous'>
        </head>
        <body>
        <center><h1><u> ZURI PHP STUDENTS </u> </h1>" . 
        "<form action='dashboard.php' method='post'>" .
             "<button class='btn btn-danger' type='submit', name='back'> Go back to Dashboard </button> 
             </form>" .
        "<table class='table table-bordered' border='0.5' style='width: 80%; background-color: smoke; border-style: none'; >
        <tr style='height: 40px'>
            <thead class='thead-dark'> <th>ID</th><th>Full Names</th> <th>Email</th> <th>Gender</th> <th>Country</th> <th>Action</th>
        </thead></tr>";
        if($result->num_rows > 0){
            while($data = mysqli_fetch_assoc($result)){
                //show data
                echo "<tr style='height: 20px'>".
                    "<td style='width: 50px; background: gray'>" . $data['id'] . "</td>" .
                    "<td style='width: 150px'>" . $data['full_names'] .
                    "</td> <td style='width: 150px'>" . $data['email'] .
                    "</td> <td style='width: 150px'>" . $data['gender'] . 
                    "</td> <td style='width: 150px'>" . $data['country'] . 
                    "</td>
                    <td style='width: 150px'> 
                    <form action='action.php' method='post'>
                    <input type='hidden' name='id'" .
                     "value=" . $data['id'] . ">".
                    "<button class='btn btn-danger' type='submit', name='delete'> DELETE </button> </form> </td>".
                    "</tr>";
                
            }
            echo "</table></table></center></body></html>";
        
        }
        }
        
    }

    public function deleteUser($id){
        $conn = $this->db->connect();
        $sql = "DELETE FROM Students WHERE id = '$id'";
        if($conn->query($sql) === TRUE){
            header("refresh:0.5; url=action.php?all");
        } else {
            header("refresh:0.5; url=action.php?all=?message=Error");
        }
    }

    public function updateUser($email, $password){
        $conn = $this->db->connect();
        if ($this->checkPassword($email, $password)) 
        {
            header("Location: forms/login.php?login=reseterror");    
        } else 
            {
            $hashed_password = password_hash($password, PASSWORD_DEFAULT);
            if ($this->checkEmailExist($email)){
            $sql = "UPDATE Students SET password = '$hashed_password' WHERE email = '$email'";
            if($conn->query($sql) === TRUE){
                header("Location: forms/login.php?login=resetsuccess");
            } 
            } else {
                header("Location: forms/register.php?registration=reseterror");
            }
            }
    }
    
    public function getUserByUsername($email){
        $conn = $this->db->connect();
        $sql = "SELECT * FROM Students WHERE email = '$email'";
        $result = $conn->query($sql);
        if($result->num_rows > 0){
            return $result->fetch_assoc();
        } else {
            return false;
        }
    }

    public function logout($email){
        if (!(isset($_SESSION['email']) && $_SESSION['email']!='')) {
            header("Location: forms/login.php?login=dashboarderror");
        } else {
            session_start();
            session_destroy();
            header("Location: forms/login.php?login=logoutsuccess");
        }
    }

    public function validatePassword($password, $confirmPassword){
        if($password === $confirmPassword){
            return true;
        } else {
            return false;
        }
    }
}