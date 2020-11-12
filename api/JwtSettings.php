<?php
    // show error reporting
    error_reporting(E_ALL);
     
    $jwtSettings = array(
        // variables used for jwt
    'key'  => "example_key",
    'iss' => "http://example.org",
    'aud' => "http://example.com",
    'cookieName' => "AuthCookie"
    );
?>