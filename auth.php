<?php
	if (!include_once './config.php')
	{
		# We're OK if this dies because we can try loading from the environment, which The Cloud will do
		$requiredCookie = getenv("requiredCookie");
		$appid = getenv("appid");
		$appsecret = getenv("appsecret");
		$subscription = getenv("subscription");
		$tenant = getenv("tenant");
		$keyvaultname = getenv("keyvaultname");
		$overridevalue = getenv("overridevalue");
	}

	if (!include_once './commonfunctions.php')
	{
		# If this fails, exit because we need those functions
		echo "Error loading common functions module.";
		die;
	}

	$result = false;
	if (isset($_POST["checkauth"]))
	{
		// We've entered by asking for authentication so make sure
		$checkvalue = $_POST["sentstring"];
		$thesecret = getAzureKeyVaultValue($overridevalue, $keyvaultname, $appid, $tenant, $subscription, $appsecret);

		if ($checkvalue = $thesecret)
		{
			// The proper value has been entered, store the cookie
			$result = setcookie("accesscontrol", $requiredCookie, time() + (86400 * 365), "/", $_SERVER["HTTP_HOST"]);
			if ($result)
			{
				$message = "Access granted.";
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
<?
	if ($result)
	{
		echo $message;
	} else {
		if ($message)
		{
			echo $message . "<br />";
		}
?>
<form method="post" action="<?php baseurl() . "/auth.php"; ?>">
	Enter access code: <input type="text" name="sentstring"><br /><br />
	<input type="submit" value="Check for access">
	<input type="hidden" name="checkauth" value="checkauth">
</form>
<?php
	}
?>
</body>
</html>