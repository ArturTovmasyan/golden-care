<?php
namespace App\Api\V1\Admin\Controller;

use App\Api\V1\Admin\Service\DiagnosisService;
use App\Api\V1\Common\Controller\BaseController;
use App\Entity\Diagnosis;
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
 * @Route("/api/v1.0/admin/diagnosis")
 *
 * Class DiagnosisController
 * @package App\Api\V1\Admin\Controller
 */
class DiagnosisController extends BaseController
{
    /**
     * @api {get} /api/v1.0/admin/diagnosis/grid Get Diagnoses Grid
     * @apiVersion 1.0.0
     * @apiName Get Diagnoses Grid
     * @apiGroup Admin Diagnoses
     * @apiDescription This function is used to listing diagnoses
     *
     * @apiHeader {String} Content-Type  application/json
     * @apiHeader {String} Authorization Bearer ACCESS_TOKEN
     *
     * @apiSuccess {Int}     id            The unique identifier of the diagnosis
     * @apiSuccess {String}  title         The title of the diagnosis
     * @apiSuccess {String}  acronym       The acronym time of the diagnosis
     * @apiSuccess {String}  description   The description time of the diagnosis
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
     *                  "title": "High Blood Pressure",
     *                  "acronym": "HBP",
     *                  "description": "some description"
     *              }
     *          ]
     *     }
     *
     * @Route("/grid", name="api_admin_diagnosis_grid", methods={"GET"})
     *
     * @param Request $request
     * @param DiagnosisService $diagnosisService
     * @return JsonResponse|PdfResponse
     * @throws \ReflectionException
     */
    public function gridAction(Request $request, DiagnosisService $diagnosisService)
    {
        return $this->respondGrid(
            $request,
            Diagnosis::class,
            'api_admin_diagnosis_grid',
            $diagnosisService
        );
    }

    /**
     * @api {options} /api/v1.0/admin/diagnosis/grid Get Diagnosis Grid Options
     * @apiVersion 1.0.0
     * @apiName Get Diagnosis Grid Options
     * @apiGroup Admin Diagnoses
     * @apiDescription This function is used to describe options of listing
     *
     * @apiHeader {String} Content-Type  application/json
     * @apiHeader {String} Authorization Bearer ACCESS_TOKEN
     *
     * @apiSuccess {Array} options The options of the diagnosis listing
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
     * @Route("/grid", name="api_admin_diagnosis_grid_options", methods={"OPTIONS"})
     *
     * @param Request $request
     * @return JsonResponse
     * @throws \ReflectionException
     */
    public function gridOptionAction(Request $request)
    {
        return $this->getOptionsByGroupName(Diagnosis::class, 'api_admin_diagnosis_grid');
    }

    /**
     * @api {get} /api/v1.0/admin/diagnosis Get Diagnoses
     * @apiVersion 1.0.0
     * @apiName Get Diagnoses
     * @apiGroup Admin Diagnoses
     * @apiDescription This function is used to listing diagnoses
     *
     * @apiHeader {String} Content-Type  application/json
     * @apiHeader {String} Authorization Bearer ACCESS_TOKEN
     *
     * @apiSuccess {Int}     id            The unique identifier of the diagnosis
     * @apiSuccess {String}  title         The title of the diagnosis
     * @apiSuccess {String}  acronym       The acronym time of the diagnosis
     * @apiSuccess {String}  description   The description time of the diagnosis
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
     *                  "title": "High Blood Pressure",
     *                  "acronym": "HBP",
     *                  "description": "some description"
     *              }
     *          ]
     *     }
     *
     * @Route("", name="api_admin_diagnosis_list", methods={"GET"})
     *
     * @param Request $request
     * @param DiagnosisService $diagnosisService
     * @return JsonResponse|PdfResponse
     * @throws \ReflectionException
     */
    public function listAction(Request $request, DiagnosisService $diagnosisService)
    {
        return $this->respondList(
            $request,
            Diagnosis::class,
            'api_admin_diagnosis_list',
            $diagnosisService
        );
    }

    /**
     * @api {get} /api/v1.0/admin/diagnosis/{id} Get Diagnosis
     * @apiVersion 1.0.0
     * @apiName Get Diagnosis
     * @apiGroup Admin Diagnoses
     * @apiDescription This function is used to get diagnosis
     *
     * @apiHeader {String} Content-Type  application/json
     * @apiHeader {String} Authorization Bearer ACCESS_TOKEN
     *
     * @apiSuccess {Int}     id            The unique identifier of the diagnosis
     * @apiSuccess {String}  title         The title of the diagnosis
     * @apiSuccess {String}  acronym       The acronym time of the diagnosis
     * @apiSuccess {String}  description   The description time of the diagnosis
     *
     * @apiSuccessExample {json} Sample Response:
     *     HTTP/1.1 200 OK
     *     {
     *          "data": {
     *                  "id": 1,
     *                  "title": "High Blood Pressure",
     *                  "acronym": "HBP",
     *                  "description": "some description"
     *          }
     *     }
     *
     * @Route("/{id}", requirements={"id"="\d+"}, name="api_admin_diagnosis_get", methods={"GET"})
     *
     * @param DiagnosisService $diagnosisService
     * @param $id
     * @return JsonResponse
     */
    public function getAction(Request $request, $id, DiagnosisService $diagnosisService)
    {
        return $this->respondSuccess(
            Response::HTTP_OK,
            '',
            $diagnosisService->getById($id),
            ['api_admin_diagnosis_get']
        );
    }

    /**
     * @api {post} /api/v1.0/admin/diagnosis Add Diagnosis
     * @apiVersion 1.0.0
     * @apiName Add Diagnosis
     * @apiGroup Admin Diagnoses
     * @apiDescription This function is used to add diagnosis
     *
     * @apiHeader {String} Content-Type  application/x-www-form-urlencoded
     * @apiHeader {String} Authorization Bearer ACCESS_TOKEN
     *
     * @apiParam {String}  title           The title of the diagnosis
     * @apiParam {String}  [acronym]       The acronym of the diagnosis
     * @apiParam {String}  [description]   The description of the diagnosis
     *
     * @apiParamExample {json} Request-Example:
     *     {
     *         "title": "High Blood Pressure",
     *         "acronym": "HBP",
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
     * @Route("", name="api_admin_diagnosis_add", methods={"POST"})
     *
     * @param Request $request
     * @param DiagnosisService $diagnosisService
     * @return JsonResponse
     * @throws \Doctrine\DBAL\ConnectionException
     */
    public function addAction(Request $request, DiagnosisService $diagnosisService)
    {
        $diagnosisService->add(
            [
                'title' => $request->get('title'),
                'acronym' => $request->get('acronym') ?? '',
                'description' => $request->get('description') ?? ''
            ]
        );

        return $this->respondSuccess(
            Response::HTTP_CREATED
        );
    }

    /**
     * @api {put} /api/v1.0/admin/diagnosis/{id} Edit Diagnosis
     * @apiVersion 1.0.0
     * @apiName Edit Diagnosis
     * @apiGroup Admin Diagnoses
     * @apiDescription This function is used to edit diagnosis
     *
     * @apiHeader {String} Content-Type  application/x-www-form-urlencoded
     * @apiHeader {String} Authorization Bearer ACCESS_TOKEN
     *
     * @apiParam {String}  title          The title of the diagnosis
     * @apiParam {String}  [acronym]      The acronym of the diagnosis
     * @apiParam {String}  [description]  The description of the diagnosis
     *
     * @apiParamExample {json} Request-Example:
     *     {
     *         "title": "High Blood Pressure",
     *         "acronym": "HBP",
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
     * @Route("/{id}", requirements={"id"="\d+"}, name="api_admin_diagnosis_edit", methods={"PUT"})
     *
     * @param Request $request
     * @param $id
     * @param DiagnosisService $diagnosisService
     * @return JsonResponse
     * @throws \Doctrine\DBAL\ConnectionException
     */
    public function editAction(Request $request, $id, DiagnosisService $diagnosisService)
    {
        $diagnosisService->edit(
            $id,
            [
                'title' => $request->get('title'),
                'acronym' => $request->get('acronym') ?? '',
                'description' => $request->get('description') ?? ''
            ]
        );

        return $this->respondSuccess(
            Response::HTTP_CREATED
        );
    }

    /**
     * @api {delete} /api/v1.0/admin/diagnosis/{id} Delete Diagnosis
     * @apiVersion 1.0.0
     * @apiName Delete Diagnosis
     * @apiGroup Admin Diagnoses
     * @apiDescription This function is used to remove diagnosis
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
     *          "error": "Diagnosis not found"
     *     }
     *
     * @Route("/{id}", requirements={"id"="\d+"}, name="api_admin_diagnosis_delete", methods={"DELETE"})
     *
     * @param $id
     * @param DiagnosisService $diagnosisService
     * @return JsonResponse
     * @throws \Doctrine\DBAL\ConnectionException
     * @throws \Throwable
     */
    public function deleteAction(Request $request, $id, DiagnosisService $diagnosisService)
    {
        $diagnosisService->remove($id);

        return $this->respondSuccess(
            Response::HTTP_NO_CONTENT
        );
    }

    /**
     * @api {delete} /api/v1.0/admin/diagnosis Bulk Delete Diagnosiss
     * @apiVersion 1.0.0
     * @apiName Bulk Delete Diagnosiss
     * @apiGroup Admin Diagnoses
     * @apiDescription This function is used to bulk remove diagnosiss
     *
     * @apiHeader {String} Content-Type  application/x-www-form-urlencoded
     * @apiHeader {String} Authorization Bearer ACCESS_TOKEN
     *
     * @apiParam {Int[]} ids The unique identifier of the diagnosiss
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
     *          "error": "Diagnosis not found"
     *     }
     *
     * @Route("", name="api_admin_diagnosis_delete_bulk", methods={"DELETE"})
     *
     * @param Request $request
     * @param DiagnosisService $diagnosisService
     * @return JsonResponse
     * @throws \Doctrine\DBAL\ConnectionException
     * @throws \Throwable
     */
    public function deleteBulkAction(Request $request, DiagnosisService $diagnosisService)
    {
        $diagnosisService->removeBulk(
            [
                'ids' => $request->get('ids')
            ]
        );

        return $this->respondSuccess(
            Response::HTTP_NO_CONTENT
        );
    }
}
