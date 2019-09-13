<?php
namespace App\Api\V1\Admin\Controller;

use App\Api\V1\Admin\Service\ResponsiblePersonRoleService;
use App\Api\V1\Common\Controller\BaseController;
use App\Entity\ResponsiblePersonRole;
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
 * @Route("/api/v1.0/admin/responsible-person-role")
 *
 * @Grant(grant="persistence-common-responsible-person-role", level="VIEW")
 *
 * Class ResponsiblePersonRoleController
 * @package App\Api\V1\Admin\Controller
 */
class ResponsiblePersonRoleController extends BaseController
{
    /**
     * @api {get} /api/v1.0/admin/responsible-person-role/grid Get ResponsiblePersonRoles Grid
     * @apiVersion 1.0.0
     * @apiName ResponsiblePersonRoles Grid
     * @apiGroup Admin ResponsiblePersonRoles
     * @apiDescription This function is used to listing ResponsiblePersonRoles
     *
     * @apiHeader {String} Content-Type  application/json
     * @apiHeader {String} Authorization Bearer ACCESS_TOKEN
     *
     * @apiSuccess {Int}      id             The unique identifier of the responsible person role
     * @apiSuccess {Object}   space          The space of the responsible person role
     * @apiSuccess {String}   title          The title of the responsible person role
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
     *                  "title": "ResponsiblePersonRole1",
     *                  "space": "alms"
     *              }
     *          ]
     *     }
     *
     * @Route("/grid", name="api_admin_responsible_person_role_grid", methods={"GET"})
     *
     * @param Request $request
     * @param ResponsiblePersonRoleService $responsiblePersonRoleService
     * @return JsonResponse|PdfResponse
     * @throws \ReflectionException
     */
    public function gridAction(Request $request, ResponsiblePersonRoleService $responsiblePersonRoleService)
    {
        return $this->respondGrid(
            $request,
            ResponsiblePersonRole::class,
            'api_admin_responsible_person_role_grid',
            $responsiblePersonRoleService
        );
    }

    /**
     * @api {options} /api/v1.0/admin/responsible-person-role/grid Get ResponsiblePersonRole Grid Options
     * @apiVersion 1.0.0
     * @apiName Get ResponsiblePersonRole Grid Options
     * @apiGroup Admin ResponsiblePersonRoles
     * @apiDescription This function is used to describe options of listing
     *
     * @apiHeader {String} Content-Type  application/json
     * @apiHeader {String} Authorization Bearer ACCESS_TOKEN
     *
     * @apiSuccess {Array} options The options of the ResponsiblePersonRole listing
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
     * @Route("/grid", name="api_admin_responsible_person_role_grid_options", methods={"OPTIONS"})
     *
     * @param Request $request
     * @return JsonResponse
     * @throws \ReflectionException
     */
    public function gridOptionAction(Request $request)
    {
        return $this->getOptionsByGroupName($request, ResponsiblePersonRole::class, 'api_admin_responsible_person_role_grid');
    }

    /**
     * @api {get} /api/v1.0/admin/responsible-person-role Get ResponsiblePersonRoles
     * @apiVersion 1.0.0
     * @apiName Get ResponsiblePersonRoles
     * @apiGroup Admin ResponsiblePersonRoles
     * @apiDescription This function is used to listing ResponsiblePersonRoles
     *
     * @apiHeader {String} Content-Type  application/json
     * @apiHeader {String} Authorization Bearer ACCESS_TOKEN
     *
     * @apiSuccess {Int}      id             The unique identifier of the ResponsiblePersonRole
     * @apiSuccess {Object}   space          The space of the ResponsiblePersonRole
     * @apiSuccess {String}   title          The title of the ResponsiblePersonRole
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
     *                  "title": "ResponsiblePersonRole1",
     *                  "space": {
     *                      "id": 1,
     *                      "name": "alms"
     *                  }
     *              }
     *          ]
     *     }
     *
     * @Route("", name="api_admin_responsible_person_role_list", methods={"GET"})
     *
     * @param Request $request
     * @param ResponsiblePersonRoleService $responsiblePersonRoleService
     * @return JsonResponse|PdfResponse
     * @throws \ReflectionException
     */
    public function listAction(Request $request, ResponsiblePersonRoleService $responsiblePersonRoleService)
    {
        return $this->respondList(
            $request,
            ResponsiblePersonRole::class,
            'api_admin_responsible_person_role_list',
            $responsiblePersonRoleService,
            ['space_id' => $request->get('space_id')]
        );
    }

    /**
     * @api {get} /api/v1.0/admin/responsible-person-role/{id} Get ResponsiblePersonRole
     * @apiVersion 1.0.0
     * @apiName Get ResponsiblePersonRole
     * @apiGroup Admin ResponsiblePersonRoles
     * @apiDescription This function is used to get ResponsiblePersonRole
     *
     * @apiHeader {String} Content-Type  application/json
     * @apiHeader {String} Authorization Bearer ACCESS_TOKEN
     *
     * @apiSuccess {Int}      id             The unique identifier of the ResponsiblePersonRole
     * @apiSuccess {Object}   space          The space of the ResponsiblePersonRole
     * @apiSuccess {String}   title          The title of the ResponsiblePersonRole
     *
     * @apiSuccessExample {json} Sample Response:
     *     HTTP/1.1 200 OK
     *     {
     *          "data": {
     *                  "id": 1,
     *                  "title": "ResponsiblePersonRole1",
     *                  "space": {
     *                      "id": 1,
     *                      "name": "alms"
     *                  }
     *          }
     *     }
     *
     * @Route("/{id}", requirements={"id"="\d+"}, name="api_admin_responsible_person_role_get", methods={"GET"})
     *
     * @param ResponsiblePersonRoleService $responsiblePersonRoleService
     * @param $id
     * @return JsonResponse
     */
    public function getAction(Request $request, $id, ResponsiblePersonRoleService $responsiblePersonRoleService)
    {
        return $this->respondSuccess(
            Response::HTTP_OK,
            '',
            $responsiblePersonRoleService->getById($id),
            ['api_admin_responsible_person_role_get']
        );
    }

    /**
     * @api {post} /api/v1.0/admin/responsible-person-role Add ResponsiblePersonRole
     * @apiVersion 1.0.0
     * @apiName Add ResponsiblePersonRole
     * @apiGroup Admin ResponsiblePersonRoles
     * @apiDescription This function is used to add ResponsiblePersonRole
     *
     * @apiHeader {String} Content-Type  application/x-www-form-urlencoded
     * @apiHeader {String} Authorization Bearer ACCESS_TOKEN
     *
     * @apiParam {Int}     space_id     The unique identifier of the space
     * @apiParam {String}  title        The number of the ResponsiblePersonRole
     *
     * @apiParamExample {json} Request-Example:
     *     {
     *          "space_id": 1,
     *          "title": "ResponsiblePersonRole1"
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
     *              "title": "Sorry, this value not be blank."
     *          }
     *     }
     *
     * @Route("", name="api_admin_responsible_person_role_add", methods={"POST"})
     *
     * @Grant(grant="persistence-common-responsible-person-role", level="ADD")
     *
     * @param Request $request
     * @param ResponsiblePersonRoleService $responsiblePersonRoleService
     * @return JsonResponse
     * @throws \Throwable
     */
    public function addAction(Request $request, ResponsiblePersonRoleService $responsiblePersonRoleService)
    {
        $id = $responsiblePersonRoleService->add(
            [
                'space_id'    => $request->get('space_id'),
                'title'       => $request->get('title'),
                'icon'        => $request->get('icon'),
                'emergency'   => $request->get('emergency'),
                'financially' => $request->get('financially'),
            ]
        );

        return $this->respondSuccess(
            Response::HTTP_CREATED,
            '',
            [$id]
        );
    }

    /**
     * @api {put} /api/v1.0/admin/responsible-person-role/{id} Edit ResponsiblePersonRole
     * @apiVersion 1.0.0
     * @apiName Edit ResponsiblePersonRole
     * @apiGroup Admin ResponsiblePersonRoles
     * @apiDescription This function is used to edit responsible person role
     *
     * @apiHeader {String} Content-Type  application/x-www-form-urlencoded
     * @apiHeader {String} Authorization Bearer ACCESS_TOKEN
     *
     * @apiParam {Int}     space_id     The unique identifier of the space
     * @apiParam {String}  title        The number of the ResponsiblePersonRole
     *
     * @apiParamExample {json} Request-Example:
     *     {
     *          "space_id": 1,
     *          "title": "ResponsiblePersonRole1"
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
     *              "title": "Sorry, this value not be blank."
     *          }
     *     }
     *
     * @Route("/{id}", requirements={"id"="\d+"}, name="api_admin_responsible_person_role_edit", methods={"PUT"})
     *
     * @Grant(grant="persistence-common-responsible-person-role", level="EDIT")
     *
     * @param Request $request
     * @param $id
     * @param ResponsiblePersonRoleService $responsiblePersonRoleService
     * @return JsonResponse
     * @throws \Throwable
     */
    public function editAction(Request $request, $id, ResponsiblePersonRoleService $responsiblePersonRoleService)
    {
        $responsiblePersonRoleService->edit(
            $id,
            [
                'space_id'    => $request->get('space_id'),
                'title'       => $request->get('title'),
                'icon'        => $request->get('icon'),
                'emergency'   => $request->get('emergency'),
                'financially' => $request->get('financially'),
            ]
        );

        return $this->respondSuccess(
            Response::HTTP_CREATED
        );
    }

    /**
     * @api {delete} /api/v1.0/admin/responsible-person-role/{id} Delete ResponsiblePersonRole
     * @apiVersion 1.0.0
     * @apiName Delete ResponsiblePersonRole
     * @apiGroup Admin ResponsiblePersonRoles
     * @apiDescription This function is used to remove ResponsiblePersonRole
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
     *          "error": "ResponsiblePersonRole not found"
     *     }
     *
     * @Route("/{id}", requirements={"id"="\d+"}, name="api_admin_responsible_person_role_delete", methods={"DELETE"})
     *
     * @Grant(grant="persistence-common-responsible-person-role", level="DELETE")
     *
     * @param $id
     * @param ResponsiblePersonRoleService $responsiblePersonRoleService
     * @return JsonResponse
     * @throws \Doctrine\DBAL\ConnectionException
     * @throws \Throwable
     */
    public function deleteAction(Request $request, $id, ResponsiblePersonRoleService $responsiblePersonRoleService)
    {
        $responsiblePersonRoleService->remove($id);

        return $this->respondSuccess(
            Response::HTTP_NO_CONTENT
        );
    }

    /**
     * @api {delete} /api/v1.0/admin/responsible-person-role Bulk Delete ResponsiblePersonRoles
     * @apiVersion 1.0.0
     * @apiName Bulk Delete ResponsiblePersonRoles
     * @apiGroup Admin ResponsiblePersonRoles
     * @apiDescription This function is used to bulk remove ResponsiblePersonRoles
     *
     * @apiHeader {String} Content-Type  application/x-www-form-urlencoded
     * @apiHeader {String} Authorization Bearer ACCESS_TOKEN
     *
     * @apiParam {Int[]} ids The unique identifier of the ResponsiblePersonRoles
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
     *          "error": "ResponsiblePersonRole not found"
     *     }
     *
     * @Route("", name="api_admin_responsible_person_role_delete_bulk", methods={"DELETE"})
     *
     * @Grant(grant="persistence-common-responsible-person-role", level="DELETE")
     *
     * @param Request $request
     * @param ResponsiblePersonRoleService $responsiblePersonRoleService
     * @return JsonResponse
     * @throws \Doctrine\DBAL\ConnectionException
     * @throws \Throwable
     */
    public function deleteBulkAction(Request $request, ResponsiblePersonRoleService $responsiblePersonRoleService)
    {
        $responsiblePersonRoleService->removeBulk($request->get('ids'));

        return $this->respondSuccess(
            Response::HTTP_NO_CONTENT
        );
    }

    /**
     * @api {post} /api/v1.0/admin/responsible-person-role/related/info ResponsiblePersonRole related info
     * @apiVersion 1.0.0
     * @apiName ResponsiblePersonRole Related Info
     * @apiGroup Admin ResponsiblePersonRoles
     * @apiDescription This function is used to get responsiblePersonRole related info
     *
     * @apiHeader {String} Content-Type  application/x-www-form-urlencoded
     * @apiHeader {String} Authorization Bearer ACCESS_TOKEN
     *
     * @apiParam {Int[]} ids The unique identifier of the facilities
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
     *          "error": "ResponsiblePersonRole not found"
     *     }
     *
     * @Route("/related/info", name="api_admin_responsible_person_role_related_info", methods={"POST"})
     *
     * @param Request $request
     * @param ResponsiblePersonRoleService $responsiblePersonRoleService
     * @return JsonResponse
     * @throws \Doctrine\DBAL\ConnectionException
     * @throws \Throwable
     */
    public function relatedInfoAction(Request $request, ResponsiblePersonRoleService $responsiblePersonRoleService)
    {
        $relatedData = $responsiblePersonRoleService->getRelatedInfo($request->get('ids'));

        return $this->respondSuccess(
            Response::HTTP_OK,
            '',
            [$relatedData]
        );
    }
}
