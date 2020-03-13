<?php

namespace App\Api\V1\Lead\Controller;

use App\Api\V1\Lead\Service\ActivityService;
use App\Api\V1\Lead\Service\LeadService;
use App\Api\V1\Common\Controller\BaseController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\JsonResponse;

/**
 * @Route("/api/v1.0/lead/zapier/af7dbdcb11823e3d719453b918d6d25e4afd3ece14a6982887a1908ea3c984295a1428765ae9e0f613d5b261235e2331793f00742c99cbc2da44189f13ad99c1")
 *
 * Class ZapierController
 * @package App\Api\V1\Lead\Controller
 */
class ZapierController extends BaseController
{
    /**
     * @Route("", name="api_lead_zapier_add", methods={"POST"})
     *
     * @param Request $request
     * @param LeadService $leadService
     * @param ActivityService $activityService
     * @return JsonResponse
     */
    public function addZapierAction(Request $request, LeadService $leadService, ActivityService $activityService): JsonResponse
    {
        $leadService->setActivityService($activityService);

        $id = $leadService->addZapier(
            [
                'from' => $request->get('from'),
                'subject' => $request->get('subject'),
                'name' => $request->get('name'),
                'email' => $request->get('email'),
                'phone' => $request->get('phone'),
                'message' => $request->get('message'),
                'preferred_date' => $request->get('preferred_date'),
                'base_url' => $request->getSchemeAndHttpHost(),
            ]
        );

        return $this->respondSuccess(
            Response::HTTP_CREATED,
            '',
            ['id' => $id]
        );
    }
}
