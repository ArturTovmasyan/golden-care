<?php
namespace App\Api\V1\Admin\Controller;

use App\Api\V1\Admin\Service\AssessmentFormService;
use App\Api\V1\Common\Controller\BaseController;
use App\Entity\Assessment\CareLevelGroup;
use App\Entity\Assessment\Form;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\JsonResponse;
use Knp\Bundle\SnappyBundle\Snappy\Response\PdfResponse;

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
 * @Route("/api/v1.0/admin/assessment/form")
 *
 * Class AssessmentFormController
 * @package App\Api\V1\Admin\Controller
 */
class AssessmentFormController extends BaseController
{
    /**
     * @api {get} /api/v1.0/admin/assessment/form/grid Get AssessmentForm Grid
     * @apiVersion 1.0.0
     * @apiName Get AssessmentForm Grid
     * @apiGroup Admin AssessmentForm
     * @apiDescription This function is used to listing assessmentForms
     *
     * @apiHeader {String} Content-Type  application/json
     * @apiHeader {String} Authorization Bearer ACCESS_TOKEN
     *
     * @apiSuccess {Int}     id                 The unique identifier of the AssessmentForm
     * @apiSuccess {String}  title              The title of the AssessmentForm
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
     *                  "title": "Group 1"
     *              }
     *          ]
     *     }
     *
     * @Route("/grid", name="api_admin_assessment_form_grid", methods={"GET"})
     *
     * @param Request $request
     * @param AssessmentFormService $formService
     * @return JsonResponse|PdfResponse
     * @throws \ReflectionException
     */
    public function gridAction(Request $request, AssessmentFormService $formService)
    {
        return $this->respondGrid(
            $request,
            Form::class,
            'api_admin_assessment_form_grid',
            $formService
        );
    }

    /**
     * @api {options} /api/v1.0/admin/assessment/form/grid Get AssessmentForm Grid Options
     * @apiVersion 1.0.0
     * @apiName Get AssessmentForm Grid Options
     * @apiGroup Admin AssessmentForm
     * @apiDescription This function is used to describe options of listing
     *
     * @apiHeader {String} Content-Type  application/json
     * @apiHeader {String} Authorization Bearer ACCESS_TOKEN
     *
     * @apiSuccess {Array} options The options of the care level group listing
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
     * @Route("/grid", name="api_admin_assessment_form_grid_options", methods={"OPTIONS"})
     *
     * @param Request $request
     * @return JsonResponse
     * @throws \ReflectionException
     */
    public function gridOptionAction(Request $request)
    {
        return $this->getOptionsByGroupName(Form::class, 'api_admin_assessment_form_grid');
    }

    /**
     * @api {get} /api/v1.0/admin/assessment/form Get AssessmentForm
     * @apiVersion 1.0.0
     * @apiName Get AssessmentForm
     * @apiGroup Admin AssessmentForm
     * @apiDescription This function is used to listing AssessmentForms
     *
     * @apiHeader {String} Content-Type  application/json
     * @apiHeader {String} Authorization Bearer ACCESS_TOKEN
     *
     * @apiSuccess {Int}     id    The unique identifier of the AssessmentForm
     * @apiSuccess {String}  title The title of the AssessmentForm
     *
     * @apiSuccessExample {json} Sample Response:
     *     HTTP/1.1 200 OK
     *     [
     *         {
     *              "id": 1,
     *              "title": "Form 1",
     *              "care_level_groups": [
     *                  {
     *                      "id": 1,
     *                      "title": "Group 1"
     *                  }
     *              ],
     *              "form_categories": [
     *                  {
     *                      "id": 1,
     *                      "order_number": 1,
     *                      "category": {
     *                          "id": 5,
     *                          "title": "Category 5",
     *                          "rows": [
     *                              {
     *                                  "id": 3,
     *                                  "title": "Row 1",
     *                                  "score": 1,
     *                                  "order_number": 1
     *                              }
     *                          ]
     *                      }
     *                  }
     *              ]
     *         }
     *     ]
     *
     * @Route("", name="api_admin_assessment_form_list", methods={"GET"})
     *
     * @param Request $request
     * @param AssessmentFormService $formService
     * @return JsonResponse|PdfResponse
     * @throws \ReflectionException
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
     * @api {get} /api/v1.0/admin/assessment/form/{id} Get AssessmentForm
     * @apiVersion 1.0.0
     * @apiName Get CareLevel
     * @apiGroup Admin CareLevel
     * @apiDescription This function is used to get careLevel
     *
     * @apiHeader {String} Content-Type  application/json
     * @apiHeader {String} Authorization Bearer ACCESS_TOKEN
     *
     * @apiSuccess {Int}     id     The unique identifier of the form
     * @apiSuccess {String}  title  The title of the form
     *
     * @apiSuccessExample {json} Sample Response:
     *     HTTP/1.1 200 OK
     *     {
     *              "id": 1,
     *              "title": "Form 1",
     *              "care_level_groups": [
     *                  {
     *                      "id": 1,
     *                      "title": "Group 1"
     *                  }
     *              ],
     *              "form_categories": [
     *                  {
     *                      "id": 1,
     *                      "order_number": 1,
     *                      "category": {
     *                          "id": 5,
     *                          "title": "Category 5",
     *                          "rows": [
     *                              {
     *                                  "id": 3,
     *                                  "title": "Row 1",
     *                                  "score": 1,
     *                                  "order_number": 1
     *                              }
     *                          ]
     *                      }
     *                  }
     *              ]
     *     }
     *
     * @Route("/{id}", requirements={"id"="\d+"}, name="api_admin_assessment_form_get", methods={"GET"})
     *
     * @param AssessmentFormService $formService
     * @param $id
     * @return JsonResponse
     */
    public function getAction(Request $request, $id, AssessmentFormService $formService)
    {
        return $this->respondSuccess(
            Response::HTTP_OK,
            '',
            $formService->getById($id),
            ['api_admin_assessment_form_get']
        );
    }

    /**
     * @api {post} /api/v1.0/admin/assessment/form Add AssessmentForm
     * @apiVersion 1.0.0
     * @apiName Add AssessmentForm
     * @apiGroup Admin AssessmentForm
     * @apiDescription This function is used to add AssessmentForm
     *
     * @apiHeader {String} Content-Type  application/x-www-form-urlencoded
     * @apiHeader {String} Authorization Bearer ACCESS_TOKEN
     *
     * @apiParam {String}  title             The title of the AssessmentForm
     * @apiParam {Int}     space_id          The unique identifier of space
     *
     * @apiParamExample {json} Request-Example:
     *     {
     *          "title": "Dr.",
     *          "space_id": 1,
     *          "care_level_groups": [1, 2],
     *          "categories": [5, 1]
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
     * @Route("", name="api_admin_assessment_form_add", methods={"POST"})
     *
     * @param Request $request
     * @param AssessmentFormService $formService
     * @return JsonResponse
     * @throws \Exception
     */
    public function addAction(Request $request, AssessmentFormService $formService)
    {
        $formService->add(
            [
                'title'             => $request->get('title'),
                'space_id'          => $request->get('space_id'),
                'care_level_groups' => $request->get('care_level_groups'),
                'categories'        => $request->get('categories'),
            ]
        );

        return $this->respondSuccess(
            Response::HTTP_CREATED
        );
    }

    /**
     * @api {put} /api/v1.0/admin/assessment/form/{id} Edit AssessmentForm
     * @apiVersion 1.0.0
     * @apiName Edit AssessmentForm
     * @apiGroup Admin AssessmentForm
     * @apiDescription This function is used to edit AssessmentForm
     *
     * @apiHeader {String} Content-Type  application/x-www-form-urlencoded
     * @apiHeader {String} Authorization Bearer ACCESS_TOKEN
     *
     * @apiParam {String}  title             The title of the AssessmentForm
     * @apiParam {Int}     space_id          The unique identifier of space
     *
     * @apiParamExample {json} Request-Example:
     *     {
     *          "title": "Form 1",
     *          "space_id": 1,
     *          "care_level_groups": [1, 2],
     *          "categories": [5, 1]
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
     * @Route("/{id}", requirements={"id"="\d+"}, name="api_admin_assessment_form_edit", methods={"PUT"})
     *
     * @param Request $request
     * @param $id
     * @param AssessmentFormService $formService
     * @return JsonResponse
     * @throws \Exception
     */
    public function editAction(Request $request, $id, AssessmentFormService $formService)
    {
        $formService->edit(
            $id,
            [
                'title'             => $request->get('title'),
                'space_id'          => $request->get('space_id'),
                'care_level_groups' => $request->get('care_level_groups'),
                'categories'        => $request->get('categories'),
            ]
        );

        return $this->respondSuccess(
            Response::HTTP_CREATED
        );
    }

    /**
     * @api {delete} /api/v1.0/admin/assessment/form/{id} Delete AssessmentForm
     * @apiVersion 1.0.0
     * @apiName Delete AssessmentForm
     * @apiGroup Admin AssessmentForm
     * @apiDescription This function is used to remove AssessmentForm
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
     *          "error": "CareLevel not found"
     *     }
     *
     * @Route("/{id}", requirements={"id"="\d+"}, name="api_admin_assessment_form_delete", methods={"DELETE"})
     *
     * @param $id
     * @param AssessmentFormService $formService
     * @return JsonResponse
     * @throws \Doctrine\DBAL\ConnectionException
     * @throws \Throwable
     */
    public function deleteAction(Request $request, $id, AssessmentFormService $formService)
    {
        $formService->remove($id);

        return $this->respondSuccess(
            Response::HTTP_NO_CONTENT
        );
    }

    /**
     * @api {delete} /api/v1.0/admin/assessment/form Bulk Delete AssessmentForm
     * @apiVersion 1.0.0
     * @apiName Bulk Delete AssessmentForm
     * @apiGroup Admin AssessmentForm
     * @apiDescription This function is used to bulk remove AssessmentForm
     *
     * @apiHeader {String} Content-Type  application/x-www-form-urlencoded
     * @apiHeader {String} Authorization Bearer ACCESS_TOKEN
     *
     * @apiParam {Int[]} ids The unique identifier of the careLevels
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
     *          "error": "CareLevel not found"
     *     }
     *
     * @Route("", name="api_admin_assessment_form_delete_bulk", methods={"DELETE"})
     *
     * @param Request $request
     * @param AssessmentFormService $formService
     * @return JsonResponse
     * @throws \Doctrine\DBAL\ConnectionException
     * @throws \Throwable
     */
    public function deleteBulkAction(Request $request, AssessmentFormService $formService)
    {
        $formService->removeBulk($request->get('ids'));

        return $this->respondSuccess(
            Response::HTTP_NO_CONTENT
        );
    }
}
