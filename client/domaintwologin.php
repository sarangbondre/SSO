<!DOCTYPE html>
<html>
<head>
<title>Domain TWO</title>
</head>

<body>
<h2>Login Form Domain TWO</h2>
<?php

if(!empty($_POST)) {
	$id = $_POST['id'];
	$token = $_POST['token'];
	$int = 3600 * 24 * 1;
	setcookie('ssoid',$id,time()+$int,'/',false);
	setcookie('ssotoken',$token,time()+$int,'/',false);
	$postredirect = "http://domaintwo.local/domaintwologinsuccess.php";\
	header('Location:'.$postredirect);
	//header('Location: http://www.example.com/');
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
			$redirectURL = "http://domaintwo.local/domaintwologin.php";
			$loginURL = 'http://localhost/login/web/app_dev.php/loginauth?redirect='.$redirectURL;
			header('Location:'.$loginURL);
		}		
	} else {
		//$redirectURL = "http://$_SERVER[HTTP_HOST]$_SERVER[SCRIPT_NAME]";
		$redirectURL = "http://domaintwo.local/domaintwologin.php";
		$loginURL = 'http://localhost/login/web/app_dev.php/loginauth?redirect='.$redirectURL;
		header('Location:'.$loginURL);
	}
}

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
