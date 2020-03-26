<?php

namespace App\Api\V1\Admin\Service\Report;

use App\Api\V1\Common\Service\BaseService;
use App\Api\V1\Common\Service\Exception\AssessmentNotFoundException;
use App\Entity\Assessment\Assessment;
use App\Model\Report\Assessment as ReportAssessment;
use App\Model\Assessment as AssessmentReportType;
use App\Entity\Resident;
use App\Repository\Assessment\AssessmentRepository;
use Symfony\Component\Routing\Exception\InvalidParameterException;

class AssessmentReportService extends BaseService
{
    /**
     * @param $group
     * @param bool|null $groupAll
     * @param bool|null $groupMulti
     * @param $groupIds
     * @param $groupId
     * @param bool|null $residentAll
     * @param $residentId
     * @param $date
     * @param $dateFrom
     * @param $dateTo
     * @param $assessmentId
     * @param $assessmentFormId
     * @return ReportAssessment
     */
    public function getBlankReport($group, ?bool $groupAll, ?bool $groupMulti, $groupIds, $groupId, ?bool $residentAll, $residentId, $date, $dateFrom, $dateTo, $assessmentId, $assessmentFormId): ReportAssessment
    {
        return $this->getReportByType($assessmentId, AssessmentReportType::TYPE_BLANK);
    }

    /**
     * @param $group
     * @param bool|null $groupAll
     * @param bool|null $groupMulti
     * @param $groupIds
     * @param $groupId
     * @param bool|null $residentAll
     * @param $residentId
     * @param $date
     * @param $dateFrom
     * @param $dateTo
     * @param $assessmentId
     * @param $assessmentFormId
     * @return ReportAssessment
     */
    public function getFilledReport($group, ?bool $groupAll, ?bool $groupMulti, $groupIds, $groupId, ?bool $residentAll, $residentId, $date, $dateFrom, $dateTo, $assessmentId, $assessmentFormId): ReportAssessment
    {
        return $this->getReportByType($assessmentId, AssessmentReportType::TYPE_FILLED);
    }

    /**
     * @param $assessmentId
     * @param $group
     * @return ReportAssessment
     */
    private function getReportByType($assessmentId, $group): ReportAssessment
    {
        $type = $group;

        if (!\in_array($type, [AssessmentReportType::TYPE_FILLED, AssessmentReportType::TYPE_BLANK], false)) {
            throw new InvalidParameterException('group');
        }

        /** @var AssessmentRepository $repo */
        $repo = $this->em->getRepository(Assessment::class);

        /**
         * @var Assessment $assessment
         * @var Resident $resident
         */
        $assessment = $repo->getOne($this->grantService->getCurrentSpace(), $this->grantService->getCurrentUserEntityGrants(Assessment::class), $assessmentId);

        if ($assessment === null) {
            throw new AssessmentNotFoundException();
        }

        $form = $assessment->getForm();
        $careLevelGroups = $form->getCareLevelGroups();
        $resident = $assessment->getResident();

        $report = new ReportAssessment();
        $report->setType($type);
        $report->setTitle('Level of Care Assessment');
        $report->setAllGroups($careLevelGroups);

        if ($type === AssessmentReportType::TYPE_FILLED) {
            $report->setResidentFullName($resident->getFirstName() . ' ' . $resident->getLastName());
            $report->setDate($assessment->getDate());
            $report->setPerformedBy($assessment->getPerformedBy());
            $report->setTable($assessment->getForm()->getFormCategories(), $assessment->getAssessmentRows());
        } else {
            $report->setResidentFullName('_________________________');
            $report->setDate('_________________________');
            $report->setPerformedBy('_________________________');
            $report->setBlankTable($assessment->getForm()->getFormCategories());
        }

        $report->setGroups($careLevelGroups);

        unset($form, $careLevelGroups, $assessment);

        return $report;
    }
}