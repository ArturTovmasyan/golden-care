<?php
namespace App\Api\V1\Admin\Controller;

use App\Api\V1\Admin\Service\AssessmentFormService;
use App\Api\V1\Common\Controller\BaseController;
use App\Entity\Assessment\CareLevelGroup;
use App\Entity\Assessment\Form;
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
 * @Route("/api/v1.0/admin/assessment/form")
 *
 * @Grant(grant="persistence-assessment-form", level="VIEW")
 *
 * Class AssessmentFormController
 * @package App\Api\V1\Admin\Controller
 */
class AssessmentFormController extends BaseController
{
    /**
     * @Route("/grid", name="api_admin_assessment_form_grid", methods={"GET"})
     *
     * @param Request $request
     * @param AssessmentFormService $formService
     * @return JsonResponse|PdfResponse
     * @throws \ReflectionException
     */
    public function gridAction(Request $request, AssessmentFormService $formService)
    {
        return $this->respondGrid(
            $request,
            Form::class,
            'api_admin_assessment_form_grid',
            $formService
        );
    }

    /**
     * @Route("/grid", name="api_admin_assessment_form_grid_options", methods={"OPTIONS"})
     *
     * @param Request $request
     * @return JsonResponse
     * @throws \ReflectionException
     */
    public function gridOptionAction(Request $request)
    {
        return $this->getOptionsByGroupName($request, Form::class, 'api_admin_assessment_form_grid');
    }

    /**
     * @Route("", name="api_admin_assessment_form_list", methods={"GET"})
     *
     * @param Request $request
     * @param AssessmentFormService $formService
     * @return JsonResponse|PdfResponse
     * @throws \ReflectionException
     */
    public function listAction(Request $request, AssessmentFormService $formService)
    {
        return $this->respondList(
            $request,
            Form::class,
            'api_admin_assessment_form_list',
            $formService
        );
    }

    /**
     * @Route("/{id}", requirements={"id"="\d+"}, name="api_admin_assessment_form_get", methods={"GET"})
     *
     * @param AssessmentFormService $formService
     * @param $id
     * @return JsonResponse
     */
    public function getAction(Request $request, $id, AssessmentFormService $formService)
    {
        return $this->respondSuccess(
            Response::HTTP_OK,
            '',
            $formService->getById($id),
            ['api_admin_assessment_form_get']
        );
    }

    /**
     * @Route("", name="api_admin_assessment_form_add", methods={"POST"})
     *
     * @Grant(grant="persistence-assessment-form", level="ADD")
     *
     * @param Request $request
     * @param AssessmentFormService $formService
     * @return JsonResponse
     * @throws \Throwable
     */
    public function addAction(Request $request, AssessmentFormService $formService)
    {
        $id = $formService->add(
            [
                'title'             => $request->get('title'),
                'space_id'          => $request->get('space_id'),
                'care_level_groups' => $request->get('care_level_groups'),
                'categories'        => $request->get('categories'),
            ]
        );

        return $this->respondSuccess(
            Response::HTTP_CREATED,
            '',
            [$id]
        );
    }

    /**
     * @Route("/{id}", requirements={"id"="\d+"}, name="api_admin_assessment_form_edit", methods={"PUT"})
     *
     * @Grant(grant="persistence-assessment-form", level="EDIT")
     *
     * @param Request $request
     * @param $id
     * @param AssessmentFormService $formService
     * @return JsonResponse
     * @throws \Throwable
     */
    public function editAction(Request $request, $id, AssessmentFormService $formService)
    {
        $formService->edit(
            $id,
            [
                'title'             => $request->get('title'),
                'space_id'          => $request->get('space_id'),
                'care_level_groups' => $request->get('care_level_groups'),
                'categories'        => $request->get('categories'),
            ]
        );

        return $this->respondSuccess(
            Response::HTTP_CREATED
        );
    }

    /**
     * @Route("/{id}", requirements={"id"="\d+"}, name="api_admin_assessment_form_delete", methods={"DELETE"})
     *
     * @Grant(grant="persistence-assessment-form", level="DELETE")
     *
     * @param $id
     * @param AssessmentFormService $formService
     * @return JsonResponse
     * @throws \Doctrine\DBAL\ConnectionException
     * @throws \Throwable
     */
    public function deleteAction(Request $request, $id, AssessmentFormService $formService)
    {
        $formService->remove($id);

        return $this->respondSuccess(
            Response::HTTP_NO_CONTENT
        );
    }

    /**
     * @Route("", name="api_admin_assessment_form_delete_bulk", methods={"DELETE"})
     *
     * @Grant(grant="persistence-assessment-form", level="DELETE")
     *
     * @param Request $request
     * @param AssessmentFormService $formService
     * @return JsonResponse
     * @throws \Doctrine\DBAL\ConnectionException
     * @throws \Throwable
     */
    public function deleteBulkAction(Request $request, AssessmentFormService $formService)
    {
        $formService->removeBulk($request->get('ids'));

        return $this->respondSuccess(
            Response::HTTP_NO_CONTENT
        );
    }

    /**
     * @Route("/related/info", name="api_admin_assessment_form_related_info", methods={"POST"})
     *
     * @param Request $request
     * @param AssessmentFormService $formService
     * @return JsonResponse
     * @throws \Doctrine\DBAL\ConnectionException
     * @throws \Throwable
     */
    public function relatedInfoAction(Request $request, AssessmentFormService $formService)
    {
        $relatedData = $formService->getRelatedInfo($request->get('ids'));

        return $this->respondSuccess(
            Response::HTTP_OK,
            '',
            [$relatedData]
        );
    }
}
