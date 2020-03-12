<?php

namespace App\Api\V1\Lead\Controller;

use App\Annotation\Grant;
use App\Api\V1\Lead\Service\LeadService;
use App\Api\V1\Common\Controller\BaseController;
use App\Entity\Lead\Lead;
use App\Entity\User;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\JsonResponse;
use Knp\Bundle\SnappyBundle\Snappy\Response\PdfResponse;

/**
 * @Route("/api/v1.0/lead/lead")
 *
 * @Grant(grant="persistence-lead-lead", level="VIEW")
 *
 * Class LeadController
 * @package App\Api\V1\Lead\Controller
 */
class LeadController extends BaseController
{
    /**
     * @Route("/grid", name="api_lead_lead", methods={"GET"})
     *
     * @param Request $request
     * @param LeadService $activityTypeService
     * @return JsonResponse
     */
    public function gridAction(Request $request, LeadService $activityTypeService): JsonResponse
    {
        /** @var User $user */
        $user = $this->get('security.token_storage')->getToken()->getUser();

        return $this->respondGrid(
            $request,
            Lead::class,
            'api_lead_lead_grid',
            $activityTypeService,
            [
                'all' => $request->get('all'),
                'my' => $request->get('my'),
                'user_id' => $user->getId()
            ]
        );
    }

    /**
     * @Route("/grid", name="api_lead_lead_grid_options", methods={"OPTIONS"})
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function gridOptionAction(Request $request): JsonResponse
    {
        return $this->getOptionsByGroupName($request, Lead::class, 'api_lead_lead_grid');
    }

    /**
     * @Route("", name="api_lead_lead_list", methods={"GET"})
     *
     * @param Request $request
     * @param LeadService $activityTypeService
     * @return PdfResponse|JsonResponse|Response
     */
    public function listAction(Request $request, LeadService $activityTypeService)
    {
        /** @var User $user */
        $user = $this->get('security.token_storage')->getToken()->getUser();

        return $this->respondList(
            $request,
            Lead::class,
            'api_lead_lead_list',
            $activityTypeService,
            [
                'all' => $request->get('all'),
                'free' => $request->get('free'),
                'my' => $request->get('my'),
                'user_id' => $user->getId(),
                'contact_id' => $request->get('contact_id')
            ]
        );
    }

    /**
     * @Route("/{id}", requirements={"id"="\d+"}, name="api_lead_lead_get", methods={"GET"})
     *
     * @param Request $request
     * @param $id
     * @param LeadService $activityTypeService
     * @return JsonResponse
     */
    public function getAction(Request $request, $id, LeadService $activityTypeService): JsonResponse
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
     */
    public function addAction(Request $request, LeadService $activityTypeService): JsonResponse
    {
        $id = $activityTypeService->add(
            [
                'first_name' => $request->get('first_name'),
                'last_name' => $request->get('last_name'),
                'care_type_id' => $request->get('care_type_id'),
                'payment_type_id' => $request->get('payment_type_id'),
                'owner_id' => $request->get('owner_id'),
                'initial_contact_date' => $request->get('initial_contact_date'),
                'state_change_reason_id' => $request->get('state_change_reason_id'),
                'state_effective_date' => $request->get('state_effective_date'),
                'responsible_person_first_name' => $request->get('responsible_person_first_name'),
                'responsible_person_last_name' => $request->get('responsible_person_last_name'),
                'responsible_person_address_1' => $request->get('responsible_person_address_1'),
                'responsible_person_address_2' => $request->get('responsible_person_address_2'),
                'responsible_person_csz_id' => $request->get('responsible_person_csz_id'),
                'responsible_person_phone' => $request->get('responsible_person_phone'),
                'responsible_person_email' => $request->get('responsible_person_email'),
                'primary_facility_id' => $request->get('primary_facility_id'),
                'facilities' => $request->get('facilities'),
                'notes' => $request->get('notes'),
                'referral' => $request->get('referral'),
                'funnel_stage_id' => $request->get('funnel_stage_id'),
                'temperature_id' => $request->get('temperature_id'),
                'base_url' => $request->getSchemeAndHttpHost(),
            ]
        );

        return $this->respondSuccess(
            Response::HTTP_CREATED,
            '',
            [$id]
        );
    }

    /**
     * @Route("/zapier", name="api_lead_lead_zapier_add", methods={"POST"})
     *
     * @Grant(grant="persistence-lead-lead", level="ADD")
     *
     * @param Request $request
     * @param LeadService $activityTypeService
     * @return JsonResponse
     */
    public function addZapierAction(Request $request, LeadService $activityTypeService): JsonResponse
    {
        $id = $activityTypeService->addZapier(
            [
                'from' => $request->get('from'),
                'name' => $request->get('name'),
                'email' => $request->get('email'),
                'phone' => $request->get('phone'),
                'message' => $request->get('message'),
                'base_url' => $request->getSchemeAndHttpHost(),
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
     */
    public function editAction(Request $request, $id, LeadService $activityTypeService): JsonResponse
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
                'responsible_person_first_name' => $request->get('responsible_person_first_name'),
                'responsible_person_last_name' => $request->get('responsible_person_last_name'),
                'responsible_person_address_1' => $request->get('responsible_person_address_1'),
                'responsible_person_address_2' => $request->get('responsible_person_address_2'),
                'responsible_person_csz_id' => $request->get('responsible_person_csz_id'),
                'responsible_person_phone' => $request->get('responsible_person_phone'),
                'responsible_person_email' => $request->get('responsible_person_email'),
                'primary_facility_id' => $request->get('primary_facility_id'),
                'facilities' => $request->get('facilities'),
                'notes' => $request->get('notes'),
                'referral' => $request->get('referral')
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
     * @param Request $request
     * @param $id
     * @param LeadService $activityTypeService
     * @return JsonResponse
     */
    public function deleteAction(Request $request, $id, LeadService $activityTypeService): JsonResponse
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
     */
    public function deleteBulkAction(Request $request, LeadService $activityTypeService): JsonResponse
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
     */
    public function relatedInfoAction(Request $request, LeadService $activityTypeService): JsonResponse
    {
        $relatedData = $activityTypeService->getRelatedInfo($request->get('ids'));

        return $this->respondSuccess(
            Response::HTTP_OK,
            '',
            [$relatedData]
        );
    }
}
