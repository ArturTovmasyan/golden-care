<?php

namespace App\Api\V1\Admin\Service\Report;

use App\Api\V1\Common\Service\BaseService;
use App\Api\V1\Common\Service\Exception\AssessmentNotFoundException;
use App\Entity\Assessment\Assessment;
use App\Model\Report\Assessment as ReportAssessment;
use App\Model\Assessment as AssessmentReportType;
use App\Entity\Resident;
use Symfony\Component\Routing\Exception\InvalidParameterException;

class AssessmentReportService extends BaseService
{
    /**
     * @param $group
     * @param bool|null $groupAll
     * @param $groupId
     * @param bool|null $residentAll
     * @param $residentId
     * @param $date
     * @param $dateFrom
     * @param $dateTo
     * @return ReportAssessment
     */
    public function getBlankReport($group, ?bool $groupAll, $groupId, ?bool $residentAll, $residentId, $date, $dateFrom, $dateTo): ReportAssessment
    {
        return $this->getReportByType($groupId, AssessmentReportType::TYPE_BLANK);
    }

    /**
     * @param $group
     * @param bool|null $groupAll
     * @param $groupId
     * @param bool|null $residentAll
     * @param $residentId
     * @param $date
     * @param $dateFrom
     * @param $dateTo
     * @return ReportAssessment
     */
    public function getFilledReport($group, ?bool $groupAll, $groupId, ?bool $residentAll, $residentId, $date, $dateFrom, $dateTo): ReportAssessment
    {
        return $this->getReportByType($groupId, AssessmentReportType::TYPE_FILLED);
    }

    /**
     * @param $groupId
     * @param $group
     * @return ReportAssessment
     */
    private function getReportByType($groupId, $group): ReportAssessment
    {
        $type = $group;
        $typeId = $groupId;

        if (!\in_array($type, [AssessmentReportType::TYPE_FILLED, AssessmentReportType::TYPE_BLANK], false)) {
            throw new InvalidParameterException('group');
        }

        /**
         * @var Assessment $assessment
         * @var Resident $resident
         */
        $assessment = $this->em->getRepository(Assessment::class)->find($typeId);

        if ($assessment === null) {
            throw new AssessmentNotFoundException();
        }

        $form = $assessment->getForm();
        $careLevelGroups = $form->getCareLevelGroups();
        $resident = $assessment->getResident();

        $report = new ReportAssessment();
        $report->setType($type);
        $report->setTitle('Level of Care Assessment');
        $report->setGroups($careLevelGroups);
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

        unset($form, $careLevelGroups, $assessment);

        return $report;
    }
}