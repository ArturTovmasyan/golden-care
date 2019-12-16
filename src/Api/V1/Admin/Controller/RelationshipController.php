<?php
namespace App\Api\V1\Admin\Controller;

use App\Api\V1\Admin\Service\RelationshipService;
use App\Api\V1\Common\Controller\BaseController;
use App\Entity\Relationship;
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
 * @Route("/api/v1.0/admin/relationship")
 *
 * @Grant(grant="persistence-common-relationship", level="VIEW")
 *
 * Class RelationshipController
 * @package App\Api\V1\Admin\Controller
 */
class RelationshipController extends BaseController
{
    /**
     * @Route("/grid", name="api_admin_relationship_grid", methods={"GET"})
     *
     * @param Request $request
     * @param RelationshipService $relationshipService
     * @return JsonResponse|PdfResponse
     * @throws \ReflectionException
     */
    public function gridAction(Request $request, RelationshipService $relationshipService)
    {
        return $this->respondGrid(
            $request,
            Relationship::class,
            'api_admin_relationship_grid',
            $relationshipService
        );
    }

    /**
     * @Route("/grid", name="api_admin_relationship_grid_options", methods={"OPTIONS"})
     *
     * @param Request $request
     * @return JsonResponse
     * @throws \ReflectionException
     */
    public function gridOptionAction(Request $request)
    {
        return $this->getOptionsByGroupName($request, Relationship::class, 'api_admin_relationship_grid');
    }

    /**
     * @Route("", name="api_admin_relationship_list", methods={"GET"})
     *
     * @param Request $request
     * @param RelationshipService $relationshipService
     * @return JsonResponse|PdfResponse
     * @throws \ReflectionException
     */
    public function listAction(Request $request, RelationshipService $relationshipService)
    {
        return $this->respondList(
            $request,
            Relationship::class,
            'api_admin_relationship_list',
            $relationshipService
        );
    }

    /**
     * @Route("/{id}", requirements={"id"="\d+"}, name="api_admin_relationship_get", methods={"GET"})
     *
     * @param Request $request
     * @param RelationshipService $relationshipService
     * @param $id
     * @return JsonResponse
     */
    public function getAction(Request $request, $id, RelationshipService $relationshipService)
    {
        return $this->respondSuccess(
            Response::HTTP_OK,
            '',
            $relationshipService->getById($id),
            ['api_admin_relationship_get']
        );
    }

    /**
     * @Route("", name="api_admin_relationship_add", methods={"POST"})
     *
     * @Grant(grant="persistence-common-relationship", level="ADD")
     *
     * @param Request $request
     * @param RelationshipService $relationshipService
     * @return JsonResponse
     * @throws \Doctrine\DBAL\ConnectionException
     */
    public function addAction(Request $request, RelationshipService $relationshipService)
    {
        $id = $relationshipService->add(
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
     * @Route("/{id}", requirements={"id"="\d+"}, name="api_admin_relationship_edit", methods={"PUT"})
     *
     * @Grant(grant="persistence-common-relationship", level="EDIT")
     *
     * @param Request $request
     * @param $id
     * @param RelationshipService $relationshipService
     * @return JsonResponse
     * @throws \Doctrine\DBAL\ConnectionException
     */
    public function editAction(Request $request, $id, RelationshipService $relationshipService)
    {
        $relationshipService->edit(
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
     * @Route("/{id}", requirements={"id"="\d+"}, name="api_admin_relationship_delete", methods={"DELETE"})
     *
     * @Grant(grant="persistence-common-relationship", level="DELETE")
     *
     * @param Request $request
     * @param $id
     * @param RelationshipService $relationshipService
     * @return JsonResponse
     * @throws \Doctrine\DBAL\ConnectionException
     * @throws \Throwable
     */
    public function deleteAction(Request $request, $id, RelationshipService $relationshipService)
    {
        $relationshipService->remove($id);

        return $this->respondSuccess(
            Response::HTTP_NO_CONTENT
        );
    }

    /**
     * @Route("", name="api_admin_relationship_delete_bulk", methods={"DELETE"})
     *
     * @Grant(grant="persistence-common-relationship", level="DELETE")
     *
     * @param Request $request
     * @param RelationshipService $relationshipService
     * @return JsonResponse
     * @throws \Doctrine\DBAL\ConnectionException
     * @throws \Throwable
     */
    public function deleteBulkAction(Request $request, RelationshipService $relationshipService)
    {
        $relationshipService->removeBulk($request->get('ids'));

        return $this->respondSuccess(
            Response::HTTP_NO_CONTENT
        );
    }

    /**
     * @Route("/related/info", name="api_admin_relationship_related_info", methods={"POST"})
     *
     * @param Request $request
     * @param RelationshipService $relationshipService
     * @return JsonResponse
     * @throws \Doctrine\DBAL\ConnectionException
     * @throws \Throwable
     */
    public function relatedInfoAction(Request $request, RelationshipService $relationshipService)
    {
        $relatedData = $relationshipService->getRelatedInfo($request->get('ids'));

        return $this->respondSuccess(
            Response::HTTP_OK,
            '',
            [$relatedData]
        );
    }
}
