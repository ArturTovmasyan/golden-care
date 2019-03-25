<?php
namespace App\Api\V1\Admin\Controller;

use App\Api\V1\Admin\Service\PaymentSourceService;
use App\Api\V1\Common\Controller\BaseController;
use App\Entity\PaymentSource;
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
 * @Route("/api/v1.0/admin/payment/source")
 *
 * @Grant(grant="persistence-common-payment_source", level="VIEW")
 *
 * Class PaymentSourceController
 * @package App\Api\V1\Admin\Controller
 */
class PaymentSourceController extends BaseController
{
    /**
     * @api {get} /api/v1.0/admin/payment/source/grid Get PaymentSources Grid
     * @apiVersion 1.0.0
     * @apiName Get PaymentSources Grid
     * @apiGroup Admin Payment Sources
     * @apiDescription This function is used to listing paymentSources
     *
     * @apiHeader {String} Content-Type  application/json
     * @apiHeader {String} Authorization Bearer ACCESS_TOKEN
     *
     * @apiSuccess {Int}     id              The unique identifier of the paymentSource
     * @apiSuccess {String}  title           The title of the paymentSource
     * @apiSuccess {Object}  space           The space of the paymentSource
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
     *                  "title": "Private Pay",
     *                  "space": "alms"
     *              }
     *          ]
     *     }
     *
     * @Route("/grid", name="api_admin_payment_source_grid", methods={"GET"})
     *
     * @param Request $request
     * @param PaymentSourceService $paymentSourceService
     * @return JsonResponse|PdfResponse
     * @throws \ReflectionException
     */
    public function gridAction(Request $request, PaymentSourceService $paymentSourceService)
    {
        return $this->respondGrid(
            $request,
            PaymentSource::class,
            'api_admin_payment_source_grid',
            $paymentSourceService
        );
    }

    /**
     * @api {options} /api/v1.0/admin/payment/source/grid Get PaymentSource Grid Options
     * @apiVersion 1.0.0
     * @apiName Get PaymentSource Grid Options
     * @apiGroup Admin Payment Sources
     * @apiDescription This function is used to describe options of listing
     *
     * @apiHeader {String} Content-Type  application/json
     * @apiHeader {String} Authorization Bearer ACCESS_TOKEN
     *
     * @apiSuccess {Array} options The options of the paymentSource listing
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
     * @Route("/grid", name="api_admin_payment_source_grid_options", methods={"OPTIONS"})
     *
     * @param Request $request
     * @return JsonResponse
     * @throws \ReflectionException
     */
    public function gridOptionAction(Request $request)
    {
        return $this->getOptionsByGroupName(PaymentSource::class, 'api_admin_payment_source_grid');
    }

    /**
     * @api {get} /api/v1.0/admin/payment/source Get PaymentSources
     * @apiVersion 1.0.0
     * @apiName Get PaymentSources
     * @apiGroup Admin Payment Sources
     * @apiDescription This function is used to listing paymentSources
     *
     * @apiHeader {String} Content-Type  application/json
     * @apiHeader {String} Authorization Bearer ACCESS_TOKEN
     *
     * @apiSuccess {Int}     id              The unique identifier of the paymentSource
     * @apiSuccess {String}  title           The title of the paymentSource
     * @apiSuccess {Object}  space           The space of the paymentSource
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
     *                  "title": "Private Pay",
     *                  "space": {
     *                      "id": 1,
     *                      "name": "alms"
     *                  }
     *              }
     *          ]
     *     }
     *
     * @Route("", name="api_admin_payment_source_list", methods={"GET"})
     *
     * @param Request $request
     * @param PaymentSourceService $paymentSourceService
     * @return JsonResponse|PdfResponse
     * @throws \ReflectionException
     */
    public function listAction(Request $request, PaymentSourceService $paymentSourceService)
    {
        return $this->respondList(
            $request,
            PaymentSource::class,
            'api_admin_payment_source_list',
            $paymentSourceService
        );
    }

    /**
     * @api {get} /api/v1.0/admin/payment/source/{id} Get PaymentSource
     * @apiVersion 1.0.0
     * @apiName Get PaymentSource
     * @apiGroup Admin Payment Sources
     * @apiDescription This function is used to get paymentSource
     *
     * @apiHeader {String} Content-Type  application/json
     * @apiHeader {String} Authorization Bearer ACCESS_TOKEN
     *
     * @apiSuccess {Int}     id              The unique identifier of the paymentSource
     * @apiSuccess {String}  title           The title of the paymentSource
     * @apiSuccess {Object}  space           The space of the paymentSource
     *
     * @apiSuccessExample {json} Sample Response:
     *     HTTP/1.1 200 OK
     *     {
     *          "data": {
     *                  "id": 1,
     *                  "title": "Private Pay",
     *                  "space": {
     *                      "id": 1,
     *                      "name": "alms"
     *                  }
     *          }
     *     }
     *
     * @Route("/{id}", requirements={"id"="\d+"}, name="api_admin_payment_source_get", methods={"GET"})
     *
     * @param PaymentSourceService $paymentSourceService
     * @param $id
     * @return JsonResponse
     */
    public function getAction(Request $request, $id, PaymentSourceService $paymentSourceService)
    {
        return $this->respondSuccess(
            Response::HTTP_OK,
            '',
            $paymentSourceService->getById($id),
            ['api_admin_payment_source_get']
        );
    }

    /**
     * @api {post} /api/v1.0/admin/payment/source Add PaymentSource
     * @apiVersion 1.0.0
     * @apiName Add PaymentSource
     * @apiGroup Admin Payment Sources
     * @apiDescription This function is used to add paymentSource
     *
     * @apiHeader {String} Content-Type  application/x-www-form-urlencoded
     * @apiHeader {String} Authorization Bearer ACCESS_TOKEN
     *
     * @apiParam {String}  title           The title of the paymentSource
     * @apiParam {Int}     space_id        The unique identifier of the space
     *
     * @apiParamExample {json} Request-Example:
     *     {
     *          "title": "Private Pay",
     *          "space_id": 1
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
     * @Route("", name="api_admin_payment_source_add", methods={"POST"})
     *
     * @Grant(grant="persistence-common-payment_source", level="ADD")
     *
     * @param Request $request
     * @param PaymentSourceService $paymentSourceService
     * @return JsonResponse
     * @throws \Exception
     */
    public function addAction(Request $request, PaymentSourceService $paymentSourceService)
    {
        $id = $paymentSourceService->add(
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
     * @api {put} /api/v1.0/admin/payment/source/{id} Edit PaymentSource
     * @apiVersion 1.0.0
     * @apiName Edit PaymentSource
     * @apiGroup Admin Payment Sources
     * @apiDescription This function is used to edit paymentSource
     *
     * @apiHeader {String} Content-Type  application/x-www-form-urlencoded
     * @apiHeader {String} Authorization Bearer ACCESS_TOKEN
     *
     * @apiParam {String}  title           The title of the paymentSource
     * @apiParam {Int}     space_id        The unique identifier of the space
     *
     * @apiParamExample {json} Request-Example:
     *     {
     *          "title": "Private Pay",
     *          "space_id": 1
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
     * @Route("/{id}", requirements={"id"="\d+"}, name="api_admin_payment_source_edit", methods={"PUT"})
     *
     * @Grant(grant="persistence-common-payment_source", level="EDIT")
     *
     * @param Request $request
     * @param $id
     * @param PaymentSourceService $paymentSourceService
     * @return JsonResponse
     * @throws \Exception
     */
    public function editAction(Request $request, $id, PaymentSourceService $paymentSourceService)
    {
        $paymentSourceService->edit(
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
     * @api {delete} /api/v1.0/admin/payment/source/{id} Delete PaymentSource
     * @apiVersion 1.0.0
     * @apiName Delete PaymentSource
     * @apiGroup Admin Payment Sources
     * @apiDescription This function is used to remove paymentSource
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
     *          "error": "PaymentSource not found"
     *     }
     *
     * @Route("/{id}", requirements={"id"="\d+"}, name="api_admin_payment_source_delete", methods={"DELETE"})
     *
     * @Grant(grant="persistence-common-payment_source", level="DELETE")
     *
     * @param $id
     * @param PaymentSourceService $paymentSourceService
     * @return JsonResponse
     * @throws \Doctrine\DBAL\ConnectionException
     * @throws \Throwable
     */
    public function deleteAction(Request $request, $id, PaymentSourceService $paymentSourceService)
    {
        $paymentSourceService->remove($id);

        return $this->respondSuccess(
            Response::HTTP_NO_CONTENT
        );
    }

    /**
     * @api {delete} /api/v1.0/admin/payment/source Bulk Delete PaymentSources
     * @apiVersion 1.0.0
     * @apiName Bulk Delete PaymentSources
     * @apiGroup Admin Payment Sources
     * @apiDescription This function is used to bulk remove paymentSources
     *
     * @apiHeader {String} Content-Type  application/x-www-form-urlencoded
     * @apiHeader {String} Authorization Bearer ACCESS_TOKEN
     *
     * @apiParam {Int[]} ids The unique identifier of the paymentSources
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
     *          "error": "PaymentSource not found"
     *     }
     *
     * @Route("", name="api_admin_payment_source_delete_bulk", methods={"DELETE"})
     *
     * @Grant(grant="persistence-common-payment_source", level="DELETE")
     *
     * @param Request $request
     * @param PaymentSourceService $paymentSourceService
     * @return JsonResponse
     * @throws \Doctrine\DBAL\ConnectionException
     * @throws \Throwable
     */
    public function deleteBulkAction(Request $request, PaymentSourceService $paymentSourceService)
    {
        $paymentSourceService->removeBulk($request->get('ids'));

        return $this->respondSuccess(
            Response::HTTP_NO_CONTENT
        );
    }

    /**
     * @api {post} /api/v1.0/admin/payment/source/related/info PaymentSource related info
     * @apiVersion 1.0.0
     * @apiName PaymentSource Related Info
     * @apiGroup Admin Payment Sources
     * @apiDescription This function is used to get paymentSource related info
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
     *          "error": "PaymentSource not found"
     *     }
     *
     * @Route("/related/info", name="api_admin_payment_source_related_info", methods={"POST"})
     *
     * @param Request $request
     * @param PaymentSourceService $paymentSourceService
     * @return JsonResponse
     * @throws \Doctrine\DBAL\ConnectionException
     * @throws \Throwable
     */
    public function relatedInfoAction(Request $request, PaymentSourceService $paymentSourceService)
    {
        $relatedData = $paymentSourceService->getRelatedInfo($request->get('ids'));

        return $this->respondSuccess(
            Response::HTTP_OK,
            '',
            [$relatedData]
        );
    }
}
