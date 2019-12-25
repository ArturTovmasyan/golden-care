<?php
namespace App\Api\V1\Admin\Controller;

use App\Api\V1\Admin\Service\ResidentAssessmentService;
use App\Api\V1\Common\Controller\BaseController;
use App\Entity\Assessment\Assessment;
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
 * @Route("/api/v1.0/admin/resident/assessment")
 *
 * @Grant(grant="persistence-resident-assessment-assessment", level="VIEW")
 *
 * Class AssessmentController
 * @package App\Api\V1\Admin\Controller
 */
class ResidentAssessmentController extends BaseController
{
    /**
     * @Route("/grid", name="api_admin_resident_assessment_grid", methods={"GET"})
     *
     * @param Request $request
     * @param ResidentAssessmentService $residentAssessmentService
     * @return JsonResponse|PdfResponse
     * @throws \ReflectionException
     */
    public function gridAction(Request $request, ResidentAssessmentService $residentAssessmentService)
    {
        return $this->respondGrid(
            $request,
            Assessment::class,
            'api_admin_resident_assessment_grid',
            $residentAssessmentService,
            ['resident_id' => $request->get('resident_id')]
        );
    }

    /**
     * @Route("/grid", name="api_admin_resident_assessment_grid_options", methods={"OPTIONS"})
     *
     * @param Request $request
     * @return JsonResponse
     * @throws \ReflectionException
     */
    public function gridOptionAction(Request $request)
    {
        return $this->getOptionsByGroupName($request, Assessment::class, 'api_admin_resident_assessment_grid');
    }

    /**
     * @Route("", name="api_admin_resident_assessment_list", methods={"GET"})
     *
     * @param Request $request
     * @param ResidentAssessmentService $residentAssessmentService
     * @return JsonResponse|PdfResponse
     * @throws \ReflectionException
     */
    public function listAction(Request $request, ResidentAssessmentService $residentAssessmentService)
    {
        return $this->respondList(
            $request,
            Assessment::class,
            'api_admin_resident_assessment_list',
            $residentAssessmentService,
            ['resident_id' => $request->get('resident_id')]
        );
    }

    /**
     * @Route("/{id}", requirements={"id"="\d+"}, name="api_admin_resident_assessment_get", methods={"GET"})
     *
     * @param Request $request
     * @param $id
     * @param ResidentAssessmentService $residentAssessmentService
     * @return JsonResponse
     */
    public function getAction(Request $request, $id, ResidentAssessmentService $residentAssessmentService)
    {
        return $this->respondSuccess(
            Response::HTTP_OK,
            '',
            $residentAssessmentService->getById($id),
            ['api_admin_resident_assessment_get']
        );
    }

    /**
     * @Route("", name="api_admin_resident_assessment_add", methods={"POST"})
     *
     * @Grant(grant="persistence-resident-assessment-assessment", level="ADD")
     *
     * @param Request $request
     * @param ResidentAssessmentService $residentAssessmentService
     * @return JsonResponse
     * @throws \Throwable
     */
    public function addAction(Request $request, ResidentAssessmentService $residentAssessmentService)
    {
        $id = $residentAssessmentService->add(
            [
                'space_id'     => $request->get('space_id'),
                'resident_id'  => $request->get('resident_id'),
                'type_id'  => $request->get('type_id'),
                'form_id'      => $request->get('form_id'),
                'date'         => $request->get('date'),
                'performed_by' => $request->get('performed_by'),
                'notes'        => $request->get('notes'),
                'rows'         => $request->get('rows'),
            ]
        );

        return $this->respondSuccess(
            Response::HTTP_CREATED,
            '',
            [$id]
        );
    }

    /**
     * @Route("/{id}", requirements={"id"="\d+"}, name="api_admin_resident_assessment_edit", methods={"PUT"})
     *
     * @Grant(grant="persistence-resident-assessment-assessment", level="EDIT")
     *
     * @param Request $request
     * @param $id
     * @param ResidentAssessmentService $residentAssessmentService
     * @return JsonResponse
     * @throws \Throwable
     */
    public function editAction(Request $request, $id, ResidentAssessmentService $residentAssessmentService)
    {
        $residentAssessmentService->edit(
            $id,
            [
                'space_id'     => $request->get('space_id'),
                'resident_id'  => $request->get('resident_id'),
                'type_id'  => $request->get('type_id'),
                'form_id'      => $request->get('form_id'),
                'date'         => $request->get('date'),
                'performed_by' => $request->get('performed_by'),
                'notes'        => $request->get('notes'),
                'rows'         => $request->get('rows'),
            ]
        );

        return $this->respondSuccess(
            Response::HTTP_CREATED
        );
    }

    /**
     * @Route("/{id}", requirements={"id"="\d+"}, name="api_admin_resident_assessment_delete", methods={"DELETE"})
     *
     * @Grant(grant="persistence-resident-assessment-assessment", level="DELETE")
     *
     * @param $id
     * @param ResidentAssessmentService $residentAssessmentService
     * @return JsonResponse
     * @throws \Doctrine\DBAL\ConnectionException
     * @throws \Throwable
     */
    public function deleteAction(Request $request, $id, ResidentAssessmentService $residentAssessmentService)
    {
        $residentAssessmentService->remove($id);

        return $this->respondSuccess(
            Response::HTTP_NO_CONTENT
        );
    }

    /**
     * @Route("", name="api_admin_resident_assessment_delete_bulk", methods={"DELETE"})
     *
     * @Grant(grant="persistence-resident-assessment-assessment", level="DELETE")
     *
     * @param Request $request
     * @param ResidentAssessmentService $residentAssessmentService
     * @return JsonResponse
     * @throws \Doctrine\DBAL\ConnectionException
     * @throws \Throwable
     */
    public function deleteBulkAction(Request $request, ResidentAssessmentService $residentAssessmentService)
    {
        $residentAssessmentService->removeBulk($request->get('ids'));

        return $this->respondSuccess(
            Response::HTTP_NO_CONTENT
        );
    }

    /**
     * @Route("/related/info", name="api_admin_resident_assessment_related_info", methods={"POST"})
     *
     * @param Request $request
     * @param ResidentAssessmentService $residentAssessmentService
     * @return JsonResponse
     * @throws \Doctrine\DBAL\ConnectionException
     * @throws \Throwable
     */
    public function relatedInfoAction(Request $request, ResidentAssessmentService $residentAssessmentService)
    {
        $relatedData = $residentAssessmentService->getRelatedInfo($request->get('ids'));

        return $this->respondSuccess(
            Response::HTTP_OK,
            '',
            [$relatedData]
        );
    }
}
