<?php
require_once('DbLink.php');
require_once('Validator.php');
require_once('Db.php');

/**
* Описание api 'Фитнесс'
*
*
* @author 'ire'
*/
class AuthDbBase extends DbPDO 
{    
    public function getUserById($userId) 
    {    
        $sql = "SELECT * FROM users WHERE id=:userId";
        
        $stmt = $this->db->prepare($sql);
        $stmt->bindValue(':userId', $userId, PDO::PARAM_STR);
        $stmt->execute();
        
        $result = $stmt->fetch(PDO::FETCH_ASSOC);

        if($result == null){
            return null;
        }
        
        $user =   array(
                    "id"           => (int)$result['id'],
                    "firstName"    => $result['firstName'],
                    "lastName"     => $result['lastName'],
                    "appleId"      => $result['appleId'],
                    "token"      => $result['token'],
                    "email"        => $result['email'],);       

        return $user;
    }

    public function getUserByAppleId($appleUserId) 
    {    
 
        $sql = "SELECT * FROM users WHERE appleId=:appleUserId";
        
        $stmt = $this->db->prepare($sql);
        $stmt->bindValue(':appleUserId', $appleUserId, PDO::PARAM_STR);
        $stmt->execute();
        
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if($result == null){
            return null;
        }
        
        $user =   array(
                    "id"           => (int)$result['id'],
                    "firstName"    => $result['firstName'],
                    "lastName"     => $result['lastName'],
                    "appleId"      => $result['appleId'],
                    "token"      => $result['token'],
                    "email"        => $result['email'],);       

       return $user;
    }
    
    public function getUserByEmail($email) 
    {     
        $query = "SELECT * FROM users where `email`=:email";
        
        $stmt = $this->db->prepare($query);
        
        $stmt->bindValue(':email', $email, PDO::PARAM_STR);
        
        $foundByEmail = $stmt->execute();
        
        if(!$foundByEmail){
            return null;
        }
        
        $user = array();

        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {	
            $user =   array(
                    "id"           => (int)$row['id'],
                    "appleId"      => $row['appleId'],
                    "email"        => $row['email'],
                    "token"      => $row['token']
            );   
 
        }		
        
        return $user;
    }
    
    public function createUser($email, $appleId) 
    {     
        $token = md5(uniqid());
        $data = [
            'email' => $email,
            'appleId' => $appleId,
              'token' => $token
        ];
        
        $sql = "INSERT INTO users (email, token, appleId) VALUES (:email,:token , :appleId)";
        $stmt= $this->db->prepare($sql);
        $stmt->execute($data);
        
        $user =  array(
                "id"           => $this->db->lastInsertId(),
                "appleId"      => $appleId,
                "token"      => $token,
                "email"        => $email
        );  
        
        return $user;
    }
    
    public function updateTokenInfo($user, $jwt) 
    {     
        /*
        $user_id = $user["id"];
        $query = 'UPDATE users SET token = :token WHERE id = :user_id';
        $stmt = $this->db->prepare($query);
        $stmt->bindValue(':token', $jwt, PDO::PARAM_STR);
        $stmt->bindValue(':user_id', $user_id, PDO::PARAM_INT);
                
        if ($stmt->execute()) {
           //  setcookie("token_$user_id",$token,time()+60*60*24*7,'/');
        }
        */
    }
}