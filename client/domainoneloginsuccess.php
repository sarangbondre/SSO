<?php



if(!empty($_GET['logout'])) {
	$id = $_GET['logout'];
	unset($_COOKIE['ssoid']);
	setcookie('ssoid', '' , time() - 3600,'/');
	unset($_COOKIE['ssotoken']);
	setcookie('ssotoken', '' , time() - 3600,'/');
	$baseApiURL = 'http://localhost/login/web/app_dev.php/api/loginapis';
	$filters = "?filters[logout]=$id";
    $url = $baseApiURL.$filters;
    $method = 'GET';
    //echo $url;exit;
    $responseAPI = CallAPI($method, $url);	
    
	// $redirectURL = "http://$_SERVER[HTTP_HOST]$_SERVER[SCRIPT_NAME]";
	$redirectURL = "http://domainone.local/domainonelogin.php";
	header('Location:'.$redirectURL);
	$message = "You have logged out successfully <br><br> <a href='".$redirectURL."'>Click to Login</a>";
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
			header('Location:'.$redirectURL);
		}
	} else {
		$redirectURL = "http://domainone.local/domainonelogin.php";
		header('Location:'.$redirectURL);	
	}

echo "I have successfully login to domain one<br><br> <a href='?logout=".$id."'>Logout</a>";
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
	// $result = json_decode($result);
	// if(!empty($result->success)) {
	// 	$result = $result;
	// } else {
	// 	$result = json_decode($result);
	// }
	return $result;
}

?>
