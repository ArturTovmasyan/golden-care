<?php

namespace App\Api\V1\Admin\Controller;

use App\Annotation\Grant;
use App\Api\V1\Admin\Service\CorporateEventService;
use App\Api\V1\Common\Controller\BaseController;
use App\Entity\CorporateEvent;
use App\Entity\User;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\JsonResponse;
use Knp\Bundle\SnappyBundle\Snappy\Response\PdfResponse;

/**
 * @Route("/api/v1.0/admin/corporate/event")
 *
 * @Grant(grant="persistence-corporate-corporate_event", level="VIEW")
 *
 * Class CorporateEventController
 * @package App\Api\V1\Admin\Controller
 */
class CorporateEventController extends BaseController
{
    /**
     * @Route("/grid", name="api_admin_corporate_event_grid", methods={"GET"})
     *
     * @param Request $request
     * @param CorporateEventService $corporateEventService
     * @return JsonResponse
     */
    public function gridAction(Request $request, CorporateEventService $corporateEventService): JsonResponse
    {
        /** @var User $user */
        $user = $this->get('security.token_storage')->getToken()->getUser();

        $userRoleIds = [];
        if ($user !== null) {
            $userRoles = $user->getRoleObjects();

            if ($userRoles !== null) {
                $userRoleIds = array_map(function ($item) {
                    return $item->getId();
                }, $userRoles->toArray());
            }
        }

        return $this->respondGrid(
            $request,
            CorporateEvent::class,
            'api_admin_corporate_event_grid',
            $corporateEventService,
            [
                'user_role_ids' => $userRoleIds
            ]
        );
    }

    /**
     * @Route("/grid", name="api_admin_corporate_event_grid_options", methods={"OPTIONS"})
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function gridOptionAction(Request $request): JsonResponse
    {
        return $this->getOptionsByGroupName($request, CorporateEvent::class, 'api_admin_corporate_event_grid');
    }

    /**
     * @Route("", name="api_admin_corporate_event_list", methods={"GET"})
     *
     * @param Request $request
     * @param CorporateEventService $corporateEventService
     * @return PdfResponse|JsonResponse|Response
     */
    public function listAction(Request $request, CorporateEventService $corporateEventService)
    {
        /** @var User $user */
        $user = $this->get('security.token_storage')->getToken()->getUser();

        $userRoleIds = [];
        if ($user !== null) {
            $userRoles = $user->getRoleObjects();

            if ($userRoles !== null) {
                $userRoleIds = array_map(function ($item) {
                    return $item->getId();
                }, $userRoles->toArray());
            }
        }

        return $this->respondList(
            $request,
            CorporateEvent::class,
            'api_admin_corporate_event_list',
            $corporateEventService,
            [
                'user_role_ids' => $userRoleIds
            ]
        );
    }

    /**
     * @Route("/{id}", requirements={"id"="\d+"}, name="api_admin_corporate_event_get", methods={"GET"})
     *
     * @param Request $request
     * @param $id
     * @param CorporateEventService $corporateEventService
     * @return JsonResponse
     */
    public function getAction(Request $request, $id, CorporateEventService $corporateEventService): JsonResponse
    {
        return $this->respondSuccess(
            Response::HTTP_OK,
            '',
            $corporateEventService->getById($id),
            ['api_admin_corporate_event_get']
        );
    }

    /**
     * @Route("", name="api_admin_corporate_event_add", methods={"POST"})
     *
     * @Grant(grant="persistence-corporate-corporate_event", level="ADD")
     *
     * @param Request $request
     * @param CorporateEventService $corporateEventService
     * @return JsonResponse
     */
    public function addAction(Request $request, CorporateEventService $corporateEventService): JsonResponse
    {
        $id = $corporateEventService->add(
            [
                'definition_id' => $request->get('definition_id'),
                'title' => $request->get('title'),
                'start_date' => $request->get('start_date'),
                'start_time' => $request->get('start_time'),
                'end_date' => $request->get('end_date'),
                'end_time' => $request->get('end_time'),
                'all_day' => $request->get('all_day'),
                'repeat' => $request->get('repeat'),
                'repeat_end' => $request->get('repeat_end'),
                'no_repeat_end' => $request->get('no_repeat_end'),
                'rsvp' => $request->get('rsvp'),
                'notes' => $request->get('notes') ?? '',
                'facilities' => $request->get('facilities'),
                'roles' => $request->get('roles'),
                'users' => $request->get('users')
            ]
        );

        return $this->respondSuccess(
            Response::HTTP_CREATED,
            '',
            [$id]
        );
    }

    /**
     * @Route("/{id}", requirements={"id"="\d+"}, name="api_admin_corporate_event_edit", methods={"PUT"})
     *
     * @Grant(grant="persistence-corporate-corporate_event", level="EDIT")
     *
     * @param Request $request
     * @param $id
     * @param CorporateEventService $corporateEventService
     * @return JsonResponse
     */
    public function editAction(Request $request, $id, CorporateEventService $corporateEventService): JsonResponse
    {
        $corporateEventService->edit(
            $id,
            [
                'definition_id' => $request->get('definition_id'),
                'title' => $request->get('title'),
                'start_date' => $request->get('start_date'),
                'start_time' => $request->get('start_time'),
                'end_date' => $request->get('end_date'),
                'end_time' => $request->get('end_time'),
                'all_day' => $request->get('all_day'),
                'repeat' => $request->get('repeat'),
                'repeat_end' => $request->get('repeat_end'),
                'no_repeat_end' => $request->get('no_repeat_end'),
                'rsvp' => $request->get('rsvp'),
                'notes' => $request->get('notes') ?? '',
                'facilities' => $request->get('facilities'),
                'roles' => $request->get('roles'),
                'users' => $request->get('users')
            ]
        );

        return $this->respondSuccess(
            Response::HTTP_CREATED
        );
    }

    /**
     * @Route("/{id}", requirements={"id"="\d+"}, name="api_admin_corporate_event_delete", methods={"DELETE"})
     *
     * @Grant(grant="persistence-corporate-corporate_event", level="DELETE")
     *
     * @param Request $request
     * @param $id
     * @param CorporateEventService $corporateEventService
     * @return JsonResponse
     */
    public function deleteAction(Request $request, $id, CorporateEventService $corporateEventService): JsonResponse
    {
        $corporateEventService->remove($id);

        return $this->respondSuccess(
            Response::HTTP_NO_CONTENT
        );
    }

    /**
     * @Route("", name="api_admin_corporate_event_delete_bulk", methods={"DELETE"})
     *
     * @Grant(grant="persistence-corporate-corporate_event", level="DELETE")
     *
     * @param Request $request
     * @param CorporateEventService $corporateEventService
     * @return JsonResponse
     */
    public function deleteBulkAction(Request $request, CorporateEventService $corporateEventService): JsonResponse
    {
        $corporateEventService->removeBulk($request->get('ids'));

        return $this->respondSuccess(
            Response::HTTP_NO_CONTENT
        );
    }

    /**
     * @Route("/related/info", name="api_admin_corporate_event_related_info", methods={"POST"})
     *
     * @param Request $request
     * @param CorporateEventService $corporateEventService
     * @return JsonResponse
     * @throws \Doctrine\DBAL\ConnectionException
     * @throws \Throwable
     */
    public function relatedInfoAction(Request $request, CorporateEventService $corporateEventService): JsonResponse
    {
        $relatedData = $corporateEventService->getRelatedInfo($request->get('ids'));

        return $this->respondSuccess(
            Response::HTTP_OK,
            '',
            [$relatedData]
        );
    }

    /**
     * @Route("/done/{id}", requirements={"id"="\d+"}, name="api_admin_corporate_event_edit_done", methods={"POST"})
     *
     * @Grant(grant="persistence-corporate-corporate_event", level="EDIT")
     *
     * @param Request $request
     * @param $id
     * @param CorporateEventService $corporateEventService
     * @return JsonResponse
     */
    public function changeDoneByCurrentUserAction(Request $request, $id, CorporateEventService $corporateEventService): JsonResponse
    {
        /** @var User $user */
        $user = $this->get('security.token_storage')->getToken()->getUser();

        $corporateEventService->changeDoneByCurrentUser(
            $id,
            $user,
            [
                'done' => $request->get('done')
            ]
        );

        return $this->respondSuccess(
            Response::HTTP_CREATED
        );
    }

    /**
     * @Route("/done/{id}", requirements={"id"="\d+"}, name="api_admin_corporate_event_get_is_done", methods={"GET"})
     *
     * @param Request $request
     * @param $id
     * @param CorporateEventService $corporateEventService
     * @return JsonResponse
     */
    public function getIsDoneAction(Request $request, $id, CorporateEventService $corporateEventService): JsonResponse
    {
        /** @var User $user */
        $user = $this->get('security.token_storage')->getToken()->getUser();

        return $this->respondSuccess(
            Response::HTTP_OK,
            '',
            $corporateEventService->getIsDone($id, $user),
            ['api_admin_corporate_event_get_is_done']
        );
    }
}
