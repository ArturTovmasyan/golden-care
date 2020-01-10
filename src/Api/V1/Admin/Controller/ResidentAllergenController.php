<?php

namespace App\Api\V1\Admin\Controller;

use App\Annotation\Grant;
use App\Api\V1\Admin\Service\ResidentAllergenService;
use App\Api\V1\Common\Controller\BaseController;
use App\Entity\ResidentAllergen;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\JsonResponse;
use Knp\Bundle\SnappyBundle\Snappy\Response\PdfResponse;

/**
 * @Route("/api/v1.0/admin/resident/history/allergy/other")
 *
 * @Grant(grant="persistence-resident-resident_allergen", level="VIEW")
 *
 * Class ResidentAllergenController
 * @package App\Api\V1\Admin\Controller
 */
class ResidentAllergenController extends BaseController
{
    /**
     * @Route("/grid", name="api_admin_resident_allergen_grid", methods={"GET"})
     *
     * @param Request $request
     * @param ResidentAllergenService $residentAllergenService
     * @return JsonResponse
     */
    public function gridAction(Request $request, ResidentAllergenService $residentAllergenService): JsonResponse
    {
        return $this->respondGrid(
            $request,
            ResidentAllergen::class,
            'api_admin_resident_allergen_grid',
            $residentAllergenService,
            ['resident_id' => $request->get('resident_id')]
        );
    }

    /**
     * @Route("/grid", name="api_admin_resident_allergen_grid_options", methods={"OPTIONS"})
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function gridOptionAction(Request $request): JsonResponse
    {
        return $this->getOptionsByGroupName($request, ResidentAllergen::class, 'api_admin_resident_allergen_grid');
    }

    /**
     * @Route("", name="api_admin_resident_allergen_list", methods={"GET"})
     *
     * @param Request $request
     * @param ResidentAllergenService $residentAllergenService
     * @return PdfResponse|JsonResponse|Response
     */
    public function listAction(Request $request, ResidentAllergenService $residentAllergenService)
    {
        return $this->respondList(
            $request,
            ResidentAllergen::class,
            'api_admin_resident_allergen_list',
            $residentAllergenService,
            ['resident_id' => $request->get('resident_id')]
        );
    }

    /**
     * @Route("/{id}", requirements={"id"="\d+"}, name="api_admin_resident_allergen_get", methods={"GET"})
     *
     * @param Request $request
     * @param $id
     * @param ResidentAllergenService $residentAllergenService
     * @return JsonResponse
     */
    public function getAction(Request $request, $id, ResidentAllergenService $residentAllergenService): JsonResponse
    {
        return $this->respondSuccess(
            Response::HTTP_OK,
            '',
            $residentAllergenService->getById($id),
            ['api_admin_resident_allergen_get']
        );
    }

    /**
     * @Route("", name="api_admin_resident_allergen_add", methods={"POST"})
     *
     * @Grant(grant="persistence-resident-resident_allergen", level="ADD")
     *
     * @param Request $request
     * @param ResidentAllergenService $residentAllergenService
     * @return JsonResponse
     */
    public function addAction(Request $request, ResidentAllergenService $residentAllergenService): JsonResponse
    {
        $id = $residentAllergenService->add(
            [
                'resident_id' => $request->get('resident_id'),
                'allergen_id' => $request->get('allergen_id'),
                'notes' => $request->get('notes') ?? ''
            ]
        );

        return $this->respondSuccess(
            Response::HTTP_CREATED,
            '',
            [$id]
        );
    }

    /**
     * @Route("/{id}", requirements={"id"="\d+"}, name="api_admin_resident_allergen_edit", methods={"PUT"})
     *
     * @Grant(grant="persistence-resident-resident_allergen", level="EDIT")
     *
     * @param Request $request
     * @param $id
     * @param ResidentAllergenService $residentAllergenService
     * @return JsonResponse
     */
    public function editAction(Request $request, $id, ResidentAllergenService $residentAllergenService): JsonResponse
    {
        $residentAllergenService->edit(
            $id,
            [
                'resident_id' => $request->get('resident_id'),
                'allergen_id' => $request->get('allergen_id'),
                'notes' => $request->get('notes') ?? ''
            ]
        );

        return $this->respondSuccess(
            Response::HTTP_CREATED
        );
    }

    /**
     * @Route("/{id}", requirements={"id"="\d+"}, name="api_admin_resident_allergen_delete", methods={"DELETE"})
     *
     * @Grant(grant="persistence-resident-resident_allergen", level="DELETE")
     *
     * @param Request $request
     * @param $id
     * @param ResidentAllergenService $residentAllergenService
     * @return JsonResponse
     */
    public function deleteAction(Request $request, $id, ResidentAllergenService $residentAllergenService): JsonResponse
    {
        $residentAllergenService->remove($id);

        return $this->respondSuccess(
            Response::HTTP_NO_CONTENT
        );
    }

    /**
     * @Route("", name="api_admin_resident_allergen_delete_bulk", methods={"DELETE"})
     *
     * @Grant(grant="persistence-resident-resident_allergen", level="DELETE")
     *
     * @param Request $request
     * @param ResidentAllergenService $residentAllergenService
     * @return JsonResponse
     */
    public function deleteBulkAction(Request $request, ResidentAllergenService $residentAllergenService): JsonResponse
    {
        $residentAllergenService->removeBulk($request->get('ids'));

        return $this->respondSuccess(
            Response::HTTP_NO_CONTENT
        );
    }

    /**
     * @Route("/related/info", name="api_admin_resident_allergen_related_info", methods={"POST"})
     *
     * @param Request $request
     * @param ResidentAllergenService $residentAllergenService
     * @return JsonResponse
     */
    public function relatedInfoAction(Request $request, ResidentAllergenService $residentAllergenService): JsonResponse
    {
        $relatedData = $residentAllergenService->getRelatedInfo($request->get('ids'));

        return $this->respondSuccess(
            Response::HTTP_OK,
            '',
            [$relatedData]
        );
    }
}
