<?php
    require_once('TokenValidator.php');
	require_once('dbSettings.php');
	require_once('AuthDb.php');
	require_once('Auth.php');
	include_once 'JwtSettings.php';
    
	$authHelper = new AuthHelper($jwtSettings);
	
	try {
	    
	    $jsonRaw = file_get_contents('php://input');
		$json = json_decode($jsonRaw);
	    
	    $validator = new TokenValidator;
	    if(!isset($json->id_token)){
	        echo 'id_token required.';
	        return;
	    }
	    
	    $token = $json->id_token;
	
	    $validationResult = $validator->ValidateToken($token);
	    
	    if($validationResult['success']){
	           
	        $authDb = new AuthDbBase(DB_NAME, DB_USERNAME, DB_PASSWORD);
	        
	        $appleId = $validationResult['userInfo']['sub'];

	        $user = $authDb->getUserByAppleId("$appleId");
	        
	        if($user == null){
	           $user = $authDb->getUserByEmail($validationResult['userInfo']['email']);
	        }
	        
	        if($user == null){
	            $user = $authDb->createUser($validationResult['userInfo']['email'], $validationResult['userInfo']['sub'], $token) ;
	        }
                $jwt = $authHelper->authorizeUser($user);
                // $authDb->updateTokenInfo($user, $jwt);
                //  echo json_encode($user);
                //   echo $jwt;
                header('Content-Type: application/json');
                echo json_encode((object)array( 
                    "result"=> true,
                    "value"=>$jwt
                ));	
	        
	    }else{
                header('Content-Type: application/json');
                echo json_encode((object)array(
                "result"=> false,
                "errorType"=>"connection_error",
                "message"=> "connection_error"
            ));
	    }
	    
    } catch (Exception $e) {
        echo 'Выброшено исключение: ',  $e->getMessage(), "\n";
        http_response_code(500);
    }
