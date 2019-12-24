<?php
namespace App\Api\V1\Admin\Controller;

use App\Api\V1\Admin\Service\FacilityService;
use App\Api\V1\Common\Controller\BaseController;
use App\Entity\Facility;
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
 * @Route("/api/v1.0/admin/facility")
 *
 * @Grant(grant="persistence-facility", level="VIEW")
 *
 * Class FacilityController
 * @package App\Api\V1\Admin\Controller
 */
class FacilityController extends BaseController
{
    /**
     * @Route("/grid", name="api_admin_facility_grid", methods={"GET"})
     *
     * @param Request $request
     * @param FacilityService $facilityService
     * @return JsonResponse|PdfResponse
     * @throws \ReflectionException
     */
    public function gridAction(Request $request, FacilityService $facilityService)
    {
        return $this->respondGrid(
            $request,
            Facility::class,
            'api_admin_facility_grid',
            $facilityService
        );
    }

    /**
     * @Route("/grid", name="api_admin_facility_grid_options", methods={"OPTIONS"})
     *
     * @param Request $request
     * @return JsonResponse
     * @throws \ReflectionException
     */
    public function gridOptionAction(Request $request)
    {
        return $this->getOptionsByGroupName($request, Facility::class, 'api_admin_facility_grid');
    }

    /**
     * @Route("", name="api_admin_facility_list", methods={"GET"})
     *
     * @param Request $request
     * @param FacilityService $facilityService
     * @return JsonResponse|PdfResponse
     * @throws \ReflectionException
     */
    public function listAction(Request $request, FacilityService $facilityService)
    {
        return $this->respondList(
            $request,
            Facility::class,
            'api_admin_facility_list',
            $facilityService,
            [
                'all' => $request->get('all')
            ]
        );
    }

    /**
     * @Route("/{id}", requirements={"id"="\d+"}, name="api_admin_facility_get", methods={"GET"})
     *
     * @param FacilityService $facilityService
     * @param $id
     * @return JsonResponse
     */
    public function getAction(Request $request, $id, FacilityService $facilityService)
    {
        return $this->respondSuccess(
            Response::HTTP_OK,
            '',
            $facilityService->getById($id),
            ['api_admin_facility_get']
        );
    }

    /**
     * @Route("", name="api_admin_facility_add", methods={"POST"})
     *
     * @Grant(grant="persistence-facility", level="ADD")
     *
     * @param Request $request
     * @param FacilityService $facilityService
     * @return JsonResponse
     * @throws \Throwable
     */
    public function addAction(Request $request, FacilityService $facilityService)
    {
        $id = $facilityService->add(
            [
                'name' => $request->get('name'),
                'description' => $request->get('description') ?? '',
                'shorthand' => $request->get('shorthand'),
                'phone' => $request->get('phone') ?? '',
                'fax' => $request->get('fax') ?? '',
                'address' => $request->get('address'),
                'license' => $request->get('license') ?? '',
                'csz_id' => $request->get('csz_id'),
                'license_capacity' => $request->get('license_capacity'),
                'capacity' => $request->get('capacity'),
                'number_of_floors' => $request->get('number_of_floors'),
                'capacity_red' => $request->get('capacity_red'),
                'capacity_yellow' => $request->get('capacity_yellow'),
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
     * @Route("/{id}", requirements={"id"="\d+"}, name="api_admin_facility_edit", methods={"PUT"})
     *
     * @Grant(grant="persistence-facility", level="EDIT")
     *
     * @param Request $request
     * @param $id
     * @param FacilityService $facilityService
     * @return JsonResponse
     * @throws \Throwable
     */
    public function editAction(Request $request, $id, FacilityService $facilityService)
    {
        $facilityService->edit(
            $id,
            [
                'name' => $request->get('name'),
                'description' => $request->get('description') ?? '',
                'shorthand' => $request->get('shorthand'),
                'phone' => $request->get('phone') ?? '',
                'fax' => $request->get('fax') ?? '',
                'address' => $request->get('address'),
                'license' => $request->get('license') ?? '',
                'csz_id' => $request->get('csz_id'),
                'license_capacity' => $request->get('license_capacity'),
                'capacity' => $request->get('capacity'),
                'number_of_floors' => $request->get('number_of_floors'),
                'capacity_red' => $request->get('capacity_red'),
                'capacity_yellow' => $request->get('capacity_yellow'),
                'space_id' => $request->get('space_id')
            ]
        );

        return $this->respondSuccess(
            Response::HTTP_CREATED
        );
    }

    /**
     * @Route("/{id}", requirements={"id"="\d+"}, name="api_admin_facility_delete", methods={"DELETE"})
     *
     * @Grant(grant="persistence-facility", level="DELETE")
     *
     * @param $id
     * @param FacilityService $facilityService
     * @return JsonResponse
     * @throws \Doctrine\DBAL\ConnectionException
     * @throws \Throwable
     */
    public function deleteAction(Request $request, $id, FacilityService $facilityService)
    {
        $facilityService->remove($id);

        return $this->respondSuccess(
            Response::HTTP_NO_CONTENT
        );
    }

    /**
     * @Route("", name="api_admin_facility_delete_bulk", methods={"DELETE"})
     *
     * @Grant(grant="persistence-facility", level="DELETE")
     *
     * @param Request $request
     * @param FacilityService $facilityService
     * @return JsonResponse
     * @throws \Doctrine\DBAL\ConnectionException
     * @throws \Throwable
     */
    public function deleteBulkAction(Request $request, FacilityService $facilityService)
    {
        $facilityService->removeBulk($request->get('ids'));

        return $this->respondSuccess(
            Response::HTTP_NO_CONTENT
        );
    }

    /**
     * @Route("/related/info", name="api_admin_facility_related_info", methods={"POST"})
     *
     * @param Request $request
     * @param FacilityService $facilityService
     * @return JsonResponse
     * @throws \Doctrine\DBAL\ConnectionException
     * @throws \Throwable
     */
    public function relatedInfoAction(Request $request, FacilityService $facilityService)
    {
        $relatedData = $facilityService->getRelatedInfo($request->get('ids'));

        return $this->respondSuccess(
            Response::HTTP_OK,
            '',
            [$relatedData]
        );
    }

    /**
     * @Route("/mobile/list", name="api_admin_facility_mobile_list", methods={"GET"})
     *
     * @param Request $request
     * @param FacilityService $facilityService
     * @return JsonResponse
     */
    public function getMobileListAction(Request $request, FacilityService $facilityService)
    {
        return $this->respondSuccess(
            Response::HTTP_OK,
            '',
            $facilityService->getMobileList($request->headers->get('date')),
            ['api_admin_facility_mobile_list']
        );
    }

    /**
     * @Route("/calendar/{id}", requirements={"id"="\d+"}, name="api_admin_facility_calendar", methods={"GET"})
     *
     * @param Request $request
     * @param FacilityService $facilityService
     * @param $id
     * @return JsonResponse
     */
    public function getFacilityCalendarAction(Request $request, $id, FacilityService $facilityService)
    {
        return $this->respondSuccess(
            Response::HTTP_OK,
            '',
            $facilityService->getCalendar($id, $request->get('date_from'), $request->get('date_to'), $request->get('definition_id')),
            ['api_admin_facility_calendar']
        );
    }
}
