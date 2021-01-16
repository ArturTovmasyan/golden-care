<?php

namespace App\Api\V1\Admin\Controller;

use App\Annotation\Grant;
use App\Api\V1\Admin\Service\FacilityDocumentService;
use App\Api\V1\Common\Controller\BaseController;
use App\Entity\FacilityDocument;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\JsonResponse;
use Knp\Bundle\SnappyBundle\Snappy\Response\PdfResponse;

/**
 * @Route("/api/v1.0/admin/facility/document")
 *
 * @Grant(grant="persistence-facility_document", level="VIEW")
 *
 * Class FacilityDocumentController
 * @package App\Api\V1\Admin\Controller
 */
class FacilityDocumentController extends BaseController
{
    protected function gridIgnoreFields(Request $request): array
    {
        $ignoreFields = [];

        $facilityId = (int)$request->get('facility_id');

        if (!empty($facilityId)) {
            $ignoreFields[] = 'facility';
        }

        return $ignoreFields;
    }

    /**
     * @Route("/grid", name="api_admin_facility_document_grid", methods={"GET"})
     *
     * @param Request $request
     * @param FacilityDocumentService $facilityDocumentService
     * @return JsonResponse
     */
    public function gridAction(Request $request, FacilityDocumentService $facilityDocumentService): JsonResponse
    {
        return $this->respondGrid(
            $request,
            FacilityDocument::class,
            'api_admin_facility_document_grid',
            $facilityDocumentService,
            [
                'facility_id' => $request->get('facility_id'),
                'category_id' => $request->get('category_id')
            ]
        );
    }

    /**
     * @Route("/grid", name="api_admin_facility_document_grid_options", methods={"OPTIONS"})
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function gridOptionAction(Request $request): JsonResponse
    {
        return $this->getOptionsByGroupName($request, FacilityDocument::class, 'api_admin_facility_document_grid');
    }

    /**
     * @Route("", name="api_admin_facility_document_list", methods={"GET"})
     *
     * @param Request $request
     * @param FacilityDocumentService $facilityDocumentService
     * @return PdfResponse|JsonResponse|Response
     */
    public function listAction(Request $request, FacilityDocumentService $facilityDocumentService)
    {
        return $this->respondList(
            $request,
            FacilityDocument::class,
            'api_admin_facility_document_list',
            $facilityDocumentService,
            [
                'facility_id' => $request->get('facility_id'),
                'category_id' => $request->get('category_id')
            ]
        );
    }

    /**
     * @Route("/{id}", requirements={"id"="\d+"}, name="api_admin_facility_document_get", methods={"GET"})
     *
     * @param Request $request
     * @param $id
     * @param FacilityDocumentService $facilityDocumentService
     * @return JsonResponse
     */
    public function getAction(Request $request, $id, FacilityDocumentService $facilityDocumentService): JsonResponse
    {
        $entity = $facilityDocumentService->getById($id);

        if ($entity !== null && $entity->getFile() !== null) {
            $downloadUrl = $request->getScheme() . '://' . $request->getHttpHost() . $this->generateUrl('api_admin_facility_document_download', ['id' => $entity->getId()]);

            $entity->setDownloadUrl($downloadUrl);
        } else {
            $entity->setDownloadUrl(null);
        }

        return $this->respondSuccess(
            Response::HTTP_OK,
            '',
            $facilityDocumentService->getById($id),
            ['api_admin_facility_document_get']
        );
    }

    /**
     * @Route("", name="api_admin_facility_document_add", methods={"POST"})
     *
     * @Grant(grant="persistence-facility_document", level="ADD")
     *
     * @param Request $request
     * @param FacilityDocumentService $facilityDocumentService
     * @return JsonResponse
     */
    public function addAction(Request $request, FacilityDocumentService $facilityDocumentService): JsonResponse
    {
        $id = $facilityDocumentService->add(
            [
                'facility_id' => $request->get('facility_id'),
                'category_id' => $request->get('category_id'),
                'title' => $request->get('title'),
                'description' => $request->get('description') ?? '',
                'file' => $request->get('file'),
                'file_name' => $request->get('file_name')
            ]
        );

        return $this->respondSuccess(
            Response::HTTP_CREATED,
            '',
            [$id]
        );
    }

    /**
     * @Route("/{id}", requirements={"id"="\d+"}, name="api_admin_facility_document_edit", methods={"PUT"})
     *
     * @Grant(grant="persistence-facility_document", level="EDIT")
     *
     * @param Request $request
     * @param $id
     * @param FacilityDocumentService $facilityDocumentService
     * @return JsonResponse
     */
    public function editAction(Request $request, $id, FacilityDocumentService $facilityDocumentService): JsonResponse
    {
        $facilityDocumentService->edit(
            $id,
            [
                'facility_id' => $request->get('facility_id'),
                'category_id' => $request->get('category_id'),
                'title' => $request->get('title'),
                'description' => $request->get('description') ?? '',
                'file' => $request->get('file'),
                'file_name' => $request->get('file_name')
            ]
        );

        return $this->respondSuccess(
            Response::HTTP_CREATED
        );
    }

    /**
     * @Route("/{id}", requirements={"id"="\d+"}, name="api_admin_facility_document_delete", methods={"DELETE"})
     *
     * @Grant(grant="persistence-facility_document", level="DELETE")
     *
     * @param Request $request
     * @param $id
     * @param FacilityDocumentService $facilityDocumentService
     * @return JsonResponse
     */
    public function deleteAction(Request $request, $id, FacilityDocumentService $facilityDocumentService): JsonResponse
    {
        $facilityDocumentService->remove($id);

        return $this->respondSuccess(
            Response::HTTP_NO_CONTENT
        );
    }

    /**
     * @Route("", name="api_admin_facility_document_delete_bulk", methods={"DELETE"})
     *
     * @Grant(grant="persistence-facility_document", level="DELETE")
     *
     * @param Request $request
     * @param FacilityDocumentService $facilityDocumentService
     * @return JsonResponse
     */
    public function deleteBulkAction(Request $request, FacilityDocumentService $facilityDocumentService): JsonResponse
    {
        $facilityDocumentService->removeBulk($request->get('ids'));

        return $this->respondSuccess(
            Response::HTTP_NO_CONTENT
        );
    }

    /**
     * @Route("/related/info", name="api_admin_facility_document_related_info", methods={"POST"})
     *
     * @param Request $request
     * @param FacilityDocumentService $facilityDocumentService
     * @return JsonResponse
     */
    public function relatedInfoAction(Request $request, FacilityDocumentService $facilityDocumentService): JsonResponse
    {
        $relatedData = $facilityDocumentService->getRelatedInfo($request->get('ids'));

        return $this->respondSuccess(
            Response::HTTP_OK,
            '',
            [$relatedData]
        );
    }

    /**
     * @Route("/download/{id}", requirements={"id"="\d+"}, name="api_admin_facility_document_download", methods={"GET"})
     *
     * @param Request $request
     * @param $id
     * @param FacilityDocumentService $facilityDocumentService
     * @return Response
     */
    public function downloadAction(Request $request, $id, FacilityDocumentService $facilityDocumentService): Response
    {
        $data = $facilityDocumentService->downloadFile($id);

        return $this->respondResource($data[0], $data[1], $data[2]);
    }
}
