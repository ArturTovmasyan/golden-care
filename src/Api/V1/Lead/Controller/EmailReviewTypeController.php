<?php

namespace App\Api\V1\Lead\Controller;

use App\Annotation\Grant;
use App\Api\V1\Lead\Service\EmailReviewTypeService;
use App\Api\V1\Common\Controller\BaseController;
use App\Entity\Lead\EmailReviewType;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\JsonResponse;
use Knp\Bundle\SnappyBundle\Snappy\Response\PdfResponse;

/**
 * @Route("/api/v1.0/lead/email-review-type")
 *
 * @Grant(grant="persistence-lead-email_review_type", level="VIEW")
 *
 * Class EmailReviewTypeController
 * @package App\Api\V1\Lead\Controller
 */
class EmailReviewTypeController extends BaseController
{
    /**
     * @Route("/grid", name="api_lead_email_review_type", methods={"GET"})
     *
     * @param Request $request
     * @param EmailReviewTypeService $emailReviewTypeService
     * @return JsonResponse
     */
    public function gridAction(Request $request, EmailReviewTypeService $emailReviewTypeService): JsonResponse
    {
        return $this->respondGrid(
            $request,
            EmailReviewType::class,
            'api_lead_email_review_type_grid',
            $emailReviewTypeService
        );
    }

    /**
     * @Route("/grid", name="api_lead_email_review_type_grid_options", methods={"OPTIONS"})
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function gridOptionAction(Request $request): JsonResponse
    {
        return $this->getOptionsByGroupName($request, EmailReviewType::class, 'api_lead_email_review_type_grid');
    }

    /**
     * @Route("", name="api_lead_email_review_type_list", methods={"GET"})
     *
     * @param Request $request
     * @param EmailReviewTypeService $emailReviewTypeService
     * @return PdfResponse|JsonResponse|Response
     */
    public function listAction(Request $request, EmailReviewTypeService $emailReviewTypeService)
    {
        return $this->respondList(
            $request,
            EmailReviewType::class,
            'api_lead_email_review_type_list',
            $emailReviewTypeService
        );
    }

    /**
     * @Route("/{id}", requirements={"id"="\d+"}, name="api_lead_email_review_type_get", methods={"GET"})
     *
     * @param Request $request
     * @param $id
     * @param EmailReviewTypeService $emailReviewTypeService
     * @return JsonResponse
     */
    public function getAction(Request $request, $id, EmailReviewTypeService $emailReviewTypeService): JsonResponse
    {
        return $this->respondSuccess(
            Response::HTTP_OK,
            '',
            $emailReviewTypeService->getById($id),
            ['api_lead_email_review_type_get']
        );
    }

    /**
     * @Route("", name="api_lead_email_review_type_add", methods={"POST"})
     *
     * @Grant(grant="persistence-lead-email_review_type", level="ADD")
     *
     * @param Request $request
     * @param EmailReviewTypeService $emailReviewTypeService
     * @return JsonResponse
     */
    public function addAction(Request $request, EmailReviewTypeService $emailReviewTypeService): JsonResponse
    {
        $id = $emailReviewTypeService->add(
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
     * @Route("/{id}", requirements={"id"="\d+"}, name="api_lead_email_review_type_edit", methods={"PUT"})
     *
     * @Grant(grant="persistence-lead-email_review_type", level="EDIT")
     *
     * @param Request $request
     * @param $id
     * @param EmailReviewTypeService $emailReviewTypeService
     * @return JsonResponse
     */
    public function editAction(Request $request, $id, EmailReviewTypeService $emailReviewTypeService): JsonResponse
    {
        $emailReviewTypeService->edit(
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
     * @Route("/{id}", requirements={"id"="\d+"}, name="api_lead_email_review_type_delete", methods={"DELETE"})
     *
     * @Grant(grant="persistence-lead-email_review_type", level="DELETE")
     *
     * @param Request $request
     * @param $id
     * @param EmailReviewTypeService $emailReviewTypeService
     * @return JsonResponse
     */
    public function deleteAction(Request $request, $id, EmailReviewTypeService $emailReviewTypeService): JsonResponse
    {
        $emailReviewTypeService->remove($id);

        return $this->respondSuccess(
            Response::HTTP_NO_CONTENT
        );
    }

    /**
     * @Route("", name="api_lead_email_review_type_delete_bulk", methods={"DELETE"})
     *
     * @Grant(grant="persistence-lead-email_review_type", level="DELETE")
     *
     * @param Request $request
     * @param EmailReviewTypeService $emailReviewTypeService
     * @return JsonResponse
     */
    public function deleteBulkAction(Request $request, EmailReviewTypeService $emailReviewTypeService): JsonResponse
    {
        $emailReviewTypeService->removeBulk($request->get('ids'));

        return $this->respondSuccess(
            Response::HTTP_NO_CONTENT
        );
    }

    /**
     * @Route("/related/info", name="api_lead_email_review_type_related_info", methods={"POST"})
     *
     * @param Request $request
     * @param EmailReviewTypeService $emailReviewTypeService
     * @return JsonResponse
     */
    public function relatedInfoAction(Request $request, EmailReviewTypeService $emailReviewTypeService): JsonResponse
    {
        $relatedData = $emailReviewTypeService->getRelatedInfo($request->get('ids'));

        return $this->respondSuccess(
            Response::HTTP_OK,
            '',
            [$relatedData]
        );
    }
}
