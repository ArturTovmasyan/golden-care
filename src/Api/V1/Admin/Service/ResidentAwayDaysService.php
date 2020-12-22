<?php

namespace App\Api\V1\Admin\Service;

use App\Api\V1\Common\Service\BaseService;
use App\Api\V1\Common\Service\Exception\DatesOverlapException;
use App\Api\V1\Common\Service\Exception\ResidentAwayDaysNotFoundException;
use App\Api\V1\Common\Service\Exception\ResidentNotFoundException;
use App\Api\V1\Common\Service\Exception\StartAndEndDateNotSameMonthException;
use App\Api\V1\Common\Service\Exception\StartGreaterEndDateException;
use App\Api\V1\Common\Service\IGridService;
use App\Entity\Resident;
use App\Entity\ResidentAwayDays;
use App\Entity\ResidentLedger;
use App\Repository\ResidentAwayDaysRepository;
use App\Repository\ResidentLedgerRepository;
use App\Repository\ResidentRepository;
use App\Util\Common\ImtDateTimeInterval;
use Doctrine\ORM\QueryBuilder;

/**
 * Class ResidentAwayDaysService
 * @package App\Api\V1\Admin\Service
 */
class ResidentAwayDaysService extends BaseService implements IGridService
{
    /**
     * @param QueryBuilder $queryBuilder
     * @param $params
     */
    public function gridSelect(QueryBuilder $queryBuilder, $params): void
    {
        if (empty($params) || empty($params[0]['resident_id'])) {
            throw new ResidentNotFoundException();
        }

        $residentId = $params[0]['resident_id'];

        $queryBuilder
            ->where('rad.resident = :residentId')
            ->setParameter('residentId', $residentId);

        /** @var ResidentAwayDaysRepository $repo */
        $repo = $this->em->getRepository(ResidentAwayDays::class);

        $repo->search($this->grantService->getCurrentSpace(), $this->grantService->getCurrentUserEntityGrants(ResidentAwayDays::class), $queryBuilder);
    }

    /**
     * @param $params
     * @return mixed
     */
    public function list($params)
    {
        if (!empty($params) && !empty($params[0]['resident_id'])) {
            $residentId = $params[0]['resident_id'];

            /** @var ResidentAwayDaysRepository $repo */
            $repo = $this->em->getRepository(ResidentAwayDays::class);

            return $repo->getBy($this->grantService->getCurrentSpace(), $this->grantService->getCurrentUserEntityGrants(ResidentAwayDays::class), $residentId);
        }

        throw new ResidentNotFoundException();
    }

    /**
     * @param $id
     * @return ResidentAwayDays|null|object
     */
    public function getById($id)
    {
        /** @var ResidentAwayDaysRepository $repo */
        $repo = $this->em->getRepository(ResidentAwayDays::class);

        return $repo->getOne($this->grantService->getCurrentSpace(), $this->grantService->getCurrentUserEntityGrants(ResidentAwayDays::class), $id);
    }

    /**
     * @param array $params
     * @param ResidentLedgerService $residentLedgerService
     * @return int|null
     * @throws \Doctrine\DBAL\ConnectionException
     * @throws \Doctrine\ORM\NonUniqueResultException
     */
    public function add(ResidentLedgerService $residentLedgerService, array $params): ?int
    {
        $insert_id = null;
        try {
            $this->em->getConnection()->beginTransaction();

            $currentSpace = $this->grantService->getCurrentSpace();

            $residentId = $params['resident_id'] ?? 0;

            /** @var ResidentRepository $residentRepo */
            $residentRepo = $this->em->getRepository(Resident::class);

            /** @var Resident $resident */
            $resident = $residentRepo->getOne($currentSpace, $this->grantService->getCurrentUserEntityGrants(Resident::class), $residentId);

            if ($resident === null) {
                throw new ResidentNotFoundException();
            }

            $residentAwayDays = new ResidentAwayDays();
            $residentAwayDays->setResident($resident);

            $start = null;
            if (!empty($params['start'])) {
                $start = new \DateTime($params['start']);
                $start->setTime(0, 0, 0);
            }

            $residentAwayDays->setStart($start);

            $end = null;
            if (!empty($params['end'])) {
                $end = new \DateTime($params['end']);
                $end->setTime(23, 59, 59);

                if ($start > $end) {
                    throw new StartGreaterEndDateException();
                }

                if ($start->format('Y') !== $end->format('Y') || $start->format('m') !== $end->format('m')) {
                    throw new StartAndEndDateNotSameMonthException();
                }
            }

            $residentAwayDays->setEnd($end);
            $residentAwayDays->setReason($params['reason']);

            $totalDays = 0;
            if ($start !== null && $end !== null) {
                $resAwayDays = $resident->getResidentAwayDays();

                if (!empty($resAwayDays)) {
                    /** @var ResidentAwayDays $residentAwayDay */
                    foreach ($resAwayDays as $residentAwayDay) {
                        if ($this->datesOverlap($start, $end, $residentAwayDay->getStart(), $residentAwayDay->getEnd()) > 0) {
                            throw new DatesOverlapException();
                        }
                    }
                }

                $totalDays = $end->diff($start)->days + 1;
            }

            $residentAwayDays->setTotalDays($totalDays);

            $this->validate($residentAwayDays, null, ['api_admin_resident_away_days_add']);

            $this->em->persist($residentAwayDays);
            $this->em->flush();

            //Re-Calculate Ledger
            $this->recalculateLedger($residentLedgerService, $currentSpace, $residentId, $residentAwayDays->getStart());

            $this->em->getConnection()->commit();

            $insert_id = $residentAwayDays->getId();
        } catch (\Exception $e) {
            $this->em->getConnection()->rollBack();

            throw $e;
        }

        return $insert_id;
    }

    /**
     * @param ResidentLedgerService $residentLedgerService
     * @param $currentSpace
     * @param $residentId
     * @param $date
     * @throws \Doctrine\ORM\NonUniqueResultException
     */
    private function recalculateLedger(ResidentLedgerService $residentLedgerService, $currentSpace, $residentId, $date): void
    {
        $dateStartFormatted = $date->format('m/01/Y 00:00:00');
        $dateEndFormatted = $date->format('m/t/Y 23:59:59');
        $dateStart = new \DateTime($dateStartFormatted);
        $dateEnd = new \DateTime($dateEndFormatted);

        /** @var ResidentLedgerRepository $ledgerRepo */
        $ledgerRepo = $this->em->getRepository(ResidentLedger::class);
        /** @var ResidentLedger $ledger */
        $ledger = $ledgerRepo->getResidentLedgerByDate($currentSpace, null, $residentId, $dateStart, $dateEnd);

        if ($ledger !== null) {
            $recalculateLedger = $residentLedgerService->calculateLedgerData($currentSpace, $ledgerRepo, $ledger, $residentId);

            $this->em->persist($recalculateLedger);

            $this->em->flush();
        }
    }

    /**
     * @param $id
     * @param ResidentLedgerService $residentLedgerService
     * @param array $params
     * @throws \Doctrine\DBAL\ConnectionException
     * @throws \Doctrine\ORM\NonUniqueResultException
     */
    public function edit($id, ResidentLedgerService $residentLedgerService, array $params): void
    {
        try {

            $this->em->getConnection()->beginTransaction();

            $currentSpace = $this->grantService->getCurrentSpace();

            /** @var ResidentAwayDaysRepository $repo */
            $repo = $this->em->getRepository(ResidentAwayDays::class);

            /** @var ResidentAwayDays $entity */
            $entity = $repo->getOne($currentSpace, $this->grantService->getCurrentUserEntityGrants(ResidentAwayDays::class), $id);

            if ($entity === null) {
                throw new ResidentAwayDaysNotFoundException();
            }

            $residentId = $params['resident_id'] ?? 0;

            /** @var ResidentRepository $residentRepo */
            $residentRepo = $this->em->getRepository(Resident::class);

            /** @var Resident $resident */
            $resident = $residentRepo->getOne($currentSpace, $this->grantService->getCurrentUserEntityGrants(Resident::class), $residentId);

            if ($resident === null) {
                throw new ResidentNotFoundException();
            }

            $entity->setResident($resident);

            $start = null;
            if (!empty($params['start'])) {
                $start = new \DateTime($params['start']);
                $start->setTime(0, 0, 0);
            }

            $entity->setStart($start);

            $end = null;
            if (!empty($params['end'])) {
                $end = new \DateTime($params['end']);
                $end->setTime(23, 59, 59);

                if ($start > $end) {
                    throw new StartGreaterEndDateException();
                }

                if ($start->format('Y') !== $end->format('Y') || $start->format('m') !== $end->format('m')) {
                    throw new StartAndEndDateNotSameMonthException();
                }
            }

            $entity->setEnd($end);
            $entity->setReason($params['reason']);

            $totalDays = 0;
            if ($start !== null && $end !== null) {
                $residentAwayDays = $resident->getResidentAwayDays();

                if (!empty($residentAwayDays)) {
                    /** @var ResidentAwayDays $residentAwayDay */
                    foreach ($residentAwayDays as $residentAwayDay) {
                        if ($residentAwayDay->getId() !== $entity->getId() && $this->datesOverlap($start, $end, $residentAwayDay->getStart(), $residentAwayDay->getEnd()) > 0) {
                            throw new DatesOverlapException();
                        }
                    }
                }

                $totalDays = $end->diff($start)->days + 1;
            }

            $entity->setTotalDays($totalDays);

            $this->validate($entity, null, ['api_admin_resident_away_days_edit']);

            $this->em->persist($entity);
            $this->em->flush();

            //Re-Calculate Ledger
            $this->recalculateLedger($residentLedgerService, $currentSpace, $residentId, $entity->getStart());

            $this->em->getConnection()->commit();
        } catch (\Exception $e) {
            $this->em->getConnection()->rollBack();

            throw $e;
        }
    }

    private function datesOverlap($startOne, $endOne, $startTwo, $endTwo) {
        if($startOne <= $endTwo && $endOne >= $startTwo) { //If the dates overlap
            return min($endOne,$endTwo)->diff(max($startTwo,$startOne))->days + 1; //return how many days overlap
        }

        return 0; //Return 0 if there is no overlap
    }

    /**
     * @param $id
     * @throws \Throwable
     */
    public function remove($id)
    {
        try {
            $this->em->getConnection()->beginTransaction();

            /** @var ResidentAwayDaysRepository $repo */
            $repo = $this->em->getRepository(ResidentAwayDays::class);

            /** @var ResidentAwayDays $entity */
            $entity = $repo->getOne($this->grantService->getCurrentSpace(), $this->grantService->getCurrentUserEntityGrants(ResidentAwayDays::class), $id);

            if ($entity === null) {
                throw new ResidentAwayDaysNotFoundException();
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
     * @throws \Throwable
     */
    public function removeBulk(array $ids): void
    {
        try {
            $this->em->getConnection()->beginTransaction();

            if (empty($ids)) {
                throw new ResidentAwayDaysNotFoundException();
            }

            /** @var ResidentAwayDaysRepository $repo */
            $repo = $this->em->getRepository(ResidentAwayDays::class);

            $data = $repo->findByIds($this->grantService->getCurrentSpace(), $this->grantService->getCurrentUserEntityGrants(ResidentAwayDays::class), $ids);

            if (empty($data)) {
                throw new ResidentAwayDaysNotFoundException();
            }

            /**
             * @var ResidentAwayDays $residentAwayDays
             */
            foreach ($data as $residentAwayDays) {
                $this->em->remove($residentAwayDays);
            }

            $this->em->flush();
            $this->em->getConnection()->commit();
        } catch (\Throwable $e) {
            $this->em->getConnection()->rollBack();

            throw $e;
        }
    }

    /**
     * @param array $ids
     * @return array
     */
    public function getRelatedInfo(array $ids): array
    {
        if (empty($ids)) {
            throw new ResidentAwayDaysNotFoundException();
        }

        /** @var ResidentAwayDaysRepository $repo */
        $repo = $this->em->getRepository(ResidentAwayDays::class);

        $entities = $repo->findByIds($this->grantService->getCurrentSpace(), $this->grantService->getCurrentUserEntityGrants(ResidentAwayDays::class), $ids);

        if (empty($entities)) {
            throw new ResidentAwayDaysNotFoundException();
        }

        return $this->getRelatedData(ResidentAwayDays::class, $entities);
    }
}
