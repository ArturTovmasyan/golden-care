<?php

namespace App\Api\V1\Admin\Controller;

use App\Annotation\Grant;
use App\Api\V1\Common\Controller\BaseController;
use App\Api\V1\Admin\Service\UserService;
use App\Entity\User;
use App\Api\V1\Common\Model\ResponseCode;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\JsonResponse;
use Knp\Bundle\SnappyBundle\Snappy\Response\PdfResponse;
use Symfony\Component\Routing\RouterInterface;

/**
 * @Route("/api/v1.0/admin/user")
 *
 * @Grant(grant="persistence-security-user", level="VIEW")
 *
 * Class UserController
 * @package App\Api\V1\Admin\Controller
 */
class UserController extends BaseController
{
    /**
     * @Route("/grid", name="api_admin_user_grid", methods={"GET"})
     *
     * @param Request $request
     * @param UserService $userService
     * @return JsonResponse
     */
    public function gridAction(Request $request, UserService $userService): JsonResponse
    {
        return $this->respondGrid(
            $request,
            User::class,
            'api_admin_user_grid',
            $userService
        );
    }

    /**
     * @Route("/grid", name="api_admin_user_grid_options", methods={"OPTIONS"})
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function gridOptionAction(Request $request): JsonResponse
    {
        return $this->getOptionsByGroupName($request, User::class, 'api_admin_user_grid');
    }

    /**
     * @Route("", name="api_admin_user_list", methods={"GET"})
     *
     * @param Request $request
     * @param UserService $userService
     * @return PdfResponse|JsonResponse|Response
     */
    public function listAction(Request $request, UserService $userService)
    {
        return $this->respondList(
            $request,
            User::class,
            'api_admin_user_list',
            $userService
        );
    }

    /**
     * @Route("/{id}", name="api_admin_user_get", requirements={"id"="\d+"}, methods={"GET"})
     *
     * @param Request $request
     * @param $id
     * @param UserService $userService
     * @return JsonResponse
     */
    public function getAction(Request $request, $id, UserService $userService): JsonResponse
    {
        return $this->respondSuccess(
            Response::HTTP_OK,
            '',
            $userService->getById($id),
            ['api_admin_user_get']
        );
    }

    /**
     * @Route("", name="api_admin_user_add", methods={"POST"})
     *
     * @Grant(grant="persistence-security-user", level="ADD")
     *
     * @param Request $request
     * @param UserService $userService
     * @return JsonResponse
     */
    public function addAction(Request $request, UserService $userService): JsonResponse
    {
        $id = $userService->add(
            [
                'first_name' => $request->get('first_name'),
                'last_name' => $request->get('last_name'),
                'username' => $request->get('username'),
                'email' => $request->get('email'),
                'password' => $request->get('password'),
                're_password' => $request->get('re_password'),
                'phones' => $request->get('phones'),
                'enabled' => $request->get('enabled'),
                'roles' => $request->get('roles'),
                'grants' => $request->get('grants'),
                'space_id' => $request->get('space_id'),
                'owner' => $request->get('owner'),
            ]
        );

        return $this->respondSuccess(
            Response::HTTP_CREATED,
            '',
            [$id]
        );
    }

    /**
     * @Route("/{id}", requirements={"id"="\d+"}, name="api_admin_user_edit", methods={"PUT"})
     *
     * @Grant(grant="persistence-security-user", level="EDIT")
     *
     * @param Request $request
     * @param $id
     * @param UserService $userService
     * @return JsonResponse
     */
    public function editAction(Request $request, $id, UserService $userService): JsonResponse
    {
        $userService->edit(
            $id,
            [
                'first_name' => $request->get('first_name'),
                'last_name' => $request->get('last_name'),
                'username' => $request->get('username'),
                'email' => $request->get('email'),
                'password' => $request->get('password'),
                're_password' => $request->get('re_password'),
                'phones' => $request->get('phones'),
                'enabled' => $request->get('enabled'),
                'roles' => $request->get('roles'),
                'grants' => $request->get('grants'),
                'space_id' => $request->get('space_id'),
                'owner' => $request->get('owner'),
            ]
        );

        return $this->respondSuccess(
            Response::HTTP_CREATED
        );
    }

    /**
     * @Route("/{id}/reset-password", requirements={"id"="\d+"}, name="api_admin_user_reset_password", methods={"PUT"})
     *
     * @Grant(grant="persistence-security-user", level="EDIT")
     *
     * @param Request $request
     * @param $id
     * @param UserService $userService
     * @return JsonResponse
     */
    public function resetPasswordAction(Request $request, $id, UserService $userService): JsonResponse
    {
        $userService->resetPassword($id);

        return $this->respondSuccess(
            ResponseCode::RECOVERY_LINK_SENT_TO_EMAIL
        );
    }

    /**
     * @Route("/{id}", requirements={"id"="\d+"}, name="api_admin_user_delete", methods={"DELETE"})
     *
     * @Grant(grant="persistence-security-user", level="DELETE")
     *
     * @param Request $request
     * @param $id
     * @param UserService $userService
     * @return JsonResponse
     */
    public function deleteAction(Request $request, $id, UserService $userService): JsonResponse
    {
        $userService->disable($id);

        return $this->respondSuccess(
            Response::HTTP_NO_CONTENT
        );
    }

    /**
     * @Route("", name="api_admin_user_delete_bulk", methods={"DELETE"})
     *
     * @Grant(grant="persistence-security-user", level="DELETE")
     *
     * @param Request $request
     * @param UserService $userService
     * @return JsonResponse
     */
    public function deleteBulkAction(Request $request, UserService $userService): JsonResponse
    {
        $userService->disableBulk($request->get('ids'));

        return $this->respondSuccess(
            Response::HTTP_NO_CONTENT
        );
    }

    /**
     * @Route("/related/info", name="api_admin_user_related_info", methods={"POST"})
     *
     * @param Request $request
     * @param UserService $userService
     * @return JsonResponse
     */
    public function relatedInfoAction(Request $request, UserService $userService): JsonResponse
    {
        $relatedData = $userService->getRelatedInfo($request->get('ids'));

        return $this->respondSuccess(
            Response::HTTP_OK,
            '',
            [$relatedData]
        );
    }

    /**
     * @Route("/mobile/list", name="api_admin_user_mobile_list", methods={"GET"})
     *
     * @param Request $request
     * @param UserService $userService
     * @param RouterInterface $router
     * @return JsonResponse
     */
    public function getMobileListAction(Request $request, UserService $userService, RouterInterface $router): JsonResponse
    {
        /** @var User $user */
        $user = $this->get('security.token_storage')->getToken()->getUser();

        return $this->respondSuccess(
            Response::HTTP_OK,
            '',
            $userService->getMobileList($router, $request->headers->get('date'), $user->getId()),
            ['api_admin_user_mobile_list']
        );
    }

    /**
     * @Route("/download/{id}", requirements={"id"="\d+"}, name="api_admin_user_image_download", methods={"GET"})
     *
     * @param Request $request
     * @param $id
     * @param UserService $userService
     * @return Response
     */
    public function downloadAction(Request $request, $id, UserService $userService): Response
    {
        $data = $userService->downloadFile($id);

        return $this->respondImageFile($data[0], $data[1], $data[2]);
    }

    /**
     * @Route("/calendar", name="api_admin_corporate_calendar", methods={"GET"})
     *
     * @param Request $request
     * @param UserService $userService
     * @return JsonResponse
     */
    public function getCorporateCalendarAction(Request $request, UserService $userService): JsonResponse
    {
        /** @var User $user */
        $user = $this->get('security.token_storage')->getToken()->getUser();

        $userRoleIds = null;
        if ($user !== null) {
            $userRoles = $user->getRoleObjects();

            if ($userRoles !== null) {
                $userRoleIds = array_map(function ($item) {
                    return $item->getId();
                }, $userRoles->toArray());
            }
        }

        return $this->respondSuccess(
            Response::HTTP_OK,
            '',
            $userService->getCalendar($user, $userRoleIds, $request->get('date_from'), $request->get('date_to')),
            ['api_admin_facility_calendar']
        );
    }
}
