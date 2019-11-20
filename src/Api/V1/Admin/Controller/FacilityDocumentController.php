<?php
namespace App\Api\V1\Admin\Controller;

use App\Api\V1\Admin\Service\FacilityDocumentService;
use App\Api\V1\Common\Controller\BaseController;
use App\Entity\FacilityDocument;
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
 * @Route("/api/v1.0/admin/facility/document")
 *
 * @Grant(grant="persistence-facility_document", level="VIEW")
 *
 * Class FacilityDocumentController
 * @package App\Api\V1\Admin\Controller
 */
class FacilityDocumentController extends BaseController
{
    /**
     * @Route("/grid", name="api_admin_facility_document_grid", methods={"GET"})
     *
     * @param Request $request
     * @param FacilityDocumentService $facilityDocumentService
     * @return JsonResponse|PdfResponse
     * @throws \ReflectionException
     */
    public function gridAction(Request $request, FacilityDocumentService $facilityDocumentService)
    {
        return $this->respondGrid(
            $request,
            FacilityDocument::class,
            'api_admin_facility_document_grid',
            $facilityDocumentService,
            ['facility_id' => $request->get('facility_id')]
        );
    }

    /**
     * @Route("/grid", name="api_admin_facility_document_grid_options", methods={"OPTIONS"})
     *
     * @param Request $request
     * @return JsonResponse
     * @throws \ReflectionException
     */
    public function gridOptionAction(Request $request)
    {
        return $this->getOptionsByGroupName($request, FacilityDocument::class, 'api_admin_facility_document_grid');
    }

    /**
     * @Route("", name="api_admin_facility_document_list", methods={"GET"})
     *
     * @param Request $request
     * @param FacilityDocumentService $facilityDocumentService
     * @return JsonResponse|PdfResponse
     * @throws \ReflectionException
     */
    public function listAction(Request $request, FacilityDocumentService $facilityDocumentService)
    {
        return $this->respondList(
            $request,
            FacilityDocument::class,
            'api_admin_facility_document_list',
            $facilityDocumentService,
            ['facility_id' => $request->get('facility_id')]
        );
    }

    /**
     * @Route("/{id}", requirements={"id"="\d+"}, name="api_admin_facility_document_get", methods={"GET"})
     *
     * @param Request $request
     * @param FacilityDocumentService $facilityDocumentService
     * @param $id
     * @return JsonResponse
     */
    public function getAction(Request $request, $id, FacilityDocumentService $facilityDocumentService)
    {
        $entity = $facilityDocumentService->getById($id);

        if ($entity !== null && $entity->getFile() !== null) {
            $downloadUrl = $request->getScheme().'://'. $request->getHttpHost().$this->generateUrl('api_admin_facility_document_download', ['id' => $entity->getId()]);

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
     * @throws \Throwable
     */
    public function addAction(Request $request, FacilityDocumentService $facilityDocumentService)
    {
        $id = $facilityDocumentService->add(
            [
                'facility_id' => $request->get('facility_id'),
                'title' => $request->get('title'),
                'file' => $request->get('file')
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
     * @throws \Throwable
     */
    public function editAction(Request $request, $id, FacilityDocumentService $facilityDocumentService)
    {
        $facilityDocumentService->edit(
            $id,
            [
                'facility_id' => $request->get('facility_id'),
                'title' => $request->get('title'),
                'file' => $request->get('file')
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
     * @param $id
     * @param FacilityDocumentService $facilityDocumentService
     * @return JsonResponse
     * @throws \Doctrine\DBAL\ConnectionException
     * @throws \Throwable
     */
    public function deleteAction(Request $request, $id, FacilityDocumentService $facilityDocumentService)
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
     * @throws \Doctrine\DBAL\ConnectionException
     * @throws \Throwable
     */
    public function deleteBulkAction(Request $request, FacilityDocumentService $facilityDocumentService)
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
     * @throws \Doctrine\DBAL\ConnectionException
     * @throws \Throwable
     */
    public function relatedInfoAction(Request $request, FacilityDocumentService $facilityDocumentService)
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
     * @param FacilityDocumentService $facilityDocumentService
     * @param $id
     * @return Response
     */
    public function downloadAction(Request $request, $id, FacilityDocumentService $facilityDocumentService)
    {
        $data = $facilityDocumentService->downloadFile($id);

        return $this->respondResource($data[0], $data[1], $data[2]);
    }
}
