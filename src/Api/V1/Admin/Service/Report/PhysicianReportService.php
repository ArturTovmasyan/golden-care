<?php

namespace App\Api\V1\Admin\Service\Report;

use App\Api\V1\Common\Service\BaseService;
use App\Entity\Physician;
use App\Model\ContractType;
use App\Model\Report\PhysicianFull;
use App\Model\Report\PhysicianSimple;
use Symfony\Component\Routing\Exception\InvalidParameterException;

class PhysicianReportService extends BaseService
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
     * @return PhysicianSimple
     */
    public function getSimpleReport($group, ?bool $groupAll, $groupId, ?bool $residentAll, $residentId, $date, $dateFrom, $dateTo)
    {
        $type = $group;
        $typeId = $groupId;

        if (!\in_array($type, [ContractType::TYPE_FACILITY, ContractType::TYPE_REGION], false)) {
            throw new InvalidParameterException('group');
        }

        $physicians = $this->em->getRepository(Physician::class)->getPhysicianSimpleReport($type, $typeId);

        $physiciansByTypeId = [];
        foreach ($physicians as $physician) {
            $physiciansByTypeId[$physician['typeId']][] = $physician;
        }

        $report = new PhysicianSimple();
        $report->setTitle('PHYSICIAN, ROSTER SIMPLE');
        $report->setType($type);
        $report->setPhysicianData($physiciansByTypeId);

        return $report;
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
     * @return PhysicianFull
     */
    public function getFullReport($group, ?bool $groupAll, $groupId, ?bool $residentAll, $residentId, $date, $dateFrom, $dateTo)
    {
        $type = $group;
        $typeId = $groupId;

        if (!\in_array($type, [ContractType::TYPE_FACILITY, ContractType::TYPE_REGION], false)) {
            throw new InvalidParameterException('group');
        }

        try {
            $physicians = $this->em->getRepository(Physician::class)->getPhysicianFullReport($type, $typeId);
        } catch (\Exception $e) {
            $physicians = [];
        }

        $report = new PhysicianFull();
        $report->setTitle('PHYSICIAN ROSTER, FULL');
        $report->setType(ContractType::getTypes()[$type]);
        $report->setPhysicians($physicians);

        return $report;
    }
}