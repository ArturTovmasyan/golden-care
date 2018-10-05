<?php
namespace App\Api\V1\Controller\Rest;

use App\Api\V1\Service\Exception\ValidationException;
use App\Api\V1\Service\UserService;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Symfony\Component\HttpFoundation\JsonResponse;

/**
 * Class SecurityController
 * @package App\Api\V1\Controller\Rest
 *
 * @Route("/api/v1.0/security")
 */
class SecurityController extends BaseController
{
    /**
     * This function is used to login user
     *
     * @Method("POST")
     * @Route("/signup", name="security_signup")
     *
     * @param Request $request
     * @param UserService $userService
     * @return JsonResponse
     */
    public function signupAction(Request $request, UserService $userService)
    {
        try {
            $this->normalizeJson($request);
            $userService->signup(
                [
                    'firstName'  => $request->get('firstName'),
                    'lastName'   => $request->get('lastName'),
                    'email'      => $request->get('email'),
                    'password'   => $request->get('password'),
                    'rePassword' => $request->get('rePassword')
                ]
            );
            $response = $this->respondSuccess(
                'Please waiting to approval.',
                Response::HTTP_CREATED
            );
        } catch (ValidationException $e) {
            $response = $this->respondError($e->getMessage(), $e->getCode(), $e->getErrors());
        } catch (\Throwable $e) {
            $response = $this->respondError($e->getMessage(), $e->getCode());
        }

        return $response;
    }

    /**
     * This function is used to change password
     *
     * @Method("PUT")
     * @Route("/change-password", name="security_change_password")
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
                    'newPassword'     => $request->get('newPassword'),
                    'confirmPassword' => $request->get('confirmPassword')
                ]
            );
            $response = $this->respondSuccess(
                '',
                Response::HTTP_CREATED
            );
        } catch (ValidationException $e) {
            $response = $this->respondError($e->getMessage(), $e->getCode(), $e->getErrors());
        } catch (\Throwable $e) {
            $response = $this->respondError($e->getMessage(), $e->getCode());
        }

        return $response;
    }

    /**
     * This function is used to forgot password
     *
     *
     * @Method("POST")
     * @Route("/forgot-password", name="security_forgot_password")
     *
     * @param Request $request
     * @param UserService $userService
     * @return JsonResponse
     */
    public function forgotPasswordAction(Request $request, UserService $userService)
    {
        try {
            $this->normalizeJson($request);

            $userService->forgotPassword(
                $request->get('email'),
                $request->getSchemeAndHttpHost()
            );
            $response = $this->respondSuccess(
                'Password recovery link sent, please check email.',
                Response::HTTP_CREATED
            );
        } catch (ValidationException $e) {
            $response = $this->respondError($e->getMessage(), $e->getCode(), $e->getErrors());
        } catch (\Throwable $e) {
            $response = $this->respondError($e->getMessage(), $e->getCode());
        }

        return $response;
    }

    /**
     * This function is used to confirm password with hash
     *
     *
     * @Method("PUT")
     * @Route("/confirm-password", name="security_confirm_password")
     *
     * @param Request $request
     * @param UserService $userService
     * @return JsonResponse
     */
    public function confirmPasswordAction(Request $request, UserService $userService)
    {
        try {
            $this->normalizeJson($request);
            $userService->confirmPassword(
                [
                    'email'      => $request->get('hash'),
                    'password'   => $request->get('newPassword'),
                    'rePassword' => $request->get('confirmPassword')
                ]
            );
            $response = $this->respondSuccess(
                'New password is not confirmed',
                Response::HTTP_CREATED
            );
        } catch (ValidationException $e) {
            $response = $this->respondError($e->getMessage(), $e->getCode(), $e->getErrors());
        } catch (\Throwable $e) {
            $response = $this->respondError($e->getMessage(), $e->getCode());
        }

        return $response;
    }
}