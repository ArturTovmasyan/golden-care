<?php
namespace App\Api\V1\Admin\Controller\Lead;

use App\Api\V1\Admin\Service\Lead\TypeOfCareService;
use App\Api\V1\Common\Controller\BaseController;
use App\Entity\Lead\TypeOfCare;
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
 * @Route("/api/v1.0/lead/type/of/care")
 *
 * @Grant(grant="persistence-lead-type_of_care", level="VIEW")
 *
 * Class TypeOfCareController
 * @package App\Api\V1\Admin\Controller
 */
class TypeOfCareController extends BaseController
{
    /**
     * @Route("/grid", name="api_lead_type_of_care", methods={"GET"})
     *
     * @param Request $request
     * @param TypeOfCareService $typeOfCareService
     * @return JsonResponse|PdfResponse
     * @throws \ReflectionException
     */
    public function gridAction(Request $request, TypeOfCareService $typeOfCareService)
    {
        return $this->respondGrid(
            $request,
            TypeOfCare::class,
            'api_lead_type_of_care_grid',
            $typeOfCareService
        );
    }

    /**
     * @Route("/grid", name="api_lead_type_of_care_grid_options", methods={"OPTIONS"})
     *
     * @param Request $request
     * @return JsonResponse
     * @throws \ReflectionException
     */
    public function gridOptionAction(Request $request)
    {
        return $this->getOptionsByGroupName(TypeOfCare::class, 'api_lead_type_of_care_grid');
    }

    /**
     * @Route("", name="api_lead_type_of_care_list", methods={"GET"})
     *
     * @param Request $request
     * @param TypeOfCareService $typeOfCareService
     * @return JsonResponse|PdfResponse
     * @throws \ReflectionException
     */
    public function listAction(Request $request, TypeOfCareService $typeOfCareService)
    {
        return $this->respondList(
            $request,
            TypeOfCare::class,
            'api_lead_type_of_care_list',
            $typeOfCareService
        );
    }

    /**
     * @Route("/{id}", requirements={"id"="\d+"}, name="api_lead_type_of_care_get", methods={"GET"})
     *
     * @param TypeOfCareService $typeOfCareService
     * @param $id
     * @return JsonResponse
     */
    public function getAction(Request $request, $id, TypeOfCareService $typeOfCareService)
    {
        return $this->respondSuccess(
            Response::HTTP_OK,
            '',
            $typeOfCareService->getById($id),
            ['api_lead_type_of_care_get']
        );
    }

    /**
     * @Route("", name="api_lead_type_of_care_add", methods={"POST"})
     *
     * @Grant(grant="persistence-lead-type_of_care", level="ADD")
     *
     * @param Request $request
     * @param TypeOfCareService $typeOfCareService
     * @return JsonResponse
     * @throws \Exception
     */
    public function addAction(Request $request, TypeOfCareService $typeOfCareService)
    {
        $id = $typeOfCareService->add(
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
     * @Route("/{id}", requirements={"id"="\d+"}, name="api_lead_type_of_care_edit", methods={"PUT"})
     *
     * @Grant(grant="persistence-lead-type_of_care", level="EDIT")
     *
     * @param Request $request
     * @param $id
     * @param TypeOfCareService $typeOfCareService
     * @return JsonResponse
     * @throws \Exception
     */
    public function editAction(Request $request, $id, TypeOfCareService $typeOfCareService)
    {
        $typeOfCareService->edit(
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
     * @Route("/{id}", requirements={"id"="\d+"}, name="api_lead_type_of_care_delete", methods={"DELETE"})
     *
     * @Grant(grant="persistence-lead-type_of_care", level="DELETE")
     *
     * @param $id
     * @param TypeOfCareService $typeOfCareService
     * @return JsonResponse
     * @throws \Doctrine\DBAL\ConnectionException
     * @throws \Throwable
     */
    public function deleteAction(Request $request, $id, TypeOfCareService $typeOfCareService)
    {
        $typeOfCareService->remove($id);

        return $this->respondSuccess(
            Response::HTTP_NO_CONTENT
        );
    }

    /**
     * @Route("", name="api_lead_type_of_care_delete_bulk", methods={"DELETE"})
     *
     * @Grant(grant="persistence-lead-type_of_care", level="DELETE")
     *
     * @param Request $request
     * @param TypeOfCareService $typeOfCareService
     * @return JsonResponse
     * @throws \Doctrine\DBAL\ConnectionException
     * @throws \Throwable
     */
    public function deleteBulkAction(Request $request, TypeOfCareService $typeOfCareService)
    {
        $typeOfCareService->removeBulk($request->get('ids'));

        return $this->respondSuccess(
            Response::HTTP_NO_CONTENT
        );
    }

    /**
     * @Route("/related/info", name="api_lead_type_of_care_related_info", methods={"POST"})
     *
     * @param Request $request
     * @param TypeOfCareService $typeOfCareService
     * @return JsonResponse
     * @throws \Doctrine\DBAL\ConnectionException
     * @throws \Throwable
     */
    public function relatedInfoAction(Request $request, TypeOfCareService $typeOfCareService)
    {
        $relatedData = $typeOfCareService->getRelatedInfo($request->get('ids'));

        return $this->respondSuccess(
            Response::HTTP_OK,
            '',
            [$relatedData]
        );
    }
}
