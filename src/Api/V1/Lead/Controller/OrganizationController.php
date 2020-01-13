<?php

namespace App\Api\V1\Lead\Controller;

use App\Annotation\Grant;
use App\Api\V1\Lead\Service\OrganizationService;
use App\Api\V1\Common\Controller\BaseController;
use App\Entity\Lead\Organization;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\JsonResponse;
use Knp\Bundle\SnappyBundle\Snappy\Response\PdfResponse;

/**
 * @Route("/api/v1.0/lead/organization")
 *
 * @Grant(grant="persistence-lead-organization", level="VIEW")
 *
 * Class OrganizationController
 * @package App\Api\V1\Lead\Controller
 */
class OrganizationController extends BaseController
{
    /**
     * @Route("/grid", name="api_lead_organization", methods={"GET"})
     *
     * @param Request $request
     * @param OrganizationService $organizationService
     * @return JsonResponse
     */
    public function gridAction(Request $request, OrganizationService $organizationService): JsonResponse
    {
        return $this->respondGrid(
            $request,
            Organization::class,
            'api_lead_organization_grid',
            $organizationService
        );
    }

    /**
     * @Route("/grid", name="api_lead_organization_grid_options", methods={"OPTIONS"})
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function gridOptionAction(Request $request): JsonResponse
    {
        return $this->getOptionsByGroupName($request, Organization::class, 'api_lead_organization_grid');
    }

    /**
     * @Route("", name="api_lead_organization_list", methods={"GET"})
     *
     * @param Request $request
     * @param OrganizationService $organizationService
     * @return PdfResponse|JsonResponse|Response
     */
    public function listAction(Request $request, OrganizationService $organizationService)
    {
        return $this->respondList(
            $request,
            Organization::class,
            'api_lead_organization_list',
            $organizationService
        );
    }

    /**
     * @Route("/{id}", requirements={"id"="\d+"}, name="api_lead_organization_get", methods={"GET"})
     *
     * @param Request $request
     * @param $id
     * @param OrganizationService $organizationService
     * @return JsonResponse
     */
    public function getAction(Request $request, $id, OrganizationService $organizationService): JsonResponse
    {
        return $this->respondSuccess(
            Response::HTTP_OK,
            '',
            $organizationService->getById($id),
            ['api_lead_organization_get']
        );
    }

    /**
     * @Route("", name="api_lead_organization_add", methods={"POST"})
     *
     * @Grant(grant="persistence-lead-organization", level="ADD")
     *
     * @param Request $request
     * @param OrganizationService $organizationService
     * @return JsonResponse
     */
    public function addAction(Request $request, OrganizationService $organizationService): JsonResponse
    {
        $id = $organizationService->add(
            [
                'name' => $request->get('name'),
                'category_id' => $request->get('category_id'),
                'address_1' => $request->get('address_1'),
                'address_2' => $request->get('address_2') ?? '',
                'csz_id' => $request->get('csz_id'),
                'website_url' => $request->get('website_url') ?? '',
                'phones' => $request->get('phones'),
                'emails' => $request->get('emails')
            ]
        );

        return $this->respondSuccess(
            Response::HTTP_CREATED,
            '',
            [$id]
        );
    }

    /**
     * @Route("/{id}", requirements={"id"="\d+"}, name="api_lead_organization_edit", methods={"PUT"})
     *
     * @Grant(grant="persistence-lead-organization", level="EDIT")
     *
     * @param Request $request
     * @param $id
     * @param OrganizationService $organizationService
     * @return JsonResponse
     */
    public function editAction(Request $request, $id, OrganizationService $organizationService): JsonResponse
    {
        $organizationService->edit(
            $id,
            [
                'name' => $request->get('name'),
                'category_id' => $request->get('category_id'),
                'address_1' => $request->get('address_1'),
                'address_2' => $request->get('address_2') ?? '',
                'csz_id' => $request->get('csz_id'),
                'website_url' => $request->get('website_url') ?? '',
                'phones' => $request->get('phones'),
                'emails' => $request->get('emails')
            ]
        );

        return $this->respondSuccess(
            Response::HTTP_CREATED
        );
    }

    /**
     * @Route("/{id}", requirements={"id"="\d+"}, name="api_lead_organization_delete", methods={"DELETE"})
     *
     * @Grant(grant="persistence-lead-organization", level="DELETE")
     *
     * @param Request $request
     * @param $id
     * @param OrganizationService $organizationService
     * @return JsonResponse
     */
    public function deleteAction(Request $request, $id, OrganizationService $organizationService): JsonResponse
    {
        $organizationService->remove($id);

        return $this->respondSuccess(
            Response::HTTP_NO_CONTENT
        );
    }

    /**
     * @Route("", name="api_lead_organization_delete_bulk", methods={"DELETE"})
     *
     * @Grant(grant="persistence-lead-organization", level="DELETE")
     *
     * @param Request $request
     * @param OrganizationService $organizationService
     * @return JsonResponse
     */
    public function deleteBulkAction(Request $request, OrganizationService $organizationService): JsonResponse
    {
        $organizationService->removeBulk($request->get('ids'));

        return $this->respondSuccess(
            Response::HTTP_NO_CONTENT
        );
    }

    /**
     * @Route("/related/info", name="api_lead_organization_related_info", methods={"POST"})
     *
     * @param Request $request
     * @param OrganizationService $organizationService
     * @return JsonResponse
     */
    public function relatedInfoAction(Request $request, OrganizationService $organizationService): JsonResponse
    {
        $relatedData = $organizationService->getRelatedInfo($request->get('ids'));

        return $this->respondSuccess(
            Response::HTTP_OK,
            '',
            [$relatedData]
        );
    }
}
