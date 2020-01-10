<?php

namespace App\Api\V1\Admin\Controller;

use App\Annotation\Grant;
use App\Api\V1\Admin\Service\SpaceService;
use App\Api\V1\Common\Controller\BaseController;
use App\Entity\Space;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\JsonResponse;
use Knp\Bundle\SnappyBundle\Snappy\Response\PdfResponse;

/**
 * @Route("/api/v1.0/admin/space")
 *
 * @Grant(grant="persistence-security-space", level="VIEW")
 *
 * Class SpaceController
 * @package App\Api\V1\Admin\Controller
 */
class SpaceController extends BaseController
{
    /**
     * @Route("/grid", name="api_admin_space_grid", methods={"GET"})
     *
     * @param Request $request
     * @param SpaceService $spaceService
     * @return JsonResponse
     */
    public function gridAction(Request $request, SpaceService $spaceService): JsonResponse
    {
        return $this->respondGrid(
            $request,
            Space::class,
            'api_admin_space_grid',
            $spaceService
        );
    }

    /**
     * @Route("/grid", name="api_admin_space_grid_options", methods={"OPTIONS"})
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function gridOptionAction(Request $request): JsonResponse
    {
        return $this->getOptionsByGroupName($request, Space::class, 'api_admin_space_grid');
    }

    /**
     * @Route("", name="api_admin_space_list", methods={"GET"})
     *
     * @param Request $request
     * @param SpaceService $spaceService
     * @return PdfResponse|JsonResponse|Response
     */
    public function listAction(Request $request, SpaceService $spaceService)
    {
        return $this->respondList(
            $request,
            Space::class,
            'api_admin_space_list',
            $spaceService
        );
    }

    /**
     * @Route("/{id}", requirements={"id"="\d+"}, name="api_admin_space_get", methods={"GET"})
     *
     * @param Request $request
     * @param $id
     * @param SpaceService $spaceService
     * @return JsonResponse
     */
    public function getAction(Request $request, $id, SpaceService $spaceService): JsonResponse
    {
        return $this->respondSuccess(
            Response::HTTP_OK,
            '',
            $spaceService->getById($id),
            ['api_admin_space_get']
        );
    }

    /**
     * @Route("", name="api_admin_space_add", methods={"POST"})
     *
     * @Grant(grant="persistence-security-space", level="ADD")
     *
     * @param Request $request
     * @param SpaceService $spaceService
     * @return JsonResponse
     */
    public function addAction(Request $request, SpaceService $spaceService): JsonResponse
    {
        $id = $spaceService->add(
            [
                'name' => $request->get('name')
            ]
        );

        return $this->respondSuccess(
            Response::HTTP_CREATED,
            '',
            [$id]
        );
    }

    /**
     * @Route("/{id}", requirements={"id"="\d+"}, name="api_admin_space_edit", methods={"PUT"})
     *
     * @Grant(grant="persistence-security-space", level="EDIT")
     *
     * @param Request $request
     * @param $id
     * @param SpaceService $spaceService
     * @return JsonResponse
     */
    public function editAction(Request $request, $id, SpaceService $spaceService): JsonResponse
    {
        $spaceService->edit(
            $id,
            [
                'name' => $request->get('name')
            ]
        );

        return $this->respondSuccess(
            Response::HTTP_CREATED
        );
    }

    /**
     * @Route("/{id}", requirements={"id"="\d+"}, name="api_admin_space_delete", methods={"DELETE"})
     *
     * @Grant(grant="persistence-security-space", level="DELETE")
     *
     * @param Request $request
     * @param $id
     * @param SpaceService $spaceService
     * @return JsonResponse
     */
    public function deleteAction(Request $request, $id, SpaceService $spaceService): JsonResponse
    {
        $spaceService->remove($id);

        return $this->respondSuccess(
            Response::HTTP_NO_CONTENT
        );
    }

    /**
     * @Route("", name="api_admin_space_delete_bulk", methods={"DELETE"})
     *
     * @Grant(grant="persistence-security-space", level="DELETE")
     *
     * @param Request $request
     * @param SpaceService $spaceService
     * @return JsonResponse
     */
    public function deleteBulkAction(Request $request, SpaceService $spaceService): JsonResponse
    {
        $spaceService->removeBulk($request->get('ids'));

        return $this->respondSuccess(
            Response::HTTP_NO_CONTENT
        );
    }

    /**
     * @Route("/related/info", name="api_admin_space_related_info", methods={"POST"})
     *
     * @param Request $request
     * @param SpaceService $spaceService
     * @return JsonResponse
     */
    public function relatedInfoAction(Request $request, SpaceService $spaceService): JsonResponse
    {
        $relatedData = $spaceService->getRelatedInfo($request->get('ids'));

        return $this->respondSuccess(
            Response::HTTP_OK,
            '',
            [$relatedData]
        );
    }
}
