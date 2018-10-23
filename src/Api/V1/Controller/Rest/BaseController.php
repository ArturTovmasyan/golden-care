<?php

namespace App\Api\V1\Controller\Rest;

use JMS\Serializer\SerializationContext;
use JMS\Serializer\Serializer;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\JsonResponse;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;
use Symfony\Component\Serializer\SerializerInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;

class BaseController extends Controller
{
    /** @var SerializerInterface */
    protected $serializer;

    /** @var EntityManagerInterface */
    protected $em;

    /** @var ValidatorInterface */
    protected $validator;

    /** @var UserPasswordEncoderInterface */
    protected $encoder;

    /**
     * BaseController constructor.
     * @param SerializerInterface $serializer
     * @param EntityManagerInterface $em
     * @param ValidatorInterface $validator
     * @param UserPasswordEncoderInterface $encoder
     */
    public function __construct(
        SerializerInterface $serializer,
        EntityManagerInterface $em,
        ValidatorInterface $validator,
        UserPasswordEncoderInterface $encoder
    ) {
        $this->serializer = $serializer;
        $this->em         = $em;
        $this->validator  = $validator;
        $this->encoder    = $encoder;
    }

    /**
     * @param string $message
     * @param int $httpStatus
     * @param array $data
     * @param array $groups
     * @param array $headers
     * @return JsonResponse
     */
    protected function respondSuccess($message = '', $httpStatus = Response::HTTP_OK, $data = [], $groups = [], $headers = [])
    {
        /** @var Serializer $serializer */
        $serializer = $this->get('jms_serializer');

        $responseData = [];

        if (!empty($message)) {
            $responseData['message'] = $message;
        }

        if (!empty($data)) {
            $responseData['data'] = $data;

            if (empty($groups)) {
                $responseData = $serializer->serialize($responseData, 'json');
            } else {
                $responseData = $serializer->serialize($responseData, 'json', SerializationContext::create()->setGroups($groups));
            }

            return new JsonResponse($responseData, $httpStatus, $headers, true);
        }

        return new JsonResponse($responseData, $httpStatus, $headers, false);
    }

    /**
     * @param $message
     * @param int $httpStatus
     * @param array $data
     * @param array $headers
     * @return JsonResponse
     */
    protected function respondError($message, $httpStatus = Response::HTTP_BAD_REQUEST, $data = [], $headers = [])
    {
        $responseData = [
            'error' => $message
        ];

        if (!empty($data)) {
            $responseData['details'] = $data;
        }

        return new JsonResponse($responseData, $httpStatus ?: Response::HTTP_INTERNAL_SERVER_ERROR, $headers, false);
    }

    /**
     * @param Request $request
     */
    protected function normalizeJson(Request &$request)
    {
        if (($request->getContentType() === 'application/json' || $request->getContentType() === 'json') &&
            !empty($request->getContent())
        ) {
            $content = $request->getContent();
            $request->request->add(json_decode($content, true));
        }
    }
}
