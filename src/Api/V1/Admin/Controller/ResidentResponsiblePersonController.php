<?php
namespace App\Api\V1\Admin\Controller;

use App\Api\V1\Admin\Service\ResidentResponsiblePersonService;
use App\Api\V1\Common\Controller\BaseController;
use App\Entity\ResidentResponsiblePerson;
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
 * @Route("/api/v1.0/admin/resident/responsible/person")
 *
 * @Grant(grant="persistence-resident-resident_responsible_person", level="VIEW")
 *
 * Class ResidentResponsiblePersonController
 * @package App\Api\V1\Admin\Controller
 */
class ResidentResponsiblePersonController extends BaseController
{
    /**
     * @Route("/grid", name="api_admin_resident_responsible_person_grid", methods={"GET"})
     *
     * @param Request $request
     * @param ResidentResponsiblePersonService $residentResponsiblePersonService
     * @return JsonResponse|PdfResponse
     * @throws \ReflectionException
     */
    public function gridAction(Request $request, ResidentResponsiblePersonService $residentResponsiblePersonService)
    {
        return $this->respondGrid(
            $request,
            ResidentResponsiblePerson::class,
            'api_admin_resident_responsible_person_grid',
            $residentResponsiblePersonService,
            ['resident_id' => $request->get('resident_id')]
        );
    }

    /**
     * @Route("/grid", name="api_admin_resident_responsible_person_grid_options", methods={"OPTIONS"})
     *
     * @param Request $request
     * @return JsonResponse
     * @throws \ReflectionException
     */
    public function gridOptionAction(Request $request)
    {
        return $this->getOptionsByGroupName($request, ResidentResponsiblePerson::class, 'api_admin_resident_responsible_person_grid');
    }

    /**
     * @Route("", name="api_admin_resident_responsible_person_list", methods={"GET"})
     *
     * @param Request $request
     * @param ResidentResponsiblePersonService $residentResponsiblePersonService
     * @return JsonResponse|PdfResponse
     * @throws \ReflectionException
     */
    public function listAction(Request $request, ResidentResponsiblePersonService $residentResponsiblePersonService)
    {
        return $this->respondList(
            $request,
            ResidentResponsiblePerson::class,
            'api_admin_resident_responsible_person_list',
            $residentResponsiblePersonService,
            ['resident_id' => $request->get('resident_id')]
        );
    }

    /**
     * @Route("/{id}", requirements={"id"="\d+"}, name="api_admin_resident_responsible_person_get", methods={"GET"})
     *
     * @param ResidentResponsiblePersonService $residentResponsiblePersonService
     * @param $id
     * @return JsonResponse
     */
    public function getAction(Request $request, $id, ResidentResponsiblePersonService $residentResponsiblePersonService)
    {
        return $this->respondSuccess(
            Response::HTTP_OK,
            '',
            $residentResponsiblePersonService->getById($id),
            ['api_admin_resident_responsible_person_get']
        );
    }

    /**
     * @Route("", name="api_admin_resident_responsible_person_add", methods={"POST"})
     *
     * @Grant(grant="persistence-resident-resident_responsible_person", level="ADD")
     *
     * @param Request $request
     * @param ResidentResponsiblePersonService $residentResponsiblePersonService
     * @return JsonResponse
     * @throws \Throwable
     */
    public function addAction(Request $request, ResidentResponsiblePersonService $residentResponsiblePersonService)
    {
        $id = $residentResponsiblePersonService->add(
            [
                'resident_id'           => $request->get('resident_id'),
                'responsible_person_id' => $request->get('responsible_person_id'),
                'relationship_id'       => $request->get('relationship_id'),
                'roles'                 => $request->get('roles'),
            ]
        );

        return $this->respondSuccess(
            Response::HTTP_CREATED,
            '',
            [$id]
        );
    }

    /**
     * @Route("/{id}", requirements={"id"="\d+"}, name="api_admin_resident_responsible_person_edit", methods={"PUT"})
     *
     * @Grant(grant="persistence-resident-resident_responsible_person", level="EDIT")
     *
     * @param Request $request
     * @param $id
     * @param ResidentResponsiblePersonService $residentResponsiblePersonService
     * @return JsonResponse
     * @throws \Throwable
     */
    public function editAction(Request $request, $id, ResidentResponsiblePersonService $residentResponsiblePersonService)
    {
        $residentResponsiblePersonService->edit(
            $id,
            [
                'resident_id'           => $request->get('resident_id'),
                'responsible_person_id' => $request->get('responsible_person_id'),
                'relationship_id'       => $request->get('relationship_id'),
                'roles'                 => $request->get('roles'),
            ]
        );

        return $this->respondSuccess(
            Response::HTTP_CREATED
        );
    }

    /**
     * @Route("/{id}", requirements={"id"="\d+"}, name="api_admin_resident_responsible_person_delete", methods={"DELETE"})
     *
     * @Grant(grant="persistence-resident-resident_responsible_person", level="DELETE")
     *
     * @param $id
     * @param ResidentResponsiblePersonService $residentResponsiblePersonService
     * @return JsonResponse
     * @throws \Doctrine\DBAL\ConnectionException
     * @throws \Throwable
     */
    public function deleteAction(Request $request, $id, ResidentResponsiblePersonService $residentResponsiblePersonService)
    {
        $residentResponsiblePersonService->remove($id);

        return $this->respondSuccess(
            Response::HTTP_NO_CONTENT
        );
    }

    /**
     * @Route("", name="api_admin_resident_responsible_person_delete_bulk", methods={"DELETE"})
     *
     * @Grant(grant="persistence-resident-resident_responsible_person", level="DELETE")
     *
     * @param Request $request
     * @param ResidentResponsiblePersonService $residentResponsiblePersonService
     * @return JsonResponse
     * @throws \Doctrine\DBAL\ConnectionException
     * @throws \Throwable
     */
    public function deleteBulkAction(Request $request, ResidentResponsiblePersonService $residentResponsiblePersonService)
    {
        $residentResponsiblePersonService->removeBulk($request->get('ids'));

        return $this->respondSuccess(
            Response::HTTP_NO_CONTENT
        );
    }

    /**
     * @Route("/related/info", name="api_admin_resident_responsible_person_related_info", methods={"POST"})
     *
     * @param Request $request
     * @param ResidentResponsiblePersonService $residentResponsiblePersonService
     * @return JsonResponse
     * @throws \Doctrine\DBAL\ConnectionException
     * @throws \Throwable
     */
    public function relatedInfoAction(Request $request, ResidentResponsiblePersonService $residentResponsiblePersonService)
    {
        $relatedData = $residentResponsiblePersonService->getRelatedInfo($request->get('ids'));

        return $this->respondSuccess(
            Response::HTTP_OK,
            '',
            [$relatedData]
        );
    }

    /**
     * @Route("/reorder", name="api_admin_resident_responsible_person_reorder", methods={"POST"})
     *
     * @Grant(grant="persistence-resident-resident_responsible_person", level="EDIT")
     *
     * @param Request $request
     * @param ResidentResponsiblePersonService $residentResponsiblePersonService
     * @return JsonResponse
     * @throws \Throwable
     */
    public function reorderAction(Request $request, ResidentResponsiblePersonService $residentResponsiblePersonService)
    {
        $residentResponsiblePersonService->reorder(
            [
                'responsible_persons' => $request->get('responsible_persons')
            ]
        );

        return $this->respondSuccess(
            Response::HTTP_CREATED
        );
    }
}
