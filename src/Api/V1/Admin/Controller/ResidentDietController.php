<?php
namespace App\Api\V1\Admin\Controller;

use App\Api\V1\Admin\Service\ResidentDietService;
use App\Api\V1\Common\Controller\BaseController;
use App\Entity\ResidentDiet;
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
 * @Route("/api/v1.0/admin/resident/diet")
 *
 * @Grant(grant="persistence-resident-resident_diet", level="VIEW")
 *
 * Class ResidentDietController
 * @package App\Api\V1\Admin\Controller
 */
class ResidentDietController extends BaseController
{
    /**
     * @Route("/grid", name="api_admin_resident_diet_grid", methods={"GET"})
     *
     * @param Request $request
     * @param ResidentDietService $residentDietService
     * @return JsonResponse|PdfResponse
     * @throws \ReflectionException
     */
    public function gridAction(Request $request, ResidentDietService $residentDietService)
    {
        return $this->respondGrid(
            $request,
            ResidentDiet::class,
            'api_admin_resident_diet_grid',
            $residentDietService,
            ['resident_id' => $request->get('resident_id')]
        );
    }

    /**
     * @Route("/grid", name="api_admin_resident_diet_grid_options", methods={"OPTIONS"})
     *
     * @param Request $request
     * @return JsonResponse
     * @throws \ReflectionException
     */
    public function gridOptionAction(Request $request)
    {
        return $this->getOptionsByGroupName($request, ResidentDiet::class, 'api_admin_resident_diet_grid');
    }

    /**
     * @Route("", name="api_admin_resident_diet_list", methods={"GET"})
     *
     * @param Request $request
     * @param ResidentDietService $residentDietService
     * @return JsonResponse|PdfResponse
     * @throws \ReflectionException
     */
    public function listAction(Request $request, ResidentDietService $residentDietService)
    {
        return $this->respondList(
            $request,
            ResidentDiet::class,
            'api_admin_resident_diet_list',
            $residentDietService,
            ['resident_id' => $request->get('resident_id')]
        );
    }

    /**
     * @Route("/{id}", requirements={"id"="\d+"}, name="api_admin_resident_diet_get", methods={"GET"})
     *
     * @param ResidentDietService $residentDietService
     * @param $id
     * @return JsonResponse
     */
    public function getAction(Request $request, $id, ResidentDietService $residentDietService)
    {
        return $this->respondSuccess(
            Response::HTTP_OK,
            '',
            $residentDietService->getById($id),
            ['api_admin_resident_diet_get']
        );
    }

    /**
     * @Route("", name="api_admin_resident_diet_add", methods={"POST"})
     *
     * @Grant(grant="persistence-resident-resident_diet", level="ADD")
     *
     * @param Request $request
     * @param ResidentDietService $residentDietService
     * @return JsonResponse
     * @throws \Throwable
     */
    public function addAction(Request $request, ResidentDietService $residentDietService)
    {
        $id = $residentDietService->add(
            [
                'resident_id' => $request->get('resident_id'),
                'diet_id' => $request->get('diet_id'),
                'description' => $request->get('description')
            ]
        );

        return $this->respondSuccess(
            Response::HTTP_CREATED,
            '',
            [$id]
        );
    }

    /**
     * @Route("/{id}", requirements={"id"="\d+"}, name="api_admin_resident_diet_edit", methods={"PUT"})
     *
     * @Grant(grant="persistence-resident-resident_diet", level="EDIT")
     *
     * @param Request $request
     * @param $id
     * @param ResidentDietService $residentDietService
     * @return JsonResponse
     * @throws \Throwable
     */
    public function editAction(Request $request, $id, ResidentDietService $residentDietService)
    {
        $residentDietService->edit(
            $id,
            [
                'resident_id' => $request->get('resident_id'),
                'diet_id' => $request->get('diet_id'),
                'description' => $request->get('description')
            ]
        );

        return $this->respondSuccess(
            Response::HTTP_CREATED
        );
    }

    /**
     * @Route("/{id}", requirements={"id"="\d+"}, name="api_admin_resident_diet_delete", methods={"DELETE"})
     *
     * @Grant(grant="persistence-resident-resident_diet", level="DELETE")
     *
     * @param $id
     * @param ResidentDietService $residentDietService
     * @return JsonResponse
     * @throws \Doctrine\DBAL\ConnectionException
     * @throws \Throwable
     */
    public function deleteAction(Request $request, $id, ResidentDietService $residentDietService)
    {
        $residentDietService->remove($id);

        return $this->respondSuccess(
            Response::HTTP_NO_CONTENT
        );
    }

    /**
     * @Route("", name="api_admin_resident_diet_delete_bulk", methods={"DELETE"})
     *
     * @Grant(grant="persistence-resident-resident_diet", level="DELETE")
     *
     * @param Request $request
     * @param ResidentDietService $residentDietService
     * @return JsonResponse
     * @throws \Doctrine\DBAL\ConnectionException
     * @throws \Throwable
     */
    public function deleteBulkAction(Request $request, ResidentDietService $residentDietService)
    {
        $residentDietService->removeBulk($request->get('ids'));

        return $this->respondSuccess(
            Response::HTTP_NO_CONTENT
        );
    }

    /**
     * @Route("/related/info", name="api_admin_resident_diet_related_info", methods={"POST"})
     *
     * @param Request $request
     * @param ResidentDietService $residentDietService
     * @return JsonResponse
     * @throws \Doctrine\DBAL\ConnectionException
     * @throws \Throwable
     */
    public function relatedInfoAction(Request $request, ResidentDietService $residentDietService)
    {
        $relatedData = $residentDietService->getRelatedInfo($request->get('ids'));

        return $this->respondSuccess(
            Response::HTTP_OK,
            '',
            [$relatedData]
        );
    }
}
