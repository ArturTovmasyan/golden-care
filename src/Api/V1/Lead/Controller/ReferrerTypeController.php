<?php

namespace App\Api\V1\Lead\Controller;

use App\Annotation\Grant;
use App\Api\V1\Lead\Service\ReferrerTypeService;
use App\Api\V1\Common\Controller\BaseController;
use App\Entity\Lead\ReferrerType;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\JsonResponse;
use Knp\Bundle\SnappyBundle\Snappy\Response\PdfResponse;

/**
 * @Route("/api/v1.0/lead/referrer-type")
 *
 * @Grant(grant="persistence-lead-referrer_type", level="VIEW")
 *
 * Class ReferrerTypeController
 * @package App\Api\V1\Lead\Controller
 */
class ReferrerTypeController extends BaseController
{
    /**
     * @Route("/grid", name="api_lead_referrer_type", methods={"GET"})
     *
     * @param Request $request
     * @param ReferrerTypeService $referrerTypeService
     * @return JsonResponse
     */
    public function gridAction(Request $request, ReferrerTypeService $referrerTypeService): JsonResponse
    {
        return $this->respondGrid(
            $request,
            ReferrerType::class,
            'api_lead_referrer_type_grid',
            $referrerTypeService
        );
    }

    /**
     * @Route("/grid", name="api_lead_referrer_type_grid_options", methods={"OPTIONS"})
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function gridOptionAction(Request $request): JsonResponse
    {
        return $this->getOptionsByGroupName($request, ReferrerType::class, 'api_lead_referrer_type_grid');
    }

    /**
     * @Route("", name="api_lead_referrer_type_list", methods={"GET"})
     *
     * @param Request $request
     * @param ReferrerTypeService $referrerTypeService
     * @return PdfResponse|JsonResponse|Response
     */
    public function listAction(Request $request, ReferrerTypeService $referrerTypeService)
    {
        return $this->respondList(
            $request,
            ReferrerType::class,
            'api_lead_referrer_type_list',
            $referrerTypeService
        );
    }

    /**
     * @Route("/{id}", requirements={"id"="\d+"}, name="api_lead_referrer_type_get", methods={"GET"})
     *
     * @param Request $request
     * @param $id
     * @param ReferrerTypeService $referrerTypeService
     * @return JsonResponse
     */
    public function getAction(Request $request, $id, ReferrerTypeService $referrerTypeService): JsonResponse
    {
        return $this->respondSuccess(
            Response::HTTP_OK,
            '',
            $referrerTypeService->getById($id),
            ['api_lead_referrer_type_get']
        );
    }

    /**
     * @Route("", name="api_lead_referrer_type_add", methods={"POST"})
     *
     * @Grant(grant="persistence-lead-referrer_type", level="ADD")
     *
     * @param Request $request
     * @param ReferrerTypeService $referrerTypeService
     * @return JsonResponse
     */
    public function addAction(Request $request, ReferrerTypeService $referrerTypeService): JsonResponse
    {
        $id = $referrerTypeService->add(
            [
                'title' => $request->get('title'),
                'organization_required' => $request->get('organization_required'),
                'representative_required' => $request->get('representative_required'),
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
     * @Route("/{id}", requirements={"id"="\d+"}, name="api_lead_referrer_type_edit", methods={"PUT"})
     *
     * @Grant(grant="persistence-lead-referrer_type", level="EDIT")
     *
     * @param Request $request
     * @param $id
     * @param ReferrerTypeService $referrerTypeService
     * @return JsonResponse
     */
    public function editAction(Request $request, $id, ReferrerTypeService $referrerTypeService): JsonResponse
    {
        $referrerTypeService->edit(
            $id,
            [
                'title' => $request->get('title'),
                'organization_required' => $request->get('organization_required'),
                'representative_required' => $request->get('representative_required'),
                'space_id' => $request->get('space_id')
            ]
        );

        return $this->respondSuccess(
            Response::HTTP_CREATED
        );
    }

    /**
     * @Route("/{id}", requirements={"id"="\d+"}, name="api_lead_referrer_type_delete", methods={"DELETE"})
     *
     * @Grant(grant="persistence-lead-referrer_type", level="DELETE")
     *
     * @param Request $request
     * @param $id
     * @param ReferrerTypeService $referrerTypeService
     * @return JsonResponse
     */
    public function deleteAction(Request $request, $id, ReferrerTypeService $referrerTypeService): JsonResponse
    {
        $referrerTypeService->remove($id);

        return $this->respondSuccess(
            Response::HTTP_NO_CONTENT
        );
    }

    /**
     * @Route("", name="api_lead_referrer_type_delete_bulk", methods={"DELETE"})
     *
     * @Grant(grant="persistence-lead-referrer_type", level="DELETE")
     *
     * @param Request $request
     * @param ReferrerTypeService $referrerTypeService
     * @return JsonResponse
     */
    public function deleteBulkAction(Request $request, ReferrerTypeService $referrerTypeService): JsonResponse
    {
        $referrerTypeService->removeBulk($request->get('ids'));

        return $this->respondSuccess(
            Response::HTTP_NO_CONTENT
        );
    }

    /**
     * @Route("/related/info", name="api_lead_referrer_type_related_info", methods={"POST"})
     *
     * @param Request $request
     * @param ReferrerTypeService $referrerTypeService
     * @return JsonResponse
     */
    public function relatedInfoAction(Request $request, ReferrerTypeService $referrerTypeService): JsonResponse
    {
        $relatedData = $referrerTypeService->getRelatedInfo($request->get('ids'));

        return $this->respondSuccess(
            Response::HTTP_OK,
            '',
            [$relatedData]
        );
    }
}
