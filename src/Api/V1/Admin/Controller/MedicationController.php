<?php
namespace App\Api\V1\Admin\Controller;

use App\Api\V1\Admin\Service\MedicationService;
use App\Api\V1\Common\Controller\BaseController;
use App\Entity\Medication;
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
 * @Route("/api/v1.0/admin/medication")
 *
 * Class MedicationController
 * @package App\Api\V1\Admin\Controller
 */
class MedicationController extends BaseController
{
    /**
     * @api {get} /api/v1.0/admin/medication/grid Get Medications
     * @apiVersion 1.0.0
     * @apiName Get Medications
     * @apiGroup Admin Medications
     * @apiDescription This function is used to listing medications
     *
     * @apiHeader {String} Content-Type  application/json
     * @apiHeader {String} Authorization Bearer ACCESS_TOKEN
     *
     * @apiSuccess {Int}     id   The unique identifier of the medication
     * @apiSuccess {String}  name The name of the medication
     *
     * @apiSuccessExample {json} Sample Response:
     *     HTTP/1.1 200 OK
     *     {
     *          "page": "1",
     *          "per_page": 10,
     *          "total": 5,
     *          "data": [
     *              {
     *                  "id": 1,
     *                  "name": "Lidocaine"
     *              }
     *          ]
     *     }
     *
     * @Route("/grid", name="api_admin_medication_grid", methods={"GET"})
     *
     * @param Request $request
     * @param MedicationService $medicationService
     * @return JsonResponse|PdfResponse
     * @throws \ReflectionException
     */
    public function gridAction(Request $request, MedicationService $medicationService)
    {
        return $this->respondGrid(
            $request,
            Medication::class,
            'api_admin_medication_grid',
            $medicationService
        );
    }

    /**
     * @api {options} /api/v1.0/admin/medication/grid Get Medications Options
     * @apiVersion 1.0.0
     * @apiName Get Medications Options
     * @apiGroup Admin Medications
     * @apiDescription This function is used to describe options of listing
     *
     * @apiHeader {String} Content-Type  application/json
     * @apiHeader {String} Authorization Bearer ACCESS_TOKEN
     *
     * @apiSuccess {Array} options The options of the medication listing
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
     * @Route("/grid", name="api_admin_medication_grid_options", methods={"OPTIONS"})
     *
     * @param Request $request
     * @return JsonResponse
     * @throws \ReflectionException
     */
    public function gridOptionAction(Request $request)
    {
        return $this->getOptionsByGroupName(Medication::class, 'api_admin_medication_grid');
    }

    /**
     * @api {get} /api/v1.0/admin/medication Get Medications
     * @apiVersion 1.0.0
     * @apiName Get Medications
     * @apiGroup Admin Medications
     * @apiDescription This function is used to listing medications
     *
     * @apiHeader {String} Content-Type  application/json
     * @apiHeader {String} Authorization Bearer ACCESS_TOKEN
     *
     * @apiSuccess {Int}     id   The unique identifier of the medication
     * @apiSuccess {String}  name The name of the medication
     *
     * @apiSuccessExample {json} Sample Response:
     *     HTTP/1.1 200 OK
     *     {
     *          [
     *              {
     *                  "id": 1,
     *                  "name": "Lidocaine"
     *              }
     *          ]
     *     }
     *
     * @Route("", name="api_admin_medication_list", methods={"GET"})
     *
     * @param Request $request
     * @param MedicationService $medicationService
     * @return JsonResponse|PdfResponse
     * @throws \ReflectionException
     */
    public function listAction(Request $request, MedicationService $medicationService)
    {
        return $this->respondList(
            $request,
            Medication::class,
            'api_admin_medication_list',
            $medicationService
        );
    }

    /**
     * @api {get} /api/v1.0/admin/medication/{id} Get Medication
     * @apiVersion 1.0.0
     * @apiName Get Medication
     * @apiGroup Admin Medications
     * @apiDescription This function is used to get medication
     *
     * @apiHeader {String} Content-Type  application/json
     * @apiHeader {String} Authorization Bearer ACCESS_TOKEN
     *
     * @apiSuccess {Int}     id            The unique identifier of the medication
     * @apiSuccess {String}  name          The Name of the medication
     *
     * @apiSuccessExample {json} Sample Response:
     *     HTTP/1.1 200 OK
     *     {
     *          "id": 1,
     *          "name": "Lidocaine"
     *     }
     *
     * @Route("/{id}", requirements={"id"="\d+"}, name="api_admin_medication_get", methods={"GET"})
     *
     * @param Request $request
     * @param MedicationService $medicationService
     * @param $id
     * @return JsonResponse
     */
    public function getAction(Request $request, $id, MedicationService $medicationService)
    {
        return $this->respondSuccess(
            Response::HTTP_OK,
            '',
            $medicationService->getById($id),
            ['api_admin_medication_get']
        );
    }

    /**
     * @api {post} /api/v1.0/admin/medication Add Medication
     * @apiVersion 1.0.0
     * @apiName Add Medication
     * @apiGroup Admin Medications
     * @apiDescription This function is used to add medication
     *
     * @apiHeader {String} Content-Type  application/json
     * @apiHeader {String} Authorization Bearer ACCESS_TOKEN
     *
     * @apiParam {String}  name The name of the medication
     *
     * @apiParamExample {json} Request-Example:
     *     {
     *         "name": "Lidocaine"
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
     *              "name": "Sorry, this name is already in use."
     *          }
     *     }
     *
     * @Route("", name="api_admin_medication_add", methods={"POST"})
     *
     * @param Request $request
     * @param MedicationService $medicationService
     * @return JsonResponse
     * @throws \Doctrine\DBAL\ConnectionException
     */
    public function addAction(Request $request, MedicationService $medicationService)
    {
        $medicationService->add(
            [
                'name' => $request->get('name')
            ]
        );

        return $this->respondSuccess(
            Response::HTTP_CREATED
        );
    }

    /**
     * @api {post} /api/v1.0/admin/medication/{id} Edit Medication
     * @apiVersion 1.0.0
     * @apiName Edit Medication
     * @apiGroup Admin Medication
     * @apiDescription This function is used to edit medication
     *
     * @apiHeader {String} Content-Type  application/json
     * @apiHeader {String} Authorization Bearer ACCESS_TOKEN
     *
     * @apiParam {Int}     id   The unique identifier of the medication
     * @apiParam {String}  name The name of the medication
     *
     * @apiParamExample {json} Request-Example:
     *     {
     *         "name": "Son"
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
     *              "name": "Sorry, this name is already in use."
     *          }
     *     }
     *
     * @Route("/{id}", requirements={"id"="\d+"}, name="api_admin_medication_edit", methods={"POST"})
     *
     * @param Request $request
     * @param $id
     * @param MedicationService $medicationService
     * @return JsonResponse
     * @throws \Doctrine\DBAL\ConnectionException
     */
    public function editAction(Request $request, $id, MedicationService $medicationService)
    {
        $medicationService->edit(
            $id,
            [
                'name' => $request->get('name')
            ]
        );

        return $this->respondSuccess(
            Response::HTTP_CREATED
        );
    }

    /**
     * @api {delete} /api/v1.0/admin/medication/{id} Delete Medication
     * @apiVersion 1.0.0
     * @apiName Delete Medication
     * @apiGroup Admin Medications
     * @apiDescription This function is used to remove medication
     *
     * @apiHeader {String} Content-Type  application/json
     * @apiHeader {String} Authorization Bearer ACCESS_TOKEN
     *
     * @apiParam {Int} id The unique identifier of the medication
     *
     * @apiSuccessExample {json} Sample Response:
     *     HTTP/1.1 204 No Content
     *     {}
     * @apiErrorExample {json} Error-Response:
     *     HTTP/1.1 400 Bad Request
     *     {
     *          "code": 627,
     *          "error": "Medication not found"
     *     }
     *
     * @Route("/{id}", requirements={"id"="\d+"}, name="api_admin_medication_delete", methods={"DELETE"})
     *
     * @param Request $request
     * @param $id
     * @param MedicationService $medicationService
     * @return JsonResponse
     * @throws \Doctrine\DBAL\ConnectionException
     * @throws \Throwable
     */
    public function deleteAction(Request $request, $id, MedicationService $medicationService)
    {
        $medicationService->remove($id);

        return $this->respondSuccess(
            Response::HTTP_NO_CONTENT
        );
    }

    /**
     * @api {delete} /api/v1.0/admin/medication Bulk Delete Medications
     * @apiVersion 1.0.0
     * @apiName Bulk Delete Medications
     * @apiGroup Admin Medications
     * @apiDescription This function is used to bulk remove medications
     *
     * @apiHeader {String} Content-Type  application/json
     * @apiHeader {String} Authorization Bearer ACCESS_TOKEN
     *
     * @apiParam {Int[]} ids The unique identifier of the medication TODO: review
     *
     * @apiSuccessExample {json} Sample Response:
     *     HTTP/1.1 204 No Content
     *     {}
     * @apiErrorExample {json} Error-Response:
     *     HTTP/1.1 400 Bad Request
     *     {
     *          "code": 627,
     *          "error": "Medication not found"
     *     }
     *
     * @Route("", name="api_admin_medication_delete_bulk", methods={"DELETE"})
     *
     * @param Request $request
     * @param MedicationService $medicationService
     * @return JsonResponse
     * @throws \Doctrine\DBAL\ConnectionException
     * @throws \Throwable
     */
    public function deleteBulkAction(Request $request, MedicationService $medicationService)
    {
        $ids = $request->get('ids');

        if (!empty($ids)) {
            foreach ($ids as $id) {
                $medicationService->remove($id);
            }
        }

        return $this->respondSuccess(
            Response::HTTP_NO_CONTENT
        );
    }
}