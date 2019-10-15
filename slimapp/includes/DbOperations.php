<?php

    class DbOperations{

        private $con;

        function __construct(){
            require_once dirname(__FILE__) . '/DbConnect.php';

            $db = new DbConnect;
            $this->con = $db->connect();
        }

        public function createUser($name, $email, $password){
            if(!$this->isEmailExist($email)){
                $stmt = $this->con->prepare("INSERT INTO login (name,email,password) VALUES (?, ?, ?)");
                $stmt->bind_param("sss", $name, $email, $password);
                if($stmt->execute()){
                    return USER_CREATED;
                }
                else{
                    return USER_FAILURE;
                }
            }
            return USER_EXIST;
        }

        public function userLogin($email, $password){
            if($this->isEmailExist($email)){
                $hashed_password =$this->getUsersPasswordByEmail($email);
                if(password_verify($password, $hashed_password)){
                    return USER_AUTHENTICATED;
                }
                else{
                    return USER_PASSWORD_DO_NOT_MATCH;
                }
            }
            else{
                return USER_NOT_FOUND;
            }
        }

            private function getUsersPasswordByEmail($email){

                $stmt = $this->con->prepare("SELECT password FROM login WHERE email = ?");
                $stmt->bind_param("s", $email);
                $stmt->execute();
                $stmt->bind_result($password);
                $stmt->fetch();
                return $password;
            }

            public function getAllUsers(){
                $stmt = $this->con->prepare("SELECT id, name, email,password FROM login;");
                $stmt->execute();
                $stmt->bind_result($id, $name, $email, $password);
                $users = array();
                while($stmt->fetch()){
                    $user = array();
                    $user['id'] = $id;
                    $user['name'] = $name;
                    $user['email'] = $email;
                    $user['password'] = $password;
                    array_push($users, $user);
                }
                return $users;
            }

            public function updateUser($name, $email,$id){
                $stmt = $this->con->prepare("UPDATE login SET  name = ? email = ?  WHERE id = ?");
                $stmt->bind_param("ssi", $name, $email, $id);
                if($stmt->execute())
                    return true;
                return false;
            }

           public function getUserByEmail($email){
                $stmt = $this->con->prepare("SELECT id, name, email,password FROM login WHERE email = ?");
                $stmt->bind_param("s", $email);
                $stmt->execute();
                $stmt->bind_result($id, $name, $email, $password);
                $stmt->fetch();
                $user = array();
                $user['id'] = $id;
                $user['name'] = $name;
                $user['email'] = $email;
                $user['password'] = $password;
                return $user;
            }
        

        private function isEmailExist($email){
            $stmt = $this->con->prepare("SELECT id FROM login WHERE email = ?");
            $stmt->bind_param("s", $email);
            $stmt->execute();
            $stmt->store_result();
            return $stmt->num_rows > 0;
        }
    }