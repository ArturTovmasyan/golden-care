<?php

namespace App\Api\V1\Admin\Controller;

use App\Annotation\Grant;
use App\Api\V1\Admin\Service\DiagnosisService;
use App\Api\V1\Common\Controller\BaseController;
use App\Entity\Diagnosis;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\JsonResponse;
use Knp\Bundle\SnappyBundle\Snappy\Response\PdfResponse;

/**
 * @Route("/api/v1.0/admin/diagnosis")
 *
 * @Grant(grant="persistence-common-diagnosis", level="VIEW")
 *
 * Class DiagnosisController
 * @package App\Api\V1\Admin\Controller
 */
class DiagnosisController extends BaseController
{
    /**
     * @Route("/grid", name="api_admin_diagnosis_grid", methods={"GET"})
     *
     * @param Request $request
     * @param DiagnosisService $diagnosisService
     * @return JsonResponse
     */
    public function gridAction(Request $request, DiagnosisService $diagnosisService): JsonResponse
    {
        return $this->respondGrid(
            $request,
            Diagnosis::class,
            'api_admin_diagnosis_grid',
            $diagnosisService
        );
    }

    /**
     * @Route("/grid", name="api_admin_diagnosis_grid_options", methods={"OPTIONS"})
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function gridOptionAction(Request $request): JsonResponse
    {
        return $this->getOptionsByGroupName($request, Diagnosis::class, 'api_admin_diagnosis_grid');
    }

    /**
     * @Route("", name="api_admin_diagnosis_list", methods={"GET"})
     *
     * @param Request $request
     * @param DiagnosisService $diagnosisService
     * @return PdfResponse|JsonResponse|Response
     */
    public function listAction(Request $request, DiagnosisService $diagnosisService)
    {
        return $this->respondList(
            $request,
            Diagnosis::class,
            'api_admin_diagnosis_list',
            $diagnosisService
        );
    }

    /**
     * @Route("/{id}", requirements={"id"="\d+"}, name="api_admin_diagnosis_get", methods={"GET"})
     *
     * @param Request $request
     * @param $id
     * @param DiagnosisService $diagnosisService
     * @return JsonResponse
     */
    public function getAction(Request $request, $id, DiagnosisService $diagnosisService): JsonResponse
    {
        return $this->respondSuccess(
            Response::HTTP_OK,
            '',
            $diagnosisService->getById($id),
            ['api_admin_diagnosis_get']
        );
    }

    /**
     * @Route("", name="api_admin_diagnosis_add", methods={"POST"})
     *
     * @Grant(grant="persistence-common-diagnosis", level="ADD")
     *
     * @param Request $request
     * @param DiagnosisService $diagnosisService
     * @return JsonResponse
     */
    public function addAction(Request $request, DiagnosisService $diagnosisService): JsonResponse
    {
        $id = $diagnosisService->add(
            [
                'title' => $request->get('title'),
                'acronym' => $request->get('acronym') ?? '',
                'description' => $request->get('description') ?? '',
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
     * @Route("/{id}", requirements={"id"="\d+"}, name="api_admin_diagnosis_edit", methods={"PUT"})
     *
     * @Grant(grant="persistence-common-diagnosis", level="EDIT")
     *
     * @param Request $request
     * @param $id
     * @param DiagnosisService $diagnosisService
     * @return JsonResponse
     */
    public function editAction(Request $request, $id, DiagnosisService $diagnosisService): JsonResponse
    {
        $diagnosisService->edit(
            $id,
            [
                'title' => $request->get('title'),
                'acronym' => $request->get('acronym') ?? '',
                'description' => $request->get('description') ?? '',
                'space_id' => $request->get('space_id')
            ]
        );

        return $this->respondSuccess(
            Response::HTTP_CREATED
        );
    }

    /**
     * @Route("/{id}", requirements={"id"="\d+"}, name="api_admin_diagnosis_delete", methods={"DELETE"})
     *
     * @Grant(grant="persistence-common-diagnosis", level="DELETE")
     *
     * @param Request $request
     * @param $id
     * @param DiagnosisService $diagnosisService
     * @return JsonResponse
     */
    public function deleteAction(Request $request, $id, DiagnosisService $diagnosisService): JsonResponse
    {
        $diagnosisService->remove($id);

        return $this->respondSuccess(
            Response::HTTP_NO_CONTENT
        );
    }

    /**
     * @Route("", name="api_admin_diagnosis_delete_bulk", methods={"DELETE"})
     *
     * @Grant(grant="persistence-common-diagnosis", level="DELETE")
     *
     * @param Request $request
     * @param DiagnosisService $diagnosisService
     * @return JsonResponse
     */
    public function deleteBulkAction(Request $request, DiagnosisService $diagnosisService): JsonResponse
    {
        $diagnosisService->removeBulk($request->get('ids'));

        return $this->respondSuccess(
            Response::HTTP_NO_CONTENT
        );
    }

    /**
     * @Route("/related/info", name="api_admin_diagnosis_related_info", methods={"POST"})
     *
     * @param Request $request
     * @param DiagnosisService $diagnosisService
     * @return JsonResponse
     */
    public function relatedInfoAction(Request $request, DiagnosisService $diagnosisService): JsonResponse
    {
        $relatedData = $diagnosisService->getRelatedInfo($request->get('ids'));

        return $this->respondSuccess(
            Response::HTTP_OK,
            '',
            [$relatedData]
        );
    }
}
