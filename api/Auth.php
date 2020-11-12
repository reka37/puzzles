<?php
include_once 'JWT/BeforeValidException.php';
include_once 'JWT/ExpiredException.php';
include_once 'JWT/SignatureInvalidException.php';
include_once 'JWT/JWT.php';
require_once('AuthDb.php');

use \Firebase\JWT\JWT;

class AuthHelper{
    
    function __construct($jwtSettings){
        $this->jwtSettings = $jwtSettings;
    }
    
    public function getCurrentUser(){
        
        if(!isset($_COOKIE[$this->jwtSettings['cookieName']])){
            return null;
        }
        
        $jwt = $_COOKIE[$this->jwtSettings['cookieName']];
        
        try {
            $decoded = JWT::decode($jwt, $this->jwtSettings['key'], array('HS256'));
            
            $userId = $decoded->data->id;
            
            $authDb = new AuthDbBase(DB_NAME, DB_USERNAME, DB_PASSWORD);
             
            $user = $authDb->getUserById($userId);
            
            
            return $user;
        }
        catch (Exception $e){
        
            
            return null;
        }
    }
    
    public function authorizeUser($user){
        
        // we need to re-generate jwt because user details might be different

        $token = array(
           "iss" => $this->jwtSettings['iss'],
           "aud" => $this->jwtSettings['aud'],
           "data" => array(
               "id" => $user['id'],
           )
        );
        
        $jwt = JWT::encode($token, $this->jwtSettings['key']);
 

       // $user_id = $user['id'];
      //  setcookie("token_$user_id",$jwt,time()+60*60*24*7,'/');
    //    return $jwt;
        return $user["token"];
    }
} 

?>