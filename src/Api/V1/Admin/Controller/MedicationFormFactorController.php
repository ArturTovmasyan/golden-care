<?php
namespace App\Api\V1\Admin\Controller;

use App\Api\V1\Admin\Service\MedicationFormFactorService;
use App\Api\V1\Common\Controller\BaseController;
use App\Entity\MedicationFormFactor;
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
 * @Route("/api/v1.0/admin/medication/form/factor")
 *
 * Class MedicationFormFactorController
 * @package App\Api\V1\Admin\Controller
 */
class MedicationFormFactorController extends BaseController
{
    /**
     * @api {get} /api/v1.0/admin/medication/form/factor/grid Get MedicationFormFactors Grid
     * @apiVersion 1.0.0
     * @apiName Get MedicationFormFactors Grid
     * @apiGroup Admin Medication Form Factors
     * @apiDescription This function is used to listing medicationFormFactors
     *
     * @apiHeader {String} Content-Type  application/json
     * @apiHeader {String} Authorization Bearer ACCESS_TOKEN
     *
     * @apiSuccess {Int}     id            The unique identifier of the medicationFormFactor
     * @apiSuccess {String}  title         The title of the medicationFormFactor
     *
     * @apiSuccessExample {json} Sample Response:
     *     HTTP/1.1 200 OK
     *     {
     *          "page": "1",
     *          "per_page": 10,
     *          "all_pages": 1,
     *          "total": 5,
     *          "data": [
     *
     *                  "id": 1,
     *                  "title": "Factor second"
     *              }
     *          ]
     *     }
     *
     * @Route("/grid", name="api_admin_medication_form_factor_grid", methods={"GET"})
     *
     * @param Request $request
     * @param MedicationFormFactorService $medicationFormFactorService
     * @return JsonResponse|PdfResponse
     * @throws \ReflectionException
     */
    public function gridAction(Request $request, MedicationFormFactorService $medicationFormFactorService)
    {
        return $this->respondGrid(
            $request,
            MedicationFormFactor::class,
            'api_admin_medication_form_factor_grid',
            $medicationFormFactorService
        );
    }

    /**
     * @api {options} /api/v1.0/admin/medication/form/factor/grid Get MedicationFormFactor Grid Options
     * @apiVersion 1.0.0
     * @apiName Get MedicationFormFactor Grid Options
     * @apiGroup Admin Medication Form Factors
     * @apiDescription This function is used to describe options of listing
     *
     * @apiHeader {String} Content-Type  application/json
     * @apiHeader {String} Authorization Bearer ACCESS_TOKEN
     *
     * @apiSuccess {Array} options The options of the medicationFormFactor listing
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
     * @Route("/grid", name="api_admin_medication_form_factor_grid_options", methods={"OPTIONS"})
     *
     * @param Request $request
     * @return JsonResponse
     * @throws \ReflectionException
     */
    public function gridOptionAction(Request $request)
    {
        return $this->getOptionsByGroupName(MedicationFormFactor::class, 'api_admin_medication_form_factor_grid');
    }

    /**
     * @api {get} /api/v1.0/admin/medication/form/factor Get MedicationFormFactors
     * @apiVersion 1.0.0
     * @apiName Get MedicationFormFactors
     * @apiGroup Admin Medication Form Factors
     * @apiDescription This function is used to listing medicationFormFactors
     *
     * @apiHeader {String} Content-Type  application/json
     * @apiHeader {String} Authorization Bearer ACCESS_TOKEN
     *
     * @apiSuccess {Int}     id            The unique identifier of the medicationFormFactor
     * @apiSuccess {String}  title         The title of the medicationFormFactor
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
     *                  "title": "Factor second"
     *              }
     *          ]
     *     }
     *
     * @Route("", name="api_admin_medication_form_factor_list", methods={"GET"})
     *
     * @param Request $request
     * @param MedicationFormFactorService $medicationFormFactorService
     * @return JsonResponse|PdfResponse
     * @throws \ReflectionException
     */
    public function listAction(Request $request, MedicationFormFactorService $medicationFormFactorService)
    {
        return $this->respondList(
            $request,
            MedicationFormFactor::class,
            'api_admin_medication_form_factor_list',
            $medicationFormFactorService
        );
    }

    /**
     * @api {get} /api/v1.0/admin/medication/form/factor/{id} Get MedicationFormFactor
     * @apiVersion 1.0.0
     * @apiName Get MedicationFormFactor
     * @apiGroup Admin Medication Form Factors
     * @apiDescription This function is used to get medicationFormFactor
     *
     * @apiHeader {String} Content-Type  application/json
     * @apiHeader {String} Authorization Bearer ACCESS_TOKEN
     *
     * @apiSuccess {Int}     id            The unique identifier of the medicationFormFactor
     * @apiSuccess {String}  title         The title of the medicationFormFactor
     *
     * @apiSuccessExample {json} Sample Response:
     *     HTTP/1.1 200 OK
     *     {
     *          "data": {
     *                  "id": 1,
     *                  "title": "Factor second"
     *          }
     *     }
     *
     * @Route("/{id}", requirements={"id"="\d+"}, name="api_admin_medication_form_factor_get", methods={"GET"})
     *
     * @param MedicationFormFactorService $medicationFormFactorService
     * @param $id
     * @return JsonResponse
     */
    public function getAction(Request $request, $id, MedicationFormFactorService $medicationFormFactorService)
    {
        return $this->respondSuccess(
            Response::HTTP_OK,
            '',
            $medicationFormFactorService->getById($id),
            ['api_admin_medication_form_factor_get']
        );
    }

    /**
     * @api {post} /api/v1.0/admin/medication/form/factor Add MedicationFormFactor
     * @apiVersion 1.0.0
     * @apiName Add MedicationFormFactor
     * @apiGroup Admin Medication Form Factors
     * @apiDescription This function is used to add medicationFormFactor
     *
     * @apiHeader {String} Content-Type  application/x-www-form-urlencoded
     * @apiHeader {String} Authorization Bearer ACCESS_TOKEN
     *
     * @apiParam {String}  title           The title of the medicationFormFactor
     *
     * @apiParamExample {json} Request-Example:
     *     {
     *         "title": "Factor second"
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
     * @Route("", name="api_admin_medication_form_factor_add", methods={"POST"})
     *
     * @param Request $request
     * @param MedicationFormFactorService $medicationFormFactorService
     * @return JsonResponse
     * @throws \Doctrine\DBAL\ConnectionException
     */
    public function addAction(Request $request, MedicationFormFactorService $medicationFormFactorService)
    {
        $medicationFormFactorService->add(
            [
                'title' => $request->get('title')
            ]
        );

        return $this->respondSuccess(
            Response::HTTP_CREATED
        );
    }

    /**
     * @api {put} /api/v1.0/admin/medication/form/factor/{id} Edit MedicationFormFactor
     * @apiVersion 1.0.0
     * @apiName Edit MedicationFormFactor
     * @apiGroup Admin Medication Form Factors
     * @apiDescription This function is used to edit medicationFormFactor
     *
     * @apiHeader {String} Content-Type  application/x-www-form-urlencoded
     * @apiHeader {String} Authorization Bearer ACCESS_TOKEN
     *
     * @apiParam {String}  title          The title of the medicationFormFactor
     *
     * @apiParamExample {json} Request-Example:
     *     {
     *         "title": "Factor second"
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
     * @Route("/{id}", requirements={"id"="\d+"}, name="api_admin_medication_form_factor_edit", methods={"PUT"})
     *
     * @param Request $request
     * @param $id
     * @param MedicationFormFactorService $medicationFormFactorService
     * @return JsonResponse
     * @throws \Doctrine\DBAL\ConnectionException
     */
    public function editAction(Request $request, $id, MedicationFormFactorService $medicationFormFactorService)
    {
        $medicationFormFactorService->edit(
            $id,
            [
                'title' => $request->get('title')
            ]
        );

        return $this->respondSuccess(
            Response::HTTP_CREATED
        );
    }

    /**
     * @api {delete} /api/v1.0/admin/medication/form/factor/{id} Delete MedicationFormFactor
     * @apiVersion 1.0.0
     * @apiName Delete MedicationFormFactor
     * @apiGroup Admin Medication Form Factors
     * @apiDescription This function is used to remove medicationFormFactor
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
     *          "error": "MedicationFormFactor not found"
     *     }
     *
     * @Route("/{id}", requirements={"id"="\d+"}, name="api_admin_medication_form_factor_delete", methods={"DELETE"})
     *
     * @param $id
     * @param MedicationFormFactorService $medicationFormFactorService
     * @return JsonResponse
     * @throws \Doctrine\DBAL\ConnectionException
     * @throws \Throwable
     */
    public function deleteAction(Request $request, $id, MedicationFormFactorService $medicationFormFactorService)
    {
        $medicationFormFactorService->remove($id);

        return $this->respondSuccess(
            Response::HTTP_NO_CONTENT
        );
    }

    /**
     * @api {delete} /api/v1.0/admin/medication/form/factor Bulk Delete MedicationFormFactors
     * @apiVersion 1.0.0
     * @apiName Bulk Delete MedicationFormFactors
     * @apiGroup Admin Medication Form Factors
     * @apiDescription This function is used to bulk remove medicationFormFactors
     *
     * @apiHeader {String} Content-Type  application/x-www-form-urlencoded
     * @apiHeader {String} Authorization Bearer ACCESS_TOKEN
     *
     * @apiParam {Int[]} ids The unique identifier of the medicationFormFactors
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
     *          "error": "MedicationFormFactor not found"
     *     }
     *
     * @Route("", name="api_admin_medication_form_factor_delete_bulk", methods={"DELETE"})
     *
     * @param Request $request
     * @param MedicationFormFactorService $medicationFormFactorService
     * @return JsonResponse
     * @throws \Doctrine\DBAL\ConnectionException
     * @throws \Throwable
     */
    public function deleteBulkAction(Request $request, MedicationFormFactorService $medicationFormFactorService)
    {
        $medicationFormFactorService->removeBulk(
            [
                'ids' => $request->get('ids')
            ]
        );

        return $this->respondSuccess(
            Response::HTTP_NO_CONTENT
        );
    }
}
