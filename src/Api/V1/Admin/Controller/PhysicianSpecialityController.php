<?php
namespace App\Api\V1\Admin\Controller;

use App\Api\V1\Admin\Service\PhysicianSpecialityService;
use App\Api\V1\Common\Controller\BaseController;
use App\Entity\PhysicianSpeciality;
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
 * @Route("/api/v1.0/admin/physician/speciality")
 *
 * Class PhysicianSpecialityController
 * @package App\Api\V1\Admin\Controller
 */
class PhysicianSpecialityController extends BaseController
{
    /**
     * @api {get} /api/v1.0/admin/physician/speciality/grid Get PhysicianSpeciality Grid
     * @apiVersion 1.0.0
     * @apiName Get PhysicianSpeciality Grid
     * @apiGroup Admin Physician Specialities
     * @apiDescription This function is used to listing physicianSpecialities
     *
     * @apiHeader {String} Content-Type  application/json
     * @apiHeader {String} Authorization Bearer ACCESS_TOKEN
     *
     * @apiSuccess {Int}      id          The unique identifier of the physicianSpeciality
     * @apiSuccess {Object}   physician   The resident of the physicianSpeciality
     * @apiSuccess {Object}   speciality  The allergen of the physicianSpeciality
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
     *                   "id": 1,
     *                   "physician": "Joe Cole",
     *                   "speciality": "Doctor"
     *              }
     *          ]
     *     }
     *
     * @Route("/grid", name="api_admin_physician_speciality_grid", methods={"GET"})
     *
     * @param Request $request
     * @param PhysicianSpecialityService $physicianSpecialityService
     * @return JsonResponse|PdfResponse
     * @throws \ReflectionException
     */
    public function gridAction(Request $request, PhysicianSpecialityService $physicianSpecialityService)
    {
        return $this->respondGrid(
            $request,
            PhysicianSpeciality::class,
            'api_admin_physician_speciality_grid',
            $physicianSpecialityService,
            ['physician_id' => $request->get('physician_id')]
        );
    }

    /**
     * @api {options} /api/v1.0/admin/physician/speciality/grid Get PhysicianSpeciality Grid Options
     * @apiVersion 1.0.0
     * @apiName Get PhysicianSpeciality Grid Options
     * @apiGroup Admin Physician Specialities
     * @apiDescription This function is used to describe options of listing
     *
     * @apiHeader {String} Content-Type  application/json
     * @apiHeader {String} Authorization Bearer ACCESS_TOKEN
     *
     * @apiSuccess {Array} options The options of the physicianSpeciality listing
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
     * @Route("/grid", name="api_admin_physician_speciality_grid_options", methods={"OPTIONS"})
     *
     * @param Request $request
     * @return JsonResponse
     * @throws \ReflectionException
     */
    public function gridOptionAction(Request $request)
    {
        return $this->getOptionsByGroupName(PhysicianSpeciality::class, 'api_admin_physician_speciality_grid');
    }

    /**
     * @api {get} /api/v1.0/admin/physician/speciality Get PhysicianSpecialities
     * @apiVersion 1.0.0
     * @apiName Get PhysicianSpecialities
     * @apiGroup Admin Physician Specialities
     * @apiDescription This function is used to listing physicianSpecialities
     *
     * @apiHeader {String} Content-Type  application/json
     * @apiHeader {String} Authorization Bearer ACCESS_TOKEN
     *
     * @apiSuccess {Int}      id                   The unique identifier of the physicianSpeciality
     * @apiSuccess {Object}   physician            The physician of the physicianSpeciality
     * @apiSuccess {Object}   speciality           The speciality of the physicianSpeciality
     *
     * @apiSuccessExample {json} Sample Response:
     *     HTTP/1.1 200 OK
     *     [
     *              {
     *                  "id": 1,
     *                  "physician": {
     *                      "id": 1,
     *                      "first_name": "Joe",
     *                      "last_name": "Cole"
     *                  },
     *                  "speciality": {
     *                      "id": 1,
     *                      "title": "Doctor"
     *                  }
     *              }
     *     ]
     *
     * @Route("", name="api_admin_physician_speciality_list", methods={"GET"})
     *
     * @param Request $request
     * @param PhysicianSpecialityService $physicianSpecialityService
     * @return JsonResponse|PdfResponse
     * @throws \ReflectionException
     */
    public function listAction(Request $request, PhysicianSpecialityService $physicianSpecialityService)
    {
        return $this->respondList(
            $request,
            PhysicianSpeciality::class,
            'api_admin_physician_speciality_list',
            $physicianSpecialityService,
            ['physician_id' => $request->get('physician_id')]
        );
    }

    /**
     * @api {get} /api/v1.0/admin/physician/speciality/{id} Get PhysicianSpeciality
     * @apiVersion 1.0.0
     * @apiName Get PhysicianSpeciality
     * @apiGroup Admin Physician Specialities
     * @apiDescription This function is used to get physicianSpeciality
     *
     * @apiHeader {String} Content-Type  application/json
     * @apiHeader {String} Authorization Bearer ACCESS_TOKEN
     *
     * @apiSuccess {Int}      id                   The unique identifier of the physicianSpeciality
     * @apiSuccess {Object}   physician            The physician of the physicianSpeciality
     * @apiSuccess {Object}   speciality           The speciality of the physicianSpeciality
     *
     * @apiSuccessExample {json} Sample Response:
     *     HTTP/1.1 200 OK
     *     {
     *          "data": {
     *                  "id": 1,
     *                  "physician": {
     *                      "id": 1,
     *                      "first_name": "Joe",
     *                      "last_name": "Cole"
     *                  },
     *                  "speciality": {
     *                      "id": 1,
     *                      "title": "Doctor"
     *                  }
     *          }
     *     }
     *
     * @Route("/{id}", requirements={"id"="\d+"}, name="api_admin_physician_speciality_get", methods={"GET"})
     *
     * @param PhysicianSpecialityService $physicianSpecialityService
     * @param $id
     * @return JsonResponse
     */
    public function getAction(Request $request, $id, PhysicianSpecialityService $physicianSpecialityService)
    {
        return $this->respondSuccess(
            Response::HTTP_OK,
            '',
            $physicianSpecialityService->getById($id),
            ['api_admin_physician_speciality_get']
        );
    }

    /**
     * @api {post} /api/v1.0/admin/physician/speciality Add PhysicianSpeciality
     * @apiVersion 1.0.0
     * @apiName Add PhysicianSpeciality
     * @apiGroup Admin Physician Specialities
     * @apiDescription This function is used to add physicianSpeciality
     *
     * @apiHeader {String} Content-Type  application/x-www-form-urlencoded
     * @apiHeader {String} Authorization Bearer ACCESS_TOKEN
     *
     * @apiParam {Int}     physician_id          The unique identifier of the resident
     * @apiParam {Int}     speciality_id         The unique identifier of the speciality in select mode
     * @apiParam {Object}  speciality            The new speciality in add new mode
     *
     * @apiParamExample {json} Request-Example:
     *     {
     *          "physician_id": 1,
     *          "speciality_id": 1,
     *          "speciality": {
     *               "title": "Doctor"
     *          }
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
     *              "speciality_id": "Sorry, this value not be blank."
     *          }
     *     }
     *
     * @Route("", name="api_admin_physician_speciality_add", methods={"POST"})
     *
     * @param Request $request
     * @param PhysicianSpecialityService $physicianSpecialityService
     * @return JsonResponse
     * @throws \Exception
     */
    public function addAction(Request $request, PhysicianSpecialityService $physicianSpecialityService)
    {
        $physicianSpecialityService->add(
            [
                'physician_id'  => $request->get('physician_id'),
                'speciality_id' => $request->get('speciality_id'),
                'speciality'    => $request->get('speciality')
            ]
        );

        return $this->respondSuccess(
            Response::HTTP_CREATED
        );
    }

    /**
     * @api {put} /api/v1.0/admin/physician/speciality/{id} Edit PhysicianSpeciality
     * @apiVersion 1.0.0
     * @apiName Edit PhysicianSpeciality
     * @apiGroup Admin Physician Specialities
     * @apiDescription This function is used to edit physicianSpeciality
     *
     * @apiHeader {String} Content-Type  application/x-www-form-urlencoded
     * @apiHeader {String} Authorization Bearer ACCESS_TOKEN
     *
     * @apiParam {Int}     physician_id          The unique identifier of the physician
     * @apiParam {Int}     speciality_id         The unique identifier of the speciality in select mode
     * @apiParam {Object}  speciality            The new speciality in add new mode
     *
     * @apiParamExample {json} Request-Example:
     *     {
     *          "physician_id": 1,
     *          "speciality_id": 1,
     *          "speciality": {
     *               "title": "Doctor"
     *          }
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
     *              "speciality_id": "Sorry, this value not be blank."
     *          }
     *     }
     *
     * @Route("/{id}", requirements={"id"="\d+"}, name="api_admin_physician_speciality_edit", methods={"PUT"})
     *
     * @param Request $request
     * @param $id
     * @param PhysicianSpecialityService $physicianSpecialityService
     * @return JsonResponse
     * @throws \Exception
     */
    public function editAction(Request $request, $id, PhysicianSpecialityService $physicianSpecialityService)
    {
        $physicianSpecialityService->edit(
            $id,
            [
                'physician_id'  => $request->get('physician_id'),
                'speciality_id' => $request->get('speciality_id'),
                'speciality'    => $request->get('speciality')
            ]
        );

        return $this->respondSuccess(
            Response::HTTP_CREATED
        );
    }

    /**
     * @api {delete} /api/v1.0/admin/physician/speciality/{id} Delete PhysicianSpeciality
     * @apiVersion 1.0.0
     * @apiName Delete PhysicianSpeciality
     * @apiGroup Admin Physician Specialities
     * @apiDescription This function is used to remove physicianSpeciality
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
     *          "error": "PhysicianSpeciality not found"
     *     }
     *
     * @Route("/{id}", requirements={"id"="\d+"}, name="api_admin_physician_speciality_delete", methods={"DELETE"})
     *
     * @param $id
     * @param PhysicianSpecialityService $physicianSpecialityService
     * @return JsonResponse
     * @throws \Doctrine\DBAL\ConnectionException
     * @throws \Throwable
     */
    public function deleteAction(Request $request, $id, PhysicianSpecialityService $physicianSpecialityService)
    {
        $physicianSpecialityService->remove($id);

        return $this->respondSuccess(
            Response::HTTP_NO_CONTENT
        );
    }

    /**
     * @api {delete} /api/v1.0/admin/physician/speciality Bulk Delete PhysicianSpecialities
     * @apiVersion 1.0.0
     * @apiName Bulk Delete PhysicianSpecialities
     * @apiGroup Admin Physician Specialities
     * @apiDescription This function is used to bulk remove physicianSpecialities
     *
     * @apiHeader {String} Content-Type  application/x-www-form-urlencoded
     * @apiHeader {String} Authorization Bearer ACCESS_TOKEN
     *
     * @apiParam {Int[]} ids The unique identifier of the physicianSpecialities
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
     *          "error": "PhysicianSpeciality not found"
     *     }
     *
     * @Route("", name="api_admin_physician_speciality_delete_bulk", methods={"DELETE"})
     *
     * @param Request $request
     * @param PhysicianSpecialityService $physicianSpecialityService
     * @return JsonResponse
     * @throws \Doctrine\DBAL\ConnectionException
     * @throws \Throwable
     */
    public function deleteBulkAction(Request $request, PhysicianSpecialityService $physicianSpecialityService)
    {
        $physicianSpecialityService->removeBulk($request->get('ids'));

        return $this->respondSuccess(
            Response::HTTP_NO_CONTENT
        );
    }
}
