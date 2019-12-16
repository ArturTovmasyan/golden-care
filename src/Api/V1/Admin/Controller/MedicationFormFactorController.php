<?php
namespace App\Api\V1\Admin\Controller;

use App\Api\V1\Admin\Service\MedicationFormFactorService;
use App\Api\V1\Common\Controller\BaseController;
use App\Entity\MedicationFormFactor;
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
 * @Route("/api/v1.0/admin/medication/form/factor")
 *
 * @Grant(grant="persistence-common-medication_form_factor", level="VIEW")
 *
 * Class MedicationFormFactorController
 * @package App\Api\V1\Admin\Controller
 */
class MedicationFormFactorController extends BaseController
{
    /**
     * @Route("/grid", name="api_admin_medication_form_factor_grid", methods={"GET"})
     *
     * @param Request $request
     * @param MedicationFormFactorService $medicationFormFactorService
     * @return JsonResponse|PdfResponse
     * @throws \ReflectionException
     */
    public function gridAction(Request $request, MedicationFormFactorService $medicationFormFactorService)
    {
        return $this->respondGrid(
            $request,
            MedicationFormFactor::class,
            'api_admin_medication_form_factor_grid',
            $medicationFormFactorService
        );
    }

    /**
     * @Route("/grid", name="api_admin_medication_form_factor_grid_options", methods={"OPTIONS"})
     *
     * @param Request $request
     * @return JsonResponse
     * @throws \ReflectionException
     */
    public function gridOptionAction(Request $request)
    {
        return $this->getOptionsByGroupName($request, MedicationFormFactor::class, 'api_admin_medication_form_factor_grid');
    }

    /**
     * @Route("", name="api_admin_medication_form_factor_list", methods={"GET"})
     *
     * @param Request $request
     * @param MedicationFormFactorService $medicationFormFactorService
     * @return JsonResponse|PdfResponse
     * @throws \ReflectionException
     */
    public function listAction(Request $request, MedicationFormFactorService $medicationFormFactorService)
    {
        return $this->respondList(
            $request,
            MedicationFormFactor::class,
            'api_admin_medication_form_factor_list',
            $medicationFormFactorService
        );
    }

    /**
     * @Route("/{id}", requirements={"id"="\d+"}, name="api_admin_medication_form_factor_get", methods={"GET"})
     *
     * @param MedicationFormFactorService $medicationFormFactorService
     * @param $id
     * @return JsonResponse
     */
    public function getAction(Request $request, $id, MedicationFormFactorService $medicationFormFactorService)
    {
        return $this->respondSuccess(
            Response::HTTP_OK,
            '',
            $medicationFormFactorService->getById($id),
            ['api_admin_medication_form_factor_get']
        );
    }

    /**
     * @Route("", name="api_admin_medication_form_factor_add", methods={"POST"})
     *
     * @Grant(grant="persistence-common-medication_form_factor", level="ADD")
     *
     * @param Request $request
     * @param MedicationFormFactorService $medicationFormFactorService
     * @return JsonResponse
     * @throws \Throwable
     */
    public function addAction(Request $request, MedicationFormFactorService $medicationFormFactorService)
    {
        $id = $medicationFormFactorService->add(
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
     * @Route("/{id}", requirements={"id"="\d+"}, name="api_admin_medication_form_factor_edit", methods={"PUT"})
     *
     * @Grant(grant="persistence-common-medication_form_factor", level="EDIT")
     *
     * @param Request $request
     * @param $id
     * @param MedicationFormFactorService $medicationFormFactorService
     * @return JsonResponse
     * @throws \Throwable
     */
    public function editAction(Request $request, $id, MedicationFormFactorService $medicationFormFactorService)
    {
        $medicationFormFactorService->edit(
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
     * @Route("/{id}", requirements={"id"="\d+"}, name="api_admin_medication_form_factor_delete", methods={"DELETE"})
     *
     * @Grant(grant="persistence-common-medication_form_factor", level="DELETE")
     *
     * @param $id
     * @param MedicationFormFactorService $medicationFormFactorService
     * @return JsonResponse
     * @throws \Doctrine\DBAL\ConnectionException
     * @throws \Throwable
     */
    public function deleteAction(Request $request, $id, MedicationFormFactorService $medicationFormFactorService)
    {
        $medicationFormFactorService->remove($id);

        return $this->respondSuccess(
            Response::HTTP_NO_CONTENT
        );
    }

    /**
     * @Route("", name="api_admin_medication_form_factor_delete_bulk", methods={"DELETE"})
     *
     * @Grant(grant="persistence-common-medication_form_factor", level="DELETE")
     *
     * @param Request $request
     * @param MedicationFormFactorService $medicationFormFactorService
     * @return JsonResponse
     * @throws \Doctrine\DBAL\ConnectionException
     * @throws \Throwable
     */
    public function deleteBulkAction(Request $request, MedicationFormFactorService $medicationFormFactorService)
    {
        $medicationFormFactorService->removeBulk($request->get('ids'));

        return $this->respondSuccess(
            Response::HTTP_NO_CONTENT
        );
    }

    /**
     * @Route("/related/info", name="api_admin_medication_form_factor_related_info", methods={"POST"})
     *
     * @param Request $request
     * @param MedicationFormFactorService $medicationFormFactorService
     * @return JsonResponse
     * @throws \Doctrine\DBAL\ConnectionException
     * @throws \Throwable
     */
    public function relatedInfoAction(Request $request, MedicationFormFactorService $medicationFormFactorService)
    {
        $relatedData = $medicationFormFactorService->getRelatedInfo($request->get('ids'));

        return $this->respondSuccess(
            Response::HTTP_OK,
            '',
            [$relatedData]
        );
    }
}
