<?php
namespace App\Api\V1\Admin\Controller;

use App\Api\V1\Admin\Service\CareLevelService;
use App\Api\V1\Common\Controller\BaseController;
use App\Entity\CareLevel;
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
 * @Route("/api/v1.0/admin/care/level")
 *
 * Class CareLevelController
 * @package App\Api\V1\Admin\Controller
 */
class CareLevelController extends BaseController
{
    /**
     * @api {get} /api/v1.0/admin/care/level/grid Get CareLevel Grid
     * @apiVersion 1.0.0
     * @apiName Get CareLevel Grid
     * @apiGroup Admin CareLevel
     * @apiDescription This function is used to listing careLevels
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
     *          "page": "1",
     *          "per_page": 10,
     *          "all_pages": 1,
     *          "total": 5,
     *          "data": [
     *              {
     *                  "created_at": "2018-11-01 17:24:48",
     *                  "updated_at": "2018-11-01 17:25:49",
     *                  "created_by": 1,
     *                  "updated_by": 5,
     *                  "id": 1,
     *                  "title": "Dr.",
     *                  "description": "some description"
     *              }
     *          ]
     *     }
     *
     * @Route("/grid", name="api_admin_care_level_grid", methods={"GET"})
     *
     * @param Request $request
     * @param CareLevelService $careLevelService
     * @return JsonResponse|PdfResponse
     * @throws \ReflectionException
     */
    public function gridAction(Request $request, CareLevelService $careLevelService)
    {
        return $this->respondGrid(
            $request,
            CareLevel::class,
            'api_admin_care_level_grid',
            $careLevelService
        );
    }

    /**
     * @api {options} /api/v1.0/admin/care/level/grid Get CareLevel Grid Options
     * @apiVersion 1.0.0
     * @apiName Get CareLevel Grid Options
     * @apiGroup Admin CareLevel
     * @apiDescription This function is used to describe options of listing
     *
     * @apiHeader {String} Content-Type  application/json
     * @apiHeader {String} Authorization Bearer ACCESS_TOKEN
     *
     * @apiSuccess {Array} options The options of the care level listing
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
     * @Route("/grid", name="api_admin_care_level_grid_options", methods={"OPTIONS"})
     *
     * @param Request $request
     * @return JsonResponse
     * @throws \ReflectionException
     */
    public function gridOptionAction(Request $request)
    {
        return $this->getOptionsByGroupName(CareLevel::class, 'api_admin_care_level_grid');
    }

    /**
     * @api {get} /api/v1.0/admin/care/level Get CareLevels
     * @apiVersion 1.0.0
     * @apiName Get CareLevels
     * @apiGroup Admin CareLevel
     * @apiDescription This function is used to listing careLevels
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
     *          [
     *              {
     *                  "created_at": "2018-11-01 17:24:48",
     *                  "updated_at": "2018-11-01 17:25:49",
     *                  "created_by": 1,
     *                  "updated_by": 5,
     *                  "id": 1,
     *                  "title": "Dr.",
     *                  "description": "some description"
     *              }
     *          ]
     *     }
     *
     * @Route("", name="api_admin_care_level_list", methods={"GET"})
     *
     * @param Request $request
     * @param CareLevelService $careLevelService
     * @return JsonResponse|PdfResponse
     * @throws \ReflectionException
     */
    public function listAction(Request $request, CareLevelService $careLevelService)
    {
        return $this->respondList(
            $request,
            CareLevel::class,
            'api_admin_care_level_list',
            $careLevelService
        );
    }

    /**
     * @api {get} /api/v1.0/admin/care/level/{id} Get CareLevel
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
     *                  "create_at": "2018-11-01 17:24:48",
     *                  "update_at": "2018-11-01 17:25:49",
     *                  "create_by": 1,
     *                  "update_by": 5,
     *                  "id": 1,
     *                  "title": "Dr.",
     *                  "description": "some description"
     *          }
     *     }
     *
     * @Route("/{id}", requirements={"id"="\d+"}, name="api_admin_care_level_get", methods={"GET"})
     *
     * @param CareLevelService $careLevelService
     * @param $id
     * @return JsonResponse
     */
    public function getAction(Request $request, $id, CareLevelService $careLevelService)
    {
        return $this->respondSuccess(
            Response::HTTP_OK,
            '',
            $careLevelService->getById($id),
            ['api_admin_care_level_get']
        );
    }

    /**
     * @api {post} /api/v1.0/admin/care/level Add CareLevel
     * @apiVersion 1.0.0
     * @apiName Add CareLevel
     * @apiGroup Admin CareLevel
     * @apiDescription This function is used to add careLevel
     *
     * @apiHeader {String} Content-Type  application/x-www-form-urlencoded
     * @apiHeader {String} Authorization Bearer ACCESS_TOKEN
     *
     * @apiParam {String}  title             The title of the careLevel
     * @apiParam {String}  [description]     The description of the careLevel
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
     * @Route("", name="api_admin_care_level_add", methods={"POST"})
     *
     * @param Request $request
     * @param CareLevelService $careLevelService
     * @return JsonResponse
     * @throws \Exception
     */
    public function addAction(Request $request, CareLevelService $careLevelService)
    {
        $careLevelService->add(
            [
                'title' => $request->get('title'),
                'description' => $request->get('description') ?? ''
            ]
        );

        return $this->respondSuccess(
            Response::HTTP_CREATED
        );
    }

    /**
     * @api {put} /api/v1.0/admin/care/level/{id} Edit CareLevel
     * @apiVersion 1.0.0
     * @apiName Edit CareLevel
     * @apiGroup Admin CareLevel
     * @apiDescription This function is used to edit careLevel
     *
     * @apiHeader {String} Content-Type  application/x-www-form-urlencoded
     * @apiHeader {String} Authorization Bearer ACCESS_TOKEN
     *
     * @apiParam {String}  title            The title of the careLevel
     * @apiParam {String}  [description]    The description of the careLevel
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
     *              "name": "Sorry, this title is already in use."
     *          }
     *     }
     *
     * @Route("/{id}", requirements={"id"="\d+"}, name="api_admin_care_level_edit", methods={"PUT"})
     *
     * @param Request $request
     * @param $id
     * @param CareLevelService $careLevelService
     * @return JsonResponse
     * @throws \Exception
     */
    public function editAction(Request $request, $id, CareLevelService $careLevelService)
    {
        $careLevelService->edit(
            $id,
            [
                'title' => $request->get('title'),
                'description' => $request->get('description') ?? ''
            ]
        );

        return $this->respondSuccess(
            Response::HTTP_CREATED
        );
    }

    /**
     * @api {delete} /api/v1.0/admin/care/level/{id} Delete CareLevel
     * @apiVersion 1.0.0
     * @apiName Delete CareLevel
     * @apiGroup Admin CareLevel
     * @apiDescription This function is used to remove careLevel
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
     * @Route("/{id}", requirements={"id"="\d+"}, name="api_admin_care_level_delete", methods={"DELETE"})
     *
     * @param $id
     * @param CareLevelService $careLevelService
     * @return JsonResponse
     * @throws \Doctrine\DBAL\ConnectionException
     * @throws \Throwable
     */
    public function deleteAction(Request $request, $id, CareLevelService $careLevelService)
    {
        $careLevelService->remove($id);

        return $this->respondSuccess(
            Response::HTTP_NO_CONTENT
        );
    }

    /**
     * @api {delete} /api/v1.0/admin/care/level Bulk Delete CareLevel
     * @apiVersion 1.0.0
     * @apiName Bulk Delete CareLevel
     * @apiGroup Admin CareLevel
     * @apiDescription This function is used to bulk remove careLevel
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
     * @Route("", name="api_admin_care_level_delete_bulk", methods={"DELETE"})
     *
     * @param Request $request
     * @param CareLevelService $careLevelService
     * @return JsonResponse
     * @throws \Doctrine\DBAL\ConnectionException
     * @throws \Throwable
     */
    public function deleteBulkAction(Request $request, CareLevelService $careLevelService)
    {
        $careLevelService->removeBulk($request->get('ids'));

        return $this->respondSuccess(
            Response::HTTP_NO_CONTENT
        );
    }
}