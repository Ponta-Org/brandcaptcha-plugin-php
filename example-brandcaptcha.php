<html>
  <body>
    <form action="" method="post">
<?php

require_once('brandcaptchalib.php');


$publickey = "your_public_key";
$privatekey = "your_private_key";

# the response from BrandCaptcha
$resp = null;
# the error code from BrandCaptcha, if any
$error = null;
# Use ssl? Change this parameter using true as value
$use_ssl = false;

# was there a BrandCaptcha response?
if ( isset($_POST["brand_cap_answer"]) && $_POST["brand_cap_answer"]) {
        $resp = brandcaptcha_check_answer ($privatekey,
                                        $_SERVER["REMOTE_ADDR"],
                                        $_POST["brand_cap_challenge"],
                                        $_POST["brand_cap_answer"]);
        if ($resp->is_valid) {
                echo "You got it!";
        } else {
                # set the error code so that we can display it
                $error = $resp->error;
        }
      
}
echo brandcaptcha_get_html($publickey, $error, $use_ssl);

?>
    <br/>
    <input type="submit" value="submit" />
    </form>
  </body>
</html>
