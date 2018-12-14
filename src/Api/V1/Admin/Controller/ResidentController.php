<?php
namespace App\Api\V1\Admin\Controller;

use App\Api\V1\Admin\Service\ResidentService;
use App\Api\V1\Common\Controller\BaseController;
use App\Api\V1\Common\Service\FileService;
use App\Api\V1\Common\Service\Helper\ResidentPhotoHelper;
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
     *                  "id": 8,
     *                  "salutation": "Mr.",
     *                  "first_name": "Gagik",
     *                  "last_name": "Gabrielyan",
     *                  "middle_name": "",
     *                  "space_id": 1,
     *                  "physician_id": 1,
     *                  "gender": 1,
     *                  "birthday": "1990-12-24T11:26:18+04:00"
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
     *              "salutation": {
     *                  "id": 1,
     *                  "name": "Mr."
     *              },
     *              "first_name": "Harut",
     *              "last_name": "Grigoryan",
     *              "middle_name": "Gagik",
     *              "birthday": "1987-12-24T15:26:20+04:00",
     *              "gender": 1,
     *              "state": 1,
     *              "resident_facility_option": {
     *                  "dining_room": {
     *                      "id": 1
     *                  },
     *                  "date_admitted": "1987-12-24T19:26:18+04:00",
     *                  "state": 1,
     *                  "dnr": true,
     *                  "polst": false,
     *                  "ambulatory": true,
     *                  "care_group": 5,
     *                  "care_level": {
     *                      "id": 1
     *                  }
     *              },
     *              "resident_apartment_option": null,
     *              "resident_region_option": null
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
     * @api {get} /api/v1.0/admin/resident/type/1/state/1 Get Residents by Params
     * @apiVersion 1.0.0
     * @apiName Get Residents By Params
     * @apiGroup Admin Residents
     * @apiDescription This function is used to listing residents by options
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
     *              "id": 8,
     *              "first_name": "Gagik",
     *              "last_name": "Gabrielyan",
     *              "resident_facility_option": {
     *                  "facility_room": {
     *                      "id": 1,
     *                      "number": "45"
     *                  }
     *              },
     *              "resident_apartment_option": null
     *          }
     *     ]
     *
     * @Route("/type/{type}/{id}/state/{state}", requirements={"type"="\d+", "id"="\d+", "state"="\d+"}, name="api_admin_resident_list_by_params", methods={"GET"})
     *
     * @param Request $request
     * @param $type
     * @param $id
     * @param $state
     * @param ResidentService $residentService
     * @return JsonResponse
     */
    public function listByOptionAction(Request $request, $type, $id, $state, ResidentService $residentService)
    {
        return $this->respondSuccess(
            Response::HTTP_OK,
            '',
            $residentService->getByTypeAndState($type, $id, $state),
            ['api_admin_resident_list_by_params']
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
     *
     *          "salutation": {
     *              "id": 1,
     *              "name": "Mr."
     *          },
     *          "first_name": "Harut",
     *          "last_name": "Grigoryan",
     *          "middle_name": "Gagik",
     *          "photo": "",
     *          "birthday": "1987-12-24T15:26:20+04:00",
     *          "gender": 1,
     *          "state": 1,
     *          "resident_facility_option": {
     *              "dining_room": {
     *                  "id": 1
     *              },
     *              "date_admitted": "1987-12-24T19:26:18+04:00",
     *              "state": 1,
     *              "dnr": true,
     *              "polst": false,
     *              "ambulatory": true,
     *              "care_group": 5,
     *              "care_level": {
     *                  "id": 1
     *              }
     *          },
     *          "resident_apartment_option": null,
     *          "resident_region_option": null
     *     }
     *
     * @Route("/{id}", requirements={"id"="\d+"}, name="api_admin_resident_get", methods={"GET"})
     *
     * @param Request $request
     * @param $id
     * @param ResidentService $residentService
     * @param ResidentPhotoHelper $residentPhotoHelper
     * @return JsonResponse
     */
    public function getAction(Request $request, $id, ResidentService $residentService, ResidentPhotoHelper $residentPhotoHelper)
    {
        $residentService->setResidentPhotoHelper($residentPhotoHelper);

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
     * @apiParamExample {json} Facility Option Request:
     *     {
     *          "first_name": "Joe",
     *          "last_name": "Cole",
     *          "middle_name": "",
     *          "type": 1,
     *          "space_id": 1,
     *          "salutation_id": 1,
     *          "gender": 1,
     *          "birthday": "12-24-1990",
     *          "option": [
     *              "dining_room_id": 1,
     *              "dnr": 1,
     *              "care_level": 1,
     *              "date_admitted": "12-24-2016",
     *              "care_group": 5,
     *              "ambulatory": 1,
     *              "polst": 1
     *          ],
     *          "phones": [
     *              {
     *                  "compatibility": 1,
     *                  "type": 1,
     *                  "number": "+3748880880",
     *                  "primary": 0,
     *                  "sms_enabled": 1
     *              }
     *          ]
     *     }
     * @apiParamExample {json} Apartment Option Request:
     *     {
     *          "first_name": "Joe",
     *          "last_name": "Cole",
     *          "middle_name": "",
     *          "type": 2,
     *          "space_id": 1,
     *          "gender": 1,
     *          "birthday": "12-24-1990",
     *          "option": [
     *              "date_admitted": "12-24-2016"
     *          ],
     *          "phones": [
     *              {
     *                  "compatibility": 1,
     *                  "type": 1,
     *                  "number": "+3748880880",
     *                  "primary": 0,
     *                  "sms_enabled": 1
     *              }
     *          ]
     *     }
     * @apiParamExample {json} Region Option Request:
     *     {
     *          "first_name": "Joe",
     *          "last_name": "Cole",
     *          "middle_name": "",
     *          "type": 3,
     *          "space_id": 1,
     *          "gender": 1,
     *          "birthday": "12-24-1990",
     *          "photo": "",
     *          "option": [
     *              "region_id": 1,
     *              "dnr": 1,
     *              "care_level": 1,
     *              "date_admitted": "12-24-2016",
     *              "care_group": 5,
     *              "ambulatory": 1,
     *              "polst": 1,
     *              "csz_id": 1,
     *              "street_address": "Alaverdyan str"
     *          ],
     *          "phones": [
     *              {
     *                  "compatibility": 1,
     *                  "type": 1,
     *                  "number": "+3748880880",
     *                  "primary": 0,
     *                  "sms_enabled": 1
     *              }
     *          ]
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
     * @param ResidentPhotoHelper $residentPhotoHelper
     * @return JsonResponse
     * @throws \Exception
     */
    public function addAction(Request $request, ResidentService $residentService, ResidentPhotoHelper $residentPhotoHelper)
    {
        $residentService->setResidentPhotoHelper($residentPhotoHelper);

        $residentService->add(
            [
                'first_name'    => $request->get('first_name'),
                'last_name'     => $request->get('last_name'),
                'middle_name'   => $request->get('middle_name'),
                'type'          => $request->get('type'),
                'space_id'      => $request->get('space_id'),
                'salutation_id' => $request->get('salutation_id'),
                'birthday'      => $request->get('birthday'),
                'gender'        => $request->get('gender'),
                'photo'         => $request->get('photo'),
                'option'        => $request->get('option'),
                'phones'        => $request->get('phones'),
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
     * @apiParamExample {json} Facility Option Request:
     *     {
     *          "first_name": "Joe",
     *          "last_name": "Cole",
     *          "middle_name": "",
     *          "space_id": 1,
     *          "salutation_id": 1,
     *          "gender": 1,
     *          "birthday": "12-24-1990",
     *          "option": [
     *              "dining_room_id": 1,
     *              "dnr": 1,
     *              "care_level": 1,
     *              "care_group": 5,
     *              "ambulatory": 1,
     *              "polst": 1
     *          ],
     *          "phones": [
     *              {
     *                  "compatibility": 1,
     *                  "type": 1,
     *                  "number": "+3748880880",
     *                  "primary": 0,
     *                  "sms_enabled": 1
     *              }
     *          ]
     *     }
     * @apiParamExample {json} Apartment Option Request:
     *     {
     *          "first_name": "Joe",
     *          "last_name": "Cole",
     *          "middle_name": "",
     *          "space_id": 1,
     *          "gender": 1,
     *          "birthday": "12-24-1990",
     *          "option": [
     *              "date_admitted": "12-24-2016"
     *          ],
     *          "phones": [
     *              {
     *                  "compatibility": 1,
     *                  "type": 1,
     *                  "number": "+3748880880",
     *                  "primary": 0,
     *                  "sms_enabled": 1
     *              }
     *          ]
     *     }
     * @apiParamExample {json} Region Option Request:
     *     {
     *          "first_name": "Joe",
     *          "last_name": "Cole",
     *          "middle_name": "",
     *          "space_id": 1,
     *          "gender": 1,
     *          "birthday": "12-24-1990",
     *          "photo": "",
     *          "option": [
     *              "region_id": 1,
     *              "dnr": 1,
     *              "care_level": 1,
     *              "care_group": 5,
     *              "ambulatory": 1,
     *              "polst": 1,
     *              "csz_id": 1,
     *              "street_address": "Alaverdyan str"
     *          ],
     *          "phones": [
     *              {
     *                  "compatibility": 1,
     *                  "type": 1,
     *                  "number": "+3748880880",
     *                  "primary": 0,
     *                  "sms_enabled": 1
     *              }
     *          ]
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
     * @param ResidentPhotoHelper $residentPhotoHelper
     * @return JsonResponse
     * @throws \Doctrine\DBAL\ConnectionException
     */
    public function editAction(Request $request, $id, ResidentService $residentService, ResidentPhotoHelper $residentPhotoHelper)
    {
        $residentService->setResidentPhotoHelper($residentPhotoHelper);

        $residentService->edit(
            $id,
            [
                'first_name'    => $request->get('first_name'),
                'last_name'     => $request->get('last_name'),
                'middle_name'   => $request->get('middle_name'),
                'type'          => $request->get('type'),
                'space_id'      => $request->get('space_id'),
                'salutation_id' => $request->get('salutation_id'),
                'birthday'      => $request->get('birthday'),
                'gender'        => $request->get('gender'),
                'photo'         => $request->get('photo'),
                'option'        => $request->get('option'),
                'phones'        => $request->get('phones'),
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
     * @param ResidentPhotoHelper $residentPhotoHelper
     * @return JsonResponse
     * @throws \Doctrine\DBAL\ConnectionException
     * @throws \Throwable
     */
    public function deleteAction(Request $request, $id, ResidentService $residentService, ResidentPhotoHelper $residentPhotoHelper)
    {
        $residentService->setResidentPhotoHelper($residentPhotoHelper);
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
     * @param ResidentPhotoHelper $residentPhotoHelper
     * @return JsonResponse
     * @throws \Doctrine\DBAL\ConnectionException
     * @throws \Throwable
     */
    public function deleteBulkAction(Request $request, ResidentService $residentService, ResidentPhotoHelper $residentPhotoHelper)
    {
        $residentService->setResidentPhotoHelper($residentPhotoHelper);
        $residentService->removeBulk($request->get('ids'));

        return $this->respondSuccess(
            Response::HTTP_NO_CONTENT
        );
    }


    /**
     * @api {put} /api/v1.0/admin/resident/{id}/photo Edit Resident Photo
     * @apiVersion 1.0.0
     * @apiName Edit Resident Photo
     * @apiGroup Admin Residents
     * @apiDescription This function is used to edit resident photo
     *
     * @apiHeader {String} Content-Type  application/json
     * @apiHeader {String} Authorization Bearer ACCESS_TOKEN
     *
     * @apiParam {Int}     id   The unique identifier of the resident
     * @apiParam {String}  photo The Base64 URL of the resident photo
     *
     * @apiParamExample {json} Facility Option Request:
     *     {
     *          "photo": "data:image/jpeg;base64,/9j/4AAQSkZJRgAB....",
     *     }
     *
     * @apiSuccessExample {json} Sample Response:
     *     HTTP/1.1 201 Created
     *     {}
     *
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
     * @Route("/{id}/photo", requirements={"id"="\d+"}, name="api_admin_resident_edit_photo", methods={"PUT"})
     *
     * @param Request $request
     * @param $id
     * @param ResidentService $residentService
     * @param ResidentPhotoHelper $residentPhotoHelper
     * @return JsonResponse
     * @throws \Doctrine\DBAL\ConnectionException
     */
    public function photoAction(Request $request, $id, ResidentService $residentService, ResidentPhotoHelper $residentPhotoHelper)
    {
        $residentService->setResidentPhotoHelper($residentPhotoHelper);

        $residentService->photo(
            $id,
            [
                'photo'         => $request->get('photo'),
            ]
        );

        return $this->respondSuccess(
            Response::HTTP_CREATED
        );
    }
}
