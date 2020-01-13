<?php

namespace App\Api\V1\Admin\Service\Report;

use App\Api\V1\Common\Service\BaseService;
use App\Entity\PhysicianPhone;
use App\Entity\Resident;
use App\Entity\ResidentPhone;
use App\Entity\ResidentPhysician;
use App\Model\GroupType;
use App\Model\Report\PhysicianFull;
use App\Model\Report\PhysicianSimple;
use App\Model\Report\ResidentsByPhysician;
use App\Repository\PhysicianPhoneRepository;
use App\Repository\ResidentPhoneRepository;
use App\Repository\ResidentPhysicianRepository;
use App\Repository\ResidentRepository;
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
     * @param $assessmentId
     * @param $assessmentFormId
     * @return PhysicianSimple
     */
    public function getSimpleReport($group, ?bool $groupAll, $groupId, ?bool $residentAll, $residentId, $date, $dateFrom, $dateTo, $assessmentId, $assessmentFormId): PhysicianSimple
    {
        $currentSpace = $this->grantService->getCurrentSpace();

        $type = $group;
        $typeId = $groupId;

        if (!\in_array($type, [GroupType::TYPE_FACILITY, GroupType::TYPE_REGION], false)) {
            throw new InvalidParameterException('group');
        }

        /** @var ResidentRepository $repo */
        $repo = $this->em->getRepository(Resident::class);

        $residents = $repo->getAdmissionResidentsInfoByTypeOrId($currentSpace, $this->grantService->getCurrentUserEntityGrants(Resident::class), $type, $typeId, null, $this->getNotGrantResidentIds());
        $residentIds = [];

        if (!empty($residents)) {
            $residentIds = array_map(function ($item) {
                return $item['id'];
            }, $residents);
            $residentIds = array_unique($residentIds);
        }

        /** @var ResidentPhysicianRepository $physicianRepo */
        $physicianRepo = $this->em->getRepository(ResidentPhysician::class);

        $physicians = $physicianRepo->getByAdmissionResidentIds($currentSpace, $this->grantService->getCurrentUserEntityGrants(ResidentPhysician::class), $type, $residentIds);

        $data = [];
        $count = [];
        $typeIds = [];
        if (!empty($physicians)) {
            foreach ($physicians as $physician) {
                foreach ($residents as $resident) {
                    if ($resident['typeId'] === $physician['typeId'] && $resident['id'] === $physician['residentId']) {
                        $count[$physician['typeId']][$physician['pId']] = isset($count[$physician['typeId']][$physician['pId']]) ? $count[$physician['typeId']][$physician['pId']] + 1 : 1;
                    }
                }

                $k = $physician['typeId'] . $physician['pId'];
                if (!isset($data[$k])) {
                    $data[$k] = $physician;
                }
            }

            $data = array_values($data);

            $typeIds = array_map(function ($item) {
                return $item['typeId'];
            }, $data);
            $typeIds = array_unique($typeIds);
        }

        $report = new PhysicianSimple();
        $report->setData($data);
        $report->setTypeIds($typeIds);
        $report->setCount($count);
        $report->setStrategy(GroupType::getTypes()[$type]);

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
     * @param $assessmentId
     * @param $assessmentFormId
     * @return PhysicianFull
     */
    public function getFullReport($group, ?bool $groupAll, $groupId, ?bool $residentAll, $residentId, $date, $dateFrom, $dateTo, $assessmentId, $assessmentFormId): PhysicianFull
    {
        $currentSpace = $this->grantService->getCurrentSpace();

        $type = $group;
        $typeId = $groupId;

        if (!\in_array($type, [GroupType::TYPE_FACILITY, GroupType::TYPE_REGION], false)) {
            throw new InvalidParameterException('group');
        }

        /** @var ResidentRepository $repo */
        $repo = $this->em->getRepository(Resident::class);

        $residents = $repo->getAdmissionResidentsInfoByTypeOrId($currentSpace, $this->grantService->getCurrentUserEntityGrants(Resident::class), $type, $typeId, null, $this->getNotGrantResidentIds());
        $residentIds = [];

        if (!empty($residents)) {
            $residentIds = array_map(function ($item) {
                return $item['id'];
            }, $residents);
            $residentIds = array_unique($residentIds);
        }

        /** @var ResidentPhysicianRepository $physicianRepo */
        $physicianRepo = $this->em->getRepository(ResidentPhysician::class);

        $physicians = $physicianRepo->getByAdmissionResidentIds($currentSpace, $this->grantService->getCurrentUserEntityGrants(ResidentPhysician::class), $type, $residentIds);

        $data = [];
        $count = [];
        $physicianPhones = [];
        if (!empty($physicians)) {
            $physicianIds = array_map(function ($item) {
                return $item['pId'];
            }, $physicians);
            $physicianIds = array_unique($physicianIds);

            /** @var PhysicianPhoneRepository $physicianPhoneRepo */
            $physicianPhoneRepo = $this->em->getRepository(PhysicianPhone::class);

            $physicianPhones = $physicianPhoneRepo->getByPhysicianIds($currentSpace, $this->grantService->getCurrentUserEntityGrants(PhysicianPhone::class), $physicianIds);

            foreach ($physicians as $physician) {
                foreach ($residents as $resident) {
                    if ($resident['typeId'] === $physician['typeId'] && $resident['id'] === $physician['residentId']) {
                        $count[$physician['typeId']][$physician['pId']] = isset($count[$physician['typeId']][$physician['pId']]) ? $count[$physician['typeId']][$physician['pId']] + 1 : 1;
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
        $report->setStrategy(GroupType::getTypes()[$type]);
        $report->setPhysicianPhones($physicianPhones);

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
     * @param $assessmentId
     * @param $assessmentFormId
     * @return ResidentsByPhysician
     */
    public function getResidentsByPhysicianReport($group, ?bool $groupAll, $groupId, ?bool $residentAll, $residentId, $date, $dateFrom, $dateTo, $assessmentId, $assessmentFormId): ResidentsByPhysician
    {
        $currentSpace = $this->grantService->getCurrentSpace();

        $type = $group;
        $typeId = $groupId;

        if (!\in_array($type, [GroupType::TYPE_FACILITY, GroupType::TYPE_REGION], false)) {
            throw new InvalidParameterException('group');
        }

        /** @var ResidentRepository $repo */
        $repo = $this->em->getRepository(Resident::class);

        $residents = $repo->getAdmissionResidentsInfoByTypeOrId($currentSpace, $this->grantService->getCurrentUserEntityGrants(Resident::class), $type, $typeId, null, $this->getNotGrantResidentIds());
        $residentIds = [];

        $finalResidents = [];
        if (!empty($residents)) {
            $residentIds = array_map(function ($item) {
                return $item['id'];
            }, $residents);
            $residentIds = array_unique($residentIds);

            /** @var ResidentPhoneRepository $phoneRepo */
            $phoneRepo = $this->em->getRepository(ResidentPhone::class);
            $phones = $phoneRepo->getByResidentIds($currentSpace, $this->grantService->getCurrentUserEntityGrants(ResidentPhone::class), $residentIds);

            foreach ($residents as $resident) {
                $resident['phones'] = null;

                foreach ($phones as $phone) {
                    if ($phone['rId'] === $resident['id']) {
                        $resident['phones'][] = $phone;
                    }
                }

                $finalResidents[] = $resident;
            }
        }

        /** @var ResidentPhysicianRepository $physicianRepo */
        $physicianRepo = $this->em->getRepository(ResidentPhysician::class);

        $physicians = $physicianRepo->getByAdmissionResidentIds($currentSpace, $this->grantService->getCurrentUserEntityGrants(ResidentPhysician::class), $type, $residentIds);

        $data = [];
        $count = [];
        $physicianResidents = [];
        $physicianPhones = [];
        if (!empty($physicians)) {
            $physicianIds = array_map(function ($item) {
                return $item['pId'];
            }, $physicians);
            $physicianIds = array_unique($physicianIds);

            /** @var PhysicianPhoneRepository $physicianPhoneRepo */
            $physicianPhoneRepo = $this->em->getRepository(PhysicianPhone::class);

            $physicianPhones = $physicianPhoneRepo->getByPhysicianIds($currentSpace, $this->grantService->getCurrentUserEntityGrants(PhysicianPhone::class), $physicianIds);

            foreach ($physicians as $physician) {
                foreach ($finalResidents as $resident) {
                    if ($resident['typeId'] === $physician['typeId'] && $resident['id'] === $physician['residentId']) {
                        $count[$physician['typeId']][$physician['pId']] = isset($count[$physician['typeId']][$physician['pId']]) ? $count[$physician['typeId']][$physician['pId']] + 1 : 1;

                        if (array_key_exists('roomNumber', $resident) && array_key_exists('bedNumber', $resident)) {
                            if ($resident['private']) {
                                $number = $resident['roomNumber'] . ' ';
                            } else {
                                $number = $resident['roomNumber'] . ' ' . $resident['bedNumber'];
                            }
                        } else {
                            $number = null;
                        }

                        $physicianResidents[$physician['typeId']][$physician['pId']][] = [
                            'id' => $resident['id'],
                            'fullName' => $resident['firstName'] . ' ' . $resident['lastName'],
                            'number' => $number,
                            'phones' => $resident['phones'],
                        ];
                    }
                }

                $k = $physician['typeId'] . $physician['pId'];
                if (!isset($data[$k])) {
                    $data[$k] = $physician;
                }
            }

            $data = array_values($data);

            $typeNames = array_map(function ($item) {
                return $item['typeName'];
            }, $data);
            array_multisort($typeNames, SORT_ASC, $data);
        }

        $report = new ResidentsByPhysician();
        $report->setTitle('RESIDENTS BY PHYSICIAN');
        $report->setData($data);
        $report->setCount($count);
        $report->setResidents($physicianResidents);
        $report->setStrategy(GroupType::getTypes()[$type]);
        $report->setPhysicianPhones($physicianPhones);

        return $report;
    }
}