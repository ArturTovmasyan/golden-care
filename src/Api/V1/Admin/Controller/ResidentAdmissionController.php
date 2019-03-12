<?php
namespace App\Api\V1\Admin\Controller;

use App\Api\V1\Admin\Service\ResidentAdmissionService;
use App\Api\V1\Common\Controller\BaseController;
use App\Entity\ResidentAdmission;
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
 * @Route("/api/v1.0/admin/resident/admission")
 *
 * @Grant(grant="persistence-resident-admission", level="VIEW")
 *
 * Class AdmissionController
 * @package App\Api\V1\Admin\Controller
 */
class ResidentAdmissionController extends BaseController
{
    /**
     * @api {get} /api/v1.0/admin/resident/admission/grid Get ResidentAdmissions Grid
     * @apiVersion 1.0.0
     * @apiName Get ResidentAdmissions Grid
     * @apiGroup Admin ResidentAdmissions
     * @apiDescription This function is used to listing admissions
     *
     * @apiHeader {String} Content-Type  application/json
     * @apiHeader {String} Authorization Bearer ACCESS_TOKEN
     *
     * @apiSuccess {Int}      id                   The unique identifier of the admission
     * @apiSuccess {Int}      group_type           The group type of the admission
     * @apiSuccess {Int}      admission_type       The admission type of the admission
     * @apiSuccess {String}   date                 The date of the admission
     * @apiSuccess {String}   start                The start date of the admission
     * @apiSuccess {String}   end                  The end date of the admission
     * @apiSuccess {Object}   facility_bed         The facility bed of the admission
     * @apiSuccess {Object}   dining_room          The dining room of the admission
     * @apiSuccess {Boolean}  dnr                  The dnr of the admission
     * @apiSuccess {Boolean}  polst                The polst of the admission
     * @apiSuccess {Boolean}  ambulatory           The ambulatory of the admission
     * @apiSuccess {Int}      care_group           The care group of the admission
     * @apiSuccess {Object}   care_level           The care level of the admission
     * @apiSuccess {Object}   apartment_bed        The apartment bed of the admission
     * @apiSuccess {Object}   region               The region of the admission
     * @apiSuccess {Object}   csz                  The city, state and zip of the admission
     * @apiSuccess {String}   address              The address date of the admission
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
     *                  "group_type": 1,
     *                  "admission_type": 1,
     *                  "date": "2018-12-11T20:18:00+00:00",
     *                  "start": "2018-12-11T20:18:00+00:00",
     *                  "end": null,
     *                  "facility_bed": "A",
     *                  "dining_room": "North Dining Room",
     *                  "dnr": false,
     *                  "polst": false,
     *                  "ambulatory": false,
     *                  "care_group": 1,
     *                  "care_level": "Level 1",
     *                  "apartment_bed": null,
     *                  "region": null,
     *                  "csz": null,
     *                  "address": null
     *              }
     *              }
     *          ]
     *     }
     *
     * @Route("/grid", name="api_admin_resident_admission_grid", methods={"GET"})
     *
     * @param Request $request
     * @param ResidentAdmissionService $residentAdmissionService
     * @return JsonResponse|PdfResponse
     * @throws \ReflectionException
     */
    public function gridAction(Request $request, ResidentAdmissionService $residentAdmissionService)
    {
        return $this->respondGrid(
            $request,
            ResidentAdmission::class,
            'api_admin_resident_admission_grid',
            $residentAdmissionService,
            ['resident_id' => $request->get('resident_id')]
        );
    }

    /**
     * @api {options} /api/v1.0/admin/resident/admission/grid Get ResidentAdmission Grid Options
     * @apiVersion 1.0.0
     * @apiName Get ResidentAdmission Grid Options
     * @apiGroup Admin ResidentAdmissions
     * @apiDescription This function is used to describe options of listing
     *
     * @apiHeader {String} Content-Type  application/json
     * @apiHeader {String} Authorization Bearer ACCESS_TOKEN
     *
     * @apiSuccess {Array} options The options of the admission listing
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
     * @Route("/grid", name="api_admin_resident_admission_grid_options", methods={"OPTIONS"})
     *
     * @param Request $request
     * @return JsonResponse
     * @throws \ReflectionException
     */
    public function gridOptionAction(Request $request)
    {
        return $this->getOptionsByGroupName(ResidentAdmission::class, 'api_admin_resident_admission_grid');
    }

    /**
     * @api {get} /api/v1.0/admin/resident/admission Get ResidentAdmissions
     * @apiVersion 1.0.0
     * @apiName Get ResidentAdmissions
     * @apiGroup Admin ResidentAdmissions
     * @apiDescription This function is used to listing admissions
     *
     * @apiHeader {String} Content-Type  application/json
     * @apiHeader {String} Authorization Bearer ACCESS_TOKEN
     *
     * @apiSuccess {Int}      id                   The unique identifier of the admission
     * @apiSuccess {Object}   resident             The resident of the admission
     * @apiSuccess {Int}      group_type           The group type of the admission
     * @apiSuccess {Int}      admission_type       The admission type of the admission
     * @apiSuccess {String}   date                 The date of the admission
     * @apiSuccess {String}   start                The start date of the admission
     * @apiSuccess {String}   end                  The end date of the admission
     * @apiSuccess {Object}   facility_bed         The facility bed of the admission
     * @apiSuccess {Object}   dining_room          The dining room of the admission
     * @apiSuccess {Boolean}  dnr                  The dnr of the admission
     * @apiSuccess {Boolean}  polst                The polst of the admission
     * @apiSuccess {Boolean}  ambulatory           The ambulatory of the admission
     * @apiSuccess {Int}      care_group           The care group of the admission
     * @apiSuccess {Object}   care_level           The care level of the admission
     * @apiSuccess {Object}   apartment_bed        The apartment bed of the admission
     * @apiSuccess {Object}   region               The region of the admission
     * @apiSuccess {Object}   csz                  The city, state and zip of the admission
     * @apiSuccess {String}   address              The address date of the admission
     *
     * @apiSuccessExample {json} Sample Response:
     *     HTTP/1.1 200 OK
     *     {
     *          "page": "1",
     *          "per_page": 10,
     *          "all_pages": 1,
     *          "total": 5,
     *          "data": {
     *                  "id": 1,
     *                  "resident": {
     *                      "id": 1
     *                  },
     *                  "group_type": 1,
     *                  "admission_type": 1,
     *                  "date": "2018-12-11T20:18:00+00:00",
     *                  "start": "2018-12-11T20:18:00+00:00",
     *                  "end": null,
     *                  "facility_bed": {
     *                      "id": 1,
     *                      "number": "A"
     *                   },
     *                  "dining_room": {
     *                      "id": 1,
     *                      "title": "North Dining Room"
     *                  },
     *                  "dnr": false,
     *                  "polst": false,
     *                  "ambulatory": false,
     *                  "care_group": 1,
     *                  "care_level": {
     *                      "id": 1,
     *                      "title": "Level 1"
     *                  },
     *                  "apartment_bed": null,
     *                  "region": null,
     *                  "csz": null,
     *                  "address": null
     *              }
     *          ]
     *     }
     *
     * @Route("", name="api_admin_resident_admission_list", methods={"GET"})
     *
     * @param Request $request
     * @param ResidentAdmissionService $residentAdmissionService
     * @return JsonResponse|PdfResponse
     * @throws \Exception
     */
    public function listAction(Request $request, ResidentAdmissionService $residentAdmissionService)
    {
        return $this->respondList(
            $request,
            ResidentAdmission::class,
            'api_admin_resident_admission_list',
            $residentAdmissionService,
            ['resident_id' => $request->get('resident_id')]
        );
    }

    /**
     * @api {get} /api/v1.0/admin/resident/admission/{id} Get ResidentAdmission
     * @apiVersion 1.0.0
     * @apiName Get ResidentAdmission
     * @apiGroup Admin ResidentAdmissions
     * @apiDescription This function is used to get admission
     *
     * @apiHeader {String} Content-Type  application/json
     * @apiHeader {String} Authorization Bearer ACCESS_TOKEN
     *
     * @apiSuccess {Int}      id                   The unique identifier of the admission
     * @apiSuccess {Object}   resident             The resident of the admission
     * @apiSuccess {Int}      group_type           The group type of the admission
     * @apiSuccess {Int}      admission_type       The admission type of the admission
     * @apiSuccess {String}   date                 The date of the admission
     * @apiSuccess {String}   start                The start date of the admission
     * @apiSuccess {String}   end                  The end date of the admission
     * @apiSuccess {Object}   facility_bed         The facility bed of the admission
     * @apiSuccess {Object}   dining_room          The dining room of the admission
     * @apiSuccess {Boolean}  dnr                  The dnr of the admission
     * @apiSuccess {Boolean}  polst                The polst of the admission
     * @apiSuccess {Boolean}  ambulatory           The ambulatory of the admission
     * @apiSuccess {Int}      care_group           The care group of the admission
     * @apiSuccess {Object}   care_level           The care level of the admission
     * @apiSuccess {Object}   apartment_bed        The apartment bed of the admission
     * @apiSuccess {Object}   region               The region of the admission
     * @apiSuccess {Object}   csz                  The city, state and zip of the admission
     * @apiSuccess {String}   address              The address date of the admission
     *
     * @apiSuccessExample {json} Sample Response:
     *     HTTP/1.1 200 OK
     *     {
     *          "data": {
     *                  "id": 1,
     *                  "resident": {
     *                      "id": 1
     *                  },
     *                  "group_type": 1,
     *                  "admission_type": 1,
     *                  "date": "2018-12-11T20:18:00+00:00",
     *                  "start": "2018-12-11T20:18:00+00:00",
     *                  "end": null,
     *                  "facility_bed": {
     *                      "id": 1,
     *                      "number": "A"
     *                   },
     *                  "dining_room": {
     *                      "id": 1,
     *                      "title": "North Dining Room"
     *                  },
     *                  "dnr": false,
     *                  "polst": false,
     *                  "ambulatory": false,
     *                  "care_group": 1,
     *                  "care_level": {
     *                      "id": 1,
     *                      "title": "Level 1"
     *                  },
     *                  "apartment_bed": null,
     *                  "region": null,
     *                  "csz": null,
     *                  "address": null
     *          }
     *     }
     *
     * @Route("/{id}", requirements={"id"="\d+"}, name="api_admin_resident_admission_get", methods={"GET"})
     *
     * @param ResidentAdmissionService $residentAdmissionService
     * @param $id
     * @return JsonResponse
     */
    public function getAction(Request $request, $id, ResidentAdmissionService $residentAdmissionService)
    {
        return $this->respondSuccess(
            Response::HTTP_OK,
            '',
            $residentAdmissionService->getById($id),
            ['api_admin_resident_admission_get']
        );
    }

    /**
     * @api {post} /api/v1.0/admin/resident/admission Add ResidentAdmission
     * @apiVersion 1.0.0
     * @apiName Add ResidentAdmission
     * @apiGroup Admin ResidentAdmissions
     * @apiDescription This function is used to add admission
     *
     * @apiHeader {String} Content-Type  application/x-www-form-urlencoded
     * @apiHeader {String} Authorization Bearer ACCESS_TOKEN
     *
     * @apiParam {Int}     resident_id         The unique identifier of the resident
     * @apiParam {Int}     group_type          The group type of the admission
     * @apiParam {Int}     admission_type      The admission type of the admission
     * @apiParam {String}  date                The date of the admission
     * @apiParam {Int}     facility_bed_id     The unique identifier of the facility bed
     * @apiParam {Int}     apartment_bed_id    The unique identifier of the apartment bed
     * @apiParam {Int}     region_id           The unique identifier of the region
     * @apiParam {Int}     csz_id              The unique identifier of the city, state and zip
     * @apiParam {String}  address             The address of the admission
     * @apiParam {Int}     dining_room_id      The unique identifier of the dining room
     * @apiParam {Int}     dnr                 The dnr of the admission
     * @apiParam {Int}     polst               The polst of the admission
     * @apiParam {Int}     ambulatory          The ambulatory of the admission
     * @apiParam {Int}     care_group          The care group of the admission
     * @apiParam {Int}     care_level_id       The unique identifier of the care level
     * @apiParam {String}  [notes]             The notes of the admission
     *
     * @apiParamExample {json} Facility Request:
     *     {
     *          "resident_id": 1,
     *          "group_type": 1,
     *          "admission_type": 1,
     *          "date": "2016-10-01",
     *          "dining_room_id": 1,
     *          "facility_bed_id": 1,
     *          "dnr": 1,
     *          "polst": 1
     *          "ambulatory": 1,
     *          "care_group": 5,
     *          "care_level_id": 1,
     *          "notes": "some notes"
     *     }
     * @apiParamExample {json} Apartment Request:
     *     {
     *          "resident_id": 1,
     *          "group_type": 1,
     *          "admission_type": 1,
     *          "date": "2016-10-01",
     *          "apartment_bed_id": 1,
     *          "notes": "some notes"
     *     }
     * @apiParamExample {json} Region Request:
     *     {
     *          "resident_id": 1,
     *          "group_type": 1,
     *          "admission_type": 1,
     *          "date": "2016-10-01",
     *          "region_id": 1,
     *          "csz_id": 1,
     *          "street_address": "7952 Old Auburn Road"
     *          "dnr": 1,
     *          "polst": 1,
     *          "ambulatory": 1,
     *          "care_group": 5,
     *          "care_level": 1,
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
     *              "group_type": "Sorry, this value not be blank."
     *          }
     *     }
     *
     * @Route("", name="api_admin_resident_admission_add", methods={"POST"})
     *
     * @Grant(grant="persistence-resident-admission", level="ADD")
     *
     * @param Request $request
     * @param ResidentAdmissionService $residentAdmissionService
     * @return JsonResponse
     * @throws \Exception
     */
    public function addAction(Request $request, ResidentAdmissionService $residentAdmissionService)
    {
        $id = $residentAdmissionService->add(
            [
                'resident_id' => $request->get('resident_id'),
                'group_type' => $request->get('group_type'),
                'admission_type' => $request->get('admission_type'),
                'date' => $request->get('date'),
                'facility_bed_id' => $request->get('facility_bed_id'),
                'apartment_bed_id' => $request->get('apartment_bed_id'),
                'region_id' => $request->get('region_id'),
                'csz_id' => $request->get('csz_id'),
                'address' => $request->get('address'),
                'dining_room_id' => $request->get('dining_room_id'),
                'dnr' => $request->get('dnr'),
                'polst' => $request->get('polst'),
                'ambulatory' => $request->get('ambulatory'),
                'care_group' => $request->get('care_group'),
                'care_level_id' => $request->get('care_level_id'),
                'notes' => $request->get('notes') ?? ''

            ]
        );

        return $this->respondSuccess(
            Response::HTTP_CREATED,
            '',
            [$id]
        );
    }

    /**
     * @api {put} /api/v1.0/admin/resident/admission/{id} Edit ResidentAdmission
     * @apiVersion 1.0.0
     * @apiName Edit ResidentAdmission
     * @apiGroup Admin ResidentAdmissions
     * @apiDescription This function is used to edit admission
     *
     * @apiHeader {String} Content-Type  application/x-www-form-urlencoded
     * @apiHeader {String} Authorization Bearer ACCESS_TOKEN
     *
     * @apiParam {Int}     resident_id         The unique identifier of the resident
     * @apiParam {Int}     admission_type      The admission type of the admission
     * @apiParam {String}  date                The date of the admission
     * @apiParam {Int}     facility_bed_id     The unique identifier of the facility bed
     * @apiParam {Int}     apartment_bed_id    The unique identifier of the apartment bed
     * @apiParam {Int}     region_id           The unique identifier of the region
     * @apiParam {Int}     csz_id              The unique identifier of the city, state and zip
     * @apiParam {String}  address             The address of the admission
     * @apiParam {Int}     dining_room_id      The unique identifier of the dining room
     * @apiParam {Int}     dnr                 The dnr of the admission
     * @apiParam {Int}     polst               The polst of the admission
     * @apiParam {Int}     ambulatory          The ambulatory of the admission
     * @apiParam {Int}     care_group          The care group of the admission
     * @apiParam {Int}     care_level_id       The unique identifier of the care level
     * @apiParam {String}  [notes]             The notes of the admission
     *
     * @apiParamExample {json} Facility Request:
     *     {
     *          "resident_id": 1,
     *          "admission_type": 1,
     *          "date": "2016-10-01",
     *          "dining_room_id": 1,
     *          "facility_bed_id": 1,
     *          "dnr": 1,
     *          "polst": 1
     *          "ambulatory": 1,
     *          "care_group": 5,
     *          "care_level_id": 1,
     *          "notes": "some notes"
     *     }
     * @apiParamExample {json} Apartment Request:
     *     {
     *          "resident_id": 1,
     *          "admission_type": 1,
     *          "date": "2016-10-01",
     *          "apartment_bed_id": 1,
     *          "notes": "some notes"
     *     }
     * @apiParamExample {json} Region Request:
     *     {
     *          "resident_id": 1,
     *          "admission_type": 1,
     *          "date": "2016-10-01",
     *          "region_id": 1,
     *          "csz_id": 1,
     *          "street_address": "7952 Old Auburn Road"
     *          "dnr": 1,
     *          "polst": 1,
     *          "ambulatory": 1,
     *          "care_group": 5,
     *          "care_level": 1,
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
     *              "admission_type": "Sorry, this value not be blank."
     *          }
     *     }
     *
     * @Route("/{id}", requirements={"id"="\d+"}, name="api_admin_resident_admission_edit", methods={"PUT"})
     *
     * @Grant(grant="persistence-resident-admission", level="EDIT")
     *
     * @param Request $request
     * @param $id
     * @param ResidentAdmissionService $residentAdmissionService
     * @return JsonResponse
     * @throws \Exception
     */
    public function editAction(Request $request, $id, ResidentAdmissionService $residentAdmissionService)
    {
        $residentAdmissionService->edit(
            $id,
            [
                'resident_id' => $request->get('resident_id'),
                'admission_type' => $request->get('admission_type'),
                'date' => $request->get('date'),
                'facility_bed_id' => $request->get('facility_bed_id'),
                'apartment_bed_id' => $request->get('apartment_bed_id'),
                'region_id' => $request->get('region_id'),
                'csz_id' => $request->get('csz_id'),
                'address' => $request->get('address'),
                'dining_room_id' => $request->get('dining_room_id'),
                'dnr' => $request->get('dnr'),
                'polst' => $request->get('polst'),
                'ambulatory' => $request->get('ambulatory'),
                'care_group' => $request->get('care_group'),
                'care_level_id' => $request->get('care_level_id'),
                'notes' => $request->get('notes') ?? ''

            ]
        );

        return $this->respondSuccess(
            Response::HTTP_CREATED
        );
    }

    /**
     * @api {put} /api/v1.0/admin/resident/admission/{id}/move Move Resident
     * @apiVersion 1.0.0
     * @apiName Move Resident
     * @apiGroup Admin ResidentAdmissions
     * @apiDescription This function is used to move resident
     *
     * @apiHeader {String} Content-Type  application/json
     * @apiHeader {String} Authorization Bearer ACCESS_TOKEN
     *
     * @apiParam {Int}     group_type  Strategy type of the resident
     * @apiParam {Int}     move_id     The unique identifier where the resident to move
     *
     * @apiParamExample {json} Sample Response:
     *     {
     *          "group_type": "1",
     *          "move_id": "1"
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
     *              "type": "Sorry, this type is already in use."
     *          }
     *     }
     *
     * @Route("/{id}/move", requirements={"id"="\d+"}, name="api_admin_resident_admission_move", methods={"PUT"})
     *
     * @Grant(grant="persistence-resident-admission", level="EDIT")
     *
     * @param Request $request
     * @param $id
     * @param ResidentAdmissionService $residentAdmissionService
     * @return JsonResponse
     * @throws \Doctrine\DBAL\ConnectionException
     */
    public function moveAction(Request $request, $id, ResidentAdmissionService $residentAdmissionService)
    {
        $residentAdmissionService->move(
            $id,
            [
                'group_type'    => $request->get('group_type'),
                'move_id' => $request->get('move_id')
            ]
        );

        return $this->respondSuccess(
            Response::HTTP_OK
        );
    }

    /**
     * @api {delete} /api/v1.0/admin/resident/admission/{id} Delete ResidentAdmission
     * @apiVersion 1.0.0
     * @apiName Delete ResidentAdmission
     * @apiGroup Admin ResidentAdmissions
     * @apiDescription This function is used to remove admission
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
     *          "code": 704,
     *          "error": "ResidentAdmission not found"
     *     }
     *
     * @Route("/{id}", requirements={"id"="\d+"}, name="api_admin_resident_admission_delete", methods={"DELETE"})
     *
     * @Grant(grant="persistence-resident-admission", level="DELETE")
     *
     * @param $id
     * @param ResidentAdmissionService $residentAdmissionService
     * @return JsonResponse
     * @throws \Doctrine\DBAL\ConnectionException
     * @throws \Throwable
     */
    public function deleteAction(Request $request, $id, ResidentAdmissionService $residentAdmissionService)
    {
        $residentAdmissionService->remove($id);

        return $this->respondSuccess(
            Response::HTTP_NO_CONTENT
        );
    }

    /**
     * @api {delete} /api/v1.0/admin/resident/admission Bulk Delete ResidentAdmissions
     * @apiVersion 1.0.0
     * @apiName Bulk Delete ResidentAdmissions
     * @apiGroup Admin ResidentAdmissions
     * @apiDescription This function is used to bulk remove admissions
     *
     * @apiHeader {String} Content-Type  application/x-www-form-urlencoded
     * @apiHeader {String} Authorization Bearer ACCESS_TOKEN
     *
     * @apiParam {Int[]} ids The unique identifier of the admissions
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
     *          "code": 704,
     *          "error": "ResidentAdmission not found"
     *     }
     *
     * @Route("", name="api_admin_resident_admission_delete_bulk", methods={"DELETE"})
     *
     * @Grant(grant="persistence-resident-admission", level="DELETE")
     *
     * @param Request $request
     * @param ResidentAdmissionService $residentAdmissionService
     * @return JsonResponse
     * @throws \Doctrine\DBAL\ConnectionException
     * @throws \Throwable
     */
    public function deleteBulkAction(Request $request, ResidentAdmissionService $residentAdmissionService)
    {
        $residentAdmissionService->removeBulk($request->get('ids'));

        return $this->respondSuccess(
            Response::HTTP_NO_CONTENT
        );
    }

    /**
     * @api {get} /api/v1.0/admin/resident/admission/{id}/active Get Active Admission
     * @apiVersion 1.0.0
     * @apiName Get Active Resident
     * @apiGroup Admin ResidentAdmissions
     * @apiDescription This function is used to get active resident
     *
     * @apiHeader {String} Content-Type  application/json
     * @apiHeader {String} Authorization Bearer ACCESS_TOKEN
     *
     * @apiSuccess {Int}      id                   The unique identifier of the admission
     * @apiSuccess {Object}   resident             The resident of the admission
     * @apiSuccess {Int}      group_type           The group type of the admission
     * @apiSuccess {Int}      admission_type       The admission type of the admission
     * @apiSuccess {String}   date                 The date of the admission
     * @apiSuccess {String}   start                The start date of the admission
     * @apiSuccess {String}   end                  The end date of the admission
     * @apiSuccess {Object}   facility_bed         The facility bed of the admission
     * @apiSuccess {Object}   dining_room          The dining room of the admission
     * @apiSuccess {Boolean}  dnr                  The dnr of the admission
     * @apiSuccess {Boolean}  polst                The polst of the admission
     * @apiSuccess {Boolean}  ambulatory           The ambulatory of the admission
     * @apiSuccess {Int}      care_group           The care group of the admission
     * @apiSuccess {Object}   care_level           The care level of the admission
     * @apiSuccess {Object}   apartment_bed        The apartment bed of the admission
     * @apiSuccess {Object}   region               The region of the admission
     * @apiSuccess {Object}   csz                  The city, state and zip of the admission
     * @apiSuccess {String}   address              The address date of the admission
     *
     * @apiSuccessExample {json} Sample Response:
     *     HTTP/1.1 200 OK
     *     {
     *          "data": {
     *                  "id": 1,
     *                  "resident": {
     *                      "id": 1
     *                  },
     *                  "group_type": 1,
     *                  "admission_type": 1,
     *                  "date": "2018-12-11T20:18:00+00:00",
     *                  "start": "2018-12-11T20:18:00+00:00",
     *                  "end": null,
     *                  "facility_bed": {
     *                      "id": 1,
     *                      "number": "A"
     *                   },
     *                  "dining_room": {
     *                      "id": 1,
     *                      "title": "North Dining Room"
     *                  },
     *                  "dnr": false,
     *                  "polst": false,
     *                  "ambulatory": false,
     *                  "care_group": 1,
     *                  "care_level": {
     *                      "id": 1,
     *                      "title": "Level 1"
     *                  },
     *                  "apartment_bed": null,
     *                  "region": null,
     *                  "csz": null,
     *                  "address": null
     *          }
     *     }
     *
     * @Route("/{id}/active", requirements={"id"="\d+"}, name="api_admin_resident_admission_get_active_resident", methods={"GET"})
     *
     * @param ResidentAdmissionService $residentAdmissionService
     * @param $id
     * @return JsonResponse
     */
    public function getActiveResidentAction(Request $request, $id, ResidentAdmissionService $residentAdmissionService)
    {
        return $this->respondSuccess(
            Response::HTTP_OK,
            '',
            $residentAdmissionService->getActiveByResidentId($id),
            ['api_admin_resident_admission_get_active']
        );
    }

    /**
     * @api {get} /api/v1.0/admin/resident/admission/active/{type}/{id} Get Active Residents
     * @apiVersion 1.0.0
     * @apiName Get Active Residents
     * @apiGroup Admin ResidentAdmissions
     * @apiDescription This function is used to get active Residents
     *
     * @apiHeader {String} Content-Type  application/json
     * @apiHeader {String} Authorization Bearer ACCESS_TOKEN
     *
     * @apiSuccess {Int}      id                   The unique identifier of the resident
     * @apiSuccess {String}   first_name           The firstName of the resident
     * @apiSuccess {String}   last_name            The lastName of the resident
     * @apiSuccess {String}   salutation           The salutation of the resident
     * @apiSuccess {String}   room_number          The room number of the resident
     * @apiSuccess {String}   bed_number           The bed number of the resident
     *
     * @apiSuccessExample {json} Sample Response:
     *     HTTP/1.1 200 OK
     *     {
     *          "data": {
     *                   "id": 4,
     *                   "first_name": FirstName,
     *                   "last_name": LastName,
     *                   "salutation": Mr.,
     *                   "room_number": 106,
     *                   "bed_number": C,
     *          }
     *     }
     *
     * @Route("/active/{type}/{id}", requirements={"type"="\d+", "id"="\d+"}, name="api_admin_resident_admission_get_active_residents", methods={"GET"})
     *
     * @param ResidentAdmissionService $residentAdmissionService
     * @param $type
     * @param $id
     * @return JsonResponse
     */
    public function getActiveResidentsAction(Request $request, $type, $id, ResidentAdmissionService $residentAdmissionService)
    {
        return $this->respondSuccess(
            Response::HTTP_OK,
            '',
            $residentAdmissionService->getActiveResidentsByStrategy($type, $id),
            ['api_admin_resident_get_active']
        );
    }

    /**
     * @api {get} /api/v1.0/admin/resident/admission/inactive/{type}/{id} Get Inactive Residents
     * @apiVersion 1.0.0
     * @apiName Get Inactive Residents
     * @apiGroup Admin ResidentAdmissions
     * @apiDescription This function is used to get inactive Residents
     *
     * @apiHeader {String} Content-Type  application/json
     * @apiHeader {String} Authorization Bearer ACCESS_TOKEN
     *
     * @apiSuccess {Int}      id                   The unique identifier of the resident
     * @apiSuccess {String}   first_name           The firstName of the resident
     * @apiSuccess {String}   last_name            The lastName of the resident
     * @apiSuccess {String}   salutation           The salutation of the resident
     * @apiSuccess {String}   room_number          The room number of the resident
     * @apiSuccess {String}   bed_number           The bed number of the resident
     *
     * @apiSuccessExample {json} Sample Response:
     *     HTTP/1.1 200 OK
     *     {
     *          "data": {
     *                   "id": 4,
     *                   "first_name": FirstName,
     *                   "last_name": LastName,
     *                   "salutation": Mr.,
     *                   "room_number": 106,
     *                   "bed_number": C,
     *          }
     *     }
     *
     * @Route("/inactive/{type}/{id}", requirements={"type"="\d+", "id"="\d+"}, name="api_admin_resident_admission_get_inactive_residents", methods={"GET"})
     *
     * @param ResidentAdmissionService $residentAdmissionService
     * @param $type
     * @param $id
     * @return JsonResponse
     */
    public function getInactiveResidentsAction(Request $request, $type, $id, ResidentAdmissionService $residentAdmissionService)
    {
        return $this->respondSuccess(
            Response::HTTP_OK,
            '',
            $residentAdmissionService->getInactiveResidentsByStrategy($type, $id),
            ['api_admin_resident_get_inactive']
        );
    }

    /**
     * @api {get} /api/v1.0/admin/resident/admission/no-admission Get Residents No Admission
     * @apiVersion 1.0.0
     * @apiName Get Residents No Admission
     * @apiGroup Admin ResidentAdmissions
     * @apiDescription This function is used to get Residents without admission
     *
     * @apiHeader {String} Content-Type  application/json
     * @apiHeader {String} Authorization Bearer ACCESS_TOKEN
     *
     * @apiSuccess {Int}      id                   The unique identifier of the resident
     * @apiSuccess {String}   first_name           The firstName of the resident
     * @apiSuccess {String}   last_name            The lastName of the resident
     * @apiSuccess {String}   salutation           The salutation of the resident
     *
     * @apiSuccessExample {json} Sample Response:
     *     HTTP/1.1 200 OK
     *     {
     *          "data": {
     *                   "id": 4,
     *                   "first_name": FirstName,
     *                   "last_name": LastName,
     *                   "salutation": Mr.
     *          }
     *     }
     *
     * @Route("/no-admission",  name="api_admin_resident_admission_get_no_admission_resident", methods={"GET"})
     *
     * @param ResidentAdmissionService $residentAdmissionService
     * @return JsonResponse
     */
    public function getNoAdmissionResidentsAction(Request $request, ResidentAdmissionService $residentAdmissionService)
    {
        return $this->respondSuccess(
            Response::HTTP_OK,
            '',
            $residentAdmissionService->getNoAdmissionResidents(),
            ['api_admin_resident_get_no_admission']
        );
    }
}
