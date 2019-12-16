<?php
namespace App\Api\V1\Admin\Controller;

use App\Api\V1\Admin\Service\AssessmentCareLevelService;
use App\Api\V1\Common\Controller\BaseController;
use App\Entity\Assessment\CareLevel;
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
 * @Route("/api/v1.0/admin/assessment/care/level")
 *
 * @Grant(grant="persistence-assessment-care_level", level="VIEW")
 *
 * Class AssessmentCareLevelController
 * @package App\Api\V1\Admin\Controller
 */
class AssessmentCareLevelController extends BaseController
{
    /**
     * @Route("/grid", name="api_admin_assessment_care_level_grid", methods={"GET"})
     *
     * @param Request $request
     * @param AssessmentCareLevelService $careLevelService
     * @return JsonResponse|PdfResponse
     * @throws \ReflectionException
     */
    public function gridAction(Request $request, AssessmentCareLevelService $careLevelService)
    {
        return $this->respondGrid(
            $request,
            CareLevel::class,
            'api_admin_assessment_care_level_grid',
            $careLevelService
        );
    }

    /**
     * @Route("/grid", name="api_admin_assessment_care_level_grid_options", methods={"OPTIONS"})
     *
     * @param Request $request
     * @return JsonResponse
     * @throws \ReflectionException
     */
    public function gridOptionAction(Request $request)
    {
        return $this->getOptionsByGroupName($request, CareLevel::class, 'api_admin_assessment_care_level_grid');
    }

    /**
     * @Route("", name="api_admin_assessment_care_level_list", methods={"GET"})
     *
     * @param Request $request
     * @param AssessmentCareLevelService $careLevelService
     * @return JsonResponse|PdfResponse
     * @throws \ReflectionException
     */
    public function listAction(Request $request, AssessmentCareLevelService $careLevelService)
    {
        return $this->respondList(
            $request,
            CareLevel::class,
            'api_admin_assessment_care_level_list',
            $careLevelService
        );
    }

    /**
     * @Route("/{id}", requirements={"id"="\d+"}, name="api_admin_assessment_care_level_get", methods={"GET"})
     *
     * @param AssessmentCareLevelService $careLevelService
     * @param $id
     * @return JsonResponse
     */
    public function getAction(Request $request, $id, AssessmentCareLevelService $careLevelService)
    {
        return $this->respondSuccess(
            Response::HTTP_OK,
            '',
            $careLevelService->getById($id),
            ['api_admin_assessment_care_level_get']
        );
    }

    /**
     * @Route("", name="api_admin_assessment_care_level_add", methods={"POST"})
     *
     * @Grant(grant="persistence-assessment-care_level", level="ADD")
     *
     * @param Request $request
     * @param AssessmentCareLevelService $careLevelService
     * @return JsonResponse
     * @throws \Throwable
     */
    public function addAction(Request $request, AssessmentCareLevelService $careLevelService)
    {
        $id = $careLevelService->add(
            [
                'title'               => $request->get('title'),
                'space_id'            => $request->get('space_id'),
                'level_low'           => $request->get('level_low'),
                'level_high'          => $request->get('level_high'),
                'care_level_group_id' => $request->get('care_level_group_id'),
            ]
        );

        return $this->respondSuccess(
            Response::HTTP_CREATED,
            '',
            [$id]
        );
    }

    /**
     * @Route("/{id}", requirements={"id"="\d+"}, name="api_admin_assessment_care_level_edit", methods={"PUT"})
     *
     * @Grant(grant="persistence-assessment-care_level", level="EDIT")
     *
     * @param Request $request
     * @param $id
     * @param AssessmentCareLevelService $careLevelService
     * @return JsonResponse
     * @throws \Throwable
     */
    public function editAction(Request $request, $id, AssessmentCareLevelService $careLevelService)
    {
        $careLevelService->edit(
            $id,
            [
                'title'               => $request->get('title'),
                'space_id'            => $request->get('space_id'),
                'level_low'           => $request->get('level_low'),
                'level_high'          => $request->get('level_high'),
                'care_level_group_id' => $request->get('care_level_group_id'),
            ]
        );

        return $this->respondSuccess(
            Response::HTTP_CREATED
        );
    }

    /**
     * @Route("/{id}", requirements={"id"="\d+"}, name="api_admin_assessment_care_level_delete", methods={"DELETE"})
     *
     * @Grant(grant="persistence-assessment-care_level", level="DELETE")
     *
     * @param $id
     * @param AssessmentCareLevelService $careLevelService
     * @return JsonResponse
     * @throws \Doctrine\DBAL\ConnectionException
     * @throws \Throwable
     */
    public function deleteAction(Request $request, $id, AssessmentCareLevelService $careLevelService)
    {
        $careLevelService->remove($id);

        return $this->respondSuccess(
            Response::HTTP_NO_CONTENT
        );
    }

    /**
     * @Route("", name="api_admin_assessment_care_level_delete_bulk", methods={"DELETE"})
     *
     * @Grant(grant="persistence-assessment-care_level", level="DELETE")
     *
     * @param Request $request
     * @param AssessmentCareLevelService $careLevelService
     * @return JsonResponse
     * @throws \Doctrine\DBAL\ConnectionException
     * @throws \Throwable
     */
    public function deleteBulkAction(Request $request, AssessmentCareLevelService $careLevelService)
    {
        $careLevelService->removeBulk($request->get('ids'));

        return $this->respondSuccess(
            Response::HTTP_NO_CONTENT
        );
    }

    /**
     * @Route("/related/info", name="api_admin_assessment_care_level_related_info", methods={"POST"})
     *
     * @param Request $request
     * @param AssessmentCareLevelService $careLevelSService
     * @return JsonResponse
     * @throws \Doctrine\DBAL\ConnectionException
     * @throws \Throwable
     */
    public function relatedInfoAction(Request $request, AssessmentCareLevelService $careLevelSService)
    {
        $relatedData = $careLevelSService->getRelatedInfo($request->get('ids'));

        return $this->respondSuccess(
            Response::HTTP_OK,
            '',
            [$relatedData]
        );
    }
}
