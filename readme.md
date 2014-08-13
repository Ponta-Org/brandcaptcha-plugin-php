#Using BrandCAPTCHA with PHP

The Brand[CAPTCHA](http://en.wikipedia.org/wiki/Captcha) PHP Library provides a simple way to place a BrandCAPTCHA on your PHP website, helping you stop bots from abusing it. The library wraps the BrandCAPTCHA API.

To use BrandCAPTCHA with PHP, you will only need to include the file "brandcaptchalib.php". The other files are examples, readme, etc, they don't affect functionality.

##Quick Start

After you've obtained your API keys, below are basic instructions for installing BrandCAPTCHA on your site.

##Client Side

If you want to use the PHP library to display the BrandCAPTCHA widget, you'll need to insert this snippet of code inside the `<form>` element where the BrandCAPTCHA widget will be placed:

```php
require_once('brandcaptchalib.php');
$publickey = "your_public_key";
//the response from BrandCAPTCHA
$resp = null;
//the error code from BrandCAPTCHA, if any
$error = null;
echo brandcaptcha_get_html($publickey, $error);
```

With the code, your form might look something like this:

```php
<html>
    <body>
        <form action="process_form.php" method="post">

            <?php

                require_once('brandcaptchalib.php');
                $publickey = "your_public_key";
                echo brandcaptcha_get_html($publickey, $error);

            ?>
                                            
            <br/>
            <input type="submit" value="submit" />
        </form>
    </body>
</html>
```

Don't forget to set $publickey by replacing your_public_key with your API public key.

Note that the value of the `action` attribute is `process_form.php`. Now, `process_form.php` is the destination file in which the values of this form are submitted to. So you will need a file `process_form.php` in the same location as the client html.

The `require_once` function in the example above expects `brandcaptchalib.php` to be in the same directory as your form file. If it is in another directory, you must link it appropriately. For example if your `brandcaptchalib.php` is in the directory called "brandcaptcha" that is on the same level as your form file, the function will look like this: `require_once('brandcaptcha/brandcaptchalib.php')`.

##Server Side

The following code should be placed at the top of the `process_form.php`:

```php
<?php

require_once('brandcaptchalib.php');
$privatekey = "your_private_key";

// the response from BrandCAPTCHA
$resp = null;
// the error code from BrandCAPTCHA, if any
$error = null;

// was there a BrandCAPTCHA response?
if ( isset($_POST["brand_cap_answer"]) && $_POST["brand_cap_answer"]) {
    $resp = brandcaptcha_check_answer ($privatekey,
        $_SERVER["REMOTE_ADDR"],
        $_POST["brand_cap_challenge"],
        $_POST["brand_cap_answer"]);

    if ($resp->is_valid) {
        // Your code here to handle a successful verification
        echo "You got it!";
    } 
    else {
        // set the error code so that we can display it
        $error = $resp->error;
    }
}

?>
```

                                                                                                                                                                                                 In the code above:

                                                                                                                                                                                                 * `brandcaptcha_check_answer` returns an object that represents whether the user successfully completed the challenge.
                                                                                                                                                                                                 * If `$resp->is_valid` is `true` then the captcha challenge was correctly completed and you should continue with form processing.
                                                                                                                                                                                                 * If `$resp->is_valid` is `false` then the user failed to provide the correct captcha text and you should redisplay the form to allow them another attempt. In this case `$resp->error` will be an error code that can be provided to `brandcaptcha_get_html`. Passing the error code makes the BrandCAPTCHA control display a message explaining that the user entered the text incorrectly and should try again.

                                                                                                                                                                                                 Notice that this code is asking for the **private key**, which should not be confused with the public key.

                                                                                                                                                                                                 Also make sure your form is set to get the form variables using `$_POST` instead of `$_REQUEST` and that the form itself is using the POST method.

                                                                                                                                                                                                 That's it! BrandCAPTCHA should now be working on your site.

