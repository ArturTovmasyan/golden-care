<?php
namespace App\Api\V1\Admin\Controller;

use App\Api\V1\Admin\Service\AssessmentCareLevelService;
use App\Api\V1\Common\Controller\BaseController;
use App\Entity\Assessment\CareLevel;
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
 * @Route("/api/v1.0/admin/assessment/care/level")
 *
 * @Grant(grant="persistence-assessment-care_level", level="VIEW")
 *
 * Class AssessmentCareLevelController
 * @package App\Api\V1\Admin\Controller
 */
class AssessmentCareLevelController extends BaseController
{
    /**
     * @api {get} /api/v1.0/admin/assessment/care/level/grid Get AssessmentCareLevel Grid
     * @apiVersion 1.0.0
     * @apiName Get AssessmentCareLevel Grid
     * @apiGroup Admin AssessmentCareLevel
     * @apiDescription This function is used to listing assessmentCareLevels
     *
     * @apiHeader {String} Content-Type  application/json
     * @apiHeader {String} Authorization Bearer ACCESS_TOKEN
     *
     * @apiSuccess {Int}     id                 The unique identifier of the AssessmentCareLevel
     * @apiSuccess {String}  title              The title of the AssessmentCareLevel
     * @apiSuccess {Int}     level_low          The lowest level of the AssessmentCareLevel
     * @apiSuccess {Int}     level_high         The highest level of the AssessmentCareLevel
     * @apiSuccess {String}  care_level_group   The care level group name of the AssessmentCareLevel
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
     *                  "title": "Group 1",
     *                  "level_low": 0,
     *                  "level_high": 5,
     *                  "care_level_group": "Group 1",
     *              }
     *          ]
     *     }
     *
     * @Route("/grid", name="api_admin_assessment_care_level_grid", methods={"GET"})
     *
     * @param Request $request
     * @param AssessmentCareLevelService $careLevelService
     * @return JsonResponse|PdfResponse
     * @throws \ReflectionException
     */
    public function gridAction(Request $request, AssessmentCareLevelService $careLevelService)
    {
        return $this->respondGrid(
            $request,
            CareLevel::class,
            'api_admin_assessment_care_level_grid',
            $careLevelService
        );
    }

    /**
     * @api {options} /api/v1.0/admin/assessment/care/level/grid Get AssessmentCareLevel Grid Options
     * @apiVersion 1.0.0
     * @apiName Get AssessmentCareLevel Grid Options
     * @apiGroup Admin AssessmentCareLevel
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
     * @Route("/grid", name="api_admin_assessment_care_level_grid_options", methods={"OPTIONS"})
     *
     * @param Request $request
     * @return JsonResponse
     * @throws \ReflectionException
     */
    public function gridOptionAction(Request $request)
    {
        return $this->getOptionsByGroupName($request, CareLevel::class, 'api_admin_assessment_care_level_grid');
    }

    /**
     * @api {get} /api/v1.0/admin/assessment/care/level Get AssessmentCareLevel
     * @apiVersion 1.0.0
     * @apiName Get AssessmentCareLevel
     * @apiGroup Admin AssessmentCareLevel
     * @apiDescription This function is used to listing AssessmentCareLevels
     *
     * @apiHeader {String} Content-Type  application/json
     * @apiHeader {String} Authorization Bearer ACCESS_TOKEN
     *
     * @apiSuccess {Int}     id    The unique identifier of the AssessmentCareLevel
     * @apiSuccess {String}  title The title of the AssessmentCareLevel
     *
     * @apiSuccessExample {json} Sample Response:
     *     HTTP/1.1 200 OK
     *     [
     *         {
     *              "id": 1,
     *              "title": "Group 1",
     *              "level_low": 0,
     *              "level_high": 5,
     *              "care_level_group": {
     *                  "id": 1,
     *                  "title": "Group 1"
     *              }
     *         }
     *     ]
     *
     * @Route("", name="api_admin_assessment_care_level_list", methods={"GET"})
     *
     * @param Request $request
     * @param AssessmentCareLevelService $careLevelService
     * @return JsonResponse|PdfResponse
     * @throws \ReflectionException
     */
    public function listAction(Request $request, AssessmentCareLevelService $careLevelService)
    {
        return $this->respondList(
            $request,
            CareLevel::class,
            'api_admin_assessment_care_level_list',
            $careLevelService
        );
    }

    /**
     * @api {get} /api/v1.0/admin/assessment/care/level/{id} Get AssessmentCareLevel
     * @apiVersion 1.0.0
     * @apiName Get CareLevel
     * @apiGroup Admin CareLevel
     * @apiDescription This function is used to get careLevel
     *
     * @apiHeader {String} Content-Type  application/json
     * @apiHeader {String} Authorization Bearer ACCESS_TOKEN
     *
     * @apiSuccess {Int}     id            The unique identifier of the careLevel
     * @apiSuccess {String}  title         The title of the careLevel
     * @apiSuccess {String}  description   The description of the careLevel
     * @apiSuccess {String}  created_at     The created time of the careLevel
     * @apiSuccess {String}  updated_at     The updated time of the careLevel
     * @apiSuccess {Int}     created_by     The created user id of the careLevel
     * @apiSuccess {Int}     updated_by     The updated user id of the careLevel
     *
     * @apiSuccessExample {json} Sample Response:
     *     HTTP/1.1 200 OK
     *     {
     *          "data": {
     *                  "id": 1,
     *                  "title": "Dr."
     *                  "level_low": 0,
     *                  "level_high": 5,
     *                  "care_level_group": {
     *                      "id": 1,
     *                      "title": "Group 1"
     *                  }
     *          }
     *     }
     *
     * @Route("/{id}", requirements={"id"="\d+"}, name="api_admin_assessment_care_level_get", methods={"GET"})
     *
     * @param AssessmentCareLevelService $careLevelService
     * @param $id
     * @return JsonResponse
     */
    public function getAction(Request $request, $id, AssessmentCareLevelService $careLevelService)
    {
        return $this->respondSuccess(
            Response::HTTP_OK,
            '',
            $careLevelService->getById($id),
            ['api_admin_assessment_care_level_get']
        );
    }

    /**
     * @api {post} /api/v1.0/admin/assessment/care/level Add AssessmentCareLevel
     * @apiVersion 1.0.0
     * @apiName Add AssessmentCareLevel
     * @apiGroup Admin AssessmentCareLevel
     * @apiDescription This function is used to add AssessmentCareLevel
     *
     * @apiHeader {String} Content-Type  application/x-www-form-urlencoded
     * @apiHeader {String} Authorization Bearer ACCESS_TOKEN
     *
     * @apiParam {String}  title             The title of the AssessmentCareLevel
     * @apiParam {Int}  space_id             The unique identifier of space
     * @apiParam {Int}  level_low            The smallest level of AssessmentCareLevel
     * @apiParam {Int}  level_high           The highest level of AssessmentCareLevel
     * @apiParam {Int}  care_level_group_id  The unique identifier of assessment care level group
     *
     * @apiParamExample {json} Request-Example:
     *     {
     *         "title": "Dr.",
     *         "space_id": 1,
     *         "level_low": 0,
     *         "level_high": 5,
     *         "care_level_group_id": 1,
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
     * @Route("", name="api_admin_assessment_care_level_add", methods={"POST"})
     *
     * @Grant(grant="persistence-assessment-care_level", level="ADD")
     *
     * @param Request $request
     * @param AssessmentCareLevelService $careLevelService
     * @return JsonResponse
     * @throws \Throwable
     */
    public function addAction(Request $request, AssessmentCareLevelService $careLevelService)
    {
        $id = $careLevelService->add(
            [
                'title'               => $request->get('title'),
                'space_id'            => $request->get('space_id'),
                'level_low'           => $request->get('level_low'),
                'level_high'          => $request->get('level_high'),
                'care_level_group_id' => $request->get('care_level_group_id'),
            ]
        );

        return $this->respondSuccess(
            Response::HTTP_CREATED,
            '',
            [$id]
        );
    }

    /**
     * @api {put} /api/v1.0/admin/assessment/care/level/{id} Edit AssessmentCareLevel
     * @apiVersion 1.0.0
     * @apiName Edit AssessmentCareLevel
     * @apiGroup Admin AssessmentCareLevel
     * @apiDescription This function is used to edit AssessmentCareLevel
     *
     * @apiHeader {String} Content-Type  application/x-www-form-urlencoded
     * @apiHeader {String} Authorization Bearer ACCESS_TOKEN
     *
     * @apiParam {String}  title             The title of the AssessmentCareLevel
     * @apiParam {Int}  space_id             The unique identifier of space
     * @apiParam {Int}  level_low            The smallest level of AssessmentCareLevel
     * @apiParam {Int}  level_high           The highest level of AssessmentCareLevel
     * @apiParam {Int}  care_level_group_id  The unique identifier of assessment care level group
     *
     * @apiParamExample {json} Request-Example:
     *     {
     *         "title": "Dr.",
     *         "space_id": 1,
     *         "level_low": 0,
     *         "level_high": 5,
     *         "care_level_group_id": 1,
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
     * @Route("/{id}", requirements={"id"="\d+"}, name="api_admin_assessment_care_level_edit", methods={"PUT"})
     *
     * @Grant(grant="persistence-assessment-care_level", level="EDIT")
     *
     * @param Request $request
     * @param $id
     * @param AssessmentCareLevelService $careLevelService
     * @return JsonResponse
     * @throws \Throwable
     */
    public function editAction(Request $request, $id, AssessmentCareLevelService $careLevelService)
    {
        $careLevelService->edit(
            $id,
            [
                'title'               => $request->get('title'),
                'space_id'            => $request->get('space_id'),
                'level_low'           => $request->get('level_low'),
                'level_high'          => $request->get('level_high'),
                'care_level_group_id' => $request->get('care_level_group_id'),
            ]
        );

        return $this->respondSuccess(
            Response::HTTP_CREATED
        );
    }

    /**
     * @api {delete} /api/v1.0/admin/assessment/care/level/{id} Delete AssessmentCareLevel
     * @apiVersion 1.0.0
     * @apiName Delete AssessmentCareLevel
     * @apiGroup Admin AssessmentCareLevel
     * @apiDescription This function is used to remove AssessmentCareLevel
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
     * @Route("/{id}", requirements={"id"="\d+"}, name="api_admin_assessment_care_level_delete", methods={"DELETE"})
     *
     * @Grant(grant="persistence-assessment-care_level", level="DELETE")
     *
     * @param $id
     * @param AssessmentCareLevelService $careLevelService
     * @return JsonResponse
     * @throws \Doctrine\DBAL\ConnectionException
     * @throws \Throwable
     */
    public function deleteAction(Request $request, $id, AssessmentCareLevelService $careLevelService)
    {
        $careLevelService->remove($id);

        return $this->respondSuccess(
            Response::HTTP_NO_CONTENT
        );
    }

    /**
     * @api {delete} /api/v1.0/admin/assessment/care/level Bulk Delete AssessmentCareLevel
     * @apiVersion 1.0.0
     * @apiName Bulk Delete AssessmentCareLevel
     * @apiGroup Admin AssessmentCareLevel
     * @apiDescription This function is used to bulk remove AssessmentCareLevel
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
     * @Route("", name="api_admin_assessment_care_level_delete_bulk", methods={"DELETE"})
     *
     * @Grant(grant="persistence-assessment-care_level", level="DELETE")
     *
     * @param Request $request
     * @param AssessmentCareLevelService $careLevelService
     * @return JsonResponse
     * @throws \Doctrine\DBAL\ConnectionException
     * @throws \Throwable
     */
    public function deleteBulkAction(Request $request, AssessmentCareLevelService $careLevelService)
    {
        $careLevelService->removeBulk($request->get('ids'));

        return $this->respondSuccess(
            Response::HTTP_NO_CONTENT
        );
    }

    /**
     * @api {post} /api/v1.0/admin/assessment/care/level/related/info AssessmentCareLevel related info
     * @apiVersion 1.0.0
     * @apiName AssessmentCareLevel Related Info
     * @apiGroup Admin AssessmentCareLevels
     * @apiDescription This function is used to get careLevelS related info
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
     *          "error": "AssessmentCareLevel not found"
     *     }
     *
     * @Route("/related/info", name="api_admin_assessment_care_level_related_info", methods={"POST"})
     *
     * @param Request $request
     * @param AssessmentCareLevelService $careLevelSService
     * @return JsonResponse
     * @throws \Doctrine\DBAL\ConnectionException
     * @throws \Throwable
     */
    public function relatedInfoAction(Request $request, AssessmentCareLevelService $careLevelSService)
    {
        $relatedData = $careLevelSService->getRelatedInfo($request->get('ids'));

        return $this->respondSuccess(
            Response::HTTP_OK,
            '',
            [$relatedData]
        );
    }
}
