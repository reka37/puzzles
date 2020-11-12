<?php
require_once('Crypt/RSA.php');

class TokenValidator
{
  function Base64UrlToBase64($input)
  {
        $remainder = strlen($input) % 4;
        
        if ($remainder) {
            $padlen = 4 - $remainder;
            $input .= str_repeat('=', $padlen);
        }
        return base64_decode(strtr($input, '-_', '+/'));
  }

  public function ValidateToken($token)
  {
    try{
    
    $token = self::Base64UrlToBase64($token);
    
    $tokenParts = explode(".", $token);
    $tokenHeader = base64_decode($tokenParts[0]);

    $kid = json_decode($tokenHeader, true)['kid'];

    $key = $this->GetSignKey($kid);

    $sig = self::Base64UrlToBase64($tokenParts[2]);
    
    
    $rsa = new Crypt_RSA();
     
    $modulus = $key['n'];
    $exponent = $key['e'];
    
    $public = [
      'n' => new Math_BigInteger(self::Base64UrlToBase64($modulus), 256),
      'e' => new Math_BigInteger(self::Base64UrlToBase64($exponent), 256),
    ];
    
    $rsa->loadKey($public);
    
    $rsa->setHash('sha256');
    $rsa->setSignatureMode(CRYPT_RSA_SIGNATURE_PKCS1);
    $hash = new Crypt_Hash('sha256');
     
    return array("success" => $rsa->verify($tokenParts[0] . '.' . $tokenParts[1], $sig), "userInfo" => json_decode(self::Base64UrlToBase64($tokenParts[1]), true));
        
    }catch (Exception $e){
        return false;
    }
  }

  function GetSignKey($kid)
  {
    $ch = curl_init("https://appleid.apple.com/auth/keys");
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

    $response = curl_exec($ch);

    if (curl_error($ch)) {
      trigger_error('Curl Error:' . curl_error($ch));
    }

    curl_close($ch);

    $parsedKeys = json_decode($response, true);

    $result = array_filter($parsedKeys['keys'], function ($key) use($kid) {
      return $key['kid'] == $kid;
    });

    if ($result == null) {
      trigger_error('Can not validate token.');
    }

    return reset($result);
  }
}
