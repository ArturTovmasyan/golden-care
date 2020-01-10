<?php

namespace App\Api\V1\Admin\Controller;

use App\Annotation\Grant;
use App\Api\V1\Admin\Service\CorporateEventUserService;
use App\Api\V1\Common\Controller\BaseController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\JsonResponse;

/**
 * @Route("/api/v1.0/admin/corporate/event/user")
 *
 * @Grant(grant="persistence-corporate-corporate_event_user", level="VIEW")
 *
 * Class CorporateEventUserController
 * @package App\Api\V1\Admin\Controller
 */
class CorporateEventUserController extends BaseController
{
    /**
     * @Route("/related/info", name="api_admin_corporate_event_user_related_info", methods={"POST"})
     *
     * @param Request $request
     * @param CorporateEventUserService $corporateEventUserService
     * @return JsonResponse
     */
    public function relatedInfoAction(Request $request, CorporateEventUserService $corporateEventUserService): JsonResponse
    {
        $relatedData = $corporateEventUserService->getRelatedInfo($request->get('ids'));

        return $this->respondSuccess(
            Response::HTTP_OK,
            '',
            [$relatedData]
        );
    }
}
