<?php

namespace App\Api\V1\Admin\Controller;

use App\Api\V1\Common\Controller\BaseController;
use App\Api\V1\Admin\Service\PhysicianService;
use App\Entity\Physician;
use Knp\Bundle\SnappyBundle\Snappy\Response\PdfResponse;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
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
 * @Route("/api/v1.0/admin/physician")
 *
 * @Grant(grant="persistence-common-physician", level="VIEW")
 *
 * Class PhysicianController
 * @package App\Api\V1\Admin\Controller
 */
class PhysicianController extends BaseController
{
    /**
     * @api {get} /api/v1.0/admin/physician/grid Get Physicians Grid
     * @apiVersion 1.0.0
     * @apiName Get Physicians Grid
     * @apiGroup Admin Physicians
     * @apiPermission none
     * @apiDescription This function is used to get user all physicians grid for admin
     *
     * @apiHeader {String} Content-Type  application/json
     * @apiHeader {String} Authorization Bearer ACCESS_TOKEN
     *
     * @apiSuccess {Int}     id                 The identifier of the physician
     * @apiSuccess {String}  speciality         The speciality title of the physician
     * @apiSuccess {String}  salutation         The salutation title of the physician
     * @apiSuccess {String}  first_name         The First Name of the physician
     * @apiSuccess {String}  middle_name        The Middle Name of the physician
     * @apiSuccess {String}  last_name          The Last Name of the physician
     * @apiSuccess {String}  address_1          The main address of the physician
     * @apiSuccess {String}  address_2          The secondary address of the physician
     * @apiSuccess {String}  office_phone       The office phone number of the physician
     * @apiSuccess {String}  fax                The fax number of the physician
     * @apiSuccess {String}  emergency_phone    The emergency phone number of the physician
     * @apiSuccess {String}  email              The email address of the physician
     * @apiSuccess {String}  website_url        The website url of the physician
     * @apiSuccess {Object}  space              The space of the physician
     * @apiSuccess {Object}  csz                The cityStateZip of the physician
     *
     * @apiSuccessExample {json} Sample Response:
     *     HTTP/1.1 200 OK
     *     {
     *          "page": "1",
     *          "per_page": "10",
     *          "total": 2,
     *          "data": [
     *              {
     *                  "id": 1,
     *                  "speciality": "Doctor",
     *                  "salutation": "Mr.",
     *                  "first_name": "Arthur",
     *                  "middle_name": "Gagik",
     *                  "last_name": "Jovhannesyan",
     *                  "address_1": "Fuchik str 2",
     *                  "address_2": "Alaverdyan str 25 ap. 2",
     *                  "office_phone": "+374544554545",
     *                  "fax": "+37410555565",
     *                  "emergency_phone": "+37455888080",
     *                  "email": "test@example.com",
     *                  "website_url": "http://example.com",
     *                  "space": "alms",
     *                  "csz_str": "Verdi CA, 89439"
     *              }
     *          ]
     *     }
     *
     * @Route("/grid", name="api_admin_physician_grid", methods={"GET"})
     *
     * @param Request $request
     * @param PhysicianService $physicianService
     * @return PdfResponse|JsonResponse
     * @throws \ReflectionException
     */
    public function gridAction(Request $request, PhysicianService $physicianService)
    {
        return $this->respondGrid(
            $request,
            Physician::class,
            'api_admin_physician_grid',
            $physicianService
        );
    }

    /**
     * @api {options} /api/v1.0/admin/physician/grid Get Physicians Grid Options
     * @apiVersion 1.0.0
     * @apiName Get Physicians Grid Options
     * @apiGroup Admin Physicians
     * @apiPermission none
     * @apiDescription This function is used to describe options of listing
     *
     * @apiHeader {String} Content-Type  application/json
     * @apiHeader {String} Authorization Bearer ACCESS_TOKEN
     *
     * @apiSuccess {Array} options The options of thr role listing
     *
     * @apiSuccessExample {json} Sample Response:
     *     HTTP/1.1 200 OK
     *     {
     *          [
     *              {
     *                  "label": "id",
     *                  "type": "integer",
     *                  "sortable": true,
     *                  "filterable": true,
     *              }
     *          ]
     *     }
     *
     * @Route("/grid", name="api_admin_physician_grid_options", methods={"OPTIONS"})
     *
     * @param Request $request
     * @return \Knp\Bundle\SnappyBundle\Snappy\Response\PdfResponse|JsonResponse
     * @throws \ReflectionException
     */
    public function gridOptionAction(Request $request)
    {
        return $this->getOptionsByGroupName(Physician::class, 'api_admin_physician_grid');
    }

    /**
     * @api {get} /api/v1.0/admin/physician Get Physicians
     * @apiVersion 1.0.0
     * @apiName Get Physicians
     * @apiGroup Admin Physicians
     * @apiPermission none
     * @apiDescription This function is used to listing physicians
     *
     * @apiHeader {String} Content-Type  application/json
     * @apiHeader {String} Authorization Bearer ACCESS_TOKEN
     *
     * @apiParam {Int} space_id  The unique identifier of the space
     *
     * @apiSuccess {Int}     id                 The identifier of the physician
     * @apiSuccess {String}  first_name         The First Name of the physician
     * @apiSuccess {String}  middle_name        The Middle Name of the physician
     * @apiSuccess {String}  last_name          The Last Name of the physician
     * @apiSuccess {String}  address_1          The main address of the physician
     * @apiSuccess {String}  address_2          The secondary address of the physician
     * @apiSuccess {String}  office_phone       The office phone number of the physician
     * @apiSuccess {String}  fax                The fax number of the physician
     * @apiSuccess {String}  emergency_phone    The emergency phone number of the physician
     * @apiSuccess {String}  email              The email address of the physician
     * @apiSuccess {String}  website_url        The website url of the physician
     * @apiSuccess {Object}  space              The related space of the physician
     * @apiSuccess {Object}  speciality         The related speciality of the physician
     * @apiSuccess {Object}  salutation         The related salutation of the physician
     * @apiSuccess {Object}  csz                The related cityStateZip of the physician
     *
     * @apiSuccessExample {json} Sample Response:
     *     HTTP/1.1 200 OK
     *     {
     *         {
     *             "id": 1,
     *             "first_name": "Arthur",
     *             "middle_name": "Gagik",
     *             "last_name": "Jovhannesyan",
     *             "address_1": "Fuchik str 2",
     *             "address_2": "Alaverdyan str 25 ap. 2",
     *             "office_phone": "+374544554545",
     *             "fax": "+37410555565",
     *             "emergency_phone": "+37455888080",
     *             "email": "test@example.com",
     *             "website_url": "http://example.com",
     *             "space": {
     *                 "id": 1,
     *                 "name": "Space N1"
     *             },
     *             "speciality": {
     *                 "id": 1,
     *                 "name": "Doctor"
     *             },
     *             "salutation": {
     *                 "id": 1,
     *                 "name": "Mr."
     *             },
     *             "speciality": {
     *                 "id": 1,
     *                 "name": "Doctor"
     *             },
     *             "csz": {
     *                 "id": 1,
     *                 "state_abbr": "CA",
     *                 "zip_main": "89439",
     *                 "zip_sub": "",
     *                 "city": "Verdi"
     *             }
     *         }
     *     }
     *
     * @Route("", name="api_admin_physician_list", methods={"GET"})
     *
     * @param Request $request
     * @param PhysicianService $physicianService
     * @return JsonResponse|PdfResponse
     * @throws \ReflectionException
     */
    public function listAction(Request $request, PhysicianService $physicianService)
    {
        return $this->respondList(
            $request,
            Physician::class,
            'api_admin_physician_list',
            $physicianService
        );
    }

    /**
     * @api {get} /api/v1.0/admin/physician/{id} Get Physician
     * @apiVersion 1.0.0
     * @apiName Get Physician
     * @apiGroup Admin Physicians
     * @apiPermission none
     * @apiDescription This function is used to get physician by id
     *
     * @apiHeader {String} Content-Type  application/json
     * @apiHeader {String} Authorization Bearer ACCESS_TOKEN
     *
     * @apiParam {Int} id       The unique identifier of the physician
     *
     * @apiSuccess {Int}     id                 The identifier of the physician
     * @apiSuccess {String}  first_name         The First Name of the physician
     * @apiSuccess {String}  middle_name        The Middle Name of the physician
     * @apiSuccess {String}  last_name          The Last Name of the physician
     * @apiSuccess {String}  address_1          The main address of the physician
     * @apiSuccess {String}  address_2          The secondary address of the physician
     * @apiSuccess {String}  office_phone       The office phone number of the physician
     * @apiSuccess {String}  fax                The fax number of the physician
     * @apiSuccess {String}  emergency_phone    The emergency phone number of the physician
     * @apiSuccess {String}  email              The email address of the physician
     * @apiSuccess {String}  website_url        The website url of the physician
     * @apiSuccess {Object}  space              The related space of the physician
     * @apiSuccess {Object}  speciality         The related speciality of the physician
     * @apiSuccess {Object}  salutation         The related salutation of the physician
     * @apiSuccess {Object}  csz                The related cityStateZip of the physician
     *
     * @apiSuccessExample {json} Sample Response:
     *     HTTP/1.1 200 OK
     *     {
     *             "id": 1,
     *             "first_name": "Arthur",
     *             "middle_name": "Gagik",
     *             "last_name": "Jovhannesyan",
     *             "address_1": "Fuchik str 2",
     *             "address_2": "Alaverdyan str 25 ap. 2",
     *             "office_phone": "+374544554545",
     *             "fax": "+37410555565",
     *             "emergency_phone": "+37455888080",
     *             "email": "test@example.com",
     *             "website_url": "http://example.com",
     *             "space": {
     *                 "id": 1,
     *                 "name": "Space N1"
     *             },
     *             "speciality": {
     *                 "id": 1,
     *                 "name": "Doctor"
     *             },
     *             "salutation": {
     *                 "id": 1,
     *                 "name": "Mr."
     *             },
     *             "csz": {
     *                 "id": 1,
     *                 "state_abbr": "CA",
     *                 "zip_main": "89439",
     *                 "zip_sub": "",
     *                 "city": "Verdi"
     *             }
     *     }
     *
     * @Route("/{id}", name="api_admin_physician_get", requirements={"id"="\d+"}, methods={"GET"})
     *
     * @param Request $request
     * @param $id
     * @param PhysicianService $physicianService
     * @return JsonResponse
     */
    public function getAction(Request $request, $id, PhysicianService $physicianService)
    {
        return $this->respondSuccess(
            Response::HTTP_OK,
            '',
            $physicianService->getById($id),
            ['api_admin_physician_get']
        );
    }

    /**
     * @api {post} /api/v1.0/admin/physician Add Physician
     * @apiVersion 1.0.0
     * @apiName Add Physician
     * @apiGroup Admin Physicians
     * @apiPermission none
     * @apiDescription This function is used to add physician
     *
     * @apiHeader {String} Content-Type  application/json
     * @apiHeader {String} Authorization Bearer ACCESS_TOKEN
     *
     * @apiParam {String}  first_name           The First Name of the physician
     * @apiParam {String}  [middle_name]        The Middle Name of the physician
     * @apiParam {String}  last_name            The Last Name of the physician
     * @apiParam {String}  address_1            The main address of the physician
     * @apiParam {String}  [address_2]          The secondary address of the physician
     * @apiParam {String}  office_phone         The office phone number of the physician
     * @apiParam {String}  [fax]                The fax number of the physician
     * @apiParam {String}  [emergency_phone]    The emergency phone number of the physician
     * @apiParam {String}  [email]              The email address of the physician
     * @apiParam {String}  [website_url]        The website url of the physician
     * @apiParam {Integer} csz_id               The unique identifier of the cityStateZip
     * @apiParam {Integer} space_id             The unique identifier of the space
     * @apiParam {Integer} salutaton_id         The unique identifier of the salutation
     *
     * @apiParamExample {json} Request-Example:
     *     {
     *          "first_name": "Harut",
     *          "last_name": "Grigoryan",
     *          "middle_name": "Gagik",
     *          "address_1": "Fuchik str. 25",
     *          "address_2": "Alaverdyan str. 25 ap 2",
     *          "office_phone": "+37499105555555",
     *          "fax": "+37499105555555",
     *          "emergency_phone": "+37499105555555",
     *          "email": "test@example.com",
     *          "website_url": "http://example.com",
     *          "csz_id": 1,
     *          "salutation_id": 1,
     *          "space_id": 1
     *     }
     * @apiSuccessExample {json} Sample Response:
     *     HTTP/1.1 201 Created
     *     {}
     *
     * @Route("", name="api_admin_physician_add", methods={"POST"})
     *
     * @Grant(grant="persistence-common-physician", level="ADD")
     *
     * @param Request $request
     * @param PhysicianService $physicianService
     * @return JsonResponse
     * @throws \Doctrine\DBAL\ConnectionException
     * @throws \Exception
     */
    public function addAction(Request $request, PhysicianService $physicianService)
    {
        $id = $physicianService->add(
            [
                'first_name'        => $request->get('first_name'),
                'middle_name'       => $request->get('middle_name'),
                'last_name'         => $request->get('last_name'),
                'address_1'         => $request->get('address_1'),
                'address_2'         => $request->get('address_2'),
                'office_phone'      => $request->get('office_phone'),
                'fax'               => $request->get('fax'),
                'emergency_phone'   => $request->get('emergency_phone'),
                'email'             => $request->get('email'),
                'website_url'       => $request->get('website_url'),
                'csz_id'            => $request->get('csz_id'),
                'space_id'          => $request->get('space_id'),
                'salutation_id'     => $request->get('salutation_id'),
                'speciality_id'     => $request->get('speciality_id')
            ]
        );

        return $this->respondSuccess(
            Response::HTTP_CREATED,
            '',
            [$id]
        );
    }

    /**
     * @api {put} /api/v1.0/admin/physician/{id} Edit Physician
     * @apiVersion 1.0.0
     * @apiName Edit Physician
     * @apiGroup Admin Physicians
     * @apiPermission none
     * @apiDescription This function is used to edit physician
     *
     * @apiHeader {String} Content-Type  application/json
     * @apiHeader {String} Authorization Bearer ACCESS_TOKEN
     *
     * @apiParam {String}  first_name           The First Name of the physician
     * @apiParam {String}  [middle_name]        The Middle Name of the physician
     * @apiParam {String}  last_name            The Last Name of the physician
     * @apiParam {String}  address_1            The main address of the physician
     * @apiParam {String}  [address_2]          The secondary address of the physician
     * @apiParam {String}  office_phone         The office phone number of the physician
     * @apiParam {String}  [fax]                The fax number of the physician
     * @apiParam {String}  [emergency_phone]    The emergency phone number of the physician
     * @apiParam {String}  [email]              The email address of the physician
     * @apiParam {String}  [website_url]        The website url of the physician
     * @apiParam {Integer} csz_id               The unique identifier of the cityStateZip
     * @apiParam {Integer} space_id             The unique identifier of the space
     * @apiParam {Integer} salutaton_id         The unique identifier of the salutation
     *
     * @apiParamExample {json} Request-Example:
     *     {
     *          "first_name": "Harut",
     *          "last_name": "Grigoryan",
     *          "middle_name": "Gagik",
     *          "address_1": "Fuchik str. 25",
     *          "address_2": "Alaverdyan str. 25 ap 2",
     *          "office_phone": "+37499105555555",
     *          "fax": "+37499105555555",
     *          "emergency_phone": "+37499105555555",
     *          "email": "test@example.com",
     *          "website_url": "http://example.com",
     *          "csz_id": 1,
     *          "salutation_id": 1,
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
     *              "address_1": "This value should not be blank."
     *          }
     *     }
     *
     * @Route("/{id}", name="api_admin_physician_edit", methods={"PUT"})
     *
     * @Grant(grant="persistence-common-physician", level="EDIT")
     *
     * @param Request $request
     * @param $id
     * @param PhysicianService $physicianService
     * @return JsonResponse
     * @throws \Doctrine\DBAL\ConnectionException
     */
    public function editAction(Request $request, $id, PhysicianService $physicianService)
    {
        $physicianService->edit(
            $id,
            [
                'first_name'        => $request->get('first_name'),
                'middle_name'       => $request->get('middle_name'),
                'last_name'         => $request->get('last_name'),
                'address_1'         => $request->get('address_1'),
                'address_2'         => $request->get('address_2'),
                'office_phone'      => $request->get('office_phone'),
                'fax'               => $request->get('fax'),
                'emergency_phone'   => $request->get('emergency_phone'),
                'email'             => $request->get('email'),
                'website_url'       => $request->get('website_url'),
                'csz_id'            => $request->get('csz_id'),
                'space_id'          => $request->get('space_id'),
                'salutation_id'     => $request->get('salutation_id'),
                'speciality_id'     => $request->get('speciality_id')
            ]
        );

        return $this->respondSuccess(
            Response::HTTP_CREATED
        );
    }

    /**
     * @api {delete} /api/v1.0/admin/physician/{id} Delete Physician
     * @apiVersion 1.0.0
     * @apiName Delete Physician
     * @apiGroup Admin Physicians
     * @apiPermission none
     * @apiDescription This function is used to remove physician
     *
     * @apiHeader {String} Content-Type  application/json
     * @apiHeader {String} Authorization Bearer ACCESS_TOKEN
     *
     * @apiParam {Int} id The unique identifier of the physician
     *
     * @apiSuccessExample {json} Sample Response:
     *     HTTP/1.1 204 No Content
     *     {}
     * @apiErrorExample {json} Error-Response:
     *     HTTP/1.1 400 Bad Request
     *     {
     *          "code": 631,
     *          "error": "Physician not found"
     *     }
     *
     * @Route("/{id}", name="api_admin_physician_delete", methods={"DELETE"})
     *
     * @Grant(grant="persistence-common-physician", level="DELETE")
     *
     * @param Request $request
     * @param $id
     * @param PhysicianService $physicianService
     * @return JsonResponse
     * @throws \Doctrine\DBAL\ConnectionException
     * @throws \Throwable
     */
    public function removeAction(Request $request, $id, PhysicianService $physicianService)
    {
        $physicianService->remove($id);

        return $this->respondSuccess(
            Response::HTTP_NO_CONTENT
        );
    }

    /**
     * @api {delete} /api/v1.0/admin/physician Bulk Delete Physicians
     * @apiVersion 1.0.0
     * @apiName Bulk Delete Physicians
     * @apiGroup Admin Physicians
     * @apiDescription This function is used to bulk remove physicians
     *
     * @apiHeader {String} Content-Type  application/json
     * @apiHeader {String} Authorization Bearer ACCESS_TOKEN
     *
     * @apiParam {Int[]} ids The unique identifier of the role
     *
     * @apiSuccessExample {json} Sample Response:
     *     HTTP/1.1 204 No Content
     *     {}
     * @apiErrorExample {json} Error-Response:
     *     HTTP/1.1 400 Bad Request
     *     {
     *          "code": 611,
     *          "error": "Role not found"
     *     }
     *
     * @Route("", name="api_admin_physician_delete_bulk", methods={"DELETE"})
     *
     * @Grant(grant="persistence-common-physician", level="DELETE")
     *
     * @param Request $request
     * @param PhysicianService $physicianService
     * @return JsonResponse
     * @throws \Doctrine\DBAL\ConnectionException
     * @throws \Throwable
     */
    public function deleteBulkAction(Request $request, PhysicianService $physicianService)
    {
        $physicianService->removeBulk($request->get('ids'));

        return $this->respondSuccess(
            Response::HTTP_NO_CONTENT
        );
    }
}
