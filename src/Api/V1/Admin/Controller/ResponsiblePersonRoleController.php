<?php

namespace App\Api\V1\Admin\Controller;

use App\Annotation\Grant;
use App\Api\V1\Admin\Service\ResponsiblePersonRoleService;
use App\Api\V1\Common\Controller\BaseController;
use App\Entity\ResponsiblePersonRole;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\JsonResponse;
use Knp\Bundle\SnappyBundle\Snappy\Response\PdfResponse;

/**
 * @Route("/api/v1.0/admin/responsible-person-role")
 *
 * @Grant(grant="persistence-common-responsible-person-role", level="VIEW")
 *
 * Class ResponsiblePersonRoleController
 * @package App\Api\V1\Admin\Controller
 */
class ResponsiblePersonRoleController extends BaseController
{
    /**
     * @Route("/grid", name="api_admin_responsible_person_role_grid", methods={"GET"})
     *
     * @param Request $request
     * @param ResponsiblePersonRoleService $responsiblePersonRoleService
     * @return JsonResponse
     */
    public function gridAction(Request $request, ResponsiblePersonRoleService $responsiblePersonRoleService): JsonResponse
    {
        return $this->respondGrid(
            $request,
            ResponsiblePersonRole::class,
            'api_admin_responsible_person_role_grid',
            $responsiblePersonRoleService
        );
    }

    /**
     * @Route("/grid", name="api_admin_responsible_person_role_grid_options", methods={"OPTIONS"})
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function gridOptionAction(Request $request): JsonResponse
    {
        return $this->getOptionsByGroupName($request, ResponsiblePersonRole::class, 'api_admin_responsible_person_role_grid');
    }

    /**
     * @Route("", name="api_admin_responsible_person_role_list", methods={"GET"})
     *
     * @param Request $request
     * @param ResponsiblePersonRoleService $responsiblePersonRoleService
     * @return PdfResponse|JsonResponse|Response
     */
    public function listAction(Request $request, ResponsiblePersonRoleService $responsiblePersonRoleService)
    {
        return $this->respondList(
            $request,
            ResponsiblePersonRole::class,
            'api_admin_responsible_person_role_list',
            $responsiblePersonRoleService,
            ['space_id' => $request->get('space_id')]
        );
    }

    /**
     * @Route("/{id}", requirements={"id"="\d+"}, name="api_admin_responsible_person_role_get", methods={"GET"})
     *
     * @param Request $request
     * @param $id
     * @param ResponsiblePersonRoleService $responsiblePersonRoleService
     * @return JsonResponse
     */
    public function getAction(Request $request, $id, ResponsiblePersonRoleService $responsiblePersonRoleService): JsonResponse
    {
        return $this->respondSuccess(
            Response::HTTP_OK,
            '',
            $responsiblePersonRoleService->getById($id),
            ['api_admin_responsible_person_role_get']
        );
    }

    /**
     * @Route("", name="api_admin_responsible_person_role_add", methods={"POST"})
     *
     * @Grant(grant="persistence-common-responsible-person-role", level="ADD")
     *
     * @param Request $request
     * @param ResponsiblePersonRoleService $responsiblePersonRoleService
     * @return JsonResponse
     */
    public function addAction(Request $request, ResponsiblePersonRoleService $responsiblePersonRoleService): JsonResponse
    {
        $id = $responsiblePersonRoleService->add(
            [
                'space_id' => $request->get('space_id'),
                'title' => $request->get('title'),
                'icon' => $request->get('icon'),
                'emergency' => $request->get('emergency'),
                'financially' => $request->get('financially'),
            ]
        );

        return $this->respondSuccess(
            Response::HTTP_CREATED,
            '',
            [$id]
        );
    }

    /**
     * @Route("/{id}", requirements={"id"="\d+"}, name="api_admin_responsible_person_role_edit", methods={"PUT"})
     *
     * @Grant(grant="persistence-common-responsible-person-role", level="EDIT")
     *
     * @param Request $request
     * @param $id
     * @param ResponsiblePersonRoleService $responsiblePersonRoleService
     * @return JsonResponse
     */
    public function editAction(Request $request, $id, ResponsiblePersonRoleService $responsiblePersonRoleService): JsonResponse
    {
        $responsiblePersonRoleService->edit(
            $id,
            [
                'space_id' => $request->get('space_id'),
                'title' => $request->get('title'),
                'icon' => $request->get('icon'),
                'emergency' => $request->get('emergency'),
                'financially' => $request->get('financially'),
            ]
        );

        return $this->respondSuccess(
            Response::HTTP_CREATED
        );
    }

    /**
     * @Route("/{id}", requirements={"id"="\d+"}, name="api_admin_responsible_person_role_delete", methods={"DELETE"})
     *
     * @Grant(grant="persistence-common-responsible-person-role", level="DELETE")
     *
     * @param Request $request
     * @param $id
     * @param ResponsiblePersonRoleService $responsiblePersonRoleService
     * @return JsonResponse
     */
    public function deleteAction(Request $request, $id, ResponsiblePersonRoleService $responsiblePersonRoleService): JsonResponse
    {
        $responsiblePersonRoleService->remove($id);

        return $this->respondSuccess(
            Response::HTTP_NO_CONTENT
        );
    }

    /**
     * @Route("", name="api_admin_responsible_person_role_delete_bulk", methods={"DELETE"})
     *
     * @Grant(grant="persistence-common-responsible-person-role", level="DELETE")
     *
     * @param Request $request
     * @param ResponsiblePersonRoleService $responsiblePersonRoleService
     * @return JsonResponse
     */
    public function deleteBulkAction(Request $request, ResponsiblePersonRoleService $responsiblePersonRoleService): JsonResponse
    {
        $responsiblePersonRoleService->removeBulk($request->get('ids'));

        return $this->respondSuccess(
            Response::HTTP_NO_CONTENT
        );
    }

    /**
     * @Route("/related/info", name="api_admin_responsible_person_role_related_info", methods={"POST"})
     *
     * @param Request $request
     * @param ResponsiblePersonRoleService $responsiblePersonRoleService
     * @return JsonResponse
     */
    public function relatedInfoAction(Request $request, ResponsiblePersonRoleService $responsiblePersonRoleService): JsonResponse
    {
        $relatedData = $responsiblePersonRoleService->getRelatedInfo($request->get('ids'));

        return $this->respondSuccess(
            Response::HTTP_OK,
            '',
            [$relatedData]
        );
    }
}
