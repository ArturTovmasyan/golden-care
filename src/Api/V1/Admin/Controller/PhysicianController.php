<?php

namespace App\Api\V1\Admin\Controller;

use App\Api\V1\Common\Controller\BaseController;
use App\Api\V1\Admin\Service\PhysicianService;
use App\Entity\Physician;
use Knp\Bundle\SnappyBundle\Snappy\Response\PdfResponse;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
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
 * @Route("/api/v1.0/admin/physician")
 *
 * @Grant(grant="persistence-common-physician", level="VIEW")
 *
 * Class PhysicianController
 * @package App\Api\V1\Admin\Controller
 */
class PhysicianController extends BaseController
{
    /**
     * @Route("/grid", name="api_admin_physician_grid", methods={"GET"})
     *
     * @param Request $request
     * @param PhysicianService $physicianService
     * @return PdfResponse|JsonResponse
     * @throws \ReflectionException
     */
    public function gridAction(Request $request, PhysicianService $physicianService)
    {
        return $this->respondGrid(
            $request,
            Physician::class,
            'api_admin_physician_grid',
            $physicianService
        );
    }

    /**
     * @Route("/grid", name="api_admin_physician_grid_options", methods={"OPTIONS"})
     *
     * @param Request $request
     * @return \Knp\Bundle\SnappyBundle\Snappy\Response\PdfResponse|JsonResponse
     * @throws \ReflectionException
     */
    public function gridOptionAction(Request $request)
    {
        return $this->getOptionsByGroupName($request, Physician::class, 'api_admin_physician_grid');
    }

    /**
     * @Route("", name="api_admin_physician_list", methods={"GET"})
     *
     * @param Request $request
     * @param PhysicianService $physicianService
     * @return JsonResponse|PdfResponse
     * @throws \ReflectionException
     */
    public function listAction(Request $request, PhysicianService $physicianService)
    {
        return $this->respondList(
            $request,
            Physician::class,
            'api_admin_physician_list',
            $physicianService
        );
    }

    /**
     * @Route("/{id}", name="api_admin_physician_get", requirements={"id"="\d+"}, methods={"GET"})
     *
     * @param Request $request
     * @param $id
     * @param PhysicianService $physicianService
     * @return JsonResponse
     */
    public function getAction(Request $request, $id, PhysicianService $physicianService)
    {
        return $this->respondSuccess(
            Response::HTTP_OK,
            '',
            $physicianService->getById($id),
            ['api_admin_physician_get']
        );
    }

    /**
     * @Route("", name="api_admin_physician_add", methods={"POST"})
     *
     * @Grant(grant="persistence-common-physician", level="ADD")
     *
     * @param Request $request
     * @param PhysicianService $physicianService
     * @return JsonResponse
     * @throws \Doctrine\DBAL\ConnectionException
     * @throws \Throwable
     */
    public function addAction(Request $request, PhysicianService $physicianService)
    {
        $id = $physicianService->add(
            [
                'first_name'        => $request->get('first_name'),
                'middle_name'       => $request->get('middle_name'),
                'last_name'         => $request->get('last_name'),
                'address_1'         => $request->get('address_1'),
                'address_2'         => $request->get('address_2'),
                'email'             => $request->get('email'),
                'website_url'       => $request->get('website_url'),
                'csz_id'            => $request->get('csz_id'),
                'space_id'          => $request->get('space_id'),
                'salutation_id'     => $request->get('salutation_id'),
                'speciality_id'     => $request->get('speciality_id'),
                'phones'            => $request->get('phones')
            ]
        );

        return $this->respondSuccess(
            Response::HTTP_CREATED,
            '',
            [$id]
        );
    }

    /**
     * @Route("/{id}", name="api_admin_physician_edit", methods={"PUT"})
     *
     * @Grant(grant="persistence-common-physician", level="EDIT")
     *
     * @param Request $request
     * @param $id
     * @param PhysicianService $physicianService
     * @return JsonResponse
     * @throws \Doctrine\DBAL\ConnectionException
     */
    public function editAction(Request $request, $id, PhysicianService $physicianService)
    {
        $physicianService->edit(
            $id,
            [
                'first_name'        => $request->get('first_name'),
                'middle_name'       => $request->get('middle_name'),
                'last_name'         => $request->get('last_name'),
                'address_1'         => $request->get('address_1'),
                'address_2'         => $request->get('address_2'),
                'email'             => $request->get('email'),
                'website_url'       => $request->get('website_url'),
                'csz_id'            => $request->get('csz_id'),
                'space_id'          => $request->get('space_id'),
                'salutation_id'     => $request->get('salutation_id'),
                'speciality_id'     => $request->get('speciality_id'),
                'phones'            => $request->get('phones')
            ]
        );

        return $this->respondSuccess(
            Response::HTTP_CREATED
        );
    }

    /**
     * @Route("/{id}", name="api_admin_physician_delete", methods={"DELETE"})
     *
     * @Grant(grant="persistence-common-physician", level="DELETE")
     *
     * @param Request $request
     * @param $id
     * @param PhysicianService $physicianService
     * @return JsonResponse
     * @throws \Doctrine\DBAL\ConnectionException
     * @throws \Throwable
     */
    public function deleteAction(Request $request, $id, PhysicianService $physicianService)
    {
        $physicianService->remove($id);

        return $this->respondSuccess(
            Response::HTTP_NO_CONTENT
        );
    }

    /**
     * @Route("", name="api_admin_physician_delete_bulk", methods={"DELETE"})
     *
     * @Grant(grant="persistence-common-physician", level="DELETE")
     *
     * @param Request $request
     * @param PhysicianService $physicianService
     * @return JsonResponse
     * @throws \Doctrine\DBAL\ConnectionException
     * @throws \Throwable
     */
    public function deleteBulkAction(Request $request, PhysicianService $physicianService)
    {
        $physicianService->removeBulk($request->get('ids'));

        return $this->respondSuccess(
            Response::HTTP_NO_CONTENT
        );
    }

    /**
     * @Route("/related/info", name="api_admin_physician_related_info", methods={"POST"})
     *
     * @param Request $request
     * @param PhysicianService $physicianService
     * @return JsonResponse
     * @throws \Doctrine\DBAL\ConnectionException
     * @throws \Throwable
     */
    public function relatedInfoAction(Request $request, PhysicianService $physicianService)
    {
        $relatedData = $physicianService->getRelatedInfo($request->get('ids'));

        return $this->respondSuccess(
            Response::HTTP_OK,
            '',
            [$relatedData]
        );
    }
}
