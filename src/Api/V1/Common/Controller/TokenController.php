<?php

namespace App\Api\V1\Common\Controller;

use App\Api\V1\Common\Service\Exception\UserBlockedException;
use App\Entity\LoginAttempt;
use App\Entity\User;
use App\Entity\UserLog;
use App\Model\Log;
use App\Repository\LoginAttemptRepository;
use Doctrine\ORM\EntityManager;
use OAuth2\OAuth2;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use FOS\OAuthServerBundle\Controller\TokenController as BaseController;
use Symfony\Component\HttpKernel\Exception\UnauthorizedHttpException;

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
 * Class TokenController
 * @package App\Api\V1\Controller\Rest
 */
class TokenController extends BaseController
{
    /**
     * @var EntityManager
     */
    private $em;

    /**
     * TokenController constructor.
     * @param OAuth2 $server
     * @param EntityManager $entityManager
     */
    public function __construct(OAuth2 $server, EntityManager $entityManager)
    {
        parent::__construct($server);
        $this->em          = $entityManager;
    }

    /**
     * @api {post} /oauth/v2/token Authorization
     * @apiVersion 1.0.0
     * @apiName Authorization
     * @apiGroup Common
     * @apiPermission none
     * @apiDescription This function is used to authorize user
     *
     * @apiHeader {String} Content-Type  application/json
     *
     * @apiParam {String} username      The unique username of the user
     * @apiParam {String} password      The password of the user
     * @apiParam {Int}    client_id     The client identifier of the user
     * @apiParam {String} client_secret The client secret of the user
     * @apiParam {String} grant_type    The grand_type for authorization
     *
     * @apiParamExample {json} Request-Example:
     *     {
     *         "username": "test",
     *         "password": "CLIENT_PASSWORD",
     *         "client_id": "CLIENT_ID",
     *         "client_secret": "CLIENT_SECRET",
     *         "grant_type": "password"
     *     }
     *
     * @apiSuccess {String} access_token   The access token of client
     * @apiSuccess {Int}    expires_in     Expired date for access token
     * @apiSuccess {String} token_type     The token type
     * @apiSuccess {String} scope          The available scopes
     * @apiSuccess {String} refresh_token  The refresh token of client
     *
     * @apiSuccessExample {json} Sample Response:
     *     HTTP/1.1 200 OK
     *     {
     *         "access_token": "YmE5YjY4MWZhNWU2MzZkM2Q2MDRhYzYVjZDZiOTMzOGFkN2ZdsmZDc4NzEzODJmZTgxNjM4NDM4MWQxZDUwOQ",
     *         "expires_in": 604800,
     *         "token_type": "bearer",
     *         "scope": "user",
     *         "refresh_token": "OTQ0NjDhmMzNiNzUwdsdsmExNWY0MDcxYmJiNmM1ZWYdsxYTBhNmZiZGYzMmRhODdkNDRhNWE3OWU1MzNhOA"
     *     }
     * @apiErrorExample {json} Error-Response:
     *     HTTP/1.1 400 Bad Request
     *     {
     *          "code": 401,
     *          "error": "Invalid username and password combination"
     *     }
     *
     * @param Request $request
     * @return Response
     * @throws \Doctrine\ORM\ORMException
     */
    public function tokenAction(Request $request)
    {
        $response = parent::tokenAction($request);

        /**
         * @var User $user
         */

        if ($response->getStatusCode() == Response::HTTP_OK) {
            $user = $this->em->getRepository(User::class)->findUserByUsername($request->get('username'));

            if ($user) {
                $attempts = $this->em->getRepository(LoginAttempt::class)
                    ->findBy(['login'=>$request->get('username')]);

                foreach ($attempts as $attempt) {
                    $this->em->remove($attempt);
                }

                // create log
                $log = new UserLog();
                $log->setCreatedAt(new \DateTime());
                $log->setUser($user);
                $log->setType(UserLog::LOG_TYPE_AUTHENTICATION);
                $log->setMessage(sprintf("User %s (%s)  logged in.", $user->getFullName(), $user->getUsername()));
                $log->setLevel(Log::LOG_LEVEL_LOW);
                $this->em->persist($log);

                $this->em->flush();
            }
        } else {
            $username = $request->get('username');

            /**
             * @var LoginAttemptRepository $loginAttemptRepository
             */
            $loginAttemptRepository = $this->em->getRepository(LoginAttempt::class);
            $attemptsCount = $loginAttemptRepository->getAttemptsCount($username, $this->getClientIp());

            if ($attemptsCount >= LoginAttempt::PASSWORD_ATTEMPT_LIMIT) {
                throw new UserBlockedException([$username, $this->getClientIp()]);
            }

            // create attempt
            $loginAttempt = new LoginAttempt();
            $loginAttempt->setCreatedAt(new \DateTime());
            $loginAttempt->setLogin($username);
            $loginAttempt->setIp($this->getClientIp());
            $this->em->persist($loginAttempt);
            $this->em->flush();

            $content = json_decode($response->getContent(), 1);
            $message = '';

            if (!empty($content['error_description'])) {
                $message = $content['error_description'];
            }

            throw new UnauthorizedHttpException("token", $message);
        }

        return $response;
    }

    /**
     * @return array|false|string
     */
    private function getClientIp()
    {
        if (getenv('HTTP_CLIENT_IP')) {
            $ip = getenv('HTTP_CLIENT_IP');
        } else if (getenv('HTTP_X_FORWARDED_FOR')) {
            $ip = getenv('HTTP_X_FORWARDED_FOR');
        } else if (getenv('HTTP_X_FORWARDED')) {
            $ip = getenv('HTTP_X_FORWARDED');
        } else if (getenv('HTTP_FORWARDED_FOR')) {
            $ip = getenv('HTTP_FORWARDED_FOR');
        } else if (getenv('HTTP_FORWARDED')) {
            $ip = getenv('HTTP_FORWARDED');
        } else if (getenv('REMOTE_ADDR')) {
            $ip = getenv('REMOTE_ADDR');
        } else {
            $ip = 'UNKNOWN';
        }

        return $ip;
    }
}
