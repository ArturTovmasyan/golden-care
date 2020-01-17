<?php

namespace App\Api\V1\Admin\Service;

use App\Api\V1\Common\Service\BaseService;
use App\Api\V1\Common\Service\Exception\ApartmentBedNotFoundException;
use App\Api\V1\Common\Service\IGridService;
use App\Entity\Apartment;
use App\Entity\ApartmentBed;
use App\Entity\ResidentAdmission;
use App\Model\GroupType;
use App\Repository\ApartmentBedRepository;
use App\Repository\ResidentAdmissionRepository;
use Doctrine\ORM\QueryBuilder;

/**
 * Class ApartmentBedService
 * @package App\Api\V1\Admin\Service
 */
class ApartmentBedService extends BaseService implements IGridService
{
    /**
     * @param QueryBuilder $queryBuilder
     * @param $params
     */
    public function gridSelect(QueryBuilder $queryBuilder, $params): void
    {
        $apartmentId = null;
        if (!empty($params) || !empty($params[0]['apartment_id'])) {
            $apartmentId = $params[0]['apartment_id'];
        }

        /** @var ApartmentBedRepository $repo */
        $repo = $this->em->getRepository(ApartmentBed::class);

        $repo->search($this->grantService->getCurrentSpace(), $this->grantService->getCurrentUserEntityGrants(ApartmentBed::class), $this->grantService->getCurrentUserEntityGrants(Apartment::class), $queryBuilder, $apartmentId);
    }

    /**
     * @param $params
     * @return mixed
     */
    public function list($params)
    {
        $currentSpace = $this->grantService->getCurrentSpace();

        $apartmentId = null;
        if (!empty($params) || !empty($params[0]['apartment_id'])) {
            $apartmentId = $params[0]['apartment_id'];
        }

        /** @var ApartmentBedRepository $repo */
        $repo = $this->em->getRepository(ApartmentBed::class);

        $beds = $repo->list($currentSpace, $this->grantService->getCurrentUserEntityGrants(ApartmentBed::class), $this->grantService->getCurrentUserEntityGrants(Apartment::class), $apartmentId);

        if (!empty($beds)) {
            $bedIds = array_map(function (ApartmentBed $item) {
                return $item->getId();
            }, $beds);

            /** @var ResidentAdmissionRepository $admissionRepo */
            $admissionRepo = $this->em->getRepository(ResidentAdmission::class);

            $residentAdmissions = $admissionRepo->getResidentsByBeds($currentSpace, $this->grantService->getCurrentUserEntityGrants(ResidentAdmission::class), GroupType::TYPE_APARTMENT, $bedIds);

            $admissions = [];
            if (!empty($residentAdmissions)) {
                foreach ($residentAdmissions as $residentAdmission) {
                    $admissions[$residentAdmission['bedId']] = $residentAdmission['admission']->getResident();
                }
            }

            /** @var ApartmentBed $bed */
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
            throw new ApartmentBedNotFoundException();
        }

        /** @var ApartmentBedRepository $repo */
        $repo = $this->em->getRepository(ApartmentBed::class);

        $entities = $repo->findByIds($this->grantService->getCurrentSpace(), $this->grantService->getCurrentUserEntityGrants(ApartmentBed::class), $ids);

        if (empty($entities)) {
            throw new ApartmentBedNotFoundException();
        }

        return $this->getRelatedData(ApartmentBed::class, $entities);
    }
}
