<?php
namespace App\Api\V1\Lead\Controller;

use App\Api\V1\Lead\Service\LeadService;
use App\Api\V1\Common\Controller\BaseController;
use App\Entity\Lead\Lead;
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
 * @Route("/api/v1.0/lead/lead")
 *
 * @Grant(grant="persistence-lead-lead", level="VIEW")
 *
 * Class LeadController
 * @package App\Api\V1\Admin\Controller
 */
class LeadController extends BaseController
{
    /**
     * @Route("/grid", name="api_lead_lead", methods={"GET"})
     *
     * @param Request $request
     * @param LeadService $activityTypeService
     * @return JsonResponse|PdfResponse
     * @throws \ReflectionException
     */
    public function gridAction(Request $request, LeadService $activityTypeService)
    {
        return $this->respondGrid(
            $request,
            Lead::class,
            'api_lead_lead_grid',
            $activityTypeService
        );
    }

    /**
     * @Route("/grid", name="api_lead_lead_grid_options", methods={"OPTIONS"})
     *
     * @param Request $request
     * @return JsonResponse
     * @throws \ReflectionException
     */
    public function gridOptionAction(Request $request)
    {
        return $this->getOptionsByGroupName(Lead::class, 'api_lead_lead_grid');
    }

    /**
     * @Route("", name="api_lead_lead_list", methods={"GET"})
     *
     * @param Request $request
     * @param LeadService $activityTypeService
     * @return JsonResponse|PdfResponse
     * @throws \ReflectionException
     */
    public function listAction(Request $request, LeadService $activityTypeService)
    {
        return $this->respondList(
            $request,
            Lead::class,
            'api_lead_lead_list',
            $activityTypeService
        );
    }

    /**
     * @Route("/{id}", requirements={"id"="\d+"}, name="api_lead_lead_get", methods={"GET"})
     *
     * @param LeadService $activityTypeService
     * @param $id
     * @return JsonResponse
     */
    public function getAction(Request $request, $id, LeadService $activityTypeService)
    {
        return $this->respondSuccess(
            Response::HTTP_OK,
            '',
            $activityTypeService->getById($id),
            ['api_lead_lead_get']
        );
    }

    /**
     * @Route("", name="api_lead_lead_add", methods={"POST"})
     *
     * @Grant(grant="persistence-lead-lead", level="ADD")
     *
     * @param Request $request
     * @param LeadService $activityTypeService
     * @return JsonResponse
     * @throws \Exception
     */
    public function addAction(Request $request, LeadService $activityTypeService)
    {
        $id = $activityTypeService->add(
            [
                'first_name' => $request->get('first_name'),
                'last_name' => $request->get('last_name'),
                'care_type_id' => $request->get('care_type_id'),
                'payment_type_id' => $request->get('payment_type_id'),
                'owner_id' => $request->get('owner_id'),
                'state_change_reason_id' => $request->get('state_change_reason_id'),
                'state_effective_date' => $request->get('state_effective_date'),
                'rp_first_name' => $request->get('rp_first_name'),
                'rp_last_name' => $request->get('rp_last_name'),
                'rp_address_1' => $request->get('rp_address_1'),
                'rp_address_2' => $request->get('rp_address_2'),
                'rp_csz_id' => $request->get('rp_csz_id'),
                'rp_phone' => $request->get('rp_phone'),
                'rp_email' => $request->get('rp_email'),
                'facility_id' => $request->get('facility_id'),
                'facilities' => $request->get('facilities'),
                'notes' => $request->get('notes')
            ]
        );

        return $this->respondSuccess(
            Response::HTTP_CREATED,
            '',
            [$id]
        );
    }

    /**
     * @Route("/{id}", requirements={"id"="\d+"}, name="api_lead_lead_edit", methods={"PUT"})
     *
     * @Grant(grant="persistence-lead-lead", level="EDIT")
     *
     * @param Request $request
     * @param $id
     * @param LeadService $activityTypeService
     * @return JsonResponse
     * @throws \Exception
     */
    public function editAction(Request $request, $id, LeadService $activityTypeService)
    {
        $activityTypeService->edit(
            $id,
            [
                'first_name' => $request->get('first_name'),
                'last_name' => $request->get('last_name'),
                'care_type_id' => $request->get('care_type_id'),
                'payment_type_id' => $request->get('payment_type_id'),
                'owner_id' => $request->get('owner_id'),
                'state_change_reason_id' => $request->get('state_change_reason_id'),
                'state_effective_date' => $request->get('state_effective_date'),
                'rp_first_name' => $request->get('rp_first_name'),
                'rp_last_name' => $request->get('rp_last_name'),
                'rp_address_1' => $request->get('rp_address_1'),
                'rp_address_2' => $request->get('rp_address_2'),
                'rp_csz_id' => $request->get('rp_csz_id'),
                'rp_phone' => $request->get('rp_phone'),
                'rp_email' => $request->get('rp_email'),
                'facility_id' => $request->get('facility_id'),
                'facilities' => $request->get('facilities'),
                'notes' => $request->get('notes')
            ]
        );

        return $this->respondSuccess(
            Response::HTTP_CREATED
        );
    }

    /**
     * @Route("/{id}", requirements={"id"="\d+"}, name="api_lead_lead_delete", methods={"DELETE"})
     *
     * @Grant(grant="persistence-lead-lead", level="DELETE")
     *
     * @param $id
     * @param LeadService $activityTypeService
     * @return JsonResponse
     * @throws \Doctrine\DBAL\ConnectionException
     * @throws \Throwable
     */
    public function deleteAction(Request $request, $id, LeadService $activityTypeService)
    {
        $activityTypeService->remove($id);

        return $this->respondSuccess(
            Response::HTTP_NO_CONTENT
        );
    }

    /**
     * @Route("", name="api_lead_lead_delete_bulk", methods={"DELETE"})
     *
     * @Grant(grant="persistence-lead-lead", level="DELETE")
     *
     * @param Request $request
     * @param LeadService $activityTypeService
     * @return JsonResponse
     * @throws \Doctrine\DBAL\ConnectionException
     * @throws \Throwable
     */
    public function deleteBulkAction(Request $request, LeadService $activityTypeService)
    {
        $activityTypeService->removeBulk($request->get('ids'));

        return $this->respondSuccess(
            Response::HTTP_NO_CONTENT
        );
    }

    /**
     * @Route("/related/info", name="api_lead_lead_related_info", methods={"POST"})
     *
     * @param Request $request
     * @param LeadService $activityTypeService
     * @return JsonResponse
     * @throws \Doctrine\DBAL\ConnectionException
     * @throws \Throwable
     */
    public function relatedInfoAction(Request $request, LeadService $activityTypeService)
    {
        $relatedData = $activityTypeService->getRelatedInfo($request->get('ids'));

        return $this->respondSuccess(
            Response::HTTP_OK,
            '',
            [$relatedData]
        );
    }
}
