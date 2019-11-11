<?php
namespace App\Api\V1\Admin\Controller;

use App\Api\V1\Admin\Service\FacilityDashboardService;
use App\Api\V1\Common\Controller\BaseController;
use App\Entity\FacilityDashboard;
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
 * @Route("/api/v1.0/admin/facility-dashboard")
 *
 * @Grant(grant="persistence-facility_dashboard", level="VIEW")
 *
 * Class FacilityDashboardController
 * @package App\Api\V1\Admin\Controller
 */
class FacilityDashboardController extends BaseController
{
    /**
     * @Route("/grid", name="api_admin_facility_dashboard_grid", methods={"GET"})
     *
     * @param Request $request
     * @param FacilityDashboardService $facilityDashboardService
     * @return JsonResponse|PdfResponse
     * @throws \ReflectionException
     */
    public function gridAction(Request $request, FacilityDashboardService $facilityDashboardService)
    {
        return $this->respondGrid(
            $request,
            FacilityDashboard::class,
            'api_admin_facility_dashboard_grid',
            $facilityDashboardService
        );
    }

    /**
     * @Route("/grid", name="api_admin_facility_dashboard_grid_options", methods={"OPTIONS"})
     *
     * @param Request $request
     * @return JsonResponse
     * @throws \ReflectionException
     */
    public function gridOptionAction(Request $request)
    {
        return $this->getOptionsByGroupName($request, FacilityDashboard::class, 'api_admin_facility_dashboard_grid');
    }

    /**
     * @Route("", name="api_admin_facility_dashboard_list", methods={"GET"})
     *
     * @param Request $request
     * @param FacilityDashboardService $facilityDashboardService
     * @return JsonResponse|PdfResponse
     * @throws \ReflectionException
     */
    public function listAction(Request $request, FacilityDashboardService $facilityDashboardService)
    {
        return $this->respondList(
            $request,
            FacilityDashboard::class,
            'api_admin_facility_dashboard_list',
            $facilityDashboardService,
            [
                'facility_id' => $request->get('facility_id'),
                'date_from' => $request->get('date_from'),
                'date_to' => $request->get('date_to'),
            ]
        );
    }

    /**
     * @Route("/{id}", requirements={"id"="\d+"}, name="api_admin_facility_dashboard_get", methods={"GET"})
     *
     * @param FacilityDashboardService $facilityDashboardService
     * @param $id
     * @return JsonResponse
     */
    public function getAction(Request $request, $id, FacilityDashboardService $facilityDashboardService)
    {
        return $this->respondSuccess(
            Response::HTTP_OK,
            '',
            $facilityDashboardService->getById($id),
            ['api_admin_facility_dashboard_get']
        );
    }

    /**
     * @Route("", name="api_admin_facility_dashboard_add", methods={"POST"})
     *
     * @Grant(grant="persistence-facility_dashboard", level="ADD")
     *
     * @param Request $request
     * @param FacilityDashboardService $facilityDashboardService
     * @return JsonResponse
     * @throws \Throwable
     */
    public function addAction(Request $request, FacilityDashboardService $facilityDashboardService)
    {
        $id = $facilityDashboardService->add(
            [
                'facility_id' => $request->get('facility_id'),
                'date' => $request->get('date'),
                'totalCapacity' => $request->get('totalCapacity'),
                'breakEven' => $request->get('breakEven'),
                'capacityYellow' => $request->get('capacityYellow'),
            ]
        );

        return $this->respondSuccess(
            Response::HTTP_CREATED,
            '',
            [$id]
        );
    }

    /**
     * @Route("/{id}", requirements={"id"="\d+"}, name="api_admin_facility_dashboard_edit", methods={"PUT"})
     *
     * @Grant(grant="persistence-facility_dashboard", level="EDIT")
     *
     * @param Request $request
     * @param $id
     * @param FacilityDashboardService $facilityDashboardService
     * @return JsonResponse
     * @throws \Throwable
     */
    public function editAction(Request $request, $id, FacilityDashboardService $facilityDashboardService)
    {
        $facilityDashboardService->edit(
            $id,
            [
                'facility_id' => $request->get('facility_id'),
                'date' => $request->get('date'),
                'totalCapacity' => $request->get('totalCapacity'),
                'breakEven' => $request->get('breakEven'),
                'capacityYellow' => $request->get('capacityYellow'),
            ]
        );

        return $this->respondSuccess(
            Response::HTTP_CREATED
        );
    }

    /**
     * @Route("/{id}", requirements={"id"="\d+"}, name="api_admin_facility_dashboard_delete", methods={"DELETE"})
     *
     * @Grant(grant="persistence-facility_dashboard", level="DELETE")
     *
     * @param $id
     * @param FacilityDashboardService $facilityDashboardService
     * @return JsonResponse
     * @throws \Doctrine\DBAL\ConnectionException
     * @throws \Throwable
     */
    public function deleteAction(Request $request, $id, FacilityDashboardService $facilityDashboardService)
    {
        $facilityDashboardService->remove($id);

        return $this->respondSuccess(
            Response::HTTP_NO_CONTENT
        );
    }

    /**
     * @Route("", name="api_admin_facility_dashboard_delete_bulk", methods={"DELETE"})
     *
     * @Grant(grant="persistence-facility_dashboard", level="DELETE")
     *
     * @param Request $request
     * @param FacilityDashboardService $facilityDashboardService
     * @return JsonResponse
     * @throws \Doctrine\DBAL\ConnectionException
     * @throws \Throwable
     */
    public function deleteBulkAction(Request $request, FacilityDashboardService $facilityDashboardService)
    {
        $facilityDashboardService->removeBulk($request->get('ids'));

        return $this->respondSuccess(
            Response::HTTP_NO_CONTENT
        );
    }

    /**
     * @Route("/related/info", name="api_admin_facility_dashboard_related_info", methods={"POST"})
     *
     * @param Request $request
     * @param FacilityDashboardService $facilityDashboardService
     * @return JsonResponse
     * @throws \Doctrine\DBAL\ConnectionException
     * @throws \Throwable
     */
    public function relatedInfoAction(Request $request, FacilityDashboardService $facilityDashboardService)
    {
        $relatedData = $facilityDashboardService->getRelatedInfo($request->get('ids'));

        return $this->respondSuccess(
            Response::HTTP_OK,
            '',
            [$relatedData]
        );
    }
}
