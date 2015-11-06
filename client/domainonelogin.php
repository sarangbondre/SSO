<!DOCTYPE html>
<html>
<head>
<title>Domain ONE</title>
</head>

<body>
<h2>Login Form ONE</h2>
<?php

/*
 * Owner: 123.sarang@gmail.com
 * Created on: 6 Nov 2015
 * Description: This is the client file which need to be placed on the client domain OR you can write the simmiler code from this file as per your requirement in you project for the single sign on
 * Get the token from the SSO and store it in the cookie, if token does not exist then redirect it to the SSO server for the authentication procedd
 */

if(!empty($_POST)) {
	$id = $_POST['id'];
	$token = $_POST['token'];
	$int = 3600 * 24 * 1;
	setcookie('ssoid',$id,time()+$int,'/',false);
	setcookie('ssotoken',$token,time()+$int,'/',false);
	$postredirect = "http://domainone.local/domainoneloginsuccess.php";
	header('Location:'.$postredirect);
	exit;
} else {
	if(!empty($_COOKIE['ssotoken'])) {
		$id=$_COOKIE['ssoid'];
		$token=$_COOKIE['ssotoken'];
		$baseApiURL = 'http://localhost/login/web/app_dev.php/api/users';
		$filters="?filters[token]=$token";
		$method = 'GET';
		$url = $baseApiURL.$filters;
		$responseAPI = CallAPI($method, $url);
		if(empty($responseAPI)) {
			$redirectURL = "http://domainone.local/domainonelogin.php";
			$loginURL = 'http://localhost/login/web/app_dev.php/loginauth?redirect='.$redirectURL;
			header('Location:'.$loginURL);
		}		
	} else {
		$redirectURL = "http://domainone.local/domainonelogin.php";
		$loginURL = 'http://localhost/login/web/app_dev.php/loginauth?redirect='.$redirectURL;
		header('Location:'.$loginURL);
	}
}

/*
 * Function
 * Call Api is the common function called for the API, for POST, PUT and get method
 * @input param: Method (GET, PUT, POST), URL, Data in array format
 * @output: Json string
 */
function CallAPI($method, $url, $data = false)
{
	$curl = curl_init();
	switch ($method)
	{
	    case "POST":
		curl_setopt($curl, CURLOPT_POST, 1);

		if ($data)
		    curl_setopt($curl, CURLOPT_POSTFIELDS, $data);
		break;
	    case "PUT":
		curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
		curl_setopt($curl, CURLOPT_CUSTOMREQUEST, "PUT");
		if ($data)
		    curl_setopt($curl, CURLOPT_POSTFIELDS, http_build_query($data));
		break;
	    default:
		if ($data)
		    $url = sprintf("%s?%s", $url, http_build_query($data));
	}
	curl_setopt($curl, CURLOPT_URL, $url);
	curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
	$result = curl_exec($curl);
	curl_close($curl);
	return $result;
}

?>

</body>
</html>
