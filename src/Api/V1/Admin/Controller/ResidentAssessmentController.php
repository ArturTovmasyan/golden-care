<?php
namespace App\Api\V1\Admin\Controller;

use App\Api\V1\Admin\Service\ResidentAssessmentService;
use App\Api\V1\Common\Controller\BaseController;
use App\Entity\Assessment\Assessment;
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
 * @Route("/api/v1.0/admin/resident/assessment")
 *
 * @Grant(grant="persistence-resident-assessment-assessment", level="VIEW")
 *
 * Class AssessmentController
 * @package App\Api\V1\Admin\Controller
 */
class ResidentAssessmentController extends BaseController
{
    /**
     * @api {get} /api/v1.0/admin/resident/assessment/grid Get Assessment Grid
     * @apiVersion 1.0.0
     * @apiName Get Assessment Grid
     * @apiGroup Admin Assessment
     * @apiDescription This function is used to listing assessments
     *
     * @apiHeader {String} Content-Type  application/json
     * @apiHeader {String} Authorization Bearer ACCESS_TOKEN
     *
     * @apiSuccess {Int}     id                 The unique identifier of the Assessment
     * @apiSuccess {String}  form               The title of the Assessment form
     * @apiSuccess {String}  date               The date of the Assessment
     * @apiSuccess {String}  performed_by       The performed by info of the Assessment
     * @apiSuccess {String}  notes              The notes of the Assessment
     * @apiSuccess {String}  score              The calculated score of the Assessment
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
     *                  "form": "Form 1x",
     *                  "date": "1987-11-24T15:47:39+04:00",
     *                  "performed_by": "Joe",
     *                  "notes": "Custom note",
     *                  "score": 2.00
     *              }
     *          ]
     *     }
     *
     * @Route("/grid", name="api_admin_resident_assessment_grid", methods={"GET"})
     *
     * @param Request $request
     * @param ResidentAssessmentService $residentAssessmentService
     * @return JsonResponse|PdfResponse
     * @throws \ReflectionException
     */
    public function gridAction(Request $request, ResidentAssessmentService $residentAssessmentService)
    {
        return $this->respondGrid(
            $request,
            Assessment::class,
            'api_admin_resident_assessment_grid',
            $residentAssessmentService,
            ['resident_id' => $request->get('resident_id')]
        );
    }

    /**
     * @api {options} /api/v1.0/admin/resident/assessment/grid Get Assessment Grid Options
     * @apiVersion 1.0.0
     * @apiName Get Assessment Grid Options
     * @apiGroup Admin Assessment
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
     * @Route("/grid", name="api_admin_resident_assessment_grid_options", methods={"OPTIONS"})
     *
     * @param Request $request
     * @return JsonResponse
     * @throws \ReflectionException
     */
    public function gridOptionAction(Request $request)
    {
        return $this->getOptionsByGroupName(Assessment::class, 'api_admin_resident_assessment_grid');
    }

    /**
     * @api {get} /api/v1.0/admin/resident/assessment Get Assessment
     * @apiVersion 1.0.0
     * @apiName Get Assessment
     * @apiGroup Admin Assessment
     * @apiDescription This function is used to listing Assessments
     *
     * @apiHeader {String} Content-Type  application/json
     * @apiHeader {String} Authorization Bearer ACCESS_TOKEN
     *
     * @apiSuccess {Int}     id                 The unique identifier of the Assessment
     * @apiSuccess {String}  form               The form of the Assessment
     * @apiSuccess {String}  date               The date of the Assessment
     * @apiSuccess {String}  performed_by       The performed by info of the Assessment
     * @apiSuccess {String}  notes              The notes of the Assessment
     * @apiSuccess {String}  assessment_rows    The filled rows of the Assessment
     * @apiSuccess {String}  score              The calculated score of the Assessment
     *
     * @apiSuccessExample {json} Sample Response:
     *     HTTP/1.1 200 OK
     *     [
     *          "id": 3,
     *          "form": {
     *              "id": 25,
     *              "title": "Form 1x",
     *              "form_categories": [
     *                  {
     *                      "id": 8,
     *                      "order_number": 1,
     *                      "category": {
     *                          "id": 3,
     *                          "title": "Category 1",
     *                          "rows": [
     *                              {
     *                                  "id": 3,
     *                                  "title": "Row 1",
     *                                  "score": 1,
     *                                  "order_number": 1
     *                              }
     *                          ],
     *                          "multi_item": false
     *                      }
     *                  }
     *              ]
     *          },
     *          "date": "1987-11-24T15:30:37+04:00",
     *          "performed_by": "Harut",
     *          "notes": "Hello note",
     *          "assessment_rows": [
     *              {
     *                  "id": 4,
     *                  "row": {
     *                      "id": 3,
     *                      "title": "Row 1",
     *                      "score": 1,
     *                      "order_number": 1
     *                  },
     *                  "score": 1
     *              }
     *          ],
     *          "score": 2.00
     *     ]
     *
     * @Route("", name="api_admin_resident_assessment_list", methods={"GET"})
     *
     * @param Request $request
     * @param ResidentAssessmentService $residentAssessmentService
     * @return JsonResponse|PdfResponse
     * @throws \ReflectionException
     */
    public function listAction(Request $request, ResidentAssessmentService $residentAssessmentService)
    {
        return $this->respondList(
            $request,
            Assessment::class,
            'api_admin_resident_assessment_list',
            $residentAssessmentService,
            ['resident_id' => $request->get('resident_id')]
        );
    }

    /**
     * @api {get} /api/v1.0/admin/resident/assessment/{id} Get Assessment
     * @apiVersion 1.0.0
     * @apiName Get CareLevel
     * @apiGroup Admin CareLevel
     * @apiDescription This function is used to get careLevel
     *
     * @apiHeader {String} Content-Type  application/json
     * @apiHeader {String} Authorization Bearer ACCESS_TOKEN
     *
     * @apiSuccess {Int}     id                 The unique identifier of the Assessment
     * @apiSuccess {String}  form               The form of the Assessment
     * @apiSuccess {String}  date               The date of the Assessment
     * @apiSuccess {String}  performed_by       The performed by info of the Assessment
     * @apiSuccess {String}  notes              The notes of the Assessment
     * @apiSuccess {String}  assessment_rows    The filled rows of the Assessment
     * @apiSuccess {String}  score              The calculated score of the Assessment
     *
     * @apiSuccessExample {json} Sample Response:
     *     HTTP/1.1 200 OK
     *     {
     *              "id": 3,
     *              "form": {
     *                  "id": 25,
     *                  "title": "Form 1x",
     *                  "form_categories": [
     *                      {
     *                          "id": 8,
     *                          "order_number": 1,
     *                          "category": {
     *                              "id": 3,
     *                              "title": "Category 1",
     *                              "rows": [
     *                                  {
     *                                      "id": 3,
     *                                      "title": "Row 1",
     *                                      "score": 1,
     *                                      "order_number": 0
     *                                  }
     *                              ],
     *                              "multi_item": false
     *                          }
     *                      }
     *                  ]
     *              },
     *              "date": "1987-11-24T15:30:37+04:00",
     *              "performed_by": "Harut",
     *              "notes": "Hello note",
     *              "assessment_rows": [
     *                  {
     *                      "id": 4,
     *                      "row": {
     *                          "id": 3,
     *                          "title": "Row 1",
     *                          "score": 1,
     *                          "order_number": 0
     *                      },
     *                      "score": 1
     *                  }
     *              ],
     *              "score": 0
     *     }
     *
     * @Route("/{id}", requirements={"id"="\d+"}, name="api_admin_resident_assessment_get", methods={"GET"})
     *
     * @param Request $request
     * @param $id
     * @param ResidentAssessmentService $residentAssessmentService
     * @return JsonResponse
     */
    public function getAction(Request $request, $id, ResidentAssessmentService $residentAssessmentService)
    {
        return $this->respondSuccess(
            Response::HTTP_OK,
            '',
            $residentAssessmentService->getById($id),
            ['api_admin_resident_assessment_get']
        );
    }

    /**
     * @api {post} /api/v1.0/admin/resident/assessment Add Assessment
     * @apiVersion 1.0.0
     * @apiName Add Assessment
     * @apiGroup Admin Assessment
     * @apiDescription This function is used to add Assessment
     *
     * @apiHeader {String} Content-Type  application/x-www-form-urlencoded
     * @apiHeader {String} Authorization Bearer ACCESS_TOKEN
     *
     * @apiParam {Int}     space_id       The unique identifier of the space
     * @apiParam {Int}     resident_id    The unique identifier of the resident
     * @apiParam {Int}     form_id        The unique identifier of the form
     * @apiParam {String}  performed_by   The performed by info of the assessment
     * @apiParam {String}  notes          The notes of the assessment
     * @apiParam {String}  date           The date of the assessment
     * @apiParam {String}  rows           The filled rows of the assessment by form categories
     *
     * @apiParamExample {json} Request-Example:
     *     {
     *          "space_id": 1,
     *          "resident_id": 1,
     *          "form_id": 1,
     *          "performed_by": "Joe"
     *          "notes": "Custom note"
     *          "date": "11-24-1987",
     *          "rows": [3,4]
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
     * @Route("", name="api_admin_resident_assessment_add", methods={"POST"})
     *
     * @Grant(grant="persistence-resident-assessment-assessment", level="ADD")
     *
     * @param Request $request
     * @param ResidentAssessmentService $residentAssessmentService
     * @return JsonResponse
     * @throws \Exception
     */
    public function addAction(Request $request, ResidentAssessmentService $residentAssessmentService)
    {
        $id = $residentAssessmentService->add(
            [
                'space_id'     => $request->get('space_id'),
                'resident_id'  => $request->get('resident_id'),
                'form_id'      => $request->get('form_id'),
                'date'         => $request->get('date'),
                'performed_by' => $request->get('performed_by'),
                'notes'        => $request->get('notes'),
                'rows'         => $request->get('rows'),
            ]
        );

        return $this->respondSuccess(
            Response::HTTP_CREATED,
            '',
            [$id]
        );
    }

    /**
     * @api {put} /api/v1.0/admin/resident/assessment/{id} Edit Assessment
     * @apiVersion 1.0.0
     * @apiName Edit Assessment
     * @apiGroup Admin Assessment
     * @apiDescription This function is used to edit Assessment
     *
     * @apiHeader {String} Content-Type  application/x-www-form-urlencoded
     * @apiHeader {String} Authorization Bearer ACCESS_TOKEN
     *
     * @apiParam {Int}     space_id       The unique identifier of the space
     * @apiParam {Int}     resident_id    The unique identifier of the resident
     * @apiParam {Int}     form_id        The unique identifier of the form
     * @apiParam {String}  performed_by   The performed by info of the assessment
     * @apiParam {String}  notes          The notes of the assessment
     * @apiParam {String}  date           The date of the assessment
     * @apiParam {String}  rows           The filled rows of the assessment by form categories
     *
     * @apiParamExample {json} Request-Example:
     *     {
     *          "space_id": 1,
     *          "form_id": 1,
     *          "performed_by": "Joe"
     *          "notes": "Custom note"
     *          "date": "11-24-1987",
     *          "rows": [3,4]
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
     * @Route("/{id}", requirements={"id"="\d+"}, name="api_admin_resident_assessment_edit", methods={"PUT"})
     *
     * @Grant(grant="persistence-resident-assessment-assessment", level="EDIT")
     *
     * @param Request $request
     * @param $id
     * @param ResidentAssessmentService $residentAssessmentService
     * @return JsonResponse
     * @throws \Exception
     */
    public function editAction(Request $request, $id, ResidentAssessmentService $residentAssessmentService)
    {
        $residentAssessmentService->edit(
            $id,
            [
                'space_id'     => $request->get('space_id'),
                'resident_id'  => $request->get('resident_id'),
                'form_id'      => $request->get('form_id'),
                'date'         => $request->get('date'),
                'performed_by' => $request->get('performed_by'),
                'notes'        => $request->get('notes'),
                'rows'         => $request->get('rows'),
            ]
        );

        return $this->respondSuccess(
            Response::HTTP_CREATED
        );
    }

    /**
     * @api {delete} /api/v1.0/admin/resident/assessment/{id} Delete Assessment
     * @apiVersion 1.0.0
     * @apiName Delete Assessment
     * @apiGroup Admin Assessment
     * @apiDescription This function is used to remove Assessment
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
     * @Route("/{id}", requirements={"id"="\d+"}, name="api_admin_resident_assessment_delete", methods={"DELETE"})
     *
     * @Grant(grant="persistence-resident-assessment-assessment", level="DELETE")
     *
     * @param $id
     * @param ResidentAssessmentService $residentAssessmentService
     * @return JsonResponse
     * @throws \Doctrine\DBAL\ConnectionException
     * @throws \Throwable
     */
    public function deleteAction(Request $request, $id, ResidentAssessmentService $residentAssessmentService)
    {
        $residentAssessmentService->remove($id);

        return $this->respondSuccess(
            Response::HTTP_NO_CONTENT
        );
    }

    /**
     * @api {delete} /api/v1.0/admin/resident/assessment Bulk Delete Assessment
     * @apiVersion 1.0.0
     * @apiName Bulk Delete Assessment
     * @apiGroup Admin Assessment
     * @apiDescription This function is used to bulk remove Assessment
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
     * @Route("", name="api_admin_resident_assessment_delete_bulk", methods={"DELETE"})
     *
     * @Grant(grant="persistence-resident-assessment-assessment", level="DELETE")
     *
     * @param Request $request
     * @param ResidentAssessmentService $residentAssessmentService
     * @return JsonResponse
     * @throws \Doctrine\DBAL\ConnectionException
     * @throws \Throwable
     */
    public function deleteBulkAction(Request $request, ResidentAssessmentService $residentAssessmentService)
    {
        $residentAssessmentService->removeBulk($request->get('ids'));

        return $this->respondSuccess(
            Response::HTTP_NO_CONTENT
        );
    }

    /**
     * @api {post} /api/v1.0/admin/resident/assessment/related/info Assessment related info
     * @apiVersion 1.0.0
     * @apiName Assessment Related Info
     * @apiGroup Admin Assessments
     * @apiDescription This function is used to get residentAssessment related info
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
     *          "error": "Assessment not found"
     *     }
     *
     * @Route("/related/info", name="api_admin_resident_assessment_related_info", methods={"POST"})
     *
     * @param Request $request
     * @param ResidentAssessmentService $residentAssessmentService
     * @return JsonResponse
     * @throws \Doctrine\DBAL\ConnectionException
     * @throws \Throwable
     */
    public function relatedInfoAction(Request $request, ResidentAssessmentService $residentAssessmentService)
    {
        $relatedData = $residentAssessmentService->getRelatedInfo($request->get('ids'));

        return $this->respondSuccess(
            Response::HTTP_OK,
            '',
            [$relatedData]
        );
    }
}
