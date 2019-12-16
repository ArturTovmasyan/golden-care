<?php
namespace App\Api\V1\Admin\Controller;

use App\Api\V1\Admin\Service\ResidentDiagnosisService;
use App\Api\V1\Common\Controller\BaseController;
use App\Entity\ResidentDiagnosis;
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
 * @Route("/api/v1.0/admin/resident/history/diagnose")
 *
 * @Grant(grant="persistence-resident-resident_diagnosis", level="VIEW")
 *
 * Class ResidentDiagnosisController
 * @package App\Api\V1\Admin\Controller
 */
class ResidentDiagnosisController extends BaseController
{
    /**
     * @Route("/grid", name="api_admin_resident_diagnosis_grid", methods={"GET"})
     *
     * @param Request $request
     * @param ResidentDiagnosisService $residentDiagnosisService
     * @return JsonResponse|PdfResponse
     * @throws \ReflectionException
     */
    public function gridAction(Request $request, ResidentDiagnosisService $residentDiagnosisService)
    {
        return $this->respondGrid(
            $request,
            ResidentDiagnosis::class,
            'api_admin_resident_diagnosis_grid',
            $residentDiagnosisService,
            ['resident_id' => $request->get('resident_id')]
        );
    }

    /**
     * @Route("/grid", name="api_admin_resident_diagnosis_grid_options", methods={"OPTIONS"})
     *
     * @param Request $request
     * @return JsonResponse
     * @throws \ReflectionException
     */
    public function gridOptionAction(Request $request)
    {
        return $this->getOptionsByGroupName($request, ResidentDiagnosis::class, 'api_admin_resident_diagnosis_grid');
    }

    /**
     * @Route("", name="api_admin_resident_diagnosis_list", methods={"GET"})
     *
     * @param Request $request
     * @param ResidentDiagnosisService $residentDiagnosisService
     * @return JsonResponse|PdfResponse
     * @throws \ReflectionException
     */
    public function listAction(Request $request, ResidentDiagnosisService $residentDiagnosisService)
    {
        return $this->respondList(
            $request,
            ResidentDiagnosis::class,
            'api_admin_resident_diagnosis_list',
            $residentDiagnosisService,
            ['resident_id' => $request->get('resident_id')]
        );
    }

    /**
     * @Route("/{id}", requirements={"id"="\d+"}, name="api_admin_resident_diagnosis_get", methods={"GET"})
     *
     * @param ResidentDiagnosisService $residentDiagnosisService
     * @param $id
     * @return JsonResponse
     */
    public function getAction(Request $request, $id, ResidentDiagnosisService $residentDiagnosisService)
    {
        return $this->respondSuccess(
            Response::HTTP_OK,
            '',
            $residentDiagnosisService->getById($id),
            ['api_admin_resident_diagnosis_get']
        );
    }

    /**
     * @Route("", name="api_admin_resident_diagnosis_add", methods={"POST"})
     *
     * @Grant(grant="persistence-resident-resident_diagnosis", level="ADD")
     *
     * @param Request $request
     * @param ResidentDiagnosisService $residentDiagnosisService
     * @return JsonResponse
     * @throws \Throwable
     */
    public function addAction(Request $request, ResidentDiagnosisService $residentDiagnosisService)
    {
        $id = $residentDiagnosisService->add(
            [
                'resident_id' => $request->get('resident_id'),
                'diagnosis_id' => $request->get('diagnosis_id'),
                'type' => $request->get('type'),
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
     * @Route("/{id}", requirements={"id"="\d+"}, name="api_admin_resident_diagnosis_edit", methods={"PUT"})
     *
     * @Grant(grant="persistence-resident-resident_diagnosis", level="EDIT")
     *
     * @param Request $request
     * @param $id
     * @param ResidentDiagnosisService $residentDiagnosisService
     * @return JsonResponse
     * @throws \Throwable
     */
    public function editAction(Request $request, $id, ResidentDiagnosisService $residentDiagnosisService)
    {
        $residentDiagnosisService->edit(
            $id,
            [
                'resident_id' => $request->get('resident_id'),
                'diagnosis_id' => $request->get('diagnosis_id'),
                'type' => $request->get('type'),
                'notes' => $request->get('notes') ?? ''
            ]
        );

        return $this->respondSuccess(
            Response::HTTP_CREATED
        );
    }

    /**
     * @Route("/{id}", requirements={"id"="\d+"}, name="api_admin_resident_diagnosis_delete", methods={"DELETE"})
     *
     * @Grant(grant="persistence-resident-resident_diagnosis", level="DELETE")
     *
     * @param $id
     * @param ResidentDiagnosisService $residentDiagnosisService
     * @return JsonResponse
     * @throws \Doctrine\DBAL\ConnectionException
     * @throws \Throwable
     */
    public function deleteAction(Request $request, $id, ResidentDiagnosisService $residentDiagnosisService)
    {
        $residentDiagnosisService->remove($id);

        return $this->respondSuccess(
            Response::HTTP_NO_CONTENT
        );
    }

    /**
     * @Route("", name="api_admin_resident_diagnosis_delete_bulk", methods={"DELETE"})
     *
     * @Grant(grant="persistence-resident-resident_diagnosis", level="DELETE")
     *
     * @param Request $request
     * @param ResidentDiagnosisService $residentDiagnosisService
     * @return JsonResponse
     * @throws \Doctrine\DBAL\ConnectionException
     * @throws \Throwable
     */
    public function deleteBulkAction(Request $request, ResidentDiagnosisService $residentDiagnosisService)
    {
        $residentDiagnosisService->removeBulk($request->get('ids'));

        return $this->respondSuccess(
            Response::HTTP_NO_CONTENT
        );
    }

    /**
     * @Route("/related/info", name="api_admin_resident_diagnosis_related_info", methods={"POST"})
     *
     * @param Request $request
     * @param ResidentDiagnosisService $residentDiagnosisService
     * @return JsonResponse
     * @throws \Doctrine\DBAL\ConnectionException
     * @throws \Throwable
     */
    public function relatedInfoAction(Request $request, ResidentDiagnosisService $residentDiagnosisService)
    {
        $relatedData = $residentDiagnosisService->getRelatedInfo($request->get('ids'));

        return $this->respondSuccess(
            Response::HTTP_OK,
            '',
            [$relatedData]
        );
    }
}
