<?php
namespace App\Api\V1\Controller\Rest;

use App\Api\V1\Service\Exception\RoleNotFoundException;
use App\Api\V1\Service\Exception\SpaceNotFoundException;
use App\Api\V1\Service\Exception\UserNotFoundException;
use App\Api\V1\Service\RoleService;
use App\Entity\Role;
use App\Entity\Space;
use App\Entity\User;
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
 * @Permission({"PERMISSION_ROLE"})
 */
class RoleController extends BaseController
{
    /**
     * Get roles by space
     *
     * @Method("GET")
     * @Route("/space/{spaceId}/role", name="space_role_list", requirements={"spaceId"="\d+"})
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function listAction(Request $request)
    {
        try {
            $space = $request->get('space');

            if (is_null($space)) {
                throw new SpaceNotFoundException(Response::HTTP_BAD_REQUEST);
            }

            $roles = $this->em->getRepository(Role::class)->findRolesBySpace($space);

            $response = $this->respondSuccess(
                '',
                Response::HTTP_OK,
                ['roles' => $roles],
                ['api_space__role_list']
            );
        } catch (\Throwable $e) {
            $response = $this->respondError($e->getMessage(), $e->getCode());
        }

        return $response;
    }

    /**
     * Add space role to user
     *
     * @Method("POST")
     * @Route("/space/{spaceId}/role/{roleId}/user/{userId}", name="space_add_role_to_user", requirements={"spaceId"="\d+", "roleId"="\d+", "userId"="\d+"})
     *
     * @param $roleId
     * @param $userId
     * @param Request $request
     * @param RoleService $roleService
     * @return JsonResponse
     */
    public function addAction($roleId, $userId, Request $request, RoleService $roleService)
    {
        try {
            /**
             * @var Space $space
             * @var Role $role
             * @var User $user
             */
            $space = $request->get('space');
            $role  = $this->em->getRepository(Role::class)->find($roleId);
            $user  = $this->em->getRepository(User::class)->find($userId);

            if (is_null($space)) {
                throw new SpaceNotFoundException(Response::HTTP_BAD_REQUEST);
            }

            if (is_null($role)) {
                throw new RoleNotFoundException(Response::HTTP_BAD_REQUEST);
            }

            if (is_null($user)) {
                throw new UserNotFoundException('User not found', Response::HTTP_BAD_REQUEST);
            }

            $roleService->addRole($space, $role, $user);

            $response = $this->respondSuccess(
                'Role successfully assigned to user',
                Response::HTTP_CREATED
            );
        } catch (\Throwable $e) {
            $response = $this->respondError($e->getMessage(), $e->getCode());
        }

        return $response;
    }

    /**
     * Add space role to user
     *
     * @Method("DELETE")
     * @Route("/space/{spaceId}/role/{roleId}/user/{userId}", name="space_delete_role_from_user", requirements={"spaceId"="\d+", "roleId"="\d+", "userId"="\d+"})
     *
     * @param $roleId
     * @param $userId
     * @param Request $request
     * @param RoleService $roleService
     * @return JsonResponse
     */
    public function removeAction($roleId, $userId, Request $request, RoleService $roleService)
    {
        try {
            /**
             * @var Space $space
             * @var Role $role
             * @var User $user
             */
            $space = $request->get('space');
            $role  = $this->em->getRepository(Role::class)->find($roleId);
            $user  = $this->em->getRepository(User::class)->find($userId);

            if (is_null($space)) {
                throw new SpaceNotFoundException(Response::HTTP_BAD_REQUEST);
            }

            if (is_null($role)) {
                throw new RoleNotFoundException(Response::HTTP_BAD_REQUEST);
            }

            if (is_null($user)) {
                throw new UserNotFoundException('User not found', Response::HTTP_BAD_REQUEST);
            }

            $roleService->removeRole($space, $role, $user);

            $response = $this->respondSuccess(
                'Role successfully remove from user',
                Response::HTTP_NO_CONTENT
            );
        } catch (\Throwable $e) {
            $response = $this->respondError($e->getMessage(), $e->getCode());
        }

        return $response;
    }
}