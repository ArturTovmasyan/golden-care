<?php

namespace App\Api\V1\Admin\Controller;

use App\Api\V1\Common\Controller\BaseController;
use App\Api\V1\Common\Service\GrantService;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

/**
 *
 * @Route("/api/v1.0/admin/grant")
 *
 * Class GrantController
 * @package App\Api\V1\Admin\Controller
 */
class GrantController extends BaseController
{
    /**
     * @Route("", name="api_admin_grant_list", methods={"GET"})
     *
     * @param Request $request
     * @param GrantService $grantService
     * @return JsonResponse
     */
    public function listAction(Request $request, GrantService $grantService)
    {
        return $this->respondSuccess(
            JsonResponse::HTTP_OK,
            '',
            $grantService->getGrants([]),
            'api_admin_grant_list'
        );
    }
}
