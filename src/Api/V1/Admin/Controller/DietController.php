<?php
namespace App\Api\V1\Admin\Controller;

use App\Api\V1\Admin\Service\DietService;
use App\Api\V1\Common\Controller\BaseController;
use App\Entity\Diet;
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
 * @Route("/api/v1.0/admin/diet")
 *
 * @Grant(grant="persistence-common-diet", level="VIEW")
 *
 * Class DietController
 * @package App\Api\V1\Admin\Controller
 */
class DietController extends BaseController
{
    /**
     * @api {get} /api/v1.0/admin/diet/grid Get Diets Grid
     * @apiVersion 1.0.0
     * @apiName Get Diets Grid
     * @apiGroup Admin Dietary Restriction
     * @apiDescription This function is used to listing diets
     *
     * @apiHeader {String} Content-Type  application/json
     * @apiHeader {String} Authorization Bearer ACCESS_TOKEN
     *
     * @apiSuccess {Int}     id            The unique identifier of the diet
     * @apiSuccess {String}  title         The title of the diet
     * @apiSuccess {String}  color         The color time of the diet
     * @apiSuccess {Object}  space         The space of the diet
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
     *                  "id": 1,
     *                  "title": "Diabetic",
     *                  "color": "#ffff00",
     *                  "space": "alms"
     *              }
     *          ]
     *     }
     *
     * @Route("/grid", name="api_admin_diet_grid", methods={"GET"})
     *
     * @param Request $request
     * @param DietService $dietService
     * @return JsonResponse|PdfResponse
     * @throws \ReflectionException
     */
    public function gridAction(Request $request, DietService $dietService)
    {
        return $this->respondGrid(
            $request,
            Diet::class,
            'api_admin_diet_grid',
            $dietService
        );
    }

    /**
     * @api {options} /api/v1.0/admin/diet/grid Get Diet Grid Options
     * @apiVersion 1.0.0
     * @apiName Get Diet Grid Options
     * @apiGroup Admin Dietary Restriction
     * @apiDescription This function is used to describe options of listing
     *
     * @apiHeader {String} Content-Type  application/json
     * @apiHeader {String} Authorization Bearer ACCESS_TOKEN
     *
     * @apiSuccess {Array} options The options of the diet listing
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
     * @Route("/grid", name="api_admin_diet_grid_options", methods={"OPTIONS"})
     *
     * @param Request $request
     * @return JsonResponse
     * @throws \ReflectionException
     */
    public function gridOptionAction(Request $request)
    {
        return $this->getOptionsByGroupName(Diet::class, 'api_admin_diet_grid');
    }

    /**
     * @api {get} /api/v1.0/admin/diet Get Diets
     * @apiVersion 1.0.0
     * @apiName Get Diets
     * @apiGroup Admin Dietary Restriction
     * @apiDescription This function is used to listing diets
     *
     * @apiHeader {String} Content-Type  application/json
     * @apiHeader {String} Authorization Bearer ACCESS_TOKEN
     *
     * @apiSuccess {Int}     id            The unique identifier of the diet
     * @apiSuccess {String}  title         The title of the diet
     * @apiSuccess {String}  color         The color time of the diet
     * @apiSuccess {Object}  space         The space of the diet
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
     *                  "id": 1,
     *                  "title": "Diabetic",
     *                  "color": "#ffff00",
     *                  "space": {
     *                      "id": 1,
     *                      "name": "alms"
     *                  }
     *              }
     *          ]
     *     }
     *
     * @Route("", name="api_admin_diet_list", methods={"GET"})
     *
     * @param Request $request
     * @param DietService $dietService
     * @return JsonResponse|PdfResponse
     * @throws \ReflectionException
     */
    public function listAction(Request $request, DietService $dietService)
    {
        return $this->respondList(
            $request,
            Diet::class,
            'api_admin_diet_list',
            $dietService
        );
    }

    /**
     * @api {get} /api/v1.0/admin/diet/{id} Get Diet
     * @apiVersion 1.0.0
     * @apiName Get Diet
     * @apiGroup Admin Dietary Restriction
     * @apiDescription This function is used to get diet
     *
     * @apiHeader {String} Content-Type  application/json
     * @apiHeader {String} Authorization Bearer ACCESS_TOKEN
     *
     * @apiSuccess {Int}     id            The unique identifier of the diet
     * @apiSuccess {String}  title         The title of the diet
     * @apiSuccess {String}  color         The color time of the diet
     * @apiSuccess {Object}  space         The space of the diet
     *
     * @apiSuccessExample {json} Sample Response:
     *     HTTP/1.1 200 OK
     *     {
     *          "data": {
     *                  "id": 1,
     *                  "title": "Diabetic",
     *                  "color": "#ffff00",
     *                  "space": {
     *                      "id": 1,
     *                      "name": "alms"
     *                  }
     *          }
     *     }
     *
     * @Route("/{id}", requirements={"id"="\d+"}, name="api_admin_diet_get", methods={"GET"})
     *
     * @param DietService $dietService
     * @param $id
     * @return JsonResponse
     */
    public function getAction(Request $request, $id, DietService $dietService)
    {
        return $this->respondSuccess(
            Response::HTTP_OK,
            '',
            $dietService->getById($id),
            ['api_admin_diet_get']
        );
    }

    /**
     * @api {post} /api/v1.0/admin/diet Add Diet
     * @apiVersion 1.0.0
     * @apiName Add Diet
     * @apiGroup Admin Dietary Restriction
     * @apiDescription This function is used to add diet
     *
     * @apiHeader {String} Content-Type  application/x-www-form-urlencoded
     * @apiHeader {String} Authorization Bearer ACCESS_TOKEN
     *
     * @apiParam {String}  title     The title of the diet
     * @apiParam {String}  color     The color of the diet
     * @apiParam {Int}     space_id  The unique identifier of the space
     *
     * @apiParamExample {json} Request-Example:
     *     {
     *         "title": "Dr.",
     *         "color": "#ffff00",
     *         "space_id": 1
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
     * @Route("", name="api_admin_diet_add", methods={"POST"})
     *
     * @Grant(grant="persistence-common-diet", level="ADD")
     *
     * @param Request $request
     * @param DietService $dietService
     * @return JsonResponse
     * @throws \Exception
     */
    public function addAction(Request $request, DietService $dietService)
    {
        $dietService->add(
            [
                'title' => $request->get('title'),
                'color' => $request->get('color'),
                'space_id' => $request->get('space_id')
            ]
        );

        return $this->respondSuccess(
            Response::HTTP_CREATED
        );
    }

    /**
     * @api {put} /api/v1.0/admin/diet/{id} Edit Diet
     * @apiVersion 1.0.0
     * @apiName Edit Diet
     * @apiGroup Admin Dietary Restriction
     * @apiDescription This function is used to edit diet
     *
     * @apiHeader {String} Content-Type  application/x-www-form-urlencoded
     * @apiHeader {String} Authorization Bearer ACCESS_TOKEN
     *
     * @apiParam {String}  title     The title of the diet
     * @apiParam {String}  color     The color of the diet
     * @apiParam {Int}     space_id  The unique identifier of the space
     *
     * @apiParamExample {json} Request-Example:
     *     {
     *         "title": "Dr.",
     *         "color": "#ffff00",
     *         "space_id": 1
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
     * @Route("/{id}", requirements={"id"="\d+"}, name="api_admin_diet_edit", methods={"PUT"})
     *
     * @Grant(grant="persistence-common-diet", level="EDIT")
     *
     * @param Request $request
     * @param $id
     * @param DietService $dietService
     * @return JsonResponse
     * @throws \Exception
     */
    public function editAction(Request $request, $id, DietService $dietService)
    {
        $dietService->edit(
            $id,
            [
                'title' => $request->get('title'),
                'color' => $request->get('color'),
                'space_id' => $request->get('space_id')
            ]
        );

        return $this->respondSuccess(
            Response::HTTP_CREATED
        );
    }

    /**
     * @api {delete} /api/v1.0/admin/diet/{id} Delete Diet
     * @apiVersion 1.0.0
     * @apiName Delete Diet
     * @apiGroup Admin Dietary Restriction
     * @apiDescription This function is used to remove diet
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
     *          "error": "Diet not found"
     *     }
     *
     * @Route("/{id}", requirements={"id"="\d+"}, name="api_admin_diet_delete", methods={"DELETE"})
     *
     * @Grant(grant="persistence-common-diet", level="DELETE")
     *
     * @param $id
     * @param DietService $dietService
     * @return JsonResponse
     * @throws \Doctrine\DBAL\ConnectionException
     * @throws \Throwable
     */
    public function deleteAction(Request $request, $id, DietService $dietService)
    {
        $dietService->remove($id);

        return $this->respondSuccess(
            Response::HTTP_NO_CONTENT
        );
    }

    /**
     * @api {delete} /api/v1.0/admin/diet Bulk Delete Diets
     * @apiVersion 1.0.0
     * @apiName Bulk Delete Diets
     * @apiGroup Admin Dietary Restriction
     * @apiDescription This function is used to bulk remove diets
     *
     * @apiHeader {String} Content-Type  application/x-www-form-urlencoded
     * @apiHeader {String} Authorization Bearer ACCESS_TOKEN
     *
     * @apiParam {Int[]} ids The unique identifier of the diets
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
     *          "error": "Diet not found"
     *     }
     *
     * @Route("", name="api_admin_diet_delete_bulk", methods={"DELETE"})
     *
     * @Grant(grant="persistence-common-diet", level="DELETE")
     *
     * @param Request $request
     * @param DietService $dietService
     * @return JsonResponse
     * @throws \Doctrine\DBAL\ConnectionException
     * @throws \Throwable
     */
    public function deleteBulkAction(Request $request, DietService $dietService)
    {
        $dietService->removeBulk($request->get('ids'));

        return $this->respondSuccess(
            Response::HTTP_NO_CONTENT
        );
    }
}
