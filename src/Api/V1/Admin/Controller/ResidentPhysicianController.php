<?php
namespace App\Api\V1\Admin\Controller;

use App\Api\V1\Admin\Service\ResidentPhysicianService;
use App\Api\V1\Common\Controller\BaseController;
use App\Entity\ResidentPhysician;
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
 * @Route("/api/v1.0/admin/resident/physician")
 *
 * Class ResidentPhysicianController
 * @package App\Api\V1\Admin\Controller
 */
class ResidentPhysicianController extends BaseController
{
    /**
     * @api {get} /api/v1.0/admin/resident/physician/grid Get ResidentPhysician Grid
     * @apiVersion 1.0.0
     * @apiName Get ResidentPhysician Grid
     * @apiGroup Admin Resident Physicians
     * @apiDescription This function is used to listing residentPhysicians
     *
     * @apiHeader {String} Content-Type  application/json
     * @apiHeader {String} Authorization Bearer ACCESS_TOKEN
     *
     * @apiSuccess {Int}      id              The unique identifier of the residentPhysicians
     * @apiSuccess {String}   resident        The resident identifier of the residentPhysicians
     * @apiSuccess {String}   physician       The physician Full Name of the residentPhysicians
     * @apiSuccess {Int}      primary      The primary status of the residentPhysicians
     *
     * @apiSuccessExample {json} Sample Response:
     *     HTTP/1.1 200 OK
     *     {
     *          "page": "1",
     *          "per_page": 10,
     *          "total": 5,
     *          "data": [
     *              {
     *                   "id": 1,
     *                   "resident": "Henrik Cole",
     *                   "physician": "Joe Cole",
     *                   "relationship": "Brother"
     *              }
     *          ]
     *     }
     *
     * @Route("/grid", name="api_admin_resident_physician_grid", methods={"GET"})
     *
     * @param Request $request
     * @param ResidentPhysicianService $residentPhysicianService
     * @return JsonResponse|PdfResponse
     * @throws \ReflectionException
     */
    public function gridAction(Request $request, ResidentPhysicianService $residentPhysicianService)
    {
        return $this->respondGrid(
            $request,
            ResidentPhysician::class,
            'api_admin_resident_physician_grid',
            $residentPhysicianService,
            ['resident_id' => $request->get('resident_id')]
        );
    }

    /**
     * @api {options} /api/v1.0/admin/resident/physician/grid Get ResidentPhysician Grid Options
     * @apiVersion 1.0.0
     * @apiName Get ResidentPhysician Grid Options
     * @apiGroup Admin Resident Physicians
     * @apiDescription This function is used to describe options of listing
     *
     * @apiHeader {String} Content-Type  application/json
     * @apiHeader {String} Authorization Bearer ACCESS_TOKEN
     *
     * @apiSuccess {Array} options The options of the residentPhysician listing
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
     * @Route("/grid", name="api_admin_resident_physician_grid_options", methods={"OPTIONS"})
     *
     * @param Request $request
     * @return JsonResponse
     * @throws \ReflectionException
     */
    public function gridOptionAction(Request $request)
    {
        return $this->getOptionsByGroupName(ResidentPhysician::class, 'api_admin_resident_physician_grid');
    }

    /**
     * @api {get} /api/v1.0/admin/resident/physician Get ResidentPhysicians
     * @apiVersion 1.0.0
     * @apiName Get ResidentPhysicians
     * @apiGroup Admin Resident Physicians
     * @apiDescription This function is used to listing ResidentPhysicians
     *
     * @apiHeader {String} Content-Type  application/json
     * @apiHeader {String} Authorization Bearer ACCESS_TOKEN
     *
     * @apiSuccess {Int}      id          The unique identifier of the residentPhysician
     * @apiSuccess {Object}   resident    The resident of the residentPhysician
     * @apiSuccess {Object}   physician   The physician of the residentPhysician
     * @apiSuccess {String}   primary     The primary status of the residentPhysician
     *
     * @apiSuccessExample {json} Sample Response:
     *     HTTP/1.1 200 OK
     *     [
     *         {
     *             "id": 1,
     *             "resident": {
     *                 "id": 1,
     *                 "first_name": "Joe",
     *                 "last_name": "Cole"
     *             },
     *             "physician": {
     *                 "id": 1,
     *                 "first_name": "Joe",
     *                 "last_name": "Cole"
     *             },
     *             "primary": 0
     *         }
     *     ]
     *
     * @Route("", name="api_admin_resident_physician_list", methods={"GET"})
     *
     * @param Request $request
     * @param ResidentPhysicianService $residentPhysicianService
     * @return JsonResponse|PdfResponse
     * @throws \ReflectionException
     */
    public function listAction(Request $request, ResidentPhysicianService $residentPhysicianService)
    {
        return $this->respondList(
            $request,
            ResidentPhysician::class,
            'api_admin_resident_physician_list',
            $residentPhysicianService,
            ['resident_id' => $request->get('resident_id')]
        );
    }

    /**
     * @api {get} /api/v1.0/admin/resident/physician/{id} Get ResidentPhysician
     * @apiVersion 1.0.0
     * @apiName Get ResidentPhysician
     * @apiGroup Admin Resident Resident Physicians
     * @apiDescription This function is used to get ResidentPhysician
     *
     * @apiHeader {String} Content-Type  application/json
     * @apiHeader {String} Authorization Bearer ACCESS_TOKEN
     *
     * @apiSuccess {Int}      id          The unique identifier of the residentPhysician
     * @apiSuccess {Object}   resident    The resident of the residentPhysician
     * @apiSuccess {Object}   physician   The physician of the residentPhysician
     * @apiSuccess {String}   primary     The primary status of the residentPhysician
     *
     * @apiSuccessExample {json} Sample Response:
     *     HTTP/1.1 200 OK
     *     {
     *          "id": 1,
     *          "resident": {
     *              "id": 1,
     *              "first_name": "Joe",
     *              "last_name": "Cole"
     *          },
     *          "physician": {
     *              "id": 1,
     *              "first_name": "Joe",
     *              "last_name": "Cole"
     *          },
     *          "primary": 0
     *     }
     *
     * @Route("/{id}", requirements={"id"="\d+"}, name="api_admin_resident_physician_get", methods={"GET"})
     *
     * @param ResidentPhysicianService $residentPhysicianService
     * @param $id
     * @return JsonResponse
     */
    public function getAction(Request $request, $id, ResidentPhysicianService $residentPhysicianService)
    {
        return $this->respondSuccess(
            Response::HTTP_OK,
            '',
            $residentPhysicianService->getById($id),
            ['api_admin_resident_physician_get']
        );
    }

    /**
     * @api {post} /api/v1.0/admin/resident/physician Add ResidentPhysician
     * @apiVersion 1.0.0
     * @apiName Add ResidentPhysician
     * @apiGroup Admin Resident Physicians
     * @apiDescription This function is used to add Physicians
     *
     * @apiHeader {String} Content-Type  application/x-www-form-urlencoded
     * @apiHeader {String} Authorization Bearer ACCESS_TOKEN
     *
     * @apiParam {Int}  resident_id      The unique identifier of the resident
     * @apiParam {Int}  physician_id     The unique identifier of the physician
     * @apiParam {Int}  primary          The status of relationship
     *
     * @apiParamExample {json} Request-Example:
     *     {
     *          "resident_id": 1,
     *          "physician_id": 1,
     *          "primary": 1
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
     *              "physician_id": "Sorry, this value not be blank."
     *          }
     *     }
     *
     * @Route("", name="api_admin_resident_physician_add", methods={"POST"})
     *
     * @param Request $request
     * @param ResidentPhysicianService $residentPhysicianService
     * @return JsonResponse
     * @throws \Exception
     */
    public function addAction(Request $request, ResidentPhysicianService $residentPhysicianService)
    {
        $residentPhysicianService->add(
            [
                'resident_id'  => $request->get('resident_id'),
                'physician_id' => $request->get('physician_id'),
                'primary'      => $request->get('primary')
            ]
        );

        return $this->respondSuccess(
            Response::HTTP_CREATED
        );
    }

    /**
     * @api {put} /api/v1.0/admin/resident/physician/{id} Edit ResidentPhysician
     * @apiVersion 1.0.0
     * @apiName Edit ResidentPhysician
     * @apiGroup Admin Resident Physician
     * @apiDescription This function is used to edit residentPhysician
     *
     * @apiHeader {String} Content-Type  application/x-www-form-urlencoded
     * @apiHeader {String} Authorization Bearer ACCESS_TOKEN
     *
     * @apiParam {Int}     resident_id   The unique identifier of the resident
     * @apiParam {Int}     physician_id  The unique identifier of the physician
     * @apiParam {Object}  primary       The primary status identifier of residentPhysician
     *
     * @apiParamExample {json} Request-Example:
     *     {
     *          "resident_id": 1,
     *          "physician_id": 1,
     *          "primary": 1
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
     *              "physician_id": "Sorry, this value not be blank."
     *          }
     *     }
     *
     * @Route("/{id}", requirements={"id"="\d+"}, name="api_admin_resident_physician_edit", methods={"PUT"})
     *
     * @param Request $request
     * @param $id
     * @param ResidentPhysicianService $residentPhysicianService
     * @return JsonResponse
     * @throws \Exception
     */
    public function editAction(Request $request, $id, ResidentPhysicianService $residentPhysicianService)
    {
        $residentPhysicianService->edit(
            $id,
            [
                'resident_id'  => $request->get('resident_id'),
                'physician_id' => $request->get('physician_id'),
                'primary'      => $request->get('primary')
            ]
        );

        return $this->respondSuccess(
            Response::HTTP_CREATED
        );
    }

    /**
     * @api {delete} /api/v1.0/admin/resident/physician/{id} Delete ResidentPhysician
     * @apiVersion 1.0.0
     * @apiName Delete ResidentPhysician
     * @apiGroup Admin Resident Physicians
     * @apiDescription This function is used to remove residentPhysician
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
     *          "error": "ResidentPhysician not found"
     *     }
     *
     * @Route("/{id}", requirements={"id"="\d+"}, name="api_admin_resident_physician_delete", methods={"DELETE"})
     *
     * @param $id
     * @param ResidentPhysicianService $residentPhysicianService
     * @return JsonResponse
     * @throws \Doctrine\DBAL\ConnectionException
     * @throws \Throwable
     */
    public function deleteAction(Request $request, $id, ResidentPhysicianService $residentPhysicianService)
    {
        $residentPhysicianService->remove($id);

        return $this->respondSuccess(
            Response::HTTP_NO_CONTENT
        );
    }

    /**
     * @api {delete} /api/v1.0/admin/resident/physician Bulk Delete ResidentPhysicians
     * @apiVersion 1.0.0
     * @apiName Bulk Delete ResidentPhysicians
     * @apiGroup Admin Resident Physicians
     * @apiDescription This function is used to bulk remove ResidentPhysicians
     *
     * @apiHeader {String} Content-Type  application/x-www-form-urlencoded
     * @apiHeader {String} Authorization Bearer ACCESS_TOKEN
     *
     * @apiParam {Int[]} ids The unique identifier of the residentPhysicians
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
     *          "error": "ResidentPhysician not found"
     *     }
     *
     * @Route("", name="api_admin_resident_physician_bulk", methods={"DELETE"})
     *
     * @param Request $request
     * @param ResidentPhysicianService $residentPhysicianService
     * @return JsonResponse
     * @throws \Doctrine\DBAL\ConnectionException
     * @throws \Throwable
     */
    public function deleteBulkAction(Request $request, ResidentPhysicianService $residentPhysicianService)
    {
        $residentPhysicianService->removeBulk($request->get('ids'));

        return $this->respondSuccess(
            Response::HTTP_NO_CONTENT
        );
    }

    /**
     * @Route("/{resident_id}/primary", requirements={"resident_id"="\d+"}, name="api_admin_resident_physician_get_primary", methods={"GET"})
     *
     * @param ResidentPhysicianService $residentPhysicianService
     * @param $resident_id
     * @return JsonResponse
     */
    public function getPrimaryAction(Request $request, $resident_id, ResidentPhysicianService $residentPhysicianService)
    {
        return $this->respondSuccess(
            Response::HTTP_OK,
            '',
            $residentPhysicianService->getPrimaryByResidentId($resident_id),
            ['api_admin_resident_physician_get']
        );
    }
}
