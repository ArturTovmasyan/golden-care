<?php

namespace App\Api\V1\Admin\Controller;

use App\Annotation\Grant;
use App\Api\V1\Admin\Service\DiningRoomService;
use App\Api\V1\Common\Controller\BaseController;
use App\Entity\DiningRoom;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\JsonResponse;
use Knp\Bundle\SnappyBundle\Snappy\Response\PdfResponse;

/**
 * @Route("/api/v1.0/admin/facility/dining/room")
 *
 * @Grant(grant="persistence-dining_room", level="VIEW")
 *
 * Class DiningRoomController
 * @package App\Api\V1\Admin\Controller
 */
class DiningRoomController extends BaseController
{
    protected function gridIgnoreFields(Request $request): array
    {
        $ignoreFields = [];

        $facilityId = (int)$request->get('facility_id');

        if (!empty($facilityId)) {
            $ignoreFields[] = 'facility';
        }

        return $ignoreFields;
    }

    /**
     * @Route("/grid", name="api_admin_dining_room_grid", methods={"GET"})
     *
     * @param Request $request
     * @param DiningRoomService $diningRoomService
     * @return JsonResponse
     */
    public function gridAction(Request $request, DiningRoomService $diningRoomService): JsonResponse
    {
        return $this->respondGrid(
            $request,
            DiningRoom::class,
            'api_admin_dining_room_grid',
            $diningRoomService,
            ['facility_id' => $request->get('facility_id')]
        );
    }

    /**
     * @Route("/grid", name="api_admin_dining_room_grid_options", methods={"OPTIONS"})
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function gridOptionAction(Request $request): JsonResponse
    {
        return $this->getOptionsByGroupName($request, DiningRoom::class, 'api_admin_dining_room_grid');
    }

    /**
     * @Route("", name="api_admin_dining_room_list", methods={"GET"})
     *
     * @param Request $request
     * @param DiningRoomService $diningRoomService
     * @return PdfResponse|JsonResponse|Response
     */
    public function listAction(Request $request, DiningRoomService $diningRoomService)
    {
        return $this->respondList(
            $request,
            DiningRoom::class,
            'api_admin_dining_room_list',
            $diningRoomService,
            ['facility_id' => $request->get('facility_id')]
        );
    }

    /**
     * @Route("/{id}", requirements={"id"="\d+"}, name="api_admin_dining_room_get", methods={"GET"})
     *
     * @param Request $request
     * @param $id
     * @param DiningRoomService $diningRoomService
     * @return JsonResponse
     */
    public function getAction(Request $request, $id, DiningRoomService $diningRoomService): JsonResponse
    {
        return $this->respondSuccess(
            Response::HTTP_OK,
            '',
            $diningRoomService->getById($id),
            ['api_admin_dining_room_get']
        );
    }

    /**
     * @Route("", name="api_admin_dining_room_add", methods={"POST"})
     *
     * @Grant(grant="persistence-dining_room", level="ADD")
     *
     * @param Request $request
     * @param DiningRoomService $diningRoomService
     * @return JsonResponse
     */
    public function addAction(Request $request, DiningRoomService $diningRoomService): JsonResponse
    {
        $id = $diningRoomService->add(
            [
                'title' => $request->get('title'),
                'facility_id' => $request->get('facility_id')
            ]
        );

        return $this->respondSuccess(
            Response::HTTP_CREATED,
            '',
            [$id]
        );
    }

    /**
     * @Route("/{id}", requirements={"id"="\d+"}, name="api_admin_dining_room_edit", methods={"PUT"})
     *
     * @Grant(grant="persistence-dining_room", level="EDIT")
     *
     * @param Request $request
     * @param $id
     * @param DiningRoomService $diningRoomService
     * @return JsonResponse
     */
    public function editAction(Request $request, $id, DiningRoomService $diningRoomService): JsonResponse
    {
        $diningRoomService->edit(
            $id,
            [
                'title' => $request->get('title'),
                'facility_id' => $request->get('facility_id')
            ]
        );

        return $this->respondSuccess(
            Response::HTTP_CREATED
        );
    }

    /**
     * @Route("/{id}", requirements={"id"="\d+"}, name="api_admin_dining_room_delete", methods={"DELETE"})
     *
     * @Grant(grant="persistence-dining_room", level="DELETE")
     *
     * @param Request $request
     * @param $id
     * @param DiningRoomService $diningRoomService
     * @return JsonResponse
     */
    public function deleteAction(Request $request, $id, DiningRoomService $diningRoomService): JsonResponse
    {
        $diningRoomService->remove($id);

        return $this->respondSuccess(
            Response::HTTP_NO_CONTENT
        );
    }

    /**
     * @Route("", name="api_admin_dining_room_delete_bulk", methods={"DELETE"})
     *
     * @Grant(grant="persistence-dining_room", level="DELETE")
     *
     * @param Request $request
     * @param DiningRoomService $diningRoomService
     * @return JsonResponse
     */
    public function deleteBulkAction(Request $request, DiningRoomService $diningRoomService): JsonResponse
    {
        $diningRoomService->removeBulk($request->get('ids'));

        return $this->respondSuccess(
            Response::HTTP_NO_CONTENT
        );
    }

    /**
     * @Route("/related/info", name="api_admin_dining_room_related_info", methods={"POST"})
     *
     * @param Request $request
     * @param DiningRoomService $diningRoomService
     * @return JsonResponse
     */
    public function relatedInfoAction(Request $request, DiningRoomService $diningRoomService): JsonResponse
    {
        $relatedData = $diningRoomService->getRelatedInfo($request->get('ids'));

        return $this->respondSuccess(
            Response::HTTP_OK,
            '',
            [$relatedData]
        );
    }
}
