<?php
namespace App\Api\V1\Admin\Controller;

use App\Api\V1\Admin\Service\ResidentResponsiblePersonService;
use App\Api\V1\Common\Controller\BaseController;
use App\Entity\ResidentResponsiblePerson;
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
 * @Route("/api/v1.0/admin/resident/responsible/person")
 *
 * @Grant(grant="persistence-resident-resident_responsible_person", level="VIEW")
 *
 * Class ResidentResponsiblePersonController
 * @package App\Api\V1\Admin\Controller
 */
class ResidentResponsiblePersonController extends BaseController
{
    /**
     * @api {get} /api/v1.0/admin/resident/responsible/person/grid Get ResidentResponsiblePersons Grid
     * @apiVersion 1.0.0
     * @apiName Get ResidentResponsiblePersons Grid
     * @apiGroup Admin Resident Responsible Persons
     * @apiDescription This function is used to listing residentResponsiblePersons
     *
     * @apiHeader {String} Content-Type  application/json
     * @apiHeader {String} Authorization Bearer ACCESS_TOKEN
     *
     * @apiSuccess {Int}      id                   The unique identifier of the residentResponsiblePersons
     * @apiSuccess {Object}   resident             The resident identifier of the residentResponsiblePersons
     * @apiSuccess {Object}   responsible_person   The responsible person Full Name of the residentResponsiblePersons
     * @apiSuccess {String}   relationship         The relationship name of the residentResponsiblePersons
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
     *                   "resident": 1,
     *                   "responsible_person": "Joe Cole",
     *                   "relationship": "Brother"
     *              }
     *          ]
     *     }
     *
     * @Route("/grid", name="api_admin_resident_responsible_person_grid", methods={"GET"})
     *
     * @param Request $request
     * @param ResidentResponsiblePersonService $residentResponsiblePersonService
     * @return JsonResponse|PdfResponse
     * @throws \ReflectionException
     */
    public function gridAction(Request $request, ResidentResponsiblePersonService $residentResponsiblePersonService)
    {
        return $this->respondGrid(
            $request,
            ResidentResponsiblePerson::class,
            'api_admin_resident_responsible_person_grid',
            $residentResponsiblePersonService,
            ['resident_id' => $request->get('resident_id')]
        );
    }

    /**
     * @api {options} /api/v1.0/admin/resident/responsible/person/grid Get ResidentResponsiblePerson Grid Options
     * @apiVersion 1.0.0
     * @apiName Get ResidentResponsiblePerson Grid Options
     * @apiGroup Admin Resident Responsible Persons
     * @apiDescription This function is used to describe options of listing
     *
     * @apiHeader {String} Content-Type  application/json
     * @apiHeader {String} Authorization Bearer ACCESS_TOKEN
     *
     * @apiSuccess {Array} options The options of the residentResponsiblePerson listing
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
     * @Route("/grid", name="api_admin_resident_responsible_person_grid_options", methods={"OPTIONS"})
     *
     * @param Request $request
     * @return JsonResponse
     * @throws \ReflectionException
     */
    public function gridOptionAction(Request $request)
    {
        return $this->getOptionsByGroupName(ResidentResponsiblePerson::class, 'api_admin_resident_responsible_person_grid');
    }

    /**
     * @api {get} /api/v1.0/admin/resident/responsible/person Get ResidentResponsiblePersons
     * @apiVersion 1.0.0
     * @apiName Get ResidentResponsiblePersons
     * @apiGroup Admin Resident Responsible Persons
     * @apiDescription This function is used to listing residentResponsiblePersons
     *
     * @apiHeader {String} Content-Type  application/json
     * @apiHeader {String} Authorization Bearer ACCESS_TOKEN
     *
     * @apiSuccess {Int}      id                   The unique identifier of the residentResponsiblePerson
     * @apiSuccess {Object}   resident             The resident of the residentResponsiblePerson
     * @apiSuccess {Object}   responsible_person   The responsible person of the residentResponsiblePerson
     * @apiSuccess {String}   relationship         The relationship of the residentResponsiblePerson
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
     *             "responsible_person": {
     *                 "id": 1,
     *                 "first_name": "Joe",
     *                 "last_name": "Cole"
     *             },
     *             "relationship": {
     *                 "id": 1
     *                 "name": "Brother"
     *             }
     *         }
     *     ]
     *
     * @Route("", name="api_admin_resident_responsible_person_list", methods={"GET"})
     *
     * @param Request $request
     * @param ResidentResponsiblePersonService $residentResponsiblePersonService
     * @return JsonResponse|PdfResponse
     * @throws \ReflectionException
     */
    public function listAction(Request $request, ResidentResponsiblePersonService $residentResponsiblePersonService)
    {
        return $this->respondList(
            $request,
            ResidentResponsiblePerson::class,
            'api_admin_resident_responsible_person_list',
            $residentResponsiblePersonService,
            ['resident_id' => $request->get('resident_id')]
        );
    }

    /**
     * @api {get} /api/v1.0/admin/resident/responsible/person/{id} Get ResidentResponsiblePerson
     * @apiVersion 1.0.0
     * @apiName Get ResidentResponsiblePerson
     * @apiGroup Admin Resident Responsible Persons
     * @apiDescription This function is used to get residentResponsiblePerson
     *
     * @apiHeader {String} Content-Type  application/json
     * @apiHeader {String} Authorization Bearer ACCESS_TOKEN
     *
     * @apiSuccess {Int}      id                   The unique identifier of the residentResponsiblePerson
     * @apiSuccess {Object}   resident             The resident of the residentResponsiblePerson
     * @apiSuccess {Object}   responsible_person   The responsible person of the residentResponsiblePerson
     * @apiSuccess {String}   relationship         The relationship of the residentResponsiblePerson
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
     *          "responsible_person": {
     *              "id": 1,
     *              "first_name": "Joe",
     *              "last_name": "Cole"
     *          },
     *          "relationship": {
     *              "id": 1
     *              "name": "Brother"
     *          }
     *     }
     *
     * @Route("/{id}", requirements={"id"="\d+"}, name="api_admin_resident_responsible_person_get", methods={"GET"})
     *
     * @param ResidentResponsiblePersonService $residentResponsiblePersonService
     * @param $id
     * @return JsonResponse
     */
    public function getAction(Request $request, $id, ResidentResponsiblePersonService $residentResponsiblePersonService)
    {
        return $this->respondSuccess(
            Response::HTTP_OK,
            '',
            $residentResponsiblePersonService->getById($id),
            ['api_admin_resident_responsible_person_get']
        );
    }

    /**
     * @api {post} /api/v1.0/admin/resident/responsible/person Add ResidentResponsiblePerson
     * @apiVersion 1.0.0
     * @apiName Add ResidentResponsiblePerson
     * @apiGroup Admin Resident Responsible Persons
     * @apiDescription This function is used to add residentResponsiblePerson
     *
     * @apiHeader {String} Content-Type  application/x-www-form-urlencoded
     * @apiHeader {String} Authorization Bearer ACCESS_TOKEN
     *
     * @apiParam {Int}     resident_id           The unique identifier of the resident
     * @apiParam {Int}     responsible_person_id The unique identifier of the responsible person
     * @apiParam {Object}  relationship_id       The unique identifier of relationship
     *
     * @apiParamExample {json} Request-Example:
     *     {
     *          "resident_id": 1,
     *          "responsible_person_id": 1,
     *          "relationship_id": 1
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
     *              "relationship_id": "Sorry, this value not be blank."
     *          }
     *     }
     *
     * @Route("", name="api_admin_resident_responsible_person_add", methods={"POST"})
     *
     * @Grant(grant="persistence-resident-resident_responsible_person", level="ADD")
     *
     * @param Request $request
     * @param ResidentResponsiblePersonService $residentResponsiblePersonService
     * @return JsonResponse
     * @throws \Exception
     */
    public function addAction(Request $request, ResidentResponsiblePersonService $residentResponsiblePersonService)
    {
        $id = $residentResponsiblePersonService->add(
            [
                'resident_id'           => $request->get('resident_id'),
                'responsible_person_id' => $request->get('responsible_person_id'),
                'relationship_id'       => $request->get('relationship_id'),
                'role_id'               => $request->get('role_id')
            ]
        );

        return $this->respondSuccess(
            Response::HTTP_CREATED,
            '',
            [$id]
        );
    }

    /**
     * @api {put} /api/v1.0/admin/resident/responsible/person/{id} Edit ResidentResponsiblePerson
     * @apiVersion 1.0.0
     * @apiName Edit ResidentResponsiblePerson
     * @apiGroup Admin Resident Responsible Persons
     * @apiDescription This function is used to edit residentResponsiblePerson
     *
     * @apiHeader {String} Content-Type  application/x-www-form-urlencoded
     * @apiHeader {String} Authorization Bearer ACCESS_TOKEN
     *
     * @apiParam {Int}     resident_id           The unique identifier of the resident
     * @apiParam {Int}     responsible_person_id The unique identifier of the responsible person
     * @apiParam {Object}  relationship_id       The unique identifier of relationship
     *
     * @apiParamExample {json} Request-Example:
     *     {
     *          "resident_id": 1,
     *          "responsible_person_id": 1,
     *          "relationship_id": 1
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
     *              "relationship_id": "Sorry, this value not be blank."
     *          }
     *     }
     *
     * @Route("/{id}", requirements={"id"="\d+"}, name="api_admin_resident_responsible_person_edit", methods={"PUT"})
     *
     * @Grant(grant="persistence-resident-resident_responsible_person", level="EDIT")
     *
     * @param Request $request
     * @param $id
     * @param ResidentResponsiblePersonService $residentResponsiblePersonService
     * @return JsonResponse
     * @throws \Exception
     */
    public function editAction(Request $request, $id, ResidentResponsiblePersonService $residentResponsiblePersonService)
    {
        $residentResponsiblePersonService->edit(
            $id,
            [
                'resident_id'           => $request->get('resident_id'),
                'responsible_person_id' => $request->get('responsible_person_id'),
                'relationship_id'       => $request->get('relationship_id'),
                'role_id'               => $request->get('role_id')
            ]
        );

        return $this->respondSuccess(
            Response::HTTP_CREATED
        );
    }

    /**
     * @api {delete} /api/v1.0/admin/resident/responsible/person/{id} Delete ResidentResponsiblePerson
     * @apiVersion 1.0.0
     * @apiName Delete ResidentResponsiblePerson
     * @apiGroup Admin Resident Responsible Persons
     * @apiDescription This function is used to remove residentResponsiblePerson
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
     *          "error": "ResidentResponsiblePerson not found"
     *     }
     *
     * @Route("/{id}", requirements={"id"="\d+"}, name="api_admin_resident_responsible_person_delete", methods={"DELETE"})
     *
     * @Grant(grant="persistence-resident-resident_responsible_person", level="DELETE")
     *
     * @param $id
     * @param ResidentResponsiblePersonService $residentResponsiblePersonService
     * @return JsonResponse
     * @throws \Doctrine\DBAL\ConnectionException
     * @throws \Throwable
     */
    public function deleteAction(Request $request, $id, ResidentResponsiblePersonService $residentResponsiblePersonService)
    {
        $residentResponsiblePersonService->remove($id);

        return $this->respondSuccess(
            Response::HTTP_NO_CONTENT
        );
    }

    /**
     * @api {delete} /api/v1.0/admin/resident/responsible/person Bulk Delete ResidentResponsiblePersons
     * @apiVersion 1.0.0
     * @apiName Bulk Delete ResidentResponsiblePersons
     * @apiGroup Admin Resident Responsible Persons
     * @apiDescription This function is used to bulk remove ResidentResponsiblePersons
     *
     * @apiHeader {String} Content-Type  application/x-www-form-urlencoded
     * @apiHeader {String} Authorization Bearer ACCESS_TOKEN
     *
     * @apiParam {Int[]} ids The unique identifier of the residentResponsiblePersons
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
     *          "error": "ResidentResponsiblePerson not found"
     *     }
     *
     * @Route("", name="api_admin_resident_responsible_person_delete_bulk", methods={"DELETE"})
     *
     * @Grant(grant="persistence-resident-resident_responsible_person", level="DELETE")
     *
     * @param Request $request
     * @param ResidentResponsiblePersonService $residentResponsiblePersonService
     * @return JsonResponse
     * @throws \Doctrine\DBAL\ConnectionException
     * @throws \Throwable
     */
    public function deleteBulkAction(Request $request, ResidentResponsiblePersonService $residentResponsiblePersonService)
    {
        $residentResponsiblePersonService->removeBulk($request->get('ids'));

        return $this->respondSuccess(
            Response::HTTP_NO_CONTENT
        );
    }
}
