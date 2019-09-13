<?php
namespace App\Api\V1\Admin\Controller;

use App\Api\V1\Admin\Service\SpecialityService;
use App\Api\V1\Common\Controller\BaseController;
use App\Entity\Speciality;
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
 * @Route("/api/v1.0/admin/speciality")
 *
 * @Grant(grant="persistence-common-speciality", level="VIEW")
 *
 * Class SpecialityController
 * @package App\Api\V1\Admin\Controller
 */
class SpecialityController extends BaseController
{
    /**
     * @api {get} /api/v1.0/admin/speciality/grid Get Specialities Grid
     * @apiVersion 1.0.0
     * @apiName Specialities Grid
     * @apiGroup Admin Specialities
     * @apiDescription This function is used to listing specialities
     *
     * @apiHeader {String} Content-Type  application/json
     * @apiHeader {String} Authorization Bearer ACCESS_TOKEN
     *
     * @apiSuccess {Int}      id             The unique identifier of the speciality
     * @apiSuccess {Object}   space          The space of the speciality
     * @apiSuccess {String}   title          The title of the speciality
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
     *                  "title": "Speciality1",
     *                  "space": "alms"
     *              }
     *          ]
     *     }
     *
     * @Route("/grid", name="api_admin_speciality_grid", methods={"GET"})
     *
     * @param Request $request
     * @param SpecialityService $specialityService
     * @return JsonResponse|PdfResponse
     * @throws \ReflectionException
     */
    public function gridAction(Request $request, SpecialityService $specialityService)
    {
        return $this->respondGrid(
            $request,
            Speciality::class,
            'api_admin_speciality_grid',
            $specialityService
        );
    }

    /**
     * @api {options} /api/v1.0/admin/speciality/grid Get Speciality Grid Options
     * @apiVersion 1.0.0
     * @apiName Get Speciality Grid Options
     * @apiGroup Admin Specialities
     * @apiDescription This function is used to describe options of listing
     *
     * @apiHeader {String} Content-Type  application/json
     * @apiHeader {String} Authorization Bearer ACCESS_TOKEN
     *
     * @apiSuccess {Array} options The options of the Speciality listing
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
     * @Route("/grid", name="api_admin_speciality_grid_options", methods={"OPTIONS"})
     *
     * @param Request $request
     * @return JsonResponse
     * @throws \ReflectionException
     */
    public function gridOptionAction(Request $request)
    {
        return $this->getOptionsByGroupName($request, Speciality::class, 'api_admin_speciality_grid');
    }

    /**
     * @api {get} /api/v1.0/admin/speciality Get Specialities
     * @apiVersion 1.0.0
     * @apiName Get Specialities
     * @apiGroup Admin Specialities
     * @apiDescription This function is used to listing specialities
     *
     * @apiHeader {String} Content-Type  application/json
     * @apiHeader {String} Authorization Bearer ACCESS_TOKEN
     *
     * @apiSuccess {Int}      id             The unique identifier of the Speciality
     * @apiSuccess {Object}   space          The space of the Speciality
     * @apiSuccess {String}   title          The title of the Speciality
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
     *                  "title": "Speciality1",
     *                  "space": {
     *                      "id": 1,
     *                      "name": "alms"
     *                  }
     *              }
     *          ]
     *     }
     *
     * @Route("", name="api_admin_speciality_list", methods={"GET"})
     *
     * @param Request $request
     * @param SpecialityService $specialityService
     * @return JsonResponse|PdfResponse
     * @throws \ReflectionException
     */
    public function listAction(Request $request, SpecialityService $specialityService)
    {
        return $this->respondList(
            $request,
            Speciality::class,
            'api_admin_speciality_list',
            $specialityService,
            ['space_id' => $request->get('space_id')]
        );
    }

    /**
     * @api {get} /api/v1.0/admin/speciality/{id} Get Speciality
     * @apiVersion 1.0.0
     * @apiName Get Speciality
     * @apiGroup Admin Specialities
     * @apiDescription This function is used to get Speciality
     *
     * @apiHeader {String} Content-Type  application/json
     * @apiHeader {String} Authorization Bearer ACCESS_TOKEN
     *
     * @apiSuccess {Int}      id             The unique identifier of the Speciality
     * @apiSuccess {Object}   space          The space of the Speciality
     * @apiSuccess {String}   title          The title of the Speciality
     *
     * @apiSuccessExample {json} Sample Response:
     *     HTTP/1.1 200 OK
     *     {
     *          "data": {
     *                  "id": 1,
     *                  "title": "Speciality1",
     *                  "space": {
     *                      "id": 1,
     *                      "name": "alms"
     *                  }
     *          }
     *     }
     *
     * @Route("/{id}", requirements={"id"="\d+"}, name="api_admin_speciality_get", methods={"GET"})
     *
     * @param SpecialityService $specialityService
     * @param $id
     * @return JsonResponse
     */
    public function getAction(Request $request, $id, SpecialityService $specialityService)
    {
        return $this->respondSuccess(
            Response::HTTP_OK,
            '',
            $specialityService->getById($id),
            ['api_admin_speciality_get']
        );
    }

    /**
     * @api {post} /api/v1.0/admin/speciality Add Speciality
     * @apiVersion 1.0.0
     * @apiName Add Speciality
     * @apiGroup Admin Specialities
     * @apiDescription This function is used to add Speciality
     *
     * @apiHeader {String} Content-Type  application/x-www-form-urlencoded
     * @apiHeader {String} Authorization Bearer ACCESS_TOKEN
     *
     * @apiParam {Int}     space_id     The unique identifier of the space
     * @apiParam {String}  title        The number of the Speciality
     *
     * @apiParamExample {json} Request-Example:
     *     {
     *          "space_id": 1,
     *          "title": "Speciality1"
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
     * @Route("", name="api_admin_speciality_add", methods={"POST"})
     *
     * @Grant(grant="persistence-common-speciality", level="ADD")
     *
     * @param Request $request
     * @param SpecialityService $specialityService
     * @return JsonResponse
     * @throws \Throwable
     */
    public function addAction(Request $request, SpecialityService $specialityService)
    {
        $id = $specialityService->add(
            [
                'space_id' => $request->get('space_id'),
                'title'    => $request->get('title')
            ]
        );

        return $this->respondSuccess(
            Response::HTTP_CREATED,
            '',
            [$id]
        );
    }

    /**
     * @api {put} /api/v1.0/admin/speciality/{id} Edit Speciality
     * @apiVersion 1.0.0
     * @apiName Edit Speciality
     * @apiGroup Admin Specialities
     * @apiDescription This function is used to edit speciality
     *
     * @apiHeader {String} Content-Type  application/x-www-form-urlencoded
     * @apiHeader {String} Authorization Bearer ACCESS_TOKEN
     *
     * @apiParam {Int}     space_id     The unique identifier of the space
     * @apiParam {String}  title        The number of the Speciality
     *
     * @apiParamExample {json} Request-Example:
     *     {
     *          "space_id": 1,
     *          "title": "Speciality1"
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
     * @Route("/{id}", requirements={"id"="\d+"}, name="api_admin_speciality_edit", methods={"PUT"})
     *
     * @Grant(grant="persistence-common-speciality", level="EDIT")
     *
     * @param Request $request
     * @param $id
     * @param SpecialityService $specialityService
     * @return JsonResponse
     * @throws \Throwable
     */
    public function editAction(Request $request, $id, SpecialityService $specialityService)
    {
        $specialityService->edit(
            $id,
            [
                'space_id' => $request->get('space_id'),
                'title'    => $request->get('title')
            ]
        );

        return $this->respondSuccess(
            Response::HTTP_CREATED
        );
    }

    /**
     * @api {delete} /api/v1.0/admin/speciality/{id} Delete Speciality
     * @apiVersion 1.0.0
     * @apiName Delete Speciality
     * @apiGroup Admin Specialities
     * @apiDescription This function is used to remove Speciality
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
     *          "error": "Speciality not found"
     *     }
     *
     * @Route("/{id}", requirements={"id"="\d+"}, name="api_admin_speciality_delete", methods={"DELETE"})
     *
     * @Grant(grant="persistence-common-speciality", level="DELETE")
     *
     * @param $id
     * @param SpecialityService $specialityService
     * @return JsonResponse
     * @throws \Doctrine\DBAL\ConnectionException
     * @throws \Throwable
     */
    public function deleteAction(Request $request, $id, SpecialityService $specialityService)
    {
        $specialityService->remove($id);

        return $this->respondSuccess(
            Response::HTTP_NO_CONTENT
        );
    }

    /**
     * @api {delete} /api/v1.0/admin/speciality Bulk Delete Specialities
     * @apiVersion 1.0.0
     * @apiName Bulk Delete Specialities
     * @apiGroup Admin Specialities
     * @apiDescription This function is used to bulk remove specialities
     *
     * @apiHeader {String} Content-Type  application/x-www-form-urlencoded
     * @apiHeader {String} Authorization Bearer ACCESS_TOKEN
     *
     * @apiParam {Int[]} ids The unique identifier of the specialities
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
     *          "error": "Speciality not found"
     *     }
     *
     * @Route("", name="api_admin_speciality_delete_bulk", methods={"DELETE"})
     *
     * @Grant(grant="persistence-common-speciality", level="DELETE")
     *
     * @param Request $request
     * @param SpecialityService $specialityService
     * @return JsonResponse
     * @throws \Doctrine\DBAL\ConnectionException
     * @throws \Throwable
     */
    public function deleteBulkAction(Request $request, SpecialityService $specialityService)
    {
        $specialityService->removeBulk($request->get('ids'));

        return $this->respondSuccess(
            Response::HTTP_NO_CONTENT
        );
    }

    /**
     * @api {post} /api/v1.0/admin/speciality/related/info Speciality related info
     * @apiVersion 1.0.0
     * @apiName Speciality Related Info
     * @apiGroup Admin Specialities
     * @apiDescription This function is used to get speciality related info
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
     *          "error": "Speciality not found"
     *     }
     *
     * @Route("/related/info", name="api_admin_speciality_related_info", methods={"POST"})
     *
     * @param Request $request
     * @param SpecialityService $specialityService
     * @return JsonResponse
     * @throws \Doctrine\DBAL\ConnectionException
     * @throws \Throwable
     */
    public function relatedInfoAction(Request $request, SpecialityService $specialityService)
    {
        $relatedData = $specialityService->getRelatedInfo($request->get('ids'));

        return $this->respondSuccess(
            Response::HTTP_OK,
            '',
            [$relatedData]
        );
    }
}
