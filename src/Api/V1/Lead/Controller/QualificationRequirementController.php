<?php

namespace App\Api\V1\Lead\Controller;

use App\Annotation\Grant;
use App\Api\V1\Lead\Service\QualificationRequirementService;
use App\Api\V1\Common\Controller\BaseController;
use App\Entity\Lead\QualificationRequirement;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\JsonResponse;
use Knp\Bundle\SnappyBundle\Snappy\Response\PdfResponse;

/**
 * @Route("/api/v1.0/lead/qualification-requirement")
 *
 * @Grant(grant="persistence-lead-qualification_requirement", level="VIEW")
 *
 * Class QualificationRequirementController
 * @package App\Api\V1\Lead\Controller
 */
class QualificationRequirementController extends BaseController
{
    /**
     * @Route("/grid", name="api_lead_qualification_requirement", methods={"GET"})
     *
     * @param Request $request
     * @param QualificationRequirementService $qualificationRequirementService
     * @return JsonResponse
     */
    public function gridAction(Request $request, QualificationRequirementService $qualificationRequirementService): JsonResponse
    {
        return $this->respondGrid(
            $request,
            QualificationRequirement::class,
            'api_lead_qualification_requirement_grid',
            $qualificationRequirementService
        );
    }

    /**
     * @Route("/grid", name="api_lead_qualification_requirement_grid_options", methods={"OPTIONS"})
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function gridOptionAction(Request $request): JsonResponse
    {
        return $this->getOptionsByGroupName($request, QualificationRequirement::class, 'api_lead_qualification_requirement_grid');
    }

    /**
     * @Route("", name="api_lead_qualification_requirement_list", methods={"GET"})
     *
     * @param Request $request
     * @param QualificationRequirementService $qualificationRequirementService
     * @return PdfResponse|JsonResponse|Response
     */
    public function listAction(Request $request, QualificationRequirementService $qualificationRequirementService)
    {
        return $this->respondList(
            $request,
            QualificationRequirement::class,
            'api_lead_qualification_requirement_list',
            $qualificationRequirementService
        );
    }

    /**
     * @Route("/{id}", requirements={"id"="\d+"}, name="api_lead_qualification_requirement_get", methods={"GET"})
     *
     * @param Request $request
     * @param $id
     * @param QualificationRequirementService $qualificationRequirementService
     * @return JsonResponse
     */
    public function getAction(Request $request, $id, QualificationRequirementService $qualificationRequirementService): JsonResponse
    {
        return $this->respondSuccess(
            Response::HTTP_OK,
            '',
            $qualificationRequirementService->getById($id),
            ['api_lead_qualification_requirement_get']
        );
    }

    /**
     * @Route("", name="api_lead_qualification_requirement_add", methods={"POST"})
     *
     * @Grant(grant="persistence-lead-qualification_requirement", level="ADD")
     *
     * @param Request $request
     * @param QualificationRequirementService $qualificationRequirementService
     * @return JsonResponse
     */
    public function addAction(Request $request, QualificationRequirementService $qualificationRequirementService): JsonResponse
    {
        $id = $qualificationRequirementService->add(
            [
                'title' => $request->get('title'),
                'use' => $request->get('use'),
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
     * @Route("/{id}", requirements={"id"="\d+"}, name="api_lead_qualification_requirement_edit", methods={"PUT"})
     *
     * @Grant(grant="persistence-lead-qualification_requirement", level="EDIT")
     *
     * @param Request $request
     * @param $id
     * @param QualificationRequirementService $qualificationRequirementService
     * @return JsonResponse
     */
    public function editAction(Request $request, $id, QualificationRequirementService $qualificationRequirementService): JsonResponse
    {
        $qualificationRequirementService->edit(
            $id,
            [
                'title' => $request->get('title'),
                'use' => $request->get('use'),
                'space_id' => $request->get('space_id')
            ]
        );

        return $this->respondSuccess(
            Response::HTTP_CREATED
        );
    }

    /**
     * @Route("/{id}", requirements={"id"="\d+"}, name="api_lead_qualification_requirement_delete", methods={"DELETE"})
     *
     * @Grant(grant="persistence-lead-qualification_requirement", level="DELETE")
     *
     * @param Request $request
     * @param $id
     * @param QualificationRequirementService $qualificationRequirementService
     * @return JsonResponse
     */
    public function deleteAction(Request $request, $id, QualificationRequirementService $qualificationRequirementService): JsonResponse
    {
        $qualificationRequirementService->remove($id);

        return $this->respondSuccess(
            Response::HTTP_NO_CONTENT
        );
    }

    /**
     * @Route("", name="api_lead_qualification_requirement_delete_bulk", methods={"DELETE"})
     *
     * @Grant(grant="persistence-lead-qualification_requirement", level="DELETE")
     *
     * @param Request $request
     * @param QualificationRequirementService $qualificationRequirementService
     * @return JsonResponse
     */
    public function deleteBulkAction(Request $request, QualificationRequirementService $qualificationRequirementService): JsonResponse
    {
        $qualificationRequirementService->removeBulk($request->get('ids'));

        return $this->respondSuccess(
            Response::HTTP_NO_CONTENT
        );
    }

    /**
     * @Route("/related/info", name="api_lead_qualification_requirement_related_info", methods={"POST"})
     *
     * @param Request $request
     * @param QualificationRequirementService $qualificationRequirementService
     * @return JsonResponse
     */
    public function relatedInfoAction(Request $request, QualificationRequirementService $qualificationRequirementService): JsonResponse
    {
        $relatedData = $qualificationRequirementService->getRelatedInfo($request->get('ids'));

        return $this->respondSuccess(
            Response::HTTP_OK,
            '',
            [$relatedData]
        );
    }
}
