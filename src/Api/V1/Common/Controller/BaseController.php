<?php

namespace App\Api\V1\Common\Controller;

use App\Annotation\Grid;
use App\Api\V1\Common\Model\ResponseCode;
use App\Entity\Role;
use JMS\Serializer\SerializationContext;
use JMS\Serializer\Serializer;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Doctrine\Common\Annotations\Reader;
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

    /** @var Reader */
    protected $reader;

    /**
     * BaseController constructor.
     * @param SerializerInterface $serializer
     * @param EntityManagerInterface $em
     * @param ValidatorInterface $validator
     * @param UserPasswordEncoderInterface $encoder
     * @param Reader $reader
     */
    public function __construct(
        SerializerInterface $serializer,
        EntityManagerInterface $em,
        ValidatorInterface $validator,
        UserPasswordEncoderInterface $encoder,
        Reader $reader
    ) {
        $this->serializer = $serializer;
        $this->em         = $em;
        $this->validator  = $validator;
        $this->encoder    = $encoder;
        $this->reader     = $reader;
    }

    /**
     * @param string $message
     * @param int $httpStatus
     * @param array $data
     * @param array $groups
     * @param array $headers
     * @return JsonResponse
     */
    protected function respondSuccess($httpStatus = Response::HTTP_OK, $message = '', $data = [], $groups = [], $headers = [])
    {
        /** @var Serializer $serializer */
        $serializer = $this->get('jms_serializer');

        $responseData = [];

        if (!empty($message)) {
            $responseData['message'] = $message;
        } elseif (isset(ResponseCode::$titles[$httpStatus])) {
            $responseData['code']    = $httpStatus;
            $responseData['message'] = ResponseCode::$titles[$httpStatus]['message'];
            $httpStatus              = ResponseCode::$titles[$httpStatus]['httpCode'];
        }

        if (!empty($data)) {
            $responseData['data'] = $data;
        }

        if (empty($groups)) {
            $responseData = $serializer->serialize($responseData, 'json');
        } else {
            $responseData = $serializer->serialize($responseData, 'json', SerializationContext::create()->setGroups($groups));
        }

        return new JsonResponse($responseData, $httpStatus, $headers, true);
    }

    /**
     * @param string $entityName
     * @param string $groupName
     * @param int $totalCount
     * @return JsonResponse
     * @throws \ReflectionException
     */
    protected function getOptionsByGroupName(string $entityName, string $groupName, int $totalCount)
    {
        /**
         * @var Grid $annotation
         */
        $reflectionProperty = new \ReflectionClass($entityName);
        $annotation         = $this->reader->getClassAnnotation($reflectionProperty, Grid::class);

        return new JsonResponse(
            json_encode(
                [
                    'options' => $annotation->getGroup($groupName),
                    'total'   => $totalCount
                ]
            ),
            Response::HTTP_OK,
            [],
            true
        );
    }
}
