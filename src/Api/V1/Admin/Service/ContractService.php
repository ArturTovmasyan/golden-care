<?php
namespace App\Api\V1\Admin\Service;

use App\Api\V1\Common\Service\BaseService;
use App\Api\V1\Common\Service\Exception\ApartmentBedNotFoundException;
use App\Api\V1\Common\Service\Exception\CareLevelNotFoundException;
use App\Api\V1\Common\Service\Exception\CityStateZipNotFoundException;
use App\Api\V1\Common\Service\Exception\ContractAlreadyExistException;
use App\Api\V1\Common\Service\Exception\DiningRoomNotFoundException;
use App\Api\V1\Common\Service\Exception\EndDateNotBeBlankException;
use App\Api\V1\Common\Service\Exception\FacilityBedNotFoundException;
use App\Api\V1\Common\Service\Exception\ContractNotFoundException;
use App\Api\V1\Common\Service\Exception\RegionNotFoundException;
use App\Api\V1\Common\Service\Exception\ResidentNotFoundException;
use App\Api\V1\Common\Service\Exception\StartGreaterEndDateException;
use App\Api\V1\Common\Service\IGridService;
use App\Entity\ApartmentBed;
use App\Entity\CareLevel;
use App\Entity\CityStateZip;
use App\Entity\ContractApartmentOption;
use App\Entity\ContractFacilityOption;
use App\Entity\ContractRegionOption;
use App\Entity\DiningRoom;
use App\Entity\FacilityBed;
use App\Entity\Region;
use App\Entity\Resident;
use App\Entity\Contract;
use App\Model\ContractState;
use App\Model\ContractType;
use Doctrine\ORM\QueryBuilder;

/**
 * Class ContractService
 * @package App\Api\V1\Admin\Service
 */
class ContractService extends BaseService implements IGridService
{
    /**
     * @param QueryBuilder $queryBuilder
     * @param $params
     * @return void
     */
    public function gridSelect(QueryBuilder $queryBuilder, $params)
    {
        if (empty($params) || empty($params[0]['resident_id'])) {
            throw new ResidentNotFoundException();
        }

        $residentId = $params[0]['resident_id'];

        $queryBuilder
            ->where('c.resident = :residentId')
            ->setParameter('residentId', $residentId);

        $this->em->getRepository(Contract::class)->search($queryBuilder);
    }

    public function list($params)
    {
        if (!empty($params) && !empty($params[0]['resident_id'])) {
            $residentId = $params[0]['resident_id'];

            return $this->em->getRepository(Contract::class)->findBy(['resident' => $residentId]);
        }

        throw new ResidentNotFoundException();
    }

    /**
     * @param $id
     * @return Contract|null|object
     */
    public function getById($id)
    {
        return $this->em->getRepository(Contract::class)->find($id);
    }

    /**
     * @param $type
     * @param $id
     * @param $state
     * @return mixed
     */
    public function getByTypeAndState($type, $id, $state)
    {
        return $this->em->getRepository(Contract::class)->getByTypeAndState($type, $id, $state);
    }

    /**
     * @param array $params
     * @throws \Exception
     */
    public function add(array $params) : void
    {
        try {
            $this->em->getConnection()->beginTransaction();

            $residentId = $params['resident_id'] ?? 0;

            $resident = null;

            if ($residentId && $residentId > 0) {
                /** @var Resident $resident */
                $resident = $this->em->getRepository(Resident::class)->find($residentId);


                if ($resident === null) {
                    throw new ResidentNotFoundException();
                }
            }

            /** @var Contract $activeContract */
            $activeContract = $this->em->getRepository(Contract::class)->findOneBy(['resident' => $residentId, 'end' => null]);

            if ($activeContract !== null) {
                throw new ContractAlreadyExistException();
            }

            $period = $params['period'] ? (int)$params['period'] : 0;
            $type = $params['type'] ? (int)$params['type'] : 0;

            $contract = new Contract();
            $contract->setResident($resident);
            $contract->setPeriod($period);
            $contract->setType($type);
            $contract->setEnd(null);

            $start = $params['start'];

            if (!empty($start)) {
                $start = new \DateTime($params['start']);
            }

            $contract->setStart($start);

            $this->validate($contract, null, ['api_admin_contract_add']);
            $this->em->persist($contract);

            $option = !empty($params['option']) ? $params['option'] : [];
            $editMode = false;

            switch ($contract->getType()) {
                case ContractType::TYPE_APARTMENT:
                    $option = $this->saveApartmentOption($contract, $option, $editMode);
                    break;
                case ContractType::TYPE_REGION:
                    $option = $this->saveRegionOption($contract, $option, $editMode);
                    break;
                default:
                    $option = $this->saveFacilityOption($contract, $option, $editMode);
            }

            $this->validate($option, null, ['api_admin_contract_add']);
            $this->em->persist($option);

            $this->em->flush();
            $this->em->getConnection()->commit();
        } catch (\Exception $e) {
            $this->em->getConnection()->rollBack();

            throw $e;
        }
    }

    /**
     * @param $id
     * @param array $params
     * @throws \Exception
     */
    public function edit($id, array $params) : void
    {
        try {

            $this->em->getConnection()->beginTransaction();

            /** @var Contract $entity */
            $entity = $this->em->getRepository(Contract::class)->find($id);

            if ($entity === null) {
                throw new ContractNotFoundException();
            }

            $residentId = $params['resident_id'] ?? 0;

            $resident = null;

            if ($residentId && $residentId > 0) {
                /** @var Resident $resident */
                $resident = $this->em->getRepository(Resident::class)->find($residentId);


                if ($resident === null) {
                    throw new ResidentNotFoundException();
                }
            }

            $period = $params['period'] ? (int)$params['period'] : 0;

            $entity->setResident($resident);
            $entity->setPeriod($period);

            $start = $params['start'];

            if (!empty($start)) {
                $start = new \DateTime($params['start']);
            }

            $entity->setStart($start);

            $end = $params['end'];

            if (!empty($end)) {
                $end = new \DateTime($params['end']);

                if ($start > $end) {
                    throw new StartGreaterEndDateException();
                }
            } else {
                $end = null;
            }

            $entity->setEnd($end);

            $this->validate($entity, null, ['api_admin_contract_edit']);
            $this->em->persist($entity);

            $option = !empty($params['option']) ? $params['option'] : [];
            $editMode = true;

            switch ($entity->getType()) {
                case ContractType::TYPE_APARTMENT:
                    $option = $this->saveApartmentOption($entity, $option, $editMode);
                    break;
                case ContractType::TYPE_REGION:
                    $option = $this->saveRegionOption($entity, $option, $editMode);
                    break;
                default:
                    $option = $this->saveFacilityOption($entity, $option, $editMode);
            }

            $this->validate($option, null, ['api_admin_contract_edit']);
            $this->em->persist($option);

            $this->em->flush();
            $this->em->getConnection()->commit();
        } catch (\Exception $e) {
            $this->em->getConnection()->rollBack();

            throw $e;
        }
    }

    /**
     * @param Contract $contract
     * @param array $params
     * @param boolean $editMode
     * @return ContractFacilityOption|null|object
     */
    private function saveFacilityOption(Contract $contract, array $params, bool $editMode)
    {
        /**
         * @var ContractFacilityOption $option
         * @var DiningRoom $diningRoom
         * @var FacilityBed $facilityBed
         * @var CareLevel $careLevel
         */
        $option = $this->em->getRepository(ContractFacilityOption::class)->findOneBy(['contract' => $contract]);

        if (!isset($params['dining_room_id']) || !$params['dining_room_id']) {
            throw new DiningRoomNotFoundException();
        }

        if (!isset($params['bed_id']) || !$params['bed_id']) {
            throw new FacilityBedNotFoundException();
        }

        if (!isset($params['care_level_id']) || !$params['care_level_id']) {
            throw new CareLevelNotFoundException();
        }

        $diningRoom = $this->em->getRepository(DiningRoom::class)->find($params['dining_room_id']);
        $facilityBed = $this->em->getRepository(FacilityBed::class)->find($params['bed_id']);
        $careLevel = $this->em->getRepository(CareLevel::class)->find($params['care_level_id']);

        if ($diningRoom === null) {
            throw new DiningRoomNotFoundException();
        }

        if ($facilityBed === null) {
            throw new FacilityBedNotFoundException();
        }

        if ($careLevel === null) {
            throw new CareLevelNotFoundException();
        }

        if ($option === null) {
            $option = new ContractFacilityOption();
            $option->setContract($contract);
        }

        if ($editMode) {
            $state = isset($params['state']) ? (int)$params['state'] : 0;

            $option->setState($state);

            if ($option->getState() === ContractState::TERMINATED && $contract->getEnd() === null) {
                throw new EndDateNotBeBlankException();
            }
        } else {
            $option->setState(ContractState::ACTIVE);
        }

        $option->setDiningRoom($diningRoom);
        $option->setFacilityBed($facilityBed);
        $option->setDnr($params['dnr'] ?? false);
        $option->setPolst($params['polst'] ?? false);
        $option->setAmbulatory($params['ambulatory'] ?? false);
        $option->setCareGroup($params['care_group'] ? (int)$params['care_group'] : 0);
        $option->setCareLevel($careLevel);

        return $option;
    }

    /**
     * @param Contract $contract
     * @param array $params
     * @param boolean $editMode
     * @return ContractApartmentOption|null|object
     */
    private function saveApartmentOption(Contract $contract, array $params, bool $editMode)
    {
        /**
         * @var ContractApartmentOption $option
         * @var ApartmentBed $apartmentBed
         */
        $option = $this->em->getRepository(ContractApartmentOption::class)->findOneBy(['contract' => $contract]);

        if (!isset($params['bed_id']) || !$params['bed_id']) {
            throw new ApartmentBedNotFoundException();
        }

        $apartmentBed = $this->em->getRepository(ApartmentBed::class)->find($params['bed_id']);

        if ($apartmentBed === null) {
            throw new ApartmentBedNotFoundException();
        }

        if ($option === null) {
            $option = new ContractApartmentOption();
            $option->setContract($contract);
        }

        if ($editMode) {
            $state = isset($params['state']) ? (int)$params['state'] : 0;

            $option->setState($state);

            if ($option->getState() === ContractState::TERMINATED && $contract->getEnd() === null) {
                throw new EndDateNotBeBlankException();
            }
        } else {
            $option->setState(ContractState::ACTIVE);
        }

        $option->setApartmentBed($apartmentBed);

        return $option;
    }

    /**
     * @param Contract $contract
     * @param array $params
     * @param boolean $editMode
     * @return ContractRegionOption|null|object
     */
    private function saveRegionOption(Contract $contract, array $params, bool $editMode)
    {
        /**
         * @var ContractRegionOption $option
         * @var Region $region
         * @var CityStateZip $csz
         * @var CareLevel $careLevel
         */
        $option = $this->em->getRepository(ContractRegionOption::class)->findOneBy(['contract' => $contract]);

        if (!isset($params['region_id']) || !$params['region_id']) {
            throw new RegionNotFoundException();
        }

        if (!isset($params['csz_id']) || !$params['csz_id']) {
            throw new FacilityBedNotFoundException();
        }

        if (!isset($params['care_level_id']) || !$params['care_level_id']) {
            throw new CareLevelNotFoundException();
        }

        $region = $this->em->getRepository(Region::class)->find($params['region_id']);
        $csz = $this->em->getRepository(CityStateZip::class)->find($params['csz_id']);
        $careLevel = $this->em->getRepository(CareLevel::class)->find($params['care_level_id']);

        if ($region === null) {
            throw new RegionNotFoundException();
        }

        if ($csz === null) {
            throw new CityStateZipNotFoundException();
        }

        if ($careLevel === null) {
            throw new CareLevelNotFoundException();
        }

        if ($option === null) {
            $option = new ContractRegionOption();
            $option->setContract($contract);
        }

        if ($editMode) {
            $state = isset($params['state']) ? (int)$params['state'] : 0;

            $option->setState($state);

            if ($option->getState() === ContractState::TERMINATED && $contract->getEnd() === null) {
                throw new EndDateNotBeBlankException();
            }
        } else {
            $option->setState(ContractState::ACTIVE);
        }

        $option->setRegion($region);
        $option->setCsz($csz);
        $option->setAddress($params['address']);
        $option->setDnr($params['dnr'] ?? false);
        $option->setPolst($params['polst'] ?? false);
        $option->setAmbulatory($params['ambulatory'] ?? false);
        $option->setCareGroup($params['care_group'] ? (int)$params['care_group'] : 0);
        $option->setCareLevel($careLevel);

        return $option;
    }

    /**
     * @param $id
     * @throws \Doctrine\DBAL\ConnectionException
     * @throws \Throwable
     */
    public function remove($id)
    {
        try {
            $this->em->getConnection()->beginTransaction();

            /** @var Contract $entity */
            $entity = $this->em->getRepository(Contract::class)->find($id);

            if ($entity === null) {
                throw new ContractNotFoundException();
            }

            $this->em->remove($entity);
            $this->em->flush();
            $this->em->getConnection()->commit();
        } catch (\Throwable $e) {
            $this->em->getConnection()->rollBack();

            throw $e;
        }
    }

    /**
     * @param array $ids
     * @throws \Doctrine\DBAL\ConnectionException
     * @throws \Throwable
     */
    public function removeBulk(array $ids): void
    {
        try {
            if (empty($ids)) {
                throw new ContractNotFoundException();
            }

            $contracts = $this->em->getRepository(Contract::class)->findByIds($ids);

            if (empty($contracts)) {
                throw new ContractNotFoundException();
            }

            /**
             * @var Contract $contract
             */
            $this->em->getConnection()->beginTransaction();

            foreach ($contracts as $contract) {
                $this->em->remove($contract);
            }

            $this->em->flush();
            $this->em->getConnection()->commit();
        } catch (ContractNotFoundException $e) {
            throw $e;
        } catch (\Throwable $e) {
            $this->em->getConnection()->rollBack();

            throw $e;
        }
    }
}
