<?php

namespace App\Api\V1\Admin\Controller;

use App\Annotation\Grant;
use App\Api\V1\Admin\Service\InsuranceCompanyService;
use App\Api\V1\Common\Controller\BaseController;
use App\Entity\InsuranceCompany;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\JsonResponse;
use Knp\Bundle\SnappyBundle\Snappy\Response\PdfResponse;

/**
 * @Route("/api/v1.0/admin/insurance/company")
 *
 * @Grant(grant="persistence-common-insurance_company", level="VIEW")
 *
 * Class InsuranceCompanyController
 * @package App\Api\V1\Admin\Controller
 */
class InsuranceCompanyController extends BaseController
{
    /**
     * @Route("/grid", name="api_admin_insurance_company_grid", methods={"GET"})
     *
     * @param Request $request
     * @param InsuranceCompanyService $insuranceCompanyService
     * @return JsonResponse
     */
    public function gridAction(Request $request, InsuranceCompanyService $insuranceCompanyService): JsonResponse
    {
        return $this->respondGrid(
            $request,
            InsuranceCompany::class,
            'api_admin_insurance_company_grid',
            $insuranceCompanyService
        );
    }

    /**
     * @Route("/grid", name="api_admin_insurance_company_grid_options", methods={"OPTIONS"})
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function gridOptionAction(Request $request): JsonResponse
    {
        return $this->getOptionsByGroupName($request, InsuranceCompany::class, 'api_admin_insurance_company_grid');
    }

    /**
     * @Route("", name="api_admin_insurance_company_list", methods={"GET"})
     *
     * @param Request $request
     * @param InsuranceCompanyService $insuranceCompanyService
     * @return PdfResponse|JsonResponse|Response
     */
    public function listAction(Request $request, InsuranceCompanyService $insuranceCompanyService)
    {
        return $this->respondList(
            $request,
            InsuranceCompany::class,
            'api_admin_insurance_company_list',
            $insuranceCompanyService
        );
    }

    /**
     * @Route("/{id}", requirements={"id"="\d+"}, name="api_admin_insurance_company_get", methods={"GET"})
     *
     * @param Request $request
     * @param $id
     * @param InsuranceCompanyService $insuranceCompanyService
     * @return JsonResponse
     */
    public function getAction(Request $request, $id, InsuranceCompanyService $insuranceCompanyService): JsonResponse
    {
        return $this->respondSuccess(
            Response::HTTP_OK,
            '',
            $insuranceCompanyService->getById($id),
            ['api_admin_insurance_company_get']
        );
    }

    /**
     * @Route("", name="api_admin_insurance_company_add", methods={"POST"})
     *
     * @Grant(grant="persistence-common-insurance_company", level="ADD")
     *
     * @param Request $request
     * @param InsuranceCompanyService $insuranceCompanyService
     * @return JsonResponse
     */
    public function addAction(Request $request, InsuranceCompanyService $insuranceCompanyService): JsonResponse
    {
        $id = $insuranceCompanyService->add(
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
     * @Route("/{id}", requirements={"id"="\d+"}, name="api_admin_insurance_company_edit", methods={"PUT"})
     *
     * @Grant(grant="persistence-common-insurance_company", level="EDIT")
     *
     * @param Request $request
     * @param $id
     * @param InsuranceCompanyService $insuranceCompanyService
     * @return JsonResponse
     */
    public function editAction(Request $request, $id, InsuranceCompanyService $insuranceCompanyService): JsonResponse
    {
        $insuranceCompanyService->edit(
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
     * @Route("/{id}", requirements={"id"="\d+"}, name="api_admin_insurance_company_delete", methods={"DELETE"})
     *
     * @Grant(grant="persistence-common-insurance_company", level="DELETE")
     *
     * @param Request $request
     * @param $id
     * @param InsuranceCompanyService $insuranceCompanyService
     * @return JsonResponse
     */
    public function deleteAction(Request $request, $id, InsuranceCompanyService $insuranceCompanyService): JsonResponse
    {
        $insuranceCompanyService->remove($id);

        return $this->respondSuccess(
            Response::HTTP_NO_CONTENT
        );
    }

    /**
     * @Route("", name="api_admin_insurance_company_delete_bulk", methods={"DELETE"})
     *
     * @Grant(grant="persistence-common-insurance_company", level="DELETE")
     *
     * @param Request $request
     * @param InsuranceCompanyService $insuranceCompanyService
     * @return JsonResponse
     */
    public function deleteBulkAction(Request $request, InsuranceCompanyService $insuranceCompanyService): JsonResponse
    {
        $insuranceCompanyService->removeBulk($request->get('ids'));

        return $this->respondSuccess(
            Response::HTTP_NO_CONTENT
        );
    }

    /**
     * @Route("/related/info", name="api_admin_insurance_company_related_info", methods={"POST"})
     *
     * @param Request $request
     * @param InsuranceCompanyService $insuranceCompanyService
     * @return JsonResponse
     */
    public function relatedInfoAction(Request $request, InsuranceCompanyService $insuranceCompanyService): JsonResponse
    {
        $relatedData = $insuranceCompanyService->getRelatedInfo($request->get('ids'));

        return $this->respondSuccess(
            Response::HTTP_OK,
            '',
            [$relatedData]
        );
    }
}
