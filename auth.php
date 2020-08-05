<?php
    if (!include_once './config.php')
    {
        // We're OK if this dies because we can try loading from the environment, which The Cloud will do
        $requiredCookie = getenv("requiredCookie");
        $appid = getenv("appid");
        $appsecret = getenv("appsecret");
        $subscription = getenv("subscription");
        $tenant = getenv("tenant");
        $keyvaultname = getenv("keyvaultname");
        $overridevalue = getenv("overridevalue");
        $installtype = getenv("installtype");
    }

    if (!include_once './commonfunctions.php')
    {
        // If this fails, exit because we need those functions
        echo "Error loading common functions module.";
        die;
    }

    $result = false;

    if (isset($_POST["checkauth"]))
    {
        // We've entered by asking for authentication so make sure
        //
        // We have to do the auth check first because we set a cookie if successful

        $checkvalue = $_POST["sentstring"];
        switch ($installtype)
        {
            case "onprem":
                $thesecret = $overridevalue;
                break;
            case "azure":
                $thesecret = getAzureKeyVaultValue($overridevalue, $keyvaultname, $appid, $tenant, $subscription, $appsecret);
                break;
            default:
                $thesecret = $overridevalue;
        }

        if ($checkvalue == $thesecret)
        {
            // The proper value has been entered, store the cookie
            $result = setcookie("accesscontrol", $requiredCookie, time() + (86400 * 365), "/");
            if ($result)
            {
                $message = "Access granted.  <a href=\"/\">Click here to return</a>\r\n";
            } else {
                $message = "Cookie not set.";
            }
        } else {
            $result = false;
            $message = "Error.";
        }
    }
?>
<html>
<head>
    <title>Slow Web Access</title>
    <meta name="viewport" content="width=device-width; initial-scale=1.0; maximum-scale=1.0; user-scalable=0;" />
</head>

<body>
<?php
    if ($result)
    {
        echo $message;
    } else {
        if ($message)
        {
            echo $message . "<br /><br /><a href=\"/\">Click here to go back home</a>\r\n";
        }
?>
<form method="post" action="<?php baseurl() . "/auth.php"; ?>">
    Enter access code: <input type="text" name="sentstring"><br /><br />
    <input type="submit" value="Check for access">
    <input type="hidden" name="checkauth" value="checkauth">
</form>
<?php

        echo "<a href=\"https://" . $_SERVER["HTTP_HOST"] . "/auth.php\">Click here for encrypted access</a>\r\n";
    }
?>
</body>
</html>