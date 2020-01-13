<?php

namespace App\Api\V1\Lead\Controller;

use App\Annotation\Grant;
use App\Api\V1\Lead\Service\LeadTemperatureService;
use App\Api\V1\Common\Controller\BaseController;
use App\Entity\Lead\LeadTemperature;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\JsonResponse;
use Knp\Bundle\SnappyBundle\Snappy\Response\PdfResponse;

/**
 * @Route("/api/v1.0/lead/lead-temperature")
 *
 * @Grant(grant="persistence-lead-lead_temperature", level="VIEW")
 *
 * Class LeadTemperatureController
 * @package App\Api\V1\Lead\Controller
 */
class LeadTemperatureController extends BaseController
{
    /**
     * @Route("/grid", name="api_lead_lead_temperature", methods={"GET"})
     *
     * @param Request $request
     * @param LeadTemperatureService $leadTemperatureService
     * @return JsonResponse
     */
    public function gridAction(Request $request, LeadTemperatureService $leadTemperatureService): JsonResponse
    {
        return $this->respondGrid(
            $request,
            LeadTemperature::class,
            'api_lead_lead_temperature_grid',
            $leadTemperatureService,
            [
                'lead_id' => $request->get('lead_id'),
            ]
        );
    }

    /**
     * @Route("/grid", name="api_lead_lead_temperature_grid_options", methods={"OPTIONS"})
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function gridOptionAction(Request $request): JsonResponse
    {
        return $this->getOptionsByGroupName($request, LeadTemperature::class, 'api_lead_lead_temperature_grid');
    }

    /**
     * @Route("", name="api_lead_lead_temperature_list", methods={"GET"})
     *
     * @param Request $request
     * @param LeadTemperatureService $leadTemperatureService
     * @return PdfResponse|JsonResponse|Response
     */
    public function listAction(Request $request, LeadTemperatureService $leadTemperatureService)
    {
        return $this->respondList(
            $request,
            LeadTemperature::class,
            'api_lead_lead_temperature_list',
            $leadTemperatureService
        );
    }

    /**
     * @Route("/{id}", requirements={"id"="\d+"}, name="api_lead_lead_temperature_get", methods={"GET"})
     *
     * @param Request $request
     * @param $id
     * @param LeadTemperatureService $leadTemperatureService
     * @return JsonResponse
     */
    public function getAction(Request $request, $id, LeadTemperatureService $leadTemperatureService): JsonResponse
    {
        return $this->respondSuccess(
            Response::HTTP_OK,
            '',
            $leadTemperatureService->getById($id),
            ['api_lead_lead_temperature_get']
        );
    }

    /**
     * @Route("", name="api_lead_lead_temperature_add", methods={"POST"})
     *
     * @Grant(grant="persistence-lead-lead_temperature", level="ADD")
     *
     * @param Request $request
     * @param LeadTemperatureService $leadTemperatureService
     * @return JsonResponse
     */
    public function addAction(Request $request, LeadTemperatureService $leadTemperatureService): JsonResponse
    {
        $id = $leadTemperatureService->add(
            [
                'lead_id' => $request->get('lead_id'),
                'temperature_id' => $request->get('temperature_id'),
                'date' => $request->get('date'),
                'notes' => $request->get('notes') ?? ''
            ]
        );

        return $this->respondSuccess(
            Response::HTTP_CREATED,
            '',
            [$id]
        );
    }

    /**
     * @Route("/{id}", requirements={"id"="\d+"}, name="api_lead_lead_temperature_edit", methods={"PUT"})
     *
     * @Grant(grant="persistence-lead-lead_temperature", level="EDIT")
     *
     * @param Request $request
     * @param $id
     * @param LeadTemperatureService $leadTemperatureService
     * @return JsonResponse
     */
    public function editAction(Request $request, $id, LeadTemperatureService $leadTemperatureService): JsonResponse
    {
        $leadTemperatureService->edit(
            $id,
            [
                'lead_id' => $request->get('lead_id'),
                'temperature_id' => $request->get('temperature_id'),
                'date' => $request->get('date'),
                'notes' => $request->get('notes') ?? ''
            ]
        );

        return $this->respondSuccess(
            Response::HTTP_CREATED
        );
    }

    /**
     * @Route("/{id}", requirements={"id"="\d+"}, name="api_lead_lead_temperature_delete", methods={"DELETE"})
     *
     * @Grant(grant="persistence-lead-lead_temperature", level="DELETE")
     *
     * @param Request $request
     * @param $id
     * @param LeadTemperatureService $leadTemperatureService
     * @return JsonResponse
     */
    public function deleteAction(Request $request, $id, LeadTemperatureService $leadTemperatureService): JsonResponse
    {
        $leadTemperatureService->remove($id);

        return $this->respondSuccess(
            Response::HTTP_NO_CONTENT
        );
    }

    /**
     * @Route("", name="api_lead_lead_temperature_delete_bulk", methods={"DELETE"})
     *
     * @Grant(grant="persistence-lead-lead_temperature", level="DELETE")
     *
     * @param Request $request
     * @param LeadTemperatureService $leadTemperatureService
     * @return JsonResponse
     */
    public function deleteBulkAction(Request $request, LeadTemperatureService $leadTemperatureService): JsonResponse
    {
        $leadTemperatureService->removeBulk($request->get('ids'));

        return $this->respondSuccess(
            Response::HTTP_NO_CONTENT
        );
    }

    /**
     * @Route("/related/info", name="api_lead_lead_temperature_related_info", methods={"POST"})
     *
     * @param Request $request
     * @param LeadTemperatureService $leadTemperatureService
     * @return JsonResponse
     */
    public function relatedInfoAction(Request $request, LeadTemperatureService $leadTemperatureService): JsonResponse
    {
        $relatedData = $leadTemperatureService->getRelatedInfo($request->get('ids'));

        return $this->respondSuccess(
            Response::HTTP_OK,
            '',
            [$relatedData]
        );
    }
}
