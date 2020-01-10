<?php

namespace App\Api\V1\Common\Controller;

use App\Api\V1\Common\Service\Exception\UserBlockedException;
use App\Entity\LoginAttempt;
use App\Entity\User;
use App\Entity\UserLog;
use App\Model\Log;
use App\Repository\LoginAttemptRepository;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManager;
use OAuth2\OAuth2;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use FOS\OAuthServerBundle\Controller\TokenController as BaseController;
use Symfony\Component\HttpKernel\Exception\UnauthorizedHttpException;

/**
 * Class TokenController
 * @package App\Api\V1\Common\Controller
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
        $this->em = $entityManager;
    }

    /**
     * @param Request $request
     * @return Response
     */
    public function tokenAction(Request $request): Response
    {
        $response = parent::tokenAction($request);

        $username = $request->get('username');

        if ($username !== null) {
            if ($response->getStatusCode() === Response::HTTP_OK) {
                /** @var UserRepository $repo */
                $repo = $this->em->getRepository(User::class);
                /** @var User $user */
                $user = $repo->findUserByUsername($username);

                if ($user) {
                    $attempts = $this->em->getRepository(LoginAttempt::class)
                        ->findBy(['login' => $username]);

                    foreach ($attempts as $attempt) {
                        $this->em->remove($attempt);
                    }

                    // create log
                    $log = new UserLog();
                    $log->setCreatedAt(new \DateTime());
                    $log->setUser($user);
                    $log->setType(UserLog::LOG_TYPE_AUTHENTICATION);
                    $log->setMessage(sprintf('User %s (%s)  logged in.', $user->getFullName(), $user->getUsername()));
                    $log->setLevel(Log::LOG_LEVEL_LOW);
                    $this->em->persist($log);

                    $this->em->flush();
                }
            } else {
                /** @var LoginAttemptRepository $loginAttemptRepository */
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

                throw new UnauthorizedHttpException('token', $message);
            }
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
