<?php

namespace App\Api\V1\Admin\Controller;

use App\Annotation\Grant;
use App\Api\V1\Admin\Service\AssessmentCategoryService;
use App\Api\V1\Common\Controller\BaseController;
use App\Entity\Assessment\Category;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\JsonResponse;
use Knp\Bundle\SnappyBundle\Snappy\Response\PdfResponse;

/**
 * @Route("/api/v1.0/admin/assessment/category")
 *
 * @Grant(grant="persistence-assessment-category", level="VIEW")
 *
 * Class AssessmentCategoryController
 * @package App\Api\V1\Admin\Controller
 */
class AssessmentCategoryController extends BaseController
{
    /**
     * @Route("/grid", name="api_admin_assessment_category_grid", methods={"GET"})
     *
     * @param Request $request
     * @param AssessmentCategoryService $assessmentCategoryService
     * @return JsonResponse
     */
    public function gridAction(Request $request, AssessmentCategoryService $assessmentCategoryService): JsonResponse
    {
        return $this->respondGrid(
            $request,
            Category::class,
            'api_admin_assessment_category_grid',
            $assessmentCategoryService
        );
    }

    /**
     * @Route("/grid", name="api_admin_assessment_category_grid_options", methods={"OPTIONS"})
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function gridOptionAction(Request $request): JsonResponse
    {
        return $this->getOptionsByGroupName($request, Category::class, 'api_admin_assessment_category_grid');
    }

    /**
     * @Route("", name="api_admin_assessment_category_list", methods={"GET"})
     *
     * @param Request $request
     * @param AssessmentCategoryService $assessmentCategoryService
     * @return PdfResponse|JsonResponse|Response
     */
    public function listAction(Request $request, AssessmentCategoryService $assessmentCategoryService)
    {
        return $this->respondList(
            $request,
            Category::class,
            'api_admin_assessment_category_list',
            $assessmentCategoryService
        );
    }

    /**
     * @Route("/{id}", requirements={"id"="\d+"}, name="api_admin_assessment_category_get", methods={"GET"})
     *
     * @param Request $request
     * @param $id
     * @param AssessmentCategoryService $assessmentCategoryService
     * @return JsonResponse
     */
    public function getAction(Request $request, $id, AssessmentCategoryService $assessmentCategoryService): JsonResponse
    {
        return $this->respondSuccess(
            Response::HTTP_OK,
            '',
            $assessmentCategoryService->getById($id),
            ['api_admin_assessment_category_get']
        );
    }

    /**
     * @Route("", name="api_admin_assessment_category_add", methods={"POST"})
     *
     * @Grant(grant="persistence-assessment-category", level="ADD")
     *
     * @param Request $request
     * @param AssessmentCategoryService $assessmentCategoryService
     * @return JsonResponse
     */
    public function addAction(Request $request, AssessmentCategoryService $assessmentCategoryService): JsonResponse
    {
        $id = $assessmentCategoryService->add(
            [
                'title' => $request->get('title'),
                'space_id' => $request->get('space_id'),
                'multi_item' => $request->get('multi_item'),
                'rows' => $request->get('rows')
            ]
        );

        return $this->respondSuccess(
            Response::HTTP_CREATED,
            '',
            [$id]
        );
    }

    /**
     * @Route("/{id}", requirements={"id"="\d+"}, name="api_admin_assessment_category_edit", methods={"PUT"})
     *
     * @Grant(grant="persistence-assessment-category", level="EDIT")
     *
     * @param Request $request
     * @param $id
     * @param AssessmentCategoryService $assessmentCategoryService
     * @return JsonResponse
     */
    public function editAction(Request $request, $id, AssessmentCategoryService $assessmentCategoryService): JsonResponse
    {
        $assessmentCategoryService->edit(
            $id,
            [
                'title' => $request->get('title'),
                'space_id' => $request->get('space_id'),
                'multi_item' => $request->get('multi_item'),
                'rows' => $request->get('rows')
            ]
        );

        return $this->respondSuccess(
            Response::HTTP_CREATED
        );
    }

    /**
     * @Route("/{id}", requirements={"id"="\d+"}, name="api_admin_assessment_category_delete", methods={"DELETE"})
     *
     * @Grant(grant="persistence-assessment-category", level="DELETE")
     *
     * @param Request $request
     * @param $id
     * @param AssessmentCategoryService $assessmentCategoryService
     * @return JsonResponse
     */
    public function deleteAction(Request $request, $id, AssessmentCategoryService $assessmentCategoryService): JsonResponse
    {
        $assessmentCategoryService->remove($id);

        return $this->respondSuccess(
            Response::HTTP_NO_CONTENT
        );
    }

    /**
     * @Route("", name="api_admin_assessment_category_delete_bulk", methods={"DELETE"})
     *
     * @Grant(grant="persistence-assessment-category", level="DELETE")
     *
     * @param Request $request
     * @param AssessmentCategoryService $assessmentCategoryService
     * @return JsonResponse
     */
    public function deleteBulkAction(Request $request, AssessmentCategoryService $assessmentCategoryService): JsonResponse
    {
        $assessmentCategoryService->removeBulk($request->get('ids'));

        return $this->respondSuccess(
            Response::HTTP_NO_CONTENT
        );
    }

    /**
     * @Route("/related/info", name="api_admin_assessment_category_related_info", methods={"POST"})
     *
     * @param Request $request
     * @param AssessmentCategoryService $assessmentCategoryService
     * @return JsonResponse
     */
    public function relatedInfoAction(Request $request, AssessmentCategoryService $assessmentCategoryService): JsonResponse
    {
        $relatedData = $assessmentCategoryService->getRelatedInfo($request->get('ids'));

        return $this->respondSuccess(
            Response::HTTP_OK,
            '',
            [$relatedData]
        );
    }
}
