<?php

namespace App\Api\V1\Lead\Controller;

use App\Annotation\Grant;
use App\Api\V1\Lead\Service\LeadService;
use App\Api\V1\Lead\Service\WebEmailService;
use App\Api\V1\Common\Controller\BaseController;
use App\Entity\Lead\WebEmail;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\JsonResponse;
use Knp\Bundle\SnappyBundle\Snappy\Response\PdfResponse;

/**
 * @Route("/api/v1.0/lead/web-email")
 *
 * @Grant(grant="persistence-lead-web_email", level="VIEW")
 *
 * Class WebEmailController
 * @package App\Api\V1\Lead\Controller
 */
class WebEmailController extends BaseController
{
    /**
     * @Route("/grid", name="api_lead_web_email", methods={"GET"})
     *
     * @param Request $request
     * @param WebEmailService $webEmailService
     * @return JsonResponse
     */
    public function gridAction(Request $request, WebEmailService $webEmailService): JsonResponse
    {
        return $this->respondGrid(
            $request,
            WebEmail::class,
            'api_lead_web_email_grid',
            $webEmailService
        );
    }

    /**
     * @Route("/grid", name="api_lead_web_email_grid_options", methods={"OPTIONS"})
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function gridOptionAction(Request $request): JsonResponse
    {
        return $this->getOptionsByGroupName($request, WebEmail::class, 'api_lead_web_email_grid');
    }

    /**
     * @Route("", name="api_lead_web_email_list", methods={"GET"})
     *
     * @param Request $request
     * @param WebEmailService $webEmailService
     * @return PdfResponse|JsonResponse|Response
     */
    public function listAction(Request $request, WebEmailService $webEmailService)
    {
        return $this->respondList(
            $request,
            WebEmail::class,
            'api_lead_web_email_list',
            $webEmailService
        );
    }

    /**
     * @Route("/{id}", requirements={"id"="\d+"}, name="api_lead_web_email_get", methods={"GET"})
     *
     * @param Request $request
     * @param $id
     * @param WebEmailService $webEmailService
     * @return JsonResponse
     */
    public function getAction(Request $request, $id, WebEmailService $webEmailService): JsonResponse
    {
        $gridData = $this->respondQueryBuilderResult(
            $request,
            WebEmail::class,
            'api_lead_web_email_grid',
            $webEmailService,
            []
        );

        return $this->respondSuccess(
            Response::HTTP_OK,
            '',
            $webEmailService->getById($id, $gridData),
            ['api_lead_web_email_get']
        );
    }

    /**
     * @Route("/{id}", requirements={"id"="\d+"}, name="api_lead_web_email_edit", methods={"PUT"})
     *
     * @Grant(grant="persistence-lead-web_email", level="EDIT")
     *
     * @param Request $request
     * @param $id
     * @param WebEmailService $webEmailService
     * @param LeadService $leadService
     * @return JsonResponse
     */
    public function editAction(Request $request, $id, WebEmailService $webEmailService, LeadService $leadService): JsonResponse
    {
        $webEmailService->edit(
            $id,
            $leadService,
            [
                'date' => $request->get('date'),
                'facility_id' => $request->get('facility_id'),
                'email_review_type_id' => $request->get('email_review_type_id'),
                'subject' => $request->get('subject'),
                'name' => $request->get('name'),
                'email' => $request->get('email'),
                'phone' => $request->get('phone'),
                'message' => $request->get('message'),
                'space_id' => $request->get('space_id'),
                'base_url' => $request->getSchemeAndHttpHost(),
            ]
        );

        return $this->respondSuccess(
            Response::HTTP_CREATED
        );
    }

    /**
     * @Route("/{id}", requirements={"id"="\d+"}, name="api_lead_web_email_delete", methods={"DELETE"})
     *
     * @Grant(grant="persistence-lead-web_email", level="DELETE")
     *
     * @param Request $request
     * @param $id
     * @param WebEmailService $webEmailService
     * @return JsonResponse
     */
    public function deleteAction(Request $request, $id, WebEmailService $webEmailService): JsonResponse
    {
        $webEmailService->remove($id);

        return $this->respondSuccess(
            Response::HTTP_NO_CONTENT
        );
    }

    /**
     * @Route("", name="api_lead_web_email_delete_bulk", methods={"DELETE"})
     *
     * @Grant(grant="persistence-lead-web_email", level="DELETE")
     *
     * @param Request $request
     * @param WebEmailService $webEmailService
     * @return JsonResponse
     */
    public function deleteBulkAction(Request $request, WebEmailService $webEmailService): JsonResponse
    {
        $webEmailService->removeBulk($request->get('ids'));

        return $this->respondSuccess(
            Response::HTTP_NO_CONTENT
        );
    }

    /**
     * @Route("/related/info", name="api_lead_web_email_related_info", methods={"POST"})
     *
     * @param Request $request
     * @param WebEmailService $webEmailService
     * @return JsonResponse
     */
    public function relatedInfoAction(Request $request, WebEmailService $webEmailService): JsonResponse
    {
        $relatedData = $webEmailService->getRelatedInfo($request->get('ids'));

        return $this->respondSuccess(
            Response::HTTP_OK,
            '',
            [$relatedData]
        );
    }
}
