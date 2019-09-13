<?php
namespace App\Api\V1\Admin\Controller;

use App\Api\V1\Admin\Service\AssessmentCategoryService;
use App\Api\V1\Common\Controller\BaseController;
use App\Entity\Assessment\Category;
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
     * @api {get} /api/v1.0/admin/assessment/category/grid Get Assessment Category Grid
     * @apiVersion 1.0.0
     * @apiName Get Assessment Category Grid
     * @apiGroup Admin Assessment Categories
     * @apiDescription This function is used to listing assessment categories
     *
     * @apiHeader {String} Content-Type  application/json
     * @apiHeader {String} Authorization Bearer ACCESS_TOKEN
     *
     * @apiSuccess {Int}     id         The unique identifier of the category
     * @apiSuccess {String}  title      The title of the category
     * @apiSuccess {Int}     multi_item The multi item status time of the category
     *
     * @apiSuccessExample {json} Sample Response:
     *     HTTP/1.1 200 OK
     *     {
     *          "page": "1",
     *          "per_page": 10,
     *          "total": 5,
     *          "data": [
     *              {
     *                  "id": 1,
     *                  "title": "Test Category",
     *                  "multi_item": true
     *              }
     *          ]
     *     }
     *
     * @Route("/grid", name="api_admin_assessment_category_grid", methods={"GET"})
     *
     * @param Request $request
     * @param AssessmentCategoryService $assessmentCategoryService
     * @return JsonResponse|PdfResponse
     * @throws \ReflectionException
     */
    public function gridAction(Request $request, AssessmentCategoryService $assessmentCategoryService)
    {
        return $this->respondGrid(
            $request,
            Category::class,
            'api_admin_assessment_category_grid',
            $assessmentCategoryService
        );
    }

    /**
     * @api {options} /api/v1.0/admin/assessment/category/grid Get Assessment Category Grid Options
     * @apiVersion 1.0.0
     * @apiName Get Assessment Category Grid Options
     * @apiGroup Admin Assessment Categories
     * @apiDescription This function is used to describe options of listing
     *
     * @apiHeader {String} Content-Type  application/json
     * @apiHeader {String} Authorization Bearer ACCESS_TOKEN
     *
     * @apiSuccess {Array} options The options of the assessment category listing
     *
     * @apiSuccessExample {json} Sample Response:
     *     HTTP/1.1 200 OK
     *     {
     *          [
     *              {
     *                  "id": "name",
     *                  "type": "integer",
     *                  "sortable": true,
     *                  "filterable": true,
     *              }
     *          ]
     *     }
     *
     * @Route("/grid", name="api_admin_assessment_category_grid_options", methods={"OPTIONS"})
     *
     * @param Request $request
     * @return JsonResponse
     * @throws \ReflectionException
     */
    public function gridOptionAction(Request $request)
    {
        return $this->getOptionsByGroupName($request, Category::class, 'api_admin_assessment_category_grid');
    }

    /**
     * @api {get} /api/v1.0/admin/assessment/category Get Assessment Categories
     * @apiVersion 1.0.0
     * @apiName Get Assessment Categories
     * @apiGroup Admin Assessment Categories
     * @apiDescription This function is used to listing assessment categories
     *
     * @apiHeader {String} Content-Type  application/json
     * @apiHeader {String} Authorization Bearer ACCESS_TOKEN
     *
     * @apiSuccess {Int}     id         The unique identifier of the category
     * @apiSuccess {String}  title      The title of the category
     * @apiSuccess {Int}     multi_item The multi item status time of the category
     *
     * @apiSuccessExample {json} Sample Response:
     *     HTTP/1.1 200 OK
     *     [
     *          {
     *              "id": 1,
     *              "title": "Test Category",
     *              "multi_item": 1
     *          }
     *     ]
     *
     * @Route("", name="api_admin_assessment_category_list", methods={"GET"})
     *
     * @param Request $request
     * @param AssessmentCategoryService $assessmentCategoryService
     * @return JsonResponse|PdfResponse
     * @throws \ReflectionException
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
     * @api {get} /api/v1.0/admin/assessment/category/{id} Get Assessment Category
     * @apiVersion 1.0.0
     * @apiName Get Assessment Category
     * @apiGroup Admin Assessment Categories
     * @apiDescription This function is used to get assessment category
     *
     * @apiHeader {String} Content-Type  application/json
     * @apiHeader {String} Authorization Bearer ACCESS_TOKEN
     *
     * @apiSuccess {Int}     id         The unique identifier of the category
     * @apiSuccess {String}  title      The title of the category
     * @apiSuccess {Int}     multi_item The multi item status time of the category
     *
     * @apiSuccessExample {json} Sample Response:
     *     HTTP/1.1 200 OK
     *     {
     *          "data": {
     *              "id": 1,
     *              "title": "Test Category",
     *              "multi_item": 1
     *          }
     *     }
     *
     * @Route("/{id}", requirements={"id"="\d+"}, name="api_admin_assessment_category_get", methods={"GET"})
     *
     * @param AssessmentCategoryService $assessmentCategoryService
     * @param $id
     * @return JsonResponse
     */
    public function getAction(Request $request, $id, AssessmentCategoryService $assessmentCategoryService)
    {
        return $this->respondSuccess(
            Response::HTTP_OK,
            '',
            $assessmentCategoryService->getById($id),
            ['api_admin_assessment_category_get']
        );
    }

    /**
     * @api {post} /api/v1.0/admin/assessment/category Add Assessment Category
     * @apiVersion 1.0.0
     * @apiName Add Assessment Category
     * @apiGroup Admin Assessment Categories
     * @apiDescription This function is used to add assessment category
     *
     * @apiHeader {String} Content-Type  application/x-www-form-urlencoded
     * @apiHeader {String} Authorization Bearer ACCESS_TOKEN
     *
     * @apiParam   {String}  title      The title of the category
     * @apiParam   {Int}     space_id   The unique identifier of the space
     * @apiParam   {String}  multi_item The multi item status of the category
     *
     * @apiParamExample {json} Request-Example:
     *     {
     *          "title": "Test category",
     *          "space_id": 1,
     *          "multi_item": 1,
     *          "rows": [
     *              {
     *                  "id": 1
     *                  "title": "Category 1",
     *                  "score": 1
     *              },{
     *                  "title": "Category 2",
     *                  "score": 2
     *              },
     *          ]
     *     }
     * @apiSuccessExample {json} Sample Response:
     *     HTTP/1.1 201 Created
     *     {}
     * @apiErrorExample {json} Error-Response:
     *     HTTP/1.1 400 Bad Request
     *     {
     *          "code": 610,
     *          "error": "Validation error",
     *          "details": {
     *              "title": "Sorry, this title is already in use."
     *          }
     *     }
     *
     * @Route("", name="api_admin_assessment_category_add", methods={"POST"})
     *
     * @Grant(grant="persistence-assessment-category", level="ADD")
     *
     * @param Request $request
     * @param AssessmentCategoryService $assessmentCategoryService
     * @return JsonResponse
     * @throws \Throwable
     */
    public function addAction(Request $request, AssessmentCategoryService $assessmentCategoryService)
    {
        $id = $assessmentCategoryService->add(
            [
                'title'      => $request->get('title'),
                'space_id'   => $request->get('space_id'),
                'multi_item' => $request->get('multi_item'),
                'rows'       => $request->get('rows')
            ]
        );

        return $this->respondSuccess(
            Response::HTTP_CREATED,
            '',
            [$id]
        );
    }

    /**
     * @api {put} /api/v1.0/admin/assessment/category/{id} Edit Assessment Category
     * @apiVersion 1.0.0
     * @apiName Edit Assessment Category
     * @apiGroup Admin Assessment Categories
     * @apiDescription This function is used to edit assessment category
     *
     * @apiHeader {String} Content-Type  application/x-www-form-urlencoded
     * @apiHeader {String} Authorization Bearer ACCESS_TOKEN
     *
     * @apiParam {String}  title          The title of the assessment category
     * @apiParam {Int}     space_id       The unique identifier of the space
     * @apiParam {String}  [description]  The multi item status of the assessment category
     *
     * @apiParamExample {json} Request-Example:
     *     {
     *          "title": "Test Category",
     *          "space_id": 1,
     *          "multi_item": 1,
     *          "rows": [
     *              {
     *                  "title": "Category 1",
     *                  "score": 1
     *              },{
     *                  "title": "Category 2",
     *                  "score": 2
     *              },
     *          ]
     *     }
     * @apiSuccessExample {json} Sample Response:
     *     HTTP/1.1 201 Created
     *     {}
     * @apiErrorExample {json} Error-Response:
     *     HTTP/1.1 400 Bad Request
     *     {
     *          "code": 610,
     *          "error": "Validation error",
     *          "details": {
     *              "name": "Sorry, this title is already in use."
     *          }
     *     }
     *
     * @Route("/{id}", requirements={"id"="\d+"}, name="api_admin_assessment_category_edit", methods={"PUT"})
     *
     * @Grant(grant="persistence-assessment-category", level="EDIT")
     *
     * @param Request $request
     * @param $id
     * @param AssessmentCategoryService $assessmentCategoryService
     * @return JsonResponse
     * @throws \Throwable
     */
    public function editAction(Request $request, $id, AssessmentCategoryService $assessmentCategoryService)
    {
        $assessmentCategoryService->edit(
            $id,
            [
                'title'      => $request->get('title'),
                'space_id'   => $request->get('space_id'),
                'multi_item' => $request->get('multi_item'),
                'rows'       => $request->get('rows')
            ]
        );

        return $this->respondSuccess(
            Response::HTTP_CREATED
        );
    }

    /**
     * @api {delete} /api/v1.0/admin/assessment/category/{id} Delete Assessment Category
     * @apiVersion 1.0.0
     * @apiName Delete Assessment Category
     * @apiGroup Admin Assessment Categories
     * @apiDescription This function is used to remove assessment category
     *
     * @apiHeader {String} Content-Type  application/json
     * @apiHeader {String} Authorization Bearer ACCESS_TOKEN
     *
     * @apiSuccessExample {json} Sample Response:
     *     HTTP/1.1 204 No Content
     *     {}
     * @apiErrorExample {json} Error-Response:
     *     HTTP/1.1 400 Bad Request
     *     {
     *          "code": 624,
     *          "error": "Allergen not found"
     *     }
     *
     * @Route("/{id}", requirements={"id"="\d+"}, name="api_admin_assessment_category_delete", methods={"DELETE"})
     *
     * @Grant(grant="persistence-assessment-category", level="DELETE")
     *
     * @param $id
     * @param AssessmentCategoryService $assessmentCategoryService
     * @return JsonResponse
     * @throws \Doctrine\DBAL\ConnectionException
     * @throws \Throwable
     */
    public function deleteAction(Request $request, $id, AssessmentCategoryService $assessmentCategoryService)
    {
        $assessmentCategoryService->remove($id);

        return $this->respondSuccess(
            Response::HTTP_NO_CONTENT
        );
    }

    /**
     * @api {delete} /api/v1.0/admin/assessment/category Bulk Delete Assessment Category
     * @apiVersion 1.0.0
     * @apiName Bulk Delete Assessment Category
     * @apiGroup Admin Assessment Category
     * @apiDescription This function is used to bulk remove assessment categories
     *
     * @apiHeader {String} Content-Type  application/x-www-form-urlencoded
     * @apiHeader {String} Authorization Bearer ACCESS_TOKEN
     *
     * @apiParam {Int[]} ids The unique identifier of the assessment categories
     *
     * @apiParamExample {json} Request-Example:
     *     ["2", "1", "5"]
     *
     * @apiSuccessExample {json} Sample Response:
     *     HTTP/1.1 204 No Content
     *     {}
     * @apiErrorExample {json} Error-Response:
     *     HTTP/1.1 400 Bad Request
     *     {
     *          "code": 624,
     *          "error": "Allergen not found"
     *     }
     *
     * @Route("", name="api_admin_assessment_category_delete_bulk", methods={"DELETE"})
     *
     * @Grant(grant="persistence-assessment-category", level="DELETE")
     *
     * @param Request $request
     * @param AssessmentCategoryService $assessmentCategoryService
     * @return JsonResponse
     * @throws \Doctrine\DBAL\ConnectionException
     * @throws \Throwable
     */
    public function deleteBulkAction(Request $request, AssessmentCategoryService $assessmentCategoryService)
    {
        $assessmentCategoryService->removeBulk($request->get('ids'));

        return $this->respondSuccess(
            Response::HTTP_NO_CONTENT
        );
    }

    /**
     * @api {post} /api/v1.0/admin/assessment/category/related/info AssessmentCategory related info
     * @apiVersion 1.0.0
     * @apiName AssessmentCategory Related Info
     * @apiGroup Admin Assessment Categories
     * @apiDescription This function is used to get assessmentCategory related info
     *
     * @apiHeader {String} Content-Type  application/x-www-form-urlencoded
     * @apiHeader {String} Authorization Bearer ACCESS_TOKEN
     *
     * @apiParam {Int[]} ids The unique identifier of the facilities
     *
     * @apiParamExample {json} Request-Example:
     *     ["2", "1", "5"]
     *
     * @apiSuccessExample {json} Sample Response:
     *     HTTP/1.1 204 No Content
     *     {}
     * @apiErrorExample {json} Error-Response:
     *     HTTP/1.1 400 Bad Request
     *     {
     *          "code": 624,
     *          "error": "AssessmentCategory not found"
     *     }
     *
     * @Route("/related/info", name="api_admin_assessment_category_related_info", methods={"POST"})
     *
     * @param Request $request
     * @param AssessmentCategoryService $assessmentCategoryService
     * @return JsonResponse
     * @throws \Doctrine\DBAL\ConnectionException
     * @throws \Throwable
     */
    public function relatedInfoAction(Request $request, AssessmentCategoryService $assessmentCategoryService)
    {
        $relatedData = $assessmentCategoryService->getRelatedInfo($request->get('ids'));

        return $this->respondSuccess(
            Response::HTTP_OK,
            '',
            [$relatedData]
        );
    }
}
