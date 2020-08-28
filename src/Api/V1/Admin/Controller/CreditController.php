<?php

namespace App\Api\V1\Admin\Controller;

use App\Annotation\Grant;
use App\Api\V1\Admin\Service\CreditService;
use App\Api\V1\Common\Controller\BaseController;
use App\Entity\Credit;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\JsonResponse;
use Knp\Bundle\SnappyBundle\Snappy\Response\PdfResponse;

/**
 * @Route("/api/v1.0/admin/credit")
 *
 * @Grant(grant="persistence-common-credit", level="VIEW")
 *
 * Class CreditController
 * @package App\Api\V1\Admin\Controller
 */
class CreditController extends BaseController
{
    /**
     * @Route("/grid", name="api_admin_credit_grid", methods={"GET"})
     *
     * @param Request $request
     * @param CreditService $creditService
     * @return JsonResponse
     */
    public function gridAction(Request $request, CreditService $creditService): JsonResponse
    {
        return $this->respondGrid(
            $request,
            Credit::class,
            'api_admin_credit_grid',
            $creditService
        );
    }

    /**
     * @Route("/grid", name="api_admin_credit_grid_options", methods={"OPTIONS"})
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function gridOptionAction(Request $request): JsonResponse
    {
        return $this->getOptionsByGroupName($request, Credit::class, 'api_admin_credit_grid');
    }

    /**
     * @Route("", name="api_admin_credit_list", methods={"GET"})
     *
     * @param Request $request
     * @param CreditService $creditService
     * @return PdfResponse|JsonResponse|Response
     */
    public function listAction(Request $request, CreditService $creditService)
    {
        return $this->respondList(
            $request,
            Credit::class,
            'api_admin_credit_list',
            $creditService
        );
    }

    /**
     * @Route("/{id}", requirements={"id"="\d+"}, name="api_admin_credit_get", methods={"GET"})
     *
     * @param Request $request
     * @param $id
     * @param CreditService $creditService
     * @return JsonResponse
     */
    public function getAction(Request $request, $id, CreditService $creditService): JsonResponse
    {
        return $this->respondSuccess(
            Response::HTTP_OK,
            '',
            $creditService->getById($id),
            ['api_admin_credit_get']
        );
    }

    /**
     * @Route("", name="api_admin_credit_add", methods={"POST"})
     *
     * @Grant(grant="persistence-common-credit", level="ADD")
     *
     * @param Request $request
     * @param CreditService $creditService
     * @return JsonResponse
     */
    public function addAction(Request $request, CreditService $creditService): JsonResponse
    {
        $id = $creditService->add(
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
     * @Route("/{id}", requirements={"id"="\d+"}, name="api_admin_credit_edit", methods={"PUT"})
     *
     * @Grant(grant="persistence-common-credit", level="EDIT")
     *
     * @param Request $request
     * @param $id
     * @param CreditService $creditService
     * @return JsonResponse
     */
    public function editAction(Request $request, $id, CreditService $creditService): JsonResponse
    {
        $creditService->edit(
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
     * @Route("/{id}", requirements={"id"="\d+"}, name="api_admin_credit_delete", methods={"DELETE"})
     *
     * @Grant(grant="persistence-common-credit", level="DELETE")
     *
     * @param Request $request
     * @param $id
     * @param CreditService $creditService
     * @return JsonResponse
     */
    public function deleteAction(Request $request, $id, CreditService $creditService): JsonResponse
    {
        $creditService->remove($id);

        return $this->respondSuccess(
            Response::HTTP_NO_CONTENT
        );
    }

    /**
     * @Route("", name="api_admin_credit_delete_bulk", methods={"DELETE"})
     *
     * @Grant(grant="persistence-common-credit", level="DELETE")
     *
     * @param Request $request
     * @param CreditService $creditService
     * @return JsonResponse
     */
    public function deleteBulkAction(Request $request, CreditService $creditService): JsonResponse
    {
        $creditService->removeBulk($request->get('ids'));

        return $this->respondSuccess(
            Response::HTTP_NO_CONTENT
        );
    }

    /**
     * @Route("/related/info", name="api_admin_credit_related_info", methods={"POST"})
     *
     * @param Request $request
     * @param CreditService $creditService
     * @return JsonResponse
     */
    public function relatedInfoAction(Request $request, CreditService $creditService): JsonResponse
    {
        $relatedData = $creditService->getRelatedInfo($request->get('ids'));

        return $this->respondSuccess(
            Response::HTTP_OK,
            '',
            [$relatedData]
        );
    }
}
