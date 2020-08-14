<?php

namespace App\Api\V1\Admin\Controller;

use App\Annotation\Grant;
use App\Api\V1\Admin\Service\ApartmentRoomService;
use App\Api\V1\Common\Controller\BaseController;
use App\Entity\ApartmentRoom;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\JsonResponse;
use Knp\Bundle\SnappyBundle\Snappy\Response\PdfResponse;

/**
 * @Route("/api/v1.0/admin/apartment/room")
 *
 * @Grant(grant="persistence-apartment_room", level="VIEW")
 *
 * Class ApartmentRoomController
 * @package App\Api\V1\Admin\Controller
 */
class ApartmentRoomController extends BaseController
{
    /**
     * @Route("/grid", name="api_admin_apartment_room_grid", methods={"GET"})
     *
     * @param Request $request
     * @param ApartmentRoomService $apartmentRoomService
     * @return JsonResponse
     */
    public function gridAction(Request $request, ApartmentRoomService $apartmentRoomService)
    {
        return $this->respondGrid(
            $request,
            ApartmentRoom::class,
            'api_admin_apartment_room_grid',
            $apartmentRoomService
        );
    }

    /**
     * @Route("/grid", name="api_admin_apartment_room_grid_options", methods={"OPTIONS"})
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function gridOptionAction(Request $request): JsonResponse
    {
        return $this->getOptionsByGroupName($request, ApartmentRoom::class, 'api_admin_apartment_room_grid');
    }

    /**
     * @Route("", name="api_admin_apartment_room_list", methods={"GET"})
     *
     * @param Request $request
     * @param ApartmentRoomService $apartmentRoomService
     * @return PdfResponse|JsonResponse|Response
     */
    public function listAction(Request $request, ApartmentRoomService $apartmentRoomService)
    {
        return $this->respondList(
            $request,
            ApartmentRoom::class,
            'api_admin_apartment_room_list',
            $apartmentRoomService,
            [
                'apartment_id' => $request->get('apartment_id'),
                'vacant' => $request->get('vacant'),
                'date' => $request->get('date'),
                'resident_id' => $request->get('resident_id')
            ]
        );
    }

    /**
     * @Route("/{id}", requirements={"id"="\d+"}, name="api_admin_apartment_room_get", methods={"GET"})
     *
     * @param Request $request
     * @param $id
     * @param ApartmentRoomService $apartmentRoomService
     * @return JsonResponse
     */
    public function getAction(Request $request, $id, ApartmentRoomService $apartmentRoomService): JsonResponse
    {
        return $this->respondSuccess(
            Response::HTTP_OK,
            '',
            $apartmentRoomService->getById($id),
            ['api_admin_apartment_room_get']
        );
    }

    /**
     * @Route("", name="api_admin_apartment_room_add", methods={"POST"})
     *
     * @Grant(grant="persistence-apartment_room", level="ADD")
     *
     * @param Request $request
     * @param ApartmentRoomService $apartmentRoomService
     * @return JsonResponse
     */
    public function addAction(Request $request, ApartmentRoomService $apartmentRoomService): JsonResponse
    {
        $id = $apartmentRoomService->add(
            [
                'apartment_id' => $request->get('apartment_id'),
                'number' => $request->get('number'),
                'floor' => $request->get('floor'),
                'notes' => $request->get('notes') ?? '',
                'beds' => $request->get('beds')
            ]
        );

        return $this->respondSuccess(
            Response::HTTP_CREATED,
            '',
            [$id]
        );
    }

    /**
     * @Route("/{id}", requirements={"id"="\d+"}, name="api_admin_apartment_room_edit", methods={"PUT"})
     *
     * @Grant(grant="persistence-apartment_room", level="EDIT")
     *
     * @param Request $request
     * @param $id
     * @param ApartmentRoomService $apartmentRoomService
     * @return JsonResponse
     */
    public function editAction(Request $request, $id, ApartmentRoomService $apartmentRoomService): JsonResponse
    {
        $apartmentRoomService->edit(
            $id,
            [
                'apartment_id' => $request->get('apartment_id'),
                'number' => $request->get('number'),
                'floor' => $request->get('floor'),
                'notes' => $request->get('notes') ?? '',
                'beds' => $request->get('beds')
            ]
        );

        return $this->respondSuccess(
            Response::HTTP_CREATED
        );
    }

    /**
     * @Route("/{id}", requirements={"id"="\d+"}, name="api_admin_apartment_room_delete", methods={"DELETE"})
     *
     * @Grant(grant="persistence-apartment_room", level="DELETE")
     *
     * @param Request $request
     * @param $id
     * @param ApartmentRoomService $apartmentRoomService
     * @return JsonResponse
     */
    public function deleteAction(Request $request, $id, ApartmentRoomService $apartmentRoomService): JsonResponse
    {
        $apartmentRoomService->remove($id);

        return $this->respondSuccess(
            Response::HTTP_NO_CONTENT
        );
    }

    /**
     * @Route("", name="api_admin_apartment_room_delete_bulk", methods={"DELETE"})
     *
     * @Grant(grant="persistence-apartment_room", level="DELETE")
     *
     * @param Request $request
     * @param ApartmentRoomService $apartmentRoomService
     * @return JsonResponse
     */
    public function deleteBulkAction(Request $request, ApartmentRoomService $apartmentRoomService): JsonResponse
    {
        $apartmentRoomService->removeBulk($request->get('ids'));

        return $this->respondSuccess(
            Response::HTTP_NO_CONTENT
        );
    }

    /**
     * @Route("/{apartment_id}/last", requirements={"apartment_id"="\d+"}, name="api_admin_apartment_room_get_last", methods={"GET"})
     *
     * @Grant(grant="persistence-apartment_room", level="VIEW")
     *
     * @param Request $request
     * @param $apartment_id
     * @param ApartmentRoomService $apartmentRoomService
     * @return JsonResponse
     */
    public function getLastAction(Request $request, $apartment_id, ApartmentRoomService $apartmentRoomService): JsonResponse
    {
        return $this->respondSuccess(
            Response::HTTP_OK,
            '',
            [$apartmentRoomService->getLastNumber($apartment_id)],
            ['api_admin_apartment_room_get_last']
        );
    }

    /**
     * @Route("/related/info", name="api_admin_apartment_room_related_info", methods={"POST"})
     *
     * @param Request $request
     * @param ApartmentRoomService $apartmentRoomService
     * @return JsonResponse
     */
    public function relatedInfoAction(Request $request, ApartmentRoomService $apartmentRoomService): JsonResponse
    {
        $relatedData = $apartmentRoomService->getRelatedInfo($request->get('ids'));

        return $this->respondSuccess(
            Response::HTTP_OK,
            '',
            [$relatedData]
        );
    }
}
