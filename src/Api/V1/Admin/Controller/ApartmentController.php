<?php
namespace App\Api\V1\Admin\Controller;

use App\Api\V1\Admin\Service\ApartmentService;
use App\Api\V1\Common\Controller\BaseController;
use App\Entity\Apartment;
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
     * @return JsonResponse|PdfResponse
     * @throws \ReflectionException
     */
    public function gridAction(Request $request, ApartmentService $apartmentService)
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
     * @throws \ReflectionException
     */
    public function gridOptionAction(Request $request)
    {
        return $this->getOptionsByGroupName($request, Apartment::class, 'api_admin_apartment_grid');
    }

    /**
     * @Route("", name="api_admin_apartment_list", methods={"GET"})
     *
     * @param Request $request
     * @param ApartmentService $apartmentService
     * @return JsonResponse|PdfResponse
     * @throws \ReflectionException
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
     * @param ApartmentService $apartmentService
     * @param $id
     * @return JsonResponse
     */
    public function getAction(Request $request, $id, ApartmentService $apartmentService)
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
     * @throws \Throwable
     */
    public function addAction(Request $request, ApartmentService $apartmentService)
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
                'license_capacity' => $request->get('license_capacity'),
                'capacity' => $request->get('capacity'),
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
     * @throws \Throwable
     */
    public function editAction(Request $request, $id, ApartmentService $apartmentService)
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
                'license_capacity' => $request->get('license_capacity'),
                'capacity' => $request->get('capacity'),
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
     * @param $id
     * @param ApartmentService $apartmentService
     * @return JsonResponse
     * @throws \Doctrine\DBAL\ConnectionException
     * @throws \Throwable
     */
    public function deleteAction(Request $request, $id, ApartmentService $apartmentService)
    {
        $apartmentService->remove($id);

        return $this->respondSuccess(
            Response::HTTP_NO_CONTENT
        );
    }

    /**
     * @Route("", name="api_admin_apartment_delete_bulk", methods={"DELETE"})
     *
     * @Grant(grant="persistence-apartment", level="DELETE")
     *
     * @param Request $request
     * @param ApartmentService $apartmentService
     * @return JsonResponse
     * @throws \Doctrine\DBAL\ConnectionException
     * @throws \Throwable
     */
    public function deleteBulkAction(Request $request, ApartmentService $apartmentService)
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
     * @throws \Doctrine\DBAL\ConnectionException
     * @throws \Throwable
     */
    public function relatedInfoAction(Request $request, ApartmentService $apartmentService)
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
    public function getMobileListAction(Request $request, ApartmentService $apartmentService)
    {
        return $this->respondSuccess(
            Response::HTTP_OK,
            '',
            $apartmentService->getMobileList($request->headers->get('date')),
            ['api_admin_apartment_mobile_list']
        );
    }
}
