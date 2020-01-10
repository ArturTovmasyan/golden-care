<?php

namespace App\Api\V1\Admin\Controller;

use App\Annotation\Grant;
use App\Api\V1\Admin\Service\AssessmentFormService;
use App\Api\V1\Common\Controller\BaseController;
use App\Entity\Assessment\Form;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\JsonResponse;
use Knp\Bundle\SnappyBundle\Snappy\Response\PdfResponse;

/**
 * @Route("/api/v1.0/admin/assessment/form")
 *
 * @Grant(grant="persistence-assessment-form", level="VIEW")
 *
 * Class AssessmentFormController
 * @package App\Api\V1\Admin\Controller
 */
class AssessmentFormController extends BaseController
{
    /**
     * @Route("/grid", name="api_admin_assessment_form_grid", methods={"GET"})
     *
     * @param Request $request
     * @param AssessmentFormService $formService
     * @return JsonResponse
     */
    public function gridAction(Request $request, AssessmentFormService $formService): JsonResponse
    {
        return $this->respondGrid(
            $request,
            Form::class,
            'api_admin_assessment_form_grid',
            $formService
        );
    }

    /**
     * @Route("/grid", name="api_admin_assessment_form_grid_options", methods={"OPTIONS"})
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function gridOptionAction(Request $request): JsonResponse
    {
        return $this->getOptionsByGroupName($request, Form::class, 'api_admin_assessment_form_grid');
    }

    /**
     * @Route("", name="api_admin_assessment_form_list", methods={"GET"})
     *
     * @param Request $request
     * @param AssessmentFormService $formService
     * @return PdfResponse|JsonResponse|Response
     */
    public function listAction(Request $request, AssessmentFormService $formService)
    {
        return $this->respondList(
            $request,
            Form::class,
            'api_admin_assessment_form_list',
            $formService
        );
    }

    /**
     * @Route("/{id}", requirements={"id"="\d+"}, name="api_admin_assessment_form_get", methods={"GET"})
     *
     * @param Request $request
     * @param $id
     * @param AssessmentFormService $formService
     * @return JsonResponse
     */
    public function getAction(Request $request, $id, AssessmentFormService $formService): JsonResponse
    {
        return $this->respondSuccess(
            Response::HTTP_OK,
            '',
            $formService->getById($id),
            ['api_admin_assessment_form_get']
        );
    }

    /**
     * @Route("", name="api_admin_assessment_form_add", methods={"POST"})
     *
     * @Grant(grant="persistence-assessment-form", level="ADD")
     *
     * @param Request $request
     * @param AssessmentFormService $formService
     * @return JsonResponse
     */
    public function addAction(Request $request, AssessmentFormService $formService): JsonResponse
    {
        $id = $formService->add(
            [
                'title' => $request->get('title'),
                'space_id' => $request->get('space_id'),
                'care_level_groups' => $request->get('care_level_groups'),
                'categories' => $request->get('categories'),
            ]
        );

        return $this->respondSuccess(
            Response::HTTP_CREATED,
            '',
            [$id]
        );
    }

    /**
     * @Route("/{id}", requirements={"id"="\d+"}, name="api_admin_assessment_form_edit", methods={"PUT"})
     *
     * @Grant(grant="persistence-assessment-form", level="EDIT")
     *
     * @param Request $request
     * @param $id
     * @param AssessmentFormService $formService
     * @return JsonResponse
     */
    public function editAction(Request $request, $id, AssessmentFormService $formService): JsonResponse
    {
        $formService->edit(
            $id,
            [
                'title' => $request->get('title'),
                'space_id' => $request->get('space_id'),
                'care_level_groups' => $request->get('care_level_groups'),
                'categories' => $request->get('categories'),
            ]
        );

        return $this->respondSuccess(
            Response::HTTP_CREATED
        );
    }

    /**
     * @Route("/{id}", requirements={"id"="\d+"}, name="api_admin_assessment_form_delete", methods={"DELETE"})
     *
     * @Grant(grant="persistence-assessment-form", level="DELETE")
     *
     * @param Request $request
     * @param $id
     * @param AssessmentFormService $formService
     * @return JsonResponse
     */
    public function deleteAction(Request $request, $id, AssessmentFormService $formService): JsonResponse
    {
        $formService->remove($id);

        return $this->respondSuccess(
            Response::HTTP_NO_CONTENT
        );
    }

    /**
     * @Route("", name="api_admin_assessment_form_delete_bulk", methods={"DELETE"})
     *
     * @Grant(grant="persistence-assessment-form", level="DELETE")
     *
     * @param Request $request
     * @param AssessmentFormService $formService
     * @return JsonResponse
     */
    public function deleteBulkAction(Request $request, AssessmentFormService $formService): JsonResponse
    {
        $formService->removeBulk($request->get('ids'));

        return $this->respondSuccess(
            Response::HTTP_NO_CONTENT
        );
    }

    /**
     * @Route("/related/info", name="api_admin_assessment_form_related_info", methods={"POST"})
     *
     * @param Request $request
     * @param AssessmentFormService $formService
     * @return JsonResponse
     */
    public function relatedInfoAction(Request $request, AssessmentFormService $formService): JsonResponse
    {
        $relatedData = $formService->getRelatedInfo($request->get('ids'));

        return $this->respondSuccess(
            Response::HTTP_OK,
            '',
            [$relatedData]
        );
    }
}
