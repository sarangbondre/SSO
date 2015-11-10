<?php

// src/AppBundle/Controller/RedirectController.php
namespace AppBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Cookie;
use Symfony\Component\HttpFoundation\RedirectResponse;

/**
 * @Route("/redirect")
 */
class RedirectController extends Controller
{
    /**
     * @Route("/")
     */
    public function authAction(\Symfony\Component\HttpFoundation\Request $request)
    {
        $ssoToken = $this->container->getParameter('ssotoken');
        $ssoId = $this->container->getParameter('ssoid');
        $expireTime = time()+$this->container->getParameter('cookie_expire_time');
        $expirePath = $this->container->getParameter('cookie_path');
        $loginURL = $this->container->getParameter('login_url');
        $baseApiURL = $this->container->getParameter('base_login_api_url');
        $redirectURL = $request->getUri();
        $message = 'Congrats you are successfully logged inn.';

        if ($request->getMethod() == 'POST') {            
            $data = $request->request->all();
            $id = $data['id'];
            $token = $data['token'];            
            $int = $expireTime;
            setcookie($ssoId,$id,time()+$int,'/',false);
            setcookie($ssoToken,$token,time()+$int,'/',false);
        } else if(!empty($_COOKIE['ssotoken'])) {
            $id=$_COOKIE['ssoid'];
            $token=$_COOKIE['ssotoken'];
            $filters="?filters[token]=$token";
            $method = 'GET';            
            $url = $baseApiURL.$filters;
            // echo $url;
            $responseAPI = $this->CallAPI($method, $url);
            $responseAPI = json_decode($responseAPI);
            if(isset($responseAPI->status) && $responseAPI->status == 0) {
                $loginURL = $loginURL.'?redirect='.$redirectURL;
                return new RedirectResponse($loginURL);
            }
        } else {
            $loginURL = $loginURL.'?redirect='.$redirectURL;
            return new RedirectResponse($loginURL);
        }
        return $this->render(
            'redirect/success.html.twig',
            array(
                'message'  => $message,
            )
        );
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

    /**
     * @Route("/lucky/number")
     */
    public function numberAction()
    {
        $number = rand(0, 100);
        return new Response(
            '<html><body>Lucky number: '.$number.'</body></html>'
        );
    }
}

?>
