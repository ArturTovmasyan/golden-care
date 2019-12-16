<?php
namespace App\Api\V1\Admin\Controller;

use App\Api\V1\Admin\Service\DietService;
use App\Api\V1\Common\Controller\BaseController;
use App\Entity\Diet;
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
 * @Route("/api/v1.0/admin/diet")
 *
 * @Grant(grant="persistence-common-diet", level="VIEW")
 *
 * Class DietController
 * @package App\Api\V1\Admin\Controller
 */
class DietController extends BaseController
{
    /**
     * @Route("/grid", name="api_admin_diet_grid", methods={"GET"})
     *
     * @param Request $request
     * @param DietService $dietService
     * @return JsonResponse|PdfResponse
     * @throws \ReflectionException
     */
    public function gridAction(Request $request, DietService $dietService)
    {
        return $this->respondGrid(
            $request,
            Diet::class,
            'api_admin_diet_grid',
            $dietService
        );
    }

    /**
     * @Route("/grid", name="api_admin_diet_grid_options", methods={"OPTIONS"})
     *
     * @param Request $request
     * @return JsonResponse
     * @throws \ReflectionException
     */
    public function gridOptionAction(Request $request)
    {
        return $this->getOptionsByGroupName($request, Diet::class, 'api_admin_diet_grid');
    }

    /**
     * @Route("", name="api_admin_diet_list", methods={"GET"})
     *
     * @param Request $request
     * @param DietService $dietService
     * @return JsonResponse|PdfResponse
     * @throws \ReflectionException
     */
    public function listAction(Request $request, DietService $dietService)
    {
        return $this->respondList(
            $request,
            Diet::class,
            'api_admin_diet_list',
            $dietService
        );
    }

    /**
     * @Route("/{id}", requirements={"id"="\d+"}, name="api_admin_diet_get", methods={"GET"})
     *
     * @param DietService $dietService
     * @param $id
     * @return JsonResponse
     */
    public function getAction(Request $request, $id, DietService $dietService)
    {
        return $this->respondSuccess(
            Response::HTTP_OK,
            '',
            $dietService->getById($id),
            ['api_admin_diet_get']
        );
    }

    /**
     * @Route("", name="api_admin_diet_add", methods={"POST"})
     *
     * @Grant(grant="persistence-common-diet", level="ADD")
     *
     * @param Request $request
     * @param DietService $dietService
     * @return JsonResponse
     * @throws \Throwable
     */
    public function addAction(Request $request, DietService $dietService)
    {
        $id = $dietService->add(
            [
                'title' => $request->get('title'),
                'color' => $request->get('color'),
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
     * @Route("/{id}", requirements={"id"="\d+"}, name="api_admin_diet_edit", methods={"PUT"})
     *
     * @Grant(grant="persistence-common-diet", level="EDIT")
     *
     * @param Request $request
     * @param $id
     * @param DietService $dietService
     * @return JsonResponse
     * @throws \Throwable
     */
    public function editAction(Request $request, $id, DietService $dietService)
    {
        $dietService->edit(
            $id,
            [
                'title' => $request->get('title'),
                'color' => $request->get('color'),
                'space_id' => $request->get('space_id')
            ]
        );

        return $this->respondSuccess(
            Response::HTTP_CREATED
        );
    }

    /**
     * @Route("/{id}", requirements={"id"="\d+"}, name="api_admin_diet_delete", methods={"DELETE"})
     *
     * @Grant(grant="persistence-common-diet", level="DELETE")
     *
     * @param $id
     * @param DietService $dietService
     * @return JsonResponse
     * @throws \Doctrine\DBAL\ConnectionException
     * @throws \Throwable
     */
    public function deleteAction(Request $request, $id, DietService $dietService)
    {
        $dietService->remove($id);

        return $this->respondSuccess(
            Response::HTTP_NO_CONTENT
        );
    }

    /**
     * @Route("", name="api_admin_diet_delete_bulk", methods={"DELETE"})
     *
     * @Grant(grant="persistence-common-diet", level="DELETE")
     *
     * @param Request $request
     * @param DietService $dietService
     * @return JsonResponse
     * @throws \Doctrine\DBAL\ConnectionException
     * @throws \Throwable
     */
    public function deleteBulkAction(Request $request, DietService $dietService)
    {
        $dietService->removeBulk($request->get('ids'));

        return $this->respondSuccess(
            Response::HTTP_NO_CONTENT
        );
    }

    /**
     * @Route("/related/info", name="api_admin_diet_related_info", methods={"POST"})
     *
     * @param Request $request
     * @param DietService $dietService
     * @return JsonResponse
     * @throws \Doctrine\DBAL\ConnectionException
     * @throws \Throwable
     */
    public function relatedInfoAction(Request $request, DietService $dietService)
    {
        $relatedData = $dietService->getRelatedInfo($request->get('ids'));

        return $this->respondSuccess(
            Response::HTTP_OK,
            '',
            [$relatedData]
        );
    }
}
