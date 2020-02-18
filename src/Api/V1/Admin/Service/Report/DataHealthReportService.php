<?php

namespace App\Api\V1\Admin\Service\Report;

use App\Api\V1\Common\Service\BaseService;
use App\Entity\Resident;
use App\Entity\ResidentResponsiblePerson;
use App\Model\GroupType;
use App\Model\Report\ResidentRps;
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
}