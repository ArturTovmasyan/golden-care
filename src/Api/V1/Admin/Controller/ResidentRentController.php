<?php
namespace App\Api\V1\Admin\Controller;

use App\Api\V1\Admin\Service\ResidentRentService;
use App\Api\V1\Common\Controller\BaseController;
use App\Entity\ResidentRent;
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
 * @Route("/api/v1.0/admin/resident/rent")
 *
 * @Grant(grant="persistence-resident-resident_rent", level="VIEW")
 *
 * Class ResidentRentController
 * @package App\Api\V1\Admin\Controller
 */
class ResidentRentController extends BaseController
{
    /**
     * @Route("/grid", name="api_admin_resident_rent_grid", methods={"GET"})
     *
     * @param Request $request
     * @param ResidentRentService $residentRentService
     * @return JsonResponse|PdfResponse
     * @throws \ReflectionException
     */
    public function gridAction(Request $request, ResidentRentService $residentRentService)
    {
        return $this->respondGrid(
            $request,
            ResidentRent::class,
            'api_admin_resident_rent_grid',
            $residentRentService,
            ['resident_id' => $request->get('resident_id')]
        );
    }

    /**
     * @Route("/grid", name="api_admin_resident_rent_grid_options", methods={"OPTIONS"})
     *
     * @param Request $request
     * @return JsonResponse
     * @throws \ReflectionException
     */
    public function gridOptionAction(Request $request)
    {
        return $this->getOptionsByGroupName($request, ResidentRent::class, 'api_admin_resident_rent_grid');
    }

    /**
     * @Route("", name="api_admin_resident_rent_list", methods={"GET"})
     *
     * @param Request $request
     * @param ResidentRentService $residentRentService
     * @return JsonResponse|PdfResponse
     * @throws \ReflectionException
     */
    public function listAction(Request $request, ResidentRentService $residentRentService)
    {
        return $this->respondList(
            $request,
            ResidentRent::class,
            'api_admin_resident_rent_list',
            $residentRentService,
            ['resident_id' => $request->get('resident_id')]
        );
    }

    /**
     * @Route("/{id}", requirements={"id"="\d+"}, name="api_admin_resident_rent_get", methods={"GET"})
     *
     * @param ResidentRentService $residentRentService
     * @param $id
     * @return JsonResponse
     */
    public function getAction(Request $request, $id, ResidentRentService $residentRentService)
    {
        return $this->respondSuccess(
            Response::HTTP_OK,
            '',
            $residentRentService->getById($id),
            ['api_admin_resident_rent_get']
        );
    }

    /**
     * @Route("", name="api_admin_resident_rent_add", methods={"POST"})
     *
     * @Grant(grant="persistence-resident-resident_rent", level="ADD")
     *
     * @param Request $request
     * @param ResidentRentService $residentRentService
     * @return JsonResponse
     * @throws \Throwable
     */
    public function addAction(Request $request, ResidentRentService $residentRentService)
    {
        $id = $residentRentService->add(
            [
                'resident_id' => $request->get('resident_id'),
                'start' => $request->get('start'),
                'end' => $request->get('end'),
                'period' => $request->get('period'),
                'amount' => $request->get('amount'),
                'notes' => $request->get('notes') ?? '',
                'source' => $request->get('source'),
            ]
        );

        return $this->respondSuccess(
            Response::HTTP_CREATED,
            '',
            [$id]
        );
    }

    /**
     * @Route("/{id}", requirements={"id"="\d+"}, name="api_admin_resident_rent_edit", methods={"PUT"})
     *
     * @Grant(grant="persistence-resident-resident_rent", level="EDIT")
     *
     * @param Request $request
     * @param $id
     * @param ResidentRentService $residentRentService
     * @return JsonResponse
     * @throws \Throwable
     */
    public function editAction(Request $request, $id, ResidentRentService $residentRentService)
    {
        $residentRentService->edit(
            $id,
            [
                'resident_id' => $request->get('resident_id'),
                'start' => $request->get('start'),
                'end' => $request->get('end'),
                'period' => $request->get('period'),
                'amount' => $request->get('amount'),
                'notes' => $request->get('notes') ?? '',
                'source' => $request->get('source'),
            ]
        );

        return $this->respondSuccess(
            Response::HTTP_CREATED
        );
    }

    /**
     * @Route("/{id}", requirements={"id"="\d+"}, name="api_admin_resident_rent_delete", methods={"DELETE"})
     *
     * @Grant(grant="persistence-resident-resident_rent", level="DELETE")
     *
     * @param $id
     * @param ResidentRentService $residentRentService
     * @return JsonResponse
     * @throws \Doctrine\DBAL\ConnectionException
     * @throws \Throwable
     */
    public function deleteAction(Request $request, $id, ResidentRentService $residentRentService)
    {
        $residentRentService->remove($id);

        return $this->respondSuccess(
            Response::HTTP_NO_CONTENT
        );
    }

    /**
     * @Route("", name="api_admin_resident_rent_delete_bulk", methods={"DELETE"})
     *
     * @Grant(grant="persistence-resident-resident_rent", level="DELETE")
     *
     * @param Request $request
     * @param ResidentRentService $residentRentService
     * @return JsonResponse
     * @throws \Doctrine\DBAL\ConnectionException
     * @throws \Throwable
     */
    public function deleteBulkAction(Request $request, ResidentRentService $residentRentService)
    {
        $residentRentService->removeBulk($request->get('ids'));

        return $this->respondSuccess(
            Response::HTTP_NO_CONTENT
        );
    }

    /**
     * @Route("/related/info", name="api_admin_resident_rent_related_info", methods={"POST"})
     *
     * @param Request $request
     * @param ResidentRentService $residentRentService
     * @return JsonResponse
     * @throws \Doctrine\DBAL\ConnectionException
     * @throws \Throwable
     */
    public function relatedInfoAction(Request $request, ResidentRentService $residentRentService)
    {
        $relatedData = $residentRentService->getRelatedInfo($request->get('ids'));

        return $this->respondSuccess(
            Response::HTTP_OK,
            '',
            [$relatedData]
        );
    }
}
