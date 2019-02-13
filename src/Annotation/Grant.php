<?php

namespace App\Annotation;

use Doctrine\Common\Annotations\Reader;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpKernel\Event\FilterControllerEvent;
use App\Entity\User;
use App\Api\V1\Common\Service\GrantService;

/**
 * @Annotation
 * @Target({"CLASS", "METHOD"})
 */
class Grant
{
    /**
     * @Required
     * @var string
     */
    public $grant;

    /**
     * @Required
     * @var int
     */
    public $level;


    /**
     * @param array $options
     */
    public function __construct($options = [])
    {
        $this->grant = $options['grant'];
        $this->level = \App\Model\Grant::str2level($options['level']);
    }

    /**
     * @return string
     */
    public function getGrant(): ?string
    {
        return $this->grant;
    }

    /**
     * @param string $grant
     */
    public function setGrant(?string $grant): void
    {
        $this->grant = $grant;
    }

    /**
     * @return int
     */
    public function getLevel(): ?int
    {
        return $this->level;
    }

    /**
     * @param int $level
     */
    public function setLevel(?int $level): void
    {
        $this->level = $level;
    }

    /**
     * @param FilterControllerEvent $event
     * @param User $user
     * @param GrantService $grantService
     * @throws \ReflectionException
     */
    public static function checkPermission(FilterControllerEvent $event, User $user, Reader $reader, GrantService $grantService)
    {
        $controllerName = $event->getController()[0];
        $reflectionClass = new \ReflectionClass($controllerName);
        $classAnnotations = $reader->getClassAnnotations($reflectionClass);

        $methodName = $event->getController()[1];
        $reflectionMethod = new \ReflectionMethod($controllerName, $methodName);
        $methodAnnotations = $reader->getMethodAnnotations($reflectionMethod);

        $grant = "";
        $level = \App\Model\Grant::$LEVEL_NONE;

        foreach ($classAnnotations as $classAnnotation) {
            if ($classAnnotation instanceof Grant) {
                $grant = $classAnnotation->getGrant();
                $level = $classAnnotation->getLevel();
                break;
            }
        }

        foreach ($methodAnnotations as $methodAnnotation) {
            if ($methodAnnotation instanceof Grant) {
                if ($methodAnnotation->getGrant() === $grant && $methodAnnotation->getLevel() > $level) {
                    $level = $methodAnnotation->getLevel();
                } else {
                    $grant = $methodAnnotation->getGrant();
                    $level = $methodAnnotation->getLevel();
                }
                break;
            }
        }

        $effectiveGrants = $grantService->getEffectiveGrants($user->getRoleObjects());

        if (!(array_key_exists($grant, $effectiveGrants) && $effectiveGrants[$grant]['level'] >= $level)) {
            $response = new JsonResponse(
                [
                    'code' => JsonResponse::HTTP_FORBIDDEN,
                    'error' => 'Access denied for this resource'
                ],
                JsonResponse::HTTP_FORBIDDEN
            );
            $event->setController(function () use ($response) {
                return $response;
            });
        }
    }
}