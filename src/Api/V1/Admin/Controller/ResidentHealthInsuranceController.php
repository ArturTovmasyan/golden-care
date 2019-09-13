<?php
namespace App\Api\V1\Admin\Controller;

use App\Api\V1\Admin\Service\ResidentHealthInsuranceService;
use App\Api\V1\Common\Controller\BaseController;
use App\Api\V1\Common\Service\S3Service;
use App\Entity\ResidentHealthInsurance;
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
 * @Route("/api/v1.0/admin/resident/health/insurance")
 *
 * @Grant(grant="persistence-resident-resident_health_insurance", level="VIEW")
 *
 * Class ResidentHealthInsuranceController
 * @package App\Api\V1\Admin\Controller
 */
class ResidentHealthInsuranceController extends BaseController
{
    /**
     * @Route("/grid", name="api_admin_resident_health_insurance_grid", methods={"GET"})
     *
     * @param Request $request
     * @param ResidentHealthInsuranceService $residentHealthInsurance
     * @return JsonResponse|PdfResponse
     * @throws \ReflectionException
     */
    public function gridAction(Request $request, ResidentHealthInsuranceService $residentHealthInsurance)
    {
        return $this->respondGrid(
            $request,
            ResidentHealthInsurance::class,
            'api_admin_resident_health_insurance_grid',
            $residentHealthInsurance,
            ['resident_id' => $request->get('resident_id')]
        );
    }

    /**
     * @Route("/grid", name="api_admin_resident_health_insurance_grid_options", methods={"OPTIONS"})
     *
     * @param Request $request
     * @return JsonResponse
     * @throws \ReflectionException
     */
    public function gridOptionAction(Request $request)
    {
        return $this->getOptionsByGroupName(ResidentHealthInsurance::class, 'api_admin_resident_health_insurance_grid');
    }

    /**
     * @Route("", name="api_admin_resident_health_insurance_list", methods={"GET"})
     *
     * @param Request $request
     * @param ResidentHealthInsuranceService $residentHealthInsurance
     * @return JsonResponse|PdfResponse
     * @throws \ReflectionException
     */
    public function listAction(Request $request, ResidentHealthInsuranceService $residentHealthInsurance)
    {
        return $this->respondList(
            $request,
            ResidentHealthInsurance::class,
            'api_admin_resident_health_insurance_list',
            $residentHealthInsurance,
            ['resident_id' => $request->get('resident_id')]
        );
    }

    /**
     * @Route("/{id}", requirements={"id"="\d+"}, name="api_admin_resident_health_insurance_get", methods={"GET"})
     *
     * @param ResidentHealthInsuranceService $residentHealthInsurance
     * @param S3Service $s3Service
     * @param Request $request
     * @param $id
     * @return JsonResponse
     */
    public function getAction(Request $request, $id, ResidentHealthInsuranceService $residentHealthInsurance, S3Service $s3Service)
    {
        $entity = $residentHealthInsurance->getById($id);

        if ($entity !== null && $entity->getFirstFile() !== null) {
            $cmdFirst = $s3Service->getS3Client()->getCommand('GetObject', [
                'Bucket' => getenv('AWS_BUCKET'),
                'Key'    => $entity->getFirstFile()->getType() . '/' . $entity->getFirstFile()->getS3Id(),
            ]);
            $s3RequestFirst = $s3Service->getS3Client()->createPresignedRequest($cmdFirst, '+20 minutes');

            $entity->setFirstFileDownloadUrl((string)$s3RequestFirst->getUri());
        } else {
            $entity->setFirstFileDownloadUrl(null);
        }

        if ($entity !== null && $entity->getSecondFile() !== null) {
            $cmdSecond = $s3Service->getS3Client()->getCommand('GetObject', [
                'Bucket' => getenv('AWS_BUCKET'),
                'Key'    => $entity->getSecondFile()->getType() . '/' . $entity->getSecondFile()->getS3Id(),
            ]);
            $s3RequestSecond = $s3Service->getS3Client()->createPresignedRequest($cmdSecond, '+20 minutes');

            $entity->setSecondFileDownloadUrl((string)$s3RequestSecond->getUri());
        } else {
            $entity->setSecondFileDownloadUrl(null);
        }

        return $this->respondSuccess(
            Response::HTTP_OK,
            '',
            $residentHealthInsurance->getById($id),
            ['api_admin_resident_health_insurance_get']
        );
    }

    /**
     * @Route("", name="api_admin_resident_health_insurance_add", methods={"POST"})
     *
     * @Grant(grant="persistence-resident-resident_health_insurance", level="ADD")
     *
     * @param Request $request
     * @param ResidentHealthInsuranceService $residentHealthInsurance
     * @return JsonResponse
     * @throws \Throwable
     */
    public function addAction(Request $request, ResidentHealthInsuranceService $residentHealthInsurance)
    {
        $id = $residentHealthInsurance->add(
            [
                'resident_id' => $request->get('resident_id'),
                'company_id' => $request->get('company_id'),
                'medical_record_number' => $request->get('medical_record_number'),
                'group_number' => $request->get('group_number'),
                'notes' => $request->get('notes') ?? '',
                'first_file' => $request->get('first_file'),
                'second_file' => $request->get('second_file')
            ]
        );

        return $this->respondSuccess(
            Response::HTTP_CREATED,
            '',
            [$id]
        );
    }

    /**
     * @Route("/{id}", requirements={"id"="\d+"}, name="api_admin_resident_health_insurance_edit", methods={"PUT"})
     *
     * @Grant(grant="persistence-resident-resident_health_insurance", level="EDIT")
     *
     * @param Request $request
     * @param $id
     * @param ResidentHealthInsuranceService $residentHealthInsurance
     * @return JsonResponse
     * @throws \Throwable
     */
    public function editAction(Request $request, $id, ResidentHealthInsuranceService $residentHealthInsurance)
    {
        $residentHealthInsurance->edit(
            $id,
            [
                'resident_id' => $request->get('resident_id'),
                'company_id' => $request->get('company_id'),
                'medical_record_number' => $request->get('medical_record_number'),
                'group_number' => $request->get('group_number'),
                'notes' => $request->get('notes') ?? '',
                'first_file' => $request->get('first_file'),
                'second_file' => $request->get('second_file')
            ]
        );

        return $this->respondSuccess(
            Response::HTTP_CREATED
        );
    }

    /**
     * @Route("/{id}", requirements={"id"="\d+"}, name="api_admin_resident_health_insurance_delete", methods={"DELETE"})
     *
     * @Grant(grant="persistence-resident-resident_health_insurance", level="DELETE")
     *
     * @param $id
     * @param ResidentHealthInsuranceService $residentHealthInsurance
     * @return JsonResponse
     * @throws \Doctrine\DBAL\ConnectionException
     * @throws \Throwable
     */
    public function deleteAction(Request $request, $id, ResidentHealthInsuranceService $residentHealthInsurance)
    {
        $residentHealthInsurance->remove($id);

        return $this->respondSuccess(
            Response::HTTP_NO_CONTENT
        );
    }

    /**
     * @Route("", name="api_admin_resident_health_insurance_delete_bulk", methods={"DELETE"})
     *
     * @Grant(grant="persistence-resident-resident_health_insurance", level="DELETE")
     *
     * @param Request $request
     * @param ResidentHealthInsuranceService $residentHealthInsurance
     * @return JsonResponse
     * @throws \Doctrine\DBAL\ConnectionException
     * @throws \Throwable
     */
    public function deleteBulkAction(Request $request, ResidentHealthInsuranceService $residentHealthInsurance)
    {
        $residentHealthInsurance->removeBulk($request->get('ids'));

        return $this->respondSuccess(
            Response::HTTP_NO_CONTENT
        );
    }

    /**
     * @Route("/related/info", name="api_admin_resident_health_insurance_related_info", methods={"POST"})
     *
     * @param Request $request
     * @param ResidentHealthInsuranceService $residentHealthInsurance
     * @return JsonResponse
     * @throws \Doctrine\DBAL\ConnectionException
     * @throws \Throwable
     */
    public function relatedInfoAction(Request $request, ResidentHealthInsuranceService $residentHealthInsurance)
    {
        $relatedData = $residentHealthInsurance->getRelatedInfo($request->get('ids'));

        return $this->respondSuccess(
            Response::HTTP_OK,
            '',
            [$relatedData]
        );
    }

    /**
     * @Route("/download/{id}/{number}", requirements={"id"="\d+", "number"="\d+"}, name="api_admin_resident_health_insurance_download", methods={"GET"})
     *
     * @param ResidentHealthInsuranceService $residentHealthInsurance
     * @param $id
     * @param $number
     * @return Response
     */
    public function downloadAction(Request $request, $id, $number, ResidentHealthInsuranceService $residentHealthInsurance)
    {
        $data = $residentHealthInsurance->downloadFile($id, (int)$number);

        return $this->respondResource($data[0], $data[1], $data[2]);
    }
}
