<?php
namespace App\Api\V1\Admin\Service;

use App\Api\V1\Common\Service\BaseService;
use App\Api\V1\Common\Service\Exception\ApartmentBedNotFoundException;
use App\Api\V1\Common\Service\Exception\CareLevelNotFoundException;
use App\Api\V1\Common\Service\Exception\CityStateZipNotFoundException;
use App\Api\V1\Common\Service\Exception\ContractActionNotFoundException;
use App\Api\V1\Common\Service\Exception\ContractAlreadyExistException;
use App\Api\V1\Common\Service\Exception\DiningRoomNotFoundException;
use App\Api\V1\Common\Service\Exception\EndDateNotBeBlankException;
use App\Api\V1\Common\Service\Exception\FacilityBedNotFoundException;
use App\Api\V1\Common\Service\Exception\ContractNotFoundException;
use App\Api\V1\Common\Service\Exception\IncorrectStrategyTypeException;
use App\Api\V1\Common\Service\Exception\RegionCanNotHaveBedException;
use App\Api\V1\Common\Service\Exception\RegionNotFoundException;
use App\Api\V1\Common\Service\Exception\ResidentNotFoundException;
use App\Api\V1\Common\Service\Exception\StartGreaterEndDateException;
use App\Api\V1\Common\Service\IGridService;
use App\Entity\ApartmentBed;
use App\Entity\CareLevel;
use App\Entity\CityStateZip;
use App\Entity\ContractAction;
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

        $this->em->getRepository(Contract::class)->search($this->grantService->getCurrentSpace(), $queryBuilder);
    }

    public function list($params)
    {
        if (!empty($params) && !empty($params[0]['resident_id'])) {
            $residentId = $params[0]['resident_id'];

            return $this->em->getRepository(Contract::class)->getBy($this->grantService->getCurrentSpace(), $residentId);
        }

        throw new ResidentNotFoundException();
    }

    /**
     * @param $id
     * @return Contract|null|object
     */
    public function getById($id)
    {
        return $this->em->getRepository(Contract::class)->getOne($this->grantService->getCurrentSpace(), $id);
    }

    /**
     * @param $id
     * @return ContractAction|null|object
     */
    public function getActiveById($id)
    {
        $action = $this->em->getRepository(ContractAction::class)->getActiveByResident($this->grantService->getCurrentSpace(), $id);
        return $action ? $action->getContract() : null;
    }

    /**
     * @param $type
     * @param $id
     * @return ContractAction|null|object
     */
    public function getActiveResidentsByStrategy($type, $id)
    {
        return $this->em->getRepository(ContractAction::class)->getActiveResidentsByStrategy($this->grantService->getCurrentSpace(), $type, $id);
    }

    /**
     * @param $type
     * @param $id
     * @return ContractAction|null|object
     */
    public function getInactiveResidentsByStrategy($type, $id)
    {
        return $this->em->getRepository(ContractAction::class)->getInactiveResidentsByStrategy($this->grantService->getCurrentSpace(), $type, $id);
    }

    /**
     * @param array $params
     * @throws \Exception
     */
    public function add(array $params) : void
    {
        try {
            $this->em->getConnection()->beginTransaction();

            $currentSpace = $this->grantService->getCurrentSpace();

            $residentId = $params['resident_id'] ?? 0;

            /** @var Resident $resident */
            $resident = $this->em->getRepository(Resident::class)->getOne($currentSpace, $residentId);

            if ($resident === null) {
                throw new ResidentNotFoundException();
            }

            /** @var Contract $activeContract */
            $activeContract = $this->em->getRepository(Contract::class)->getOneByEndDateNull($currentSpace, $residentId);

            if ($activeContract !== null) {
                throw new ContractAlreadyExistException();
            }

            $type = $params['type'] ? (int)$params['type'] : 0;

            $contract = new Contract();
            $contract->setResident($resident);
            $contract->setType($type);

            $start = $params['start'];

            if (!empty($start)) {
                $start = new \DateTime($params['start']);
            }

            $contract->setStart($start);

            $end = $params['end'];

            if (!empty($end)) {
                $end = new \DateTime($params['end']);

                if ($start > $end) {
                    throw new StartGreaterEndDateException();
                }
            } else {
                $end = null;
            }

            $contract->setEnd($end);

            $this->validate($contract, null, ['api_admin_contract_add']);
            $this->em->persist($contract);

            $option = !empty($params['option']) ? $params['option'] : [];
            $editMode = false;

            switch ($contract->getType()) {
                case ContractType::TYPE_FACILITY:
                    $option = $this->saveFacilityOption($contract, $option, $editMode);
                    break;
                case ContractType::TYPE_APARTMENT:
                    $option = $this->saveApartmentOption($contract, $option, $editMode);
                    break;
                case ContractType::TYPE_REGION:
                    $option = $this->saveRegionOption($contract, $option, $editMode);
                    break;
                default:
                    throw new IncorrectStrategyTypeException();
            }

            $this->validate($option, null, ['api_admin_contract_add']);
            $this->em->persist($option);

            switch ($contract->getType()) {
                case ContractType::TYPE_FACILITY:
                    $contractAction = $this->saveContractActionForFacility($contract, $option, $editMode);
                    break;
                case ContractType::TYPE_APARTMENT:
                    $contractAction = $this->saveContractActionForApartment($contract, $option, $editMode);
                    break;
                case ContractType::TYPE_REGION:
                    $contractAction = $this->saveContractActionForRegion($contract, $option, $editMode);
                    break;
                default:
                    throw new IncorrectStrategyTypeException();
            }

            $this->em->persist($contractAction);

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

            $currentSpace = $this->grantService->getCurrentSpace();

            /** @var Contract $entity */
            $entity = $this->em->getRepository(Contract::class)->getOne($currentSpace, $id);

            if ($entity === null) {
                throw new ContractNotFoundException();
            }

            $residentId = $params['resident_id'] ?? 0;

            /** @var Resident $resident */
            $resident = $this->em->getRepository(Resident::class)->getOne($currentSpace, $residentId);

            if ($resident === null) {
                throw new ResidentNotFoundException();
            }

            $entity->setResident($resident);

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
                case ContractType::TYPE_FACILITY:
                    $option = $this->saveFacilityOption($entity, $option, $editMode);
                    break;
                case ContractType::TYPE_APARTMENT:
                    $option = $this->saveApartmentOption($entity, $option, $editMode);
                    break;
                case ContractType::TYPE_REGION:
                    $option = $this->saveRegionOption($entity, $option, $editMode);
                    break;
                default:
                    throw new IncorrectStrategyTypeException();
            }

            $this->validate($option, null, ['api_admin_contract_edit']);
            $this->em->persist($option);

            $uow = $this->em->getUnitOfWork();
            $uow->computeChangeSets();

            $optionChangeSet = $this->em->getUnitOfWork()->getEntityChangeSet($option);

            if (!empty($optionChangeSet)) {
                switch ($entity->getType()) {
                    case ContractType::TYPE_FACILITY:
                        $contractAction = $this->saveContractActionForFacility($entity, $option, $editMode);
                        break;
                    case ContractType::TYPE_APARTMENT:
                        $contractAction = $this->saveContractActionForApartment($entity, $option, $editMode);
                        break;
                    case ContractType::TYPE_REGION:
                        $contractAction = $this->saveContractActionForRegion($entity, $option, $editMode);
                        break;
                    default:
                        throw new IncorrectStrategyTypeException();
                }

                $this->em->persist($contractAction);
            }

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
    public function move($id, array $params) : void
    {
        try {

            $this->em->getConnection()->beginTransaction();

            $currentSpace = $this->grantService->getCurrentSpace();

            $resident = $this->em->getRepository(Resident::class)->getOne($currentSpace, $id);

            if ($resident === null) {
                throw new ResidentNotFoundException();
            }

            $type = !empty($params['type']) ? (int)$params['type'] : 0;

            if (!empty($params['move_id']) && !empty($params['option'])) {
                throw new IncorrectStrategyTypeException();
            }

            //assignment mode
            if (!empty($params['move_id'])) {
                /** @var ContractAction $action */
                $action = $this->em->getRepository(ContractAction::class)->getDataByResident($currentSpace, $type, $id);

                if ($action === null) {
                    throw new ContractActionNotFoundException();
                }

                $editMode = true;
                $moveId = (int)$params['move_id'];

                switch ($type) {
                    case ContractType::TYPE_FACILITY:
                        /** @var FacilityBed $entity */
                        $entity = $this->em->getRepository(FacilityBed::class)->getOne($currentSpace, $moveId);

                        if ($entity === null) {
                            throw new FacilityBedNotFoundException();
                        }

                        $contract = $action->getContract();

                        if ($contract !== null) {
                            $option = $contract->getOption();

                            if ($option !== null) {
                                $option->setFacilityBed($entity);

                                $this->em->persist($option);

                                $contractAction = $this->saveContractActionForFacility($contract, $option, $editMode);

                                $this->em->persist($contractAction);
                            }
                        }

                        break;
                    case ContractType::TYPE_APARTMENT:
                        /** @var ApartmentBed $entity */
                        $entity = $this->em->getRepository(ApartmentBed::class)->getOne($currentSpace, $moveId);

                        if ($entity === null) {
                            throw new ApartmentBedNotFoundException();
                        }

                        $contract = $action->getContract();

                        if ($contract !== null) {
                            $option = $contract->getOption();

                            if ($option !== null) {
                                $option->setApartmentBed($entity);

                                $this->em->persist($option);

                                $contractAction = $this->saveContractActionForApartment($contract, $option, $editMode);

                                $this->em->persist($contractAction);
                            }
                        }

                        break;
                    case ContractType::TYPE_REGION:
                        throw new RegionCanNotHaveBedException();

                        break;
                    default:
                        throw new IncorrectStrategyTypeException();
                }
            }

            //transfer mode
            if (!empty($params['option'])) {
                /** @var ContractAction $action */
                $action = $this->em->getRepository(ContractAction::class)->getActiveByResident($currentSpace, $id);

                if ($action === null) {
                    throw new ContractActionNotFoundException();
                }

                $editMode = false;
                $option = $params['option'];

                $oldContract = $action->getContract();

                $resident = null;
                if ($oldContract) {
                    $resident = $oldContract->getResident();

                    $oldOption = $oldContract->getOption();
                    if ($oldOption) {
                        $oldOption->setState(ContractState::TERMINATED);

                        $this->em->persist($oldOption);
                    }
                }

                $contract = new Contract();
                $contract->setResident($resident);
                $contract->setType($type);
                $contract->setEnd(null);

                $newDateTime = new \DateTime('now');
                $contract->setStart($newDateTime);

                $action->setEnd($newDateTime);
                $action->setState(ContractState::TERMINATED);

                $this->validate($contract, null, ['api_admin_contract_add']);
                $this->em->persist($contract);
                $this->em->persist($action);

                switch ($contract->getType()) {
                    case ContractType::TYPE_FACILITY:
                        $option = $this->saveFacilityOption($contract, $option, $editMode);
                        break;
                    case ContractType::TYPE_APARTMENT:
                        $option = $this->saveApartmentOption($contract, $option, $editMode);
                        break;
                    case ContractType::TYPE_REGION:
                        $option = $this->saveRegionOption($contract, $option, $editMode);
                        break;
                    default:
                        throw new IncorrectStrategyTypeException();
                }

                $this->validate($option, null, ['api_admin_contract_add']);
                $this->em->persist($option);

                switch ($contract->getType()) {
                    case ContractType::TYPE_FACILITY:
                        $contractAction = $this->saveContractActionForFacility($contract, $option, $editMode);
                        break;
                    case ContractType::TYPE_APARTMENT:
                        $contractAction = $this->saveContractActionForApartment($contract, $option, $editMode);
                        break;
                    case ContractType::TYPE_REGION:
                        $contractAction = $this->saveContractActionForRegion($contract, $option, $editMode);
                        break;
                    default:
                        throw new IncorrectStrategyTypeException();
                }

                $this->em->persist($contractAction);
            }

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
        $currentSpace = $this->grantService->getCurrentSpace();

        /**
         * @var ContractFacilityOption $option
         * @var DiningRoom $diningRoom
         * @var FacilityBed $facilityBed
         * @var CareLevel $careLevel
         */
        $option = $this->em->getRepository(ContractFacilityOption::class)->getOneBy($currentSpace, $contract);

        if (!isset($params['dining_room_id']) || !$params['dining_room_id']) {
            throw new DiningRoomNotFoundException();
        }

        if (!isset($params['bed_id']) || !$params['bed_id']) {
            throw new FacilityBedNotFoundException();
        }

        if (!isset($params['care_level_id']) || !$params['care_level_id']) {
            throw new CareLevelNotFoundException();
        }

        $diningRoom = $this->em->getRepository(DiningRoom::class)->getOne($currentSpace, $params['dining_room_id']);
        $facilityBed = $this->em->getRepository(FacilityBed::class)->getOne($currentSpace, $params['bed_id']);
        $careLevel = $this->em->getRepository(CareLevel::class)->getOne($currentSpace, $params['care_level_id']);

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

//            if ($option->getState() === ContractState::TERMINATED && $contract->getEnd() === null) {
//                throw new EndDateNotBeBlankException();
//            }
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
        $currentSpace = $this->grantService->getCurrentSpace();

        /**
         * @var ContractApartmentOption $option
         * @var ApartmentBed $apartmentBed
         */
        $option = $this->em->getRepository(ContractApartmentOption::class)->getOneBy($currentSpace, $contract);

        if (!isset($params['bed_id']) || !$params['bed_id']) {
            throw new ApartmentBedNotFoundException();
        }

        $apartmentBed = $this->em->getRepository(ApartmentBed::class)->getOne($currentSpace, $params['bed_id']);

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

//            if ($option->getState() === ContractState::TERMINATED && $contract->getEnd() === null) {
//                throw new EndDateNotBeBlankException();
//            }
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
        $currentSpace = $this->grantService->getCurrentSpace();

        /**
         * @var ContractRegionOption $option
         * @var Region $region
         * @var CityStateZip $csz
         * @var CareLevel $careLevel
         */
        $option = $this->em->getRepository(ContractRegionOption::class)->getOneBy($currentSpace, $contract);

        if (!isset($params['region_id']) || !$params['region_id']) {
            throw new RegionNotFoundException();
        }

        if (!isset($params['csz_id']) || !$params['csz_id']) {
            throw new FacilityBedNotFoundException();
        }

        if (!isset($params['care_level_id']) || !$params['care_level_id']) {
            throw new CareLevelNotFoundException();
        }

        $region = $this->em->getRepository(Region::class)->getOne($currentSpace, $params['region_id']);
        $csz = $this->em->getRepository(CityStateZip::class)->getOne($currentSpace, $params['csz_id']);
        $careLevel = $this->em->getRepository(CareLevel::class)->getOne($currentSpace, $params['care_level_id']);

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

//            if ($option->getState() === ContractState::TERMINATED && $contract->getEnd() === null) {
//                throw new EndDateNotBeBlankException();
//            }
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
     * @param Contract $contract
     * @param ContractFacilityOption $option
     * @param boolean $editMode
     * @return ContractAction|null|object
     */
    private function saveContractActionForFacility(Contract $contract, ContractFacilityOption $option, bool $editMode)
    {
        $newDateTime = new \DateTime('now');

        $contractAction = new ContractAction();
        $contractAction->setContract($contract);
        $contractAction->setStart($newDateTime);

        if ($option->getState() === ContractState::TERMINATED) {
            $contractAction->setEnd($newDateTime);
        } else {
            $contractAction->setEnd(null);
        }

        $contractAction->setState($option->getState());
        $contractAction->setFacilityBed($option->getFacilityBed());
        $contractAction->setDnr($option->isDnr());
        $contractAction->setPolst($option->isPolst());
        $contractAction->setAmbulatory($option->isAmbulatory());
        $contractAction->setCareGroup($option->getCareGroup());
        $contractAction->setCareLevel($option->getCareLevel());

        if ($editMode) {
            /** @var ContractAction $lastAction */
            $lastAction = $this->em->getRepository(ContractAction::class)->getContractLastAction($this->grantService->getCurrentSpace(), $contract->getId());

            if ($lastAction !== null) {
                $lastAction->setEnd($newDateTime);

                $this->em->persist($lastAction);
            }
        }

        return $contractAction;
    }

    /**
     * @param Contract $contract
     * @param ContractApartmentOption $option
     * @param boolean $editMode
     * @return ContractAction|null|object
     */
    private function saveContractActionForApartment(Contract $contract, ContractApartmentOption $option, bool $editMode)
    {
        $newDateTime = new \DateTime('now');

        $contractAction = new ContractAction();
        $contractAction->setContract($contract);
        $contractAction->setStart($newDateTime);

        if ($option->getState() === ContractState::TERMINATED) {
            $contractAction->setEnd($newDateTime);
        } else {
            $contractAction->setEnd(null);
        }

        $contractAction->setState($option->getState());
        $contractAction->setApartmentBed($option->getApartmentBed());

        if ($editMode) {
            /** @var ContractAction $lastAction */
            $lastAction = $this->em->getRepository(ContractAction::class)->getContractLastAction($this->grantService->getCurrentSpace(), $contract->getId());

            if ($lastAction !== null) {
                $lastAction->setEnd($newDateTime);

                $this->em->persist($lastAction);
            }
        }

        return $contractAction;
    }

    /**
     * @param Contract $contract
     * @param ContractRegionOption $option
     * @param boolean $editMode
     * @return ContractAction|null|object
     */
    private function saveContractActionForRegion(Contract $contract, ContractRegionOption $option, bool $editMode)
    {
        $newDateTime = new \DateTime('now');

        $contractAction = new ContractAction();
        $contractAction->setContract($contract);
        $contractAction->setStart($newDateTime);

        if ($option->getState() === ContractState::TERMINATED) {
            $contractAction->setEnd($newDateTime);
        } else {
            $contractAction->setEnd(null);
        }

        $contractAction->setState($option->getState());
        $contractAction->setRegion($option->getRegion());
        $contractAction->setCsz($option->getCsz());
        $contractAction->setAddress($option->getAddress());
        $contractAction->setDnr($option->isDnr());
        $contractAction->setPolst($option->isPolst());
        $contractAction->setAmbulatory($option->isAmbulatory());
        $contractAction->setCareGroup($option->getCareGroup());
        $contractAction->setCareLevel($option->getCareLevel());

        if ($editMode) {
            /** @var ContractAction $lastAction */
            $lastAction = $this->em->getRepository(ContractAction::class)->getContractLastAction($this->grantService->getCurrentSpace(), $contract->getId());

            if ($lastAction !== null) {
                $lastAction->setEnd($newDateTime);

                $this->em->persist($lastAction);
            }
        }

        return $contractAction;
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
            $entity = $this->em->getRepository(Contract::class)->getOne($this->grantService->getCurrentSpace(), $id);

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
            $this->em->getConnection()->beginTransaction();

            if (empty($ids)) {
                throw new ContractNotFoundException();
            }

            $contracts = $this->em->getRepository(Contract::class)->findByIds($this->grantService->getCurrentSpace(), $ids);

            if (empty($contracts)) {
                throw new ContractNotFoundException();
            }

            /**
             * @var Contract $contract
             */
            foreach ($contracts as $contract) {
                $this->em->remove($contract);
            }

            $this->em->flush();
            $this->em->getConnection()->commit();
        } catch (\Throwable $e) {
            $this->em->getConnection()->rollBack();

            throw $e;
        }
    }
}
