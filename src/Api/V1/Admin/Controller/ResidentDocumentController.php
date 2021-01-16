<?php

namespace App\Api\V1\Admin\Controller;

use App\Annotation\Grant;
use App\Api\V1\Admin\Service\ResidentDocumentService;
use App\Api\V1\Common\Controller\BaseController;
use App\Entity\ResidentDocument;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\JsonResponse;
use Knp\Bundle\SnappyBundle\Snappy\Response\PdfResponse;

/**
 * @Route("/api/v1.0/admin/resident/document")
 *
 * @Grant(grant="persistence-resident-resident_document", level="VIEW")
 *
 * Class ResidentDocumentController
 * @package App\Api\V1\Admin\Controller
 */
class ResidentDocumentController extends BaseController
{
    /**
     * @Route("/grid", name="api_admin_resident_document_grid", methods={"GET"})
     *
     * @param Request $request
     * @param ResidentDocumentService $residentDocumentService
     * @return JsonResponse
     */
    public function gridAction(Request $request, ResidentDocumentService $residentDocumentService): JsonResponse
    {
        return $this->respondGrid(
            $request,
            ResidentDocument::class,
            'api_admin_resident_document_grid',
            $residentDocumentService,
            ['resident_id' => $request->get('resident_id')]
        );
    }

    /**
     * @Route("/grid", name="api_admin_resident_document_grid_options", methods={"OPTIONS"})
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function gridOptionAction(Request $request): JsonResponse
    {
        return $this->getOptionsByGroupName($request, ResidentDocument::class, 'api_admin_resident_document_grid');
    }

    /**
     * @Route("", name="api_admin_resident_document_list", methods={"GET"})
     *
     * @param Request $request
     * @param ResidentDocumentService $residentDocumentService
     * @return PdfResponse|JsonResponse|Response
     */
    public function listAction(Request $request, ResidentDocumentService $residentDocumentService)
    {
        return $this->respondList(
            $request,
            ResidentDocument::class,
            'api_admin_resident_document_list',
            $residentDocumentService,
            ['resident_id' => $request->get('resident_id')]
        );
    }

    /**
     * @Route("/{id}", requirements={"id"="\d+"}, name="api_admin_resident_document_get", methods={"GET"})
     *
     * @param Request $request
     * @param $id
     * @param ResidentDocumentService $residentDocumentService
     * @return JsonResponse
     */
    public function getAction(Request $request, $id, ResidentDocumentService $residentDocumentService): JsonResponse
    {
        $entity = $residentDocumentService->getById($id);

        if ($entity !== null && $entity->getFile() !== null) {
            $downloadUrl = $request->getScheme() . '://' . $request->getHttpHost() . $this->generateUrl('api_admin_resident_document_download', ['id' => $entity->getId()]);

            $entity->setDownloadUrl($downloadUrl);
        } else {
            $entity->setDownloadUrl(null);
        }

        return $this->respondSuccess(
            Response::HTTP_OK,
            '',
            $residentDocumentService->getById($id),
            ['api_admin_resident_document_get']
        );
    }

    /**
     * @Route("", name="api_admin_resident_document_add", methods={"POST"})
     *
     * @Grant(grant="persistence-resident-resident_document", level="ADD")
     *
     * @param Request $request
     * @param ResidentDocumentService $residentDocumentService
     * @return JsonResponse
     */
    public function addAction(Request $request, ResidentDocumentService $residentDocumentService): JsonResponse
    {
        $id = $residentDocumentService->add(
            [
                'resident_id' => $request->get('resident_id'),
                'title' => $request->get('title'),
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
     * @Route("/{id}", requirements={"id"="\d+"}, name="api_admin_resident_document_edit", methods={"PUT"})
     *
     * @Grant(grant="persistence-resident-resident_document", level="EDIT")
     *
     * @param Request $request
     * @param $id
     * @param ResidentDocumentService $residentDocumentService
     * @return JsonResponse
     */
    public function editAction(Request $request, $id, ResidentDocumentService $residentDocumentService): JsonResponse
    {
        $residentDocumentService->edit(
            $id,
            [
                'resident_id' => $request->get('resident_id'),
                'title' => $request->get('title'),
                'file' => $request->get('file'),
                'file_name' => $request->get('file_name')
            ]
        );

        return $this->respondSuccess(
            Response::HTTP_CREATED
        );
    }

    /**
     * @Route("/{id}", requirements={"id"="\d+"}, name="api_admin_resident_document_delete", methods={"DELETE"})
     *
     * @Grant(grant="persistence-resident-resident_document", level="DELETE")
     *
     * @param Request $request
     * @param $id
     * @param ResidentDocumentService $residentDocumentService
     * @return JsonResponse
     */
    public function deleteAction(Request $request, $id, ResidentDocumentService $residentDocumentService): JsonResponse
    {
        $residentDocumentService->remove($id);

        return $this->respondSuccess(
            Response::HTTP_NO_CONTENT
        );
    }

    /**
     * @Route("", name="api_admin_resident_document_delete_bulk", methods={"DELETE"})
     *
     * @Grant(grant="persistence-resident-resident_document", level="DELETE")
     *
     * @param Request $request
     * @param ResidentDocumentService $residentDocumentService
     * @return JsonResponse
     */
    public function deleteBulkAction(Request $request, ResidentDocumentService $residentDocumentService): JsonResponse
    {
        $residentDocumentService->removeBulk($request->get('ids'));

        return $this->respondSuccess(
            Response::HTTP_NO_CONTENT
        );
    }

    /**
     * @Route("/related/info", name="api_admin_resident_document_related_info", methods={"POST"})
     *
     * @param Request $request
     * @param ResidentDocumentService $residentDocumentService
     * @return JsonResponse
     */
    public function relatedInfoAction(Request $request, ResidentDocumentService $residentDocumentService): JsonResponse
    {
        $relatedData = $residentDocumentService->getRelatedInfo($request->get('ids'));

        return $this->respondSuccess(
            Response::HTTP_OK,
            '',
            [$relatedData]
        );
    }

    /**
     * @Route("/download/{id}", requirements={"id"="\d+"}, name="api_admin_resident_document_download", methods={"GET"})
     *
     * @param Request $request
     * @param $id
     * @param ResidentDocumentService $residentDocumentService
     * @return Response
     */
    public function downloadAction(Request $request, $id, ResidentDocumentService $residentDocumentService): Response
    {
        $data = $residentDocumentService->downloadFile($id);

        return $this->respondResource($data[0], $data[1], $data[2]);
    }
}
