<?php
namespace App\Api\V1\Admin\Controller;

use App\Api\V1\Admin\Service\ResidentRentIncreaseService;
use App\Api\V1\Common\Controller\BaseController;
use App\Entity\ResidentRentIncrease;
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
 * @Route("/api/v1.0/admin/resident-rent-increase")
 *
 * @Grant(grant="persistence-resident-resident_rent_increase", level="VIEW")
 *
 * Class ResidentRentIncreaseController
 * @package App\Api\V1\Admin\Controller
 */
class ResidentRentIncreaseController extends BaseController
{
    /**
     * @Route("/grid", name="api_admin_resident_rent_increase_grid", methods={"GET"})
     *
     * @param Request $request
     * @param ResidentRentIncreaseService $residentRentService
     * @return JsonResponse|PdfResponse
     * @throws \ReflectionException
     */
    public function gridAction(Request $request, ResidentRentIncreaseService $residentRentService)
    {
        return $this->respondGrid(
            $request,
            ResidentRentIncrease::class,
            'api_admin_resident_rent_increase_grid',
            $residentRentService,
            ['resident_id' => $request->get('resident_id')]
        );
    }

    /**
     * @Route("/grid", name="api_admin_resident_rent_increase_grid_options", methods={"OPTIONS"})
     *
     * @param Request $request
     * @return JsonResponse
     * @throws \ReflectionException
     */
    public function gridOptionAction(Request $request)
    {
        return $this->getOptionsByGroupName($request, ResidentRentIncrease::class, 'api_admin_resident_rent_increase_grid');
    }

    /**
     * @Route("", name="api_admin_resident_rent_increase_list", methods={"GET"})
     *
     * @param Request $request
     * @param ResidentRentIncreaseService $residentRentService
     * @return JsonResponse|PdfResponse
     * @throws \ReflectionException
     */
    public function listAction(Request $request, ResidentRentIncreaseService $residentRentService)
    {
        return $this->respondList(
            $request,
            ResidentRentIncrease::class,
            'api_admin_resident_rent_increase_list',
            $residentRentService,
            ['resident_id' => $request->get('resident_id')]
        );
    }

    /**
     * @Route("/{id}", requirements={"id"="\d+"}, name="api_admin_resident_rent_increase_get", methods={"GET"})
     *
     * @param ResidentRentIncreaseService $residentRentService
     * @param $id
     * @return JsonResponse
     */
    public function getAction(Request $request, $id, ResidentRentIncreaseService $residentRentService)
    {
        return $this->respondSuccess(
            Response::HTTP_OK,
            '',
            $residentRentService->getById($id),
            ['api_admin_resident_rent_increase_get']
        );
    }

    /**
     * @Route("", name="api_admin_resident_rent_increase_add", methods={"POST"})
     *
     * @Grant(grant="persistence-resident-resident_rent_increase", level="ADD")
     *
     * @param Request $request
     * @param ResidentRentIncreaseService $residentRentService
     * @return JsonResponse
     * @throws \Throwable
     */
    public function addAction(Request $request, ResidentRentIncreaseService $residentRentService)
    {
        $id = $residentRentService->add(
            [
                'resident_id' => $request->get('resident_id'),
                'reason' => $request->get('reason'),
                'amount' => $request->get('amount'),
                'effective_date' => $request->get('effective_date'),
                'notification_date' => $request->get('notification_date'),
            ]
        );

        return $this->respondSuccess(
            Response::HTTP_CREATED,
            '',
            [$id]
        );
    }

    /**
     * @Route("/{id}", requirements={"id"="\d+"}, name="api_admin_resident_rent_increase_edit", methods={"PUT"})
     *
     * @Grant(grant="persistence-resident-resident_rent_increase", level="EDIT")
     *
     * @param Request $request
     * @param $id
     * @param ResidentRentIncreaseService $residentRentService
     * @return JsonResponse
     * @throws \Throwable
     */
    public function editAction(Request $request, $id, ResidentRentIncreaseService $residentRentService)
    {
        $residentRentService->edit(
            $id,
            [
                'resident_id' => $request->get('resident_id'),
                'reason' => $request->get('reason'),
                'amount' => $request->get('amount'),
                'effective_date' => $request->get('effective_date'),
                'notification_date' => $request->get('notification_date'),
            ]
        );

        return $this->respondSuccess(
            Response::HTTP_CREATED
        );
    }

    /**
     * @Route("/{id}", requirements={"id"="\d+"}, name="api_admin_resident_rent_increase_delete", methods={"DELETE"})
     *
     * @Grant(grant="persistence-resident-resident_rent_increase", level="DELETE")
     *
     * @param $id
     * @param ResidentRentIncreaseService $residentRentService
     * @return JsonResponse
     * @throws \Doctrine\DBAL\ConnectionException
     * @throws \Throwable
     */
    public function deleteAction(Request $request, $id, ResidentRentIncreaseService $residentRentService)
    {
        $residentRentService->remove($id);

        return $this->respondSuccess(
            Response::HTTP_NO_CONTENT
        );
    }

    /**
     * @Route("", name="api_admin_resident_rent_increase_delete_bulk", methods={"DELETE"})
     *
     * @Grant(grant="persistence-resident-resident_rent_increase", level="DELETE")
     *
     * @param Request $request
     * @param ResidentRentIncreaseService $residentRentService
     * @return JsonResponse
     * @throws \Doctrine\DBAL\ConnectionException
     * @throws \Throwable
     */
    public function deleteBulkAction(Request $request, ResidentRentIncreaseService $residentRentService)
    {
        $residentRentService->removeBulk($request->get('ids'));

        return $this->respondSuccess(
            Response::HTTP_NO_CONTENT
        );
    }

    /**
     * @Route("/related/info", name="api_admin_resident_rent_increase_related_info", methods={"POST"})
     *
     * @param Request $request
     * @param ResidentRentIncreaseService $residentRentService
     * @return JsonResponse
     * @throws \Doctrine\DBAL\ConnectionException
     * @throws \Throwable
     */
    public function relatedInfoAction(Request $request, ResidentRentIncreaseService $residentRentService)
    {
        $relatedData = $residentRentService->getRelatedInfo($request->get('ids'));

        return $this->respondSuccess(
            Response::HTTP_OK,
            '',
            [$relatedData]
        );
    }
}
