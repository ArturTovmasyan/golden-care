<?php
namespace App\Api\V1\Admin\Controller;

use App\Api\V1\Admin\Service\SpaceService;
use App\Api\V1\Common\Controller\BaseController;
use App\Entity\Space;
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
 * @Route("/api/v1.0/admin/space")
 *
 * @Grant(grant="persistence-security-space", level="VIEW")
 *
 * Class SpaceController
 * @package App\Api\V1\Admin\Controller
 */
class SpaceController extends BaseController
{
    /**
     * @Route("/grid", name="api_admin_space_grid", methods={"GET"})
     *
     * @param Request $request
     * @param SpaceService $spaceService
     * @return JsonResponse|PdfResponse
     * @throws \ReflectionException
     */
    public function gridAction(Request $request, SpaceService $spaceService)
    {
        return $this->respondGrid(
            $request,
            Space::class,
            'api_admin_space_grid',
            $spaceService
        );
    }

    /**
     * @Route("/grid", name="api_admin_space_grid_options", methods={"OPTIONS"})
     *
     * @param Request $request
     * @return JsonResponse
     * @throws \ReflectionException
     */
    public function gridOptionAction(Request $request)
    {
        return $this->getOptionsByGroupName($request, Space::class, 'api_admin_space_grid');
    }

    /**
     * @Route("", name="api_admin_space_list", methods={"GET"})
     *
     * @param Request $request
     * @param SpaceService $spaceService
     * @return JsonResponse|PdfResponse
     * @throws \ReflectionException
     */
    public function listAction(Request $request, SpaceService $spaceService)
    {
        return $this->respondList(
            $request,
            Space::class,
            'api_admin_space_list',
            $spaceService
        );
    }

    /**
     * @Route("/{id}", requirements={"id"="\d+"}, name="api_admin_space_get", methods={"GET"})
     *
     * @param Request $request
     * @param SpaceService $spaceService
     * @param $id
     * @return JsonResponse
     */
    public function getAction(Request $request, $id, SpaceService $spaceService)
    {
        return $this->respondSuccess(
            Response::HTTP_OK,
            '',
            $spaceService->getById($id),
            ['api_admin_space_get']
        );
    }

    /**
     * @Route("", name="api_admin_space_add", methods={"POST"})
     *
     * @Grant(grant="persistence-security-space", level="ADD")
     *
     * @param Request $request
     * @param SpaceService $spaceService
     * @return JsonResponse
     * @throws \Throwable
     */
    public function addAction(Request $request, SpaceService $spaceService)
    {
        $id = $spaceService->add(
            [
                'name' => $request->get('name')
            ]
        );

        return $this->respondSuccess(
            Response::HTTP_CREATED,
            '',
            [$id]
        );
    }

    /**
     * @Route("/{id}", requirements={"id"="\d+"}, name="api_admin_space_edit", methods={"PUT"})
     *
     * @Grant(grant="persistence-security-space", level="EDIT")
     *
     * @param Request $request
     * @param $id
     * @param SpaceService $spaceService
     * @return JsonResponse
     * @throws \Doctrine\DBAL\ConnectionException
     */
    public function editAction(Request $request, $id, SpaceService $spaceService)
    {
        $spaceService->edit(
            $id,
            [
                'name' => $request->get('name')
            ]
        );

        return $this->respondSuccess(
            Response::HTTP_CREATED
        );
    }

    /**
     * @Route("/{id}", requirements={"id"="\d+"}, name="api_admin_space_delete", methods={"DELETE"})
     *
     * @Grant(grant="persistence-security-space", level="DELETE")
     *
     * @param $id
     * @param SpaceService $spaceService
     * @return JsonResponse
     * @throws \Doctrine\DBAL\ConnectionException
     * @throws \Throwable
     */
    public function deleteAction(Request $request, $id, SpaceService $spaceService)
    {
        $spaceService->remove($id);

        return $this->respondSuccess(
            Response::HTTP_NO_CONTENT
        );
    }

    /**
     * @Route("", name="api_admin_space_delete_bulk", methods={"DELETE"})
     *
     * @Grant(grant="persistence-security-space", level="DELETE")
     *
     * @param Request $request
     * @param SpaceService $spaceService
     * @return JsonResponse
     * @throws \Doctrine\DBAL\ConnectionException
     * @throws \Throwable
     */
    public function deleteBulkAction(Request $request, SpaceService $spaceService)
    {
        $spaceService->removeBulk($request->get('ids'));

        return $this->respondSuccess(
            Response::HTTP_NO_CONTENT
        );
    }

    /**
     * @Route("/related/info", name="api_admin_space_related_info", methods={"POST"})
     *
     * @param Request $request
     * @param SpaceService $spaceService
     * @return JsonResponse
     * @throws \Doctrine\DBAL\ConnectionException
     * @throws \Throwable
     */
    public function relatedInfoAction(Request $request, SpaceService $spaceService)
    {
        $relatedData = $spaceService->getRelatedInfo($request->get('ids'));

        return $this->respondSuccess(
            Response::HTTP_OK,
            '',
            [$relatedData]
        );
    }
}
