<?php

namespace App\Api\V1\Admin\Controller;

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
     * @return JsonResponse|PdfResponse
     * @throws \ReflectionException
     */
    public function gridAction(Request $request, ResidentService $residentService, ResidentAdmissionService $residentAdmissionService)
    {
        $residentService->setResidentAdmissionService($residentAdmissionService);

        return $this->respondGrid(
            $request,
            Resident::class,
            'api_admin_resident_grid',
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
     * @throws \ReflectionException
     */
    public function gridOptionAction(Request $request)
    {
        return $this->getOptionsByGroupName($request, Resident::class, 'api_admin_resident_grid');
    }

    /**
     * @Route("", name="api_admin_resident_list", methods={"GET"})
     *
     * @param Request $request
     * @param ResidentService $residentService
     * @return JsonResponse|PdfResponse
     * @throws \Throwable
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
    public function getAction(Request $request, $id, ResidentService $residentService)
    {
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
     * @throws \Throwable
     */
    public function addAction(Request $request, ResidentService $residentService, ImageFilterService $imageFilterService)
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
     * @throws \Doctrine\DBAL\ConnectionException
     */
    public function editAction(Request $request, $id, ResidentService $residentService, ImageFilterService $imageFilterService)
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
     * @throws \Doctrine\DBAL\ConnectionException
     * @throws \Throwable
     */
    public function deleteAction(Request $request, $id, ResidentService $residentService)
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
     * @throws \Doctrine\DBAL\ConnectionException
     * @throws \Throwable
     */
    public function deleteBulkAction(Request $request, ResidentService $residentService)
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
     * @throws \Doctrine\DBAL\ConnectionException
     * @throws \Throwable
     */
    public function relatedInfoAction(Request $request, ResidentService $residentService)
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
     * @throws \Doctrine\DBAL\ConnectionException
     */
    public function photoAction(Request $request, $id, ResidentService $residentService, ImageFilterService $imageFilterService)
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
     * @param ResidentService $residentService
     * @param $id
     * @return JsonResponse
     */
    public function getResidentStateAction(Request $request, $id, ResidentService $residentService)
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
     * @param ResidentAdmissionService $residentAdmissionService
     * @param $id
     * @return JsonResponse
     */
    public function getResidentLastAdmissionAction(Request $request, $id, ResidentAdmissionService $residentAdmissionService)
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
     * @param ResidentService $residentService
     * @param $id
     * @return JsonResponse
     */
    public function getResidentCalendarAction(Request $request, $id, ResidentService $residentService)
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
     * @throws \Doctrine\DBAL\ConnectionException
     */
    public function mobileEditAction(Request $request, $id, ResidentService $residentService)
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
     * @param ResidentService $residentService
     * @param $id
     * @return Response
     */
    public function downloadAction(Request $request, $id, ResidentService $residentService)
    {
        $data = $residentService->downloadFile($id);

        return $this->respondImageFile($data[0], $data[1], $data[2]);
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
     * @throws \Throwable
     */
    public function mobileUploadAction(Request $request, ResidentService $residentService, ImageFilterService $imageFilterService)
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
