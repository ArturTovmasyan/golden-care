<?php

namespace App\Api\V1\Admin\Controller;

use App\Annotation\Grant;
use App\Api\V1\Admin\Service\ResidentAdmissionService;
use App\Api\V1\Admin\Service\ResidentService;
use App\Api\V1\Common\Controller\BaseController;
use App\Api\V1\Common\Service\ImageFilterService;
use App\Entity\Resident;
use App\Model\GroupType;
use App\Model\ResidentState;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\JsonResponse;
use Knp\Bundle\SnappyBundle\Snappy\Response\PdfResponse;

/**
 * @Route("/api/v1.0/admin/resident")
 *
 * @Grant(grant="persistence-resident-resident", level="VIEW")
 *
 * Class ResidentController
 * @package App\Api\V1\Admin\Controller
 */
class ResidentController extends BaseController
{
    protected function gridIgnoreFields(Request $request): array
    {
        $ignoreFields = [];

        $state = $request->get('state');
        $type = (int)$request->get('type');
        $typeId = (int)$request->get('type_id');

        if (!empty($state) || (!empty($type) && !empty($typeId))) {
            if (!empty($type) && !empty($typeId)) {
                $ignoreFields[] = 'group_name';

                if ($type === GroupType::TYPE_FACILITY || $type === GroupType::TYPE_APARTMENT) {
                    $ignoreFields[] = 'address';
                    $ignoreFields[] = 'csz_str';
                }
                if ($type === GroupType::TYPE_REGION) {
                    $ignoreFields[] = 'room';
                }
            }

            if ($state === ResidentState::TYPE_NO_ADMISSION) {
                $ignoreFields[] = 'group_name';
                $ignoreFields[] = 'room';
                $ignoreFields[] = 'address';
                $ignoreFields[] = 'csz_str';
            }
        }

        return $ignoreFields;
    }

    /**
     * @Route("/grid", name="api_admin_resident_grid", methods={"GET"})
     *
     * @param Request $request
     * @param ResidentService $residentService
     * @param ResidentAdmissionService $residentAdmissionService
     * @return JsonResponse
     */
    public function gridAction(Request $request, ResidentService $residentService, ResidentAdmissionService $residentAdmissionService): JsonResponse
    {
        $residentService->setResidentAdmissionService($residentAdmissionService);

        return $this->respondGrid(
            $request,
            Resident::class,
            $request->get('compact') ? 'api_admin_resident_compact_grid' : 'api_admin_resident_grid',
            $residentService,
            [
                'type' => $request->get('type'),
                'type_id' => $request->get('type_id'),
                'state' => $request->get('state')
            ]
        );
    }

    /**
     * @Route("/grid", name="api_admin_resident_grid_options", methods={"OPTIONS"})
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function gridOptionAction(Request $request): JsonResponse
    {
        return $this->getOptionsByGroupName($request, Resident::class, $request->get('compact') ? 'api_admin_resident_compact_grid' : 'api_admin_resident_grid');
    }

    /**
     * @Route("", name="api_admin_resident_list", methods={"GET"})
     *
     * @param Request $request
     * @param ResidentService $residentService
     * @param ResidentAdmissionService $residentAdmissionService
     * @return PdfResponse|JsonResponse|Response
     */
    public function listAction(Request $request, ResidentService $residentService, ResidentAdmissionService $residentAdmissionService)
    {
        $residentService->setResidentAdmissionService($residentAdmissionService);

        return $this->respondList(
            $request,
            Resident::class,
            'api_admin_resident_list',
            $residentService,
            [
                'type' => $request->get('type'),
                'type_id' => $request->get('type_id'),
                'state' => $request->get('state')
            ]
        );
    }

    /**
     * @Route("/{id}", requirements={"id"="\d+"}, name="api_admin_resident_get", methods={"GET"})
     *
     * @param Request $request
     * @param $id
     * @param ResidentService $residentService
     * @return JsonResponse
     */
    public function getAction(Request $request, $id, ResidentService $residentService): JsonResponse
    {
        $entity = $residentService->getById($id);

        if ($entity !== null && $entity->getImage() !== null) {
            $downloadUrl = $request->getScheme() . '://' . $request->getHttpHost() . $this->generateUrl('api_admin_resident_image_download', ['id' => $entity->getId()]);

            $entity->setDownloadUrl($downloadUrl);
        } else {
            $entity->setDownloadUrl(null);
        }

        return $this->respondSuccess(
            Response::HTTP_OK,
            '',
            $residentService->getById($id),
            ['api_admin_resident_get']
        );
    }

    /**
     * @Route("", name="api_admin_resident_add", methods={"POST"})
     *
     * @Grant(grant="persistence-resident-resident", level="ADD")
     *
     * @param Request $request
     * @param ResidentService $residentService
     * @param ImageFilterService $imageFilterService
     * @return JsonResponse
     */
    public function addAction(Request $request, ResidentService $residentService, ImageFilterService $imageFilterService): JsonResponse
    {
        $residentService->setImageFilterService($imageFilterService);

        $id = $residentService->add(
            [
                'first_name' => $request->get('first_name'),
                'last_name' => $request->get('last_name'),
                'middle_name' => $request->get('middle_name'),
                'space_id' => $request->get('space_id'),
                'salutation_id' => $request->get('salutation_id'),
                'birthday' => $request->get('birthday'),
                'gender' => $request->get('gender'),
                'ssn' => $request->get('ssn'),
                'photo' => $request->get('photo'),
                'phones' => $request->get('phones'),
            ]
        );

        return $this->respondSuccess(
            Response::HTTP_CREATED,
            '',
            [$id]
        );
    }

    /**
     * @Route("/{id}", requirements={"id"="\d+"}, name="api_admin_resident_edit", methods={"PUT"})
     *
     * @Grant(grant="persistence-resident-resident", level="EDIT")
     *
     * @param Request $request
     * @param $id
     * @param ResidentService $residentService
     * @param ImageFilterService $imageFilterService
     * @return JsonResponse
     */
    public function editAction(Request $request, $id, ResidentService $residentService, ImageFilterService $imageFilterService): JsonResponse
    {
        $residentService->setImageFilterService($imageFilterService);

        $residentService->edit(
            $id,
            [
                'first_name' => $request->get('first_name'),
                'last_name' => $request->get('last_name'),
                'middle_name' => $request->get('middle_name'),
                'space_id' => $request->get('space_id'),
                'salutation_id' => $request->get('salutation_id'),
                'birthday' => $request->get('birthday'),
                'gender' => $request->get('gender'),
                'ssn' => $request->get('ssn'),
                'photo' => $request->get('photo'),
                'phones' => $request->get('phones'),
                'dnr' => $request->get('dnr'),
                'polst' => $request->get('polst'),
                'ambulatory' => $request->get('ambulatory'),
                'care_group' => $request->get('care_group'),
                'care_level_id' => $request->get('care_level_id'),
                'address' => $request->get('address'),
                'csz_id' => $request->get('csz_id'),
            ]
        );

        return $this->respondSuccess(
            Response::HTTP_CREATED
        );
    }

    /**
     * @Route("/{id}", requirements={"id"="\d+"}, name="api_admin_resident_delete", methods={"DELETE"})
     *
     * @Grant(grant="persistence-resident-resident", level="DELETE")
     *
     * @param Request $request
     * @param $id
     * @param ResidentService $residentService
     * @return JsonResponse
     */
    public function deleteAction(Request $request, $id, ResidentService $residentService): JsonResponse
    {
        $residentService->remove($id);

        return $this->respondSuccess(
            Response::HTTP_NO_CONTENT
        );
    }

    /**
     * @Route("", name="api_admin_resident_delete_bulk", methods={"DELETE"})
     *
     * @Grant(grant="persistence-resident-resident", level="DELETE")
     *
     * @param Request $request
     * @param ResidentService $residentService
     * @return JsonResponse
     */
    public function deleteBulkAction(Request $request, ResidentService $residentService): JsonResponse
    {
        $residentService->removeBulk($request->get('ids'));

        return $this->respondSuccess(
            Response::HTTP_NO_CONTENT
        );
    }

    /**
     * @Route("/related/info", name="api_admin_resident_related_info", methods={"POST"})
     *
     * @param Request $request
     * @param ResidentService $residentService
     * @return JsonResponse
     */
    public function relatedInfoAction(Request $request, ResidentService $residentService): JsonResponse
    {
        $relatedData = $residentService->getRelatedInfo($request->get('ids'));

        return $this->respondSuccess(
            Response::HTTP_OK,
            '',
            [$relatedData]
        );
    }

    /**
     * @Route("/{id}/photo", requirements={"id"="\d+"}, name="api_admin_resident_edit_photo", methods={"PUT"})
     *
     * @Grant(grant="persistence-resident-resident", level="EDIT")
     *
     * @param Request $request
     * @param $id
     * @param ResidentService $residentService
     * @param ImageFilterService $imageFilterService
     * @return JsonResponse
     */
    public function photoAction(Request $request, $id, ResidentService $residentService, ImageFilterService $imageFilterService): JsonResponse
    {
        $residentService->setImageFilterService($imageFilterService);

        $residentService->photo(
            $id,
            [
                'photo' => $request->get('photo'),
            ]
        );

        return $this->respondSuccess(
            Response::HTTP_OK
        );
    }

    /**
     * @Route("/state/{id}", requirements={"id"="\d+"}, name="api_admin_resident_get_state", methods={"GET"})
     *
     * @param Request $request
     * @param $id
     * @param ResidentService $residentService
     * @return JsonResponse
     */
    public function getResidentStateAction(Request $request, $id, ResidentService $residentService): JsonResponse
    {
        return $this->respondSuccess(
            Response::HTTP_OK,
            '',
            $residentService->getResidentStateById($id),
            ['api_admin_resident_get_state']
        );
    }

    /**
     * @Route("/last/admission/{id}", requirements={"id"="\d+"}, name="api_admin_resident_get_last_admission", methods={"GET"})
     *
     * @param Request $request
     * @param $id
     * @param ResidentAdmissionService $residentAdmissionService
     * @return JsonResponse
     */
    public function getResidentLastAdmissionAction(Request $request, $id, ResidentAdmissionService $residentAdmissionService): JsonResponse
    {
        return $this->respondSuccess(
            Response::HTTP_OK,
            '',
            $residentAdmissionService->getResidentLastAdmission($id),
            ['api_admin_resident_get_last_admission']
        );
    }

    /**
     * @Route("/calendar/{id}", requirements={"id"="\d+"}, name="api_admin_resident_calendar", methods={"GET"})
     *
     * @param Request $request
     * @param $id
     * @param ResidentService $residentService
     * @return JsonResponse
     */
    public function getResidentCalendarAction(Request $request, $id, ResidentService $residentService): JsonResponse
    {
        return $this->respondSuccess(
            Response::HTTP_OK,
            '',
            $residentService->getCalendar($id, $request->get('date_from'), $request->get('date_to')),
            ['api_admin_resident_calendar']
        );
    }

    /**
     * @Route("/mobile/{id}", requirements={"id"="\d+"}, name="api_admin_resident_edit_mobile", methods={"PUT"})
     *
     * @Grant(grant="persistence-resident-resident", level="EDIT")
     *
     * @param Request $request
     * @param $id
     * @param ResidentService $residentService
     * @return JsonResponse
     */
    public function mobileEditAction(Request $request, $id, ResidentService $residentService): JsonResponse
    {
        $residentService->mobileEdit(
            $id,
            [
                'first_name' => $request->get('first_name'),
                'last_name' => $request->get('last_name'),
                'middle_name' => $request->get('middle_name'),
                'salutation_id' => $request->get('salutation_id'),
                'birthday' => $request->get('birthday'),
                'gender' => $request->get('gender'),
                'phones' => $request->get('phones'),
                'dnr' => $request->get('dnr'),
                'polst' => $request->get('polst'),
                'ambulatory' => $request->get('ambulatory'),
                'care_group' => $request->get('care_group'),
                'care_level_id' => $request->get('care_level_id'),
                'address' => $request->get('address'),
                'csz_id' => $request->get('csz_id'),
            ]
        );

        return $this->respondSuccess(
            Response::HTTP_CREATED
        );
    }

    /**
     * @Route("/download/{id}", requirements={"id"="\d+"}, name="api_admin_resident_image_download", methods={"GET"})
     *
     * @param Request $request
     * @param $id
     * @param ResidentService $residentService
     * @return Response
     */
    public function downloadAction(Request $request, $id, ResidentService $residentService): Response
    {
        $isMobile = $request->query->has('mobile') ? true : false;

        $data = $residentService->downloadFile($id, $isMobile);

        return $this->respondResource($data[0], $data[1], $data[2]);

    }

    /**
     * @Route("/mobile/upload", name="api_admin_resident_upload_mobile", methods={"POST"})
     *
     * @Grant(grant="persistence-resident-resident", level="ADD")
     *
     * @param Request $request
     * @param ResidentService $residentService
     * @param ImageFilterService $imageFilterService
     * @return JsonResponse
     */
    public function mobileUploadAction(Request $request, ResidentService $residentService, ImageFilterService $imageFilterService): JsonResponse
    {
        $residentService->setImageFilterService($imageFilterService);

        $id = $residentService->mobileUpload(
            [
                'request_id' => $request->get('request_id'),
                'resident_id' => $request->get('resident_id'),
                'user_id' => $request->get('user_id'),
                'chunk' => $request->get('chunk'),
                'chunk_id' => $request->get('chunk_id'),
                'total_chunk' => $request->get('total_chunk'),
            ]
        );

        return $id !== null ? $this->respondSuccess(
            Response::HTTP_CREATED,
            '',
            [$id]
        ) : $this->respondSuccess(
            Response::HTTP_CREATED
        );
    }
}
