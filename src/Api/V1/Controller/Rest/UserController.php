<?php
namespace App\Api\V1\Controller\Rest;

use App\Entity\User;
use Doctrine\ORM\EntityManager;
use JMS\Serializer\SerializationContext;
use JMS\Serializer\Serializer;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use App\Annotation\Permission;
use Symfony\Component\HttpFoundation\JsonResponse;

/**
 * Class UserController
 * @package App\Api\V1\Controller\Rest
 * @Route("/api/v1.0/space/{spaceId}/user")
 * @Permission({"PERMISSION_USER"})
 */
class UserController extends BaseController
{
    /**
     * @Route("/", name="user_list")
     * @Method("GET")
     */
    public function getAction()
    {
        /** @var EntityManager $em */
        $em = $this->getDoctrine()->getManager();

        /** @var User[] $users */
        $users = $em->getRepository(User::class)->findAll();

        /** @var Serializer $serializer */
        $serializer = $this->get('jms_serializer');

        $data = $serializer->serialize(
            [
                'status'  => JsonResponse::HTTP_OK,
                'message' => 'Success',
                'data'    => [
                    'users' => $users
                ]
            ],
            'json',
            SerializationContext::create()->setGroups(['api_user__list'])
        );

        $response = new JsonResponse();
        $response->setContent($data);

        return $response;
    }
}