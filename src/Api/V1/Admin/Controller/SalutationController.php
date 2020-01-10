<?php

namespace App\Api\V1\Admin\Controller;

use App\Annotation\Grant;
use App\Api\V1\Admin\Service\SalutationService;
use App\Api\V1\Common\Controller\BaseController;
use App\Entity\Salutation;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\JsonResponse;
use Knp\Bundle\SnappyBundle\Snappy\Response\PdfResponse;

/**
 * @Route("/api/v1.0/admin/salutation")
 *
 * @Grant(grant="persistence-common-salutation", level="VIEW")
 *
 * Class SalutationController
 * @package App\Api\V1\Admin\Controller
 */
class SalutationController extends BaseController
{
    /**
     * @Route("/grid", name="api_admin_salutation_grid", methods={"GET"})
     *
     * @param Request $request
     * @param SalutationService $salutationService
     * @return JsonResponse
     */
    public function gridAction(Request $request, SalutationService $salutationService): JsonResponse
    {
        return $this->respondGrid(
            $request,
            Salutation::class,
            'api_admin_salutation_grid',
            $salutationService
        );
    }

    /**
     * @Route("/grid", name="api_admin_salutation_grid_options", methods={"OPTIONS"})
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function gridOptionAction(Request $request): JsonResponse
    {
        return $this->getOptionsByGroupName($request, Salutation::class, 'api_admin_salutation_grid');
    }

    /**
     * @Route("", name="api_admin_salutation_list", methods={"GET"})
     *
     * @param Request $request
     * @param SalutationService $salutationService
     * @return PdfResponse|JsonResponse|Response
     */
    public function listAction(Request $request, SalutationService $salutationService)
    {
        return $this->respondList(
            $request,
            Salutation::class,
            'api_admin_salutation_list',
            $salutationService
        );
    }

    /**
     * @Route("/{id}", requirements={"id"="\d+"}, name="api_admin_salutation_get", methods={"GET"})
     *
     * @param Request $request
     * @param $id
     * @param SalutationService $salutationService
     * @return JsonResponse
     */
    public function getAction(Request $request, $id, SalutationService $salutationService): JsonResponse
    {
        return $this->respondSuccess(
            Response::HTTP_OK,
            '',
            $salutationService->getById($id),
            ['api_admin_salutation_get']
        );
    }

    /**
     * @Route("", name="api_admin_salutation_add", methods={"POST"})
     *
     * @Grant(grant="persistence-common-salutation", level="ADD")
     *
     * @param Request $request
     * @param SalutationService $salutationService
     * @return JsonResponse
     */
    public function addAction(Request $request, SalutationService $salutationService): JsonResponse
    {
        $id = $salutationService->add(
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
     * @Route("/{id}", requirements={"id"="\d+"}, name="api_admin_salutation_edit", methods={"PUT"})
     *
     * @Grant(grant="persistence-common-salutation", level="EDIT")
     *
     * @param Request $request
     * @param $id
     * @param SalutationService $salutationService
     * @return JsonResponse
     */
    public function editAction(Request $request, $id, SalutationService $salutationService): JsonResponse
    {
        $salutationService->edit(
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
     * @Route("/{id}", requirements={"id"="\d+"}, name="api_admin_salutation_delete", methods={"DELETE"})
     *
     * @Grant(grant="persistence-common-salutation", level="DELETE")
     *
     * @param Request $request
     * @param $id
     * @param SalutationService $salutationService
     * @return JsonResponse
     */
    public function deleteAction(Request $request, $id, SalutationService $salutationService): JsonResponse
    {
        $salutationService->remove($id);

        return $this->respondSuccess(
            Response::HTTP_NO_CONTENT
        );
    }

    /**
     * @Route("", name="api_admin_salutation_delete_bulk", methods={"DELETE"})
     *
     * @Grant(grant="persistence-common-salutation", level="DELETE")
     *
     * @param Request $request
     * @param SalutationService $salutationService
     * @return JsonResponse
     */
    public function deleteBulkAction(Request $request, SalutationService $salutationService): JsonResponse
    {
        $salutationService->removeBulk($request->get('ids'));

        return $this->respondSuccess(
            Response::HTTP_NO_CONTENT
        );
    }

    /**
     * @Route("/related/info", name="api_admin_salutation_related_info", methods={"POST"})
     *
     * @param Request $request
     * @param SalutationService $salutationService
     * @return JsonResponse
     */
    public function relatedInfoAction(Request $request, SalutationService $salutationService): JsonResponse
    {
        $relatedData = $salutationService->getRelatedInfo($request->get('ids'));

        return $this->respondSuccess(
            Response::HTTP_OK,
            '',
            [$relatedData]
        );
    }

    /**
     * @Route("/mobile/list", name="api_admin_salutation_mobile_list", methods={"GET"})
     *
     * @param Request $request
     * @param SalutationService $salutationService
     * @return JsonResponse
     */
    public function getMobileListAction(Request $request, SalutationService $salutationService): JsonResponse
    {
        return $this->respondSuccess(
            Response::HTTP_OK,
            '',
            $salutationService->getMobileList($request->headers->get('date')),
            ['api_admin_salutation_mobile_list']
        );
    }
}
