<?php

namespace App\Api\V1\Lead\Controller;

use App\Annotation\Grant;
use App\Api\V1\Lead\Service\TemperatureService;
use App\Api\V1\Common\Controller\BaseController;
use App\Entity\Lead\Temperature;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\JsonResponse;
use Knp\Bundle\SnappyBundle\Snappy\Response\PdfResponse;

/**
 * @Route("/api/v1.0/lead/temperature")
 *
 * @Grant(grant="persistence-lead-temperature", level="VIEW")
 *
 * Class TemperatureController
 * @package App\Api\V1\Lead\Controller
 */
class TemperatureController extends BaseController
{
    /**
     * @Route("/grid", name="api_lead_temperature", methods={"GET"})
     *
     * @param Request $request
     * @param TemperatureService $temperatureService
     * @return JsonResponse
     */
    public function gridAction(Request $request, TemperatureService $temperatureService): JsonResponse
    {
        return $this->respondGrid(
            $request,
            Temperature::class,
            'api_lead_temperature_grid',
            $temperatureService
        );
    }

    /**
     * @Route("/grid", name="api_lead_temperature_grid_options", methods={"OPTIONS"})
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function gridOptionAction(Request $request): JsonResponse
    {
        return $this->getOptionsByGroupName($request, Temperature::class, 'api_lead_temperature_grid');
    }

    /**
     * @Route("", name="api_lead_temperature_list", methods={"GET"})
     *
     * @param Request $request
     * @param TemperatureService $temperatureService
     * @return PdfResponse|JsonResponse|Response
     */
    public function listAction(Request $request, TemperatureService $temperatureService)
    {
        return $this->respondList(
            $request,
            Temperature::class,
            'api_lead_temperature_list',
            $temperatureService
        );
    }

    /**
     * @Route("/{id}", requirements={"id"="\d+"}, name="api_lead_temperature_get", methods={"GET"})
     *
     * @param Request $request
     * @param $id
     * @param TemperatureService $temperatureService
     * @return JsonResponse
     */
    public function getAction(Request $request, $id, TemperatureService $temperatureService): JsonResponse
    {
        return $this->respondSuccess(
            Response::HTTP_OK,
            '',
            $temperatureService->getById($id),
            ['api_lead_temperature_get']
        );
    }

    /**
     * @Route("", name="api_lead_temperature_add", methods={"POST"})
     *
     * @Grant(grant="persistence-lead-temperature", level="ADD")
     *
     * @param Request $request
     * @param TemperatureService $temperatureService
     * @return JsonResponse
     */
    public function addAction(Request $request, TemperatureService $temperatureService): JsonResponse
    {
        $id = $temperatureService->add(
            [
                'title' => $request->get('title'),
                'value' => $request->get('value'),
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
     * @Route("/{id}", requirements={"id"="\d+"}, name="api_lead_temperature_edit", methods={"PUT"})
     *
     * @Grant(grant="persistence-lead-temperature", level="EDIT")
     *
     * @param Request $request
     * @param $id
     * @param TemperatureService $temperatureService
     * @return JsonResponse
     */
    public function editAction(Request $request, $id, TemperatureService $temperatureService): JsonResponse
    {
        $temperatureService->edit(
            $id,
            [
                'title' => $request->get('title'),
                'value' => $request->get('value'),
                'space_id' => $request->get('space_id')
            ]
        );

        return $this->respondSuccess(
            Response::HTTP_CREATED
        );
    }

    /**
     * @Route("/{id}", requirements={"id"="\d+"}, name="api_lead_temperature_delete", methods={"DELETE"})
     *
     * @Grant(grant="persistence-lead-temperature", level="DELETE")
     *
     * @param Request $request
     * @param $id
     * @param TemperatureService $temperatureService
     * @return JsonResponse
     */
    public function deleteAction(Request $request, $id, TemperatureService $temperatureService): JsonResponse
    {
        $temperatureService->remove($id);

        return $this->respondSuccess(
            Response::HTTP_NO_CONTENT
        );
    }

    /**
     * @Route("", name="api_lead_temperature_delete_bulk", methods={"DELETE"})
     *
     * @Grant(grant="persistence-lead-temperature", level="DELETE")
     *
     * @param Request $request
     * @param TemperatureService $temperatureService
     * @return JsonResponse
     */
    public function deleteBulkAction(Request $request, TemperatureService $temperatureService): JsonResponse
    {
        $temperatureService->removeBulk($request->get('ids'));

        return $this->respondSuccess(
            Response::HTTP_NO_CONTENT
        );
    }

    /**
     * @Route("/related/info", name="api_lead_temperature_related_info", methods={"POST"})
     *
     * @param Request $request
     * @param TemperatureService $temperatureService
     * @return JsonResponse
     */
    public function relatedInfoAction(Request $request, TemperatureService $temperatureService): JsonResponse
    {
        $relatedData = $temperatureService->getRelatedInfo($request->get('ids'));

        return $this->respondSuccess(
            Response::HTTP_OK,
            '',
            [$relatedData]
        );
    }
}
