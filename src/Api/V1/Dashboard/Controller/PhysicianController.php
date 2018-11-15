<?php

namespace App\Api\V1\Dashboard\Controller;

use App\Api\V1\Common\Controller\BaseController;
use App\Annotation\Permission;
use App\Api\V1\Dashboard\Service\MedicationService;
use App\Api\V1\Dashboard\Service\PhysicianService;
use App\Entity\Medication;
use App\Entity\Physician;
use App\Entity\Space;
use Knp\Bundle\SnappyBundle\Snappy\Response\PdfResponse;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

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
 * @Route("/api/v1.0/dashboard/space/{spaceId}/physician")
 * @Permission({"PERMISSION_PHYSICIAN"})
 *
 * Class PhysicianController
 * @package App\Api\V1\Dashboard\Controller
 */
class PhysicianController extends BaseController
{
    /**
     * @api {get} /api/v1.0/dashboard/space/{spaceId}/physician/grid Get Physicians Grid
     * @apiVersion 1.0.0
     * @apiName Get Physicians Grid
     * @apiGroup Dashboard Physicians
     * @apiPermission PERMISSION_PHYSICIAN
     * @apiDescription This function is used to get user all physicians grid for dashboard
     *
     * @apiHeader {String} Content-Type  application/json
     * @apiHeader {String} Authorization Bearer ACCESS_TOKEN
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
     *
     * @apiSuccessExample {json} Sample Response:
     *     HTTP/1.1 200 OK
     *     {
     *          [
     *              {
     *                  "id": 1,
     *                  "first_name": "Arthur",
     *                  "middle_name": "Gagik",
     *                  "last_name": "Jovhannesyan",
     *                  "address_1": "Fuchik str 2",
     *                  "address_2": "Alaverdyan str 25 ap. 2",
     *                  "office_phone": "+374544554545",
     *                  "fax": "+37410555565",
     *                  "emergency_phone": "+37455888080",
     *                  "email": "test@example.com",
     *                  "website_url": "http://example.com"
     *              }
     *          ]
     *     }
     *
     * @Route("/grid", name="api_dashboard_physician_grid", requirements={"spaceId"="\d+"}, methods={"GET"})
     *
     * @param Request $request
     * @param $spaceId
     * @param PhysicianService $physicianService
     * @return PdfResponse|JsonResponse
     * @throws \ReflectionException
     */
    public function gridAction(Request $request, $spaceId, PhysicianService $physicianService)
    {
        return $this->respondGrid(
            $request,
            Physician::class,
            'api_dashboard_physician_grid',
            $physicianService,
            $request->get('space')
        );
    }

    /**
     * @api {options} /api/v1.0/dashboard/space/{spaceId}/physician/grid Get Physicians Grid Options
     * @apiVersion 1.0.0
     * @apiName Get Physicians Grid Options
     * @apiGroup Dashboard Physicians
     * @apiPermission PERMISSION_PHYSICIAN
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
     * @Route("/grid", name="api_dashboard_physician_grid_options", methods={"OPTIONS"})
     *
     * @param Request $request
     * @return \Knp\Bundle\SnappyBundle\Snappy\Response\PdfResponse|JsonResponse
     * @throws \ReflectionException
     */
    public function gridOptionAction(Request $request)
    {
        return $this->getOptionsByGroupName(Physician::class, 'api_dashboard_physician_grid');
    }

    /**
     * @api {get} /api/v1.0/dashboard/space/{spaceId}/physician Get Physicians
     * @apiVersion 1.0.0
     * @apiName Get Physicians
     * @apiGroup Dashboard Physicians
     * @apiPermission PERMISSION_PHYSICIAN
     * @apiDescription This function is used to listing physicians by space
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
     *                  "first_name": "Arthur",
     *                  "middle_name": "Gagik",
     *                  "last_name": "Jovhannesyan",
     *                  "address_1": "Fuchik str 2",
     *                  "address_2": "Alaverdyan str 25 ap. 2",
     *                  "office_phone": "+374544554545",
     *                  "fax": "+37410555565",
     *                  "emergency_phone": "+37455888080",
     *                  "email": "test@example.com",
     *                  "website_url": "http://example.com"
     *              }
     *          }
     *     }
     *
     * @Route("", name="api_dashboard_physician_list", requirements={"spaceId"="\d+"}, methods={"GET"})
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
            'api_dashboard_physician_list',
            $physicianService,
            $request->get('space')
        );
    }

    /**
     * @api {get} /api/v1.0/dashboard/space/{space_id}/physician/{id} Get Physician
     * @apiVersion 1.0.0
     * @apiName Get Physician
     * @apiGroup Dashboard Physicians
     * @apiPermission PERMISSION_PHYSICIAN
     * @apiDescription This function is used to get physician by space and id
     *
     * @apiHeader {String} Content-Type  application/json
     * @apiHeader {String} Authorization Bearer ACCESS_TOKEN
     *
     * @apiParam {Int} space_id The unique identifier of the space
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
     *
     * @apiSuccessExample {json} Sample Response:
     *     HTTP/1.1 200 OK
     *     {
     *          "id": 1,
     *          "first_name": "Harut",
     *          "last_name": "Grigoryan",
     *          "middle_name": "Gagik",
     *          "address1": "Fuchik str. 25",
     *          "address2": "Alaverdyan str. 25 ap 2",
     *          "office_phone": "+37499105555555",
     *          "fax": "+37499105555555",
     *          "emergency_phone": "+37499105555555",
     *          "email": "test@example.com",
     *          "website_url": "http://example.com"
     *     }
     *
     * @Route("/{id}", name="api_dashboard_physician_get", requirements={"spaceId"="\d+", "id"="\d+"}, methods={"GET"})
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
            $physicianService->getBySpaceAndId($request->get('space'), $id),
            ['api_dashboard_physician_get']
        );
    }

    /**
     * @api {post} /api/v1.0/dashboard/space/{space_id}/physician Add Physician
     * @apiVersion 1.0.0
     * @apiName Add Physician
     * @apiGroup Dashboard Physicians
     * @apiPermission PERMISSION_PHYSICIAN
     * @apiDescription This function is used to add physician for space
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
     *
     * @apiParamExample {json} Request-Example:
     *     {
     *          "first_name": "Harut",
     *          "last_name": "Grigoryan",
     *          "middle_name": "Gagik",
     *          "address1": "Fuchik str. 25",
     *          "address2": "Alaverdyan str. 25 ap 2",
     *          "office_phone": "+37499105555555",
     *          "fax": "+37499105555555",
     *          "emergency_phone": "+37499105555555",
     *          "email": "test@example.com",
     *          "website_url": "http://example.com"
     *     }
     * @apiSuccessExample {json} Sample Response:
     *     HTTP/1.1 201 Created
     *     {}
     *
     * @Route("", name="api_dashboard_physician_add", methods={"POST"})
     *
     * @param Request $request
     * @param PhysicianService $physicianService
     * @return JsonResponse
     * @throws \Doctrine\DBAL\ConnectionException
     */
    public function addAction(Request $request, PhysicianService $physicianService)
    {
        $physicianService->add(
            $request->get('space'),
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
                'website_url'       => $request->get('website_url')
            ]
        );

        return $this->respondSuccess(
            Response::HTTP_CREATED
        );
    }

    /**
     * @api {put} /api/v1.0/dashboard/space/{space_id}/physician/{id} Edit Physician
     * @apiVersion 1.0.0
     * @apiName Edit Physician
     * @apiGroup Dashboard Physicians
     * @apiPermission PERMISSION_PHYSICIAN
     * @apiDescription This function is used to edit physician for space
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
     *
     * @apiParamExample {json} Request-Example:
     *     {
     *          "first_name": "Harut",
     *          "last_name": "Grigoryan",
     *          "middle_name": "Gagik",
     *          "address1": "Fuchik str. 25",
     *          "address2": "Alaverdyan str. 25 ap 2",
     *          "office_phone": "+37499105555555",
     *          "fax": "+37499105555555",
     *          "emergency_phone": "+37499105555555",
     *          "email": "test@example.com",
     *          "website_url": "http://example.com"
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
     *              "address1": "This value should not be blank."
     *          }
     *     }
     *
     * @Route("/{id}", requirements={"spaceId"="\d+", "id"="\d+"}, name="api_dashboard_physician_edit", methods={"PUT"})
     *
     * @param Request $request
     * @param $id
     * @param Space $space
     * @param PhysicianService $physicianService
     * @return JsonResponse
     * @throws \Doctrine\DBAL\ConnectionException
     */
    public function editAction(Request $request, $id, Space $space, PhysicianService $physicianService)
    {
        $physicianService->edit(
            $id,
            $space,
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
                'website_url'       => $request->get('website_url')
            ]
        );

        return $this->respondSuccess(
            Response::HTTP_CREATED
        );
    }

    /**
     * @api {delete} /api/v1.0/dashboard/space/{space_id}/physician/{id} Delete Physician
     * @apiVersion 1.0.0
     * @apiName Delete Physician
     * @apiGroup Dashboard Physicians
     * @apiPermission PERMISSION_PHYSICIAN
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
     * @Route("/{id}", requirements={"spaceId"="\d+", "id"="\d+"}, name="api_dashboard_physician_delete", methods={"DELETE"})
     *
     * @param Request $request
     * @param $id
     * @param Space $space
     * @param PhysicianService $physicianService
     * @return JsonResponse
     * @throws \Doctrine\DBAL\ConnectionException
     * @throws \Throwable
     */
    public function removeAction(Request $request, $id, Space $space, PhysicianService $physicianService)
    {
        $physicianService->remove($id, $space);

        return $this->respondSuccess(
            Response::HTTP_NO_CONTENT
        );
    }
}
