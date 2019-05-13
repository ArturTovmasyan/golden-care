<?php
namespace App\Api\V1\Lead\Controller;

use App\Api\V1\Lead\Service\CareTypeService;
use App\Api\V1\Common\Controller\BaseController;
use App\Entity\Lead\CareType;
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
 * @Route("/api/v1.0/lead/care/type")
 *
 * @Grant(grant="persistence-lead-care_type", level="VIEW")
 *
 * Class CareTypeController
 * @package App\Api\V1\Admin\Controller
 */
class CareTypeController extends BaseController
{
    /**
     * @Route("/grid", name="api_lead_care_type", methods={"GET"})
     *
     * @param Request $request
     * @param CareTypeService $careTypeService
     * @return JsonResponse|PdfResponse
     * @throws \ReflectionException
     */
    public function gridAction(Request $request, CareTypeService $careTypeService)
    {
        return $this->respondGrid(
            $request,
            CareType::class,
            'api_lead_care_type_grid',
            $careTypeService
        );
    }

    /**
     * @Route("/grid", name="api_lead_care_type_grid_options", methods={"OPTIONS"})
     *
     * @param Request $request
     * @return JsonResponse
     * @throws \ReflectionException
     */
    public function gridOptionAction(Request $request)
    {
        return $this->getOptionsByGroupName(CareType::class, 'api_lead_care_type_grid');
    }

    /**
     * @Route("", name="api_lead_care_type_list", methods={"GET"})
     *
     * @param Request $request
     * @param CareTypeService $careTypeService
     * @return JsonResponse|PdfResponse
     * @throws \ReflectionException
     */
    public function listAction(Request $request, CareTypeService $careTypeService)
    {
        return $this->respondList(
            $request,
            CareType::class,
            'api_lead_care_type_list',
            $careTypeService
        );
    }

    /**
     * @Route("/{id}", requirements={"id"="\d+"}, name="api_lead_care_type_get", methods={"GET"})
     *
     * @param CareTypeService $careTypeService
     * @param $id
     * @return JsonResponse
     */
    public function getAction(Request $request, $id, CareTypeService $careTypeService)
    {
        return $this->respondSuccess(
            Response::HTTP_OK,
            '',
            $careTypeService->getById($id),
            ['api_lead_care_type_get']
        );
    }

    /**
     * @Route("", name="api_lead_care_type_add", methods={"POST"})
     *
     * @Grant(grant="persistence-lead-care_type", level="ADD")
     *
     * @param Request $request
     * @param CareTypeService $careTypeService
     * @return JsonResponse
     * @throws \Exception
     */
    public function addAction(Request $request, CareTypeService $careTypeService)
    {
        $id = $careTypeService->add(
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
     * @Route("/{id}", requirements={"id"="\d+"}, name="api_lead_care_type_edit", methods={"PUT"})
     *
     * @Grant(grant="persistence-lead-care_type", level="EDIT")
     *
     * @param Request $request
     * @param $id
     * @param CareTypeService $careTypeService
     * @return JsonResponse
     * @throws \Exception
     */
    public function editAction(Request $request, $id, CareTypeService $careTypeService)
    {
        $careTypeService->edit(
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
     * @Route("/{id}", requirements={"id"="\d+"}, name="api_lead_care_type_delete", methods={"DELETE"})
     *
     * @Grant(grant="persistence-lead-care_type", level="DELETE")
     *
     * @param $id
     * @param CareTypeService $careTypeService
     * @return JsonResponse
     * @throws \Doctrine\DBAL\ConnectionException
     * @throws \Throwable
     */
    public function deleteAction(Request $request, $id, CareTypeService $careTypeService)
    {
        $careTypeService->remove($id);

        return $this->respondSuccess(
            Response::HTTP_NO_CONTENT
        );
    }

    /**
     * @Route("", name="api_lead_care_type_delete_bulk", methods={"DELETE"})
     *
     * @Grant(grant="persistence-lead-care_type", level="DELETE")
     *
     * @param Request $request
     * @param CareTypeService $careTypeService
     * @return JsonResponse
     * @throws \Doctrine\DBAL\ConnectionException
     * @throws \Throwable
     */
    public function deleteBulkAction(Request $request, CareTypeService $careTypeService)
    {
        $careTypeService->removeBulk($request->get('ids'));

        return $this->respondSuccess(
            Response::HTTP_NO_CONTENT
        );
    }

    /**
     * @Route("/related/info", name="api_lead_care_type_related_info", methods={"POST"})
     *
     * @param Request $request
     * @param CareTypeService $careTypeService
     * @return JsonResponse
     * @throws \Doctrine\DBAL\ConnectionException
     * @throws \Throwable
     */
    public function relatedInfoAction(Request $request, CareTypeService $careTypeService)
    {
        $relatedData = $careTypeService->getRelatedInfo($request->get('ids'));

        return $this->respondSuccess(
            Response::HTTP_OK,
            '',
            [$relatedData]
        );
    }
}
