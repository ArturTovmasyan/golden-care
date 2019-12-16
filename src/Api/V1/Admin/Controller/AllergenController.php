<?php
namespace App\Api\V1\Admin\Controller;

use App\Api\V1\Admin\Service\AllergenService;
use App\Api\V1\Common\Controller\BaseController;
use App\Entity\Allergen;
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
 * @Route("/api/v1.0/admin/allergen")
 *
 * @Grant(grant="persistence-common-allergen", level="VIEW")
 *
 * Class AllergenController
 * @package App\Api\V1\Admin\Controller
 */
class AllergenController extends BaseController
{
    /**
     * @Route("/grid", name="api_admin_allergen_grid", methods={"GET"})
     *
     * @param Request $request
     * @param AllergenService $allergenService
     * @return JsonResponse|PdfResponse
     * @throws \ReflectionException
     */
    public function gridAction(Request $request, AllergenService $allergenService)
    {
        return $this->respondGrid(
            $request,
            Allergen::class,
            'api_admin_allergen_grid',
            $allergenService
        );
    }

    /**
     * @Route("/grid", name="api_admin_allergen_grid_options", methods={"OPTIONS"})
     *
     * @param Request $request
     * @return JsonResponse
     * @throws \ReflectionException
     */
    public function gridOptionAction(Request $request)
    {
        return $this->getOptionsByGroupName($request, Allergen::class, 'api_admin_allergen_grid');
    }

    /**
     * @Route("", name="api_admin_allergen_list", methods={"GET"})
     *
     * @param Request $request
     * @param AllergenService $allergenService
     * @return JsonResponse|PdfResponse
     * @throws \ReflectionException
     */
    public function listAction(Request $request, AllergenService $allergenService)
    {
        return $this->respondList(
            $request,
            Allergen::class,
            'api_admin_allergen_list',
            $allergenService
        );
    }

    /**
     * @Route("/{id}", requirements={"id"="\d+"}, name="api_admin_allergen_get", methods={"GET"})
     *
     * @param AllergenService $allergenService
     * @param $id
     * @return JsonResponse
     */
    public function getAction(Request $request, $id, AllergenService $allergenService)
    {
        return $this->respondSuccess(
            Response::HTTP_OK,
            '',
            $allergenService->getById($id),
            ['api_admin_allergen_get']
        );
    }

    /**
     * @Route("", name="api_admin_allergen_add", methods={"POST"})
     *
     * @Grant(grant="persistence-common-allergen", level="ADD")
     *
     * @param Request $request
     * @param AllergenService $allergenService
     * @return JsonResponse
     * @throws \Throwable
     */
    public function addAction(Request $request, AllergenService $allergenService)
    {
        $id = $allergenService->add(
            [
                'title' => $request->get('title'),
                'description' => $request->get('description') ?? '',
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
     * @Route("/{id}", requirements={"id"="\d+"}, name="api_admin_allergen_edit", methods={"PUT"})
     *
     * @Grant(grant="persistence-common-allergen", level="EDIT")
     *
     * @param Request $request
     * @param $id
     * @param AllergenService $allergenService
     * @return JsonResponse
     * @throws \Throwable
     */
    public function editAction(Request $request, $id, AllergenService $allergenService)
    {
        $allergenService->edit(
            $id,
            [
                'title' => $request->get('title'),
                'description' => $request->get('description') ?? '',
                'space_id' => $request->get('space_id')
            ]
        );

        return $this->respondSuccess(
            Response::HTTP_CREATED
        );
    }

    /**
     * @Route("/{id}", requirements={"id"="\d+"}, name="api_admin_allergen_delete", methods={"DELETE"})
     *
     * @Grant(grant="persistence-common-allergen", level="DELETE")
     *
     * @param $id
     * @param AllergenService $allergenService
     * @return JsonResponse
     * @throws \Doctrine\DBAL\ConnectionException
     * @throws \Throwable
     */
    public function deleteAction(Request $request, $id, AllergenService $allergenService)
    {
        $allergenService->remove($id);

        return $this->respondSuccess(
            Response::HTTP_NO_CONTENT
        );
    }

    /**
     * @Route("", name="api_admin_allergen_delete_bulk", methods={"DELETE"})
     *
     * @Grant(grant="persistence-common-allergen", level="DELETE")
     *
     * @param Request $request
     * @param AllergenService $allergenService
     * @return JsonResponse
     * @throws \Doctrine\DBAL\ConnectionException
     * @throws \Throwable
     */
    public function deleteBulkAction(Request $request, AllergenService $allergenService)
    {
        $allergenService->removeBulk($request->get('ids'));

        return $this->respondSuccess(
            Response::HTTP_NO_CONTENT
        );
    }

    /**
     * @Route("/related/info", name="api_admin_allergen_related_info", methods={"POST"})
     *
     * @param Request $request
     * @param AllergenService $allergenService
     * @return JsonResponse
     * @throws \Doctrine\DBAL\ConnectionException
     * @throws \Throwable
     */
    public function relatedInfoAction(Request $request, AllergenService $allergenService)
    {
        $relatedData = $allergenService->getRelatedInfo($request->get('ids'));

        return $this->respondSuccess(
            Response::HTTP_OK,
            '',
            [$relatedData]
        );
    }
}
