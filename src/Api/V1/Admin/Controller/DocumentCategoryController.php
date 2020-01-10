<?php

namespace App\Api\V1\Admin\Controller;

use App\Annotation\Grant;
use App\Api\V1\Admin\Service\DocumentCategoryService;
use App\Api\V1\Common\Controller\BaseController;
use App\Entity\DocumentCategory;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\JsonResponse;
use Knp\Bundle\SnappyBundle\Snappy\Response\PdfResponse;

/**
 * @Route("/api/v1.0/admin/document_category")
 *
 * @Grant(grant="persistence-common-document_category", level="VIEW")
 *
 * Class DocumentCategoryController
 * @package App\Api\V1\Admin\Controller
 */
class DocumentCategoryController extends BaseController
{
    /**
     * @Route("/grid", name="api_admin_document_category_grid", methods={"GET"})
     *
     * @param Request $request
     * @param DocumentCategoryService $documentCategory
     * @return JsonResponse
     */
    public function gridAction(Request $request, DocumentCategoryService $documentCategory): JsonResponse
    {
        return $this->respondGrid(
            $request,
            DocumentCategory::class,
            'api_admin_document_category_grid',
            $documentCategory
        );
    }

    /**
     * @Route("/grid", name="api_admin_document_category_grid_options", methods={"OPTIONS"})
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function gridOptionAction(Request $request): JsonResponse
    {
        return $this->getOptionsByGroupName($request, DocumentCategory::class, 'api_admin_document_category_grid');
    }

    /**
     * @Route("", name="api_admin_document_category_list", methods={"GET"})
     *
     * @param Request $request
     * @param DocumentCategoryService $documentCategory
     * @return PdfResponse|JsonResponse|Response
     */
    public function listAction(Request $request, DocumentCategoryService $documentCategory)
    {
        return $this->respondList(
            $request,
            DocumentCategory::class,
            'api_admin_document_category_list',
            $documentCategory
        );
    }

    /**
     * @Route("/{id}", requirements={"id"="\d+"}, name="api_admin_document_category_get", methods={"GET"})
     *
     * @param Request $request
     * @param $id
     * @param DocumentCategoryService $documentCategory
     * @return JsonResponse
     */
    public function getAction(Request $request, $id, DocumentCategoryService $documentCategory): JsonResponse
    {
        return $this->respondSuccess(
            Response::HTTP_OK,
            '',
            $documentCategory->getById($id),
            ['api_admin_document_category_get']
        );
    }

    /**
     * @Route("", name="api_admin_document_category_add", methods={"POST"})
     *
     * @Grant(grant="persistence-common-document_category", level="ADD")
     *
     * @param Request $request
     * @param DocumentCategoryService $documentCategory
     * @return JsonResponse
     */
    public function addAction(Request $request, DocumentCategoryService $documentCategory): JsonResponse
    {
        $id = $documentCategory->add(
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
     * @Route("/{id}", requirements={"id"="\d+"}, name="api_admin_document_category_edit", methods={"PUT"})
     *
     * @Grant(grant="persistence-common-document_category", level="EDIT")
     *
     * @param Request $request
     * @param $id
     * @param DocumentCategoryService $documentCategory
     * @return JsonResponse
     */
    public function editAction(Request $request, $id, DocumentCategoryService $documentCategory): JsonResponse
    {
        $documentCategory->edit(
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
     * @Route("/{id}", requirements={"id"="\d+"}, name="api_admin_document_category_delete", methods={"DELETE"})
     *
     * @Grant(grant="persistence-common-document_category", level="DELETE")
     *
     * @param Request $request
     * @param $id
     * @param DocumentCategoryService $documentCategory
     * @return JsonResponse
     */
    public function deleteAction(Request $request, $id, DocumentCategoryService $documentCategory): JsonResponse
    {
        $documentCategory->remove($id);

        return $this->respondSuccess(
            Response::HTTP_NO_CONTENT
        );
    }

    /**
     * @Route("", name="api_admin_document_category_delete_bulk", methods={"DELETE"})
     *
     * @Grant(grant="persistence-common-document_category", level="DELETE")
     *
     * @param Request $request
     * @param DocumentCategoryService $documentCategory
     * @return JsonResponse
     */
    public function deleteBulkAction(Request $request, DocumentCategoryService $documentCategory): JsonResponse
    {
        $documentCategory->removeBulk($request->get('ids'));

        return $this->respondSuccess(
            Response::HTTP_NO_CONTENT
        );
    }

    /**
     * @Route("/related/info", name="api_admin_document_category_related_info", methods={"POST"})
     *
     * @param Request $request
     * @param DocumentCategoryService $documentCategory
     * @return JsonResponse
     */
    public function relatedInfoAction(Request $request, DocumentCategoryService $documentCategory): JsonResponse
    {
        $relatedData = $documentCategory->getRelatedInfo($request->get('ids'));

        return $this->respondSuccess(
            Response::HTTP_OK,
            '',
            [$relatedData]
        );
    }
}
