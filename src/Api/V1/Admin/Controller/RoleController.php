<?php

namespace App\Api\V1\Admin\Controller;

use App\Annotation\Grant;
use App\Api\V1\Common\Controller\BaseController;
use App\Api\V1\Admin\Service\RoleService;
use App\Api\V1\Common\Service\GrantService;
use App\Entity\Role;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\JsonResponse;
use Knp\Bundle\SnappyBundle\Snappy\Response\PdfResponse;

/**
 * @Route("/api/v1.0/admin/role")
 *
 * @Grant(grant="persistence-security-role", level="VIEW")
 *
 * Class RoleController
 * @package App\Api\V1\Admin\Controller
 */
class RoleController extends BaseController
{
    /**
     * @Route("/grid", name="api_admin_role_grid", methods={"GET"})
     *
     * @param Request $request
     * @param RoleService $roleService
     * @return JsonResponse
     */
    public function gridAction(Request $request, RoleService $roleService): JsonResponse
    {
        return $this->respondGrid(
            $request,
            Role::class,
            'api_admin_role_grid',
            $roleService
        );
    }

    /**
     * @Route("/grid", name="api_admin_role_grid_options", methods={"OPTIONS"})
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function gridOptionAction(Request $request): JsonResponse
    {
        return $this->getOptionsByGroupName($request, Role::class, 'api_admin_role_grid');
    }

    /**
     * @Route("", name="api_admin_role_list", methods={"GET"})
     *
     * @param Request $request
     * @param RoleService $roleService
     * @return PdfResponse|JsonResponse|Response
     */
    public function listAction(Request $request, RoleService $roleService)
    {
        return $this->respondList(
            $request,
            Role::class,
            'api_admin_role_list',
            $roleService
        );
    }

    /**
     * @Route("/{id}", requirements={"id"="\d+"}, name="api_admin_role_get", methods={"GET"})
     *
     * @param Request $request
     * @param $id
     * @param RoleService $roleService
     * @param GrantService $grantService
     * @return JsonResponse
     */
    public function getAction(Request $request, $id, RoleService $roleService, GrantService $grantService): JsonResponse
    {
        return $this->respondSuccess(
            Response::HTTP_OK,
            '',
            $roleService->getById($id, $grantService),
            ['api_admin_role_get']
        );
    }

    /**
     * @Route("", name="api_admin_role_add", methods={"POST"})
     *
     * @Grant(grant="persistence-security-role", level="ADD")
     *
     * @param Request $request
     * @param RoleService $roleService
     * @return JsonResponse
     */
    public function addAction(Request $request, RoleService $roleService): JsonResponse
    {
        $id = $roleService->add(
            [
                'name' => $request->get('name'),
                'grants' => $request->get('grants'),
                'default' => $request->get('default')
            ]
        );

        return $this->respondSuccess(
            Response::HTTP_CREATED,
            '',
            [$id]
        );
    }

    /**
     * @Route("/{id}", requirements={"id"="\d+"}, name="api_admin_role_edit", methods={"PUT"})
     *
     * @Grant(grant="persistence-security-role", level="EDIT")
     *
     * @param Request $request
     * @param $id
     * @param RoleService $roleService
     * @return JsonResponse
     */
    public function editAction(Request $request, $id, RoleService $roleService): JsonResponse
    {
        $roleService->edit(
            $id,
            [
                'name' => $request->get('name'),
                'grants' => $request->get('grants'),
                'default' => $request->get('default')
            ]
        );

        return $this->respondSuccess(
            Response::HTTP_CREATED
        );
    }

    /**
     * @Route("/{id}", requirements={"id"="\d+"}, name="api_admin_role_delete", methods={"DELETE"})
     *
     * @Grant(grant="persistence-security-role", level="DELETE")
     *
     * @param Request $request
     * @param $id
     * @param RoleService $roleService
     * @return JsonResponse
     */
    public function deleteAction(Request $request, $id, RoleService $roleService): JsonResponse
    {
        $roleService->remove($id);

        return $this->respondSuccess(
            Response::HTTP_NO_CONTENT
        );
    }

    /**
     * @Route("", name="api_admin_role_delete_bulk", methods={"DELETE"})
     *
     * @Grant(grant="persistence-security-role", level="DELETE")
     *
     * @param Request $request
     * @param RoleService $roleService
     * @return JsonResponse
     */
    public function deleteBulkAction(Request $request, RoleService $roleService): JsonResponse
    {
        $roleService->removeBulk($request->get('ids'));

        return $this->respondSuccess(
            Response::HTTP_NO_CONTENT
        );
    }

    /**
     * @Route("/related/info", name="api_admin_role_related_info", methods={"POST"})
     *
     * @param Request $request
     * @param RoleService $roleService
     * @return JsonResponse
     */
    public function relatedInfoAction(Request $request, RoleService $roleService): JsonResponse
    {
        $relatedData = $roleService->getRelatedInfo($request->get('ids'));

        return $this->respondSuccess(
            Response::HTTP_OK,
            '',
            [$relatedData]
        );
    }
}
