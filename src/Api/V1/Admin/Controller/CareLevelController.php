<?php
namespace App\Api\V1\Admin\Controller;

use App\Api\V1\Admin\Service\CareLevelService;
use App\Api\V1\Common\Controller\BaseController;
use App\Entity\CareLevel;
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
 * @Route("/api/v1.0/admin/care/level")
 *
 * @Grant(grant="persistence-common-care_level", level="VIEW")
 *
 * Class CareLevelController
 * @package App\Api\V1\Admin\Controller
 */
class CareLevelController extends BaseController
{
    /**
     * @Route("/grid", name="api_admin_care_level_grid", methods={"GET"})
     *
     * @param Request $request
     * @param CareLevelService $careLevelService
     * @return JsonResponse|PdfResponse
     * @throws \ReflectionException
     */
    public function gridAction(Request $request, CareLevelService $careLevelService)
    {
        return $this->respondGrid(
            $request,
            CareLevel::class,
            'api_admin_care_level_grid',
            $careLevelService
        );
    }

    /**
     * @Route("/grid", name="api_admin_care_level_grid_options", methods={"OPTIONS"})
     *
     * @param Request $request
     * @return JsonResponse
     * @throws \ReflectionException
     */
    public function gridOptionAction(Request $request)
    {
        return $this->getOptionsByGroupName($request, CareLevel::class, 'api_admin_care_level_grid');
    }

    /**
     * @Route("", name="api_admin_care_level_list", methods={"GET"})
     *
     * @param Request $request
     * @param CareLevelService $careLevelService
     * @return JsonResponse|PdfResponse
     * @throws \ReflectionException
     */
    public function listAction(Request $request, CareLevelService $careLevelService)
    {
        return $this->respondList(
            $request,
            CareLevel::class,
            'api_admin_care_level_list',
            $careLevelService
        );
    }

    /**
     * @Route("/{id}", requirements={"id"="\d+"}, name="api_admin_care_level_get", methods={"GET"})
     *
     * @param CareLevelService $careLevelService
     * @param $id
     * @return JsonResponse
     */
    public function getAction(Request $request, $id, CareLevelService $careLevelService)
    {
        return $this->respondSuccess(
            Response::HTTP_OK,
            '',
            $careLevelService->getById($id),
            ['api_admin_care_level_get']
        );
    }

    /**
     * @Route("", name="api_admin_care_level_add", methods={"POST"})
     *
     * @Grant(grant="persistence-common-care_level", level="ADD")
     *
     * @param Request $request
     * @param CareLevelService $careLevelService
     * @return JsonResponse
     * @throws \Throwable
     */
    public function addAction(Request $request, CareLevelService $careLevelService)
    {
        $id = $careLevelService->add(
            [
                'title' => $request->get('title'),
                'description' => $request->get('description') ?? '',
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
     * @Route("/{id}", requirements={"id"="\d+"}, name="api_admin_care_level_edit", methods={"PUT"})
     *
     * @Grant(grant="persistence-common-care_level", level="EDIT")
     *
     * @param Request $request
     * @param $id
     * @param CareLevelService $careLevelService
     * @return JsonResponse
     * @throws \Throwable
     */
    public function editAction(Request $request, $id, CareLevelService $careLevelService)
    {
        $careLevelService->edit(
            $id,
            [
                'title' => $request->get('title'),
                'description' => $request->get('description') ?? '',
                'space_id' => $request->get('space_id')
            ]
        );

        return $this->respondSuccess(
            Response::HTTP_CREATED
        );
    }

    /**
     * @Route("/{id}", requirements={"id"="\d+"}, name="api_admin_care_level_delete", methods={"DELETE"})
     *
     * @Grant(grant="persistence-common-care_level", level="DELETE")
     *
     * @param $id
     * @param CareLevelService $careLevelService
     * @return JsonResponse
     * @throws \Doctrine\DBAL\ConnectionException
     * @throws \Throwable
     */
    public function deleteAction(Request $request, $id, CareLevelService $careLevelService)
    {
        $careLevelService->remove($id);

        return $this->respondSuccess(
            Response::HTTP_NO_CONTENT
        );
    }

    /**
     * @Route("", name="api_admin_care_level_delete_bulk", methods={"DELETE"})
     *
     * @Grant(grant="persistence-common-care_level", level="DELETE")
     *
     * @param Request $request
     * @param CareLevelService $careLevelService
     * @return JsonResponse
     * @throws \Doctrine\DBAL\ConnectionException
     * @throws \Throwable
     */
    public function deleteBulkAction(Request $request, CareLevelService $careLevelService)
    {
        $careLevelService->removeBulk($request->get('ids'));

        return $this->respondSuccess(
            Response::HTTP_NO_CONTENT
        );
    }

    /**
     * @Route("/related/info", name="api_admin_care_level_related_info", methods={"POST"})
     *
     * @param Request $request
     * @param CareLevelService $careLevelService
     * @return JsonResponse
     * @throws \Doctrine\DBAL\ConnectionException
     * @throws \Throwable
     */
    public function relatedInfoAction(Request $request, CareLevelService $careLevelService)
    {
        $relatedData = $careLevelService->getRelatedInfo($request->get('ids'));

        return $this->respondSuccess(
            Response::HTTP_OK,
            '',
            [$relatedData]
        );
    }

    /**
     * @Route("/mobile/list", name="api_admin_care_level_mobile_list", methods={"GET"})
     *
     * @param Request $request
     * @param CareLevelService $careLevelService
     * @return JsonResponse
     */
    public function getMobileListAction(Request $request, CareLevelService $careLevelService)
    {
        return $this->respondSuccess(
            Response::HTTP_OK,
            '',
            $careLevelService->getMobileList($request->headers->get('date')),
            ['api_admin_care_level_mobile_list']
        );
    }
}
