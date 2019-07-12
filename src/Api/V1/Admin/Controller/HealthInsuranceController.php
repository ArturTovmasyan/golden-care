<?php
namespace App\Api\V1\Admin\Controller;

use App\Api\V1\Admin\Service\HealthInsuranceService;
use App\Api\V1\Common\Controller\BaseController;
use App\Entity\HealthInsurance;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\JsonResponse;
use Knp\Bundle\SnappyBundle\Snappy\Response\PdfResponse;
use App\Annotation\Grant as Grant;

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
 * @Route("/api/v1.0/admin/resident/health/insurance")
 *
 * @Grant(grant="persistence-resident-health_insurance", level="VIEW")
 *
 * Class HealthInsuranceController
 * @package App\Api\V1\Admin\Controller
 */
class HealthInsuranceController extends BaseController
{
    /**
     * @Route("/grid", name="api_admin_health_insurance_grid", methods={"GET"})
     *
     * @param Request $request
     * @param HealthInsuranceService $residentAllergenService
     * @return JsonResponse|PdfResponse
     * @throws \ReflectionException
     */
    public function gridAction(Request $request, HealthInsuranceService $residentAllergenService)
    {
        return $this->respondGrid(
            $request,
            HealthInsurance::class,
            'api_admin_health_insurance_grid',
            $residentAllergenService,
            ['resident_id' => $request->get('resident_id')]
        );
    }

    /**
     * @Route("/grid", name="api_admin_health_insurance_grid_options", methods={"OPTIONS"})
     *
     * @param Request $request
     * @return JsonResponse
     * @throws \ReflectionException
     */
    public function gridOptionAction(Request $request)
    {
        return $this->getOptionsByGroupName(HealthInsurance::class, 'api_admin_health_insurance_grid');
    }

    /**
     * @Route("", name="api_admin_health_insurance_list", methods={"GET"})
     *
     * @param Request $request
     * @param HealthInsuranceService $residentAllergenService
     * @return JsonResponse|PdfResponse
     * @throws \ReflectionException
     */
    public function listAction(Request $request, HealthInsuranceService $residentAllergenService)
    {
        return $this->respondList(
            $request,
            HealthInsurance::class,
            'api_admin_health_insurance_list',
            $residentAllergenService,
            ['resident_id' => $request->get('resident_id')]
        );
    }

    /**
     * @Route("/{id}", requirements={"id"="\d+"}, name="api_admin_health_insurance_get", methods={"GET"})
     *
     * @param HealthInsuranceService $residentAllergenService
     * @param $id
     * @return JsonResponse
     */
    public function getAction(Request $request, $id, HealthInsuranceService $residentAllergenService)
    {
        return $this->respondSuccess(
            Response::HTTP_OK,
            '',
            $residentAllergenService->getById($id),
            ['api_admin_health_insurance_get']
        );
    }

    /**
     * @Route("", name="api_admin_health_insurance_add", methods={"POST"})
     *
     * @Grant(grant="persistence-resident-health_insurance", level="ADD")
     *
     * @param Request $request
     * @param HealthInsuranceService $residentAllergenService
     * @return JsonResponse
     * @throws \Exception
     */
    public function addAction(Request $request, HealthInsuranceService $residentAllergenService)
    {
        $id = $residentAllergenService->add(
            [
                'resident_id' => $request->get('resident_id'),
                'company_id' => $request->get('company_id'),
                'medical_record_number' => $request->get('medical_record_number'),
                'group_number' => $request->get('group_number'),
                'notes' => $request->get('notes') ?? ''
            ]
        );

        return $this->respondSuccess(
            Response::HTTP_CREATED,
            '',
            [$id]
        );
    }

    /**
     * @Route("/{id}", requirements={"id"="\d+"}, name="api_admin_health_insurance_edit", methods={"PUT"})
     *
     * @Grant(grant="persistence-resident-health_insurance", level="EDIT")
     *
     * @param Request $request
     * @param $id
     * @param HealthInsuranceService $residentAllergenService
     * @return JsonResponse
     * @throws \Exception
     */
    public function editAction(Request $request, $id, HealthInsuranceService $residentAllergenService)
    {
        $residentAllergenService->edit(
            $id,
            [
                'resident_id' => $request->get('resident_id'),
                'company_id' => $request->get('company_id'),
                'medical_record_number' => $request->get('medical_record_number'),
                'group_number' => $request->get('group_number'),
                'notes' => $request->get('notes') ?? ''
            ]
        );

        return $this->respondSuccess(
            Response::HTTP_CREATED
        );
    }

    /**
     * @Route("/{id}", requirements={"id"="\d+"}, name="api_admin_health_insurance_delete", methods={"DELETE"})
     *
     * @Grant(grant="persistence-resident-health_insurance", level="DELETE")
     *
     * @param $id
     * @param HealthInsuranceService $residentAllergenService
     * @return JsonResponse
     * @throws \Doctrine\DBAL\ConnectionException
     * @throws \Throwable
     */
    public function deleteAction(Request $request, $id, HealthInsuranceService $residentAllergenService)
    {
        $residentAllergenService->remove($id);

        return $this->respondSuccess(
            Response::HTTP_NO_CONTENT
        );
    }

    /**
     * @Route("", name="api_admin_health_insurance_delete_bulk", methods={"DELETE"})
     *
     * @Grant(grant="persistence-resident-health_insurance", level="DELETE")
     *
     * @param Request $request
     * @param HealthInsuranceService $residentAllergenService
     * @return JsonResponse
     * @throws \Doctrine\DBAL\ConnectionException
     * @throws \Throwable
     */
    public function deleteBulkAction(Request $request, HealthInsuranceService $residentAllergenService)
    {
        $residentAllergenService->removeBulk($request->get('ids'));

        return $this->respondSuccess(
            Response::HTTP_NO_CONTENT
        );
    }

    /**
     * @Route("/related/info", name="api_admin_health_insurance_related_info", methods={"POST"})
     *
     * @param Request $request
     * @param HealthInsuranceService $residentAllergenService
     * @return JsonResponse
     * @throws \Doctrine\DBAL\ConnectionException
     * @throws \Throwable
     */
    public function relatedInfoAction(Request $request, HealthInsuranceService $residentAllergenService)
    {
        $relatedData = $residentAllergenService->getRelatedInfo($request->get('ids'));

        return $this->respondSuccess(
            Response::HTTP_OK,
            '',
            [$relatedData]
        );
    }
}
