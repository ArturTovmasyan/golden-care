<?php

namespace App\Api\V1\Common\Controller;

use App\Api\V1\Common\Service\GrantService;
use App\Api\V1\Common\Service\Helper\UserAvatarHelper;
use App\Api\V1\Common\Service\ImageFilterService;
use App\Api\V1\Common\Service\ProfileService;
use App\Entity\User;
use App\Entity\UserImage;
use App\Repository\UserImageRepository;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

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
 * @Route("/api/v1.0/profile")
 *
 * Class ProfileController
 * @package App\Api\V1\Common\Controller
 */
class ProfileController extends BaseController
{
    /**
     * @api {get} /api/v1.0/profile/me My Profile
     * @apiVersion 1.0.0
     * @apiName My Profile
     * @apiGroup Profile
     * @apiPermission none
     * @apiDescription This function is used to get user profile
     *
     * @apiHeader {String} Content-Type  application/json
     * @apiHeader {String} Authorization Bearer ACCESS_TOKEN
     *
     * @apiSuccess {Int}     id                The identifier of the user
     * @apiSuccess {String}  first_name        The First Name of the user
     * @apiSuccess {String}  last_name         The Last Name of the user
     * @apiSuccess {String}  email             The email of the user
     * @apiSuccess {Boolean} enabled           The enabled status of the user
     * @apiSuccess {Boolean} completed         The profile completed status of the user
     * @apiSuccess {String}  last_activity_at  The last activity date of the user
     *
     * @apiSuccessExample {json} Sample Response:
     *     HTTP/1.1 200 OK
     *     {
     *          "data": {
     *               "id": 1,
     *               "first_name": "Joe",
     *               "last_name": "Cole",
     *               "username": "joe",
     *               "email": "joe.cole@gmail.com",
     *               "enabled": true,
     *               "completed": true,
     *               "last_activity_at": "2018-10-22T17:31:48+04:00",
     *               "space_user_roles": [
     *                   {
     *                      "space": {
     *                          "id": 1,
     *                          "name": "First"
     *                      },
     *                      "role": {
     *                          "id": 1,
     *                          "name": "Admin Management",
     *                          "permissions": [
     *                              {
     *                                  "id": 1,
     *                                  "name": "PERMISSION_ROLE"
     *                              },
     *                              {
     *                                  "id": 2,
     *                                   "name": "PERMISSION_USER"
     *                              }
     *                          ]
     *                      }
     *                  }
     *              ]
     *          }
     *     }
     *
     * @Route("/{type}", name="api_profile_me", methods={"GET"}, requirements={"page": "(me|view)"})
     *
     * @var Request $request
     * @return JsonResponse
     */
    public function getAction(Request $request, $type, ProfileService $profileService, GrantService $grantService)
    {
        /** @var User $user */
        $user = $this->get('security.token_storage')->getToken()->getUser();
        $user->setPermissions($grantService->getEffectiveGrants($user->getRoleObjects()));

        return $this->respondSuccess(
            Response::HTTP_OK,
            '',
            $user,
            ['api_profile_'.$type]
        );
    }

    /**
     * @api {put} /api/v1.0/profile/edit Edit Profile
     * @apiVersion 1.0.0
     * @apiName Edit Profile
     * @apiGroup Profile
     * @apiPermission none
     * @apiDescription This function is used to edit user profile
     *
     * @apiHeader {String} Content-Type  application/json
     * @apiHeader {String} Authorization Bearer ACCESS_TOKEN
     *
     * @apiParam {String}  first_name        The First Name of the user
     * @apiParam {String}  last_name         The Last Name of the user
     * @apiParam {String}  phone             The phone number of the user
     *
     * @apiParamExample {json} Request-Example:
     *     {
     *         "first_name": "Joe",
     *         "last_name": "Cole",
     *         "phone": "+37400000000"
     *     }
     * @apiSuccessExample {json} Sample Response:
     *     HTTP/1.1 201 Created
     *     {}
     * @apiErrorExample {json} Error-Response:
     *     HTTP/1.1 400 Bad Request
     *     {
     *          "code": 610,
     *          "error": "Validation error",
     *          "details": {
     *              "first_name": "This value should not be blank."
     *          }
     *     }
     *
     * @Route("/edit", name="api_profile_edit", methods={"PUT"})
     *
     * @param Request $request
     * @param ProfileService $profileService
     * @param UserAvatarHelper $userAvatarHelper
     * @param ImageFilterService $imageFilterService
     * @return JsonResponse
     * @throws \Doctrine\DBAL\ConnectionException
     */
    public function editAction(Request $request, ProfileService $profileService, UserAvatarHelper $userAvatarHelper, ImageFilterService $imageFilterService)
    {
        $profileService->setImageFilterService($imageFilterService);

        $profileService->edit(
            $this->get('security.token_storage')->getToken()->getUser(),
            [
                'password'    => $request->get('password'),
                'first_name'  => $request->get('first_name'),
                'last_name'   => $request->get('last_name'),
                'email'       => $request->get('email'),
                'phones'      => $request->get('phones'),
                'avatar'      => $request->get('avatar')
            ]
        );

        return $this->respondSuccess(
            Response::HTTP_CREATED
        );
    }

    /**
     * @api {put} /api/v1.0/profile/change-password Change Password
     * @apiVersion 1.0.0
     * @apiName Change Password
     * @apiGroup Profile
     * @apiPermission none
     * @apiDescription This function is used to change password
     *
     * @apiHeader {String} Content-Type  application/json
     * @apiHeader {String} Authorization Bearer ACCESS_TOKEN
     *
     * @apiParam {String} password         The old password of the user
     * @apiParam {String} new_password     The new password of the user
     * @apiParam {String} re_new_password  The confirmation of the user password
     *
     * @apiParamExample {json} Request-Example:
     *     {
     *         "password": "OLD_PASSWORD",
     *         "new_password": "NEW_PASSWORD",
     *         "re_new_password": "NEW_PASSWORD"
     *     }
     * @apiSuccessExample {json} Sample Response:
     *     HTTP/1.1 201 Created
     *     {}
     * @apiErrorExample {json} Error-Response:
     *     HTTP/1.1 400 Bad Request
     *     {
     *          "code": 619,
     *          "error": "Validation error",
     *          "details": {
     *              "confirmPassword": "This value should be equal to password",
     *              "plainPassword": "This value should not be equal to old password"
     *          }
     *     }
     *
     * @Route("/change-password", name="api_profile_change_password", methods={"PUT"})
     *
     * @param Request $request
     * @param ProfileService $profileService
     * @return JsonResponse
     * @throws \Doctrine\DBAL\ConnectionException
     */
    public function changePasswordAction(Request $request, ProfileService $profileService)
    {
        $profileService->changePassword(
            $this->get('security.token_storage')->getToken()->getUser(),
            [
                'password'        => $request->get('password'),
                'new_password'    => $request->get('new_password'),
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
     * @throws \Doctrine\DBAL\ConnectionException
     */
    public function acceptLicenseAction(Request $request, ProfileService $userService)
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
     * @throws \Doctrine\DBAL\ConnectionException
     */
    public function declineLicenseAction(Request $request, ProfileService $userService)
    {
        $userService->declineLicense($this->get('security.token_storage')->getToken()->getUser());

        return $this->respondSuccess(
            Response::HTTP_CREATED
        );
    }
}
