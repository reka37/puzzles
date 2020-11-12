<?php
require_once(__DIR__ . '/DbLink.php');
require_once(__DIR__ . '/Validator.php');
require_once(__DIR__ . '/../vendor/autoload.php');

class e_otb_exception extends Exception {}
class e_login_exception extends e_otb_exception {}

/**
* Описание api 'Пазлы'
*
*
* @author 'ire'
*/
class DbBase extends DbPDO 
{
    
    /**
	* РЕГИСТРАЦИЯ ПОЛЬЗОВАТЕЛЯ
	* 
	* POST запрос на этот адрес<br>
	*  baseURL/api/Api.php?action=reg&token=secret_token  <br>
	* передать json параметр {"email":"почта пользователя", "password":"пароль пользователя", "languages":"языки - ru или en"}<br>
	* Возвращается<br>
	* 'true' если регистрация прошла успешно<br>
	* 'false' если регистрация не произошла<br>
	* @param string $email почта пользователя
	* @param string $password пароль пользователя
	* @return bool
	*/
    public function reg($email, $password, $languages) 
    {     
        $language = $this->getLanguagesErrore();
	
		$query = "SELECT * FROM users WHERE email = '$email'";
		$stmt = $this->db->prepare($query);
		if ($stmt->execute()) {
    		$row = $stmt->fetch(PDO::FETCH_ASSOC);
    
        	if ($row) {	
        			return (object)array(
        				"result"=> false,
        				"errorType"=>"mail_is_already_taken",
        				"message"=> $language[$languages]["mail_is_already_taken"]
        			);	
    		} else {			
    
    			$sql=
    				'INSERT INTO users(`email`, `password`, `token`) ' .
    				'VALUES (:email, :password, :token)';
    			$stmt = $this->db->prepare($sql);
    			$stmt->bindValue(':email', $email, PDO::PARAM_STR);			
    			$password = password_hash($password, PASSWORD_DEFAULT);			
    			$stmt->bindValue(':password', $password, PDO::PARAM_STR);
    			$token = md5(uniqid());
    			$stmt->bindValue(':token', $token, PDO::PARAM_STR);
    			
    			if ($stmt->execute()) {
    				return (object)array(
    				        "value"=> $token,
    						"result"=> true,
    						"message"=> $language[$languages]["success"]
    				);
    			} else {
    				return (object)array(
    					"result"=> false,
    					"errorType"=>"connection_error",
    					"message"=> $language[$languages]["connection_error"]
    				);				
    			} 		
    		}
		} else {
		    return (object)array(
				"result"=> false,
				"errorType"=>"connection_error",
				"message"=> $language[$languages]["connection_error"]
			);	
		}	
    }
    
    /**
	* ВХОД ПОЛЬЗОВАТЕЛЯ
	* 
	* POST запрос на этот адрес<br>
	*  baseURL/api/Api.php?action=login&token=secret_token  <br>
	* передать json параметр {"email":"почта пользователя", "password":"пароль пользователя", "languages":"языки - ru или en"}<br>
	* Возвращается<br>
	* 'true' если успешно<br>
	* 'false' если не произошла<br>
	* @param string $name имя пользователя
	* @param string $email почта пользователя
	* @param string $password пароль пользователя
	* @return bool
	*/ 
    public function login($email, $password, $languages) 
    {       
        $language = $this->getLanguagesErrore();
	 
		$query = "SELECT * FROM users WHERE email = :email";
		$stmt = $this->db->prepare($query);
		$stmt->bindValue(':email', $email, PDO::PARAM_STR);
		
		if ($stmt->execute()) {
		
    		$row = $stmt->fetch(PDO::FETCH_ASSOC);
    
    		if (!$row) {	
    			return (object)array(
    				"result"=> false,
    				"errorType"=>"mail_is_incorrect",
    				"message"=> $language[$languages]["no_mail"]
    			);
    		} else {
    			
    			if (password_verify($password, $row['password'])) {
    				return (object)array(
    					"result"=> true,
    					"message"=> $language[$languages]["success"],
    					"value"=> $row['token']
    				);
    			} else {
    				return (object)array(
    					"result"=> false,
    					"errorType"=>"password_is_incorrect",
    					"message"=> $language[$languages]["password_is_incorrect"]
    				);	
    			}
    		}
		} else {
		    return (object)array(
				"result"=> false,
				"errorType"=>"connection_error",
				"message"=> $language[$languages]["connection_error"]
			);	
		}	
    } 
    
   /**
    * @ignore
    */
    public function getLanguagesErrore() 
    {       
        $language = [];
		$language["ru"] = [
			"no_mail" => "Не верная почта",
			"mail_is_already_taken" => "Почта занята",
			"password_is_incorrect" => "Не верный пароль или почта",
			"connection_error" => "Ошибка в соединении",
	    	"mail_not_sent" => "Письмо не отправлено",
			"success" => "Успешно"
		];
		
		$language["en"] = [
			"no_mail" => "Incorrect mail",
			"mail_is_already_taken" => "Email address is already taken",
			"password_is_incorrect" => "Incorrect mail or password",
			"connection_error" => "Connection error",
			"mail_not_sent" => "Mail not sent",
			"success" => "Success"
		];
	
	
        return $language;	
        
    } 
    
    /**
    * УСТАНОВИТЬ АВТОРИЗАЦИОННЫЙ ТОКЕН
    * 
    * POST запрос на этот адрес<br>
    *  baseURL/api/Api.php?action=setTokenGlobal&token=secret_token  <br>
    * передать json параметр {"token":"авторизационный токен"}<br>
    * Возвращается json строка
    *    1. Если токен уже установлен, то возвращается: {id - id, token - авторизационный токен,<br>
    *  money - количество монет у акаунта<br>
    *   2. Если токен не установлен, то true 
    *   3. Если ошибка, то false
    * @return string
    */
    public function setTokenGlobal($token) 
    {     
        $query="SELECT * FROM users WHERE token=:token";
        $stmt = $this->db->prepare($query);
        $stmt->bindValue(':token', $token, PDO::PARAM_STR);
        $stmt->execute();
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if ($row) {
            $items = (object) array(
                    "id"      => (int)$row['id'],
                    "token"    => $row['token'],
                    "money"   => $row['money']
            );       
            return $items;
        } else {
            $sql = 'INSERT INTO users(`token`) ' .
				'VALUES (:token)';
			$stmt = $this->db->prepare($sql);
			$stmt->bindValue(':token', $token, PDO::PARAM_STR);
			return $stmt->execute(); 
        }
       
    }
    
   /**
	* АВТОРИЗАЦИЯ ПОЛЬЗОВАТЕЛЯ ГУГЛ И ЭЙПЛ(ВСПОМОГАТЕЛЬНЫЙ МЕТОД - НЕ ИСПОЛЬЗОВАТЬ, ЕСТЬ АНАЛОГИЧНЫЕ С ВАЛИДАЦИЕЙ)
	* 
	* POST запрос на этот адрес<br>
	*  baseURL/api/Api.php?action=authorization&token=secret_token  <br>
	* передать json параметр {"email":"почта пользователя", "languages":"языки - ru или en"}<br>
	* Возвращается<br>
	* 'true' если успешно<br>
	* 'false' если не произошла<brЮ
	* @return bool
	*/ 
    public function authorization($email, $languages) 
    {      
        $language = $this->getLanguagesErrore();
        
        $token = md5(uniqid());
        
		$query = "SELECT * FROM users WHERE email = :email";
		$stmt = $this->db->prepare($query);
		$stmt->bindValue(':email', $email, PDO::PARAM_STR);
		
		if ($stmt->execute()) {
    		$row = $stmt->fetch(PDO::FETCH_ASSOC);
    
    		if (!$row) {	
    			$sql=
    				'INSERT INTO users(`email`, `token`) ' .
    				'VALUES (:email, :token)';
    			$stmt = $this->db->prepare($sql);
    			$stmt->bindValue(':email', $email, PDO::PARAM_STR);	
    			$stmt->bindValue(':token', $token, PDO::PARAM_STR);				
    		
    			if ($stmt->execute()) {
    				return (object)array(
    						"result"=> true,
    						"message"=> $language[$languages]["success"],
    						"value" => $token
    				);		
    			} else {
    
    				return (object)array(
    					"result"=> false,
    					"errorType"=>"connection_error",
    					"message"=> $language[$languages]["connection_error"]
    				);				
    			} 		
    		} else {
					return (object)array(
						"result"=> true,
						"message"=> $language[$languages]["success"],
						"token" => $row['token']
					);
    				
    		}
		} else {
		    return (object)array(
				"result"=> false,
				"errorType"=>"connection_error",
				"message"=> $language[$languages]["connection_error"]
			);
		}	
    }
    
    /**
	* АВТОРИЗАЦИЯ ПОЛЬЗОВАТЕЛЯ ГУГЛ C ВАЛИДАЦИЕЙ ТОКЕНА И ИЗВЛЕЧЕНИИ ИНФОРМАЦИИ О ПОЛЬЗОВАТЕЛЕ
	* 
	* POST запрос на этот адрес<br>
	*  baseURL/api/Api.php?action=authorizationGoogle&token=secret_token  <br>
	* передать json параметр {"token":"id_token, "languages":"языки - ru или en", "client_id":"client_id от google"}<br>
	* Возвращается<br>
	* 'true' если успешно<br>
	* 'false' если не произошла<br>
	* @param string $token token пользователя
	* @param string $email почта пользователя
	* @param string $password пароль пользователя
	* @return bool
	*/ 
    public function authorizationGoogle($id_token, $languages, $client_id) 
    {        
        $language = $this->getLanguagesErrore();
        
	    $client = new Google_Client(['client_id' => $client_id]);  

        $payload = $client->verifyIdToken($id_token);

        if ($payload) {
            $email = $payload['email'];
            $result = $this->authorization($email, $languages);
            return $result;
        } else {
            return (object)array(
                "result"=> false,
                "errorType"=>"connection_error",
                "message"=> $language[$languages]["connection_error"]
            );	
        }
    }
    
     /**
	* АВТОРИЗАЦИЯ ПОЛЬЗОВАТЕЛЯ FACEBOOK C ВАЛИДАЦИЕЙ ТОКЕНА И ИЗВЛЕЧЕНИИ ИНФОРМАЦИИ О ПОЛЬЗОВАТЕЛЕ
	* 
	* POST запрос на этот адрес<br>
	*  baseURL/api/Api.php?action=authorizationFacebook&token=secret_token  <br>
	* передать json параметр {"token":"access_token", "languages":"языки - ru или en"}<br>
	* Возвращается<br>
	* 'true' если успешно<br>
	* 'false' если не произошла<br>
	* @return bool
	*/ 
    public function authorizationFacebook($token, $languages) 
    { 
        $language = $this->getLanguagesErrore();

		$params = array(
			'access_token' => $token,
			'fields'       => 'id,email,first_name,last_name,picture'
		);

		$info = file_get_contents('https://graph.facebook.com/me?' . urldecode(http_build_query($params)));
		$info = json_decode($info, true);

		$email = $info['email'];
	
		if (!empty($email)) {
    		$result = $this->authorization($email, $languages);
            return $result;
		} else {
	        return (object)array(
                "result"=> false,
                "errorType"=>"connection_error",
                "message"=> $language[$languages]["connection_error"]
            );
		}
    }
    
    /*
    * АВТОРИЗАЦИЯ ПОЛЬЗОВАТЕЛЯ ЧЕРЕЗ APPLE C ВАЛИДАЦИЕЙ ТОКЕНА И ИЗВЛЕЧЕНИИ ИНФОРМАЦИИ О ПОЛЬЗОВАТЕЛЕ
    * 
    * POST запрос на этот адрес<br>
    *  baseURL/api/Api.php?action=authorizationApple&token=secret_token  <br>
    * передать json параметр {"id_token":"токен apple"}<br>
    * Возвращается json строка
    * @return string
    */
    public function authorizationApple($id_token) {    

        $params = array(
            'id_token' => $id_token 
        );
        
        $string = json_encode($params); 
		$ch = curl_init(SERVER . "api/Login.php");
		curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type: application/json','Content-Length: ' . strlen($string)));
		curl_setopt($ch, CURLOPT_POSTFIELDS, $string); 
	    curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($params)); 
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
		curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
		curl_setopt($ch, CURLOPT_HEADER, false);
		$html = curl_exec($ch);
		curl_close($ch);  		
		return json_decode($html);

    }
    
	 /**
	* ВОСТАНОВЛЕНИЕ ПАРОЛЯ
	* 
	* POST запрос на этот адрес<br>
	*  baseURL/api/Api.php?action=recovery&token=secret_token  <br>
	* передать json параметр {"email":"почта пользователя", "languages":"языки - ru или en"}<br>
	* Возвращается<br>
	* 'true' если успешно<br>
	* 'false' если не произошла<br>
	* @param string $email почта пользователя
	* @return bool
	*/ 
	public function recovery($email, $languages) 
    {   	
		$language = $this->getLanguagesErrore();
		$query = "SELECT * FROM users WHERE email = :email";
		$stmt = $this->db->prepare($query);
		$stmt->bindValue(':email', $email, PDO::PARAM_STR);
		
		if ($stmt->execute()) {
    		$row = $stmt->fetch(PDO::FETCH_ASSOC);
    
    		if (!$row) {	
    			return (object)array(
    				"result"=> false,
    				"errorType"=>"no_such_mail",
    				"message"=> $language[$languages]["no_mail"]
    			);
    		} else {				
				$code = rand(1000, 9999);
    			
				$params = array(
					'mail' => $email,
					'code' => $code,
					'languages' => $languages,
					'secret' => md5('djj2jdss93ukdks12jss')
				);
				
				$string = json_encode($params); 
				$ch = curl_init("https://martingrey.app/appmail.php");
				curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type: application/json','Content-Length: ' . strlen($string)));
				curl_setopt($ch, CURLOPT_POSTFIELDS, $string); 
				curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($params)); 
				curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
				curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
				curl_setopt($ch, CURLOPT_HEADER, false);
				$html = curl_exec($ch);
				curl_close($ch);  
				$html = json_decode($html);

				if ($html->result) {
					return "$code"; 
				} else {
					return (object)array(
						"result"=> false,
						"errorType"=>"mail_not_sent",
						"message"=> $language[$languages]["mail_not_sent"]
					);	
				}	
			} 
		} else {
			return (object)array(
				"result"=> false,
				"errorType"=>"connection_error",
				"message"=> $language[$languages]["connection_error"]
			);
		}		
	}
	 
	/*
    public function recovery($email, $languages) 
    {        
        $language = $this->getLanguagesErrore();

		$query = "SELECT * FROM users WHERE email = :email";
		$stmt = $this->db->prepare($query);
		$stmt->bindValue(':email', $email, PDO::PARAM_STR);
		
		if ($stmt->execute()) {
    		$row = $stmt->fetch(PDO::FETCH_ASSOC);
    
    		if (!$row) {	
    			return (object)array(
    				"result"=> false,
    				"errorType"=>"no_such_mail",
    				"message"=> $language[$languages]["no_mail"]
    			);
    		} else {	
    			$code = rand(1000, 9999);
    			$email=$row['email']; 		
    			$to  =$email; 
    						
    			if ($languages == "en") {
    								
    				$subject = "Password recovery"; 
    				$message = '<p> Hello!!!<br>';
    				$message .= 'Your code is - ' . $code . '<br>';
    				$message .= 'If you didn’t ask to reset your password please contact our team<br>';
    				$message .= 'immediately by replying this email.<br>';
    				$message .= 'Regards,<br>';
    				$message .= 'Martin Grey Team<br>';	
    
    			} elseif ($languages == "ru") { 
    				
    				$subject = "Восстановление пароля";
    				$message = '<p> Привет,'. $row['name'] . '<br>';
    				$message.= 'Ваш код - '. $code . '<br>';
    				$message.= 'Если вы не просили сбросить пароль, свяжитесь с нашей командой <br>';
    				$message.= 'немедленно ответив на это письмо. <br>';
    				$message.= 'С уважением, <br>';
    				$message.= 'Команда Мартина Грея <br>';				
    			}
    										
    			$headers  = "Content-type: text/html; charset=utf-8 \r\n"; 
    			$headers .= "From: <support@martingrey.app>\r\n"; 
    			$headers .= "Reply-To: reply-to@support@martingrey.app\r\n"; 
    							
    			if (mail($to, $subject, $message, $headers)) {
    				return "$code"; 
    			} else {
    				return (object)array(
    					"result"=> false,
    					"errorType"=>"mail_not_sent",
    					"message"=> $language[$languages]["mail_not_sent"]
    				);	
    			}					
    		} 
		} else {
	    	return (object)array(
				"result"=> false,
				"errorType"=>"connection_error",
				"message"=> $language[$languages]["connection_error"]
			);
		}
    }
	*/
	/**
	* СМЕНА ПАРОЛЯ
	* 
	* POST запрос на этот адрес<br>
	*  baseURL/api/Api.php?action=changepassword&token=secret_token  <br>
	* передать json параметр {"email":"почта пользователя", "password":"новый пароль пользователя", "languages":"языки - ru или en"}<br>
	* Возвращается<br>
	* 'true' если успешно<br>
	* 'false' если не произошла<br>
	* @param string $email почта пользователя
	* @return bool
	*/
    public function changepassword($email, $password, $languages) 
    {        
        $language = $this->getLanguagesErrore();
	
		$query = "SELECT * FROM users WHERE email = :email";
		$stmt = $this->db->prepare($query);
		$stmt->bindValue(':email', $email, PDO::PARAM_STR);
		
		if ($stmt->execute()) {
    		$row = $stmt->fetch(PDO::FETCH_ASSOC);
    
    		if (!$row) {	
    			return (object)array(
    				"result"=> false,
    				"errorType"=>"no_such_mail",
    				"message"=> $language[$languages]["no_mail"]
    			);  
    		} else {				
    			$id=$row['id']; 
    			$sql = "UPDATE users SET password =:password WHERE `id`= :id";
    			$stmt = $this->db->prepare($sql);
    			$stmt->bindValue(':id', $id, PDO::PARAM_INT);
    			$password_ = password_hash($password, PASSWORD_DEFAULT);
    			$stmt->bindValue(':password', $password_, PDO::PARAM_STR);
    	
    			if ($stmt->execute()) {
    				return (object)array(
    					"result"=> true,
    					"message"=> $language[$languages]["success"]
    				);
    			} else {
    				return (object)array(
    					"result"=> false,
    					"errorType"=>"connection_error",
    					"message"=> $language[$languages]["connection_error"]
    				);
    			}					
    		} 
		} else {
		    return (object)array(
				"result"=> false,
				"errorType"=>"connection_error",
				"message"=> $language[$languages]["connection_error"]
			);
		}
    }
  
     /**
    * ПОЛУЧИТЬ ВСЮ ИНФОРМАЦИЮ, СВЯЗАННУЮ С АВТОРИЗАЦИОННЫМ ТОКЕНОМ
    * 
    * POST запрос на этот адрес<br>
    *  baseURL/api/Api.php?action=getListCatalog&token=secret_token  <br>
    * передать json параметр {"token":"авторизационный токен"}<br>
    * Возвращается json строка: id - id , token - авторизационый токен, money - количество монет<br>
    * @return string
    */
    public function getInfoTokenGlobal($token) 
    {     
        $query="SELECT * FROM users WHERE token=:token";
        $stmt = $this->db->prepare($query);
        $stmt->bindValue(':token', $token, PDO::PARAM_STR);
        $stmt->execute();
        $items = array();

        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {	
            $items = array(
                    "id"      => (int)$row['id'],
                    "token"    => $row['token'],
                    "money"   => $row['money']
            );       
        }			
        return (object)$items;
    }
    
   /**
    * ОБНУЛИТЬ ИНФОРМАЦИЮ, СВЯЗАННУЮ С ПОЛЬЗОВАТЕЛЕМ(монеты и купленные каталоги)
    * 
    * POST запрос на этот адрес<br>
    *  baseURL/api/Api.php?action=userInfoNull&token=secret_token  <br>
    * передать json параметр {"token":"авторизационный токен"}<br>
    * Возвращается json строка: id - id , token - авторизационый токен, money - количество монет<br>
    * @return string
    */
    public function userInfoNull($token) 
    {     
        $query="SELECT * FROM users WHERE token=:token";
        $stmt = $this->db->prepare($query);
        $stmt->bindValue(':token', $token, PDO::PARAM_STR);
        $stmt->execute();

        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        if ($row) {
            $user_id = $row['id'];
            $query = "UPDATE `users` SET `money` = NULL WHERE `token` = :token";
            $stmt = $this->db->prepare($query);
            $stmt->bindValue(':token', $token, PDO::PARAM_STR);
            
            if ($stmt->execute()) {
                $sql = 'DELETE FROM users_catalogs WHERE user_id = :user_id';
                $stmt = $this->db->prepare($sql);
                $stmt->bindValue(':user_id', $user_id, PDO::PARAM_INT);
                return $stmt->execute();
            } else {
                return false;
            }
        } else { 
            return false; 
        }
    }
    
    /**
    * ПОЛУЧИТЬ СПИСОК ВСЕХ КАТЕГОРИЙ
    * 
    * POST запрос на этот адрес<br>
    *  baseURL/api/Api.php?action=getListCatalog&token=secret_token  <br>
    * передать json параметр {"language":"ru или en", "token":"авторизационный токен"}<br>
    * Возвращается json строка: id - id каталога, name - имя каталога<br>
    *  image - путь к картинке<br>
    * @return string
    */
    public function getListCatalog($language, $token) 
    {     
        $query="SELECT * FROM users WHERE token=:token";
        $stmt = $this->db->prepare($query);
        $stmt->bindValue(':token', $token, PDO::PARAM_STR);
        $stmt->execute();
        $row_ = $stmt->fetch(PDO::FETCH_ASSOC);
        
        $query="SELECT * FROM users_catalogs WHERE user_id=:user_id";
        $stmt = $this->db->prepare($query);
        $stmt->bindValue(':user_id', $row_['id'], PDO::PARAM_INT);
        $stmt->execute();

        while ($row__ = $stmt->fetch(PDO::FETCH_ASSOC)) {	
            $users_catalogs[$row__['catalog_id']] = true;     
        }
               
        $query = "SELECT * FROM catalogs";
        $stmt = $this->db->prepare($query);
        $stmt->execute();
        $items = array();

        $allLanguage = $this->getLanguages();

        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {	
          
                if (!empty($users_catalogs[$row['id']])){ 

                        $isBuy = $users_catalogs[$row['id']];                
                } else {
                    if ($row['price'] == '0') {
                        $isBuy = true;
                    } else {
                        $isBuy = false;
                    } 
                    
                }
            $items[] = (object) array(
                    "id"      => (int)$row['id'],
                    "name"    => $allLanguage[$row['name']][$language],
                    "image"    => SERVER . 'api/content/catalogs/' . $row['image'],
                    "price"    => $row['price'],
                   "isBuy" => $isBuy
            );       
        }			
        return $items;
    }
    
     /**
	* ПОЛУЧИТЬ ВСЕ КАРТИНКИ ОДНОЙ КАТЕГОРИИ
	* POST запрос на этот адрес<br>
	*  baseURL/api/Api.php?action=getImagesOneCatalog&token=secret_token  <br>
	* передать json параметр {"catalogs_id":"ID каталога"}<br>
	* Возвращается json строка: id - id картинки, name - имя картинки,<br>
	* price - цена в монетах, image - путь к картинке<br>
	* @param string $catalogs_id id каталога
	* @return string
	*/
    public function getImagesOneCatalog($catalogs_id) 
    {     
        $query = "SELECT * FROM images WHERE catalogs_id=:catalogs_id";
        $stmt = $this->db->prepare($query);
        $stmt->bindValue(':catalogs_id', $catalogs_id, PDO::PARAM_STR);
        $stmt->execute();
        $items = array();

        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {	

                $items[] = (object) array(
                        "id"      => (int)$row['id'],
                        "name"    => $row['name'],
                        "price"    => $row['price'],
                        "image"    => SERVER . 'api/content/images/' . $row['name'],
                );       
        }			
        return $items;
    }
    
     /**
	* ПОЛУЧИТЬ ТРИ СЛУЧАЙНЫЕ КАРТИНКИ
	* POST запрос на этот адрес<br>
	*  baseURL/api/Api.php?action=getImagesThreeRandom&token=secret_token  <br>
	* передать json параметр {"token":"авторизационный токен"}<br>
	* Возвращается json строка: id - id картинки, name - имя картинки,<br>
	* price - цена в монетах, image - путь к картинке<br>
	* @return string
	*/
    public function getImagesThreeRandom($token) 
    {     
       $query = "SELECT * FROM users WHERE token = '$token'";
        $stmt = $this->db->prepare($query);
        $stmt->execute();
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        $user_id = $row['id'];
       
        $query = "SELECT 
        `images`.`id` as `id`,
        `images`.`name` as `name`, 
        `images`.`price` as `price` 
        FROM `images` LEFT JOIN `catalogs` ON `catalogs`.`id` = `images`.`catalogs_id` 
        WHERE `catalogs`.`price` LIKE '0' OR `catalogs`.`id` IN 
        (SELECT catalog_id FROM users_catalogs WHERE user_id = $user_id) ORDER BY rand() LIMIT 3";
       
        $stmt = $this->db->prepare($query);
        $stmt->execute();
        $items = array();

        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {	

                $items[] = (object) array(
                        "id"      => (int)$row['id'],
                        "name"    => $row['name'],
                        "price"    => $row['price'],
                        "image"    => SERVER . 'api/content/images/' . $row['name'],
                );       
        }			
        return $items;
    }
    /**
    * ПОЛУЧИТЬ СПИСОК ВСЕХ ПЕСЕН
    * 
    * GET запрос на этот адрес<br>
    * baseURL/api/Api.php?action=getMusic&token=secret_token<br>
    * Возвращается json строка: id - id музыкального файла, name - название, link - ссылка на музыку<br>
    * @return string
    */
    public function getMusic() 
    {    
        /*
        $path = "content/music/";
        $musics = scandir($path);
        array_shift($musics);
        array_shift($musics);
        $answer = [];
        foreach ($musics as $result) {
            
            $result = str_replace(' ', '', $result);
            $answer[] = [
                    $result,
                    SERVER . 'api/content/music/' . $result
            ];
        }
        return $answer;	
        */
         $query = "SELECT * FROM audio";
        $stmt = $this->db->prepare($query);
        $stmt->execute();
        $items = array();

        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {	
            $link = str_replace(' ', '%20', $row['name']);
            $items[] = (object) array(
                    "id"      => (int)$row['id'],
                    "name"    => $row['name'],
                    "link"    => SERVER . 'api/content/music/' . $link,
            );       
        }			
        return $items;       
    }
        
    /**
    * ПОЛУЧИТЬ ОДНУ ПЕСНЮ
    * 
    * POST запрос на этот адрес<br>
    * baseURL/api/Api.php?action=getOneMusic&token=secret_token<br>
    * передать json параметр {"name":"имя музыкального файла"}<br>
    * Возвращается json строка:  путь до песни<br>
    *  false в случае ошибки<br>
    * @param int $id id музыки
    * @return array
    */
    public function getOneMusic($name) 
    {    
        /*
          $path = "content/music/";
          $musics = scandir($path);
          array_shift($musics);
          array_shift($musics);
          $answer = [];
          foreach ($musics as $result) {
            $result = str_replace(' ', '', $result);
            $answer[$result] = SERVER . 'api/content/music/' . $result;
          }
          return $answer[$name];
         */
        $query = "SELECT * FROM audio WHERE name=:name";
        $stmt = $this->db->prepare($query);
        $stmt->bindValue(':name', $name, PDO::PARAM_STR);
        $stmt->execute();
        $items = array();
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
/*
        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {	

            $items = (object) array(
                    "id"      => (int)$row['id'],
                    "name"    => $row['name'],
                    "link"    => SERVER . 'api/content/music/' . $row['name'],
            );       
        }	
        */
        if ($row) {
            $link = str_replace(' ', '%20', $row['name']);
            return SERVER . 'api/content/music/' . $link; 
        } else {
            return false;
        }
         
    }
    
    /**
    * ДОБАВИТЬ ИЛИ СНЯТЬ МОНЕТЫ ОПРЕДЕЛЕННОМУ ПОЛЬЗОВАТЕЛЮ
    * 
    * POST запрос на этот адрес<br>
    *  baseURL/api/Api.php?action=addMoney&token=secret_token<br>
    * передать json параметр {"token": "авторизационный токен", "action": "add(добавить) или remove(снять)",<br>
    * "count": "количество монет для добавления или снятия"}<br>
    * Возвращается: количество монет после операции<br>
    * false если произошло не успешно<br>
    * @param string $token авторизационный токен
    *  @param string $action add(добавить) или remove(снять)
    *  @param string $count авторизационный токен
    * @return bool | integer;
    */
    public function addMoney($token, $action, $count) 
    {    			
        $query="SELECT * FROM users WHERE token=:token";
        $stmt = $this->db->prepare($query);
        $stmt->bindValue(':token', $token, PDO::PARAM_STR);
        $stmt->execute();
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if ($row) {
            if ($action == 'add') {
                $count = $row['money'] + $count;

            } elseif ($action == 'remove') {
                $count = $row['money'] - $count;
            } else {
                return false;
            }
            $query = "UPDATE `users` SET `money` = $count WHERE `token` = :token";
            $stmt = $this->db->prepare($query);
            $stmt->bindValue(':token', $token, PDO::PARAM_STR);
            $stmt->execute();
            return $count;

        } else {
            return false;
        }			
    }
        
    /**
	* ПОЛУЧИТЬ ПАЗЛЫ ДЛЯ ОДНОЙ КАРТИНКИ
	* 
	* POST запрос на этот адрес<br>
	*  baseURL/api/Api.php?action=getPazzlessOneImage&token=secret_token<br>
	* передать json параметр {"images_id":"id картинки"}<br>
	* Возвращается массив: id - id картинки, picture - путь до картинки, <br>
        * @param integer $id id картинки
	* @param string $picture путь до картинки
	* @return array
	*/
	public function getPazzlessOneImage($images_id) 
	{    
		$query = "SELECT * FROM puzzles WHERE images_id = {$images_id}";
        $stmt = $this->db->prepare($query);
        $stmt->execute();
        $items = [];
       	while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {	
              $items[] = (object) array(
                    "id"      => (int)$row['id'],
                    "picture"    => SERVER . 'api/content/puzzles/' . $row['name'],

                );       
            }
        return $items;
	}
    /*
    public function audio() 
	{    	
            
		$path = "content/music/";
		$musics = scandir($path);
		array_shift($musics);
		array_shift($musics);
		$answer = [];
		foreach ($musics as $result) {
			$sql=
				'INSERT INTO audio(`name`) ' .
				'VALUES (:name)';
			$stmt = $this->db->prepare($sql);
			$stmt->bindValue(':name', $result, PDO::PARAM_STR);
			 $stmt->execute();
			
		}
           
		//return $answer[$id][1];
                return 'true';
	}
     */
     
     
	public function foto() 
	{    	
            
		$path = "content/images2/";
		$musics = scandir($path);
		array_shift($musics);
		array_shift($musics);
		$answer = [];
		foreach ($musics as $result) {
			$sql=
				'INSERT INTO images(`name`,`price`,`catalogs_id`) ' .
				'VALUES (:name, 0, 16)';
			$stmt = $this->db->prepare($sql);
			$stmt->bindValue(':name', $result, PDO::PARAM_STR);
			 $stmt->execute();
			
		}
           
		//return $answer[$id][1];
                return 'true';
	}
        
        /*
        public function delCategoryWithFoto($catalogs_id) 
	{    	
            
            $query = 'SELECT * FROM images WHERE catalogs_id = :catalogs_id';
            $smtp = $this->db->prepare($query);
            $smtp->bindValue('catalogs_id', $catalogs_id, PDO::PARAM_INT);
            $smtp->execute();
             $items = [];
       	while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {	
              $items[] = (object) array(
             //       "id"      => (int)$row['id'],
               //     "picture"    => SERVER . 'api/content/puzzles/' . $row['name'],

                );       
            }
        return $items;
            
            
		$path = "content/images2/";
		$musics = scandir($path);
		array_shift($musics);
		array_shift($musics);
		$answer = [];
		foreach ($musics as $result) {
			$sql=
				'INSERT INTO images(`name`,`price`,`catalogs_id`) ' .
				'VALUES (:name, 0, 7)';
			$stmt = $this->db->prepare($sql);
			$stmt->bindValue(':name', $result, PDO::PARAM_STR);
			 $stmt->execute();
			
		}
           
		//return $answer[$id][1];
                return 'true';
	}
  
      */
       

    
	/**
	* ПОСТАВИТЬ (МЕСЯЦ ГОД) ИЛИ СНЯТЬ ПЛАТНУЮ ПОДПИСКУ(NULL) ПОЛЬЗОВАТЕЛЯ
	* 
	* POST запрос на этот адрес<br>
	*  baseURL/api/Api.php?action=subscriptionUser&token=secret_token  <br>
	* передать json параметр {"name":"имя пользователя", "type":"month(месяц), year(год) или null(снять подписку)"}<br>
	* 'true' если регистрация прошла успешно<br>
	* 'false' если регистрация не произошла<br>
	* @param string $name имя пользователя
	* @param string $type month(месяц), year(год) или null(снять подписку)
	* @return bool
	*/
    public function subscriptionUser($token, $type) 
    {        
		if ($type == 'month') {
			$lasting_subscription = strtotime("+30 days");
		} elseif ($type == 'year') {
			$lasting_subscription = strtotime("+365 days");
		} elseif ($type == 'null') {
			$lasting_subscription = NULL;
		}
		
		$sql = "UPDATE users SET subscription =:subscription WHERE `token` LIKE :token";
		$stmt = $this->db->prepare($sql);
		$stmt->bindValue(':token', $token, PDO::PARAM_STR);
		$stmt->bindValue(':subscription', $lasting_subscription, PDO::PARAM_STR);
		return $stmt->execute();      
    }
	
	/**
	* ПРОВЕРИТЬ НАЛИЧИЕ ПЛАТНОЙ ПОДПИСКИ ПОЛЬЗОВАТЕЛЯ 
	* 
	* POST запрос на этот адрес<br>
	*  baseURL/api/Api.php?action=testSubscriptionUser&token=secret_token  <br>
	* передать json параметр {"token":"авторизационный токен"}<br>
	* 'true' если регистрация прошла успешно<br>
	* 'false' если регистрация не произошла<br>
	* @param string $name имя пользователя
	* @return bool
	*/
    public function testSubscriptionUser($token) 
    {     
		$query = "SELECT * FROM users WHERE token = '$token'";
		$stmt = $this->db->prepare($query);
		$stmt->execute();
		$row = $stmt->fetch(PDO::FETCH_ASSOC);

		if (!empty($row)) {	
			$is_subscriptionUserCategory = false;

			if (!empty($row['subscription'])) {	
			
				if (time() < $row['subscription']) {
					$is_subscriptionUserCategory = true;
				} else {
					$is_subscriptionUserCategory = false;
				}
			}
			return $is_subscriptionUserCategory;
		}     
    }
    
    /**
    * @ignore
    */
    public function getLanguages() 
    {     
        $query = "SELECT * FROM languages";
        $stmt = $this->db->prepare($query);
        $stmt->execute();
        $items = array();

        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {	
            $items[$row['name']] = $row;       
        }			
        return $items;
    }
    
     /**
    * ПОЛУЧИТЬ предложение дня
    * 
    * POST запрос на этот адрес<br>
    *  baseURL/api/Api.php?action=getOfferDay&token=secret_token  <br>
    * передать json параметр {"language":"ru или en"}<br>
    * Возвращается json строка: id - id каталога, name - имя каталога<br>
    *  image - путь к картинке<br>
    * @return string
    */
    public function getOfferDay($language) 
    {    
        $offerday = gmdate('Y-m-d');
        $query = "SELECT * FROM offerday WHERE offerday = '$offerday'";
        $smtp = $this->db->prepare($query);
        $smtp->execute();
        $row = $smtp->fetch(PDO::FETCH_ASSOC);

        if (empty($row)) {  
                                
            $sql = 'DELETE FROM offerday';
            $stmt = $this->db->prepare($sql);
            $stmt->execute(); 
                      
            $query = "SELECT * FROM catalogs WHERE `price` > '0' ORDER BY rand() LIMIT 3";
            $stmt = $this->db->prepare($query);
            $stmt->execute();
            $items = array();

            $allLanguage = $this->getLanguages();
            $procent = rand(15, 65);
            
            while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {	
 
                $items[] = (object) array(
                    "id"      => (int)$row['id'],
                    "name"    => $row['name'],
                    "price"    => $row['price'],
                    "image"    => SERVER . 'api/content/catalogs/' . $row['image'],
                    "procent" => $procent
                );       
            }			
            $catalogs = serialize($items);
             
            $sql = 'INSERT INTO offerday(`offerday`, `catalogs`) ' .
                'VALUES (:offerday, :catalogs)';
            $stmt = $this->db->prepare($sql);
            $stmt->bindValue(':offerday', $offerday, PDO::PARAM_STR);
            $stmt->bindValue(':catalogs', $catalogs, PDO::PARAM_STR);
            $stmt->execute(); 
     
        } 
                      
        $query = "SELECT * FROM offerday";
        $stmt = $this->db->prepare($query);
        $stmt->execute();
        $items = array();

        $allLanguage = $this->getLanguages();

        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {	
            
           $catalogs = unserialize($row['catalogs']);
           $catalogs_ = [];
            foreach ($catalogs as $key => $result) {
                
                $procent = $result->procent;
                $price += $result->price;
                $catalogs_[] = [
                    'id' => $result->id,
                     'name' => $allLanguage[$result->name][$language],
                     'image' => $result->image,
                    'price' => $result->price,
                ];
           }
           
            $items = (object) array(
                "catalogs"    => $catalogs_,
                "final" => strtotime(gmdate('Y-m-d 23:59:59')),
                "server_time_grinvich" => gmdate('Y-m-d H:i:s'),
                'price' => (string)$price,
                'procent' => (string)(100 - $procent),
                'pricenew' => (string)ceil(($price /100) * $procent),
            );       
        }			
        return $items;                 
    }
    
     /**
    * ДОСТУПНА ЛИ АКЦИЯ ДЛЯ КОНКРЕТНОГО ПОЛЬЗОВАТЕЛЯ
    * 
    * POST запрос на этот адрес<br>
    *  baseURL/api/Api.php?action=getSaleAvailability&token=secret_token  <br>
    * передать json параметр {"token":"авторизационный токен"}<br>
    * Возвращается json строка: id - id каталога, name - имя каталога<br>
    *  image - путь к картинке<br>
    * @return string
    */
    public function getSaleAvailability($token) 
    {  
        $query = "SELECT * FROM users WHERE token = '$token'";
        $stmt = $this->db->prepare($query);
        $stmt->execute();
        $row = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!empty($row)) {
            $user_id = $row['id'];
            $query = "SELECT * FROM users_catalogs WHERE user_id = '$user_id'";
            $stmt_ = $this->db->prepare($query);
            $stmt_->execute();

            while ($row = $stmt_->fetch(PDO::FETCH_ASSOC)) {	
                $catalogs_id[] = $row['catalog_id'];      
            }
        }
                       
        $flag = true;
        $query = "SELECT * FROM offerday";
        $stmt = $this->db->prepare($query);
        $stmt->execute();
        $items = array();

        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {	
            
            $catalogs = unserialize($row['catalogs']);
            $catalogs_ = [];
            
            foreach ($catalogs as $key => $result) {
                
                if (in_array($result->id, $catalogs_id)) {
                    $flag = false;
                }
           }
        }			
        return $flag;       
    }
     
    /**
    * КУПИТЬ КАТАЛОГ 
    * 
    * POST запрос на этот адрес<br>
    *  baseURL/api/Api.php?action=buyCatalog&token=secret_token  <br>
    * передать json параметр {"token":"авторизационный токен", "$catalog_id_array":"массив id каталогов", "catalog_price":"цена всех каталогов"}<br>
    * Возвращается json строка: TRUE или FALSE<br>
    * @return string
    */
    public function buyCatalog($token, $catalog_id_array, $catalog_price) 
    {     
     
        $query="SELECT * FROM users WHERE token=:token";
        $stmt = $this->db->prepare($query);
        $stmt->bindValue(':token', $token, PDO::PARAM_STR);
        $stmt->execute();
        $items = array();
        $row = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!empty($row)) {  
            if ($row['money'] < $catalog_price) {
                return false;
            } else {
                foreach ($catalog_id_array as $result) {
                 
                    $sql = 'INSERT INTO users_catalogs(`user_id`, `catalog_id`) ' .
                                'VALUES (:user_id, :catalog_id)';
                            $stmt = $this->db->prepare($sql);
                            $stmt->bindValue(':user_id', $row['id'], PDO::PARAM_INT);
                            $stmt->bindValue(':catalog_id', $result, PDO::PARAM_INT);
                            $res = $stmt->execute();
                } 
                return $res;
            }
        }        
    }	
}