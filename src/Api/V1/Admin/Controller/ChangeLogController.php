<?php

namespace App\Api\V1\Admin\Controller;

use App\Annotation\Grant;
use App\Api\V1\Admin\Service\ChangeLogService;
use App\Api\V1\Common\Controller\BaseController;
use App\Entity\ChangeLog;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\JsonResponse;
use Knp\Bundle\SnappyBundle\Snappy\Response\PdfResponse;

/**
 * @Route("/api/v1.0/admin/change-log")
 *
 * @Grant(grant="persistence-common-change_log", level="VIEW")
 *
 * Class ChangeLogController
 * @package App\Api\V1\Admin\Controller
 */
class ChangeLogController extends BaseController
{
    /**
     * @Route("/grid", name="api_admin_change_log", methods={"GET"})
     *
     * @param Request $request
     * @param ChangeLogService $activityTypeService
     * @return JsonResponse
     */
    public function gridAction(Request $request, ChangeLogService $activityTypeService): JsonResponse
    {
        return $this->respondGrid(
            $request,
            ChangeLog::class,
            'api_admin_change_log_grid',
            $activityTypeService
        );
    }

    /**
     * @Route("/grid", name="api_admin_change_log_grid_options", methods={"OPTIONS"})
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function gridOptionAction(Request $request): JsonResponse
    {
        return $this->getOptionsByGroupName($request, ChangeLog::class, 'api_admin_change_log_grid');
    }

    /**
     * @Route("", name="api_admin_change_log_list", methods={"GET"})
     *
     * @param Request $request
     * @param ChangeLogService $activityTypeService
     * @return PdfResponse|JsonResponse|Response
     */
    public function listAction(Request $request, ChangeLogService $activityTypeService)
    {
        return $this->respondList(
            $request,
            ChangeLog::class,
            'api_admin_change_log_list',
            $activityTypeService
        );
    }

    /**
     * @Route("/{id}", requirements={"id"="\d+"}, name="api_admin_change_log_get", methods={"GET"})
     *
     * @param Request $request
     * @param $id
     * @param ChangeLogService $activityTypeService
     * @return JsonResponse
     */
    public function getAction(Request $request, $id, ChangeLogService $activityTypeService): JsonResponse
    {
        return $this->respondSuccess(
            Response::HTTP_OK,
            '',
            $activityTypeService->getById($id),
            ['api_admin_change_log_get']
        );
    }

    /**
     * @Route("/{id}", requirements={"id"="\d+"}, name="api_admin_change_log_delete", methods={"DELETE"})
     *
     * @Grant(grant="persistence-common-change_log", level="DELETE")
     *
     * @param Request $request
     * @param $id
     * @param ChangeLogService $activityTypeService
     * @return JsonResponse
     */
    public function deleteAction(Request $request, $id, ChangeLogService $activityTypeService): JsonResponse
    {
        $activityTypeService->remove($id);

        return $this->respondSuccess(
            Response::HTTP_NO_CONTENT
        );
    }

    /**
     * @Route("", name="api_admin_change_log_delete_bulk", methods={"DELETE"})
     *
     * @Grant(grant="persistence-common-change_log", level="DELETE")
     *
     * @param Request $request
     * @param ChangeLogService $activityTypeService
     * @return JsonResponse
     */
    public function deleteBulkAction(Request $request, ChangeLogService $activityTypeService): JsonResponse
    {
        $activityTypeService->removeBulk($request->get('ids'));

        return $this->respondSuccess(
            Response::HTTP_NO_CONTENT
        );
    }

    /**
     * @Route("/related/info", name="api_admin_change_log_related_info", methods={"POST"})
     *
     * @param Request $request
     * @param ChangeLogService $activityTypeService
     * @return JsonResponse
     */
    public function relatedInfoAction(Request $request, ChangeLogService $activityTypeService): JsonResponse
    {
        $relatedData = $activityTypeService->getRelatedInfo($request->get('ids'));

        return $this->respondSuccess(
            Response::HTTP_OK,
            '',
            [$relatedData]
        );
    }
}
