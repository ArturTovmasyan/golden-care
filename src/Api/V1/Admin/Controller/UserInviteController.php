<?php
namespace App\Api\V1\Admin\Controller;

use App\Api\V1\Admin\Service\UserInviteService;
use App\Api\V1\Common\Controller\BaseController;
use App\Api\V1\Common\Model\ResponseCode;
use App\Entity\UserInvite;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\JsonResponse;
use Knp\Bundle\SnappyBundle\Snappy\Response\PdfResponse;
use App\Annotation\Grant as Grant;

/**
 * @IgnoreAnnotation("api")
 * @IgnoreAnnotation("apiVersion")
 * @IgnoreAnnotation("apiName")
 * @IgnoreAnnotation("apiGroup")
 * @IgnoreAnnotation("apiDescription")
 * @IgnoreAnnotation("apiHeader")
 * @IgnoreAnnotation("apiSuccess")
 * @IgnoreAnnotation("apiSuccessExample")
 * @IgnoreAnnotation("apiParam")
 * @IgnoreAnnotation("apiParamExample")
 * @IgnoreAnnotation("apiErrorExample")
 * @IgnoreAnnotation("apiPermission")
 *
 * @Route("/api/v1.0/admin/user/invite")
 *
 * @Grant(grant="persistence-security-user_invite", level="VIEW")
 *
 * ClassUserInviteController
 * @package App\Api\V1\Admin\Controller
 */
class UserInviteController extends BaseController
{
    /**
     * @api {get} /api/v1.0/admin/user/invite/grid Get UserInvites Grid
     * @apiVersion 1.0.0
     * @apiName Get UserInvites Grid
     * @apiGroup Admin User Invite
     * @apiDescription This function is used to listing user invites
     *
     * @apiHeader {String} Content-Type  application/json
     * @apiHeader {String} Authorization Bearer ACCESS_TOKEN
     *
     * @apiSuccess {Int}     id     The unique identifier of the userInvite
     * @apiSuccess {String}  email  The email of the userInvite
     * @apiSuccess {Object}  space  The space of the userInvite
     *
     * @apiSuccessExample {json} Sample Response:
     *     HTTP/1.1 200 OK
     *     {
     *          "page": "1",
     *          "per_page": 10,
     *          "total": 5,
     *          "data": [
     *              {
     *                  "id": 1,
     *                  "email": "test@gmail.com",
     *                  "space": "alms"
     *              }
     *          ]
     *     }
     *
     * @Route("/grid", name="api_admin_user_invite_grid", methods={"GET"})
     *
     * @param Request $request
     * @param UserInviteService $userInviteService
     * @return JsonResponse|PdfResponse
     * @throws \ReflectionException
     */
    public function gridAction(Request $request, UserInviteService $userInviteService)
    {
        return $this->respondGrid(
            $request,
            UserInvite::class,
            'api_admin_user_invite_grid',
            $userInviteService
        );
    }

    /**
     * @api {options} /api/v1.0/admin/user/invite/grid Get UserInvites Grid Options
     * @apiVersion 1.0.0
     * @apiName Get UserInvites Grid Options
     * @apiGroup Admin User Invite
     * @apiDescription This function is used to describe options of listing
     *
     * @apiHeader {String} Content-Type  application/json
     * @apiHeader {String} Authorization Bearer ACCESS_TOKEN
     *
     * @apiSuccess {Array} options The options of the medication listing
     *
     * @apiSuccessExample {json} Sample Response:
     *     HTTP/1.1 200 OK
     *     {
     *          [
     *              {
     *                  "id": "name",
     *                  "type": "integer",
     *                  "sortable": true,
     *                  "filterable": true,
     *              }
     *          ]
     *     }
     *
     * @Route("/grid", name="api_admin_user_invite_grid_options", methods={"OPTIONS"})
     *
     * @param Request $request
     * @return JsonResponse
     * @throws \ReflectionException
     */
    public function gridOptionAction(Request $request)
    {
        return $this->getOptionsByGroupName(UserInvite::class, 'api_admin_user_invite_grid');
    }

    /**
     * @api {get} /api/v1.0/user/invite Get UserInvites
     * @apiVersion 1.0.0
     * @apiName Get UserInvites
     * @apiGroup Admin User Invite
     * @apiDescription This function is used to listing user invites
     *
     * @apiHeader {String} Content-Type  application/json
     * @apiHeader {String} Authorization Bearer ACCESS_TOKEN
     *
     * @apiSuccess {Int}     id     The unique identifier of the userInvite
     * @apiSuccess {String}  email  The email of the userInvite
     * @apiSuccess {Object}  space  The space of the userInvite
     *
     * @apiSuccessExample {json} Sample Response:
     *     HTTP/1.1 200 OK
     *     {
     *          [
     *              {
     *                  "id": 1,
     *                  "email": "test@gmail.com",
     *                  "space": {
     *                      "id": 1,
     *                      "name": "alms"
     *                  }
     *              }
     *          ]
     *     }
     *
     * @Route("", name="api_admin_user_invite_list", methods={"GET"})
     *
     * @param Request $request
     * @param UserInviteService $userInviteService
     * @return JsonResponse|PdfResponse
     * @throws \ReflectionException
     */
    public function listAction(Request $request, UserInviteService $userInviteService)
    {
        return $this->respondList(
            $request,
            UserInvite::class,
            'api_admin_user_invite_list',
            $userInviteService
        );
    }

    /**
     * @api {get} /api/v1.0/admin/user/invite /{id} Get UserInvite
     * @apiVersion 1.0.0
     * @apiName Get UserInvite
     * @apiGroup Admin User Invite
     * @apiDescription This function is used to get user invite
     *
     * @apiHeader {String} Content-Type  application/json
     * @apiHeader {String} Authorization Bearer ACCESS_TOKEN
     *
     * @apiSuccess {Int}     id            The unique identifier of the userInvite
     * @apiSuccess {String}  email         The email of the userInvite
     * @apiSuccess {Object}  space         The space of the userInvite
     *
     * @apiSuccessExample {json} Sample Response:
     *     HTTP/1.1 200 OK
     *     {
     *          "id": 1,
     *          "email": "test@gmail.com",
     *          "space": {
     *              "id": 1,
     *              "name": "alms"
     *          }
     *     }
     *
     * @Route("/{id}", requirements={"id"="\d+"}, name="api_admin_user_invite_get", methods={"GET"})
     *
     * @param Request $request
     * @param UserInviteService $userInviteService
     * @param $id
     * @return JsonResponse
     */
    public function getAction(Request $request, $id, UserInviteService $userInviteService)
    {
        return $this->respondSuccess(
            Response::HTTP_OK,
            '',
            $userInviteService->getById($id),
            ['api_admin_user_invite_get']
        );
    }

    /**
     * @api {post} /api/v1.0/admin/user/invite Invite User
     * @apiVersion 1.0.0
     * @apiName Invite User
     * @apiGroup Admin User Invite
     * @apiDescription This function is used to invite user
     *
     * @apiHeader {String} Content-Type  application/json
     * @apiHeader {String} Authorization Bearer ACCESS_TOKEN
     *
     * @apiParam {String}  email      The email of the invite user
     * @apiParam {Int}     space_id   The unique identifier of the space
     * @apiParam {Int}     user_id    The unique identifier of the user
     *
     * @apiParamExample {json} Request-Example:
     *     {
     *         "email": "test@gmail.com",
     *         "space_id": 1
     *     }
     * @apiSuccessExample {json} Sample Response:
     *     HTTP/1.1 201 Created
     *     {}
     * @apiErrorExample {json} Error-Response:
     *     HTTP/1.1 400 Bad Request
     *     {
     *          "code": 610,
     *          "error": "Validation error",
     *          "details": {
     *              "title": "Sorry, this email is already in use."
     *          }
     *     }
     *
     * @Route("", name="api_admin_user_invite_add", methods={"POST"})
     *
     * @Grant(grant="persistence-security-user_invite", level="ADD")
     *
     * @param Request $request
     * @param UserInviteService $userInviteService
     * @return JsonResponse
     * @throws \Doctrine\DBAL\ConnectionException
     */
    public function addAction(Request $request, UserInviteService $userInviteService)
    {
        $id = $userInviteService->add(
            $request->get('space_id'),
            $request->get('user_id'),
            $request->get('email'),
            $request->get('owner'),
            $request->get('roles'),
            $request->getSchemeAndHttpHost()
        );

        return $this->respondSuccess(
            ResponseCode::INVITATION_LINK_SENT_TO_EMAIL,
            '',
            [$id]
        );
    }

    /**
     * @api {delete} /api/v1.0/admin/user/invite/{id} Reject Invitation
     * @apiVersion 1.0.0
     * @apiName Reject Invitation
     * @apiGroup Admin User Invite
     * @apiDescription This function is used to reject space invitation
     *
     * @apiHeader {String} Content-Type  application/json
     * @apiHeader {String} Authorization Bearer ACCESS_TOKEN
     *
     * @apiParam {Int} id The unique identifier of user invite
     *
     * @apiSuccessExample {json} Sample Response:
     *     HTTP/1.1 204 No Content
     *     {}
     * @apiErrorExample {json} Error-Response:
     *     HTTP/1.1 400 Bad Request
     *     {
     *          "code": 627,
     *          "error": "UserUnvite not found"
     *     }
     *
     * @Route("/{id}", requirements={"id"="\d+"}, name="api_admin_user_invite_delete", methods={"DELETE"})
     *
     * @Grant(grant="persistence-security-user_invite", level="DELETE")
     *
     * @param Request $request
     * @param $id
     * @param UserInviteService $userInviteService
     * @return JsonResponse
     * @throws \Doctrine\DBAL\ConnectionException
     * @throws \Throwable
     */
    public function deleteAction(Request $request, $id, UserInviteService $userInviteService)
    {
        $userInviteService->remove($id);

        return $this->respondSuccess(
            Response::HTTP_NO_CONTENT
        );
    }
}
