<?php
namespace App\Api\V1\Lead\Controller;

use App\Api\V1\Lead\Service\ReferralService;
use App\Api\V1\Common\Controller\BaseController;
use App\Entity\Lead\Referral;
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
 * @Route("/api/v1.0/lead/referral")
 *
 * @Grant(grant="persistence-lead-referral", level="VIEW")
 *
 * Class ReferralController
 * @package App\Api\V1\Admin\Controller
 */
class ReferralController extends BaseController
{
    /**
     * @Route("/grid", name="api_lead_referral", methods={"GET"})
     *
     * @param Request $request
     * @param ReferralService $referralService
     * @return JsonResponse|PdfResponse
     * @throws \ReflectionException
     */
    public function gridAction(Request $request, ReferralService $referralService)
    {
        return $this->respondGrid(
            $request,
            Referral::class,
            'api_lead_referral_grid',
            $referralService,
            [
                'organization_id' => $request->get('organization_id')
            ]
        );
    }

    /**
     * @Route("/grid", name="api_lead_referral_grid_options", methods={"OPTIONS"})
     *
     * @param Request $request
     * @return JsonResponse
     * @throws \ReflectionException
     */
    public function gridOptionAction(Request $request)
    {
        return $this->getOptionsByGroupName($request, Referral::class, 'api_lead_referral_grid');
    }

    /**
     * @Route("", name="api_lead_referral_list", methods={"GET"})
     *
     * @param Request $request
     * @param ReferralService $referralService
     * @return JsonResponse|PdfResponse
     * @throws \ReflectionException
     */
    public function listAction(Request $request, ReferralService $referralService)
    {
        return $this->respondList(
            $request,
            Referral::class,
            'api_lead_referral_list',
            $referralService,
            [
                'organization_id' => $request->get('organization_id')
            ]
        );
    }

    /**
     * @Route("/{id}", requirements={"id"="\d+"}, name="api_lead_referral_get", methods={"GET"})
     *
     * @param ReferralService $referralService
     * @param $id
     * @return JsonResponse
     */
    public function getAction(Request $request, $id, ReferralService $referralService)
    {
        return $this->respondSuccess(
            Response::HTTP_OK,
            '',
            $referralService->getById($id),
            ['api_lead_referral_get']
        );
    }

    /**
     * @Route("", name="api_lead_referral_add", methods={"POST"})
     *
     * @Grant(grant="persistence-lead-referral", level="ADD")
     *
     * @param Request $request
     * @param ReferralService $referralService
     * @return JsonResponse
     * @throws \Throwable
     */
    public function addAction(Request $request, ReferralService $referralService)
    {
        $id = $referralService->add(
            [
                'lead_id' => $request->get('lead_id'),
                'type_id' => $request->get('type_id'),
                'organization_id' => $request->get('organization_id'),
                'contact_id' => $request->get('contact_id'),
                'notes' => $request->get('notes')
            ]
        );

        return $this->respondSuccess(
            Response::HTTP_CREATED,
            '',
            [$id]
        );
    }

    /**
     * @Route("/{id}", requirements={"id"="\d+"}, name="api_lead_referral_edit", methods={"PUT"})
     *
     * @Grant(grant="persistence-lead-referral", level="EDIT")
     *
     * @param Request $request
     * @param $id
     * @param ReferralService $referralService
     * @return JsonResponse
     * @throws \Throwable
     */
    public function editAction(Request $request, $id, ReferralService $referralService)
    {
        $referralService->edit(
            $id,
            [
                'lead_id' => $request->get('lead_id'),
                'type_id' => $request->get('type_id'),
                'organization_id' => $request->get('organization_id'),
                'contact_id' => $request->get('contact_id'),
                'notes' => $request->get('notes')
            ]
        );

        return $this->respondSuccess(
            Response::HTTP_CREATED
        );
    }

    /**
     * @Route("/{id}", requirements={"id"="\d+"}, name="api_lead_referral_delete", methods={"DELETE"})
     *
     * @Grant(grant="persistence-lead-referral", level="DELETE")
     *
     * @param $id
     * @param ReferralService $referralService
     * @return JsonResponse
     * @throws \Doctrine\DBAL\ConnectionException
     * @throws \Throwable
     */
    public function deleteAction(Request $request, $id, ReferralService $referralService)
    {
        $referralService->remove($id);

        return $this->respondSuccess(
            Response::HTTP_NO_CONTENT
        );
    }

    /**
     * @Route("", name="api_lead_referral_delete_bulk", methods={"DELETE"})
     *
     * @Grant(grant="persistence-lead-referral", level="DELETE")
     *
     * @param Request $request
     * @param ReferralService $referralService
     * @return JsonResponse
     * @throws \Doctrine\DBAL\ConnectionException
     * @throws \Throwable
     */
    public function deleteBulkAction(Request $request, ReferralService $referralService)
    {
        $referralService->removeBulk($request->get('ids'));

        return $this->respondSuccess(
            Response::HTTP_NO_CONTENT
        );
    }

    /**
     * @Route("/related/info", name="api_lead_referral_related_info", methods={"POST"})
     *
     * @param Request $request
     * @param ReferralService $referralService
     * @return JsonResponse
     * @throws \Doctrine\DBAL\ConnectionException
     * @throws \Throwable
     */
    public function relatedInfoAction(Request $request, ReferralService $referralService)
    {
        $relatedData = $referralService->getRelatedInfo($request->get('ids'));

        return $this->respondSuccess(
            Response::HTTP_OK,
            '',
            [$relatedData]
        );
    }
}
