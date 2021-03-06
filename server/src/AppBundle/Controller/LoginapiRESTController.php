<?php

namespace AppBundle\Controller;

use AppBundle\Entity\Loginapi;
use AppBundle\Form\LoginapiType;

use FOS\RestBundle\Controller\Annotations\QueryParam;
use FOS\RestBundle\Controller\Annotations\RouteResource;
use FOS\RestBundle\Controller\Annotations\View;
use FOS\RestBundle\Request\ParamFetcherInterface;
use FOS\RestBundle\Util\Codes;
use FOS\RestBundle\View\View as FOSView;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\Form\Form;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

use Voryx\RESTGeneratorBundle\Controller\VoryxController;

/**
 * Loginapi controller.
 * @RouteResource("Loginapi")
 */
class LoginapiRESTController extends VoryxController
{
    /**
     * Get a Loginapi entity
     *
     * @View(serializerEnableMaxDepthChecks=true)
     *
     * @return Response
     *
     */
    public function getAction(Loginapi $entity)
    {
        return $entity;
    }
    /**
     * Get all Loginapi entities.
     *
     * @View(serializerEnableMaxDepthChecks=true)
     *
     * @param ParamFetcherInterface $paramFetcher
     *
     * @return Response
     *
     * @QueryParam(name="offset", requirements="\d+", nullable=true, description="Offset from which to start listing notes.")
     * @QueryParam(name="limit", requirements="\d+", default="20", description="How many notes to return.")
     * @QueryParam(name="order_by", nullable=true, array=true, description="Order by fields. Must be an array ie. &order_by[name]=ASC&order_by[description]=DESC")
     * @QueryParam(name="filters", nullable=true, array=true, description="Filter by fields. Must be an array ie. &filters[id]=3")
     */
    public function cgetAction(ParamFetcherInterface $paramFetcher)
    {

    	$filters = !is_null($paramFetcher->get('filters')) ? $paramFetcher->get('filters') : array();
        $baseApiURL = $this->container->getParameter('base_api_url');
        $cookieName = $this->container->getParameter('cookie_name');
        $int = $this->container->getParameter('cookie_expire_time');

        if(!empty($filters['login'])) {
            $username = $filters['username'];
            $password = $filters['password'];
            $filters="?filters[username]=$username&filters[password]=$password";
            $method = 'GET';
            $url = $baseApiURL.$filters;
            $responseAPI = $this->CallAPI($method, $url);
            if(!empty($responseAPI)) {
                $responseData = json_decode($responseAPI);
                $responseData = $responseData[0];
                $responseId = $responseData->id;
                $responseName = $responseData->name;
                $token = sha1($username.$password);
                $method = 'PUT';
                $data['token'] = $token;
                $url = $baseApiURL.'/'.$responseId;
                $responseAPI = $this->CallAPI($method, $url, $data);
                setcookie($cookieName,$token,time()+$int,'/',false);
                return $responseAPI;
            } else {
                return FOSView::create('Not able to validate username and password', Codes::HTTP_INTERNAL_SERVER_ERROR);
            }
        } else if(!empty($filters['logout'])) {
            unset($_COOKIE['SSOkey']);
            setcookie('SSOkey', '' , time() - 3600,'/');            
            $id=$filters['logout'];
            $method = 'PUT';
            $url = $baseApiURL.'/'.$id;
            $data['token'] = '';
            $responseAPI = $this->CallAPI($method, $url, $data);
            echo $responseAPI;exit;
            if(!empty($responseAPI)) {

                } else {
                        return FOSView::create('Not a valid ID', Codes::HTTP_INTERNAL_SERVER_ERROR);
                }
        } else if(!empty($filters['token'])) {
            $token = $filters['token'];
            $filters="?filters[token]=$token";
            $method = 'GET';
            $url = $baseApiURL.$filters;
            $responseAPI = $this->CallAPI($method, $url);            
            if(!empty($responseAPI)) {                
                if(isset($_COOKIE[$cookieName])) {
                    setcookie($cookieName,$token,time()+$int,'/',false);
                    $responseAPI = json_decode($responseAPI);
                    $responseAPI = $responseAPI[0];
                    $responseData['status'] = 0;
                    $responseData['code'] = 403;
                    $responseData['message'] = 'Token expired';
                    $responseData['id'] = $responseAPI->id;
                    $responseData['name'] = $responseAPI->name;
                    $responseData['username'] = $responseAPI->username;
                    $responseData['email'] = $responseAPI->email;
                    $responseData['token'] = $responseAPI->token;
                    $responseAPI = $responseData;
                }
            } else {
                $responseAPI['status'] = 0;
                $responseAPI['code'] = 403;
                $responseAPI['message'] = 'Token expired';
            }
            return $responseAPI;
        }
        /*try {
            $offset = $paramFetcher->get('offset');
            $limit = $paramFetcher->get('limit');
            $order_by = $paramFetcher->get('order_by');
            $filters = !is_null($paramFetcher->get('filters')) ? $paramFetcher->get('filters') : array();

            $em = $this->getDoctrine()->getManager();
            $entities = $em->getRepository('AppBundle:Loginapi')->findBy($filters, $order_by, $limit, $offset);
            if ($entities) {
                return $entities;
            }

            return FOSView::create('Not Found', Codes::HTTP_NO_CONTENT);
        } catch (\Exception $e) {
            return FOSView::create($e->getMessage(), Codes::HTTP_INTERNAL_SERVER_ERROR);
        }*/
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


    /**
     * Create a Loginapi entity.
     *
     * @View(statusCode=201, serializerEnableMaxDepthChecks=true)
     *
     * @param Request $request
     *
     * @return Response
     *
     */
    public function postAction(Request $request)
    {
        $entity = new Loginapi();
        $form = $this->createForm(new LoginapiType(), $entity, array("method" => $request->getMethod()));
        $this->removeExtraFields($request, $form);
        $form->handleRequest($request);

        if ($form->isValid()) {
            $em = $this->getDoctrine()->getManager();
            $em->persist($entity);
            $em->flush();

            return $entity;
        }

        return FOSView::create(array('errors' => $form->getErrors()), Codes::HTTP_INTERNAL_SERVER_ERROR);
    }
    /**
     * Update a Loginapi entity.
     *
     * @View(serializerEnableMaxDepthChecks=true)
     *
     * @param Request $request
     * @param $entity
     *
     * @return Response
     */
    public function putAction(Request $request, Loginapi $entity)
    {
        try {
            $em = $this->getDoctrine()->getManager();
            $request->setMethod('PATCH'); //Treat all PUTs as PATCH
            $form = $this->createForm(new LoginapiType(), $entity, array("method" => $request->getMethod()));
            $this->removeExtraFields($request, $form);
            $form->handleRequest($request);
            if ($form->isValid()) {
                $em->flush();

                return $entity;
            }

            return FOSView::create(array('errors' => $form->getErrors()), Codes::HTTP_INTERNAL_SERVER_ERROR);
        } catch (\Exception $e) {
            return FOSView::create($e->getMessage(), Codes::HTTP_INTERNAL_SERVER_ERROR);
        }
    }
    /**
     * Partial Update to a Loginapi entity.
     *
     * @View(serializerEnableMaxDepthChecks=true)
     *
     * @param Request $request
     * @param $entity
     *
     * @return Response
*/
    public function patchAction(Request $request, Loginapi $entity)
    {
        return $this->putAction($request, $entity);
    }
    /**
     * Delete a Loginapi entity.
     *
     * @View(statusCode=204)
     *
     * @param Request $request
     * @param $entity
     *
     * @return Response
     */
    public function deleteAction(Request $request, Loginapi $entity)
    {
        try {
            $em = $this->getDoctrine()->getManager();
            $em->remove($entity);
            $em->flush();

            return null;
        } catch (\Exception $e) {
            return FOSView::create($e->getMessage(), Codes::HTTP_INTERNAL_SERVER_ERROR);
        }
    }
}
