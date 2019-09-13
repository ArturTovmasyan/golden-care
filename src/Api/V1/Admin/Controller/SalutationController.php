<?php
namespace App\Api\V1\Admin\Controller;

use App\Api\V1\Admin\Service\SalutationService;
use App\Api\V1\Common\Controller\BaseController;
use App\Entity\Salutation;
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
 * @Route("/api/v1.0/admin/salutation")
 *
 * @Grant(grant="persistence-common-salutation", level="VIEW")
 *
 * Class SalutationController
 * @package App\Api\V1\Admin\Controller
 */
class SalutationController extends BaseController
{
    /**
     * @api {get} /api/v1.0/admin/salutation/grid Get Salutations Grid
     * @apiVersion 1.0.0
     * @apiName Get Salutations Grid
     * @apiGroup Admin Salutation
     * @apiDescription This function is used to listing salutations
     *
     * @apiHeader {String} Content-Type  application/json
     * @apiHeader {String} Authorization Bearer ACCESS_TOKEN
     *
     * @apiSuccess {Int}     id             The unique identifier of the salutation
     * @apiSuccess {String}  title          The title of the salutation
     * @apiSuccess {String}  created_at     The created time of the salutation
     * @apiSuccess {String}  updated_at     The updated time of the salutation
     * @apiSuccess {Int}     created_by     The created user id of the salutation
     * @apiSuccess {Int}     updated_by     The updated user id of the salutation
     * @apiSuccess {Object}  space          The space of the salutation
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
     *                  "space": "alms"
     *              }
     *          ]
     *     }
     *
     * @Route("/grid", name="api_admin_salutation_grid", methods={"GET"})
     *
     * @param Request $request
     * @param SalutationService $salutationService
     * @return JsonResponse|PdfResponse
     * @throws \ReflectionException
     */
    public function gridAction(Request $request, SalutationService $salutationService)
    {
        return $this->respondGrid(
            $request,
            Salutation::class,
            'api_admin_salutation_grid',
            $salutationService
        );
    }

    /**
     * @api {options} /api/v1.0/admin/salutation/grid Get Salutation Grid Options
     * @apiVersion 1.0.0
     * @apiName Get Salutation Grid Options
     * @apiGroup Admin Salutation
     * @apiDescription This function is used to describe options of listing
     *
     * @apiHeader {String} Content-Type  application/json
     * @apiHeader {String} Authorization Bearer ACCESS_TOKEN
     *
     * @apiSuccess {Array} options The options of the salutation listing
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
     * @Route("/grid", name="api_admin_salutation_grid_options", methods={"OPTIONS"})
     *
     * @param Request $request
     * @return JsonResponse
     * @throws \ReflectionException
     */
    public function gridOptionAction(Request $request)
    {
        return $this->getOptionsByGroupName(Salutation::class, 'api_admin_salutation_grid');
    }

    /**
     * @api {get} /api/v1.0/admin/salutation Get Salutations
     * @apiVersion 1.0.0
     * @apiName Get Salutations
     * @apiGroup Admin Salutation
     * @apiDescription This function is used to listing salutations
     *
     * @apiHeader {String} Content-Type  application/json
     * @apiHeader {String} Authorization Bearer ACCESS_TOKEN
     *
     * @apiSuccess {Int}     id             The unique identifier of the salutation
     * @apiSuccess {String}  title          The title of the salutation
     * @apiSuccess {String}  created_at     The created time of the salutation
     * @apiSuccess {String}  updated_at     The updated time of the salutation
     * @apiSuccess {Int}     created_by     The created user id of the salutation
     * @apiSuccess {Int}     updated_by     The updated user id of the salutation
     * @apiSuccess {Object}  space          The space of the salutation
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
     *                  "space": {
     *                      "id": 1,
     *                      "name": "alms"
     *                  }
     *              }
     *          ]
     *     }
     *
     * @Route("", name="api_admin_salutation_list", methods={"GET"})
     *
     * @param Request $request
     * @param SalutationService $salutationService
     * @return JsonResponse|PdfResponse
     * @throws \ReflectionException
     */
    public function listAction(Request $request, SalutationService $salutationService)
    {
        return $this->respondList(
            $request,
            Salutation::class,
            'api_admin_salutation_list',
            $salutationService
        );
    }

    /**
     * @api {get} /api/v1.0/admin/salutation/{id} Get Salutation
     * @apiVersion 1.0.0
     * @apiName Get Salutation
     * @apiGroup Admin Salutation
     * @apiDescription This function is used to get salutation
     *
     * @apiHeader {String} Content-Type  application/json
     * @apiHeader {String} Authorization Bearer ACCESS_TOKEN
     *
     * @apiSuccess {Int}     id             The unique identifier of the salutation
     * @apiSuccess {String}  title          The title of the salutation
     * @apiSuccess {String}  created_at     The created time of the salutation
     * @apiSuccess {String}  updated_at     The updated time of the salutation
     * @apiSuccess {Int}     created_by     The created user id of the salutation
     * @apiSuccess {Int}     updated_by     The updated user id of the salutation
     * @apiSuccess {Object}  space          The space of the salutation
     *
     * @apiSuccessExample {json} Sample Response:
     *     HTTP/1.1 200 OK
     *     {
     *          "data": {
     *                  "created_at": "2018-11-01 17:24:48",
     *                  "updated_at": "2018-11-01 17:25:49",
     *                  "created_by": 1,
     *                  "updated_by": 5,
     *                  "id": 1,
     *                  "title": "Dr.",
     *                  "space": {
     *                      "id": 1,
     *                      "name": "alms"
     *                  }
     *          }
     *     }
     *
     * @Route("/{id}", requirements={"id"="\d+"}, name="api_admin_salutation_get", methods={"GET"})
     *
     * @param SalutationService $salutationService
     * @param $id
     * @return JsonResponse
     */
    public function getAction(Request $request, $id, SalutationService $salutationService)
    {
        return $this->respondSuccess(
            Response::HTTP_OK,
            '',
            $salutationService->getById($id),
            ['api_admin_salutation_get']
        );
    }

    /**
     * @api {post} /api/v1.0/admin/salutation Add Salutation
     * @apiVersion 1.0.0
     * @apiName Add Salutation
     * @apiGroup Admin Salutation
     * @apiDescription This function is used to add salutation
     *
     * @apiHeader {String} Content-Type  application/x-www-form-urlencoded
     * @apiHeader {String} Authorization Bearer ACCESS_TOKEN
     *
     * @apiParam {String}  title     The title of the salutation
     * @apiParam {Int}     space_id  The unique identifier of the space
     *
     * @apiParamExample {json} Request-Example:
     *     {
     *         "title": "Dr."
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
     * @Route("", name="api_admin_salutation_add", methods={"POST"})
     *
     * @Grant(grant="persistence-common-salutation", level="ADD")
     *
     * @param Request $request
     * @param SalutationService $salutationService
     * @return JsonResponse
     * @throws \Throwable
     */
    public function addAction(Request $request, SalutationService $salutationService)
    {
        $id = $salutationService->add(
            [
                'title' => $request->get('title'),
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
     * @api {put} /api/v1.0/admin/salutation/{id} Edit Salutation
     * @apiVersion 1.0.0
     * @apiName Edit Salutation
     * @apiGroup Admin Salutation
     * @apiDescription This function is used to edit salutation
     *
     * @apiHeader {String} Content-Type  application/x-www-form-urlencoded
     * @apiHeader {String} Authorization Bearer ACCESS_TOKEN
     *
     * @apiParam {String}  title     The title of the salutation
     * @apiParam {Int}     space_id  The unique identifier of the space
     *
     * @apiParamExample {json} Request-Example:
     *     {
     *         "title": "Dr."
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
     * @Route("/{id}", requirements={"id"="\d+"}, name="api_admin_salutation_edit", methods={"PUT"})
     *
     * @Grant(grant="persistence-common-salutation", level="EDIT")
     *
     * @param Request $request
     * @param $id
     * @param SalutationService $salutationService
     * @return JsonResponse
     * @throws \Throwable
     */
    public function editAction(Request $request, $id, SalutationService $salutationService)
    {
        $salutationService->edit(
            $id,
            [
                'title' => $request->get('title'),
                'space_id' => $request->get('space_id')
            ]
        );

        return $this->respondSuccess(
            Response::HTTP_CREATED
        );
    }

    /**
     * @api {delete} /api/v1.0/admin/salutation/{id} Delete Salutation
     * @apiVersion 1.0.0
     * @apiName Delete Salutation
     * @apiGroup Admin Salutation
     * @apiDescription This function is used to remove salutation
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
     *          "error": "Salutation not found"
     *     }
     *
     * @Route("/{id}", requirements={"id"="\d+"}, name="api_admin_salutation_delete", methods={"DELETE"})
     *
     * @Grant(grant="persistence-common-salutation", level="DELETE")
     *
     * @param $id
     * @param SalutationService $salutationService
     * @return JsonResponse
     * @throws \Doctrine\DBAL\ConnectionException
     * @throws \Throwable
     */
    public function deleteAction(Request $request, $id, SalutationService $salutationService)
    {
        $salutationService->remove($id);

        return $this->respondSuccess(
            Response::HTTP_NO_CONTENT
        );
    }

    /**
     * @api {delete} /api/v1.0/admin/salutation Bulk Delete Salutations
     * @apiVersion 1.0.0
     * @apiName Bulk Delete Salutations
     * @apiGroup Admin Salutation
     * @apiDescription This function is used to bulk remove salutations
     *
     * @apiHeader {String} Content-Type  application/x-www-form-urlencoded
     * @apiHeader {String} Authorization Bearer ACCESS_TOKEN
     *
     * @apiParam {Int[]} ids The unique identifier of the salutations
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
     *          "error": "Salutation not found"
     *     }
     *
     * @Route("", name="api_admin_salutation_delete_bulk", methods={"DELETE"})
     *
     * @Grant(grant="persistence-common-salutation", level="DELETE")
     *
     * @param Request $request
     * @param SalutationService $salutationService
     * @return JsonResponse
     * @throws \Doctrine\DBAL\ConnectionException
     * @throws \Throwable
     */
    public function deleteBulkAction(Request $request, SalutationService $salutationService)
    {
        $salutationService->removeBulk($request->get('ids'));

        return $this->respondSuccess(
            Response::HTTP_NO_CONTENT
        );
    }

    /**
     * @api {post} /api/v1.0/admin/salutation/related/info Salutation related info
     * @apiVersion 1.0.0
     * @apiName Salutation Related Info
     * @apiGroup Admin Salutation
     * @apiDescription This function is used to get salutation related info
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
     *          "error": "Salutation not found"
     *     }
     *
     * @Route("/related/info", name="api_admin_salutation_related_info", methods={"POST"})
     *
     * @param Request $request
     * @param SalutationService $salutationService
     * @return JsonResponse
     * @throws \Doctrine\DBAL\ConnectionException
     * @throws \Throwable
     */
    public function relatedInfoAction(Request $request, SalutationService $salutationService)
    {
        $relatedData = $salutationService->getRelatedInfo($request->get('ids'));

        return $this->respondSuccess(
            Response::HTTP_OK,
            '',
            [$relatedData]
        );
    }
}
