<?php
namespace App\Api\V1\Controller\Rest;

use FOS\RestBundle\Controller\FOSRestController;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Symfony\Component\Routing\Annotation\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Symfony\Component\HttpFoundation\JsonResponse;

class SecurityController extends BaseController
{
    /**
     * @Route("/users", name="get_user")
     * @Method("GET")
     */
    public function getAction()
    {
        $response = new JsonResponse();
        $response->setContent('gfhfhgfhgf');

        return $response;
    }
}