<?php

namespace App\Api\V1\Admin\Controller;

use App\Annotation\Grant;
use App\Api\V1\Admin\Service\KeyFinanceTypeService;
use App\Api\V1\Common\Controller\BaseController;
use App\Entity\KeyFinanceType;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\JsonResponse;
use Knp\Bundle\SnappyBundle\Snappy\Response\PdfResponse;

/**
 * @Route("/api/v1.0/admin/key-finance-type")
 *
 * @Grant(grant="persistence-common-key_finance_type", level="VIEW")
 *
 * Class KeyFinanceTypeController
 * @package App\Api\V1\Admin\Controller
 */
class KeyFinanceTypeController extends BaseController
{
    /**
     * @Route("/grid", name="api_admin_key_finance_type_grid", methods={"GET"})
     *
     * @param Request $request
     * @param KeyFinanceTypeService $keyFinanceTypeService
     * @return JsonResponse
     */
    public function gridAction(Request $request, KeyFinanceTypeService $keyFinanceTypeService): JsonResponse
    {
        return $this->respondGrid(
            $request,
            KeyFinanceType::class,
            'api_admin_key_finance_type_grid',
            $keyFinanceTypeService
        );
    }

    /**
     * @Route("/grid", name="api_admin_key_finance_type_grid_options", methods={"OPTIONS"})
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function gridOptionAction(Request $request): JsonResponse
    {
        return $this->getOptionsByGroupName($request, KeyFinanceType::class, 'api_admin_key_finance_type_grid');
    }

    /**
     * @Route("", name="api_admin_key_finance_type_list", methods={"GET"})
     *
     * @param Request $request
     * @param KeyFinanceTypeService $keyFinanceTypeService
     * @return PdfResponse|JsonResponse|Response
     */
    public function listAction(Request $request, KeyFinanceTypeService $keyFinanceTypeService)
    {
        return $this->respondList(
            $request,
            KeyFinanceType::class,
            'api_admin_key_finance_type_list',
            $keyFinanceTypeService
        );
    }

    /**
     * @Route("/{id}", requirements={"id"="\d+"}, name="api_admin_key_finance_type_get", methods={"GET"})
     *
     * @param Request $request
     * @param $id
     * @param KeyFinanceTypeService $keyFinanceTypeService
     * @return JsonResponse
     */
    public function getAction(Request $request, $id, KeyFinanceTypeService $keyFinanceTypeService): JsonResponse
    {
        return $this->respondSuccess(
            Response::HTTP_OK,
            '',
            $keyFinanceTypeService->getById($id),
            ['api_admin_key_finance_type_get']
        );
    }

    /**
     * @Route("", name="api_admin_key_finance_type_add", methods={"POST"})
     *
     * @Grant(grant="persistence-common-key_finance_type", level="ADD")
     *
     * @param Request $request
     * @param KeyFinanceTypeService $keyFinanceTypeService
     * @return JsonResponse
     */
    public function addAction(Request $request, KeyFinanceTypeService $keyFinanceTypeService): JsonResponse
    {
        $id = $keyFinanceTypeService->add(
            [
                'type' => $request->get('type'),
                'title' => $request->get('title'),
                'description' => $request->get('description') ?? '',
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
     * @Route("/{id}", requirements={"id"="\d+"}, name="api_admin_key_finance_type_edit", methods={"PUT"})
     *
     * @Grant(grant="persistence-common-key_finance_type", level="EDIT")
     *
     * @param Request $request
     * @param $id
     * @param KeyFinanceTypeService $keyFinanceTypeService
     * @return JsonResponse
     */
    public function editAction(Request $request, $id, KeyFinanceTypeService $keyFinanceTypeService): JsonResponse
    {
        $keyFinanceTypeService->edit(
            $id,
            [
                'title' => $request->get('title'),
                'description' => $request->get('description') ?? '',
                'space_id' => $request->get('space_id')
            ]
        );

        return $this->respondSuccess(
            Response::HTTP_CREATED
        );
    }

    /**
     * @Route("/{id}", requirements={"id"="\d+"}, name="api_admin_key_finance_type_delete", methods={"DELETE"})
     *
     * @Grant(grant="persistence-common-key_finance_type", level="DELETE")
     *
     * @param Request $request
     * @param $id
     * @param KeyFinanceTypeService $keyFinanceTypeService
     * @return JsonResponse
     */
    public function deleteAction(Request $request, $id, KeyFinanceTypeService $keyFinanceTypeService): JsonResponse
    {
        $keyFinanceTypeService->remove($id);

        return $this->respondSuccess(
            Response::HTTP_NO_CONTENT
        );
    }

    /**
     * @Route("", name="api_admin_key_finance_type_delete_bulk", methods={"DELETE"})
     *
     * @Grant(grant="persistence-common-key_finance_type", level="DELETE")
     *
     * @param Request $request
     * @param KeyFinanceTypeService $keyFinanceTypeService
     * @return JsonResponse
     */
    public function deleteBulkAction(Request $request, KeyFinanceTypeService $keyFinanceTypeService): JsonResponse
    {
        $keyFinanceTypeService->removeBulk($request->get('ids'));

        return $this->respondSuccess(
            Response::HTTP_NO_CONTENT
        );
    }

    /**
     * @Route("/related/info", name="api_admin_key_finance_type_related_info", methods={"POST"})
     *
     * @param Request $request
     * @param KeyFinanceTypeService $keyFinanceTypeService
     * @return JsonResponse
     */
    public function relatedInfoAction(Request $request, KeyFinanceTypeService $keyFinanceTypeService): JsonResponse
    {
        $relatedData = $keyFinanceTypeService->getRelatedInfo($request->get('ids'));

        return $this->respondSuccess(
            Response::HTTP_OK,
            '',
            [$relatedData]
        );
    }
}
