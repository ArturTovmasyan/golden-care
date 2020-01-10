<?php

namespace App\Api\V1\Admin\Controller;

use App\Annotation\Grant;
use App\Api\V1\Admin\Service\DietService;
use App\Api\V1\Common\Controller\BaseController;
use App\Entity\Diet;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\JsonResponse;
use Knp\Bundle\SnappyBundle\Snappy\Response\PdfResponse;

/**
 * @Route("/api/v1.0/admin/diet")
 *
 * @Grant(grant="persistence-common-diet", level="VIEW")
 *
 * Class DietController
 * @package App\Api\V1\Admin\Controller
 */
class DietController extends BaseController
{
    /**
     * @Route("/grid", name="api_admin_diet_grid", methods={"GET"})
     *
     * @param Request $request
     * @param DietService $dietService
     * @return JsonResponse
     */
    public function gridAction(Request $request, DietService $dietService): JsonResponse
    {
        return $this->respondGrid(
            $request,
            Diet::class,
            'api_admin_diet_grid',
            $dietService
        );
    }

    /**
     * @Route("/grid", name="api_admin_diet_grid_options", methods={"OPTIONS"})
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function gridOptionAction(Request $request): JsonResponse
    {
        return $this->getOptionsByGroupName($request, Diet::class, 'api_admin_diet_grid');
    }

    /**
     * @Route("", name="api_admin_diet_list", methods={"GET"})
     *
     * @param Request $request
     * @param DietService $dietService
     * @return PdfResponse|JsonResponse|Response
     */
    public function listAction(Request $request, DietService $dietService)
    {
        return $this->respondList(
            $request,
            Diet::class,
            'api_admin_diet_list',
            $dietService
        );
    }

    /**
     * @Route("/{id}", requirements={"id"="\d+"}, name="api_admin_diet_get", methods={"GET"})
     *
     * @param Request $request
     * @param $id
     * @param DietService $dietService
     * @return JsonResponse
     */
    public function getAction(Request $request, $id, DietService $dietService): JsonResponse
    {
        return $this->respondSuccess(
            Response::HTTP_OK,
            '',
            $dietService->getById($id),
            ['api_admin_diet_get']
        );
    }

    /**
     * @Route("", name="api_admin_diet_add", methods={"POST"})
     *
     * @Grant(grant="persistence-common-diet", level="ADD")
     *
     * @param Request $request
     * @param DietService $dietService
     * @return JsonResponse
     */
    public function addAction(Request $request, DietService $dietService): JsonResponse
    {
        $id = $dietService->add(
            [
                'title' => $request->get('title'),
                'color' => $request->get('color'),
                'space_id' => $request->get('space_id')
            ]
        );

        return $this->respondSuccess(
            Response::HTTP_CREATED,
            '',
            [$id]
        );
    }

    /**
     * @Route("/{id}", requirements={"id"="\d+"}, name="api_admin_diet_edit", methods={"PUT"})
     *
     * @Grant(grant="persistence-common-diet", level="EDIT")
     *
     * @param Request $request
     * @param $id
     * @param DietService $dietService
     * @return JsonResponse
     */
    public function editAction(Request $request, $id, DietService $dietService): JsonResponse
    {
        $dietService->edit(
            $id,
            [
                'title' => $request->get('title'),
                'color' => $request->get('color'),
                'space_id' => $request->get('space_id')
            ]
        );

        return $this->respondSuccess(
            Response::HTTP_CREATED
        );
    }

    /**
     * @Route("/{id}", requirements={"id"="\d+"}, name="api_admin_diet_delete", methods={"DELETE"})
     *
     * @Grant(grant="persistence-common-diet", level="DELETE")
     *
     * @param Request $request
     * @param $id
     * @param DietService $dietService
     * @return JsonResponse
     */
    public function deleteAction(Request $request, $id, DietService $dietService): JsonResponse
    {
        $dietService->remove($id);

        return $this->respondSuccess(
            Response::HTTP_NO_CONTENT
        );
    }

    /**
     * @Route("", name="api_admin_diet_delete_bulk", methods={"DELETE"})
     *
     * @Grant(grant="persistence-common-diet", level="DELETE")
     *
     * @param Request $request
     * @param DietService $dietService
     * @return JsonResponse
     */
    public function deleteBulkAction(Request $request, DietService $dietService): JsonResponse
    {
        $dietService->removeBulk($request->get('ids'));

        return $this->respondSuccess(
            Response::HTTP_NO_CONTENT
        );
    }

    /**
     * @Route("/related/info", name="api_admin_diet_related_info", methods={"POST"})
     *
     * @param Request $request
     * @param DietService $dietService
     * @return JsonResponse
     */
    public function relatedInfoAction(Request $request, DietService $dietService): JsonResponse
    {
        $relatedData = $dietService->getRelatedInfo($request->get('ids'));

        return $this->respondSuccess(
            Response::HTTP_OK,
            '',
            [$relatedData]
        );
    }
}
