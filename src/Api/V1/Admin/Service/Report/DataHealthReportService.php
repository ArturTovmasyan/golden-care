<?php

namespace App\Api\V1\Admin\Service\Report;

use App\Api\V1\Common\Service\BaseService;
use App\Entity\Facility;
use App\Entity\FacilityRoomBaseRate;
use App\Entity\FacilityRoomBaseRateCareLevel;
use App\Entity\FacilityRoomType;
use App\Entity\Resident;
use App\Entity\ResidentRent;
use App\Entity\ResidentResponsiblePerson;
use App\Model\GroupType;
use App\Model\Report\InvalidRentAmount;
use App\Model\Report\MissingRentRecords;
use App\Model\Report\ResidentRps;
use App\Repository\FacilityRoomTypeRepository;
use App\Repository\ResidentRentRepository;
use App\Repository\ResidentRepository;
use App\Repository\ResidentResponsiblePersonRepository;
use Symfony\Component\Routing\Exception\InvalidParameterException;

class DataHealthReportService extends BaseService
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
     * @param $discontinued
     * @return ResidentRps
     */
    public function getResidentRpsReport($group, ?bool $groupAll, $groupId, ?bool $residentAll, $residentId, $date, $dateFrom, $dateTo, $assessmentId, $assessmentFormId, $discontinued): ResidentRps
    {
        $currentSpace = $this->grantService->getCurrentSpace();

        $type = $group;
        $typeId = $groupId;

        if (!\in_array($type, GroupType::getTypeValues(), false)) {
            throw new InvalidParameterException('group');
        }

        /** @var ResidentRepository $repo */
        $repo = $this->em->getRepository(Resident::class);

        $residents = $repo->getAdmissionResidentsFullInfoByTypeOrId($currentSpace, $this->grantService->getCurrentUserEntityGrants(Resident::class), $type, $typeId, $residentId, $this->getNotGrantResidentIds());
        $residentIds = array_map(function ($item) {
            return $item['id'];
        }, $residents);

        /** @var ResidentResponsiblePersonRepository $responsiblePersonRepo */
        $responsiblePersonRepo = $this->em->getRepository(ResidentResponsiblePerson::class);

        $responsiblePersons = $responsiblePersonRepo->getResponsiblePersonByResidentIds($currentSpace, $this->grantService->getCurrentUserEntityGrants(ResidentResponsiblePerson::class), $residentIds);

        $responsiblePersonResidentIds = [];
        if (!empty($responsiblePersons)) {
            $responsiblePersonResidentIds = array_map(function ($item) {
                return $item->getResident()->getId();
            }, $responsiblePersons);
            $responsiblePersonResidentIds = array_unique($responsiblePersonResidentIds);
        }

        $residentsById = [];
        foreach ($residents as $resident) {
            if (!\in_array($resident['id'], $responsiblePersonResidentIds, false)) {
                $residentsById[$resident['id']] = $resident;
            }
        }

        $report = new ResidentRps();
        $report->setStrategy(GroupType::getTypes()[$type]);
        $report->setResidents($residentsById);
        $report->setStrategyId($type);

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
     * @param $discontinued
     * @return MissingRentRecords
     */
    public function getMissingRentRecordsReport($group, ?bool $groupAll, $groupId, ?bool $residentAll, $residentId, $date, $dateFrom, $dateTo, $assessmentId, $assessmentFormId, $discontinued): MissingRentRecords
    {
        $currentSpace = $this->grantService->getCurrentSpace();

        $type = $group;
        $typeId = $groupId;

        if (!\in_array($type, GroupType::getTypeValues(), false)) {
            throw new InvalidParameterException('group');
        }

        /** @var ResidentRentRepository $rentRepo */
        $rentRepo = $this->em->getRepository(ResidentRent::class);

        $rents = $rentRepo->getByActiveResidents($currentSpace, $this->grantService->getCurrentUserEntityGrants(ResidentRent::class), $type);

        $modifiedRents = [];
        $rentResidentIds = [];
        foreach ($rents as $rent) {
            $rentResidentIds[] = $rent['id'];

            $modifiedRents[$rent['id']][] = [
                'id' => $rent['rentId'],
                'start' => $rent['start'],
                'end' => $rent['end'],
            ];
        }

        $rentResidentIds = array_unique($rentResidentIds);

        $now = new \DateTime('now');
        $nowFormatted = $now->format('Y-m-d');

        $endDateInThePastIds = [];
        foreach ($modifiedRents as $key => $modifiedRent) {
            $isEndDateInThePast = false;
            foreach ($modifiedRent as $rent) {
                if ($rent['end'] !== null && $rent['end']->format('Y-m-d') < $nowFormatted) {
                    $isEndDateInThePast = true;
                } else {
                    $isEndDateInThePast = false;
                    break;
                }
            }

            if ($isEndDateInThePast) {
                $endDateInThePastIds[] = $key;
            }
        }

        $moreThanOneEndDateNullIds = [];
        foreach ($modifiedRents as $key => $modifiedRent) {
            $countMoreThanOneEndDateNull = 0;
            foreach ($modifiedRent as $rent) {
                if ($rent['end'] === null) {
                    ++$countMoreThanOneEndDateNull;
                }
            }

            if ($countMoreThanOneEndDateNull > 1) {
                $moreThanOneEndDateNullIds[] = $key;
            }
        }

        $overlapIds = [];
        foreach ($modifiedRents as $key => $modifiedRent) {
            $isOverlap = false;
            if (\count($modifiedRent) > 1) {
                foreach ($modifiedRent as $rentKey => $rent) {
                    if (array_key_exists($rentKey + 1, $modifiedRent) && $modifiedRent[$rentKey + 1]['start'] >= $modifiedRent[$rentKey]['start'] && ($modifiedRent[$rentKey]['end'] === null ||
                            ($modifiedRent[$rentKey]['end'] !== null && $modifiedRent[$rentKey + 1]['start'] <= $modifiedRent[$rentKey]['start']))) {
                        $isOverlap = true;
                        break;
                    }

                    if (array_key_exists($rentKey + 1, $modifiedRent) && $modifiedRent[$rentKey]['end'] !== null && $modifiedRent[$rentKey]['end'] >= $modifiedRent[$rentKey + 1]['start'] &&
                            ($modifiedRent[$rentKey+1]['end'] === null || ($modifiedRent[$rentKey+1]['end'] !== null && $modifiedRent[$rentKey]['end'] <= $modifiedRent[$rentKey+1]['end']))) {
                        $isOverlap = true;
                        break;
                    }
                }
            }

            if ($isOverlap && !\in_array($key, $moreThanOneEndDateNullIds, false)) {
                $overlapIds[] = $key;
            }
        }

        /** @var ResidentRepository $repo */
        $repo = $this->em->getRepository(Resident::class);

        $residents = $repo->getAdmissionResidentsFullInfoByTypeOrId($currentSpace, $this->grantService->getCurrentUserEntityGrants(Resident::class), $type, $typeId, $residentId, $this->getNotGrantResidentIds());

        $report = new MissingRentRecords();
        $report->setStrategy(GroupType::getTypes()[$type]);
        $report->setResidents($residents);
        $report->setRentResidentIds($rentResidentIds);
        $report->setEndDateInThePastIds($endDateInThePastIds);
        $report->setMoreThanOneEndDateNullIds($moreThanOneEndDateNullIds);
        $report->setOverlapIds($overlapIds);
        $report->setStrategyId($type);

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
     * @param $discontinued
     * @return InvalidRentAmount
     */
    public function getInvalidRentAmountReport($group, ?bool $groupAll, $groupId, ?bool $residentAll, $residentId, $date, $dateFrom, $dateTo, $assessmentId, $assessmentFormId, $discontinued): InvalidRentAmount
    {
        $currentSpace = $this->grantService->getCurrentSpace();

        $type = $group;
        $typeId = $groupId;

        if (!\in_array($type, GroupType::getTypeValues(), false)) {
            throw new InvalidParameterException('group');
        }

        /** @var ResidentRentRepository $rentRepo */
        $rentRepo = $this->em->getRepository(ResidentRent::class);

        $residentRents = $rentRepo->getZeroAmountResidentRents($currentSpace, $this->grantService->getCurrentUserEntityGrants(ResidentRent::class), $type, $typeId);

        $activeResidentRents = [];
        $averageRent = [];
        if ((int)$type === GroupType::TYPE_FACILITY) {
            $activeResidents = $rentRepo->getMoreThanZeroAmountActiveResidentRents($currentSpace, $this->grantService->getCurrentUserEntityGrants(ResidentRent::class), $type, $typeId);

            $roomTypeIds = array_map(function ($item) {
                return $item['roomTypeId'];
            }, $activeResidents);

            $roomTypeIds = array_unique($roomTypeIds);

            /** @var FacilityRoomTypeRepository $roomTypeRepo */
            $roomTypeRepo = $this->em->getRepository(FacilityRoomType::class);

            $roomTypes = $roomTypeRepo->findByIdsWithRates($currentSpace, $this->grantService->getCurrentUserEntityGrants(FacilityRoomType::class), $this->grantService->getCurrentUserEntityGrants(Facility::class), $roomTypeIds);

            $roomTypeAverageRent = [];
            /** @var FacilityRoomType $roomType */
            foreach ($roomTypes as $roomType) {
                $roomTypeId = $roomType->getId();

                /** @var FacilityRoomBaseRate $baseRate */
                $baseRate = $roomType->getBaseRates()[0];

                $count = \count($baseRate->getLevels());
                /** @var FacilityRoomBaseRateCareLevel $level */
                foreach ($baseRate->getLevels() as $key => $level) {
                    $roomTypeAverageRent[$roomTypeId] = array_key_exists($roomTypeId, $roomTypeAverageRent) ? $roomTypeAverageRent[$roomTypeId] + $level->getAmount() : $level->getAmount();

                    if ($count > 0 && $key + 1 === $count) {
                        $roomTypeAverageRent[$roomTypeId] /= $count;
                    }
                }
            }

            foreach ($roomTypeIds as $roomTypeId) {
                $averageRent[$roomTypeId] = array_key_exists($roomTypeId, $roomTypeAverageRent) ? $roomTypeAverageRent[$roomTypeId] : 0;
            }

            foreach ($activeResidents as $activeResident) {
                $roomTypeId = $activeResident['roomTypeId'];
                $amount = $activeResident['amount'];

                if ($amount <= $averageRent[$roomTypeId] * 0.7) {
                    $activeResidentRents[] = $activeResident;
                }
            }
        }

        $finalResidents = array_merge($residentRents, $activeResidentRents);

        $report = new InvalidRentAmount();
        $report->setStrategy(GroupType::getTypes()[$type]);
        $report->setResidents($finalResidents);
        $report->setAverageRent($averageRent);
        $report->setStrategyId($type);

        return $report;
    }
}