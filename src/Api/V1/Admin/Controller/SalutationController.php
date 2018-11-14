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
 * Class SalutationController
 * @package App\Api\V1\Admin\Controller
 */
class SalutationController extends BaseController
{
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
     * @apiSuccess {Int}     id            The unique identifier of the salutation
     * @apiSuccess {String}  title         The title of the salutation
     * @apiSuccess {String}  createdAt     The created time of the salutation
     * @apiSuccess {String}  updatedAt     The updated time of the salutation
     * @apiSuccess {Int}     createdBy     The created user id of the salutation
     * @apiSuccess {Int}     updatedBy     The updated user id of the salutation
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
     *                  "createdAt": "2018-11-01 17:24:48",
     *                  "updatedAt": "2018-11-01 17:25:49",
     *                  "createdBy": 1,
     *                  "updatedBy": 5,
     *                  "id": 1,
     *                  "title": "Dr."
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
        return $this->respondGrid(
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
     * @apiSuccess {Int}     id            The unique identifier of the salutation
     * @apiSuccess {String}  title         The title of the salutation
     * @apiSuccess {String}  createdAt     The created time of the salutation
     * @apiSuccess {String}  updatedAt     The updated time of the salutation
     * @apiSuccess {Int}     createdBy     The created user id of the salutation
     * @apiSuccess {Int}     updatedBy     The updated user id of the salutation
     *
     * @apiSuccessExample {json} Sample Response:
     *     HTTP/1.1 200 OK
     *     {
     *          "data": {
     *                  "createdAt": "2018-11-01 17:24:48",
     *                  "updatedAt": "2018-11-01 17:25:49",
     *                  "createdBy": 1,
     *                  "updatedBy": 5,
     *                  "id": 1,
     *                  "title": "Dr."
     *          }
     *     }
     *
     * @Route("/{id}", requirements={"id"="\d+"}, name="api_admin_salutation_get", methods={"GET"})
     *
     * @param SalutationService $salutationService
     * @param $id
     * @return JsonResponse
     */
    public function getAction($id, SalutationService $salutationService) : JsonResponse
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
     * @param Request $request
     * @param SalutationService $salutationService
     * @return JsonResponse
     * @throws \Doctrine\DBAL\ConnectionException
     */
    public function addAction(Request $request, SalutationService $salutationService) : JsonResponse
    {
        $salutationService->add(
            [
                'title' => $request->get('title')
            ]
        );

        return $this->respondSuccess(
            Response::HTTP_CREATED
        );
    }

    /**
     * @api {post} /api/v1.0/admin/salutation/{id} Edit Salutation
     * @apiVersion 1.0.0
     * @apiName Edit Salutation
     * @apiGroup Admin Salutation
     * @apiDescription This function is used to edit salutation
     *
     * @apiHeader {String} Content-Type  application/x-www-form-urlencoded
     * @apiHeader {String} Authorization Bearer ACCESS_TOKEN
     *
     * @apiParam {String}  title          The title of the salutation
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
     * @Route("/{id}", requirements={"id"="\d+"}, name="api_admin_salutation_edit", methods={"POST"})
     *
     * @param Request $request
     * @param $id
     * @param SalutationService $salutationService
     * @return JsonResponse
     * @throws \Doctrine\DBAL\ConnectionException
     */
    public function editAction(Request $request, $id, SalutationService $salutationService) : JsonResponse
    {
        $salutationService->edit(
            $id,
            [
                'title' => $request->get('title'),
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
     * @param $id
     * @param SalutationService $salutationService
     * @return JsonResponse
     * @throws \Doctrine\DBAL\ConnectionException
     * @throws \Throwable
     */
    public function deleteAction($id, SalutationService $salutationService) : JsonResponse
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
     * @apiHeader {String} Content-Type  application/json
     * @apiHeader {String} Authorization Bearer ACCESS_TOKEN
     *
     * @apiParam {Int[]} ids The unique identifier of the salutations
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
     * @param Request $request
     * @param SalutationService $salutationService
     * @return JsonResponse
     * @throws \Doctrine\DBAL\ConnectionException
     * @throws \Throwable
     */
    public function deleteBulkAction(Request $request, SalutationService $salutationService) : JsonResponse
    {
        $salutationService->removeBulk(
            [
                'ids' => $request->get('ids'),
            ]
        );

        return $this->respondSuccess(
            Response::HTTP_NO_CONTENT
        );
    }
}