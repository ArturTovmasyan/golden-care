<?php
namespace App\Api\V1\Admin\Controller;

use App\Api\V1\Admin\Service\RegionService;
use App\Api\V1\Common\Controller\BaseController;
use App\Entity\Region;
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
 * @Route("/api/v1.0/admin/region")
 *
 * @Grant(grant="persistence-region", level="VIEW")
 *
 * Class RegionController
 * @package App\Api\V1\Admin\Controller
 */
class RegionController extends BaseController
{
    /**
     * @Route("/grid", name="api_admin_region_grid", methods={"GET"})
     *
     * @param Request $request
     * @param RegionService $regionService
     * @return JsonResponse|PdfResponse
     * @throws \ReflectionException
     */
    public function gridAction(Request $request, RegionService $regionService)
    {
        return $this->respondGrid(
            $request,
            Region::class,
            'api_admin_region_grid',
            $regionService
        );
    }

    /**
     * @Route("/grid", name="api_admin_region_grid_options", methods={"OPTIONS"})
     *
     * @param Request $request
     * @return JsonResponse
     * @throws \ReflectionException
     */
    public function gridOptionAction(Request $request)
    {
        return $this->getOptionsByGroupName($request, Region::class, 'api_admin_region_grid');
    }

    /**
     * @Route("", name="api_admin_region_list", methods={"GET"})
     *
     * @param Request $request
     * @param RegionService $regionService
     * @return JsonResponse|PdfResponse
     * @throws \ReflectionException
     */
    public function listAction(Request $request, RegionService $regionService)
    {
        return $this->respondList(
            $request,
            Region::class,
            'api_admin_region_list',
            $regionService
        );
    }

    /**
     * @Route("/{id}", requirements={"id"="\d+"}, name="api_admin_region_get", methods={"GET"})
     *
     * @param RegionService $regionService
     * @param $id
     * @return JsonResponse
     */
    public function getAction(Request $request, $id, RegionService $regionService)
    {
        return $this->respondSuccess(
            Response::HTTP_OK,
            '',
            $regionService->getById($id),
            ['api_admin_region_get']
        );
    }

    /**
     * @Route("", name="api_admin_region_add", methods={"POST"})
     *
     * @Grant(grant="persistence-region", level="ADD")
     *
     * @param Request $request
     * @param RegionService $regionService
     * @return JsonResponse
     * @throws \Throwable
     */
    public function addAction(Request $request, RegionService $regionService)
    {
        $id = $regionService->add(
            [
                'name' => $request->get('name'),
                'description' => $request->get('description') ?? '',
                'shorthand' => $request->get('shorthand'),
                'phone' => $request->get('phone') ?? '',
                'fax' => $request->get('fax') ?? '',
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
     * @Route("/{id}", requirements={"id"="\d+"}, name="api_admin_region_edit", methods={"PUT"})
     *
     * @Grant(grant="persistence-region", level="EDIT")
     *
     * @param Request $request
     * @param $id
     * @param RegionService $regionService
     * @return JsonResponse
     * @throws \Throwable
     */
    public function editAction(Request $request, $id, RegionService $regionService)
    {
        $regionService->edit(
            $id,
            [
                'name' => $request->get('name'),
                'description' => $request->get('description') ?? '',
                'shorthand' => $request->get('shorthand'),
                'phone' => $request->get('phone') ?? '',
                'fax' => $request->get('fax') ?? '',
                'space_id' => $request->get('space_id')
            ]
        );

        return $this->respondSuccess(
            Response::HTTP_CREATED
        );
    }

    /**
     * @Route("/{id}", requirements={"id"="\d+"}, name="api_admin_region_delete", methods={"DELETE"})
     *
     * @Grant(grant="persistence-region", level="DELETE")
     *
     * @param $id
     * @param RegionService $regionService
     * @return JsonResponse
     * @throws \Doctrine\DBAL\ConnectionException
     * @throws \Throwable
     */
    public function deleteAction(Request $request, $id, RegionService $regionService)
    {
        $regionService->remove($id);

        return $this->respondSuccess(
            Response::HTTP_NO_CONTENT
        );
    }

    /**
     * @Route("", name="api_admin_region_delete_bulk", methods={"DELETE"})
     *
     * @Grant(grant="persistence-region", level="DELETE")
     *
     * @param Request $request
     * @param RegionService $regionService
     * @return JsonResponse
     * @throws \Doctrine\DBAL\ConnectionException
     * @throws \Throwable
     */
    public function deleteBulkAction(Request $request, RegionService $regionService)
    {
        $regionService->removeBulk($request->get('ids'));

        return $this->respondSuccess(
            Response::HTTP_NO_CONTENT
        );
    }

    /**
     * @Route("/related/info", name="api_admin_region_related_info", methods={"POST"})
     *
     * @param Request $request
     * @param RegionService $regionService
     * @return JsonResponse
     * @throws \Doctrine\DBAL\ConnectionException
     * @throws \Throwable
     */
    public function relatedInfoAction(Request $request, RegionService $regionService)
    {
        $relatedData = $regionService->getRelatedInfo($request->get('ids'));

        return $this->respondSuccess(
            Response::HTTP_OK,
            '',
            [$relatedData]
        );
    }

    /**
     * @Route("/mobile/list", name="api_admin_region_mobile_list", methods={"GET"})
     *
     * @param Request $request
     * @param RegionService $regionService
     * @return JsonResponse
     */
    public function getMobileListAction(Request $request, RegionService $regionService)
    {
        return $this->respondSuccess(
            Response::HTTP_OK,
            '',
            $regionService->getMobileList($request->headers->get('date')),
            ['api_admin_region_mobile_list']
        );
    }
}
