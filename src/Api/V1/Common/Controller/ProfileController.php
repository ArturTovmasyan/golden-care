<?php

namespace App\Api\V1\Common\Controller;

use App\Api\V1\Admin\Service\ReportService;
use App\Api\V1\Admin\Service\UserService;
use App\Api\V1\Common\Service\GrantService;
use App\Api\V1\Common\Service\ImageFilterService;
use App\Api\V1\Common\Service\ProfileService;
use App\Api\V1\Common\Service\S3Service;
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
     * @param S3Service $s3Service
     * @return JsonResponse
     */
    public function getAction(Request $request, $type, ProfileService $profileService, GrantService $grantService, ReportService $reportService, S3Service $s3Service): JsonResponse
    {
        /** @var User $user */
        $user = $this->get('security.token_storage')->getToken()->getUser();

        $permissions = $grantService->getEffectiveGrants($user->getRoleObjects());
        $reportService->addGroupReportPermission($permissions);
        $user->setPermissions($permissions);

        $user->setDownloadUrl(null);
        if ($user !== null && $user->getImage() !== null) {
            $cmd = $s3Service->getS3Client()->getCommand('GetObject', [
                'Bucket' => getenv('AWS_BUCKET'),
                'Key' => $user->getImage()->getType() . '/' . $user->getImage()->getS3Id(),
            ]);
            $result = $s3Service->getS3Client()->createPresignedRequest($cmd, '+20 minutes');

            $user->setDownloadUrl((string)$result->getUri());
        } 

        return $this->respondSuccess(
            Response::HTTP_OK,
            '',
            $user,
            ['api_profile_' . $type]
        );
    }

    /**
     * @Route("/download/{id}", requirements={"id"="\d+"}, name="api_profile_user_image_download", methods={"GET"})
     *
     * @param Request $request
     * @param $id
     * @param ProfileService $profileService
     * @param UserService $userService
     * @return Response
     */
    public function downloadAction(Request $request, $id, ProfileService $profileService, UserService $userService): Response
    {
        $isMe = $request->query->has('me') ? true : false;

        $data = $profileService->downloadFile($userService, $id, $isMe);

        return $this->respondResource($data[0], $data[1], $data[2]);
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
