<?php
namespace App\Api\V1\Admin\Controller;

use App\Api\V1\Admin\Service\ResidentDietService;
use App\Api\V1\Common\Controller\BaseController;
use App\Entity\ResidentDiet;
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
 * @Route("/api/v1.0/admin/resident/diet")
 *
 * Class ResidentDietController
 * @package App\Api\V1\Admin\Controller
 */
class ResidentDietController extends BaseController
{
    /**
     * @api {get} /api/v1.0/admin/resident/diet/grid Get ResidentDiets Grid
     * @apiVersion 1.0.0
     * @apiName Get ResidentDiets Grid
     * @apiGroup Admin Resident Dietary Restriction
     * @apiDescription This function is used to listing residentDiets
     *
     * @apiHeader {String} Content-Type  application/json
     * @apiHeader {String} Authorization Bearer ACCESS_TOKEN
     *
     * @apiSuccess {Int}      id              The unique identifier of the residentDiet
     * @apiSuccess {Object}   resident        The resident of the residentDiet
     * @apiSuccess {Object}   diet            The diet of the residentDiet
     * @apiSuccess {String}   description     The description of the residentDiet
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
     *                  "resident": 1,
     *                  "diet_title": "Diabetic",
     *                  "diet_color": "#ffff00",
     *                  "description": "some description",
     *              }
     *          ]
     *     }
     *
     * @Route("/grid", name="api_admin_resident_diet_grid", methods={"GET"})
     *
     * @param Request $request
     * @param ResidentDietService $residentDietService
     * @return JsonResponse|PdfResponse
     * @throws \ReflectionException
     */
    public function gridAction(Request $request, ResidentDietService $residentDietService)
    {
        return $this->respondGrid(
            $request,
            ResidentDiet::class,
            'api_admin_resident_diet_grid',
            $residentDietService
        );
    }

    /**
     * @api {options} /api/v1.0/admin/resident/diet/grid Get ResidentDiet Grid Options
     * @apiVersion 1.0.0
     * @apiName Get ResidentDiet Grid Options
     * @apiGroup Admin Resident Dietary Restriction
     * @apiDescription This function is used to describe options of listing
     *
     * @apiHeader {String} Content-Type  application/json
     * @apiHeader {String} Authorization Bearer ACCESS_TOKEN
     *
     * @apiSuccess {Array} options The options of the residentDiet listing
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
     * @Route("/grid", name="api_admin_resident_diet_grid_options", methods={"OPTIONS"})
     *
     * @param Request $request
     * @return JsonResponse
     * @throws \ReflectionException
     */
    public function gridOptionAction(Request $request)
    {
        return $this->getOptionsByGroupName(ResidentDiet::class, 'api_admin_resident_diet_grid');
    }

    /**
     * @api {get} /api/v1.0/admin/resident/diet Get ResidentDiets
     * @apiVersion 1.0.0
     * @apiName Get ResidentDiets
     * @apiGroup Admin Resident Dietary Restriction
     * @apiDescription This function is used to listing residentDiets
     *
     * @apiHeader {String} Content-Type  application/json
     * @apiHeader {String} Authorization Bearer ACCESS_TOKEN
     *
     * @apiSuccess {Int}      id              The unique identifier of the residentDiet
     * @apiSuccess {Object}   resident        The resident of the residentDiet
     * @apiSuccess {Object}   diet            The diet of the residentDiet
     * @apiSuccess {String}   description     The description of the residentDiet
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
     *                  "resident": {
     *                      "id": 1
     *                  },
     *                  "diet": {
     *                      "id": 1,
     *                      "title": "Diabetic",
     *                      "color": "#ffff00"
     *                  },
     *                  "description": "some description"
     *              }
     *          ]
     *     }
     *
     * @Route("", name="api_admin_resident_diet_list", methods={"GET"})
     *
     * @param Request $request
     * @param ResidentDietService $residentDietService
     * @return JsonResponse|PdfResponse
     * @throws \ReflectionException
     */
    public function listAction(Request $request, ResidentDietService $residentDietService)
    {
        return $this->respondList(
            $request,
            ResidentDiet::class,
            'api_admin_resident_diet_list',
            $residentDietService,
            ['resident_id' => $request->get('resident_id')]
        );
    }

    /**
     * @api {get} /api/v1.0/admin/resident/diet/{id} Get ResidentDiet
     * @apiVersion 1.0.0
     * @apiName Get ResidentDiet
     * @apiGroup Admin Resident Dietary Restriction
     * @apiDescription This function is used to get residentDiet
     *
     * @apiHeader {String} Content-Type  application/json
     * @apiHeader {String} Authorization Bearer ACCESS_TOKEN
     *
     * @apiSuccess {Int}      id              The unique identifier of the residentDiet
     * @apiSuccess {Object}   resident        The resident of the residentDiet
     * @apiSuccess {Object}   diet            The diet of the residentDiet
     * @apiSuccess {String}   description     The description of the residentDiet
     *
     * @apiSuccessExample {json} Sample Response:
     *     HTTP/1.1 200 OK
     *     {
     *          "data": {
     *                  "id": 1,
     *                  "resident": {
     *                      "id": 1
     *                  },
     *                  "diet": {
     *                      "id": 1,
     *                      "title": "Diabetic",
     *                      "color": "#ffff00"
     *                  },
     *                  "description": "some description"
     *          }
     *     }
     *
     * @Route("/{id}", requirements={"id"="\d+"}, name="api_admin_resident_diet_get", methods={"GET"})
     *
     * @param ResidentDietService $residentDietService
     * @param $id
     * @return JsonResponse
     */
    public function getAction(Request $request, $id, ResidentDietService $residentDietService)
    {
        return $this->respondSuccess(
            Response::HTTP_OK,
            '',
            $residentDietService->getById($id),
            ['api_admin_resident_diet_get']
        );
    }

    /**
     * @api {post} /api/v1.0/admin/resident/diet Add ResidentDiet
     * @apiVersion 1.0.0
     * @apiName Add ResidentDiet
     * @apiGroup Admin Resident Dietary Restriction
     * @apiDescription This function is used to add residentDiet
     *
     * @apiHeader {String} Content-Type  application/x-www-form-urlencoded
     * @apiHeader {String} Authorization Bearer ACCESS_TOKEN
     *
     * @apiParam {Int}     resident_id     The unique identifier of the resident
     * @apiParam {Int}     diet_id         The unique identifier of the diet
     * @apiParam {String}  description     The description of the residentDiet
     *
     * @apiParamExample {json} Request-Example:
     *     {
     *          "resident_id": 1,
     *          "diet_id": 1,
     *          "description": "some description",
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
     *              "description": "Sorry, this value not be blank."
     *          }
     *     }
     *
     * @Route("", name="api_admin_resident_diet_add", methods={"POST"})
     *
     * @param Request $request
     * @param ResidentDietService $residentDietService
     * @return JsonResponse
     * @throws \Exception
     */
    public function addAction(Request $request, ResidentDietService $residentDietService)
    {
        $residentDietService->add(
            [
                'resident_id' => $request->get('resident_id'),
                'diet_id' => $request->get('diet_id'),
                'description' => $request->get('description')
            ]
        );

        return $this->respondSuccess(
            Response::HTTP_CREATED
        );
    }

    /**
     * @api {put} /api/v1.0/admin/resident/diet/{id} Edit ResidentDiet
     * @apiVersion 1.0.0
     * @apiName Edit ResidentDiet
     * @apiGroup Admin Resident Dietary Restriction
     * @apiDescription This function is used to edit residentDiet
     *
     * @apiHeader {String} Content-Type  application/x-www-form-urlencoded
     * @apiHeader {String} Authorization Bearer ACCESS_TOKEN
     *
     * @apiParam {Int}     resident_id     The unique identifier of the resident
     * @apiParam {Int}     diet_id         The unique identifier of the diet
     * @apiParam {String}  description     The description of the residentDiet
     *
     * @apiParamExample {json} Request-Example:
     *     {
     *          "resident_id": 1,
     *          "diet_id": 1,
     *          "description": "some description",
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
     *              "description": "Sorry, this value not be blank."
     *          }
     *     }
     *
     * @Route("/{id}", requirements={"id"="\d+"}, name="api_admin_resident_diet_edit", methods={"PUT"})
     *
     * @param Request $request
     * @param $id
     * @param ResidentDietService $residentDietService
     * @return JsonResponse
     * @throws \Exception
     */
    public function editAction(Request $request, $id, ResidentDietService $residentDietService)
    {
        $residentDietService->edit(
            $id,
            [
                'resident_id' => $request->get('resident_id'),
                'diet_id' => $request->get('diet_id'),
                'description' => $request->get('description')
            ]
        );

        return $this->respondSuccess(
            Response::HTTP_CREATED
        );
    }

    /**
     * @api {delete} /api/v1.0/admin/resident/diet/{id} Delete ResidentDiet
     * @apiVersion 1.0.0
     * @apiName Delete ResidentDiet
     * @apiGroup Admin Resident Dietary Restriction
     * @apiDescription This function is used to remove residentDiet
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
     *          "code": 639,
     *          "error": "ResidentDiet not found"
     *     }
     *
     * @Route("/{id}", requirements={"id"="\d+"}, name="api_admin_resident_diet_delete", methods={"DELETE"})
     *
     * @param $id
     * @param ResidentDietService $residentDietService
     * @return JsonResponse
     * @throws \Doctrine\DBAL\ConnectionException
     * @throws \Throwable
     */
    public function deleteAction(Request $request, $id, ResidentDietService $residentDietService)
    {
        $residentDietService->remove($id);

        return $this->respondSuccess(
            Response::HTTP_NO_CONTENT
        );
    }

    /**
     * @api {delete} /api/v1.0/admin/resident/diet Bulk Delete ResidentDiets
     * @apiVersion 1.0.0
     * @apiName Bulk Delete ResidentDiets
     * @apiGroup Admin Resident Dietary Restriction
     * @apiDescription This function is used to bulk remove residentDiets
     *
     * @apiHeader {String} Content-Type  application/x-www-form-urlencoded
     * @apiHeader {String} Authorization Bearer ACCESS_TOKEN
     *
     * @apiParam {Int[]} ids The unique identifier of the residentDiets
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
     *          "code": 639,
     *          "error": "ResidentDiet not found"
     *     }
     *
     * @Route("", name="api_admin_resident_diet_delete_bulk", methods={"DELETE"})
     *
     * @param Request $request
     * @param ResidentDietService $residentDietService
     * @return JsonResponse
     * @throws \Doctrine\DBAL\ConnectionException
     * @throws \Throwable
     */
    public function deleteBulkAction(Request $request, ResidentDietService $residentDietService)
    {
        $residentDietService->removeBulk($request->get('ids'));

        return $this->respondSuccess(
            Response::HTTP_NO_CONTENT
        );
    }
}
