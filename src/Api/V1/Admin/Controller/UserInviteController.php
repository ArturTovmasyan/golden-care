<?php

namespace App\Api\V1\Admin\Controller;

use App\Annotation\Grant;
use App\Api\V1\Admin\Service\UserInviteService;
use App\Api\V1\Common\Controller\BaseController;
use App\Api\V1\Common\Model\ResponseCode;
use App\Entity\UserInvite;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\JsonResponse;
use Knp\Bundle\SnappyBundle\Snappy\Response\PdfResponse;

/**
 * @Route("/api/v1.0/admin/user/invite")
 *
 * @Grant(grant="persistence-security-user_invite", level="VIEW")
 *
 * Class UserInviteController
 * @package App\Api\V1\Admin\Controller
 */
class UserInviteController extends BaseController
{
    /**
     * @Route("/grid", name="api_admin_user_invite_grid", methods={"GET"})
     *
     * @param Request $request
     * @param UserInviteService $userInviteService
     * @return JsonResponse
     */
    public function gridAction(Request $request, UserInviteService $userInviteService): JsonResponse
    {
        return $this->respondGrid(
            $request,
            UserInvite::class,
            'api_admin_user_invite_grid',
            $userInviteService
        );
    }

    /**
     * @Route("/grid", name="api_admin_user_invite_grid_options", methods={"OPTIONS"})
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function gridOptionAction(Request $request): JsonResponse
    {
        return $this->getOptionsByGroupName($request, UserInvite::class, 'api_admin_user_invite_grid');
    }

    /**
     * @Route("", name="api_admin_user_invite_list", methods={"GET"})
     *
     * @param Request $request
     * @param UserInviteService $userInviteService
     * @return PdfResponse|JsonResponse|Response
     */
    public function listAction(Request $request, UserInviteService $userInviteService)
    {
        return $this->respondList(
            $request,
            UserInvite::class,
            'api_admin_user_invite_list',
            $userInviteService
        );
    }

    /**
     * @Route("/{id}", requirements={"id"="\d+"}, name="api_admin_user_invite_get", methods={"GET"})
     *
     * @param Request $request
     * @param $id
     * @param UserInviteService $userInviteService
     * @return JsonResponse
     */
    public function getAction(Request $request, $id, UserInviteService $userInviteService)
    {
        return $this->respondSuccess(
            Response::HTTP_OK,
            '',
            $userInviteService->getById($id),
            ['api_admin_user_invite_get']
        );
    }

    /**
     * @Route("", name="api_admin_user_invite_add", methods={"POST"})
     *
     * @Grant(grant="persistence-security-user_invite", level="ADD")
     *
     * @param Request $request
     * @param UserInviteService $userInviteService
     * @return JsonResponse
     */
    public function addAction(Request $request, UserInviteService $userInviteService): JsonResponse
    {
        $id = $userInviteService->add(
            $request->get('space_id'),
            $request->get('user_id'),
            $request->get('email'),
            $request->get('owner'),
            $request->get('roles'),
            $request->getSchemeAndHttpHost()
        );

        return $this->respondSuccess(
            ResponseCode::INVITATION_LINK_SENT_TO_EMAIL,
            '',
            [$id]
        );
    }

    /**
     * @Route("/{id}", requirements={"id"="\d+"}, name="api_admin_user_invite_delete", methods={"DELETE"})
     *
     * @Grant(grant="persistence-security-user_invite", level="DELETE")
     *
     * @param Request $request
     * @param $id
     * @param UserInviteService $userInviteService
     * @return JsonResponse
     */
    public function deleteAction(Request $request, $id, UserInviteService $userInviteService): JsonResponse
    {
        $userInviteService->remove($id);

        return $this->respondSuccess(
            Response::HTTP_NO_CONTENT
        );
    }

    /**
     * @Route("", name="api_admin_user_invite_delete_bulk", methods={"DELETE"})
     *
     * @Grant(grant="persistence-security-user_invite", level="DELETE")
     *
     * @param Request $request
     * @param UserInviteService $userInviteService
     * @return JsonResponse
     */
    public function deleteBulkAction(Request $request, UserInviteService $userInviteService): JsonResponse
    {
        $userInviteService->removeBulk($request->get('ids'));

        return $this->respondSuccess(
            Response::HTTP_NO_CONTENT
        );
    }

    /**
     * @Route("/related/info", name="api_admin_user_invite_related_info", methods={"POST"})
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function relatedInfoAction(Request $request): JsonResponse
    {
        return $this->respondSuccess(
            Response::HTTP_OK,
            '',
            []
        );
    }
}
