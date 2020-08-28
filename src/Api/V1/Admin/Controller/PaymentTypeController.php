<?php

namespace App\Api\V1\Admin\Controller;

use App\Annotation\Grant;
use App\Api\V1\Admin\Service\PaymentTypeService;
use App\Api\V1\Common\Controller\BaseController;
use App\Entity\PaymentType;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\JsonResponse;
use Knp\Bundle\SnappyBundle\Snappy\Response\PdfResponse;

/**
 * @Route("/api/v1.0/admin/payment-type")
 *
 * @Grant(grant="persistence-common-payment_type", level="VIEW")
 *
 * Class PaymentTypeController
 * @package App\Api\V1\Admin\Controller
 */
class PaymentTypeController extends BaseController
{
    /**
     * @Route("/grid", name="api_admin_payment_type_grid", methods={"GET"})
     *
     * @param Request $request
     * @param PaymentTypeService $paymentTypeService
     * @return JsonResponse
     */
    public function gridAction(Request $request, PaymentTypeService $paymentTypeService): JsonResponse
    {
        return $this->respondGrid(
            $request,
            PaymentType::class,
            'api_admin_payment_type_grid',
            $paymentTypeService
        );
    }

    /**
     * @Route("/grid", name="api_admin_payment_type_grid_options", methods={"OPTIONS"})
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function gridOptionAction(Request $request): JsonResponse
    {
        return $this->getOptionsByGroupName($request, PaymentType::class, 'api_admin_payment_type_grid');
    }

    /**
     * @Route("", name="api_admin_payment_type_list", methods={"GET"})
     *
     * @param Request $request
     * @param PaymentTypeService $paymentTypeService
     * @return PdfResponse|JsonResponse|Response
     */
    public function listAction(Request $request, PaymentTypeService $paymentTypeService)
    {
        return $this->respondList(
            $request,
            PaymentType::class,
            'api_admin_payment_type_list',
            $paymentTypeService
        );
    }

    /**
     * @Route("/{id}", requirements={"id"="\d+"}, name="api_admin_payment_type_get", methods={"GET"})
     *
     * @param Request $request
     * @param $id
     * @param PaymentTypeService $paymentTypeService
     * @return JsonResponse
     */
    public function getAction(Request $request, $id, PaymentTypeService $paymentTypeService): JsonResponse
    {
        return $this->respondSuccess(
            Response::HTTP_OK,
            '',
            $paymentTypeService->getById($id),
            ['api_admin_payment_type_get']
        );
    }

    /**
     * @Route("", name="api_admin_payment_type_add", methods={"POST"})
     *
     * @Grant(grant="persistence-common-payment_type", level="ADD")
     *
     * @param Request $request
     * @param PaymentTypeService $paymentTypeService
     * @return JsonResponse
     */
    public function addAction(Request $request, PaymentTypeService $paymentTypeService): JsonResponse
    {
        $id = $paymentTypeService->add(
            [
                'title' => $request->get('title'),
                'space_id' => $request->get('space_id')
            ]
        );

        return $this->respondSuccess(
            Response::HTTP_CREATED,
            '',
            [$id]
        );
    }

    /**
     * @Route("/{id}", requirements={"id"="\d+"}, name="api_admin_payment_type_edit", methods={"PUT"})
     *
     * @Grant(grant="persistence-common-payment_type", level="EDIT")
     *
     * @param Request $request
     * @param $id
     * @param PaymentTypeService $paymentTypeService
     * @return JsonResponse
     */
    public function editAction(Request $request, $id, PaymentTypeService $paymentTypeService): JsonResponse
    {
        $paymentTypeService->edit(
            $id,
            [
                'title' => $request->get('title'),
                'space_id' => $request->get('space_id')
            ]
        );

        return $this->respondSuccess(
            Response::HTTP_CREATED
        );
    }

    /**
     * @Route("/{id}", requirements={"id"="\d+"}, name="api_admin_payment_type_delete", methods={"DELETE"})
     *
     * @Grant(grant="persistence-common-payment_type", level="DELETE")
     *
     * @param Request $request
     * @param $id
     * @param PaymentTypeService $paymentTypeService
     * @return JsonResponse
     */
    public function deleteAction(Request $request, $id, PaymentTypeService $paymentTypeService): JsonResponse
    {
        $paymentTypeService->remove($id);

        return $this->respondSuccess(
            Response::HTTP_NO_CONTENT
        );
    }

    /**
     * @Route("", name="api_admin_payment_type_delete_bulk", methods={"DELETE"})
     *
     * @Grant(grant="persistence-common-payment_type", level="DELETE")
     *
     * @param Request $request
     * @param PaymentTypeService $paymentTypeService
     * @return JsonResponse
     */
    public function deleteBulkAction(Request $request, PaymentTypeService $paymentTypeService): JsonResponse
    {
        $paymentTypeService->removeBulk($request->get('ids'));

        return $this->respondSuccess(
            Response::HTTP_NO_CONTENT
        );
    }

    /**
     * @Route("/related/info", name="api_admin_payment_type_related_info", methods={"POST"})
     *
     * @param Request $request
     * @param PaymentTypeService $paymentTypeService
     * @return JsonResponse
     */
    public function relatedInfoAction(Request $request, PaymentTypeService $paymentTypeService): JsonResponse
    {
        $relatedData = $paymentTypeService->getRelatedInfo($request->get('ids'));

        return $this->respondSuccess(
            Response::HTTP_OK,
            '',
            [$relatedData]
        );
    }
}
