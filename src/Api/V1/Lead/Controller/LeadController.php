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
     * @param LeadService $leadService
     * @return JsonResponse
     */
    public function gridAction(Request $request, LeadService $leadService): JsonResponse
    {
        /** @var User $user */
        $user = $this->get('security.token_storage')->getToken()->getUser();

        return $this->respondGrid(
            $request,
            Lead::class,
            'api_lead_lead_grid',
            $leadService,
            [
                'open' => $request->get('open'),
                'closed' => $request->get('closed'),
                'both' => $request->get('both'),
                'my' => $request->get('my'),
                'user_id' => $user->getId(),
                'spam' => $request->get('spam')
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
     * @param LeadService $leadService
     * @return PdfResponse|JsonResponse|Response
     */
    public function listAction(Request $request, LeadService $leadService)
    {
        /** @var User $user */
        $user = $this->get('security.token_storage')->getToken()->getUser();

        return $this->respondList(
            $request,
            Lead::class,
            'api_lead_lead_list',
            $leadService,
            [
                'all' => $request->get('all'),
                'free' => $request->get('free'),
                'spam' => $request->get('spam'),
                'my' => $request->get('my'),
                'user_id' => $user->getId(),
                'contact_id' => $request->get('contact_id'),
                'facility_id' => $request->get('facility_id'),
                'date_from' => $request->get('date_from'),
                'date_to' => $request->get('date_to')
            ]
        );
    }

    /**
     * @Route("/{id}", requirements={"id"="\d+"}, name="api_lead_lead_get", methods={"GET"})
     *
     * @param Request $request
     * @param $id
     * @param LeadService $leadService
     * @return JsonResponse
     */
    public function getAction(Request $request, $id, LeadService $leadService): JsonResponse
    {
        /** @var User $user */
        $user = $this->get('security.token_storage')->getToken()->getUser();

        $gridData = $this->respondQueryBuilderResult(
            $request,
            Lead::class,
            'api_lead_lead_grid',
            $leadService,
            [
                'all' => $request->get('all'),
                'my' => $request->get('my'),
                'user_id' => $user->getId(),
                'spam' => $request->get('spam')
            ]
        );

        return $this->respondSuccess(
            Response::HTTP_OK,
            '',
            $leadService->getById($id, $gridData),
            ['api_lead_lead_get']
        );
    }

    /**
     * @Route("", name="api_lead_lead_add", methods={"POST"})
     *
     * @Grant(grant="persistence-lead-lead", level="ADD")
     *
     * @param Request $request
     * @param LeadService $leadService
     * @return JsonResponse
     */
    public function addAction(Request $request, LeadService $leadService): JsonResponse
    {
        $id = $leadService->add(
            [
                'first_name' => $request->get('first_name'),
                'last_name' => $request->get('last_name'),
                'care_type_id' => $request->get('care_type_id'),
                'care_level_id' => $request->get('care_level_id'),
                'payment_type_id' => $request->get('payment_type_id'),
                'owner_id' => $request->get('owner_id'),
                'initial_contact_date' => $request->get('initial_contact_date'),
                'birthday' => $request->get('birthday'),
                'spouse_name' => $request->get('spouse_name'),
                'current_residence_id' => $request->get('current_residence_id'),
                'hobbies' => $request->get('hobbies'),
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
     * @Route("/{id}", requirements={"id"="\d+"}, name="api_lead_lead_edit", methods={"PUT"})
     *
     * @Grant(grant="persistence-lead-lead", level="EDIT")
     *
     * @param Request $request
     * @param $id
     * @param LeadService $leadService
     * @return JsonResponse
     */
    public function editAction(Request $request, $id, LeadService $leadService): JsonResponse
    {
        $leadService->edit(
            $id,
            [
                'first_name' => $request->get('first_name'),
                'last_name' => $request->get('last_name'),
                'care_type_id' => $request->get('care_type_id'),
                'care_level_id' => $request->get('care_level_id'),
                'payment_type_id' => $request->get('payment_type_id'),
                'owner_id' => $request->get('owner_id'),
                'birthday' => $request->get('birthday'),
                'spouse_name' => $request->get('spouse_name'),
                'current_residence_id' => $request->get('current_residence_id'),
                'hobbies' => $request->get('hobbies'),
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
     * @Route("/{id}/interest", name="api_lead_lead_interest_edit", methods={"PUT"})
     *
     * @Grant(grant="persistence-lead-lead", level="EDIT")
     *
     * @param Request $request
     * @param $id
     * @param LeadService $leadService
     * @return JsonResponse
     */
    public function editInterest(Request $request, $id, LeadService $leadService): JsonResponse
    {
        $leadService->editInterest(
            $id,
            [
                'hobbies' => $request->get('hobbies'),
                'notes' => $request->get('notes')
            ]
        );

        return $this->respondSuccess(
            Response::HTTP_CREATED
        );
    }

    /**
     * @Route("/spam", name="api_lead_lead_spam", methods={"PUT"})
     *
     * @Grant(grant="persistence-lead-lead", level="EDIT")
     *
     * @param Request $request
     * @param LeadService $leadService
     * @return JsonResponse
     */
    public function spam(Request $request, LeadService $leadService): JsonResponse
    {
        $leadService->spam(
            [
                'ids' => $request->get('ids'),
                'spam' => $request->get('spam')
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
     * @param LeadService $leadService
     * @return JsonResponse
     */
    public function deleteAction(Request $request, $id, LeadService $leadService): JsonResponse
    {
        $leadService->remove($id);

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
     * @param LeadService $leadService
     * @return JsonResponse
     */
    public function deleteBulkAction(Request $request, LeadService $leadService): JsonResponse
    {
        $leadService->removeBulk($request->get('ids'));

        return $this->respondSuccess(
            Response::HTTP_NO_CONTENT
        );
    }

    /**
     * @Route("/related/info", name="api_lead_lead_related_info", methods={"POST"})
     *
     * @param Request $request
     * @param LeadService $leadService
     * @return JsonResponse
     */
    public function relatedInfoAction(Request $request, LeadService $leadService): JsonResponse
    {
        $relatedData = $leadService->getRelatedInfo($request->get('ids'));

        return $this->respondSuccess(
            Response::HTTP_OK,
            '',
            [$relatedData]
        );
    }
}
