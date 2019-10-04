<?php
namespace App\Api\V1\Lead\Controller;

use App\Api\V1\Lead\Service\TemperatureService;
use App\Api\V1\Common\Controller\BaseController;
use App\Entity\Lead\Temperature;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\JsonResponse;
use Knp\Bundle\SnappyBundle\Snappy\Response\PdfResponse;
use App\Annotation\Grant as Grant;

/**
 * @IgnoreAnnotation("api")
 * @IgnoreAnnotation("apiVersion")
 * @IgnoreAnnotation("apiName")
 * @IgnoreAnnotation("apiGroup")
 * @IgnoreAnnotation("apiDescription")
 * @IgnoreAnnotation("apiHeader")
 * @IgnoreAnnotation("apiSuccess")
 * @IgnoreAnnotation("apiSuccessExample")
 * @IgnoreAnnotation("apiParam")
 * @IgnoreAnnotation("apiParamExample")
 * @IgnoreAnnotation("apiErrorExample")
 * @IgnoreAnnotation("apiPermission")
 *
 * @Route("/api/v1.0/lead/temperature")
 *
 * @Grant(grant="persistence-lead-temperature", level="VIEW")
 *
 * Class TemperatureController
 * @package App\Api\V1\Admin\Controller
 */
class TemperatureController extends BaseController
{
    /**
     * @Route("/grid", name="api_lead_temperature", methods={"GET"})
     *
     * @param Request $request
     * @param TemperatureService $temperatureService
     * @return JsonResponse|PdfResponse
     * @throws \ReflectionException
     */
    public function gridAction(Request $request, TemperatureService $temperatureService)
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
     * @throws \ReflectionException
     */
    public function gridOptionAction(Request $request)
    {
        return $this->getOptionsByGroupName($request, Temperature::class, 'api_lead_temperature_grid');
    }

    /**
     * @Route("", name="api_lead_temperature_list", methods={"GET"})
     *
     * @param Request $request
     * @param TemperatureService $temperatureService
     * @return JsonResponse|PdfResponse
     * @throws \ReflectionException
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
     * @param TemperatureService $temperatureService
     * @param $id
     * @return JsonResponse
     */
    public function getAction(Request $request, $id, TemperatureService $temperatureService)
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
     * @throws \Throwable
     */
    public function addAction(Request $request, TemperatureService $temperatureService)
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
     * @throws \Throwable
     */
    public function editAction(Request $request, $id, TemperatureService $temperatureService)
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
     * @param $id
     * @param TemperatureService $temperatureService
     * @return JsonResponse
     * @throws \Doctrine\DBAL\ConnectionException
     * @throws \Throwable
     */
    public function deleteAction(Request $request, $id, TemperatureService $temperatureService)
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
     * @throws \Doctrine\DBAL\ConnectionException
     * @throws \Throwable
     */
    public function deleteBulkAction(Request $request, TemperatureService $temperatureService)
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
     * @throws \Doctrine\DBAL\ConnectionException
     * @throws \Throwable
     */
    public function relatedInfoAction(Request $request, TemperatureService $temperatureService)
    {
        $relatedData = $temperatureService->getRelatedInfo($request->get('ids'));

        return $this->respondSuccess(
            Response::HTTP_OK,
            '',
            [$relatedData]
        );
    }
}
