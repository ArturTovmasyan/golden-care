<?php

namespace App\Api\V1\Lead\Controller;

use App\Api\V1\Common\Controller\BaseController;
use App\Api\V1\Lead\Service\LeadAssessmentService;
use App\Entity\Lead\Assessment;
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
 * @Route("/api/v1.0/lead/lead/assessment")
 *
 * @Grant(grant="persistence-lead-assessment-assessment", level="VIEW")
 *
 * Class LeadAssessmentController
 * @package App\Api\V1\Lead\Controller
 */
class LeadAssessmentController extends BaseController
{
    /**
     * @Route("/grid", name="api_lead_assessment_grid", methods={"GET"})
     *
     * @param Request $request
     * @param LeadAssessmentService $leadAssessmentService
     * @return JsonResponse|PdfResponse
     * @throws \ReflectionException
     */
    public function gridAction(Request $request, LeadAssessmentService $leadAssessmentService)
    {
        return $this->respondGrid(
            $request,
            Assessment::class,
            'api_lead_assessment_grid',
            $leadAssessmentService,
            ['lead_id' => $request->get('lead_id')]
        );
    }

    /**
     * @Route("/grid", name="api_lead_assessment_grid_options", methods={"OPTIONS"})
     *
     * @param Request $request
     * @return JsonResponse
     * @throws \ReflectionException
     */
    public function gridOptionAction(Request $request)
    {
        return $this->getOptionsByGroupName($request, Assessment::class, 'api_lead_assessment_grid');
    }

    /**
     * @Route("", name="api_lead_assessment_list", methods={"GET"})
     *
     * @param Request $request
     * @param LeadAssessmentService $leadAssessmentService
     * @return JsonResponse|PdfResponse
     * @throws \ReflectionException
     */
    public function listAction(Request $request, LeadAssessmentService $leadAssessmentService)
    {
        return $this->respondList(
            $request,
            Assessment::class,
            'api_lead_assessment_list',
            $leadAssessmentService,
            ['lead_id' => $request->get('lead_id')]
        );
    }

    /**
     * @Route("/{id}", requirements={"id"="\d+"}, name="api_lead_assessment_get", methods={"GET"})
     *
     * @param Request $request
     * @param $id
     * @param LeadAssessmentService $leadAssessmentService
     * @return JsonResponse
     */
    public function getAction(Request $request, $id, LeadAssessmentService $leadAssessmentService)
    {
        return $this->respondSuccess(
            Response::HTTP_OK,
            '',
            $leadAssessmentService->getById($id),
            ['api_lead_assessment_get']
        );
    }

    /**
     * @Route("", name="api_lead_assessment_add", methods={"POST"})
     *
     * @Grant(grant="persistence-lead-assessment-assessment", level="ADD")
     *
     * @param Request $request
     * @param LeadAssessmentService $leadAssessmentService
     * @return JsonResponse
     * @throws \Throwable
     */
    public function addAction(Request $request, LeadAssessmentService $leadAssessmentService)
    {
        $id = $leadAssessmentService->add(
            [
                'lead_id' => $request->get('lead_id'),
                'form_id' => $request->get('form_id'),
                'date' => $request->get('date'),
                'performed_by' => $request->get('performed_by'),
                'notes' => $request->get('notes'),
                'rows' => $request->get('rows'),
            ]
        );

        return $this->respondSuccess(
            Response::HTTP_CREATED,
            '',
            [$id]
        );
    }

    /**
     * @Route("/{id}", requirements={"id"="\d+"}, name="api_lead_assessment_edit", methods={"PUT"})
     *
     * @Grant(grant="persistence-lead-assessment-assessment", level="EDIT")
     *
     * @param Request $request
     * @param $id
     * @param LeadAssessmentService $leadAssessmentService
     * @return JsonResponse
     * @throws \Throwable
     */
    public function editAction(Request $request, $id, LeadAssessmentService $leadAssessmentService)
    {
        $leadAssessmentService->edit(
            $id,
            [
                'lead_id' => $request->get('lead_id'),
                'form_id' => $request->get('form_id'),
                'date' => $request->get('date'),
                'performed_by' => $request->get('performed_by'),
                'notes' => $request->get('notes'),
                'rows' => $request->get('rows'),
            ]
        );

        return $this->respondSuccess(
            Response::HTTP_CREATED
        );
    }

    /**
     * @Route("/{id}", requirements={"id"="\d+"}, name="api_lead_assessment_delete", methods={"DELETE"})
     *
     * @Grant(grant="persistence-lead-assessment-assessment", level="DELETE")
     *
     * @param $id
     * @param LeadAssessmentService $leadAssessmentService
     * @return JsonResponse
     * @throws \Doctrine\DBAL\ConnectionException
     * @throws \Throwable
     */
    public function deleteAction(Request $request, $id, LeadAssessmentService $leadAssessmentService)
    {
        $leadAssessmentService->remove($id);

        return $this->respondSuccess(
            Response::HTTP_NO_CONTENT
        );
    }

    /**
     * @Route("", name="api_lead_assessment_delete_bulk", methods={"DELETE"})
     *
     * @Grant(grant="persistence-lead-assessment-assessment", level="DELETE")
     *
     * @param Request $request
     * @param LeadAssessmentService $leadAssessmentService
     * @return JsonResponse
     * @throws \Doctrine\DBAL\ConnectionException
     * @throws \Throwable
     */
    public function deleteBulkAction(Request $request, LeadAssessmentService $leadAssessmentService)
    {
        $leadAssessmentService->removeBulk($request->get('ids'));

        return $this->respondSuccess(
            Response::HTTP_NO_CONTENT
        );
    }

    /**
     * @Route("/related/info", name="api_lead_assessment_related_info", methods={"POST"})
     *
     * @param Request $request
     * @param LeadAssessmentService $leadAssessmentService
     * @return JsonResponse
     * @throws \Doctrine\DBAL\ConnectionException
     * @throws \Throwable
     */
    public function relatedInfoAction(Request $request, LeadAssessmentService $leadAssessmentService)
    {
        $relatedData = $leadAssessmentService->getRelatedInfo($request->get('ids'));

        return $this->respondSuccess(
            Response::HTTP_OK,
            '',
            [$relatedData]
        );
    }
}
