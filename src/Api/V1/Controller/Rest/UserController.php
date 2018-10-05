<?php
namespace App\Api\V1\Controller\Rest;

use App\Api\V1\Service\UserService;
use App\Entity\User;
use Doctrine\ORM\EntityManager;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use App\Annotation\Permission;
use Symfony\Component\HttpFoundation\JsonResponse;

/**
 * Class UserController
 * @package App\Api\V1\Controller\Rest
 * @Route("/api/v1.0")
 * @Permission({"PERMISSION_USER"})
 */
class UserController extends BaseController
{
    /**
     * @Method("GET")
     * @Route("/space/{spaceId}/user", name="user_list")
     */
    public function getAction()
    {
        $users = $this->em->getRepository(User::class)->findAll();

        return $this->respondSuccess(
            '',
            Response::HTTP_OK,
            ['users' => $users],
            ['api_user__list']
        );
    }

    /**
     * This function is used to reset password
     *
     * @Method("PUT")
     * @Route("/user/reset-password/{id}", name="user_reset_password", requirements={"id"="\d+"})
     *
     * @param $id
     * @param UserService $userService
     * @return JsonResponse
     */
    public function resetPasswordAction($id, UserService $userService)
    {
        try {
            $userService->resetPassword($id);
            $response = $this->respondSuccess(
                'Password recovery link sent, please check email.',
                Response::HTTP_CREATED
            );
        } catch (\Throwable $e) {
            $response = $this->respondError($e->getMessage(), $e->getCode());
        }

        return $response;
    }
}