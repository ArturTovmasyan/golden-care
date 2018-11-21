<?php
namespace App\Api\V1\Admin\Controller;

use App\Api\V1\Admin\Service\ResidentService;
use App\Api\V1\Common\Controller\BaseController;
use App\Entity\Resident;
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
 * @Route("/api/v1.0/admin/resident")
 *
 * Class ResidentController
 * @package App\Api\V1\Admin\Controller
 */
class ResidentController extends BaseController
{
    /**
     * @api {get} /api/v1.0/admin/resident/grid Get Residents Grid
     * @apiVersion 1.0.0
     * @apiName Get Residents Grid
     * @apiGroup Admin Residents
     * @apiDescription This function is used to listing residents
     *
     * @apiHeader {String} Content-Type  application/json
     * @apiHeader {String} Authorization Bearer ACCESS_TOKEN
     *
     * @apiSuccess {Int}     id   The unique identifier of the residents
     * @apiSuccess {String}  name The name of the residents
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
     *                  "space": {
     *                      "id": 1
     *                  },
     *                  "physician": {
     *                      "id": 1
     *                  },
     *                  "first_name": "Harut",
     *                  "last_name": "Grigoryan",
     *                  "middle_name": "Gagik",
     *                  "birthday": "1987-12-24T15:26:20+04:00",
     *                  "gender": 1,
     *                  "state": 1
     *              }
     *          ]
     *     }
     *
     * @Route("/grid", name="api_admin_resident_grid", methods={"GET"})
     *
     * @param Request $request
     * @param ResidentService $residentService
     * @return JsonResponse|PdfResponse
     * @throws \ReflectionException
     */
    public function gridAction(Request $request, ResidentService $residentService)
    {
        return $this->respondGrid(
            $request,
            Resident::class,
            'api_admin_resident_grid',
            $residentService
        );
    }

    /**
     * @api {options} /api/v1.0/admin/resident/grid Get Residents Grid Options
     * @apiVersion 1.0.0
     * @apiName Get Residents Grid Options
     * @apiGroup Admin Residents
     * @apiDescription This function is used to describe options of listing
     *
     * @apiHeader {String} Content-Type  application/json
     * @apiHeader {String} Authorization Bearer ACCESS_TOKEN
     *
     * @apiSuccess {Array} options The options of the resident listing
     *
     * @apiSuccessExample {json} Sample Response:
     *     HTTP/1.1 200 OK
     *     [
     *          {
     *               "id": "name",
     *               "type": "integer",
     *               "sortable": true,
     *               "filterable": true,
     *          }
     *     ]
     *
     * @Route("/grid", name="api_admin_resident_grid_options", methods={"OPTIONS"})
     *
     * @param Request $request
     * @return JsonResponse
     * @throws \ReflectionException
     */
    public function gridOptionAction(Request $request)
    {
        return $this->getOptionsByGroupName(Resident::class, 'api_admin_resident_grid');
    }

    /**
     * @api {get} /api/v1.0/admin/resident Get Residents
     * @apiVersion 1.0.0
     * @apiName Get Residents
     * @apiGroup Admin Residents
     * @apiDescription This function is used to listing residents
     *
     * @apiHeader {String} Content-Type  application/json
     * @apiHeader {String} Authorization Bearer ACCESS_TOKEN
     *
     * @apiSuccess {Int}     id   The unique identifier of the resident
     * @apiSuccess {String}  name The name of the resident
     *
     * @apiSuccessExample {json} Sample Response:
     *     HTTP/1.1 200 OK
     *     [
     *          {
     *              "id": 1,
     *              "space": {
     *                  "id": 1
     *              },
     *              "physician": {
     *                  "id": 1
     *              },
     *              "first_name": "Harut",
     *              "last_name": "Grigoryan",
     *              "middle_name": "Gagik",
     *              "birthday": "1987-12-24T15:26:20+04:00",
     *              "gender": 1,
     *              "state": 1
     *          }
     *     ]
     *
     * @Route("", name="api_admin_resident_list", methods={"GET"})
     *
     * @param Request $request
     * @param ResidentService $residentService
     * @return JsonResponse|PdfResponse
     * @throws \ReflectionException
     */
    public function listAction(Request $request, ResidentService $residentService)
    {
        return $this->respondList(
            $request,
            Resident::class,
            'api_admin_resident_list',
            $residentService
        );
    }

    /**
     * @api {get} /api/v1.0/admin/resident/{id} Get Resident
     * @apiVersion 1.0.0
     * @apiName Get Resident
     * @apiGroup Admin Residents
     * @apiDescription This function is used to get resident
     *
     * @apiHeader {String} Content-Type  application/json
     * @apiHeader {String} Authorization Bearer ACCESS_TOKEN
     *
     * @apiSuccess {Int}     id            The unique identifier of the resident
     * @apiSuccess {String}  name          The Name of the resident
     *
     * @apiSuccessExample {json} Sample Response:
     *     HTTP/1.1 200 OK
     *     {
     *          "id": 1,
     *          "space": {
     *              "id": 1
     *          },
     *          "physician": {
     *              "id": 1
     *          },
     *          "first_name": "Harut",
     *          "last_name": "Grigoryan",
     *          "middle_name": "Gagik",
     *          "birthday": "1987-12-24T15:26:20+04:00",
     *          "gender": 1,
     *          "state": 1
     *     }
     *
     * @Route("/{id}", requirements={"id"="\d+"}, name="api_admin_resident_get", methods={"GET"})
     *
     * @param Request $request
     * @param ResidentService $residentService
     * @param $id
     * @return JsonResponse
     */
    public function getAction(Request $request, $id, ResidentService $residentService)
    {
        return $this->respondSuccess(
            Response::HTTP_OK,
            '',
            $residentService->getById($id),
            ['api_admin_resident_get']
        );
    }

    /**
     * @api {post} /api/v1.0/admin/resident Add Resident
     * @apiVersion 1.0.0
     * @apiName Add Resident
     * @apiGroup Admin Residents
     * @apiDescription This function is used to add resident
     *
     * @apiHeader {String} Content-Type  application/json
     * @apiHeader {String} Authorization Bearer ACCESS_TOKEN
     *
     * @apiParam {String}  name The name of the resident
     *
     * @apiParamExample {json} Request-Example:
     *     {
     *         "name": "User Management"
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
     * @Route("", name="api_admin_resident_add", methods={"POST"})
     *
     * @param Request $request
     * @param ResidentService $residentService
     * @return JsonResponse
     * @throws \Exception
     */
    public function addAction(Request $request, ResidentService $residentService)
    {
        $residentService->add(
            [
                'first_name'    => $request->get('first_name'),
                'last_name'     => $request->get('last_name'),
                'middle_name'   => $request->get('middle_name'),
                'space_id'      => $request->get('space_id'),
                'physician_id'  => $request->get('physician_id'),
                'birthday'      => $request->get('birthday'),
                'gender'        => $request->get('gender'),
            ]
        );

        return $this->respondSuccess(
            Response::HTTP_CREATED
        );
    }

    /**
     * @api {put} /api/v1.0/admin/resident/{id} Edit Resident
     * @apiVersion 1.0.0
     * @apiName Edit Resident
     * @apiGroup Admin Residents
     * @apiDescription This function is used to edit resident
     *
     * @apiHeader {String} Content-Type  application/json
     * @apiHeader {String} Authorization Bearer ACCESS_TOKEN
     *
     * @apiParam {Int}     id   The unique identifier of the resident
     * @apiParam {String}  name The name of the resident
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
     * @Route("/{id}", requirements={"id"="\d+"}, name="api_admin_resident_edit", methods={"PUT"})
     *
     * @param Request $request
     * @param $id
     * @param ResidentService $residentService
     * @return JsonResponse
     * @throws \Exception
     */
    public function editAction(Request $request, $id, ResidentService $residentService)
    {
        $residentService->edit(
            $id,
            [
                'first_name'    => $request->get('first_name'),
                'last_name'     => $request->get('last_name'),
                'middle_name'   => $request->get('middle_name'),
                'space_id'      => $request->get('space_id'),
                'physician_id'  => $request->get('physician_id'),
                'birthday'      => $request->get('birthday'),
                'gender'        => $request->get('gender'),
            ]
        );

        return $this->respondSuccess(
            Response::HTTP_CREATED
        );
    }

    /**
     * @api {delete} /api/v1.0/admin/resident/{id} Delete Resident
     * @apiVersion 1.0.0
     * @apiName Delete Resident
     * @apiGroup Admin Residents
     * @apiDescription This function is used to remove resident
     *
     * @apiHeader {String} Content-Type  application/json
     * @apiHeader {String} Authorization Bearer ACCESS_TOKEN
     *
     * @apiParam {Int} id The unique identifier of the resident
     *
     * @apiSuccessExample {json} Sample Response:
     *     HTTP/1.1 204 No Content
     *     {}
     * @apiErrorExample {json} Error-Response:
     *     HTTP/1.1 400 Bad Request
     *     {
     *          "code": 627,
     *          "error": "Resident not found"
     *     }
     *
     * @Route("/{id}", requirements={"id"="\d+"}, name="api_admin_resident_delete", methods={"DELETE"})
     *
     * @param Request $request
     * @param $id
     * @param ResidentService $residentService
     * @return JsonResponse
     * @throws \Doctrine\DBAL\ConnectionException
     * @throws \Throwable
     */
    public function deleteAction(Request $request, $id, ResidentService $residentService)
    {
        $residentService->remove($id);

        return $this->respondSuccess(
            Response::HTTP_NO_CONTENT
        );
    }

    /**
     * @api {delete} /api/v1.0/admin/resident Bulk Delete Residents
     * @apiVersion 1.0.0
     * @apiName Bulk Delete Residents
     * @apiGroup Admin Residents
     * @apiDescription This function is used to bulk remove residents
     *
     * @apiHeader {String} Content-Type  application/json
     * @apiHeader {String} Authorization Bearer ACCESS_TOKEN
     *
     * @apiParam {Int[]} ids The unique identifier of the resident TODO: review
     *
     * @apiSuccessExample {json} Sample Response:
     *     HTTP/1.1 204 No Content
     *     {}
     * @apiErrorExample {json} Error-Response:
     *     HTTP/1.1 400 Bad Request
     *     {
     *          "code": 627,
     *          "error": "Resident not found"
     *     }
     *
     * @Route("", name="api_admin_resident_delete_bulk", methods={"DELETE"})
     *
     * @param Request $request
     * @param ResidentService $residentService
     * @return JsonResponse
     * @throws \Doctrine\DBAL\ConnectionException
     * @throws \Throwable
     */
    public function deleteBulkAction(Request $request, ResidentService $residentService)
    {
        $residentService->removeBulk($request->get('ids'));

        return $this->respondSuccess(
            Response::HTTP_NO_CONTENT
        );
    }
}
