<?php

namespace App\Api\V1\Admin\Controller;

use App\Annotation\Grant;
use App\Api\V1\Admin\Service\HospiceProviderService;
use App\Api\V1\Common\Controller\BaseController;
use App\Entity\HospiceProvider;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\JsonResponse;
use Knp\Bundle\SnappyBundle\Snappy\Response\PdfResponse;

/**
 * @Route("/api/v1.0/admin/hospice-provider")
 *
 * @Grant(grant="persistence-common-hospice_provider", level="VIEW")
 *
 * Class HospiceProviderController
 * @package App\Api\V1\Admin\Controller
 */
class HospiceProviderController extends BaseController
{
    /**
     * @Route("/grid", name="api_admin_hospice_provider_grid", methods={"GET"})
     *
     * @param Request $request
     * @param HospiceProviderService $hospiceProviderService
     * @return JsonResponse
     */
    public function gridAction(Request $request, HospiceProviderService $hospiceProviderService): JsonResponse
    {
        return $this->respondGrid(
            $request,
            HospiceProvider::class,
            'api_admin_hospice_provider_grid',
            $hospiceProviderService
        );
    }

    /**
     * @Route("/grid", name="api_admin_hospice_provider_grid_options", methods={"OPTIONS"})
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function gridOptionAction(Request $request): JsonResponse
    {
        return $this->getOptionsByGroupName($request, HospiceProvider::class, 'api_admin_hospice_provider_grid');
    }

    /**
     * @Route("", name="api_admin_hospice_provider_list", methods={"GET"})
     *
     * @param Request $request
     * @param HospiceProviderService $hospiceProviderService
     * @return PdfResponse|JsonResponse|Response
     */
    public function listAction(Request $request, HospiceProviderService $hospiceProviderService)
    {
        return $this->respondList(
            $request,
            HospiceProvider::class,
            'api_admin_hospice_provider_list',
            $hospiceProviderService
        );
    }

    /**
     * @Route("/{id}", requirements={"id"="\d+"}, name="api_admin_hospice_provider_get", methods={"GET"})
     *
     * @param Request $request
     * @param $id
     * @param HospiceProviderService $hospiceProviderService
     * @return JsonResponse
     */
    public function getAction(Request $request, $id, HospiceProviderService $hospiceProviderService): JsonResponse
    {
        return $this->respondSuccess(
            Response::HTTP_OK,
            '',
            $hospiceProviderService->getById($id),
            ['api_admin_hospice_provider_get']
        );
    }

    /**
     * @Route("", name="api_admin_hospice_provider_add", methods={"POST"})
     *
     * @Grant(grant="persistence-common-hospice_provider", level="ADD")
     *
     * @param Request $request
     * @param HospiceProviderService $hospiceProviderService
     * @return JsonResponse
     */
    public function addAction(Request $request, HospiceProviderService $hospiceProviderService): JsonResponse
    {
        $id = $hospiceProviderService->add(
            [
                'name' => $request->get('name'),
                'address_1' => $request->get('address_1'),
                'address_2' => $request->get('address_2'),
                'csz_id' => $request->get('csz_id'),
                'phone' => $request->get('phone'),
                'email' => $request->get('email'),
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
     * @Route("/{id}", requirements={"id"="\d+"}, name="api_admin_hospice_provider_edit", methods={"PUT"})
     *
     * @Grant(grant="persistence-common-hospice_provider", level="EDIT")
     *
     * @param Request $request
     * @param $id
     * @param HospiceProviderService $hospiceProviderService
     * @return JsonResponse
     */
    public function editAction(Request $request, $id, HospiceProviderService $hospiceProviderService): JsonResponse
    {
        $hospiceProviderService->edit(
            $id,
            [
                'name' => $request->get('name'),
                'address_1' => $request->get('address_1'),
                'address_2' => $request->get('address_2'),
                'csz_id' => $request->get('csz_id'),
                'phone' => $request->get('phone'),
                'email' => $request->get('email'),
                'space_id' => $request->get('space_id')
            ]
        );

        return $this->respondSuccess(
            Response::HTTP_CREATED
        );
    }

    /**
     * @Route("/{id}", requirements={"id"="\d+"}, name="api_admin_hospice_provider_delete", methods={"DELETE"})
     *
     * @Grant(grant="persistence-common-hospice_provider", level="DELETE")
     *
     * @param Request $request
     * @param $id
     * @param HospiceProviderService $hospiceProviderService
     * @return JsonResponse
     */
    public function deleteAction(Request $request, $id, HospiceProviderService $hospiceProviderService): JsonResponse
    {
        $hospiceProviderService->remove($id);

        return $this->respondSuccess(
            Response::HTTP_NO_CONTENT
        );
    }

    /**
     * @Route("", name="api_admin_hospice_provider_delete_bulk", methods={"DELETE"})
     *
     * @Grant(grant="persistence-common-hospice_provider", level="DELETE")
     *
     * @param Request $request
     * @param HospiceProviderService $hospiceProviderService
     * @return JsonResponse
     */
    public function deleteBulkAction(Request $request, HospiceProviderService $hospiceProviderService): JsonResponse
    {
        $hospiceProviderService->removeBulk($request->get('ids'));

        return $this->respondSuccess(
            Response::HTTP_NO_CONTENT
        );
    }

    /**
     * @Route("/related/info", name="api_admin_hospice_provider_related_info", methods={"POST"})
     *
     * @param Request $request
     * @param HospiceProviderService $hospiceProviderService
     * @return JsonResponse
     */
    public function relatedInfoAction(Request $request, HospiceProviderService $hospiceProviderService): JsonResponse
    {
        $relatedData = $hospiceProviderService->getRelatedInfo($request->get('ids'));

        return $this->respondSuccess(
            Response::HTTP_OK,
            '',
            [$relatedData]
        );
    }
}
