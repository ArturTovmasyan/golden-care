<?php

namespace App\EventListener;

use App\Annotation\Grant;
use App\Api\V1\Common\Model\ResponseCode;
use App\Api\V1\Common\Service\Exception\ApiException;
use App\Api\V1\Common\Service\Exception\ValidationException;
use App\Api\V1\Common\Service\GrantService;
use App\Entity\User;
use App\Util\Mailer;
use Doctrine\Common\Annotations\Reader;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Event\FilterControllerEvent;
use Symfony\Component\HttpKernel\Event\GetResponseForExceptionEvent;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Symfony\Component\HttpKernel\Exception\UnauthorizedHttpException;
use Symfony\Component\HttpKernel\HttpKernel;
use Symfony\Component\Security\Core\Security;

class MainListener
{
    /**
     * @var EntityManagerInterface
     */
    private $em;

    /**
     * @var Security
     */
    private $security;

    /**
     * @var Reader
     */
    private $reader;

    /**
     * @var GrantService
     */
    private $grantService;

    /**
     * @var Mailer
     */
    protected $mailer;

    /**
     * MainListener constructor.
     * @param EntityManagerInterface $em
     * @param Security $security
     * @param Reader $reader
     * @param GrantService $grantService
     * @param Mailer $mailer
     */
    public function __construct(EntityManagerInterface $em, Security $security, Reader $reader, GrantService $grantService, Mailer $mailer)
    {
        $this->em           = $em;
        $this->security     = $security;
        $this->reader       = $reader;
        $this->grantService = $grantService;
        $this->mailer       = $mailer;
    }

    /**
     * @param GetResponseForExceptionEvent $event
     */
    public function onKernelException(GetResponseForExceptionEvent $event)
    {
        $sendEmail = false;
        $body = [];

        $exception = $event->getException();

        if ($exception instanceof ValidationException) {
            $response = $this->respondError(
                $exception->getMessage(),
                $exception->getCode(),
                $exception->getErrors()
            );
        } else if ($exception instanceof ApiException) {
            $response = $this->respondError(
                $exception->getMessage(),
                $exception->getCode()
            );
        } else if ($exception instanceof UnauthorizedHttpException) {
            $response = $this->respondError(
                $exception->getMessage(),
                $exception->getStatusCode()
            );
        }  else if ($exception instanceof AccessDeniedHttpException) {
            $response = $this->respondError(
                'Access denied to resource.',
                $exception->getStatusCode()
            );
        } else if ($exception instanceof \ErrorException) {
            $response = $this->respondError(
                sprintf(
                    '%s:%d %s',
                    $exception->getFile(),
                    $exception->getLine(),
                    $exception->getMessage()
                ),
                JsonResponse::HTTP_BAD_REQUEST
            );

            $sendEmail = true;

            $body = [
                'code' => $exception->getCode(),
                'message' => $exception->getMessage(),
                'file' => $exception->getFile(),
                'line' => $exception->getLine()
            ];
        } else {
            $response = $this->respondError(
                $exception->getMessage(),
                $exception->getCode()
            );

            $sendEmail = true;

            $body = [
                'code' => $exception->getCode(),
                'message' => $exception->getMessage()
            ];
        }

        // send email when handled customer exception
        if ($sendEmail) {
            $customer = $this->grantService->getCurrentSpace() ? $this->grantService->getCurrentSpace()->getName() : $event->getRequest()->getHost();
            $subject = '[SeniorCare] Exception from customer <'.$customer.'>';

            $user = $user = $this->security->getToken()->getUser();

            $this->mailer->sendHandledCustomerException($user, $customer, $subject, $body);
        }

        $event->setResponse($response);
    }

    /**
     * @param $message
     * @param int $code
     * @param array $data
     * @param array $headers
     * @return JsonResponse
     */
    private function respondError($message, $code = Response::HTTP_BAD_REQUEST, $data = [], $headers = [])
    {
        $responseCode    = $code ?: Response::HTTP_BAD_REQUEST;
        $responseMessage = ResponseCode::$titles[$responseCode]['message']  ?? $message;
        $headerCode      = ResponseCode::$titles[$responseCode]['httpCode'] ?? $responseCode;

        $responseData = [
            'code'  => $responseCode,
            'error' => $responseMessage
        ];

        if (!empty($data)) {
            $responseData['details'] = $data;
        }

        return new JsonResponse($responseData, $headerCode, $headers, false);
    }

    /**
     * @param FilterControllerEvent $event
     * @throws \ReflectionException
     * @throws \Exception
     */
    public function onCoreController(FilterControllerEvent $event)
    {
        // Check that the current request is a "MASTER_REQUEST"
        // Ignore any sub-request
        if ($event->getRequestType() !== HttpKernel::MASTER_REQUEST) {
            return;
        }

        // normalize json
        if ($event->getRequest()->getMethod() !== 'GET' &&
            ($event->getRequest()->getContentType() === 'application/json' || $event->getRequest()->getContentType() === 'json') &&
            !empty($event->getRequest()->getContent())
        ) {
            $content = $event->getRequest()->getContent();
            $event->getRequest()->request->add(json_decode($content, true));
        }


        // Check token authentication availability
        if ($this->security->getToken()) {
            /** @var User $user **/
            $user = $this->security->getToken()->getUser();

            if ($user instanceof User) {
                $this->grantService->setCurrentUser($user);
                $user->setLastActivityAt(new \DateTime());
                $this->em->persist($user);
                $this->em->flush();

                Grant::checkPermission($event, $user, $this->reader, $this->grantService);
            }
        }
    }

}
