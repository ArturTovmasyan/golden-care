<?php

namespace App\Api\V1\Lead\Controller;

use App\Annotation\Grant;
use App\Api\V1\Lead\Service\HobbyService;
use App\Api\V1\Common\Controller\BaseController;
use App\Entity\Lead\Hobby;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\JsonResponse;
use Knp\Bundle\SnappyBundle\Snappy\Response\PdfResponse;

/**
 * @Route("/api/v1.0/lead/hobby")
 *
 * @Grant(grant="persistence-lead-hobby", level="VIEW")
 *
 * Class HobbyController
 * @package App\Api\V1\Lead\Controller
 */
class HobbyController extends BaseController
{
    /**
     * @Route("/grid", name="api_lead_hobby", methods={"GET"})
     *
     * @param Request $request
     * @param HobbyService $hobbyService
     * @return JsonResponse
     */
    public function gridAction(Request $request, HobbyService $hobbyService): JsonResponse
    {
        return $this->respondGrid(
            $request,
            Hobby::class,
            'api_lead_hobby_grid',
            $hobbyService
        );
    }

    /**
     * @Route("/grid", name="api_lead_hobby_grid_options", methods={"OPTIONS"})
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function gridOptionAction(Request $request): JsonResponse
    {
        return $this->getOptionsByGroupName($request, Hobby::class, 'api_lead_hobby_grid');
    }

    /**
     * @Route("", name="api_lead_hobby_list", methods={"GET"})
     *
     * @param Request $request
     * @param HobbyService $hobbyService
     * @return PdfResponse|JsonResponse|Response
     */
    public function listAction(Request $request, HobbyService $hobbyService)
    {
        return $this->respondList(
            $request,
            Hobby::class,
            'api_lead_hobby_list',
            $hobbyService
        );
    }

    /**
     * @Route("/{id}", requirements={"id"="\d+"}, name="api_lead_hobby_get", methods={"GET"})
     *
     * @param Request $request
     * @param $id
     * @param HobbyService $hobbyService
     * @return JsonResponse
     */
    public function getAction(Request $request, $id, HobbyService $hobbyService): JsonResponse
    {
        return $this->respondSuccess(
            Response::HTTP_OK,
            '',
            $hobbyService->getById($id),
            ['api_lead_hobby_get']
        );
    }

    /**
     * @Route("", name="api_lead_hobby_add", methods={"POST"})
     *
     * @Grant(grant="persistence-lead-hobby", level="ADD")
     *
     * @param Request $request
     * @param HobbyService $hobbyService
     * @return JsonResponse
     */
    public function addAction(Request $request, HobbyService $hobbyService): JsonResponse
    {
        $id = $hobbyService->add(
            [
                'title' => $request->get('title'),
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
     * @Route("/{id}", requirements={"id"="\d+"}, name="api_lead_hobby_edit", methods={"PUT"})
     *
     * @Grant(grant="persistence-lead-hobby", level="EDIT")
     *
     * @param Request $request
     * @param $id
     * @param HobbyService $hobbyService
     * @return JsonResponse
     */
    public function editAction(Request $request, $id, HobbyService $hobbyService): JsonResponse
    {
        $hobbyService->edit(
            $id,
            [
                'title' => $request->get('title'),
                'space_id' => $request->get('space_id')
            ]
        );

        return $this->respondSuccess(
            Response::HTTP_CREATED
        );
    }

    /**
     * @Route("/{id}", requirements={"id"="\d+"}, name="api_lead_hobby_delete", methods={"DELETE"})
     *
     * @Grant(grant="persistence-lead-hobby", level="DELETE")
     *
     * @param Request $request
     * @param $id
     * @param HobbyService $hobbyService
     * @return JsonResponse
     */
    public function deleteAction(Request $request, $id, HobbyService $hobbyService): JsonResponse
    {
        $hobbyService->remove($id);

        return $this->respondSuccess(
            Response::HTTP_NO_CONTENT
        );
    }

    /**
     * @Route("", name="api_lead_hobby_delete_bulk", methods={"DELETE"})
     *
     * @Grant(grant="persistence-lead-hobby", level="DELETE")
     *
     * @param Request $request
     * @param HobbyService $hobbyService
     * @return JsonResponse
     */
    public function deleteBulkAction(Request $request, HobbyService $hobbyService): JsonResponse
    {
        $hobbyService->removeBulk($request->get('ids'));

        return $this->respondSuccess(
            Response::HTTP_NO_CONTENT
        );
    }

    /**
     * @Route("/related/info", name="api_lead_hobby_related_info", methods={"POST"})
     *
     * @param Request $request
     * @param HobbyService $hobbyService
     * @return JsonResponse
     */
    public function relatedInfoAction(Request $request, HobbyService $hobbyService): JsonResponse
    {
        $relatedData = $hobbyService->getRelatedInfo($request->get('ids'));

        return $this->respondSuccess(
            Response::HTTP_OK,
            '',
            [$relatedData]
        );
    }
}
