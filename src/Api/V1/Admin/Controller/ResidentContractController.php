<?php
namespace App\Api\V1\Admin\Controller;

use App\Api\V1\Admin\Service\ContractService;
use App\Api\V1\Common\Controller\BaseController;
use App\Entity\Contract;
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
 * @Route("/api/v1.0/admin/resident/contract")
 *
 * Class ContractController
 * @package App\Api\V1\Admin\Controller
 */
class ResidentContractController extends BaseController
{
    /**
     * @api {get} /api/v1.0/admin/resident/contract/grid Get Contracts Grid
     * @apiVersion 1.0.0
     * @apiName Get Contracts Grid
     * @apiGroup Admin Contracts
     * @apiDescription This function is used to listing contracts
     *
     * @apiHeader {String} Content-Type  application/json
     * @apiHeader {String} Authorization Bearer ACCESS_TOKEN
     *
     * @apiSuccess {Int}      id                   The unique identifier of the contract
     * @apiSuccess {Int}      period               The period of the contract
     * @apiSuccess {String}   start                The start date of the contract
     * @apiSuccess {String}   end                  The end date of the contract
     * @apiSuccess {Int}      type                 The type of the contract
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
     *                  "period": 1,
     *                  "start": "2018-12-11T20:18:00+00:00",
     *                  "end": null,
     *                  "type": "1"
     *              }
     *          ]
     *     }
     *
     * @Route("/grid", name="api_admin_contract_grid", methods={"GET"})
     *
     * @param Request $request
     * @param ContractService $contractService
     * @return JsonResponse|PdfResponse
     * @throws \ReflectionException
     */
    public function gridAction(Request $request, ContractService $contractService)
    {
        return $this->respondGrid(
            $request,
            Contract::class,
            'api_admin_contract_grid',
            $contractService,
            ['resident_id' => $request->get('resident_id')]
        );
    }

    /**
     * @api {options} /api/v1.0/admin/resident/contract/grid Get Contract Grid Options
     * @apiVersion 1.0.0
     * @apiName Get Contract Grid Options
     * @apiGroup Admin Contracts
     * @apiDescription This function is used to describe options of listing
     *
     * @apiHeader {String} Content-Type  application/json
     * @apiHeader {String} Authorization Bearer ACCESS_TOKEN
     *
     * @apiSuccess {Array} options The options of the contract listing
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
     * @Route("/grid", name="api_admin_contract_grid_options", methods={"OPTIONS"})
     *
     * @param Request $request
     * @return JsonResponse
     * @throws \ReflectionException
     */
    public function gridOptionAction(Request $request)
    {
        return $this->getOptionsByGroupName(Contract::class, 'api_admin_contract_grid');
    }

    /**
     * @api {get} /api/v1.0/admin/resident/contract Get Contracts
     * @apiVersion 1.0.0
     * @apiName Get Contracts
     * @apiGroup Admin Contracts
     * @apiDescription This function is used to listing contracts
     *
     * @apiHeader {String} Content-Type  application/json
     * @apiHeader {String} Authorization Bearer ACCESS_TOKEN
     *
     * @apiSuccess {Int}      id                   The unique identifier of the contract
     * @apiSuccess {Object}   resident             The resident of the contract
     * @apiSuccess {Int}      period               The period of the contract
     * @apiSuccess {String}   start                The start date of the contract
     * @apiSuccess {String}   end                  The end date of the contract
     * @apiSuccess {Int}      type                 The type of the contract
     * @apiSuccess {Array}    option               The option data(by type) of the contract
     *
     * @apiSuccessExample {json} Sample Response:
     *     HTTP/1.1 200 OK
     *     {
     *          "page": "1",
     *          "per_page": 10,
     *          "all_pages": 1,
     *          "total": 5,
     *          "data": {
     *                  "option": {
     *                      "state": 1,
     *                      "dining_room": {
     *                          "id": 1,
     *                          "title": "North Dining Room"
     *                      },
     *                      "bed": {
     *                          "id": 1,
     *                          "number": "A"
     *                      },
     *                      "dnr": false,
     *                      "polst": false,
     *                      "ambulatory": false,
     *                      "care_group": 1,
     *                      "care_level": {
     *                          "id": 1,
     *                          "title": "Level 1"
     *                      }
     *                  },
     *                  "id": 1,
     *                  "resident": {
     *                      "id": 1
     *                  },
     *                  "period": 1,
     *                  "start": "2018-12-11T20:18:00+00:00",
     *                  "end": null,
     *                  "type": "1"
     *              }
     *          ]
     *     }
     *
     * @Route("", name="api_admin_contract_list", methods={"GET"})
     *
     * @param Request $request
     * @param ContractService $contractService
     * @return JsonResponse|PdfResponse
     * @throws \Exception
     */
    public function listAction(Request $request, ContractService $contractService)
    {
        return $this->respondList(
            $request,
            Contract::class,
            'api_admin_contract_list',
            $contractService,
            ['resident_id' => $request->get('resident_id')]
        );
    }

    /**
     * @api {get} /api/v1.0/admin/resident/contract/{id} Get Contract
     * @apiVersion 1.0.0
     * @apiName Get Contract
     * @apiGroup Admin Contracts
     * @apiDescription This function is used to get contract
     *
     * @apiHeader {String} Content-Type  application/json
     * @apiHeader {String} Authorization Bearer ACCESS_TOKEN
     *
     * @apiSuccess {Int}      id                   The unique identifier of the contract
     * @apiSuccess {Object}   resident             The resident of the contract
     * @apiSuccess {Int}      period               The period of the contract
     * @apiSuccess {String}   start                The start date of the contract
     * @apiSuccess {String}   end                  The end date of the contract
     * @apiSuccess {Int}      type                 The type of the contract
     * @apiSuccess {Array}    option               The option data(by type) of the contract
     *
     * @apiSuccessExample {json} Sample Response:
     *     HTTP/1.1 200 OK
     *     {
     *          "data": {
     *                  "option": {
     *                      "state": 1,
     *                      "dining_room": {
     *                          "id": 1,
     *                          "title": "North Dining Room"
     *                      },
     *                      "bed": {
     *                          "id": 1,
     *                          "number": "A"
     *                      },
     *                      "dnr": false,
     *                      "polst": false,
     *                      "ambulatory": false,
     *                      "care_group": 1,
     *                      "care_level": {
     *                          "id": 1,
     *                          "title": "Level 1"
     *                      }
     *                  },
     *                  "id": 1,
     *                  "resident": {
     *                      "id": 1
     *                  },
     *                  "period": 1,
     *                  "start": "2018-12-11T20:18:00+00:00",
     *                  "end": null,
     *                  "type": "1"
     *          }
     *     }
     *
     * @Route("/{id}", requirements={"id"="\d+"}, name="api_admin_contract_get", methods={"GET"})
     *
     * @param ContractService $contractService
     * @param $id
     * @return JsonResponse
     */
    public function getAction(Request $request, $id, ContractService $contractService)
    {
        return $this->respondSuccess(
            Response::HTTP_OK,
            '',
            $contractService->getById($id),
            ['api_admin_contract_get']
        );
    }

    /**
     * @api {post} /api/v1.0/admin/resident/contract Add Contract
     * @apiVersion 1.0.0
     * @apiName Add Contract
     * @apiGroup Admin Contracts
     * @apiDescription This function is used to add contract
     *
     * @apiHeader {String} Content-Type  application/x-www-form-urlencoded
     * @apiHeader {String} Authorization Bearer ACCESS_TOKEN
     *
     * @apiParam {Int}     resident_id     The unique identifier of the resident
     * @apiParam {Int}     period          The type of the contract
     * @apiParam {Int}     type            The type of the contract
     * @apiParam {String}  start           The start date of the contract
     * @apiParam {Array}  option          The option data of the contract
     *
     * @apiParamExample {json} Facility Option Request:
     *     {
     *          "resident_id": 1,
     *          "period": 1,
     *          "type": 1,
     *          "start": "2016-10-01",
     *          "option": {
     *              "dining_room_id": 1,
     *              "bed_id": 1,
     *              "dnr": 1,
     *              "polst": 1
     *              "ambulatory": 1,
     *              "care_group": 5,
     *              "care_level_id": 1
     *          }
     *     }
     * @apiParamExample {json} Apartment Option Request:
     *     {
     *          "resident_id": 1,
     *          "period": 1,
     *          "type": 1,
     *          "start": "2016-10-01",
     *          "option": {
     *              "bed_id": 1,
     *          }
     *     }
     * @apiParamExample {json} Region Option Request:
     *     {
     *          "resident_id": 1,
     *          "period": 1,
     *          "type": 1,
     *          "start": "2016-10-01",
     *          "option": {
     *              "region_id": 1,
     *              "csz_id": 1,
     *              "street_address": "7952 Old Auburn Road"
     *              "dnr": 1,
     *              "polst": 1,
     *              "ambulatory": 1,
     *              "care_group": 5,
     *              "care_level": 1
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
     *              "type": "Sorry, this value not be blank."
     *          }
     *     }
     *
     * @Route("", name="api_admin_contract_add", methods={"POST"})
     *
     * @param Request $request
     * @param ContractService $contractService
     * @return JsonResponse
     * @throws \Exception
     */
    public function addAction(Request $request, ContractService $contractService)
    {
        $contractService->add(
            [
                'resident_id' => $request->get('resident_id'),
                'period' => $request->get('period'),
                'type' => $request->get('type'),
                'start' => $request->get('start'),
                'option'        => $request->get('option')
            ]
        );

        return $this->respondSuccess(
            Response::HTTP_CREATED
        );
    }

    /**
     * @api {put} /api/v1.0/admin/resident/contract/{id} Edit Contract
     * @apiVersion 1.0.0
     * @apiName Edit Contract
     * @apiGroup Admin Contracts
     * @apiDescription This function is used to edit contract
     *
     * @apiHeader {String} Content-Type  application/x-www-form-urlencoded
     * @apiHeader {String} Authorization Bearer ACCESS_TOKEN
     *
     * @apiParam {Int}     resident_id     The unique identifier of the resident
     * @apiParam {Int}     period          The type of the contract
     * @apiParam {String}  start           The start date of the contract
     * @apiParam {String}  end             The end date of the contract
     * @apiParam {Array}  option          The option data of the contract
     *
     * @apiParamExample {json} Facility Option Request:
     *     {
     *          "resident_id": 1,
     *          "period": 1,
     *          "start": "2016-10-01",
     *          "end": "",
     *          "option": {
     *              "state": 1,
     *              "dining_room_id": 1,
     *              "bed_id": 1,
     *              "dnr": 1,
     *              "polst": 1
     *              "ambulatory": 1,
     *              "care_group": 5,
     *              "care_level_id": 1
     *          }
     *     }
     * @apiParamExample {json} Apartment Option Request:
     *     {
     *          "resident_id": 1,
     *          "period": 1,
     *          "start": "2016-10-01",
     *          "end": "",
     *          "option": {
     *              "state": 1,
     *              "bed_id": 1
     *          }
     *     }
     * @apiParamExample {json} Region Option Request:
     *     {
     *          "resident_id": 1,
     *          "period": 1,
     *          "start": "2016-10-01",
     *          "end": "",
     *          "option": {
     *              "state": 1,
     *              "region_id": 1,
     *              "csz_id": 1,
     *              "street_address": "7952 Old Auburn Road"
     *              "dnr": 1,
     *              "polst": 1,
     *              "ambulatory": 1,
     *              "care_group": 5,
     *              "care_level": 1
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
     *              "type": "Sorry, this value not be blank."
     *          }
     *     }
     *
     * @Route("/{id}", requirements={"id"="\d+"}, name="api_admin_contract_edit", methods={"PUT"})
     *
     * @param Request $request
     * @param $id
     * @param ContractService $contractService
     * @return JsonResponse
     * @throws \Exception
     */
    public function editAction(Request $request, $id, ContractService $contractService)
    {
        $contractService->edit(
            $id,
            [
                'resident_id' => $request->get('resident_id'),
                'period' => $request->get('period'),
                'start' => $request->get('start'),
                'end' => $request->get('end'),
                'option'        => $request->get('option')
            ]
        );

        return $this->respondSuccess(
            Response::HTTP_CREATED
        );
    }

    /**
     * @api {delete} /api/v1.0/admin/resident/contract/{id} Delete Contract
     * @apiVersion 1.0.0
     * @apiName Delete Contract
     * @apiGroup Admin Contracts
     * @apiDescription This function is used to remove contract
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
     *          "error": "Contract not found"
     *     }
     *
     * @Route("/{id}", requirements={"id"="\d+"}, name="api_admin_contract_delete", methods={"DELETE"})
     *
     * @param $id
     * @param ContractService $contractService
     * @return JsonResponse
     * @throws \Doctrine\DBAL\ConnectionException
     * @throws \Throwable
     */
    public function deleteAction(Request $request, $id, ContractService $contractService)
    {
        $contractService->remove($id);

        return $this->respondSuccess(
            Response::HTTP_NO_CONTENT
        );
    }

    /**
     * @api {delete} /api/v1.0/admin/resident/contract Bulk Delete Contracts
     * @apiVersion 1.0.0
     * @apiName Bulk Delete Contracts
     * @apiGroup Admin Contracts
     * @apiDescription This function is used to bulk remove contracts
     *
     * @apiHeader {String} Content-Type  application/x-www-form-urlencoded
     * @apiHeader {String} Authorization Bearer ACCESS_TOKEN
     *
     * @apiParam {Int[]} ids The unique identifier of the contracts
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
     *          "error": "Contract not found"
     *     }
     *
     * @Route("", name="api_admin_contract_delete_bulk", methods={"DELETE"})
     *
     * @param Request $request
     * @param ContractService $contractService
     * @return JsonResponse
     * @throws \Doctrine\DBAL\ConnectionException
     * @throws \Throwable
     */
    public function deleteBulkAction(Request $request, ContractService $contractService)
    {
        $contractService->removeBulk($request->get('ids'));

        return $this->respondSuccess(
            Response::HTTP_NO_CONTENT
        );
    }

    /**
     * @api {get} /api/v1.0/admin/resident/contract/{id}/active Get Active Contract
     * @apiVersion 1.0.0
     * @apiName Get Active Contract
     * @apiGroup Admin Contracts
     * @apiDescription This function is used to get active contract
     *
     * @apiHeader {String} Content-Type  application/json
     * @apiHeader {String} Authorization Bearer ACCESS_TOKEN
     *
     * @apiSuccess {Int}      id                   The unique identifier of the contract action
     * @apiSuccess {Object}   contract             The contract of the resident
     * @apiSuccess {Int}      type                 The type of the contract
     * @apiSuccess {String}   start                The start date of the contract
     * @apiSuccess {String}   end                  The end date of the contract
     * @apiSuccess {Int}      state                The state of the contract
     * @apiSuccess {Object}   facility_bed         The facility bed of the contract
     * @apiSuccess {Object}   apartment_bed        The apartment bed of the contract
     * @apiSuccess {Object}   region               The region bed of the contract
     * @apiSuccess {Object}   csz                  The csz bed of the region
     * @apiSuccess {String}   address              The address bed of the region
     * @apiSuccess {Boolean}  dnr                  The dnr of the resident
     * @apiSuccess {Boolean}  polst                The polst of the resident
     * @apiSuccess {Boolean}  ambulatory           The ambulatory of the resident
     * @apiSuccess {Int}      care_group           The care group of the resident
     * @apiSuccess {Object}   care_level           The care level of the resident
     *
     * @apiSuccessExample {json} Sample Response:
     *     HTTP/1.1 200 OK
     *     {
     *          "data": {
     *                   "id": 4,
     *                   "contract": {
     *                      "id": 5,
     *                      "type": 1,
     *                      "period": 1,
     *                      "start": "2018-12-11T20:18:00+00:00",
     *                      "end": null
     *                   },
     *                   "start": "2018-12-14T16:45:58+00:00",
     *                   "end": null,
     *                   "state": 1,
     *                   "facility_bed": {
     *                      "id": 25,
     *                      "number": "C"
     *                   },
     *                   "apartment_bed": null,
     *                   "region": null,
     *                   "csz": null,
     *                   "address": null,
     *                   "dnr": false,
     *                   "polst": false,
     *                   "ambulatory": true,
     *                   "care_group": 1,
     *                   "care_level": {
     *                      "id": 1,
     *                      "title": "Level 1"
     *                   }
     *          }
     *     }
     *
     * @Route("/{id}/active", requirements={"id"="\d+"}, name="api_admin_contract_get_active", methods={"GET"})
     *
     * @param ContractService $contractService
     * @param $id
     * @return JsonResponse
     */
    public function getActiveAction(Request $request, $id, ContractService $contractService)
    {
        return $this->respondSuccess(
            Response::HTTP_OK,
            '',
            $contractService->getActiveById($id),
            ['api_admin_contract_get_active']
        );
    }
}
