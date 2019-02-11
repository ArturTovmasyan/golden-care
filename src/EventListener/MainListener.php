<?php

namespace App\EventListener;

use App\Annotation\Permission;
use App\Api\V1\Common\Model\ResponseCode;
use App\Api\V1\Common\Service\Exception\ValidationException;
use App\Api\V1\Common\Service\GrantService;
use App\Entity\Space;
use App\Entity\User;
use Doctrine\Common\Annotations\Reader;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Event\FilterControllerEvent;
use Symfony\Component\HttpKernel\Event\GetResponseForExceptionEvent;
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
     * ActivityListener constructor.
     * @param EntityManagerInterface $em
     * @param Security $security
     * @param Reader $reader
     */
    public function __construct(EntityManagerInterface $em, Security $security, Reader $reader, GrantService $grantService)
    {
        $this->em           = $em;
        $this->security     = $security;
        $this->reader       = $reader;
        $this->grantService = $grantService;
    }

    /**
     * @param GetResponseForExceptionEvent $event
     */
    public function onKernelException(GetResponseForExceptionEvent $event)
    {
        $exception = $event->getException();

        if ($exception instanceof ValidationException) {
            $response = $this->respondError(
                $exception->getMessage(),
                $exception->getCode(),
                $exception->getErrors()
            );
        } else if ($exception instanceof UnauthorizedHttpException) {
            $response = $this->respondError(
                $exception->getMessage(),
                $exception->getStatusCode()
            );
        } else if ($exception instanceof \ErrorException) {
            $response = $this->respondError(
                sprintf(
                    "%s:%d %s",
                    $exception->getFile(),
                    $exception->getLine(),
                    $exception->getMessage()
                ),
                $exception->getCode()
            );
        } else {
            $response = $this->respondError(
                $exception->getMessage(),
                $exception->getCode()
            );
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
        if ($event->getRequest()->getMethod() != 'GET' &&
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

            if (($user instanceof User) && !($user->isActiveNow())) {
                $this->grantService->setCurrentUser($user);

                $this->checkPermission($event, $user);

                $user->setLastActivityAt(new \DateTime());

                $this->em->persist($user);
                $this->em->flush();
            }
        }
    }

    /**
     * @param FilterControllerEvent $event
     * @param User $user
     * @throws \ReflectionException
     */
    private function checkPermission(FilterControllerEvent $event, User $user)
    {
        $spaceId = $event->getRequest()->attributes->get('_route_params')['spaceId'] ?? 0;

        $controllerName   = $event->getController()[0];
        $reflectionClass  = new \ReflectionClass($controllerName);
        $classAnnotations = $this->reader->getClassAnnotations($reflectionClass);

        $methodName       = $event->getController()[1];
        $reflectionMethod = new \ReflectionMethod($controllerName, $methodName);
        $methodAnnotations = $this->reader->getMethodAnnotations($reflectionMethod);

        $needPermissions       = [];
        $userPermissionsByName = [];

        foreach ($classAnnotations as $classAnnotation) {
            if (!$classAnnotation instanceof Permission) {
                continue;
            }

            $needPermissions = $classAnnotation->getPermissions();
        }

        foreach ($methodAnnotations as $methodAnnotation) {
            if (!$methodAnnotation instanceof Permission) {
                continue;
            }

            $needPermissions = array_merge($needPermissions, $methodAnnotation->getPermissions());
        }

        $needPermissions = array_unique($needPermissions);

        if (!empty($needPermissions)) {
            try {
                if ($spaceId) {
                    $space       = $this->em->getRepository(Space::class)->find($spaceId);
                    $permissions = $this->em->getRepository(\App\Entity\Permission::class)->getUserPermissions($user, $space);
                    $event->getRequest()->attributes->set('space', $space);
                } else {
                    $permissions = $this->em->getRepository(\App\Entity\Permission::class)->getUserPermissions($user);
                }

                $event->getRequest()->attributes->set('userPermissions', $permissions);

                foreach ($permissions as $permission) {
                    /** @var \App\Entity\Permission $permission **/
                    $userPermissionsByName[$permission->getName()] = $permission;
                }

                foreach ($needPermissions as $needPermissionName) {
                    if (isset($userPermissionsByName[$needPermissionName])) {
                        continue;
                    }

                    throw new \Exception();
                }
            } catch (\Throwable $e) {
                $response = new JsonResponse(
                    [
                        'code'  => Response::HTTP_FORBIDDEN,
                        'error' => 'Permission denied for this resource'
                    ],
                    Response::HTTP_FORBIDDEN
                );

                $event->setController(function() use ($response) {
                    return $response;
                });
            }
        }
    }
}
