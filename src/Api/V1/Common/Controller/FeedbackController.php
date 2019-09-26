<?php

namespace App\Api\V1\Common\Controller;

use App\Api\V1\Common\Service\Exception\FeedbackUnknownException;
use App\Api\V1\Common\Service\GrantService;
use App\Entity\User;
use http\Exception\InvalidArgumentException;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route("/api/v1.0/feedback")
 *
 * Class FeedbackController
 * @package App\Api\V1\Common\Controller
 */
class FeedbackController extends BaseController
{
    /**
     * @Route("", name="api_feedback_add", methods={"POST"})
     *
     * @var Request $request
     * @return JsonResponse
     */
    public function addAction(Request $request)
    {
        /** @var User $user */
        $user = $this->get('security.token_storage')->getToken()->getUser();

        $subject = $request->get('subject');
        $message = $request->get('message');

        $post_data = http_build_query([
            'domain' => $request->getHost(),
            'username' => $user->getUsername(),
            'email' => $user->getEmail(),
            'full_name' => $user->getFullName(),
            'subject' => $subject,
            'message' => $message,
            'date' => (new \DateTime())->format('Y-m-d H:i:s')
        ]);

        $opts = ['http' => [
            'method' => 'POST',
            'header' => implode("\r\n", [
                'Content-Type: application/x-www-form-urlencoded',
                'Content-Length: '. strlen($post_data)
            ]),
            'content' => $post_data
        ]];

        $result = file_get_contents(
//            'http://seniorcare-mc.local/backend/api/5766d45bdba1152105abfd9662e55140/feedback',
            'https://console.seniorcaresw.com/backend/api/5766d45bdba1152105abfd9662e55140/feedback',
            false,
            stream_context_create($opts)
        );

        if($result !== false) {
            $result = json_decode($result, true);
        } else {
            throw new FeedbackUnknownException();
        }

        return $this->respondSuccess(
            Response::HTTP_OK,
            '',
            $result
        );
    }
}
