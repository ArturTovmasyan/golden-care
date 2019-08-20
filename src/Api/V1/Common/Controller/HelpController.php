<?php

namespace App\Api\V1\Common\Controller;

use App\Api\V1\Common\Service\GrantService;
use App\Entity\User;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route("/api/v1.0/help")
 *
 * Class HelpController
 * @package App\Api\V1\Common\Controller
 */
class HelpController extends BaseController
{
    /**
     * @Route("", name="api_help_get", methods={"GET"})
     *
     * @var Request $request
     * @return JsonResponse
     */
    public function getAction(Request $request, GrantService $grantService)
    {
        /** @var User $user */
        $user = $this->get('security.token_storage')->getToken()->getUser();

        $permissions = $grantService->getEffectiveGrants($user->getRoleObjects());

        $permissions = array_keys(array_filter($permissions, function($value) {
            return $value['enabled'] === true && (array_key_exists('level', $value) ? $value['level'] > 0 : true);
        }));

        $post_data = http_build_query(['permissions' => $permissions]);

        $opts = ['http' => [
            'method' => 'POST',
            'header' => implode("\r\n", [
                'Content-Type: application/x-www-form-urlencoded',
                'Content-Length: '. strlen($post_data)
            ]),
            'content' => $post_data
        ]];

        $result = file_get_contents(
//            'http://seniorcare-mc.local/backend/api/5766d45bdba1152105abfd9662e55140/help',
            'https://console.seniorcaresw.com/backend/api/5766d45bdba1152105abfd9662e55140/help',
            false,
            stream_context_create($opts)
        );

        if($result !== false) {
            $result = json_decode($result, true);
        }

        return $this->respondSuccess(
            Response::HTTP_OK,
            '',
            $result
        );
    }
}
