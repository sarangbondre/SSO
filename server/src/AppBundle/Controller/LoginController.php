<?php

// src/AppBundle/Controller/LoginController.php
namespace AppBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;

class LoginController extends Controller
{
    /**
     * @Route("/login")
     */
    public function indexAction(Request $request)
    {
	$errormsg = $lastUsername = $error = '';
$baseApiURL = 'http://localhost/login/web/app_dev.php/api/users';
	if ($request->getMethod() == 'POST') {		
		$data = $request->request->all();
		$lastUsername = $username = $data['_username'];
		$password = $data['_password'];
		if(empty($username) || empty($password)) {
			$errormsg = 'Username or password field cannot be empty';
			$error = '';
		} else {
			$filters="?filters[username]=$username&filters[password]=$password";
			$method = 'GET';
			$url = $baseApiURL.$filters;
			$responseAPI = $this->CallAPI($method, $url);
			if(!empty($responseAPI)) {
				$responseData = json_decode($responseAPI);
				$responseData = $responseData[0];
				$responseId = $responseData->id;
				$responseName = $responseData->name;
				$errormsg = 'Congrats you are Authorised';
				$int = 3600 * 24 * 1;
				$token = sha1($username.$password);
				$method = 'PUT';
				$data['token'] = $token;
				$url = $baseApiURL.'/'.$responseId;
				$responseAPI = $this->CallAPI($method, $url, $data);
				$cookieName = 'SSOkey';
				setcookie($cookieName,$token,time()+$int,'/',false);
				//$redirectURL = $_GET['redirect'].'?login=1';
				$redirectURL = $_GET['redirect'];
				//For curl post
				// $params = array(
				// 	"name" => $responseName,
				// 	"id" => $responseId,
				// 	"token" => $token
				// );
				return $this->render(
					'login/dummy.html.twig',
					array(
					    // last username entered by the user
					    'name' => $responseName,
					    'id'         => $responseId,
					    'token'	    => $token,
					    'url' => $redirectURL
					)
				);
				//$curlResponse = $this->httpPost($redirectURL,$params);
				//echo $curlResponse;exit;
				//return new Response($this->httpPost($redirectURL,$params));
				//return $this->redirect($redirectURL);
			} else {
				$errormsg = 'Sorry, Am not able to identify you';
			}
		}
	} else if(!empty($_COOKIE)) {
		$token = $_COOKIE['SSOkey'];
		$filters="?filters[token]=$token";
		$method = 'GET';
		$url = $baseApiURL.$filters;
		$responseAPI = $this->CallAPI($method, $url);
		if(empty($responseAPI)) {
			unset($_COOKIE['SSOkey']);
            setcookie('SSOkey', '' , time() - 3600,'/');
            $redirectURL = $_GET['redirect'];
            //$redirectURL = "http://$_SERVER[HTTP_HOST]$_SERVER[SCRIPT_NAME]";
            return $this->redirect($redirectURL);
		} else {
			$responseData = json_decode($responseAPI);
			$responseData = $responseData[0];
			$responseId = $responseData->id;
			$responseName = $responseData->name;
			$token = $responseData->token;
			$redirectURL = $_GET['redirect'];			
			return $this->render(
				'login/dummy.html.twig',
				array(
				    // last username entered by the user
				    'name' => $responseName,
				    'id'         => $responseId,
				    'token'	    => $token,
				    'url' => $redirectURL
				));
		}
	}
	return $this->render(
			'login/index.html.twig',
			array(
			    // last username entered by the user
			    'last_username' => $lastUsername,
			    'error'         => $error,
			    'errormsg'	    => $errormsg,
			)
		);
    }

    function httpPost($url,$params)
	{
	  $postData = '';
	   //create name value pairs seperated by &
	   foreach($params as $k => $v) 
	   { 
	      $postData .= $k . '='.$v.'&'; 
	   }
	   rtrim($postData, '&');
	 
	    $ch = curl_init();  
	 
	    curl_setopt($ch,CURLOPT_URL,$url);
	    curl_setopt($ch,CURLOPT_RETURNTRANSFER,true);
	    curl_setopt($ch,CURLOPT_HEADER, false); 
	    curl_setopt($ch, CURLOPT_POST, count($postData));
        curl_setopt($ch, CURLOPT_POSTFIELDS, $postData);
	 	
	    $output=curl_exec($ch);
	 
	    curl_close($ch);
	    return $output;
	 
	}

    /**
     * @Route("/loginauth")
     */
    public function loginauthAction(Request $request)
    {    	
	$redirectURL = $_GET['redirect'];
	if(!empty($_COOKIE['SSOkey'])) {
		$baseApiURL = 'http://localhost/login/web/app_dev.php/api/users';
		$token = $_COOKIE['SSOkey'];
		$filters="?filters[token]=$token";
		$method = 'GET';
		$url = $baseApiURL.$filters;
		$responseAPI = $this->CallAPI($method, $url);
		if(!empty($responseAPI)) {

			$responseData = json_decode($responseAPI);
			$responseData = $responseData[0];
		    $responseId = $responseData->id;
			$responseName = $responseData->name;
			$token = $responseData->token;

			return $this->render(
				'login/dummy.html.twig',
				array(
				    // last username entered by the user
				    'name' => $responseName,
				    'id'         => $responseId,
				    'token'	    => $token,
				    'url' => $redirectURL
				));
		} else {
			$redirectURL = "http://$_SERVER[HTTP_HOST]$_SERVER[SCRIPT_NAME]/login?redirect=".$redirectURL;
		}
        } else {
            $redirectURL = "http://$_SERVER[HTTP_HOST]$_SERVER[SCRIPT_NAME]/login?redirect=".$redirectURL;
	}
	return $this->redirect($redirectURL);
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
//                curl_setopt($curl, CURLOPT_PUT, 1);
		curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
		curl_setopt($curl, CURLOPT_CUSTOMREQUEST, "PUT");
		if ($data)
                    curl_setopt($curl, CURLOPT_POSTFIELDS, http_build_query($data));
                break;
            default:
                if ($data)
                    $url = sprintf("%s?%s", $url, http_build_query($data));
        }

        // Optional Authentication:
//        curl_setopt($curl, CURLOPT_HTTPAUTH, CURLAUTH_BASIC);
//        curl_setopt($curl, CURLOPT_USERPWD, "username:password");

        curl_setopt($curl, CURLOPT_URL, $url);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);

        $result = curl_exec($curl);

        curl_close($curl);

        return $result;
    }

	/**
	 * @Route("/logout", name="logout")
	 */
	public function logoutAction() {
		if ($request->getMethod() == 'GET') {
			$data = $request->request->all();
			echo 'Something<pre>';print_r($data);exit;
		}		
	}

    /**
     * @Route("/login_check", name="login_check")
     */
    public function loginCheckAction(Request $request)
    {
    }
}

?>