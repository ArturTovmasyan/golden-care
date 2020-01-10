<?php

namespace App\Api\V1\Admin\Controller;

use App\Annotation\Grant;
use App\Api\V1\Admin\Service\DocumentService;
use App\Api\V1\Common\Controller\BaseController;
use App\Api\V1\Common\Service\S3Service;
use App\Entity\Document;
use App\Entity\User;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\JsonResponse;
use Knp\Bundle\SnappyBundle\Snappy\Response\PdfResponse;

/**
 * @Route("/api/v1.0/admin/document")
 *
 * @Grant(grant="persistence-common-document", level="VIEW")
 *
 * Class DocumentController
 * @package App\Api\V1\Admin\Controller
 */
class DocumentController extends BaseController
{
    /**
     * @Route("/grid", name="api_admin_document_grid", methods={"GET"})
     *
     * @param Request $request
     * @param DocumentService $documentService
     * @return JsonResponse
     */
    public function gridAction(Request $request, DocumentService $documentService): JsonResponse
    {
        /** @var User $user */
        $user = $this->get('security.token_storage')->getToken()->getUser();

        $isAdmin = false;
        $userRoleIds = [];
        if ($user !== null) {
            $isAdmin = $this->grantService->hasCurrentUserGrant('activity-is_administrator');

            $userRoles = $user->getRoleObjects();

            if ($userRoles !== null) {
                $userRoleIds = array_map(function ($item) {
                    return $item->getId();
                }, $userRoles->toArray());
            }
        }

        return $this->respondGrid(
            $request,
            Document::class,
            'api_admin_document_grid',
            $documentService,
            [
                'category_id' => $request->get('category_id'),
                'user_role_ids' => $userRoleIds,
                'is_admin' => $isAdmin
            ]
        );
    }

    /**
     * @Route("/grid", name="api_admin_document_grid_options", methods={"OPTIONS"})
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function gridOptionAction(Request $request): JsonResponse
    {
        return $this->getOptionsByGroupName($request, Document::class, 'api_admin_document_grid');
    }

    /**
     * @Route("", name="api_admin_document_list", methods={"GET"})
     *
     * @param Request $request
     * @param DocumentService $documentService
     * @return PdfResponse|JsonResponse|Response
     */
    public function listAction(Request $request, DocumentService $documentService)
    {
        /** @var User $user */
        $user = $this->get('security.token_storage')->getToken()->getUser();

        $isAdmin = false;
        $userRoleIds = [];
        if ($user !== null) {
            $isAdmin = $this->grantService->hasCurrentUserGrant('activity-is_administrator');

            $userRoles = $user->getRoleObjects();

            if ($userRoles !== null) {
                $userRoleIds = array_map(function ($item) {
                    return $item->getId();
                }, $userRoles->toArray());
            }
        }

        return $this->respondList(
            $request,
            Document::class,
            'api_admin_document_list',
            $documentService,
            [
                'category_id' => $request->get('category_id'),
                'user_role_ids' => $userRoleIds,
                'is_admin' => $isAdmin
            ]
        );
    }

    /**
     * @Route("/{id}", requirements={"id"="\d+"}, name="api_admin_document_get", methods={"GET"})
     *
     * @param Request $request
     * @param $id
     * @param DocumentService $documentService
     * @param S3Service $s3Service
     * @return JsonResponse
     */
    public function getAction(Request $request, $id, DocumentService $documentService, S3Service $s3Service): JsonResponse
    {
        $entity = $documentService->getById($id);

        if ($entity !== null && $entity->getFile() !== null) {
            $cmd = $s3Service->getS3Client()->getCommand('GetObject', [
                'Bucket' => getenv('AWS_BUCKET'),
                'Key' => $entity->getFile()->getType() . '/' . $entity->getFile()->getS3Id(),
            ]);
            $s3Request = $s3Service->getS3Client()->createPresignedRequest($cmd, '+20 minutes');

            $entity->setDownloadUrl((string)$s3Request->getUri());
        } else {
            $entity->setDownloadUrl(null);
        }

        return $this->respondSuccess(
            Response::HTTP_OK,
            '',
            $documentService->getById($id),
            ['api_admin_document_get']
        );
    }

    /**
     * @Route("", name="api_admin_document_add", methods={"POST"})
     *
     * @Grant(grant="persistence-common-document", level="ADD")
     *
     * @param Request $request
     * @param DocumentService $documentService
     * @return JsonResponse
     */
    public function addAction(Request $request, DocumentService $documentService): JsonResponse
    {
        $id = $documentService->add(
            [
                'category_id' => $request->get('category_id'),
                'title' => $request->get('title'),
                'description' => $request->get('description') ?? '',
                'facilities' => $request->get('facilities'),
                'file' => $request->get('file'),
                'roles' => $request->get('roles'),
                'notification' => $request->get('notification'),
                'emails' => $request->get('emails'),
            ],
            $request->getSchemeAndHttpHost()
        );

        return $this->respondSuccess(
            Response::HTTP_CREATED,
            '',
            [$id]
        );
    }

    /**
     * @Route("/{id}", requirements={"id"="\d+"}, name="api_admin_document_edit", methods={"PUT"})
     *
     * @Grant(grant="persistence-common-document", level="EDIT")
     *
     * @param Request $request
     * @param $id
     * @param DocumentService $documentService
     * @return JsonResponse
     */
    public function editAction(Request $request, $id, DocumentService $documentService): JsonResponse
    {
        $documentService->edit(
            $id,
            [
                'category_id' => $request->get('category_id'),
                'title' => $request->get('title'),
                'description' => $request->get('description') ?? '',
                'facilities' => $request->get('facilities'),
                'file' => $request->get('file'),
                'roles' => $request->get('roles'),
                'notification' => $request->get('notification'),
                'emails' => $request->get('emails'),
            ],
            $request->getSchemeAndHttpHost()
        );

        return $this->respondSuccess(
            Response::HTTP_CREATED
        );
    }

    /**
     * @Route("/{id}", requirements={"id"="\d+"}, name="api_admin_document_delete", methods={"DELETE"})
     *
     * @Grant(grant="persistence-common-document", level="DELETE")
     *
     * @param Request $request
     * @param $id
     * @param DocumentService $documentService
     * @return JsonResponse
     */
    public function deleteAction(Request $request, $id, DocumentService $documentService): JsonResponse
    {
        $documentService->remove($id);

        return $this->respondSuccess(
            Response::HTTP_NO_CONTENT
        );
    }

    /**
     * @Route("", name="api_admin_document_delete_bulk", methods={"DELETE"})
     *
     * @Grant(grant="persistence-common-document", level="DELETE")
     *
     * @param Request $request
     * @param DocumentService $documentService
     * @return JsonResponse
     */
    public function deleteBulkAction(Request $request, DocumentService $documentService): JsonResponse
    {
        $documentService->removeBulk($request->get('ids'));

        return $this->respondSuccess(
            Response::HTTP_NO_CONTENT
        );
    }

    /**
     * @Route("/related/info", name="api_admin_document_related_info", methods={"POST"})
     *
     * @param Request $request
     * @param DocumentService $documentService
     * @return JsonResponse
     */
    public function relatedInfoAction(Request $request, DocumentService $documentService): JsonResponse
    {
        $relatedData = $documentService->getRelatedInfo($request->get('ids'));

        return $this->respondSuccess(
            Response::HTTP_OK,
            '',
            [$relatedData]
        );
    }

    /**
     * @Route("/download/{id}", requirements={"id"="\d+"}, name="api_admin_document_download", methods={"GET"})
     *
     * @param Request $request
     * @param $id
     * @param DocumentService $documentService
     * @return Response
     */
    public function downloadAction(Request $request, $id, DocumentService $documentService): Response
    {
        $data = $documentService->downloadFile($id);

        return $this->respondResource($data[0], $data[1], $data[2]);
    }
}
