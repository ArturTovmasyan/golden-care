<?php

namespace App\Api\V1\Admin\Service\Report;

use App\Api\V1\Common\Service\BaseService;
use App\Entity\CareLevel;
use App\Entity\ResidentAdmission;
use App\Model\GroupType;
use App\Model\Report\FacilityCareLevels;
use App\Repository\CareLevelRepository;
use App\Repository\ResidentAdmissionRepository;
use Symfony\Component\Routing\Exception\InvalidParameterException;

class FacilityReportService extends BaseService
{
    /**
     * @param $group
     * @param bool|null $groupAll
     * @param $groupIds
     * @param $groupId
     * @param bool|null $residentAll
     * @param $residentId
     * @param $date
     * @param $dateFrom
     * @param $dateTo
     * @param $assessmentId
     * @param $assessmentFormId
     * @param $discontinued
     * @return FacilityCareLevels
     */
    public function getFacilityCareLevelsReport($group, ?bool $groupAll, $groupIds, $groupId, ?bool $residentAll, $residentId, $date, $dateFrom, $dateTo, $assessmentId, $assessmentFormId, $discontinued): FacilityCareLevels
    {
        $currentSpace = $this->grantService->getCurrentSpace();

        $type = $group;
        $typeId = $groupId;

        if ($type !== GroupType::TYPE_FACILITY) {
            throw new InvalidParameterException('group');
        }

        /** @var ResidentAdmissionRepository $repo */
        $repo = $this->em->getRepository(ResidentAdmission::class);

        $residents = $repo->getActiveResidentsForFacilityReport($currentSpace, $this->grantService->getCurrentUserEntityGrants(ResidentAdmission::class), $typeId);

        /** @var CareLevelRepository $careLevelRepo */
        $careLevelRepo = $this->em->getRepository(CareLevel::class);

        $careLevels = $careLevelRepo->list($currentSpace, $this->grantService->getCurrentUserEntityGrants(CareLevel::class));

        $data = [];
        if (!empty($residents) && !empty($careLevels)) {
            foreach ($residents as $resident) {
                /** @var CareLevel $careLevel */
                foreach ($careLevels as $careLevel) {
                    if ($careLevel->getId() === $resident['careLevelId']) {
                        $data[$resident['typeName']][$careLevel->getTitle()] = array_key_exists($resident['typeName'], $data) && array_key_exists($careLevel->getTitle(), $data[$resident['typeName']]) ? $data[$resident['typeName']][$careLevel->getTitle()] + 1 : 1;
                    }

                    if (array_key_exists($resident['typeName'], $data) && !array_key_exists($careLevel->getTitle(), $data[$resident['typeName']])) {
                        $data[$resident['typeName']][$careLevel->getTitle()] = 0;
                    }
                }
            }

            /** @var CareLevel $careLevel */
            foreach ($careLevels as $careLevel) {
                foreach ($data as $datum) {
                    if (!array_key_exists($careLevel->getTitle(), $datum)) {
                        $datum[$careLevel->getTitle()] = 0;
                    }

                    ksort($datum);
                }
            }
        }

        $report = new FacilityCareLevels();
        $report->setData($data);

        return $report;
    }
}