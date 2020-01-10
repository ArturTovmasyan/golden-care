<?php

namespace App\Api\V1\Common\Controller;

use App\Api\V1\Admin\Service\ReportService;
use App\Api\V1\Common\Service\GrantService;
use App\Api\V1\Common\Service\ImageFilterService;
use App\Api\V1\Common\Service\ProfileService;
use App\Entity\User;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route("/api/v1.0/profile")
 *
 * Class ProfileController
 * @package App\Api\V1\Common\Controller
 */
class ProfileController extends BaseController
{
    /**
     * @Route("/{type}", name="api_profile_me", methods={"GET"}, requirements={"page": "(me|view)"})
     *
     * @param Request $request
     * @param $type
     * @param ProfileService $profileService
     * @param GrantService $grantService
     * @param ReportService $reportService
     * @return JsonResponse
     */
    public function getAction(Request $request, $type, ProfileService $profileService, GrantService $grantService, ReportService $reportService): JsonResponse
    {
        /** @var User $user */
        $user = $this->get('security.token_storage')->getToken()->getUser();

        $permissions = $grantService->getEffectiveGrants($user->getRoleObjects());
        $reportService->addGroupReportPermission($permissions);
        $user->setPermissions($permissions);

        return $this->respondSuccess(
            Response::HTTP_OK,
            '',
            $user,
            ['api_profile_' . $type]
        );
    }

    /**
     * @Route("/edit", name="api_profile_edit", methods={"PUT"})
     *
     * @param Request $request
     * @param ProfileService $profileService
     * @param ImageFilterService $imageFilterService
     * @return JsonResponse
     */
    public function editAction(Request $request, ProfileService $profileService, ImageFilterService $imageFilterService): JsonResponse
    {
        $profileService->setImageFilterService($imageFilterService);

        $profileService->edit(
            $this->get('security.token_storage')->getToken()->getUser(),
            [
                'password' => $request->get('password'),
                'first_name' => $request->get('first_name'),
                'last_name' => $request->get('last_name'),
                'email' => $request->get('email'),
                'phones' => $request->get('phones'),
                'avatar' => $request->get('avatar')
            ]
        );

        return $this->respondSuccess(
            Response::HTTP_CREATED
        );
    }

    /**
     * @Route("/change-password", name="api_profile_change_password", methods={"PUT"})
     *
     * @param Request $request
     * @param ProfileService $profileService
     * @return JsonResponse
     */
    public function changePasswordAction(Request $request, ProfileService $profileService): JsonResponse
    {
        $profileService->changePassword(
            $this->get('security.token_storage')->getToken()->getUser(),
            [
                'password' => $request->get('password'),
                'new_password' => $request->get('new_password'),
                're_new_password' => $request->get('re_new_password')
            ]
        );

        return $this->respondSuccess(
            Response::HTTP_CREATED
        );
    }

    /**
     * @Route("/license/accept", name="api_account_license_accept", methods={"PUT"})
     *
     * @param Request $request
     * @param ProfileService $userService
     * @return JsonResponse
     */
    public function acceptLicenseAction(Request $request, ProfileService $userService): JsonResponse
    {
        $userService->acceptLicense($this->get('security.token_storage')->getToken()->getUser());

        return $this->respondSuccess(
            Response::HTTP_CREATED
        );
    }

    /**
     * @Route("/license/decline", name="api_account_license_decline", methods={"PUT"})
     *
     * @param Request $request
     * @param ProfileService $userService
     * @return JsonResponse
     */
    public function declineLicenseAction(Request $request, ProfileService $userService): JsonResponse
    {
        $userService->declineLicense($this->get('security.token_storage')->getToken()->getUser());

        return $this->respondSuccess(
            Response::HTTP_CREATED
        );
    }
}
