<?php

namespace App\Api\V1\Admin\Controller;

use App\Annotation\Grant;
use App\Api\V1\Admin\Service\ApartmentService;
use App\Api\V1\Common\Controller\BaseController;
use App\Entity\Apartment;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\JsonResponse;
use Knp\Bundle\SnappyBundle\Snappy\Response\PdfResponse;

/**
 * @Route("/api/v1.0/admin/apartment")
 *
 * @Grant(grant="persistence-apartment", level="VIEW")
 *
 * Class ApartmentController
 * @package App\Api\V1\Admin\Controller
 */
class ApartmentController extends BaseController
{
    /**
     * @Route("/grid", name="api_admin_apartment_grid", methods={"GET"})
     *
     * @param Request $request
     * @param ApartmentService $apartmentService
     * @return JsonResponse
     */
    public function gridAction(Request $request, ApartmentService $apartmentService): JsonResponse
    {
        return $this->respondGrid(
            $request,
            Apartment::class,
            'api_admin_apartment_grid',
            $apartmentService
        );
    }

    /**
     * @Route("/grid", name="api_admin_apartment_grid_options", methods={"OPTIONS"})
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function gridOptionAction(Request $request): JsonResponse
    {
        return $this->getOptionsByGroupName($request, Apartment::class, 'api_admin_apartment_grid');
    }

    /**
     * @Route("", name="api_admin_apartment_list", methods={"GET"})
     *
     * @param Request $request
     * @param ApartmentService $apartmentService
     * @return PdfResponse|JsonResponse|Response
     */
    public function listAction(Request $request, ApartmentService $apartmentService)
    {
        return $this->respondList(
            $request,
            Apartment::class,
            'api_admin_apartment_list',
            $apartmentService
        );
    }

    /**
     * @Route("/{id}", requirements={"id"="\d+"}, name="api_admin_apartment_get", methods={"GET"})
     *
     * @param Request $request
     * @param $id
     * @param ApartmentService $apartmentService
     * @return JsonResponse
     */
    public function getAction(Request $request, $id, ApartmentService $apartmentService): JsonResponse
    {
        return $this->respondSuccess(
            Response::HTTP_OK,
            '',
            $apartmentService->getById($id),
            ['api_admin_apartment_get']
        );
    }

    /**
     * @Route("", name="api_admin_apartment_add", methods={"POST"})
     *
     * @Grant(grant="persistence-apartment", level="ADD")
     *
     * @param Request $request
     * @param ApartmentService $apartmentService
     * @return JsonResponse
     */
    public function addAction(Request $request, ApartmentService $apartmentService): JsonResponse
    {
        $id = $apartmentService->add(
            [
                'name' => $request->get('name'),
                'description' => $request->get('description') ?? '',
                'shorthand' => $request->get('shorthand'),
                'phone' => $request->get('phone') ?? '',
                'fax' => $request->get('fax') ?? '',
                'address' => $request->get('address'),
                'license' => $request->get('license') ?? '',
                'csz_id' => $request->get('csz_id'),
                'beds_licensed' => $request->get('beds_licensed'),
                'beds_target' => $request->get('beds_target'),
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
     * @Route("/{id}", requirements={"id"="\d+"}, name="api_admin_apartment_edit", methods={"PUT"})
     *
     * @Grant(grant="persistence-apartment", level="EDIT")
     *
     * @param Request $request
     * @param $id
     * @param ApartmentService $apartmentService
     * @return JsonResponse
     */
    public function editAction(Request $request, $id, ApartmentService $apartmentService): JsonResponse
    {
        $apartmentService->edit(
            $id,
            [
                'name' => $request->get('name'),
                'description' => $request->get('description') ?? '',
                'shorthand' => $request->get('shorthand'),
                'phone' => $request->get('phone') ?? '',
                'fax' => $request->get('fax') ?? '',
                'address' => $request->get('address'),
                'license' => $request->get('license') ?? '',
                'csz_id' => $request->get('csz_id'),
                'beds_licensed' => $request->get('beds_licensed'),
                'beds_target' => $request->get('beds_target'),
                'space_id' => $request->get('space_id')
            ]
        );

        return $this->respondSuccess(
            Response::HTTP_CREATED
        );
    }

    /**
     * @Route("/{id}", requirements={"id"="\d+"}, name="api_admin_apartment_delete", methods={"DELETE"})
     *
     * @Grant(grant="persistence-apartment", level="DELETE")
     *
     * @param Request $request
     * @param $id
     * @param ApartmentService $apartmentService
     * @return JsonResponse
     */
    public function deleteAction(Request $request, $id, ApartmentService $apartmentService): JsonResponse
    {
        $apartmentService->remove($id);

        return $this->respondSuccess(
            Response::HTTP_NO_CONTENT
        );
    }

    /**
     * @Route("/{id}", requirements={"id"="\d+"}, name="api_admin_apartment_delete", methods={"DELETE"})
     *
     * @Grant(grant="persistence-apartment", level="DELETE")
     *
     * @param Request $request
     * @param ApartmentService $apartmentService
     * @return JsonResponse
     */
    public function deleteBulkAction(Request $request, ApartmentService $apartmentService): JsonResponse
    {
        $apartmentService->removeBulk($request->get('ids'));

        return $this->respondSuccess(
            Response::HTTP_NO_CONTENT
        );
    }

    /**
     * @Route("/related/info", name="api_admin_apartment_related_info", methods={"POST"})
     *
     * @param Request $request
     * @param ApartmentService $apartmentService
     * @return JsonResponse
     */
    public function relatedInfoAction(Request $request, ApartmentService $apartmentService): JsonResponse
    {
        $relatedData = $apartmentService->getRelatedInfo($request->get('ids'));

        return $this->respondSuccess(
            Response::HTTP_OK,
            '',
            [$relatedData]
        );
    }

    /**
     * @Route("/mobile/list", name="api_admin_apartment_mobile_list", methods={"GET"})
     *
     * @param Request $request
     * @param ApartmentService $apartmentService
     * @return JsonResponse
     */
    public function getMobileListAction(Request $request, ApartmentService $apartmentService): JsonResponse
    {
        return $this->respondSuccess(
            Response::HTTP_OK,
            '',
            $apartmentService->getMobileList($request->headers->get('date')),
            ['api_admin_apartment_mobile_list']
        );
    }
}
