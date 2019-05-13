<?php
namespace App\Api\V1\Lead\Controller;

use App\Api\V1\Lead\Service\StateChangeReasonService;
use App\Api\V1\Common\Controller\BaseController;
use App\Entity\Lead\StateChangeReason;
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
 * @Route("/api/v1.0/lead/state/change/reason")
 *
 * @Grant(grant="persistence-lead-state_change_reason", level="VIEW")
 *
 * Class StateChangeReasonController
 * @package App\Api\V1\Admin\Controller
 */
class StateChangeReasonController extends BaseController
{
    /**
     * @Route("/grid", name="api_lead_state_change_reason", methods={"GET"})
     *
     * @param Request $request
     * @param StateChangeReasonService $stateChangeReasonService
     * @return JsonResponse|PdfResponse
     * @throws \ReflectionException
     */
    public function gridAction(Request $request, StateChangeReasonService $stateChangeReasonService)
    {
        return $this->respondGrid(
            $request,
            StateChangeReason::class,
            'api_lead_state_change_reason_grid',
            $stateChangeReasonService
        );
    }

    /**
     * @Route("/grid", name="api_lead_state_change_reason_grid_options", methods={"OPTIONS"})
     *
     * @param Request $request
     * @return JsonResponse
     * @throws \ReflectionException
     */
    public function gridOptionAction(Request $request)
    {
        return $this->getOptionsByGroupName(StateChangeReason::class, 'api_lead_state_change_reason_grid');
    }

    /**
     * @Route("", name="api_lead_state_change_reason_list", methods={"GET"})
     *
     * @param Request $request
     * @param StateChangeReasonService $stateChangeReasonService
     * @return JsonResponse|PdfResponse
     * @throws \ReflectionException
     */
    public function listAction(Request $request, StateChangeReasonService $stateChangeReasonService)
    {
        return $this->respondList(
            $request,
            StateChangeReason::class,
            'api_lead_state_change_reason_list',
            $stateChangeReasonService
        );
    }

    /**
     * @Route("/{id}", requirements={"id"="\d+"}, name="api_lead_state_change_reason_get", methods={"GET"})
     *
     * @param StateChangeReasonService $stateChangeReasonService
     * @param $id
     * @return JsonResponse
     */
    public function getAction(Request $request, $id, StateChangeReasonService $stateChangeReasonService)
    {
        return $this->respondSuccess(
            Response::HTTP_OK,
            '',
            $stateChangeReasonService->getById($id),
            ['api_lead_state_change_reason_get']
        );
    }

    /**
     * @Route("", name="api_lead_state_change_reason_add", methods={"POST"})
     *
     * @Grant(grant="persistence-lead-state_change_reason", level="ADD")
     *
     * @param Request $request
     * @param StateChangeReasonService $stateChangeReasonService
     * @return JsonResponse
     * @throws \Exception
     */
    public function addAction(Request $request, StateChangeReasonService $stateChangeReasonService)
    {
        $id = $stateChangeReasonService->add(
            [
                'title' => $request->get('title'),
                'state' => $request->get('state'),
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
     * @Route("/{id}", requirements={"id"="\d+"}, name="api_lead_state_change_reason_edit", methods={"PUT"})
     *
     * @Grant(grant="persistence-lead-state_change_reason", level="EDIT")
     *
     * @param Request $request
     * @param $id
     * @param StateChangeReasonService $stateChangeReasonService
     * @return JsonResponse
     * @throws \Exception
     */
    public function editAction(Request $request, $id, StateChangeReasonService $stateChangeReasonService)
    {
        $stateChangeReasonService->edit(
            $id,
            [
                'title' => $request->get('title'),
                'state' => $request->get('state'),
                'space_id' => $request->get('space_id')
            ]
        );

        return $this->respondSuccess(
            Response::HTTP_CREATED
        );
    }

    /**
     * @Route("/{id}", requirements={"id"="\d+"}, name="api_lead_state_change_reason_delete", methods={"DELETE"})
     *
     * @Grant(grant="persistence-lead-state_change_reason", level="DELETE")
     *
     * @param $id
     * @param StateChangeReasonService $stateChangeReasonService
     * @return JsonResponse
     * @throws \Doctrine\DBAL\ConnectionException
     * @throws \Throwable
     */
    public function deleteAction(Request $request, $id, StateChangeReasonService $stateChangeReasonService)
    {
        $stateChangeReasonService->remove($id);

        return $this->respondSuccess(
            Response::HTTP_NO_CONTENT
        );
    }

    /**
     * @Route("", name="api_lead_state_change_reason_delete_bulk", methods={"DELETE"})
     *
     * @Grant(grant="persistence-lead-state_change_reason", level="DELETE")
     *
     * @param Request $request
     * @param StateChangeReasonService $stateChangeReasonService
     * @return JsonResponse
     * @throws \Doctrine\DBAL\ConnectionException
     * @throws \Throwable
     */
    public function deleteBulkAction(Request $request, StateChangeReasonService $stateChangeReasonService)
    {
        $stateChangeReasonService->removeBulk($request->get('ids'));

        return $this->respondSuccess(
            Response::HTTP_NO_CONTENT
        );
    }

    /**
     * @Route("/related/info", name="api_lead_state_change_reason_related_info", methods={"POST"})
     *
     * @param Request $request
     * @param StateChangeReasonService $stateChangeReasonService
     * @return JsonResponse
     * @throws \Doctrine\DBAL\ConnectionException
     * @throws \Throwable
     */
    public function relatedInfoAction(Request $request, StateChangeReasonService $stateChangeReasonService)
    {
        $relatedData = $stateChangeReasonService->getRelatedInfo($request->get('ids'));

        return $this->respondSuccess(
            Response::HTTP_OK,
            '',
            [$relatedData]
        );
    }
}
