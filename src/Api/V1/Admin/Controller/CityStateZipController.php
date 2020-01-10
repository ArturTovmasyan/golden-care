<?php

namespace App\Api\V1\Admin\Controller;

use App\Annotation\Grant;
use App\Api\V1\Admin\Service\CityStateZipService;
use App\Api\V1\Common\Controller\BaseController;
use App\Entity\CityStateZip;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\JsonResponse;
use Knp\Bundle\SnappyBundle\Snappy\Response\PdfResponse;

/**
 * @Route("/api/v1.0/admin/city/state/zip")
 *
 * @Grant(grant="persistence-common-city_state_zip", level="VIEW")
 *
 * Class CityStateZipController
 * @package App\Api\V1\Admin\Controller
 */
class CityStateZipController extends BaseController
{
    /**
     * @Route("/grid", name="api_admin_city_state_zip_grid", methods={"GET"})
     *
     * @param Request $request
     * @param CityStateZipService $cityStateZipService
     * @return JsonResponse
     */
    public function gridAction(Request $request, CityStateZipService $cityStateZipService): JsonResponse
    {
        return $this->respondGrid(
            $request,
            CityStateZip::class,
            'api_admin_city_state_zip_grid',
            $cityStateZipService
        );
    }

    /**
     * @Route("/grid", name="api_admin_city_state_zip_grid_options", methods={"OPTIONS"})
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function gridOptionAction(Request $request): JsonResponse
    {
        return $this->getOptionsByGroupName($request, CityStateZip::class, 'api_admin_city_state_zip_grid');
    }

    /**
     * @Route("", name="api_admin_city_state_zip_list", methods={"GET"})
     *
     * @param Request $request
     * @param CityStateZipService $cityStateZipService
     * @return PdfResponse|JsonResponse|Response
     */
    public function listAction(Request $request, CityStateZipService $cityStateZipService)
    {
        return $this->respondList(
            $request,
            CityStateZip::class,
            'api_admin_city_state_zip_list',
            $cityStateZipService
        );
    }

    /**
     * @Route("/{id}", requirements={"id"="\d+"}, name="api_admin_city_state_zip_get", methods={"GET"})
     *
     * @param Request $request
     * @param $id
     * @param CityStateZipService $cityStateZipService
     * @return JsonResponse
     */
    public function getAction(Request $request, $id, CityStateZipService $cityStateZipService): JsonResponse
    {
        return $this->respondSuccess(
            Response::HTTP_OK,
            '',
            $cityStateZipService->getById($id),
            ['api_admin_city_state_zip_get']
        );
    }

    /**
     * @Route("", name="api_admin_city_state_zip_add", methods={"POST"})
     *
     * @Grant(grant="persistence-common-city_state_zip", level="ADD")
     *
     * @param Request $request
     * @param CityStateZipService $cityStateZipService
     * @return JsonResponse
     */
    public function addAction(Request $request, CityStateZipService $cityStateZipService): JsonResponse
    {
        $id = $cityStateZipService->add(
            [
                'state_full' => $request->get('state_full'),
                'state_abbr' => $request->get('state_abbr'),
                'zip_main' => $request->get('zip_main'),
                'zip_sub' => $request->get('zip_sub') ?? '',
                'city' => $request->get('city'),
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
     * @Route("/{id}", requirements={"id"="\d+"}, name="api_admin_city_state_zip_edit", methods={"PUT"})
     *
     * @Grant(grant="persistence-common-city_state_zip", level="EDIT")
     *
     * @param Request $request
     * @param $id
     * @param CityStateZipService $cityStateZipService
     * @return JsonResponse
     */
    public function editAction(Request $request, $id, CityStateZipService $cityStateZipService): JsonResponse
    {
        $cityStateZipService->edit(
            $id,
            [
                'state_full' => $request->get('state_full'),
                'state_abbr' => $request->get('state_abbr'),
                'zip_main' => $request->get('zip_main'),
                'zip_sub' => $request->get('zip_sub') ?? '',
                'city' => $request->get('city'),
                'space_id' => $request->get('space_id')
            ]
        );

        return $this->respondSuccess(
            Response::HTTP_CREATED
        );
    }

    /**
     * @Route("/{id}", requirements={"id"="\d+"}, name="api_admin_city_state_zip_delete", methods={"DELETE"})
     *
     * @Grant(grant="persistence-common-city_state_zip", level="DELETE")
     *
     * @param Request $request
     * @param $id
     * @param CityStateZipService $cityStateZipService
     * @return JsonResponse
     */
    public function deleteAction(Request $request, $id, CityStateZipService $cityStateZipService): JsonResponse
    {
        $cityStateZipService->remove($id);

        return $this->respondSuccess(
            Response::HTTP_NO_CONTENT
        );
    }

    /**
     * @Route("", name="api_admin_city_state_zip_delete_bulk", methods={"DELETE"})
     *
     * @Grant(grant="persistence-common-city_state_zip", level="DELETE")
     *
     * @param Request $request
     * @param CityStateZipService $cityStateZipService
     * @return JsonResponse
     */
    public function deleteBulkAction(Request $request, CityStateZipService $cityStateZipService): JsonResponse
    {
        $cityStateZipService->removeBulk($request->get('ids'));

        return $this->respondSuccess(
            Response::HTTP_NO_CONTENT
        );
    }

    /**
     * @Route("/related/info", name="api_admin_city_state_zip_related_info", methods={"POST"})
     *
     * @param Request $request
     * @param CityStateZipService $cityStateZipService
     * @return JsonResponse
     */
    public function relatedInfoAction(Request $request, CityStateZipService $cityStateZipService): JsonResponse
    {
        $relatedData = $cityStateZipService->getRelatedInfo($request->get('ids'));

        return $this->respondSuccess(
            Response::HTTP_OK,
            '',
            [$relatedData]
        );
    }
}
