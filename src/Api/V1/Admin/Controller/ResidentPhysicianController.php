<?php

namespace App\Api\V1\Admin\Controller;

use App\Annotation\Grant;
use App\Api\V1\Admin\Service\ResidentPhysicianService;
use App\Api\V1\Common\Controller\BaseController;
use App\Entity\ResidentPhysician;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\JsonResponse;
use Knp\Bundle\SnappyBundle\Snappy\Response\PdfResponse;

/**
 * @Route("/api/v1.0/admin/resident/physician")
 *
 * @Grant(grant="persistence-resident-resident_physician", level="VIEW")
 *
 * Class ResidentPhysicianController
 * @package App\Api\V1\Admin\Controller
 */
class ResidentPhysicianController extends BaseController
{
    /**
     * @Route("/grid", name="api_admin_resident_physician_grid", methods={"GET"})
     *
     * @param Request $request
     * @param ResidentPhysicianService $residentPhysicianService
     * @return JsonResponse
     */
    public function gridAction(Request $request, ResidentPhysicianService $residentPhysicianService): JsonResponse
    {
        return $this->respondGrid(
            $request,
            ResidentPhysician::class,
            'api_admin_resident_physician_grid',
            $residentPhysicianService,
            ['resident_id' => $request->get('resident_id')]
        );
    }

    /**
     * @Route("/grid", name="api_admin_resident_physician_grid_options", methods={"OPTIONS"})
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function gridOptionAction(Request $request): JsonResponse
    {
        return $this->getOptionsByGroupName($request, ResidentPhysician::class, 'api_admin_resident_physician_grid');
    }

    /**
     * @Route("", name="api_admin_resident_physician_list", methods={"GET"})
     *
     * @param Request $request
     * @param ResidentPhysicianService $residentPhysicianService
     * @return PdfResponse|JsonResponse|Response
     */
    public function listAction(Request $request, ResidentPhysicianService $residentPhysicianService)
    {
        return $this->respondList(
            $request,
            ResidentPhysician::class,
            'api_admin_resident_physician_list',
            $residentPhysicianService,
            ['resident_id' => $request->get('resident_id')]
        );
    }

    /**
     * @Route("/{id}", requirements={"id"="\d+"}, name="api_admin_resident_physician_get", methods={"GET"})
     *
     * @param Request $request
     * @param $id
     * @param ResidentPhysicianService $residentPhysicianService
     * @return JsonResponse
     */
    public function getAction(Request $request, $id, ResidentPhysicianService $residentPhysicianService): JsonResponse
    {
        return $this->respondSuccess(
            Response::HTTP_OK,
            '',
            $residentPhysicianService->getById($id),
            ['api_admin_resident_physician_get']
        );
    }

    /**
     * @Route("", name="api_admin_resident_physician_add", methods={"POST"})
     *
     * @Grant(grant="persistence-resident-resident_physician", level="ADD")
     *
     * @param Request $request
     * @param ResidentPhysicianService $residentPhysicianService
     * @return JsonResponse
     */
    public function addAction(Request $request, ResidentPhysicianService $residentPhysicianService): JsonResponse
    {
        $id = $residentPhysicianService->add(
            [
                'resident_id' => $request->get('resident_id'),
                'physician_id' => $request->get('physician_id'),
                'primary' => $request->get('primary')
            ]
        );

        return $this->respondSuccess(
            Response::HTTP_CREATED,
            '',
            [$id]
        );
    }

    /**
     * @Route("/{id}", requirements={"id"="\d+"}, name="api_admin_resident_physician_edit", methods={"PUT"})
     *
     * @Grant(grant="persistence-resident-resident_physician", level="EDIT")
     *
     * @param Request $request
     * @param $id
     * @param ResidentPhysicianService $residentPhysicianService
     * @return JsonResponse
     */
    public function editAction(Request $request, $id, ResidentPhysicianService $residentPhysicianService): JsonResponse
    {
        $residentPhysicianService->edit(
            $id,
            [
                'resident_id' => $request->get('resident_id'),
                'physician_id' => $request->get('physician_id'),
                'primary' => $request->get('primary')
            ]
        );

        return $this->respondSuccess(
            Response::HTTP_CREATED
        );
    }

    /**
     * @Route("/{id}", requirements={"id"="\d+"}, name="api_admin_resident_physician_delete", methods={"DELETE"})
     *
     * @Grant(grant="persistence-resident-resident_physician", level="DELETE")
     *
     * @param Request $request
     * @param $id
     * @param ResidentPhysicianService $residentPhysicianService
     * @return JsonResponse
     */
    public function deleteAction(Request $request, $id, ResidentPhysicianService $residentPhysicianService): JsonResponse
    {
        $residentPhysicianService->remove($id);

        return $this->respondSuccess(
            Response::HTTP_NO_CONTENT
        );
    }

    /**
     * @Route("", name="api_admin_resident_physician_bulk", methods={"DELETE"})
     *
     * @Grant(grant="persistence-resident-resident_physician", level="DELETE")
     *
     * @param Request $request
     * @param ResidentPhysicianService $residentPhysicianService
     * @return JsonResponse
     */
    public function deleteBulkAction(Request $request, ResidentPhysicianService $residentPhysicianService): JsonResponse
    {
        $residentPhysicianService->removeBulk($request->get('ids'));

        return $this->respondSuccess(
            Response::HTTP_NO_CONTENT
        );
    }

    /**
     * @Route("/{resident_id}/primary", requirements={"resident_id"="\d+"}, name="api_admin_resident_physician_get_primary", methods={"GET"})
     *
     * @param Request $request
     * @param $resident_id
     * @param ResidentPhysicianService $residentPhysicianService
     * @return JsonResponse
     */
    public function getPrimaryAction(Request $request, $resident_id, ResidentPhysicianService $residentPhysicianService): JsonResponse
    {
        return $this->respondSuccess(
            Response::HTTP_OK,
            '',
            $residentPhysicianService->getPrimaryByResidentId($resident_id),
            ['api_admin_resident_physician_get']
        );
    }

    /**
     * @Route("/related/info", name="api_admin_resident_physician_related_info", methods={"POST"})
     *
     * @param Request $request
     * @param ResidentPhysicianService $residentDietService
     * @return JsonResponse
     */
    public function relatedInfoAction(Request $request, ResidentPhysicianService $residentDietService): JsonResponse
    {
        $relatedData = $residentDietService->getRelatedInfo($request->get('ids'));

        return $this->respondSuccess(
            Response::HTTP_OK,
            '',
            [$relatedData]
        );
    }

    /**
     * @Route("/reorder", name="api_admin_resident_physician_reorder", methods={"POST"})
     *
     * @Grant(grant="persistence-resident-resident_physician", level="EDIT")
     *
     * @param Request $request
     * @param ResidentPhysicianService $residentPhysicianService
     * @return JsonResponse
     */
    public function reorderAction(Request $request, ResidentPhysicianService $residentPhysicianService): JsonResponse
    {
        $residentPhysicianService->reorder(
            [
                'physicians' => $request->get('physicians')
            ]
        );

        return $this->respondSuccess(
            Response::HTTP_CREATED
        );
    }
}
