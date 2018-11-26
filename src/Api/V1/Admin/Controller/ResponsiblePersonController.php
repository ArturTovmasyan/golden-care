<?php
namespace App\Api\V1\Admin\Controller;

use App\Api\V1\Admin\Service\ResponsiblePersonService;
use App\Api\V1\Common\Controller\BaseController;
use App\Entity\ResponsiblePerson;
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
 * @Route("/api/v1.0/admin/responsible/person")
 *
 * Class ResponsiblePersonController
 * @package App\Api\V1\Admin\Controller
 */
class ResponsiblePersonController extends BaseController
{
    /**
     * @api {get} /api/v1.0/admin/responsible/person/grid Get Responsible Person Grid
     * @apiVersion 1.0.0
     * @apiName Get Responsible Person Grid
     * @apiGroup Admin Responsible Person
     * @apiDescription This function is used to listing rp's
     *
     * @apiHeader {String} Content-Type  application/json
     * @apiHeader {String} Authorization Bearer ACCESS_TOKEN
     *
     * @apiSuccess {Int}     id              The unique identifier of the rp
     * @apiSuccess {String}  first_name      The First Name of the rp
     * @apiSuccess {String}  middle_name     The Middle Name of the rp
     * @apiSuccess {String}  last_name       The Last Name of the rp
     * @apiSuccess {String}  address_1       The main address of the rp
     * @apiSuccess {String}  address_2       The secondary address of the rp
     * @apiSuccess {String}  email           The email address of the rp
     * @apiSuccess {Int}     financially  The financially status of rp
     * @apiSuccess {Int}     emergency    The emergency status of rp
     * @apiSuccess {String}  space           The space name of the rp
     * @apiSuccess {String}  csz             The cityStateZip short address of the rp
     * @apiSuccess {String}  salutation      The salutation of the rp
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
     *                  "salutation": "Mr",
     *                  "first_name": "Arthur",
     *                  "middle_name": "Gagik",
     *                  "last_name": "Jovhannesyan",
     *                  "address_1": "Fuchik str 2",
     *                  "address_2": "Alaverdyan str 25 ap. 2",
     *                  "financially": 0,
     *                  "emergency": 1,
     *                  "email": "test@example.com",
     *                  "csz": "Verdi CA, 89439",
     *                  "space": "alms"
     *              }
     *          ]
     *     }
     *
     * @Route("/grid", name="api_admin_responsible_person_grid", methods={"GET"})
     *
     * @param Request $request
     * @param ResponsiblePersonService $responsiblePersonService
     * @return JsonResponse|PdfResponse
     * @throws \ReflectionException
     */
    public function gridAction(Request $request, ResponsiblePersonService $responsiblePersonService)
    {
        return $this->respondGrid(
            $request,
            ResponsiblePerson::class,
            'api_admin_responsible_person_grid',
            $responsiblePersonService
        );
    }

    /**
     * @api {options} /api/v1.0/admin/responsible/person/grid Get Responsible Person Grid Options
     * @apiVersion 1.0.0
     * @apiName Get Responsible Person Grid Options
     * @apiGroup Admin Responsible Person
     * @apiDescription This function is used to describe options of listing
     *
     * @apiHeader {String} Content-Type  application/json
     * @apiHeader {String} Authorization Bearer ACCESS_TOKEN
     *
     * @apiSuccess {Array} options The options of the rp listing
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
     * @Route("/grid", name="api_admin_responsible_person_grid_options", methods={"OPTIONS"})
     *
     * @param Request $request
     * @return JsonResponse
     * @throws \ReflectionException
     */
    public function gridOptionAction(Request $request)
    {
        return $this->getOptionsByGroupName(ResponsiblePerson::class, 'api_admin_responsible_person_grid');
    }

    /**
     * @api {get} /api/v1.0/admin/responsible/person Get Responsible Persons
     * @apiVersion 1.0.0
     * @apiName Get Responsible Person
     * @apiGroup Admin Responsible Person
     * @apiDescription This function is used to listing rp's
     *
     * @apiHeader {String} Content-Type  application/json
     * @apiHeader {String} Authorization Bearer ACCESS_TOKEN
     *
     * @apiSuccess {Int}     id              The unique identifier of the rp
     * @apiSuccess {String}  first_name      The First Name of the rp
     * @apiSuccess {String}  middle_name     The Middle Name of the rp
     * @apiSuccess {String}  last_name       The Last Name of the rp
     * @apiSuccess {String}  address_1       The main address of the rp
     * @apiSuccess {String}  address_2       The secondary address of the rp
     * @apiSuccess {String}  email           The email address of the rp
     * @apiSuccess {Int}     financially     The financially status of rp
     * @apiSuccess {Int}     emergency       The emergency status of rp
     * @apiSuccess {Object}  space           The space of the rp
     * @apiSuccess {Object}  csz             The cityStateZip of the rp
     *
     * @apiSuccessExample {json} Sample Response:
     *     HTTP/1.1 200 OK
     *     [
     *          {
     *              "id": 1,
     *              "first_name": "Gagik",
     *              "last_name": "Gabrielyan",
     *              "middle_name": "",
     *              "address_1": "",
     *              "address_2": "",
     *              "email": "",
     *              "financially": false,
     *              "emergency": false,
     *              "csz": {
     *                  "id": 1
     *               },
     *              "space": {
     *                  "id": 1
     *              },
     *              "salutation": {
     *                  "id": 1
     *              },
     *              "phones": [
     *                  {
     *                      "compatibility": 1,
     *                      "type": 1,
     *                      "number": "+3748880880",
     *                      "primary": 0,
     *                      "sms_enabled": 1,
     *                      "extension": 1515
     *                  }
     *              ]
     *          }
     *     ]
     *
     * @Route("", name="api_admin_responsible_person_list", methods={"GET"})
     *
     * @param Request $request
     * @param ResponsiblePersonService $responsiblePersonService
     * @return JsonResponse|PdfResponse
     * @throws \ReflectionException
     */
    public function listAction(Request $request, ResponsiblePersonService $responsiblePersonService)
    {
        return $this->respondList(
            $request,
            ResponsiblePerson::class,
            'api_admin_responsible_person_list',
            $responsiblePersonService
        );
    }

    /**
     * @api {get} /api/v1.0/admin/responsible/person/space/{space_id} Get Responsible Persons by Space
     * @apiVersion 1.0.0
     * @apiName Get Responsible Person by Space
     * @apiGroup Admin Responsible Person
     * @apiDescription This function is used to listing rp's by space
     *
     * @apiHeader {String} Content-Type  application/json
     * @apiHeader {String} Authorization Bearer ACCESS_TOKEN
     *
     * @apiSuccess {Int}     id              The unique identifier of the rp
     * @apiSuccess {String}  first_name      The First Name of the rp
     * @apiSuccess {String}  middle_name     The Middle Name of the rp
     * @apiSuccess {String}  last_name       The Last Name of the rp
     * @apiSuccess {String}  address_1       The main address of the rp
     * @apiSuccess {String}  address_2       The secondary address of the rp
     * @apiSuccess {String}  email           The email address of the rp
     * @apiSuccess {Int}     financially  The financially status of rp
     * @apiSuccess {Int}     emergency    The emergency status of rp
     * @apiSuccess {Object}  space           The space of the rp
     * @apiSuccess {Object}  csz             The cityStateZip of the rp
     *
     * @apiSuccessExample {json} Sample Response:
     *     HTTP/1.1 200 OK
     *     [
     *          {
     *              "id": 1,
     *              "first_name": "Gagik",
     *              "last_name": "Gabrielyan",
     *              "middle_name": "",
     *              "address_1": "",
     *              "address_2": "",
     *              "email": "",
     *              "financially": false,
     *              "emergency": false,
     *              "csz": {
     *                  "id": 1
     *               },
     *              "space": {
     *                  "id": 1
     *              },
     *              "salutation": {
     *                  "id": 1
     *              },
     *              "phones": [
     *                  {
     *                      "compatibility": 1,
     *                      "type": 1,
     *                      "number": "+3748880880",
     *                      "primary": 0,
     *                      "sms_enabled": 1,
     *                      "extension": 1515
     *                  }
     *              ]
     *          }
     *     ]
     *
     * @Route("/space/{spaceId}", requirements={"spaceId"="\d+"}, name="api_admin_responsible_person_list_by_space", methods={"GET"})
     *
     * @param Request $request
     * @param $spaceId
     * @param ResponsiblePersonService $responsiblePersonService
     * @return JsonResponse
     */
    public function listBySpaceAction(Request $request, $spaceId, ResponsiblePersonService $responsiblePersonService)
    {
        return $this->respondSuccess(
            Response::HTTP_OK,
            '',
            $responsiblePersonService->getBySpaceId($spaceId),
            'api_admin_responsible_person_list_by_space'
        );
    }

    /**
     * @api {get} /api/v1.0/admin/responsible/person/{id} Get Responsible Person
     * @apiVersion 1.0.0
     * @apiName Get Responsible Person
     * @apiGroup Admin Responsible Person
     * @apiDescription This function is used to get rp
     *
     * @apiHeader {String} Content-Type  application/json
     * @apiHeader {String} Authorization Bearer ACCESS_TOKEN
     *
     * @apiSuccess {Int}     id              The unique identifier of the rp
     * @apiSuccess {String}  first_name      The First Name of the rp
     * @apiSuccess {String}  middle_name     The Middle Name of the rp
     * @apiSuccess {String}  last_name       The Last Name of the rp
     * @apiSuccess {String}  address_1       The main address of the rp
     * @apiSuccess {String}  address_2       The secondary address of the rp
     * @apiSuccess {String}  email           The email address of the rp
     * @apiSuccess {Int}     financially  The financially status of rp
     * @apiSuccess {Int}     _emergency    The emergency status of rp
     * @apiSuccess {Object}  space           The space of the rp
     * @apiSuccess {Object}  csz             The cityStateZip of the rp
     *
     * @apiSuccessExample {json} Sample Response:
     *     HTTP/1.1 200 OK
     *     {
     *          "data": {
     *              "id": 1,
     *              "first_name": "Arthur",
     *              "middle_name": "Gagik",
     *              "last_name": "Jovhannesyan",
     *              "address_1": "Fuchik str 2",
     *              "address_2": "Alaverdyan str 25 ap. 2",
     *              "financially": 0,
     *              "emergency": 1,
     *              "email": "test@example.com",
     *              "space": {
     *                  id: 5
     *              },
     *              "csz": {
     *                  id: 1
     *              },
     *              "phones": [
     *                  {
     *                      "compatibility": 1,
     *                      "type": 1,
     *                      "number": "+3748880880",
     *                      "primary": 0,
     *                      "sms_enabled": 1,
     *                      "extension": 1515
     *                  }
     *              ]
     *          }
     *     }
     *
     * @Route("/{id}", requirements={"id"="\d+"}, name="api_admin_responsible_person_get", methods={"GET"})
     *
     * @param ResponsiblePersonService $responsiblePersonService
     * @param $id
     * @return JsonResponse
     */
    public function getAction(Request $request, $id, ResponsiblePersonService $responsiblePersonService)
    {
        return $this->respondSuccess(
            Response::HTTP_OK,
            '',
            $responsiblePersonService->getById($id),
            ['api_admin_responsible_person_get']
        );
    }

    /**
     * @api {post} /api/v1.0/admin/responsible/person Add Responsible Person
     * @apiVersion 1.0.0
     * @apiName Add Responsible Person
     * @apiGroup Admin Responsible Person
     * @apiDescription This function is used to add rp
     *
     * @apiHeader {String} Content-Type  application/x-www-form-urlencoded
     * @apiHeader {String} Authorization Bearer ACCESS_TOKEN
     *
     * @apiParam {String}  first_name      The First Name of the rp
     * @apiParam {String}  [middle_name]   The Middle Name of the rp
     * @apiParam {String}  last_name       The Last Name of the rp
     * @apiParam {String}  address_1       The first address of the rp
     * @apiParam {String}  [address_2]     The second address of the rp
     * @apiParam {String}  financially  The financially status of rp
     * @apiParam {String}  emergency    The emergency status of rp
     * @apiParam {String}  email           The email address of the rp
     * @apiParam {Int}     csz_id          The unique identifier of the City State & Zip
     * @apiParam {Int}     space_id        The unique identifier of the space
     * @apiParam {Int}     salutation_id   The unique identifier of the salutation
     *
     * @apiParamExample {json} Request-Example:
     *     {
     *          "first_name": "Arthur",
     *          "middle_name": "Gagik",
     *          "last_name": "Jovhannesyan",
     *          "address_1": "Fuchik str 2",
     *          "address_2": "Alaverdyan str 25 ap. 2",
     *          "financially": 0,
     *          "emergency": 1,
     *          "email": "test@example.com",
     *          "csz_id": 1,
     *          "space_id": 1,
     *          "salutation_id": 1
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
     *              "email": "This value is not a valid email address."
     *          }
     *     }
     *
     * @Route("", name="api_admin_responsible_person_add", methods={"POST"})
     *
     * @param Request $request
     * @param ResponsiblePersonService $responsiblePersonService
     * @return JsonResponse
     * @throws \Exception
     */
    public function addAction(Request $request, ResponsiblePersonService $responsiblePersonService)
    {
        $responsiblePersonService->add(
            [
                'first_name'        => $request->get('first_name'),
                'middle_name'       => $request->get('middle_name'),
                'last_name'         => $request->get('last_name'),
                'address_1'         => $request->get('address_1'),
                'address_2'         => $request->get('address_2'),
                'emergency'         => $request->get('emergency'),
                'financially'       => $request->get('financially'),
                'email'             => $request->get('email'),
                'csz_id'            => $request->get('csz_id'),
                'space_id'          => $request->get('space_id'),
                'salutation_id'     => $request->get('salutation_id'),
                'phone'             => $request->get('phone')
            ]
        );

        return $this->respondSuccess(
            Response::HTTP_CREATED
        );
    }

    /**
     * @api {put} /api/v1.0/admin/responsible/person/{id} Edit Responsible Person
     * @apiVersion 1.0.0
     * @apiName Edit Responsible Person
     * @apiGroup Admin Responsible Person
     * @apiDescription This function is used to edit rp
     *
     * @apiHeader {String} Content-Type  application/x-www-form-urlencoded
     * @apiHeader {String} Authorization Bearer ACCESS_TOKEN
     *
     * @apiParam {String}  first_name      The First Name of the rp
     * @apiParam {String}  [middle_name]   The Middle Name of the rp
     * @apiParam {String}  last_name       The Last Name of the rp
     * @apiParam {String}  address_1       The first address of the rp
     * @apiParam {String}  [address_2]     The second address of the rp
     * @apiParam {String}  financially  The financially status of rp
     * @apiParam {String}  emergency    The emergency status of rp
     * @apiParam {String}  email           The email address of the rp
     * @apiParam {Int}     csz_id          The unique identifier of the City State & Zip
     * @apiParam {Int}     space_id        The unique identifier of the space
     * @apiParam {Int}     salutation_id   The unique identifier of the salutation
     *
     * @apiParamExample {json} Request-Example:
     *     {
     *          "first_name": "Arthur",
     *          "middle_name": "Gagik",
     *          "last_name": "Jovhannesyan",
     *          "address_1": "Fuchik str 2",
     *          "address_2": "Alaverdyan str 25 ap. 2",
     *          "financially": 0,
     *          "emergency": 1,
     *          "email": "test@example.com",
     *          "csz_id": 1,
     *          "space_id": 1,
     *          "salutation_id": 1
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
     *              "email": "This value is not a valid email address."
     *          }
     *     }
     *
     * @Route("/{id}", requirements={"id"="\d+"}, name="api_admin_responsible_person_edit", methods={"PUT"})
     *
     * @param Request $request
     * @param $id
     * @param ResponsiblePersonService $responsiblePersonService
     * @return JsonResponse
     * @throws \Exception
     */
    public function editAction(Request $request, $id, ResponsiblePersonService $responsiblePersonService)
    {
        $responsiblePersonService->edit(
            $id,
            [
                'first_name'        => $request->get('first_name'),
                'middle_name'       => $request->get('middle_name'),
                'last_name'         => $request->get('last_name'),
                'address_1'         => $request->get('address_1'),
                'address_2'         => $request->get('address_2'),
                'emergency'         => $request->get('emergency'),
                'financially'       => $request->get('financially'),
                'email'             => $request->get('email'),
                'csz_id'            => $request->get('csz_id'),
                'space_id'          => $request->get('space_id'),
                'salutation_id'     => $request->get('salutation_id'),
                'phone'             => $request->get('phone')
            ]
        );

        return $this->respondSuccess(
            Response::HTTP_CREATED
        );
    }

    /**
     * @api {delete} /api/v1.0/admin/responsible/person/{id} Delete Responsible Person
     * @apiVersion 1.0.0
     * @apiName Delete Responsible Person
     * @apiGroup Admin Responsible Person
     * @apiDescription This function is used to remove rp
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
     *          "error": "Responsible person not found"
     *     }
     *
     * @Route("/{id}", requirements={"id"="\d+"}, name="api_admin_responsible_person_delete", methods={"DELETE"})
     *
     * @param $id
     * @param ResponsiblePersonService $responsiblePersonService
     * @return JsonResponse
     * @throws \Doctrine\DBAL\ConnectionException
     * @throws \Throwable
     */
    public function deleteAction(Request $request, $id, ResponsiblePersonService $responsiblePersonService)
    {
        $responsiblePersonService->remove($id);

        return $this->respondSuccess(
            Response::HTTP_NO_CONTENT
        );
    }

    /**
     * @api {delete} /api/v1.0/admin/responsible/person Bulk Delete Responsible Person
     * @apiVersion 1.0.0
     * @apiName Bulk Delete Responsible Person
     * @apiGroup Admin Responsible Person
     * @apiDescription This function is used to bulk remove rp
     *
     * @apiHeader {String} Content-Type  application/x-www-form-urlencoded
     * @apiHeader {String} Authorization Bearer ACCESS_TOKEN
     *
     * @apiParam {Int[]} ids The unique identifier of the rp
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
     *          "error": "Responsible person not found"
     *     }
     *
     * @Route("", name="api_admin_responsible_person_delete_bulk", methods={"DELETE"})
     *
     * @param Request $request
     * @param ResponsiblePersonService $responsiblePersonService
     * @return JsonResponse
     * @throws \Doctrine\DBAL\ConnectionException
     * @throws \Throwable
     */
    public function deleteBulkAction(Request $request, ResponsiblePersonService $responsiblePersonService)
    {
        $responsiblePersonService->removeBulk($request->get('ids'));

        return $this->respondSuccess(
            Response::HTTP_NO_CONTENT
        );
    }
}
