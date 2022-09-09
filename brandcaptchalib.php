<?php

/*
 * Copyright (c) 2015 by PontaMedia 
 * 
 * This is a PHP library that handles calling BrandCaptcha.
 *    - Documentation and latest version
 *          http://www.pontamedia.com/
 */

define("BRANDCAPTCHA_API_HOST", "api.ponta.co");

define("BRANDCAPTCHA_VERIFY_PATH", "/verify.php");
define("BRANDCAPTCHA_CHALLENGE_PATH", "/challenge.php");

function _brandcaptcha_qsencode ($data) {
        $req = "";
        foreach ( $data as $key => $value )
                $req .= $key . '=' . urlencode( stripslashes($value) ) . '&';

        // Cut the last '&'
        $req=substr($req,0,strlen($req)-1);
        return $req;
}

/**
 * Submits an HTTP POST to a BrandCaptcha server
 * @param string $host
 * @param string $path
 * @param array $data
 * @param int port
 * @return array response
 */
function _brandcaptcha_http_post($host, $path, $data, $port = 80) {

        $req = _brandcaptcha_qsencode ($data);

        $http_request  = "POST $path HTTP/1.0\r\n";
        $http_request .= "Host: $host\r\n";
        $http_request .= "Content-Type: application/x-www-form-urlencoded;\r\n";
        $http_request .= "Content-Length: " . strlen($req) . "\r\n";
        $http_request .= "User-Agent: brandcaptcha/PHP\r\n";
        $http_request .= "\r\n";
        $http_request .= $req;

        $response = '';
        if( false == ( $fs = @fsockopen(gethostbyname($host), $port, $errno, $errstr, 10) ) ) {
                die ('Could not open socket');
        }

        fwrite($fs, $http_request);

        while ( !feof($fs) )
                $response .= fgets($fs, 1160); // One TCP-IP packet
        fclose($fs);

        $response = explode("\r\n\r\n", $response, 2);

        return $response;
}



/**
 * Gets the challenge HTML (javascript).
 * This is called from the browser, and the resulting BrandCaptcha HTML widget
 * is embedded within the HTML form it was called from.
 * @param string $pubkey A public key for BrandCaptcha
 * @param string $error The error given by BrandCaptcha (optional, default is null)

 * @return string - The HTML to be embedded in the user's form.
 */
function brandcaptcha_get_html ($pubkey, $error = null, $use_ssl = false, $tags = '')
{
        if ($pubkey == null || $pubkey == '') {
                die ("To use BrandCaptcha you must get an API Key");
        }

        if ($use_ssl) {
                $server = "https://". BRANDCAPTCHA_API_HOST . BRANDCAPTCHA_CHALLENGE_PATH;
        } else {
                $server = "//". BRANDCAPTCHA_API_HOST . BRANDCAPTCHA_CHALLENGE_PATH;
        }

        $errorpart = "";
        if ($error) {
           $errorpart = "&amp;error=" . $error;
        }

        $tagspart = "";
        if (!empty($tags)) {
           $tagspart = "&amp;tags=" . $tags;
        }

        return '<script type="text/javascript" src="'. $server . '?k=' . $pubkey . $errorpart . $tagspart . '"></script>';
}




/**
 * A BrandCaptchaResponse is returned from brandcaptcha_check_answer()
 */
class BrandCaptchaResponse {
        var $is_valid;
        var $error;
}
/**
  * Calls an HTTP POST function to verify if the user's guess was correct
  * @param string $privkey
  * @param string $remoteip
  * @param string $challenge
  * @param string $response
  * @param array $extra_params an array of extra variables to post to the server
  * @return BrandCaptchaResponse
  */
function brandcaptcha_check_answer ($privkey, $remoteip, $challenge, $response, $extra_params = array())
{
        if ($privkey == null || $privkey == '') {
                die ("To use BrandCaptcha you must get an API key");
        }

        if ($remoteip == null || $remoteip == '') {
                die ("For security reasons, you must pass the remote ip to BrandCaptcha");
        }

        //discard spam submissions
        if ($challenge == null || strlen($challenge) == 0 || $response == null || strlen($response) == 0) {
                $brandcaptcha_response = new BrandCaptchaResponse();
                $brandcaptcha_response->is_valid = false;
                $brandcaptcha_response->error = 'incorrect-captcha-sol';
                return $brandcaptcha_response;
        }

        $response = _brandcaptcha_http_post (BRANDCAPTCHA_API_HOST, BRANDCAPTCHA_VERIFY_PATH,
                                          array (
                                                 'privatekey' => $privkey,
                                                 'remoteip' => $remoteip,
                                                 'challenge' => $challenge,
                                                 'response' => $response
                                                 ) + $extra_params
                                          );

        $answers = explode ("\n", $response [1]);
        $brandcaptcha_response = new BrandCaptchaResponse();

        if (trim ($answers [0]) == 'true') {
                $brandcaptcha_response->is_valid = true;
        }
        else {
                $brandcaptcha_response->is_valid = false;
                $brandcaptcha_response->error = $answers [1];
        }
        return $brandcaptcha_response;

}
