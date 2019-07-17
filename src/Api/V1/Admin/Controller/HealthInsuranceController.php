<?php
namespace App\Api\V1\Admin\Controller;

use App\Api\V1\Admin\Service\HealthInsuranceService;
use App\Api\V1\Common\Controller\BaseController;
use App\Api\V1\Common\Service\ImageFilterService;
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
     * @param HealthInsuranceService $healthInsuranceService
     * @return JsonResponse|PdfResponse
     * @throws \ReflectionException
     */
    public function gridAction(Request $request, HealthInsuranceService $healthInsuranceService)
    {
        return $this->respondGrid(
            $request,
            HealthInsurance::class,
            'api_admin_health_insurance_grid',
            $healthInsuranceService,
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
     * @param HealthInsuranceService $healthInsuranceService
     * @return JsonResponse|PdfResponse
     * @throws \ReflectionException
     */
    public function listAction(Request $request, HealthInsuranceService $healthInsuranceService)
    {
        return $this->respondList(
            $request,
            HealthInsurance::class,
            'api_admin_health_insurance_list',
            $healthInsuranceService,
            ['resident_id' => $request->get('resident_id')]
        );
    }

    /**
     * @Route("/{id}", requirements={"id"="\d+"}, name="api_admin_health_insurance_get", methods={"GET"})
     *
     * @param HealthInsuranceService $healthInsuranceService
     * @param $id
     * @return JsonResponse
     */
    public function getAction(Request $request, $id, HealthInsuranceService $healthInsuranceService)
    {
        return $this->respondSuccess(
            Response::HTTP_OK,
            '',
            $healthInsuranceService->getById($id),
            ['api_admin_health_insurance_get']
        );
    }

    /**
     * @Route("", name="api_admin_health_insurance_add", methods={"POST"})
     *
     * @Grant(grant="persistence-resident-health_insurance", level="ADD")
     *
     * @param Request $request
     * @param HealthInsuranceService $healthInsuranceService
     * @param ImageFilterService $imageFilterService
     * @return JsonResponse
     * @throws \Exception
     */
    public function addAction(Request $request, HealthInsuranceService $healthInsuranceService, ImageFilterService $imageFilterService)
    {
        $healthInsuranceService->setImageFilterService($imageFilterService);

        $id = $healthInsuranceService->add(
            [
                'resident_id' => $request->get('resident_id'),
                'company_id' => $request->get('company_id'),
                'medical_record_number' => $request->get('medical_record_number'),
                'group_number' => $request->get('group_number'),
                'notes' => $request->get('notes') ?? '',
                'first_file' => $request->get('first_file'),
                'second_file' => $request->get('second_file')
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
     * @param HealthInsuranceService $healthInsuranceService
     * @param ImageFilterService $imageFilterService
     * @return JsonResponse
     * @throws \Exception
     */
    public function editAction(Request $request, $id, HealthInsuranceService $healthInsuranceService, ImageFilterService $imageFilterService)
    {
        $healthInsuranceService->setImageFilterService($imageFilterService);

        $healthInsuranceService->edit(
            $id,
            [
                'resident_id' => $request->get('resident_id'),
                'company_id' => $request->get('company_id'),
                'medical_record_number' => $request->get('medical_record_number'),
                'group_number' => $request->get('group_number'),
                'notes' => $request->get('notes') ?? '',
                'first_file' => $request->get('first_file'),
                'second_file' => $request->get('second_file')
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
     * @param HealthInsuranceService $healthInsuranceService
     * @return JsonResponse
     * @throws \Doctrine\DBAL\ConnectionException
     * @throws \Throwable
     */
    public function deleteAction(Request $request, $id, HealthInsuranceService $healthInsuranceService)
    {
        $healthInsuranceService->remove($id);

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
     * @param HealthInsuranceService $healthInsuranceService
     * @return JsonResponse
     * @throws \Doctrine\DBAL\ConnectionException
     * @throws \Throwable
     */
    public function deleteBulkAction(Request $request, HealthInsuranceService $healthInsuranceService)
    {
        $healthInsuranceService->removeBulk($request->get('ids'));

        return $this->respondSuccess(
            Response::HTTP_NO_CONTENT
        );
    }

    /**
     * @Route("/related/info", name="api_admin_health_insurance_related_info", methods={"POST"})
     *
     * @param Request $request
     * @param HealthInsuranceService $healthInsuranceService
     * @return JsonResponse
     * @throws \Doctrine\DBAL\ConnectionException
     * @throws \Throwable
     */
    public function relatedInfoAction(Request $request, HealthInsuranceService $healthInsuranceService)
    {
        $relatedData = $healthInsuranceService->getRelatedInfo($request->get('ids'));

        return $this->respondSuccess(
            Response::HTTP_OK,
            '',
            [$relatedData]
        );
    }
}
