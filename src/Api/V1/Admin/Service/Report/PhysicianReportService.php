<?php

namespace App\Api\V1\Admin\Service\Report;

use App\Api\V1\Common\Service\BaseService;
use App\Entity\Resident;
use App\Entity\ResidentPhysician;
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

        $residents = $this->em->getRepository(Resident::class)->getResidentsInfoByTypeOrId($type, $typeId);
        $residentIds = [];

        if (!empty($residents)) {
            $residentIds = array_map(function($item){return $item['id'];} , $residents);
            $residentIds = array_unique($residentIds);
        }

        $physicians = $this->em->getRepository(ResidentPhysician::class)->getByResidentIds($type, $residentIds);

        $data = [];
        $count = [];
        $typeIds = [];
        if (!empty($physicians)) {
            foreach ($physicians as $physician) {
                foreach ($residents as $resident) {
                    if ($resident['typeId'] === $physician['typeId'] &&  $resident['id'] === $physician['residentId']) {
                        $count[$physician['typeId']][$physician['pId']] = isset($count[$physician['typeId']][$physician['pId']]) ? \count($count[$physician['typeId']][$physician['pId']]) + \count($resident['id']) : \count($resident['id']);
                    }
                }

                $k = $physician['typeId'] . $physician['pId'];
                if (!isset($data[$k])) {
                    $data[$k] = $physician;
                }
            }

            $data = array_values($data);

            $typeIds = array_map(function($item){return $item['typeId'];} , $data);
            $typeIds = array_unique($typeIds);
        }

        $report = new PhysicianSimple();
        $report->setData($data);
        $report->setTypeIds($typeIds);
        $report->setCount($count);
        $report->setStrategy(ContractType::getTypes()[$type]);

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

        $residents = $this->em->getRepository(Resident::class)->getResidentsInfoByTypeOrId($type, $typeId);
        $residentIds = [];

        if (!empty($residents)) {
            $residentIds = array_map(function($item){return $item['id'];} , $residents);
            $residentIds = array_unique($residentIds);
        }

        $physicians = $this->em->getRepository(ResidentPhysician::class)->getByResidentIds($type, $residentIds);

        $data = [];
        $count = [];
        if (!empty($physicians)) {
            foreach ($physicians as $physician) {
                foreach ($residents as $resident) {
                    if ($resident['typeId'] === $physician['typeId'] &&  $resident['id'] === $physician['residentId']) {
                        $count[$physician['typeId']][$physician['pId']] = isset($count[$physician['typeId']][$physician['pId']]) ? \count($count[$physician['typeId']][$physician['pId']]) + \count($resident['id']) : \count($resident['id']);
                    }
                }

                $k = $physician['typeId'] . $physician['pId'];
                if (!isset($data[$k])) {
                    $data[$k] = $physician;
                }
            }

            $data = array_values($data);
        }

        $report = new PhysicianFull();
        $report->setTitle('PHYSICIAN ROSTER, FULL');
        $report->setData($data);
        $report->setCount($count);
        $report->setStrategy(ContractType::getTypes()[$type]);

        return $report;
    }
}