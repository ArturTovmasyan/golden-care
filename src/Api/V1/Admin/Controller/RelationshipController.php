<?php
namespace App\Api\V1\Admin\Controller;

use App\Api\V1\Admin\Service\RelationshipService;
use App\Api\V1\Common\Controller\BaseController;
use App\Entity\Relationship;
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
 * @Route("/api/v1.0/admin/relationship")
 *
 * Class RelationshipController
 * @package App\Api\V1\Admin\Controller
 */
class RelationshipController extends BaseController
{
    /**
     * @api {get} /api/v1.0/admin/relationship Get Relationships
     * @apiVersion 1.0.0
     * @apiName Get Relationships
     * @apiGroup Admin Relationships
     * @apiDescription This function is used to listing relationships
     *
     * @apiHeader {String} Content-Type  application/json
     * @apiHeader {String} Authorization Bearer ACCESS_TOKEN
     *
     * @apiSuccess {Int}     id   The unique identifier of the relationship
     * @apiSuccess {String}  name The name of the relationship
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
     *                  "name": "Son"
     *              }
     *          ]
     *     }
     *
     * @Route("", name="api_admin_relationship_list", methods={"GET"})
     *
     * @param Request $request
     * @param RelationshipService $relationshipService
     * @return JsonResponse|PdfResponse
     * @throws \ReflectionException
     */
    public function listAction(Request $request, RelationshipService $relationshipService)
    {
        return $this->respondGrid(
            $request,
            Relationship::class,
            'api_admin_relationship_list',
            $relationshipService
        );
    }

    /**
     * @api {options} /api/v1.0/admin/relationship Get Relationships Options
     * @apiVersion 1.0.0
     * @apiName Get Relationships Options
     * @apiGroup Admin Relationships
     * @apiDescription This function is used to describe options of listing
     *
     * @apiHeader {String} Content-Type  application/json
     * @apiHeader {String} Authorization Bearer ACCESS_TOKEN
     *
     * @apiSuccess {Array} options The options of the relationship listing
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
     * @Route("", name="api_admin_relationship_options", methods={"OPTIONS"})
     *
     * @param Request $request
     * @return JsonResponse
     * @throws \ReflectionException
     */
    public function optionAction(Request $request)
    {
        return $this->getOptionsByGroupName(Relationship::class, 'api_admin_relationship_list');
    }

    /**
     * @api {get} /api/v1.0/admin/relationship/{id} Get Relationship
     * @apiVersion 1.0.0
     * @apiName Get Relationship
     * @apiGroup Admin Relationships
     * @apiDescription This function is used to get relationship
     *
     * @apiHeader {String} Content-Type  application/json
     * @apiHeader {String} Authorization Bearer ACCESS_TOKEN
     *
     * @apiSuccess {Int}     id            The unique identifier of the relationship
     * @apiSuccess {String}  name          The Name of the relationship
     *
     * @apiSuccessExample {json} Sample Response:
     *     HTTP/1.1 200 OK
     *     {
     *          "id": 1,
     *          "name": "Son"
     *     }
     *
     * @Route("/{id}", requirements={"id"="\d+"}, name="api_admin_relationship_get", methods={"GET"})
     *
     * @param Request $request
     * @param RelationshipService $relationshipService
     * @param $id
     * @return JsonResponse
     */
    public function getAction(Request $request, $id, RelationshipService $relationshipService)
    {
        return $this->respondSuccess(
            Response::HTTP_OK,
            '',
            $relationshipService->getById($id),
            ['api_admin_relationship_get']
        );
    }

    /**
     * @api {post} /api/v1.0/admin/relationship Add Relationship
     * @apiVersion 1.0.0
     * @apiName Add Relationship
     * @apiGroup Admin Relationships
     * @apiDescription This function is used to add relationship
     *
     * @apiHeader {String} Content-Type  application/json
     * @apiHeader {String} Authorization Bearer ACCESS_TOKEN
     *
     * @apiParam {String}  name The name of the relationship
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
     * @Route("", name="api_admin_relationship_add", methods={"POST"})
     *
     * @param Request $request
     * @param RelationshipService $relationshipService
     * @return JsonResponse
     * @throws \Doctrine\DBAL\ConnectionException
     */
    public function addAction(Request $request, RelationshipService $relationshipService)
    {
        $relationshipService->add(
            [
                'name' => $request->get('name')
            ]
        );

        return $this->respondSuccess(
            Response::HTTP_CREATED
        );
    }

    /**
     * @api {post} /api/v1.0/admin/relationship/{id} Edit Relationship
     * @apiVersion 1.0.0
     * @apiName Edit Relationship
     * @apiGroup Admin Relationship
     * @apiDescription This function is used to edit relationship
     *
     * @apiHeader {String} Content-Type  application/json
     * @apiHeader {String} Authorization Bearer ACCESS_TOKEN
     *
     * @apiParam {Int}     id   The unique identifier of the relationship
     * @apiParam {String}  name The name of the relationship
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
     * @Route("/{id}", requirements={"id"="\d+"}, name="api_admin_relationship_edit", methods={"POST"})
     *
     * @param Request $request
     * @param $id
     * @param RelationshipService $relationshipService
     * @return JsonResponse
     * @throws \Doctrine\DBAL\ConnectionException
     */
    public function editAction(Request $request, $id, RelationshipService $relationshipService)
    {
        $relationshipService->edit(
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
     * @api {delete} /api/v1.0/admin/relationship/{id} Delete Relationship
     * @apiVersion 1.0.0
     * @apiName Delete Relationship
     * @apiGroup Admin Relationships
     * @apiDescription This function is used to remove relationship
     *
     * @apiHeader {String} Content-Type  application/json
     * @apiHeader {String} Authorization Bearer ACCESS_TOKEN
     *
     * @apiParam {Int} id The unique identifier of the relationship
     *
     * @apiSuccessExample {json} Sample Response:
     *     HTTP/1.1 204 No Content
     *     {}
     * @apiErrorExample {json} Error-Response:
     *     HTTP/1.1 400 Bad Request
     *     {
     *          "code": 627,
     *          "error": "Relationship not found"
     *     }
     *
     * @Route("/{id}", requirements={"id"="\d+"}, name="api_admin_relationship_delete", methods={"DELETE"})
     *
     * @param Request $request
     * @param $id
     * @param RelationshipService $relationshipService
     * @return JsonResponse
     * @throws \Doctrine\DBAL\ConnectionException
     * @throws \Throwable
     */
    public function deleteAction(Request $request, $id, RelationshipService $relationshipService)
    {
        $relationshipService->remove($id);

        return $this->respondSuccess(
            Response::HTTP_NO_CONTENT
        );
    }

    /**
     * @api {delete} /api/v1.0/admin/relationship Bulk Delete Relationships
     * @apiVersion 1.0.0
     * @apiName Bulk Delete Relationships
     * @apiGroup Admin Relationships
     * @apiDescription This function is used to bulk remove relationships
     *
     * @apiHeader {String} Content-Type  application/json
     * @apiHeader {String} Authorization Bearer ACCESS_TOKEN
     *
     * @apiParam {Int[]} ids The unique identifier of the relationship TODO: review
     *
     * @apiSuccessExample {json} Sample Response:
     *     HTTP/1.1 204 No Content
     *     {}
     * @apiErrorExample {json} Error-Response:
     *     HTTP/1.1 400 Bad Request
     *     {
     *          "code": 627,
     *          "error": "Relationship not found"
     *     }
     *
     * @Route("", name="api_admin_relationship_delete_bulk", methods={"DELETE"})
     *
     * @param Request $request
     * @param RelationshipService $relationshipService
     * @return JsonResponse
     * @throws \Doctrine\DBAL\ConnectionException
     * @throws \Throwable
     */
    public function deleteBulkAction(Request $request, RelationshipService $relationshipService)
    {
        $ids = $request->get('ids');

        if (!empty($ids)) {
            foreach ($ids as $id) {
                $relationshipService->remove($id);
            }
        }

        return $this->respondSuccess(
            Response::HTTP_NO_CONTENT
        );
    }
}