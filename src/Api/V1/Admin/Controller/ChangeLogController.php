<?php
namespace App\Api\V1\Admin\Controller;

use App\Api\V1\Admin\Service\ChangeLogService;
use App\Api\V1\Common\Controller\BaseController;
use App\Entity\ChangeLog;
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
 * @Route("/api/v1.0/admin/change-log")
 *
 * @Grant(grant="persistence-common-change_log", level="VIEW")
 *
 * Class ChangeLogController
 * @package  App\Api\V1\Admin\Controller
 */
class ChangeLogController extends BaseController
{
    /**
     * @Route("/grid", name="api_admin_change_log", methods={"GET"})
     *
     * @param Request $request
     * @param ChangeLogService $activityTypeService
     * @return JsonResponse|PdfResponse
     * @throws \ReflectionException
     */
    public function gridAction(Request $request, ChangeLogService $activityTypeService)
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
     * @throws \ReflectionException
     */
    public function gridOptionAction(Request $request)
    {
        return $this->getOptionsByGroupName(ChangeLog::class, 'api_admin_change_log_grid');
    }

    /**
     * @Route("", name="api_admin_change_log_list", methods={"GET"})
     *
     * @param Request $request
     * @param ChangeLogService $activityTypeService
     * @return JsonResponse|PdfResponse
     * @throws \ReflectionException
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
     * @param ChangeLogService $activityTypeService
     * @param $id
     * @return JsonResponse
     */
    public function getAction(Request $request, $id, ChangeLogService $activityTypeService)
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
     * @param $id
     * @param ChangeLogService $activityTypeService
     * @return JsonResponse
     * @throws \Doctrine\DBAL\ConnectionException
     * @throws \Throwable
     */
    public function deleteAction(Request $request, $id, ChangeLogService $activityTypeService)
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
     * @throws \Doctrine\DBAL\ConnectionException
     * @throws \Throwable
     */
    public function deleteBulkAction(Request $request, ChangeLogService $activityTypeService)
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
     * @throws \Doctrine\DBAL\ConnectionException
     * @throws \Throwable
     */
    public function relatedInfoAction(Request $request, ChangeLogService $activityTypeService)
    {
        $relatedData = $activityTypeService->getRelatedInfo($request->get('ids'));

        return $this->respondSuccess(
            Response::HTTP_OK,
            '',
            [$relatedData]
        );
    }
}
