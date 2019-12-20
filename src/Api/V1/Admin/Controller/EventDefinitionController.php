<?php
namespace App\Api\V1\Admin\Controller;

use App\Api\V1\Admin\Service\EventDefinitionService;
use App\Api\V1\Common\Controller\BaseController;
use App\Entity\EventDefinition;
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
 * @Route("/api/v1.0/admin/event/definition")
 *
 * @Grant(grant="persistence-common-event_definition", level="VIEW")
 *
 * Class EventDefinitionController
 * @package App\Api\V1\Admin\Controller
 */
class EventDefinitionController extends BaseController
{
    /**
     * @Route("/grid", name="api_admin_event_definition_grid", methods={"GET"})
     *
     * @param Request $request
     * @param EventDefinitionService $eventDefinitionService
     * @return JsonResponse|PdfResponse
     * @throws \ReflectionException
     */
    public function gridAction(Request $request, EventDefinitionService $eventDefinitionService)
    {
        return $this->respondGrid(
            $request,
            EventDefinition::class,
            'api_admin_event_definition_grid',
            $eventDefinitionService
        );
    }

    /**
     * @Route("/grid", name="api_admin_event_definition_grid_options", methods={"OPTIONS"})
     *
     * @param Request $request
     * @return JsonResponse
     * @throws \ReflectionException
     */
    public function gridOptionAction(Request $request)
    {
        return $this->getOptionsByGroupName($request, EventDefinition::class, 'api_admin_event_definition_grid');
    }

    /**
     * @Route("", name="api_admin_event_definition_list", methods={"GET"})
     *
     * @param Request $request
     * @param EventDefinitionService $eventDefinitionService
     * @return JsonResponse|PdfResponse
     * @throws \ReflectionException
     */
    public function listAction(Request $request, EventDefinitionService $eventDefinitionService)
    {
        return $this->respondList(
            $request,
            EventDefinition::class,
            'api_admin_event_definition_list',
            $eventDefinitionService,
            [
                'show' => $request->get('show')
            ]
        );
    }

    /**
     * @Route("/{id}", requirements={"id"="\d+"}, name="api_admin_event_definition_get", methods={"GET"})
     *
     * @param EventDefinitionService $eventDefinitionService
     * @param $id
     * @return JsonResponse
     */
    public function getAction(Request $request, $id, EventDefinitionService $eventDefinitionService)
    {
        return $this->respondSuccess(
            Response::HTTP_OK,
            '',
            $eventDefinitionService->getById($id),
            ['api_admin_event_definition_get']
        );
    }

    /**
     * @Route("", name="api_admin_event_definition_add", methods={"POST"})
     *
     * @Grant(grant="persistence-common-event_definition", level="ADD")
     *
     * @param Request $request
     * @param EventDefinitionService $eventDefinitionService
     * @return JsonResponse
     * @throws \Throwable
     */
    public function addAction(Request $request, EventDefinitionService $eventDefinitionService)
    {
        $id = $eventDefinitionService->add(
            [
                'type' => $request->get('type'),
                'show' => $request->get('show'),
                'title' => $request->get('title'),
                'in_chooser' => $request->get('in_chooser'),
                'ffc' => $request->get('ffc'),
                'ihc' => $request->get('ihc'),
                'il' => $request->get('il'),
                'physician' => $request->get('physician'),
                'physician_optional' => $request->get('physician_optional'),
                'responsible_person' => $request->get('responsible_person'),
                'responsible_person_optional' => $request->get('responsible_person_optional'),
                'responsible_person_multi' => $request->get('responsible_person_multi'),
                'responsible_person_multi_optional' => $request->get('responsible_person_multi_optional'),
                'additional_date' => $request->get('additional_date'),
                'residents' => $request->get('residents'),
                'users' => $request->get('users'),
                'duration' => $request->get('duration'),
                'repeats' => $request->get('repeats'),
                'rsvp' => $request->get('rsvp'),
                'done' => $request->get('done'),
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
     * @Route("/{id}", requirements={"id"="\d+"}, name="api_admin_event_definition_edit", methods={"PUT"})
     *
     * @Grant(grant="persistence-common-event_definition", level="EDIT")
     *
     * @param Request $request
     * @param $id
     * @param EventDefinitionService $eventDefinitionService
     * @return JsonResponse
     * @throws \Throwable
     */
    public function editAction(Request $request, $id, EventDefinitionService $eventDefinitionService)
    {
        $eventDefinitionService->edit(
            $id,
            [
                'type' => $request->get('type'),
                'show' => $request->get('show'),
                'title' => $request->get('title'),
                'in_chooser' => $request->get('in_chooser'),
                'ffc' => $request->get('ffc'),
                'ihc' => $request->get('ihc'),
                'il' => $request->get('il'),
                'physician' => $request->get('physician'),
                'physician_optional' => $request->get('physician_optional'),
                'responsible_person' => $request->get('responsible_person'),
                'responsible_person_optional' => $request->get('responsible_person_optional'),
                'responsible_person_multi' => $request->get('responsible_person_multi'),
                'responsible_person_multi_optional' => $request->get('responsible_person_multi_optional'),
                'additional_date' => $request->get('additional_date'),
                'residents' => $request->get('residents'),
                'users' => $request->get('users'),
                'duration' => $request->get('duration'),
                'repeats' => $request->get('repeats'),
                'rsvp' => $request->get('rsvp'),
                'done' => $request->get('done'),
                'space_id' => $request->get('space_id')
            ]
        );

        return $this->respondSuccess(
            Response::HTTP_CREATED
        );
    }

    /**
     * @Route("/{id}", requirements={"id"="\d+"}, name="api_admin_event_definition_delete", methods={"DELETE"})
     *
     * @Grant(grant="persistence-common-event_definition", level="DELETE")
     *
     * @param $id
     * @param EventDefinitionService $eventDefinitionService
     * @return JsonResponse
     * @throws \Doctrine\DBAL\ConnectionException
     * @throws \Throwable
     */
    public function deleteAction(Request $request, $id, EventDefinitionService $eventDefinitionService)
    {
        $eventDefinitionService->remove($id);

        return $this->respondSuccess(
            Response::HTTP_NO_CONTENT
        );
    }

    /**
     * @Route("", name="api_admin_event_definition_delete_bulk", methods={"DELETE"})
     *
     * @Grant(grant="persistence-common-event_definition", level="DELETE")
     *
     * @param Request $request
     * @param EventDefinitionService $eventDefinitionService
     * @return JsonResponse
     * @throws \Doctrine\DBAL\ConnectionException
     * @throws \Throwable
     */
    public function deleteBulkAction(Request $request, EventDefinitionService $eventDefinitionService)
    {
        $eventDefinitionService->removeBulk($request->get('ids'));

        return $this->respondSuccess(
            Response::HTTP_NO_CONTENT
        );
    }

    /**
     * @Route("/related/info", name="api_admin_event_definition_related_info", methods={"POST"})
     *
     * @param Request $request
     * @param EventDefinitionService $eventDefinitionService
     * @return JsonResponse
     * @throws \Doctrine\DBAL\ConnectionException
     * @throws \Throwable
     */
    public function relatedInfoAction(Request $request, EventDefinitionService $eventDefinitionService)
    {
        $relatedData = $eventDefinitionService->getRelatedInfo($request->get('ids'));

        return $this->respondSuccess(
            Response::HTTP_OK,
            '',
            [$relatedData]
        );
    }
}
