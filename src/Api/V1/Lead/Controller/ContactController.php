<?php
namespace App\Api\V1\Lead\Controller;

use App\Api\V1\Lead\Service\ContactService;
use App\Api\V1\Common\Controller\BaseController;
use App\Entity\Lead\Contact;
use App\Entity\User;
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
 * @Route("/api/v1.0/lead/contact")
 *
 * @Grant(grant="persistence-lead-contact", level="VIEW")
 *
 * Class ContactController
 * @package App\Api\V1\Admin\Controller
 */
class ContactController extends BaseController
{
    /**
     * @Route("/grid", name="api_lead_contact", methods={"GET"})
     *
     * @param Request $request
     * @param ContactService $contactService
     * @return JsonResponse|PdfResponse
     * @throws \ReflectionException
     */
    public function gridAction(Request $request, ContactService $contactService)
    {
        /** @var User $user */
        $user = $this->get('security.token_storage')->getToken()->getUser();

        return $this->respondGrid(
            $request,
            Contact::class,
            'api_lead_contact_grid',
            $contactService,
            [
                'my' => $request->get('my'),
                'user_id' => $user->getId()
            ]
        );
    }

    /**
     * @Route("/grid", name="api_lead_contact_grid_options", methods={"OPTIONS"})
     *
     * @param Request $request
     * @return JsonResponse
     * @throws \ReflectionException
     */
    public function gridOptionAction(Request $request)
    {
        return $this->getOptionsByGroupName($request, Contact::class, 'api_lead_contact_grid');
    }

    /**
     * @Route("", name="api_lead_contact_list", methods={"GET"})
     *
     * @param Request $request
     * @param ContactService $contactService
     * @return JsonResponse|PdfResponse
     * @throws \ReflectionException
     */
    public function listAction(Request $request, ContactService $contactService)
    {
        /** @var User $user */
        $user = $this->get('security.token_storage')->getToken()->getUser();

        return $this->respondList(
            $request,
            Contact::class,
            'api_lead_contact_list',
            $contactService,
            [
                'my' => $request->get('my'),
                'user_id' => $user->getId()
            ]
        );
    }

    /**
     * @Route("/{id}", requirements={"id"="\d+"}, name="api_lead_contact_get", methods={"GET"})
     *
     * @param ContactService $contactService
     * @param $id
     * @return JsonResponse
     */
    public function getAction(Request $request, $id, ContactService $contactService)
    {
        return $this->respondSuccess(
            Response::HTTP_OK,
            '',
            $contactService->getById($id),
            ['api_lead_contact_get']
        );
    }

    /**
     * @Route("", name="api_lead_contact_add", methods={"POST"})
     *
     * @Grant(grant="persistence-lead-contact", level="ADD")
     *
     * @param Request $request
     * @param ContactService $contactService
     * @return JsonResponse
     * @throws \Throwable
     */
    public function addAction(Request $request, ContactService $contactService)
    {
        $id = $contactService->add(
            [
                'first_name' => $request->get('first_name'),
                'last_name' => $request->get('last_name'),
                'organization_id' => $request->get('organization_id'),
                'notes' => $request->get('notes'),
                'phones' => $request->get('phones'),
                'emails' => $request->get('emails'),
                'space_id' => $request->get('space_id')
            ]
        );

        return $this->respondSuccess(
            Response::HTTP_CREATED,
            '',
            [$id]
        );
    }

    /**
     * @Route("/{id}", requirements={"id"="\d+"}, name="api_lead_contact_edit", methods={"PUT"})
     *
     * @Grant(grant="persistence-lead-contact", level="EDIT")
     *
     * @param Request $request
     * @param $id
     * @param ContactService $contactService
     * @return JsonResponse
     * @throws \Throwable
     */
    public function editAction(Request $request, $id, ContactService $contactService)
    {
        $contactService->edit(
            $id,
            [
                'first_name' => $request->get('first_name'),
                'last_name' => $request->get('last_name'),
                'organization_id' => $request->get('organization_id'),
                'notes' => $request->get('notes'),
                'phones' => $request->get('phones'),
                'emails' => $request->get('emails'),
                'space_id' => $request->get('space_id')
            ]
        );

        return $this->respondSuccess(
            Response::HTTP_CREATED
        );
    }

    /**
     * @Route("/{id}", requirements={"id"="\d+"}, name="api_lead_contact_delete", methods={"DELETE"})
     *
     * @Grant(grant="persistence-lead-contact", level="DELETE")
     *
     * @param $id
     * @param ContactService $contactService
     * @return JsonResponse
     * @throws \Doctrine\DBAL\ConnectionException
     * @throws \Throwable
     */
    public function deleteAction(Request $request, $id, ContactService $contactService)
    {
        $contactService->remove($id);

        return $this->respondSuccess(
            Response::HTTP_NO_CONTENT
        );
    }

    /**
     * @Route("", name="api_lead_contact_delete_bulk", methods={"DELETE"})
     *
     * @Grant(grant="persistence-lead-contact", level="DELETE")
     *
     * @param Request $request
     * @param ContactService $contactService
     * @return JsonResponse
     * @throws \Doctrine\DBAL\ConnectionException
     * @throws \Throwable
     */
    public function deleteBulkAction(Request $request, ContactService $contactService)
    {
        $contactService->removeBulk($request->get('ids'));

        return $this->respondSuccess(
            Response::HTTP_NO_CONTENT
        );
    }

    /**
     * @Route("/related/info", name="api_lead_contact_related_info", methods={"POST"})
     *
     * @param Request $request
     * @param ContactService $contactService
     * @return JsonResponse
     * @throws \Doctrine\DBAL\ConnectionException
     * @throws \Throwable
     */
    public function relatedInfoAction(Request $request, ContactService $contactService)
    {
        $relatedData = $contactService->getRelatedInfo($request->get('ids'));

        return $this->respondSuccess(
            Response::HTTP_OK,
            '',
            [$relatedData]
        );
    }
}
