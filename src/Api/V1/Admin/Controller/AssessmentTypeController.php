<?php
namespace App\Api\V1\Admin\Controller;

use App\Api\V1\Admin\Service\AssessmentTypeService;
use App\Api\V1\Common\Controller\BaseController;
use App\Entity\Assessment\AssessmentType;
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
 * @Route("/api/v1.0/admin/assessment/type")
 *
 * @Grant(grant="persistence-assessment-assessment_type", level="VIEW")
 *
 * Class AssessmentTypeController
 * @package App\Api\V1\Admin\Controller
 */
class AssessmentTypeController extends BaseController
{
    /**
     * @Route("/grid", name="api_admin_assessment_type_grid", methods={"GET"})
     *
     * @param Request $request
     * @param AssessmentTypeService $assessmentTypeService
     * @return JsonResponse|PdfResponse
     * @throws \ReflectionException
     */
    public function gridAction(Request $request, AssessmentTypeService $assessmentTypeService)
    {
        return $this->respondGrid(
            $request,
            AssessmentType::class,
            'api_admin_assessment_type_grid',
            $assessmentTypeService
        );
    }

    /**
     * @Route("/grid", name="api_admin_assessment_type_grid_options", methods={"OPTIONS"})
     *
     * @param Request $request
     * @return JsonResponse
     * @throws \ReflectionException
     */
    public function gridOptionAction(Request $request)
    {
        return $this->getOptionsByGroupName($request, AssessmentType::class, 'api_admin_assessment_type_grid');
    }

    /**
     * @Route("", name="api_admin_assessment_type_list", methods={"GET"})
     *
     * @param Request $request
     * @param AssessmentTypeService $assessmentTypeService
     * @return JsonResponse|PdfResponse
     * @throws \ReflectionException
     */
    public function listAction(Request $request, AssessmentTypeService $assessmentTypeService)
    {
        return $this->respondList(
            $request,
            AssessmentType::class,
            'api_admin_assessment_type_list',
            $assessmentTypeService
        );
    }

    /**
     * @Route("/{id}", requirements={"id"="\d+"}, name="api_admin_assessment_type_get", methods={"GET"})
     *
     * @param AssessmentTypeService $assessmentTypeService
     * @param $id
     * @return JsonResponse
     */
    public function getAction(Request $request, $id, AssessmentTypeService $assessmentTypeService)
    {
        return $this->respondSuccess(
            Response::HTTP_OK,
            '',
            $assessmentTypeService->getById($id),
            ['api_admin_assessment_type_get']
        );
    }

    /**
     * @Route("", name="api_admin_assessment_type_add", methods={"POST"})
     *
     * @Grant(grant="persistence-assessment-assessment_type", level="ADD")
     *
     * @param Request $request
     * @param AssessmentTypeService $assessmentTypeService
     * @return JsonResponse
     * @throws \Throwable
     */
    public function addAction(Request $request, AssessmentTypeService $assessmentTypeService)
    {
        $id = $assessmentTypeService->add(
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
     * @Route("/{id}", requirements={"id"="\d+"}, name="api_admin_assessment_type_edit", methods={"PUT"})
     *
     * @Grant(grant="persistence-assessment-assessment_type", level="EDIT")
     *
     * @param Request $request
     * @param $id
     * @param AssessmentTypeService $assessmentTypeService
     * @return JsonResponse
     * @throws \Throwable
     */
    public function editAction(Request $request, $id, AssessmentTypeService $assessmentTypeService)
    {
        $assessmentTypeService->edit(
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
     * @Route("/{id}", requirements={"id"="\d+"}, name="api_admin_assessment_type_delete", methods={"DELETE"})
     *
     * @Grant(grant="persistence-assessment-assessment_type", level="DELETE")
     *
     * @param $id
     * @param AssessmentTypeService $assessmentTypeService
     * @return JsonResponse
     * @throws \Doctrine\DBAL\ConnectionException
     * @throws \Throwable
     */
    public function deleteAction(Request $request, $id, AssessmentTypeService $assessmentTypeService)
    {
        $assessmentTypeService->remove($id);

        return $this->respondSuccess(
            Response::HTTP_NO_CONTENT
        );
    }

    /**
     * @Route("", name="api_admin_assessment_type_delete_bulk", methods={"DELETE"})
     *
     * @Grant(grant="persistence-assessment-assessment_type", level="DELETE")
     *
     * @param Request $request
     * @param AssessmentTypeService $assessmentTypeService
     * @return JsonResponse
     * @throws \Doctrine\DBAL\ConnectionException
     * @throws \Throwable
     */
    public function deleteBulkAction(Request $request, AssessmentTypeService $assessmentTypeService)
    {
        $assessmentTypeService->removeBulk($request->get('ids'));

        return $this->respondSuccess(
            Response::HTTP_NO_CONTENT
        );
    }

    /**
     * @Route("/related/info", name="api_admin_assessment_type_related_info", methods={"POST"})
     *
     * @param Request $request
     * @param AssessmentTypeService $assessmentTypeService
     * @return JsonResponse
     * @throws \Doctrine\DBAL\ConnectionException
     * @throws \Throwable
     */
    public function relatedInfoAction(Request $request, AssessmentTypeService $assessmentTypeService)
    {
        $relatedData = $assessmentTypeService->getRelatedInfo($request->get('ids'));

        return $this->respondSuccess(
            Response::HTTP_OK,
            '',
            [$relatedData]
        );
    }
}
