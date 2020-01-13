<?php

namespace App\Api\V1\Lead\Controller;

use App\Annotation\Grant;
use App\Api\V1\Lead\Service\CareTypeService;
use App\Api\V1\Common\Controller\BaseController;
use App\Entity\Lead\CareType;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\JsonResponse;
use Knp\Bundle\SnappyBundle\Snappy\Response\PdfResponse;

/**
 * @Route("/api/v1.0/lead/care-type")
 *
 * @Grant(grant="persistence-lead-care_type", level="VIEW")
 *
 * Class CareTypeController
 * @package App\Api\V1\Lead\Controller
 */
class CareTypeController extends BaseController
{
    /**
     * @Route("/grid", name="api_lead_care_type", methods={"GET"})
     *
     * @param Request $request
     * @param CareTypeService $careTypeService
     * @return JsonResponse
     */
    public function gridAction(Request $request, CareTypeService $careTypeService): JsonResponse
    {
        return $this->respondGrid(
            $request,
            CareType::class,
            'api_lead_care_type_grid',
            $careTypeService
        );
    }

    /**
     * @Route("/grid", name="api_lead_care_type_grid_options", methods={"OPTIONS"})
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function gridOptionAction(Request $request): JsonResponse
    {
        return $this->getOptionsByGroupName($request, CareType::class, 'api_lead_care_type_grid');
    }

    /**
     * @Route("", name="api_lead_care_type_list", methods={"GET"})
     *
     * @param Request $request
     * @param CareTypeService $careTypeService
     * @return PdfResponse|JsonResponse|Response
     */
    public function listAction(Request $request, CareTypeService $careTypeService)
    {
        return $this->respondList(
            $request,
            CareType::class,
            'api_lead_care_type_list',
            $careTypeService
        );
    }

    /**
     * @Route("/{id}", requirements={"id"="\d+"}, name="api_lead_care_type_get", methods={"GET"})
     *
     * @param Request $request
     * @param $id
     * @param CareTypeService $careTypeService
     * @return JsonResponse
     */
    public function getAction(Request $request, $id, CareTypeService $careTypeService): JsonResponse
    {
        return $this->respondSuccess(
            Response::HTTP_OK,
            '',
            $careTypeService->getById($id),
            ['api_lead_care_type_get']
        );
    }

    /**
     * @Route("", name="api_lead_care_type_add", methods={"POST"})
     *
     * @Grant(grant="persistence-lead-care_type", level="ADD")
     *
     * @param Request $request
     * @param CareTypeService $careTypeService
     * @return JsonResponse
     */
    public function addAction(Request $request, CareTypeService $careTypeService): JsonResponse
    {
        $id = $careTypeService->add(
            [
                'title' => $request->get('title'),
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
     * @Route("/{id}", requirements={"id"="\d+"}, name="api_lead_care_type_edit", methods={"PUT"})
     *
     * @Grant(grant="persistence-lead-care_type", level="EDIT")
     *
     * @param Request $request
     * @param $id
     * @param CareTypeService $careTypeService
     * @return JsonResponse
     */
    public function editAction(Request $request, $id, CareTypeService $careTypeService): JsonResponse
    {
        $careTypeService->edit(
            $id,
            [
                'title' => $request->get('title'),
                'space_id' => $request->get('space_id')
            ]
        );

        return $this->respondSuccess(
            Response::HTTP_CREATED
        );
    }

    /**
     * @Route("/{id}", requirements={"id"="\d+"}, name="api_lead_care_type_delete", methods={"DELETE"})
     *
     * @Grant(grant="persistence-lead-care_type", level="DELETE")
     *
     * @param Request $request
     * @param $id
     * @param CareTypeService $careTypeService
     * @return JsonResponse
     */
    public function deleteAction(Request $request, $id, CareTypeService $careTypeService): JsonResponse
    {
        $careTypeService->remove($id);

        return $this->respondSuccess(
            Response::HTTP_NO_CONTENT
        );
    }

    /**
     * @Route("", name="api_lead_care_type_delete_bulk", methods={"DELETE"})
     *
     * @Grant(grant="persistence-lead-care_type", level="DELETE")
     *
     * @param Request $request
     * @param CareTypeService $careTypeService
     * @return JsonResponse
     */
    public function deleteBulkAction(Request $request, CareTypeService $careTypeService): JsonResponse
    {
        $careTypeService->removeBulk($request->get('ids'));

        return $this->respondSuccess(
            Response::HTTP_NO_CONTENT
        );
    }

    /**
     * @Route("/related/info", name="api_lead_care_type_related_info", methods={"POST"})
     *
     * @param Request $request
     * @param CareTypeService $careTypeService
     * @return JsonResponse
     */
    public function relatedInfoAction(Request $request, CareTypeService $careTypeService): JsonResponse
    {
        $relatedData = $careTypeService->getRelatedInfo($request->get('ids'));

        return $this->respondSuccess(
            Response::HTTP_OK,
            '',
            [$relatedData]
        );
    }
}
