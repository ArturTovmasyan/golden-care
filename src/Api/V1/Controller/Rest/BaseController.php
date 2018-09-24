<?php

namespace App\Api\V1\Controller\Rest;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\JsonResponse;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
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
     * @param null $message
     * @param array $groups
     * @return JsonResponse
     */
    protected function respondSuccess($message = null, $groups = [])
    {
        if ($message) {
            $data = $this->serializer->serialize($message, 'json', ['groups' => []]);

            return new JsonResponse($data, JsonResponse::HTTP_OK, [], true);
        }

        return new JsonResponse();
    }

    /**
     * @param $message
     * @return JsonResponse
     */
    protected function respondError($message)
    {
        return new JsonResponse(
            [
                'error' => $message
            ],
            JsonResponse::HTTP_BAD_REQUEST, [], false
        );
    }

    /**
     * @param Request $request
     */
    protected function normalizeJson(Request $request)
    {
        if ($request->getContentType() === 'application/json' ||
            $request->getContentType() === 'json' && !empty($request->getContent())
        ) {
            $content = $request->getContent();
            $request->request->add(json_decode($content, true));
        }
    }
}
