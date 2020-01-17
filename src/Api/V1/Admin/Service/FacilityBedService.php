<?php

namespace App\Api\V1\Admin\Service;

use App\Api\V1\Common\Service\BaseService;
use App\Api\V1\Common\Service\Exception\FacilityBedNotFoundException;
use App\Api\V1\Common\Service\IGridService;
use App\Entity\Facility;
use App\Entity\FacilityBed;
use App\Entity\ResidentAdmission;
use App\Model\GroupType;
use App\Repository\FacilityBedRepository;
use App\Repository\ResidentAdmissionRepository;
use Doctrine\ORM\QueryBuilder;

/**
 * Class FacilityBedService
 * @package App\Api\V1\Admin\Service
 */
class FacilityBedService extends BaseService implements IGridService
{
    /**
     * @param QueryBuilder $queryBuilder
     * @param $params
     */
    public function gridSelect(QueryBuilder $queryBuilder, $params): void
    {
        $facilityId = null;
        if (!empty($params) || !empty($params[0]['facility_id'])) {
            $facilityId = $params[0]['facility_id'];
        }

        /** @var FacilityBedRepository $repo */
        $repo = $this->em->getRepository(FacilityBed::class);

        $repo->search($this->grantService->getCurrentSpace(), $this->grantService->getCurrentUserEntityGrants(FacilityBed::class), $this->grantService->getCurrentUserEntityGrants(Facility::class), $queryBuilder, $facilityId);
    }

    /**
     * @param $params
     * @return mixed
     */
    public function list($params)
    {
        $currentSpace = $this->grantService->getCurrentSpace();

        $facilityId = null;
        if (!empty($params) || !empty($params[0]['facility_id'])) {
            $facilityId = $params[0]['facility_id'];
        }

        /** @var FacilityBedRepository $repo */
        $repo = $this->em->getRepository(FacilityBed::class);

        $beds = $repo->list($currentSpace, $this->grantService->getCurrentUserEntityGrants(FacilityBed::class), $this->grantService->getCurrentUserEntityGrants(Facility::class), $facilityId);

        if (!empty($beds)) {
            $bedIds = array_map(function (FacilityBed $item) {
                return $item->getId();
            }, $beds);

            /** @var ResidentAdmissionRepository $admissionRepo */
            $admissionRepo = $this->em->getRepository(ResidentAdmission::class);

            $residentAdmissions = $admissionRepo->getResidentsByBeds($currentSpace, $this->grantService->getCurrentUserEntityGrants(ResidentAdmission::class), GroupType::TYPE_FACILITY, $bedIds);

            $admissions = [];
            if (!empty($residentAdmissions)) {
                foreach ($residentAdmissions as $residentAdmission) {
                    $admissions[$residentAdmission['bedId']] = $residentAdmission['admission']->getResident();
                }
            }

            /** @var FacilityBed $bed */
            foreach ($beds as $bed) {
                if (!empty($admissions[$bed->getId()])) {
                    $bed->setResident($admissions[$bed->getId()]);
                }
            }
        }

        return $beds;
    }

    /**
     * @param array $ids
     * @return array
     */
    public function getRelatedInfo(array $ids): array
    {
        if (empty($ids)) {
            throw new FacilityBedNotFoundException();
        }

        /** @var FacilityBedRepository $repo */
        $repo = $this->em->getRepository(FacilityBed::class);

        $entities = $repo->findByIds($this->grantService->getCurrentSpace(), $this->grantService->getCurrentUserEntityGrants(FacilityBed::class), $ids);

        if (empty($entities)) {
            throw new FacilityBedNotFoundException();
        }

        return $this->getRelatedData(FacilityBed::class, $entities);
    }
}
