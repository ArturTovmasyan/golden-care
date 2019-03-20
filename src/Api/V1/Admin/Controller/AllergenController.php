<?php
namespace App\Api\V1\Admin\Controller;

use App\Api\V1\Admin\Service\AllergenService;
use App\Api\V1\Common\Controller\BaseController;
use App\Entity\Allergen;
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
 * @Route("/api/v1.0/admin/allergen")
 *
 * @Grant(grant="persistence-common-allergen", level="VIEW")
 *
 * Class AllergenController
 * @package App\Api\V1\Admin\Controller
 */
class AllergenController extends BaseController
{
    /**
     * @api {get} /api/v1.0/admin/allergen/grid Get Allergens Grid
     * @apiVersion 1.0.0
     * @apiName Get Allergens Grid
     * @apiGroup Admin Allergens
     * @apiDescription This function is used to listing allergens
     *
     * @apiHeader {String} Content-Type  application/json
     * @apiHeader {String} Authorization Bearer ACCESS_TOKEN
     *
     * @apiSuccess {Int}     id            The unique identifier of the allergen
     * @apiSuccess {String}  title         The title of the allergen
     * @apiSuccess {String}  description   The description time of the allergen
     * @apiSuccess {Object}  space         The space of the allergen
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
     *                  "title": "Lidocaine",
     *                  "description": "Medication Allergies",
     *                  "space": "alms"
     *              }
     *          ]
     *     }
     *
     * @Route("/grid", name="api_admin_allergen_grid", methods={"GET"})
     *
     * @param Request $request
     * @param AllergenService $allergenService
     * @return JsonResponse|PdfResponse
     * @throws \ReflectionException
     */
    public function gridAction(Request $request, AllergenService $allergenService)
    {
        return $this->respondGrid(
            $request,
            Allergen::class,
            'api_admin_allergen_grid',
            $allergenService
        );
    }

    /**
     * @api {options} /api/v1.0/admin/allergen/grid Get Allergen Grid Options
     * @apiVersion 1.0.0
     * @apiName Get Allergen Grid Options
     * @apiGroup Admin Allergens
     * @apiDescription This function is used to describe options of listing
     *
     * @apiHeader {String} Content-Type  application/json
     * @apiHeader {String} Authorization Bearer ACCESS_TOKEN
     *
     * @apiSuccess {Array} options The options of the allergen listing
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
     * @Route("/grid", name="api_admin_allergen_grid_options", methods={"OPTIONS"})
     *
     * @param Request $request
     * @return JsonResponse
     * @throws \ReflectionException
     */
    public function gridOptionAction(Request $request)
    {
        return $this->getOptionsByGroupName(Allergen::class, 'api_admin_allergen_grid');
    }

    /**
     * @api {get} /api/v1.0/admin/allergen Get Allergens
     * @apiVersion 1.0.0
     * @apiName Get Allergens
     * @apiGroup Admin Allergens
     * @apiDescription This function is used to listing allergens
     *
     * @apiHeader {String} Content-Type  application/json
     * @apiHeader {String} Authorization Bearer ACCESS_TOKEN
     *
     * @apiSuccess {Int}     id            The unique identifier of the allergen
     * @apiSuccess {String}  title         The title of the allergen
     * @apiSuccess {String}  description   The description time of the allergen
     * @apiSuccess {Object}  space         The space of the allergen
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
     *                  "title": "Lidocaine",
     *                  "description": "Medication Allergies",
     *                  "space": {
     *                      "id": 1,
     *                      "name": "alms"
     *                  }
     *              }
     *          ]
     *     }
     *
     * @Route("", name="api_admin_allergen_list", methods={"GET"})
     *
     * @param Request $request
     * @param AllergenService $allergenService
     * @return JsonResponse|PdfResponse
     * @throws \ReflectionException
     */
    public function listAction(Request $request, AllergenService $allergenService)
    {
        return $this->respondList(
            $request,
            Allergen::class,
            'api_admin_allergen_list',
            $allergenService
        );
    }

    /**
     * @api {get} /api/v1.0/admin/allergen/{id} Get Allergen
     * @apiVersion 1.0.0
     * @apiName Get Allergen
     * @apiGroup Admin Allergens
     * @apiDescription This function is used to get allergen
     *
     * @apiHeader {String} Content-Type  application/json
     * @apiHeader {String} Authorization Bearer ACCESS_TOKEN
     *
     * @apiSuccess {Int}     id            The unique identifier of the allergen
     * @apiSuccess {String}  title         The title of the allergen
     * @apiSuccess {String}  description   The description time of the allergen
     * @apiSuccess {Object}  space         The space of the allergen
     *
     * @apiSuccessExample {json} Sample Response:
     *     HTTP/1.1 200 OK
     *     {
     *          "data": {
     *              "id": 1,
     *              "title": "Lidocaine",
     *              "description": "Medication Allergies",
     *              "space": {
     *                  "id": 1,
     *                  "name": "alms"
     *              }
     *          }
     *     }
     *
     * @Route("/{id}", requirements={"id"="\d+"}, name="api_admin_allergen_get", methods={"GET"})
     *
     * @param AllergenService $allergenService
     * @param $id
     * @return JsonResponse
     */
    public function getAction(Request $request, $id, AllergenService $allergenService)
    {
        return $this->respondSuccess(
            Response::HTTP_OK,
            '',
            $allergenService->getById($id),
            ['api_admin_allergen_get']
        );
    }

    /**
     * @api {post} /api/v1.0/admin/allergen Add Allergen
     * @apiVersion 1.0.0
     * @apiName Add Allergen
     * @apiGroup Admin Allergens
     * @apiDescription This function is used to add allergen
     *
     * @apiHeader {String} Content-Type  application/x-www-form-urlencoded
     * @apiHeader {String} Authorization Bearer ACCESS_TOKEN
     *
     * @apiParam {String}  title           The title of the allergen
     * @apiParam {String}  [description]   The description of the allergen
     * @apiParam {Int}     space_id        The unique identifier of the space
     *
     * @apiParamExample {json} Request-Example:
     *     {
     *         "title": "Lidocaine",
     *         "description": "Medication Allergies",
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
     * @Route("", name="api_admin_allergen_add", methods={"POST"})
     *
     * @Grant(grant="persistence-common-allergen", level="ADD")
     *
     * @param Request $request
     * @param AllergenService $allergenService
     * @return JsonResponse
     * @throws \Exception
     */
    public function addAction(Request $request, AllergenService $allergenService)
    {
        $id = $allergenService->add(
            [
                'title' => $request->get('title'),
                'description' => $request->get('description') ?? '',
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
     * @api {put} /api/v1.0/admin/allergen/{id} Edit Allergen
     * @apiVersion 1.0.0
     * @apiName Edit Allergen
     * @apiGroup Admin Allergens
     * @apiDescription This function is used to edit allergen
     *
     * @apiHeader {String} Content-Type  application/x-www-form-urlencoded
     * @apiHeader {String} Authorization Bearer ACCESS_TOKEN
     *
     * @apiParam {String}  title          The title of the allergen
     * @apiParam {String}  [description]  The description of the allergen
     * @apiParam {Int}     space_id       The unique identifier of the space
     *
     * @apiParamExample {json} Request-Example:
     *     {
     *         "title": "Lidocaine",
     *         "description": "Medication Allergies",
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
     * @Route("/{id}", requirements={"id"="\d+"}, name="api_admin_allergen_edit", methods={"PUT"})
     *
     * @Grant(grant="persistence-common-allergen", level="EDIT")
     *
     * @param Request $request
     * @param $id
     * @param AllergenService $allergenService
     * @return JsonResponse
     * @throws \Exception
     */
    public function editAction(Request $request, $id, AllergenService $allergenService)
    {
        $allergenService->edit(
            $id,
            [
                'title' => $request->get('title'),
                'description' => $request->get('description') ?? '',
                'space_id' => $request->get('space_id')
            ]
        );

        return $this->respondSuccess(
            Response::HTTP_CREATED
        );
    }

    /**
     * @api {delete} /api/v1.0/admin/allergen/{id} Delete Allergen
     * @apiVersion 1.0.0
     * @apiName Delete Allergen
     * @apiGroup Admin Allergens
     * @apiDescription This function is used to remove allergen
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
     * @Route("/{id}", requirements={"id"="\d+"}, name="api_admin_allergen_delete", methods={"DELETE"})
     *
     * @Grant(grant="persistence-common-allergen", level="DELETE")
     *
     * @param $id
     * @param AllergenService $allergenService
     * @return JsonResponse
     * @throws \Doctrine\DBAL\ConnectionException
     * @throws \Throwable
     */
    public function deleteAction(Request $request, $id, AllergenService $allergenService)
    {
        $allergenService->remove($id);

        return $this->respondSuccess(
            Response::HTTP_NO_CONTENT
        );
    }

    /**
     * @api {delete} /api/v1.0/admin/allergen Bulk Delete Allergens
     * @apiVersion 1.0.0
     * @apiName Bulk Delete Allergens
     * @apiGroup Admin Allergens
     * @apiDescription This function is used to bulk remove allergens
     *
     * @apiHeader {String} Content-Type  application/x-www-form-urlencoded
     * @apiHeader {String} Authorization Bearer ACCESS_TOKEN
     *
     * @apiParam {Int[]} ids The unique identifier of the allergens
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
     * @Route("", name="api_admin_allergen_delete_bulk", methods={"DELETE"})
     *
     * @Grant(grant="persistence-common-allergen", level="DELETE")
     *
     * @param Request $request
     * @param AllergenService $allergenService
     * @return JsonResponse
     * @throws \Doctrine\DBAL\ConnectionException
     * @throws \Throwable
     */
    public function deleteBulkAction(Request $request, AllergenService $allergenService)
    {
        $allergenService->removeBulk($request->get('ids'));

        return $this->respondSuccess(
            Response::HTTP_NO_CONTENT
        );
    }

    /**
     * @api {post} /api/v1.0/admin/allergen/related/info Allergen related info
     * @apiVersion 1.0.0
     * @apiName Allergen Related Info
     * @apiGroup Admin Allergen
     * @apiDescription This function is used to get allergen related info
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
     *          "error": "Allergen not found"
     *     }
     *
     * @Route("/related/info", name="api_admin_allergen_related_info", methods={"POST"})
     *
     * @param Request $request
     * @param AllergenService $allergenService
     * @return JsonResponse
     * @throws \Doctrine\DBAL\ConnectionException
     * @throws \Throwable
     */
    public function relatedInfoAction(Request $request, AllergenService $allergenService)
    {
        $relatedData = $allergenService->getRelatedInfo($request->get('ids'));

        return $this->respondSuccess(
            Response::HTTP_OK,
            '',
            [$relatedData]
        );
    }
}
