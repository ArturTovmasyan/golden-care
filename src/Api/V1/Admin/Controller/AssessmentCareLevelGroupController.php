<?php
namespace App\Api\V1\Admin\Controller;

use App\Api\V1\Admin\Service\AssessmentCareLevelGroupService;
use App\Api\V1\Common\Controller\BaseController;
use App\Entity\Assessment\CareLevelGroup;
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
 * @Route("/api/v1.0/admin/assessment/care/level/group")
 *
 * @Grant(grant="persistence-assessment-care_level_group", level="VIEW")
 *
 * Class AssessmentCareLevelGroupController
 * @package App\Api\V1\Admin\Controller
 */
class AssessmentCareLevelGroupController extends BaseController
{
    /**
     * @Route("/grid", name="api_admin_assessment_care_level_group_grid", methods={"GET"})
     *
     * @param Request $request
     * @param AssessmentCareLevelGroupService $careLevelGroupService
     * @return JsonResponse|PdfResponse
     * @throws \ReflectionException
     */
    public function gridAction(Request $request, AssessmentCareLevelGroupService $careLevelGroupService)
    {
        return $this->respondGrid(
            $request,
            CareLevelGroup::class,
            'api_admin_assessment_care_level_group_grid',
            $careLevelGroupService
        );
    }

    /**
     * @Route("/grid", name="api_admin_assessment_care_level_group_grid_options", methods={"OPTIONS"})
     *
     * @param Request $request
     * @return JsonResponse
     * @throws \ReflectionException
     */
    public function gridOptionAction(Request $request)
    {
        return $this->getOptionsByGroupName($request, CareLevelGroup::class, 'api_admin_assessment_care_level_group_grid');
    }

    /**
     * @Route("", name="api_admin_assessment_care_level_group_list", methods={"GET"})
     *
     * @param Request $request
     * @param AssessmentCareLevelGroupService $careLevelGroupService
     * @return JsonResponse|PdfResponse
     * @throws \ReflectionException
     */
    public function listAction(Request $request, AssessmentCareLevelGroupService $careLevelGroupService)
    {
        return $this->respondList(
            $request,
            CareLevelGroup::class,
            'api_admin_assessment_care_level_group_list',
            $careLevelGroupService
        );
    }

    /**
     * @Route("/{id}", requirements={"id"="\d+"}, name="api_admin_assessment_care_level_group_get", methods={"GET"})
     *
     * @param AssessmentCareLevelGroupService $careLevelGroupService
     * @param $id
     * @return JsonResponse
     */
    public function getAction(Request $request, $id, AssessmentCareLevelGroupService $careLevelGroupService)
    {
        return $this->respondSuccess(
            Response::HTTP_OK,
            '',
            $careLevelGroupService->getById($id),
            ['api_admin_assessment_care_level_group_get']
        );
    }

    /**
     * @Route("", name="api_admin_assessment_care_level_group_add", methods={"POST"})
     *
     * @Grant(grant="persistence-assessment-care_level_group", level="ADD")
     *
     * @param Request $request
     * @param AssessmentCareLevelGroupService $careLevelGroupService
     * @return JsonResponse
     * @throws \Throwable
     */
    public function addAction(Request $request, AssessmentCareLevelGroupService $careLevelGroupService)
    {
        $id = $careLevelGroupService->add(
            [
                'title'    => $request->get('title'),
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
     * @Route("/{id}", requirements={"id"="\d+"}, name="api_admin_assessment_care_level_group_edit", methods={"PUT"})
     *
     * @Grant(grant="persistence-assessment-care_level_group", level="EDIT")
     *
     * @param Request $request
     * @param $id
     * @param AssessmentCareLevelGroupService $careLevelGroupService
     * @return JsonResponse
     * @throws \Throwable
     */
    public function editAction(Request $request, $id, AssessmentCareLevelGroupService $careLevelGroupService)
    {
        $careLevelGroupService->edit(
            $id,
            [
                'title'    => $request->get('title'),
                'space_id' => $request->get('space_id')
            ]
        );

        return $this->respondSuccess(
            Response::HTTP_CREATED
        );
    }

    /**
     * @Route("/{id}", requirements={"id"="\d+"}, name="api_admin_assessment_care_level_group_delete", methods={"DELETE"})
     *
     * @Grant(grant="persistence-assessment-care_level_group", level="DELETE")
     *
     * @param $id
     * @param AssessmentCareLevelGroupService $careLevelGroupService
     * @return JsonResponse
     * @throws \Doctrine\DBAL\ConnectionException
     * @throws \Throwable
     */
    public function deleteAction(Request $request, $id, AssessmentCareLevelGroupService $careLevelGroupService)
    {
        $careLevelGroupService->remove($id);

        return $this->respondSuccess(
            Response::HTTP_NO_CONTENT
        );
    }

    /**
     * @Route("", name="api_admin_assessment_care_level_group_delete_bulk", methods={"DELETE"})
     *
     * @Grant(grant="persistence-assessment-care_level_group", level="DELETE")
     *
     * @param Request $request
     * @param AssessmentCareLevelGroupService $careLevelGroupService
     * @return JsonResponse
     * @throws \Doctrine\DBAL\ConnectionException
     * @throws \Throwable
     */
    public function deleteBulkAction(Request $request, AssessmentCareLevelGroupService $careLevelGroupService)
    {
        $careLevelGroupService->removeBulk($request->get('ids'));

        return $this->respondSuccess(
            Response::HTTP_NO_CONTENT
        );
    }

    /**
     * @Route("/related/info", name="api_admin_assessment_care_level_group_related_info", methods={"POST"})
     *
     * @param Request $request
     * @param AssessmentCareLevelGroupService $careLevelGroupService
     * @return JsonResponse
     * @throws \Doctrine\DBAL\ConnectionException
     * @throws \Throwable
     */
    public function relatedInfoAction(Request $request, AssessmentCareLevelGroupService $careLevelGroupService)
    {
        $relatedData = $careLevelGroupService->getRelatedInfo($request->get('ids'));

        return $this->respondSuccess(
            Response::HTTP_OK,
            '',
            [$relatedData]
        );
    }
}
