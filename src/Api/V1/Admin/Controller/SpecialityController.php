<?php
namespace App\Api\V1\Admin\Controller;

use App\Api\V1\Admin\Service\SpecialityService;
use App\Api\V1\Common\Controller\BaseController;
use App\Entity\Speciality;
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
 * @Route("/api/v1.0/admin/speciality")
 *
 * @Grant(grant="persistence-common-speciality", level="VIEW")
 *
 * Class SpecialityController
 * @package App\Api\V1\Admin\Controller
 */
class SpecialityController extends BaseController
{
    /**
     * @Route("/grid", name="api_admin_speciality_grid", methods={"GET"})
     *
     * @param Request $request
     * @param SpecialityService $specialityService
     * @return JsonResponse|PdfResponse
     * @throws \ReflectionException
     */
    public function gridAction(Request $request, SpecialityService $specialityService)
    {
        return $this->respondGrid(
            $request,
            Speciality::class,
            'api_admin_speciality_grid',
            $specialityService
        );
    }

    /**
     * @Route("/grid", name="api_admin_speciality_grid_options", methods={"OPTIONS"})
     *
     * @param Request $request
     * @return JsonResponse
     * @throws \ReflectionException
     */
    public function gridOptionAction(Request $request)
    {
        return $this->getOptionsByGroupName($request, Speciality::class, 'api_admin_speciality_grid');
    }

    /**
     * @Route("", name="api_admin_speciality_list", methods={"GET"})
     *
     * @param Request $request
     * @param SpecialityService $specialityService
     * @return JsonResponse|PdfResponse
     * @throws \ReflectionException
     */
    public function listAction(Request $request, SpecialityService $specialityService)
    {
        return $this->respondList(
            $request,
            Speciality::class,
            'api_admin_speciality_list',
            $specialityService,
            ['space_id' => $request->get('space_id')]
        );
    }

    /**
     * @Route("/{id}", requirements={"id"="\d+"}, name="api_admin_speciality_get", methods={"GET"})
     *
     * @param SpecialityService $specialityService
     * @param $id
     * @return JsonResponse
     */
    public function getAction(Request $request, $id, SpecialityService $specialityService)
    {
        return $this->respondSuccess(
            Response::HTTP_OK,
            '',
            $specialityService->getById($id),
            ['api_admin_speciality_get']
        );
    }

    /**
     * @Route("", name="api_admin_speciality_add", methods={"POST"})
     *
     * @Grant(grant="persistence-common-speciality", level="ADD")
     *
     * @param Request $request
     * @param SpecialityService $specialityService
     * @return JsonResponse
     * @throws \Throwable
     */
    public function addAction(Request $request, SpecialityService $specialityService)
    {
        $id = $specialityService->add(
            [
                'space_id' => $request->get('space_id'),
                'title'    => $request->get('title')
            ]
        );

        return $this->respondSuccess(
            Response::HTTP_CREATED,
            '',
            [$id]
        );
    }

    /**
     * @Route("/{id}", requirements={"id"="\d+"}, name="api_admin_speciality_edit", methods={"PUT"})
     *
     * @Grant(grant="persistence-common-speciality", level="EDIT")
     *
     * @param Request $request
     * @param $id
     * @param SpecialityService $specialityService
     * @return JsonResponse
     * @throws \Throwable
     */
    public function editAction(Request $request, $id, SpecialityService $specialityService)
    {
        $specialityService->edit(
            $id,
            [
                'space_id' => $request->get('space_id'),
                'title'    => $request->get('title')
            ]
        );

        return $this->respondSuccess(
            Response::HTTP_CREATED
        );
    }

    /**
     * @Route("/{id}", requirements={"id"="\d+"}, name="api_admin_speciality_delete", methods={"DELETE"})
     *
     * @Grant(grant="persistence-common-speciality", level="DELETE")
     *
     * @param $id
     * @param SpecialityService $specialityService
     * @return JsonResponse
     * @throws \Doctrine\DBAL\ConnectionException
     * @throws \Throwable
     */
    public function deleteAction(Request $request, $id, SpecialityService $specialityService)
    {
        $specialityService->remove($id);

        return $this->respondSuccess(
            Response::HTTP_NO_CONTENT
        );
    }

    /**
     * @Route("", name="api_admin_speciality_delete_bulk", methods={"DELETE"})
     *
     * @Grant(grant="persistence-common-speciality", level="DELETE")
     *
     * @param Request $request
     * @param SpecialityService $specialityService
     * @return JsonResponse
     * @throws \Doctrine\DBAL\ConnectionException
     * @throws \Throwable
     */
    public function deleteBulkAction(Request $request, SpecialityService $specialityService)
    {
        $specialityService->removeBulk($request->get('ids'));

        return $this->respondSuccess(
            Response::HTTP_NO_CONTENT
        );
    }

    /**
     * @Route("/related/info", name="api_admin_speciality_related_info", methods={"POST"})
     *
     * @param Request $request
     * @param SpecialityService $specialityService
     * @return JsonResponse
     * @throws \Doctrine\DBAL\ConnectionException
     * @throws \Throwable
     */
    public function relatedInfoAction(Request $request, SpecialityService $specialityService)
    {
        $relatedData = $specialityService->getRelatedInfo($request->get('ids'));

        return $this->respondSuccess(
            Response::HTTP_OK,
            '',
            [$relatedData]
        );
    }
}
