<?php
namespace App\Api\V1\Admin\Controller;

use App\Api\V1\Admin\Service\ResidentAllergenService;
use App\Api\V1\Common\Controller\BaseController;
use App\Entity\ResidentAllergen;
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
 * @Route("/api/v1.0/admin/resident/history/allergy/other")
 *
 * Class ResidentAllergenController
 * @package App\Api\V1\Admin\Controller
 */
class ResidentAllergenController extends BaseController
{
    /**
     * @api {get} /api/v1.0/admin/resident/history/allergy/other/grid Get ResidentAllergens Grid
     * @apiVersion 1.0.0
     * @apiName Get ResidentAllergens Grid
     * @apiGroup Admin Resident Allergens
     * @apiDescription This function is used to listing residentAllergens
     *
     * @apiHeader {String} Content-Type  application/json
     * @apiHeader {String} Authorization Bearer ACCESS_TOKEN
     *
     * @apiSuccess {Int}      id                   The unique identifier of the residentAllergen
     * @apiSuccess {Object}   resident             The resident of the residentAllergen
     * @apiSuccess {Object}   allergen             The allergen of the residentAllergen
     * @apiSuccess {String}   notes                The notes of the residentAllergen
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
     *                   "id": 1,
     *                   "allergen": "Lidocaine",
     *                   "notes": "some notes"
     *              }
     *          ]
     *     }
     *
     * @Route("/grid", name="api_admin_resident_allergen_grid", methods={"GET"})
     *
     * @param Request $request
     * @param ResidentAllergenService $residentAllergenService
     * @return JsonResponse|PdfResponse
     * @throws \ReflectionException
     */
    public function gridAction(Request $request, ResidentAllergenService $residentAllergenService)
    {
        return $this->respondGrid(
            $request,
            ResidentAllergen::class,
            'api_admin_resident_allergen_grid',
            $residentAllergenService,
            ['resident_id' => $request->get('resident_id')]
        );
    }

    /**
     * @api {options} /api/v1.0/admin/resident/history/allergy/other/grid Get ResidentAllergen Grid Options
     * @apiVersion 1.0.0
     * @apiName Get ResidentAllergen Grid Options
     * @apiGroup Admin Resident Allergens
     * @apiDescription This function is used to describe options of listing
     *
     * @apiHeader {String} Content-Type  application/json
     * @apiHeader {String} Authorization Bearer ACCESS_TOKEN
     *
     * @apiSuccess {Array} options The options of the residentAllergen listing
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
     * @Route("/grid", name="api_admin_resident_allergen_grid_options", methods={"OPTIONS"})
     *
     * @param Request $request
     * @return JsonResponse
     * @throws \ReflectionException
     */
    public function gridOptionAction(Request $request)
    {
        return $this->getOptionsByGroupName(ResidentAllergen::class, 'api_admin_resident_allergen_grid');
    }

    /**
     * @api {get} /api/v1.0/admin/resident/history/allergy/other Get ResidentAllergens
     * @apiVersion 1.0.0
     * @apiName Get ResidentAllergens
     * @apiGroup Admin Resident Allergens
     * @apiDescription This function is used to listing residentAllergens
     *
     * @apiHeader {String} Content-Type  application/json
     * @apiHeader {String} Authorization Bearer ACCESS_TOKEN
     *
     * @apiSuccess {Int}      id                   The unique identifier of the residentAllergen
     * @apiSuccess {Object}   resident             The resident of the residentAllergen
     * @apiSuccess {Object}   allergen             The allergen of the residentAllergen
     * @apiSuccess {String}   notes                The notes of the residentAllergen
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
     *                  "allergen": {
     *                      "id": 1,
     *                      "title": "Lidocaine"
     *                  },
     *                  "notes": "some notes"
     *              }
     *          ]
     *     }
     *
     * @Route("", name="api_admin_resident_allergen_list", methods={"GET"})
     *
     * @param Request $request
     * @param ResidentAllergenService $residentAllergenService
     * @return JsonResponse|PdfResponse
     * @throws \ReflectionException
     */
    public function listAction(Request $request, ResidentAllergenService $residentAllergenService)
    {
        return $this->respondList(
            $request,
            ResidentAllergen::class,
            'api_admin_resident_allergen_list',
            $residentAllergenService,
            ['resident_id' => $request->get('resident_id')]
        );
    }

    /**
     * @api {get} /api/v1.0/admin/resident/history/allergy/other/{id} Get ResidentAllergen
     * @apiVersion 1.0.0
     * @apiName Get ResidentAllergen
     * @apiGroup Admin Resident Allergens
     * @apiDescription This function is used to get residentAllergen
     *
     * @apiHeader {String} Content-Type  application/json
     * @apiHeader {String} Authorization Bearer ACCESS_TOKEN
     *
     * @apiSuccess {Int}      id                   The unique identifier of the residentAllergen
     * @apiSuccess {Object}   resident             The resident of the residentAllergen
     * @apiSuccess {Object}   allergen             The allergen of the residentAllergen
     * @apiSuccess {String}   notes                The notes of the residentAllergen
     *
     * @apiSuccessExample {json} Sample Response:
     *     HTTP/1.1 200 OK
     *     {
     *          "data": {
     *                  "id": 1,
     *                  "resident": {
     *                      "id": 1
     *                  },
     *                  "allergen": {
     *                      "id": 1,
     *                      "title": "Lidocaine"
     *                  },
     *                  "notes": "some notes"
     *          }
     *     }
     *
     * @Route("/{id}", requirements={"id"="\d+"}, name="api_admin_resident_allergen_get", methods={"GET"})
     *
     * @param ResidentAllergenService $residentAllergenService
     * @param $id
     * @return JsonResponse
     */
    public function getAction(Request $request, $id, ResidentAllergenService $residentAllergenService)
    {
        return $this->respondSuccess(
            Response::HTTP_OK,
            '',
            $residentAllergenService->getById($id),
            ['api_admin_resident_allergen_get']
        );
    }

    /**
     * @api {post} /api/v1.0/admin/resident/history/allergy/other Add ResidentAllergen
     * @apiVersion 1.0.0
     * @apiName Add ResidentAllergen
     * @apiGroup Admin Resident Allergens
     * @apiDescription This function is used to add residentAllergen
     *
     * @apiHeader {String} Content-Type  application/x-www-form-urlencoded
     * @apiHeader {String} Authorization Bearer ACCESS_TOKEN
     *
     * @apiParam {Int}     resident_id           The unique identifier of the resident
     * @apiParam {Int}     allergen_id           The unique identifier of the allergen in select mode
     * @apiParam {Object}  allergen              The new allergen in add new mode
     * @apiParam {String}  [notes]               The notes of the residentAllergen
     *
     * @apiParamExample {json} Request-Example:
     *     {
     *          "resident_id": 1,
     *          "allergen_id": 1,
     *          "allergen": {
     *                          "title": "Lidocaine",
     *                          "description": "some description"
     *                        },
     *          "notes": "some notes"
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
     *              "allergen_id": "Sorry, this value not be blank."
     *          }
     *     }
     *
     * @Route("", name="api_admin_resident_allergen_add", methods={"POST"})
     *
     * @param Request $request
     * @param ResidentAllergenService $residentAllergenService
     * @return JsonResponse
     * @throws \Exception
     */
    public function addAction(Request $request, ResidentAllergenService $residentAllergenService)
    {
        $residentAllergenService->add(
            [
                'resident_id' => $request->get('resident_id'),
                'allergen_id' => $request->get('allergen_id'),
                'allergen' => $request->get('allergen'),
                'notes' => $request->get('notes') ?? ''
            ]
        );

        return $this->respondSuccess(
            Response::HTTP_CREATED
        );
    }

    /**
     * @api {put} /api/v1.0/admin/resident/history/allergy/other/{id} Edit ResidentAllergen
     * @apiVersion 1.0.0
     * @apiName Edit ResidentAllergen
     * @apiGroup Admin Resident Allergens
     * @apiDescription This function is used to edit residentAllergen
     *
     * @apiHeader {String} Content-Type  application/x-www-form-urlencoded
     * @apiHeader {String} Authorization Bearer ACCESS_TOKEN
     *
     * @apiParam {Int}     resident_id           The unique identifier of the resident
     * @apiParam {Int}     allergen_id           The unique identifier of the allergen in select mode
     * @apiParam {Object}  allergen              The new allergen in add new mode
     * @apiParam {String}  [notes]               The notes of the residentAllergen
     *
     * @apiParamExample {json} Request-Example:
     *     {
     *          "resident_id": 1,
     *          "allergen_id": 1,
     *          "allergen": {
     *                          "title": "Lidocaine",
     *                          "description": "some description"
     *                        },
     *          "notes": "some notes"
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
     *              "allergen_id": "Sorry, this value not be blank."
     *          }
     *     }
     *
     * @Route("/{id}", requirements={"id"="\d+"}, name="api_admin_resident_allergen_edit", methods={"PUT"})
     *
     * @param Request $request
     * @param $id
     * @param ResidentAllergenService $residentAllergenService
     * @return JsonResponse
     * @throws \Exception
     */
    public function editAction(Request $request, $id, ResidentAllergenService $residentAllergenService)
    {
        $residentAllergenService->edit(
            $id,
            [
                'resident_id' => $request->get('resident_id'),
                'allergen_id' => $request->get('allergen_id'),
                'allergen' => $request->get('allergen'),
                'notes' => $request->get('notes') ?? ''
            ]
        );

        return $this->respondSuccess(
            Response::HTTP_CREATED
        );
    }

    /**
     * @api {delete} /api/v1.0/admin/resident/history/allergy/other/{id} Delete ResidentAllergen
     * @apiVersion 1.0.0
     * @apiName Delete ResidentAllergen
     * @apiGroup Admin Resident Allergens
     * @apiDescription This function is used to remove residentAllergen
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
     *          "error": "ResidentAllergen not found"
     *     }
     *
     * @Route("/{id}", requirements={"id"="\d+"}, name="api_admin_resident_allergen_delete", methods={"DELETE"})
     *
     * @param $id
     * @param ResidentAllergenService $residentAllergenService
     * @return JsonResponse
     * @throws \Doctrine\DBAL\ConnectionException
     * @throws \Throwable
     */
    public function deleteAction(Request $request, $id, ResidentAllergenService $residentAllergenService)
    {
        $residentAllergenService->remove($id);

        return $this->respondSuccess(
            Response::HTTP_NO_CONTENT
        );
    }

    /**
     * @api {delete} /api/v1.0/admin/resident/history/allergy/other Bulk Delete ResidentAllergens
     * @apiVersion 1.0.0
     * @apiName Bulk Delete ResidentAllergens
     * @apiGroup Admin Resident Allergens
     * @apiDescription This function is used to bulk remove residentAllergens
     *
     * @apiHeader {String} Content-Type  application/x-www-form-urlencoded
     * @apiHeader {String} Authorization Bearer ACCESS_TOKEN
     *
     * @apiParam {Int[]} ids The unique identifier of the residentAllergens
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
     *          "error": "ResidentAllergen not found"
     *     }
     *
     * @Route("", name="api_admin_resident_allergen_delete_bulk", methods={"DELETE"})
     *
     * @param Request $request
     * @param ResidentAllergenService $residentAllergenService
     * @return JsonResponse
     * @throws \Doctrine\DBAL\ConnectionException
     * @throws \Throwable
     */
    public function deleteBulkAction(Request $request, ResidentAllergenService $residentAllergenService)
    {
        $residentAllergenService->removeBulk($request->get('ids'));

        return $this->respondSuccess(
            Response::HTTP_NO_CONTENT
        );
    }
}
