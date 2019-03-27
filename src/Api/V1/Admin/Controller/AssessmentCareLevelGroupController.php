<?php
namespace App\Api\V1\Admin\Controller;

use App\Api\V1\Admin\Service\AssessmentCareLevelGroupService;
use App\Api\V1\Common\Controller\BaseController;
use App\Entity\Assessment\CareLevelGroup;
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
 * @Route("/api/v1.0/admin/assessment/care/level/group")
 *
 * @Grant(grant="persistence-assessment-care_level_group", level="VIEW")
 *
 * Class AssessmentCareLevelGroupController
 * @package App\Api\V1\Admin\Controller
 */
class AssessmentCareLevelGroupController extends BaseController
{
    /**
     * @api {get} /api/v1.0/admin/assessment/care/level/group/grid Get AssessmentCareLevelGroup Grid
     * @apiVersion 1.0.0
     * @apiName Get AssessmentCareLevelGroup Grid
     * @apiGroup Admin AssessmentCareLevelGroup
     * @apiDescription This function is used to listing assessmentCareLevelGroups
     *
     * @apiHeader {String} Content-Type  application/json
     * @apiHeader {String} Authorization Bearer ACCESS_TOKEN
     *
     * @apiSuccess {Int}     id            The unique identifier of the AssessmentCareLevelGroup
     * @apiSuccess {String}  title         The title of the AssessmentCareLevelGroup
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
     * @Route("/grid", name="api_admin_assessment_care_level_group_grid", methods={"GET"})
     *
     * @param Request $request
     * @param AssessmentCareLevelGroupService $careLevelGroupService
     * @return JsonResponse|PdfResponse
     * @throws \ReflectionException
     */
    public function gridAction(Request $request, AssessmentCareLevelGroupService $careLevelGroupService)
    {
        return $this->respondGrid(
            $request,
            CareLevelGroup::class,
            'api_admin_assessment_care_level_group_grid',
            $careLevelGroupService
        );
    }

    /**
     * @api {options} /api/v1.0/admin/assessment/care/level/group/grid Get AssessmentCareLevelGroup Grid Options
     * @apiVersion 1.0.0
     * @apiName Get AssessmentCareLevelGroup Grid Options
     * @apiGroup Admin AssessmentCareLevelGroup
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
     * @Route("/grid", name="api_admin_assessment_care_level_group_grid_options", methods={"OPTIONS"})
     *
     * @param Request $request
     * @return JsonResponse
     * @throws \ReflectionException
     */
    public function gridOptionAction(Request $request)
    {
        return $this->getOptionsByGroupName(CareLevelGroup::class, 'api_admin_assessment_care_level_group_grid');
    }

    /**
     * @api {get} /api/v1.0/admin/assessment/care/level/group Get AssessmentCareLevelGroup
     * @apiVersion 1.0.0
     * @apiName Get AssessmentCareLevelGroup
     * @apiGroup Admin AssessmentCareLevelGroup
     * @apiDescription This function is used to listing AssessmentCareLevelGroups
     *
     * @apiHeader {String} Content-Type  application/json
     * @apiHeader {String} Authorization Bearer ACCESS_TOKEN
     *
     * @apiSuccess {Int}     id    The unique identifier of the AssessmentCareLevelGroup
     * @apiSuccess {String}  title The title of the AssessmentCareLevelGroup
     *
     * @apiSuccessExample {json} Sample Response:
     *     HTTP/1.1 200 OK
     *     [
     *         {
     *              "id": 1,
     *              "title": "Group 1"
     *         }
     *     ]
     *
     * @Route("", name="api_admin_assessment_care_level_group_list", methods={"GET"})
     *
     * @param Request $request
     * @param AssessmentCareLevelGroupService $careLevelGroupService
     * @return JsonResponse|PdfResponse
     * @throws \ReflectionException
     */
    public function listAction(Request $request, AssessmentCareLevelGroupService $careLevelGroupService)
    {
        return $this->respondList(
            $request,
            CareLevelGroup::class,
            'api_admin_assessment_care_level_group_list',
            $careLevelGroupService
        );
    }

    /**
     * @api {get} /api/v1.0/admin/assessment/care/level/group/{id} Get AssessmentCareLevelGroup
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
     *               "id": 1,
     *               "title": "Dr."
     *          }
     *     }
     *
     * @Route("/{id}", requirements={"id"="\d+"}, name="api_admin_assessment_care_level_group_get", methods={"GET"})
     *
     * @param AssessmentCareLevelGroupService $careLevelGroupService
     * @param $id
     * @return JsonResponse
     */
    public function getAction(Request $request, $id, AssessmentCareLevelGroupService $careLevelGroupService)
    {
        return $this->respondSuccess(
            Response::HTTP_OK,
            '',
            $careLevelGroupService->getById($id),
            ['api_admin_assessment_care_level_group_get']
        );
    }

    /**
     * @api {post} /api/v1.0/admin/assessment/care/level/group Add AssessmentCareLevelGroup
     * @apiVersion 1.0.0
     * @apiName Add AssessmentCareLevelGroup
     * @apiGroup Admin AssessmentCareLevelGroup
     * @apiDescription This function is used to add AssessmentCareLevelGroup
     *
     * @apiHeader {String} Content-Type  application/x-www-form-urlencoded
     * @apiHeader {String} Authorization Bearer ACCESS_TOKEN
     *
     * @apiParam {String}  title             The title of the AssessmentCareLevelGroup
     *
     * @apiParamExample {json} Request-Example:
     *     {
     *         "title": "Dr.",
     *         "description": "some description"
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
     * @Route("", name="api_admin_assessment_care_level_group_add", methods={"POST"})
     *
     * @Grant(grant="persistence-assessment-care_level_group", level="ADD")
     *
     * @param Request $request
     * @param AssessmentCareLevelGroupService $careLevelGroupService
     * @return JsonResponse
     * @throws \Exception
     */
    public function addAction(Request $request, AssessmentCareLevelGroupService $careLevelGroupService)
    {
        $id = $careLevelGroupService->add(
            [
                'title'    => $request->get('title'),
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
     * @api {put} /api/v1.0/admin/assessment/care/level/group/{id} Edit AssessmentCareLevelGroup
     * @apiVersion 1.0.0
     * @apiName Edit AssessmentCareLevelGroup
     * @apiGroup Admin AssessmentCareLevelGroup
     * @apiDescription This function is used to edit AssessmentCareLevelGroup
     *
     * @apiHeader {String} Content-Type  application/x-www-form-urlencoded
     * @apiHeader {String} Authorization Bearer ACCESS_TOKEN
     *
     * @apiParam {String}  title The title of the AssessmentCareLevelGroup
     *
     * @apiParamExample {json} Request-Example:
     *     {
     *         "title": "Group 1"
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
     * @Route("/{id}", requirements={"id"="\d+"}, name="api_admin_assessment_care_level_group_edit", methods={"PUT"})
     *
     * @Grant(grant="persistence-assessment-care_level_group", level="EDIT")
     *
     * @param Request $request
     * @param $id
     * @param AssessmentCareLevelGroupService $careLevelGroupService
     * @return JsonResponse
     * @throws \Exception
     */
    public function editAction(Request $request, $id, AssessmentCareLevelGroupService $careLevelGroupService)
    {
        $careLevelGroupService->edit(
            $id,
            [
                'title'    => $request->get('title'),
                'space_id' => $request->get('space_id')
            ]
        );

        return $this->respondSuccess(
            Response::HTTP_CREATED
        );
    }

    /**
     * @api {delete} /api/v1.0/admin/assessment/care/level/group/{id} Delete AssessmentCareLevelGroup
     * @apiVersion 1.0.0
     * @apiName Delete AssessmentCareLevelGroup
     * @apiGroup Admin AssessmentCareLevelGroup
     * @apiDescription This function is used to remove AssessmentCareLevelGroup
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
     * @Route("/{id}", requirements={"id"="\d+"}, name="api_admin_assessment_care_level_group_delete", methods={"DELETE"})
     *
     * @Grant(grant="persistence-assessment-care_level_group", level="DELETE")
     *
     * @param $id
     * @param AssessmentCareLevelGroupService $careLevelGroupService
     * @return JsonResponse
     * @throws \Doctrine\DBAL\ConnectionException
     * @throws \Throwable
     */
    public function deleteAction(Request $request, $id, AssessmentCareLevelGroupService $careLevelGroupService)
    {
        $careLevelGroupService->remove($id);

        return $this->respondSuccess(
            Response::HTTP_NO_CONTENT
        );
    }

    /**
     * @api {delete} /api/v1.0/admin/assessment/care/level/group Bulk Delete AssessmentCareLevelGroup
     * @apiVersion 1.0.0
     * @apiName Bulk Delete AssessmentCareLevelGroup
     * @apiGroup Admin AssessmentCareLevelGroup
     * @apiDescription This function is used to bulk remove AssessmentCareLevelGroup
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
     * @Route("", name="api_admin_assessment_care_level_group_delete_bulk", methods={"DELETE"})
     *
     * @Grant(grant="persistence-assessment-care_level_group", level="DELETE")
     *
     * @param Request $request
     * @param AssessmentCareLevelGroupService $careLevelGroupService
     * @return JsonResponse
     * @throws \Doctrine\DBAL\ConnectionException
     * @throws \Throwable
     */
    public function deleteBulkAction(Request $request, AssessmentCareLevelGroupService $careLevelGroupService)
    {
        $careLevelGroupService->removeBulk($request->get('ids'));

        return $this->respondSuccess(
            Response::HTTP_NO_CONTENT
        );
    }

    /**
     * @api {post} /api/v1.0/admin/assessment/care/level/group/related/info AssessmentCareLevelGroup related info
     * @apiVersion 1.0.0
     * @apiName AssessmentCareLevelGroup Related Info
     * @apiGroup Admin AssessmentCareLevelGroups
     * @apiDescription This function is used to get careLevelGroup related info
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
     *          "error": "AssessmentCareLevelGroup not found"
     *     }
     *
     * @Route("/related/info", name="api_admin_assessment_care_level_group_related_info", methods={"POST"})
     *
     * @param Request $request
     * @param AssessmentCareLevelGroupService $careLevelGroupService
     * @return JsonResponse
     * @throws \Doctrine\DBAL\ConnectionException
     * @throws \Throwable
     */
    public function relatedInfoAction(Request $request, AssessmentCareLevelGroupService $careLevelGroupService)
    {
        $relatedData = $careLevelGroupService->getRelatedInfo($request->get('ids'));

        return $this->respondSuccess(
            Response::HTTP_OK,
            '',
            [$relatedData]
        );
    }
}
