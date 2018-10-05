<?php

namespace App\Api\V1\Controller\Rest;

use App\Api\V1\Controller\Rest\Exception\UserBlockedException;
use App\Entity\User;
use App\Entity\UserLog;
use App\Model\Log;
use Doctrine\ORM\EntityManager;
use OAuth2\OAuth2;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use FOS\OAuthServerBundle\Controller\TokenController as BaseController;

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
    public function tokenAction(Request $request)
    {
        try {
            /** @var User $user **/
            $user = $this->em->getRepository(User::class)->findOneByUsername($request->get('username'));

            if ($user && $user->getPasswordBlockedAt() instanceof \DateTime) {
                $now  = new \DateTime();
                $diff = $user->getPasswordBlockedAt()->diff($now);

                if ($diff->invert > 0) {
                    $interval = $diff->i . ' minutes';
                    $message  = sprintf('User blocked, please try after %s', $interval);

                    throw new UserBlockedException($message);
                }
            }

            $response = parent::tokenAction($request);

            if ($response->getStatusCode() == Response::HTTP_OK) {
                // clean mistakes
                $user->setPasswordMistakes(0);
                $user->setPasswordBlockedAt(null);
                $this->em->persist($user);

                // create log
                $log = new UserLog();
                $log->setCreatedAt(new \DateTime());
                $log->setUser($user);
                $log->setType(UserLog::LOG_TYPE_AUTHENTICATION);
                $log->setMessage(sprintf("User %s (%s)  logged in.", $user->getFullName(), $user->getUsername()));
                $log->setLevel(Log::LOG_LEVEL_LOW);
                $this->em->persist($log);
            } elseif ($user) {
                $user->incrementPasswordMistakes();

                if ($user->getPasswordMistakes() == User::PASSWORD_MISTAKES_LIMIT) {
                    // block user
                    $blockedTime = new \DateTime();
                    $blockedTime->modify('+15 minutes');
                    $user->setPasswordBlockedAt($blockedTime);

                    // create log
                    $log = new UserLog();
                    $log->setCreatedAt(new \DateTime());
                    $log->setUser($user);
                    $log->setType(UserLog::LOG_TYPE_BLOCK_USER_PASSWORD);
                    $log->setLevel(Log::LOG_LEVEL_HIGH);
                    $log->setMessage(sprintf("User %s (%s) blocked for bad password request.", $user->getFullName(), $user->getUsername()));
                    $this->em->persist($log);
                }

                $this->em->persist($user);
            } else {
                $content = json_decode($response->getContent(), 1);
                $message = '';

                if (!empty($content['error_description'])) {
                    $message = $content['error_description'];
                }

                throw new \Exception($message);
            }

            $this->em->flush();

            return $response;
        } catch (\Throwable $e) {
            return new JsonResponse([
                'status'  => Response::HTTP_UNAUTHORIZED,
                'message' => $e->getMessage()
            ], Response::HTTP_UNAUTHORIZED);
        }
    }
}
