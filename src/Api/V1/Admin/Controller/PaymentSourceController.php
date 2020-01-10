<?php

namespace App\Api\V1\Admin\Controller;

use App\Annotation\Grant;
use App\Api\V1\Admin\Service\PaymentSourceService;
use App\Api\V1\Common\Controller\BaseController;
use App\Entity\PaymentSource;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\JsonResponse;
use Knp\Bundle\SnappyBundle\Snappy\Response\PdfResponse;

/**
 * @Route("/api/v1.0/admin/payment/source")
 *
 * @Grant(grant="persistence-common-payment_source", level="VIEW")
 *
 * Class PaymentSourceController
 * @package App\Api\V1\Admin\Controller
 */
class PaymentSourceController extends BaseController
{
    /**
     * @Route("/grid", name="api_admin_payment_source_grid", methods={"GET"})
     *
     * @param Request $request
     * @param PaymentSourceService $paymentSourceService
     * @return JsonResponse
     */
    public function gridAction(Request $request, PaymentSourceService $paymentSourceService): JsonResponse
    {
        return $this->respondGrid(
            $request,
            PaymentSource::class,
            'api_admin_payment_source_grid',
            $paymentSourceService
        );
    }

    /**
     * @Route("/grid", name="api_admin_payment_source_grid_options", methods={"OPTIONS"})
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function gridOptionAction(Request $request): JsonResponse
    {
        return $this->getOptionsByGroupName($request, PaymentSource::class, 'api_admin_payment_source_grid');
    }

    /**
     * @Route("", name="api_admin_payment_source_list", methods={"GET"})
     *
     * @param Request $request
     * @param PaymentSourceService $paymentSourceService
     * @return PdfResponse|JsonResponse|Response
     */
    public function listAction(Request $request, PaymentSourceService $paymentSourceService)
    {
        return $this->respondList(
            $request,
            PaymentSource::class,
            'api_admin_payment_source_list',
            $paymentSourceService
        );
    }

    /**
     * @Route("/{id}", requirements={"id"="\d+"}, name="api_admin_payment_source_get", methods={"GET"})
     *
     * @param Request $request
     * @param $id
     * @param PaymentSourceService $paymentSourceService
     * @return JsonResponse
     */
    public function getAction(Request $request, $id, PaymentSourceService $paymentSourceService): JsonResponse
    {
        return $this->respondSuccess(
            Response::HTTP_OK,
            '',
            $paymentSourceService->getById($id),
            ['api_admin_payment_source_get']
        );
    }

    /**
     * @Route("", name="api_admin_payment_source_add", methods={"POST"})
     *
     * @Grant(grant="persistence-common-payment_source", level="ADD")
     *
     * @param Request $request
     * @param PaymentSourceService $paymentSourceService
     * @return JsonResponse
     */
    public function addAction(Request $request, PaymentSourceService $paymentSourceService): JsonResponse
    {
        $id = $paymentSourceService->add(
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
     * @Route("/{id}", requirements={"id"="\d+"}, name="api_admin_payment_source_edit", methods={"PUT"})
     *
     * @Grant(grant="persistence-common-payment_source", level="EDIT")
     *
     * @param Request $request
     * @param $id
     * @param PaymentSourceService $paymentSourceService
     * @return JsonResponse
     */
    public function editAction(Request $request, $id, PaymentSourceService $paymentSourceService): JsonResponse
    {
        $paymentSourceService->edit(
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
     * @Route("/{id}", requirements={"id"="\d+"}, name="api_admin_payment_source_delete", methods={"DELETE"})
     *
     * @Grant(grant="persistence-common-payment_source", level="DELETE")
     *
     * @param Request $request
     * @param $id
     * @param PaymentSourceService $paymentSourceService
     * @return JsonResponse
     */
    public function deleteAction(Request $request, $id, PaymentSourceService $paymentSourceService): JsonResponse
    {
        $paymentSourceService->remove($id);

        return $this->respondSuccess(
            Response::HTTP_NO_CONTENT
        );
    }

    /**
     * @Route("", name="api_admin_payment_source_delete_bulk", methods={"DELETE"})
     *
     * @Grant(grant="persistence-common-payment_source", level="DELETE")
     *
     * @param Request $request
     * @param PaymentSourceService $paymentSourceService
     * @return JsonResponse
     */
    public function deleteBulkAction(Request $request, PaymentSourceService $paymentSourceService): JsonResponse
    {
        $paymentSourceService->removeBulk($request->get('ids'));

        return $this->respondSuccess(
            Response::HTTP_NO_CONTENT
        );
    }

    /**
     * @Route("/related/info", name="api_admin_payment_source_related_info", methods={"POST"})
     *
     * @param Request $request
     * @param PaymentSourceService $paymentSourceService
     * @return JsonResponse
     */
    public function relatedInfoAction(Request $request, PaymentSourceService $paymentSourceService): JsonResponse
    {
        $relatedData = $paymentSourceService->getRelatedInfo($request->get('ids'));

        return $this->respondSuccess(
            Response::HTTP_OK,
            '',
            [$relatedData]
        );
    }
}
