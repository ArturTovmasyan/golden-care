<?php

namespace App\Api\V1\Dashboard\Controller;

use App\Api\V1\Common\Controller\BaseController;
use App\Annotation\Permission;
use App\Api\V1\Dashboard\Service\RelationshipService;
use App\Entity\Relationship;
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
 * @Route("/api/v1.0/dashboard/relationship")
 *
 * Class RelationshipController
 * @package App\Api\V1\Dashboard\Controller
 */
class RelationshipController extends BaseController
{
    /**
     * @api {get} /api/v1.0/dashboard/relationship Get Relationships
     * @apiVersion 1.0.0
     * @apiName Get Relationships
     * @apiGroup Dashboard Relationships
     * @apiPermission none
     * @apiDescription This function is used to get user all relationships for dashboard
     *
     * @apiHeader {String} Content-Type  application/json
     * @apiHeader {String} Authorization Bearer ACCESS_TOKEN
     *
     * @apiSuccess {Int}     id   The identifier of the user
     * @apiSuccess {String}  name The name of the relationship
     *
     * @apiSuccessExample {json} Sample Response:
     *     HTTP/1.1 200 OK
     *     {
     *          "page": 1,
     *          "total": 2,
     *          "data": [
     *              {
     *                  "id": 1,
     *                  "name": "Son"
     *              }
     *          ]
     *     }
     *
     * @Route("", name="api_dashboard_relationship_list", methods={"GET"})
     *
     * @param Request $request
     * @param RelationshipService $relationshipService
     * @return \Knp\Bundle\SnappyBundle\Snappy\Response\PdfResponse|JsonResponse
     * @throws \ReflectionException
     */
    public function listAction(Request $request, RelationshipService $relationshipService)
    {
        return $this->respondGrid(
            $request,
            Relationship::class,
            'api_dashboard_relationship_list',
            $relationshipService
        );
    }

    /**
     * @api {options} /api/v1.0/dashboard/space/{space_id}/permission Get Relationship Options
     * @apiVersion 1.0.0
     * @apiName Get Relationship Options
     * @apiGroup Dashboard Relationship
     * @apiPermission none
     * @apiDescription This function is used to describe options of listing
     *
     * @apiHeader {String} Content-Type  application/json
     * @apiHeader {String} Authorization Bearer ACCESS_TOKEN
     *
     * @apiSuccess {Array} options The options of thr relationship listing
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
     * @Route("", name="api_dashboard_relationship_options", methods={"OPTIONS"})
     *
     * @param Request $request
     * @return JsonResponse
     * @throws \ReflectionException
     */
    public function optionAction(Request $request)
    {
        return $this->getOptionsByGroupName(Relationship::class, 'api_dashboard_relationship_list');
    }
}
