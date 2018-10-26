<?php
namespace App\Api\V1\Dashboard\Controller;

use App\Api\V1\Dashboard\Service\UserService;
use App\Api\V1\Common\Controller\BaseController;
use App\Api\V1\Common\Service\Exception\SpaceNotFoundException;
use App\Api\V1\Common\Model\ResponseCode;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use App\Annotation\Permission;
use Symfony\Component\HttpFoundation\JsonResponse;

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
 * @Route("/api/v1.0/dashboard/user")
 *
 * Class AccountController
 * @package App\Api\V1\Dashboard\Controller
 */
class UserController extends BaseController
{
    /**
     * @api {put} /api/v1.0/dashboard/user/change-password Change Password
     * @apiVersion 1.0.0
     * @apiName Change Password
     * @apiGroup Dashboard User
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
     *          "code": 619
     *          "error": "New password must be different from last password"
     *     }
     *
     * @Route("/change-password", name="api_dashboard_user_change_password", methods={"PUT"})
     *
     * @param Request $request
     * @param UserService $userService
     * @return JsonResponse
     */
    public function changePasswordAction(Request $request, UserService $userService)
    {
        try {
            $this->normalizeJson($request);

            $userService->changePassword(
                $this->get('security.token_storage')->getToken()->getUser(),
                [
                    'password'        => $request->get('password'),
                    'new_password'    => $request->get('new_password'),
                    're_new_password' => $request->get('re_new_password')
                ]
            );
            $response = $this->respondSuccess(
                Response::HTTP_CREATED
            );
        } catch (\Throwable $e) {
            $response = $this->respondError($e->getMessage(), $e->getCode());
        }

        return $response;
    }
}