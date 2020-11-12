<?php

class e_validate_exception extends Exception {
    
}

class Validator
{
    const NAME_REGEX = "/^[A-ZА-ЯЁ0-9\-\s\.\,\)\(\\\"\']+$/iu";
    const RIGHT_NAME_REGEX = "/^[A-Z0-9\_]+$/iu";
    const TOKEN_REGEX = "/^[A-Z0-9]+$/ui";
    const TIME_REGEX = "/^[0-9\-\s\:]+$/";

    static public function id($id, $name, $allowedNull = false) {
        return self::numeric($id, $name, $allowedNull);
    }
    
    static public function numeric($value, $name, $allowedNull = false) {
        if (is_numeric($value) || ($allowedNull && ($value == NULL))) {
            return true;
        } else {
            throw new e_validate_exception("$name is not valid");
        }
    }
    
    static public function validate_name($name) 
    {
        return preg_match(self::NAME_REGEX, $name);
    }

    static public function validate_password($pass)
    {
        return is_string($pass) && $pass != '';
    }

    static public function validate_right_name($right_name) 
    {
        return preg_match(self::RIGHT_NAME_REGEX, $right_name);
    }

    static public function validate_token($token) 
    {
        return preg_match(self::TOKEN_REGEX, $token);
    }

    static public function validate_time($time)
    {
        return preg_match(self::TIME_REGEX, $time);
    }

    static public function validate_email($email)
    {
        return filter_var($email, FILTER_VALIDATE_EMAIL);
    }

    static public function validate_ip($ip) 
    {
        return filter_var($ip, FILTER_VALIDATE_IP);
    }
}

?>