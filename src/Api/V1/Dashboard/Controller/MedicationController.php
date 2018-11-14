<?php

namespace App\Api\V1\Dashboard\Controller;

use App\Api\V1\Common\Controller\BaseController;
use App\Annotation\Permission;
use App\Api\V1\Dashboard\Service\MedicationService;
use App\Entity\Medication;
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
 * @Route("/api/v1.0/dashboard/medication")
 *
 * Class MedicationController
 * @package App\Api\V1\Dashboard\Controller
 */
class MedicationController extends BaseController
{
    /**
     * @api {get} /api/v1.0/dashboard/medication Get Medications
     * @apiVersion 1.0.0
     * @apiName Get Medications
     * @apiGroup Dashboard Medications
     * @apiPermission none
     * @apiDescription This function is used to get user all medications for dashboard
     *
     * @apiHeader {String} Content-Type  application/json
     * @apiHeader {String} Authorization Bearer ACCESS_TOKEN
     *
     * @apiSuccess {Int}     id   The identifier of the user
     * @apiSuccess {String}  name The name of the medication
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
     * @Route("", name="api_dashboard_medication_list", methods={"GET"})
     *
     * @param Request $request
     * @param MedicationService $medicationService
     * @return \Knp\Bundle\SnappyBundle\Snappy\Response\PdfResponse|JsonResponse
     * @throws \ReflectionException
     */
    public function listAction(Request $request, MedicationService $medicationService)
    {
        return $this->respondGrid(
            $request,
            Medication::class,
            'api_dashboard_medication_list',
            $medicationService
        );
    }

    /**
     * @api {options} /api/v1.0/dashboard/space/{space_id}/permission Get Medication Options
     * @apiVersion 1.0.0
     * @apiName Get Medication Options
     * @apiGroup Dashboard Medication
     * @apiPermission none
     * @apiDescription This function is used to describe options of listing
     *
     * @apiHeader {String} Content-Type  application/json
     * @apiHeader {String} Authorization Bearer ACCESS_TOKEN
     *
     * @apiSuccess {Array} options The options of thr medication listing
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
     * @Route("", name="api_dashboard_medication_options", methods={"OPTIONS"})
     *
     * @param Request $request
     * @return JsonResponse
     * @throws \ReflectionException
     */
    public function optionAction(Request $request)
    {
        return $this->getOptionsByGroupName(Medication::class, 'api_dashboard_medication_list');
    }
}
