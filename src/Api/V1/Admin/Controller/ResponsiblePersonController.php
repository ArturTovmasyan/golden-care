<?php
namespace App\Api\V1\Admin\Controller;

use App\Api\V1\Admin\Service\ResponsiblePersonService;
use App\Api\V1\Common\Controller\BaseController;
use App\Entity\ResponsiblePerson;
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
 * @Route("/api/v1.0/admin/responsible/person")
 *
 * @Grant(grant="persistence-common-responsible_person", level="VIEW")
 *
 * Class ResponsiblePersonController
 * @package App\Api\V1\Admin\Controller
 */
class ResponsiblePersonController extends BaseController
{
    /**
     * @Route("/grid", name="api_admin_responsible_person_grid", methods={"GET"})
     *
     * @param Request $request
     * @param ResponsiblePersonService $responsiblePersonService
     * @return JsonResponse|PdfResponse
     * @throws \ReflectionException
     */
    public function gridAction(Request $request, ResponsiblePersonService $responsiblePersonService)
    {
        return $this->respondGrid(
            $request,
            ResponsiblePerson::class,
            'api_admin_responsible_person_grid',
            $responsiblePersonService
        );
    }

    /**
     * @Route("/grid", name="api_admin_responsible_person_grid_options", methods={"OPTIONS"})
     *
     * @param Request $request
     * @return JsonResponse
     * @throws \ReflectionException
     */
    public function gridOptionAction(Request $request)
    {
        return $this->getOptionsByGroupName($request, ResponsiblePerson::class, 'api_admin_responsible_person_grid');
    }

    /**
     * @Route("", name="api_admin_responsible_person_list", methods={"GET"})
     *
     * @param Request $request
     * @param ResponsiblePersonService $responsiblePersonService
     * @return JsonResponse|PdfResponse
     * @throws \ReflectionException
     */
    public function listAction(Request $request, ResponsiblePersonService $responsiblePersonService)
    {
        return $this->respondList(
            $request,
            ResponsiblePerson::class,
            'api_admin_responsible_person_list',
            $responsiblePersonService
        );
    }

    /**
     * @Route("/{id}", requirements={"id"="\d+"}, name="api_admin_responsible_person_get", methods={"GET"})
     *
     * @param ResponsiblePersonService $responsiblePersonService
     * @param $id
     * @return JsonResponse
     */
    public function getAction(Request $request, $id, ResponsiblePersonService $responsiblePersonService)
    {
        return $this->respondSuccess(
            Response::HTTP_OK,
            '',
            $responsiblePersonService->getById($id),
            ['api_admin_responsible_person_get']
        );
    }

    /**
     * @Route("", name="api_admin_responsible_person_add", methods={"POST"})
     *
     * @Grant(grant="persistence-common-responsible_person", level="ADD")
     *
     * @param Request $request
     * @param ResponsiblePersonService $responsiblePersonService
     * @return JsonResponse
     * @throws \Throwable
     */
    public function addAction(Request $request, ResponsiblePersonService $responsiblePersonService)
    {
        $id = $responsiblePersonService->add(
            [
                'first_name'        => $request->get('first_name'),
                'middle_name'       => $request->get('middle_name'),
                'last_name'         => $request->get('last_name'),
                'address_1'         => $request->get('address_1'),
                'address_2'         => $request->get('address_2'),
                'email'             => $request->get('email'),
                'csz_id'            => $request->get('csz_id'),
                'space_id'          => $request->get('space_id'),
                'salutation_id'     => $request->get('salutation_id'),
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
     * @Route("/{id}", requirements={"id"="\d+"}, name="api_admin_responsible_person_edit", methods={"PUT"})
     *
     * @Grant(grant="persistence-common-responsible_person", level="EDIT")
     *
     * @param Request $request
     * @param $id
     * @param ResponsiblePersonService $responsiblePersonService
     * @return JsonResponse
     * @throws \Throwable
     */
    public function editAction(Request $request, $id, ResponsiblePersonService $responsiblePersonService)
    {
        $responsiblePersonService->edit(
            $id,
            [
                'first_name'        => $request->get('first_name'),
                'middle_name'       => $request->get('middle_name'),
                'last_name'         => $request->get('last_name'),
                'address_1'         => $request->get('address_1'),
                'address_2'         => $request->get('address_2'),
                'email'             => $request->get('email'),
                'csz_id'            => $request->get('csz_id'),
                'space_id'          => $request->get('space_id'),
                'salutation_id'     => $request->get('salutation_id'),
                'phones'            => $request->get('phones')
            ]
        );

        return $this->respondSuccess(
            Response::HTTP_CREATED
        );
    }

    /**
     * @Route("/{id}", requirements={"id"="\d+"}, name="api_admin_responsible_person_delete", methods={"DELETE"})
     *
     * @Grant(grant="persistence-common-responsible_person", level="DELETE")
     *
     * @param $id
     * @param ResponsiblePersonService $responsiblePersonService
     * @return JsonResponse
     * @throws \Doctrine\DBAL\ConnectionException
     * @throws \Throwable
     */
    public function deleteAction(Request $request, $id, ResponsiblePersonService $responsiblePersonService)
    {
        $responsiblePersonService->remove($id);

        return $this->respondSuccess(
            Response::HTTP_NO_CONTENT
        );
    }

    /**
     * @Route("", name="api_admin_responsible_person_delete_bulk", methods={"DELETE"})
     *
     * @Grant(grant="persistence-common-responsible_person", level="DELETE")
     *
     * @param Request $request
     * @param ResponsiblePersonService $responsiblePersonService
     * @return JsonResponse
     * @throws \Doctrine\DBAL\ConnectionException
     * @throws \Throwable
     */
    public function deleteBulkAction(Request $request, ResponsiblePersonService $responsiblePersonService)
    {
        $responsiblePersonService->removeBulk($request->get('ids'));

        return $this->respondSuccess(
            Response::HTTP_NO_CONTENT
        );
    }

    /**
     * @Route("/related/info", name="api_admin_responsible_person_related_info", methods={"POST"})
     *
     * @param Request $request
     * @param ResponsiblePersonService $responsiblePersonService
     * @return JsonResponse
     * @throws \Doctrine\DBAL\ConnectionException
     * @throws \Throwable
     */
    public function relatedInfoAction(Request $request, ResponsiblePersonService $responsiblePersonService)
    {
        $relatedData = $responsiblePersonService->getRelatedInfo($request->get('ids'));

        return $this->respondSuccess(
            Response::HTTP_OK,
            '',
            [$relatedData]
        );
    }
}
