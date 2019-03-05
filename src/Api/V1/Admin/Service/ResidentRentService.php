<?php
namespace App\Api\V1\Admin\Service;

use App\Api\V1\Common\Service\BaseService;
use App\Api\V1\Common\Service\Exception\ResidentNotFoundException;
use App\Api\V1\Common\Service\Exception\ResidentRentNegativeRemainingTotalException;
use App\Api\V1\Common\Service\Exception\ResidentRentNotFoundException;
use App\Api\V1\Common\Service\Exception\StartGreaterEndDateException;
use App\Api\V1\Common\Service\IGridService;
use App\Entity\Resident;
use App\Entity\ResidentRent;
use App\Repository\ResidentRentRepository;
use App\Repository\ResidentRepository;
use Doctrine\ORM\QueryBuilder;

/**
 * Class ResidentRentService
 * @package App\Api\V1\Admin\Service
 */
class ResidentRentService extends BaseService implements IGridService
{
    /**
     * @param QueryBuilder $queryBuilder
     * @param $params
     */
    public function gridSelect(QueryBuilder $queryBuilder, $params) : void
    {
        if (empty($params) || empty($params[0]['resident_id'])) {
            throw new ResidentNotFoundException();
        }

        $residentId = $params[0]['resident_id'];

        $queryBuilder
            ->where('rr.resident = :residentId')
            ->setParameter('residentId', $residentId);

        /** @var ResidentRentRepository $repo */
        $repo = $this->em->getRepository(ResidentRent::class);

        $repo->search($this->grantService->getCurrentSpace(), $this->grantService->getCurrentUserEntityGrants(ResidentRent::class), $queryBuilder);
    }

    /**
     * @param $params
     * @return mixed
     */
    public function list($params)
    {
        if (!empty($params) && !empty($params[0]['resident_id'])) {
            $residentId = $params[0]['resident_id'];

            /** @var ResidentRentRepository $repo */
            $repo = $this->em->getRepository(ResidentRent::class);

            return $repo->getBy($this->grantService->getCurrentSpace(), $this->grantService->getCurrentUserEntityGrants(ResidentRent::class), $residentId);
        }

        throw new ResidentNotFoundException();
    }

    /**
     * @param $id
     * @return ResidentRent|null|object
     */
    public function getById($id)
    {
        /** @var ResidentRentRepository $repo */
        $repo = $this->em->getRepository(ResidentRent::class);

        return $repo->getOne($this->grantService->getCurrentSpace(), $this->grantService->getCurrentUserEntityGrants(ResidentRent::class), $id);
    }

    /**
     * @param array $params
     * @return int|null
     * @throws \Exception
     */
    public function add(array $params) : ?int
    {
        $insert_id = null;
        try {
            $this->em->getConnection()->beginTransaction();

            $residentId = $params['resident_id'] ?? 0;

            /** @var ResidentRepository $residentRepo */
            $residentRepo = $this->em->getRepository(Resident::class);

            /** @var Resident $resident */
            $resident = $residentRepo->getOne($this->grantService->getCurrentSpace(), $this->grantService->getCurrentUserEntityGrants(Resident::class), $residentId);

            if ($resident === null) {
                throw new ResidentNotFoundException();
            }

            $period = $params['period'] ? (int)$params['period'] : 0;

            $residentRent = new ResidentRent();
            $residentRent->setResident($resident);
            $residentRent->setPeriod($period);
            $residentRent->setAmount($params['amount']);
            $residentRent->setNotes($params['notes']);

            $start = $params['start'];

            if (!empty($start)) {
                $start = new \DateTime($params['start']);
            }

            $residentRent->setStart($start);

            $end = $params['end'];

            if (!empty($end)) {
                $end = new \DateTime($params['end']);

                if ($start > $end) {
                    throw new StartGreaterEndDateException();
                }
            } else {
                $end = null;
            }

            $residentRent->setEnd($end);

            $paymentSources = $params['source'];

            $source = [];

            if (!empty($paymentSources)) {
                $amounts = array_map(function($item){return $item['amount'];} , $paymentSources);
                $sum  = array_sum($amounts);

                if ($sum > $residentRent->getAmount()) {
                    throw new ResidentRentNegativeRemainingTotalException();
                }

                $source = $paymentSources;
            }

            $residentRent->setSource($source);

            $this->validate($residentRent, null, ['api_admin_resident_rent_add']);

            $this->em->persist($residentRent);
            $this->em->flush();
            $this->em->getConnection()->commit();

            $insert_id = $residentRent->getId();
        } catch (\Exception $e) {
            $this->em->getConnection()->rollBack();

            throw $e;
        }

        return $insert_id;
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

            /** @var ResidentRentRepository $repo */
            $repo = $this->em->getRepository(ResidentRent::class);

            /** @var ResidentRent $entity */
            $entity = $repo->getOne($currentSpace, $this->grantService->getCurrentUserEntityGrants(ResidentRent::class), $id);

            if ($entity === null) {
                throw new ResidentRentNotFoundException();
            }

            $residentId = $params['resident_id'] ?? 0;

            /** @var ResidentRepository $residentRepo */
            $residentRepo = $this->em->getRepository(Resident::class);

            /** @var Resident $resident */
            $resident = $residentRepo->getOne($currentSpace, $this->grantService->getCurrentUserEntityGrants(Resident::class), $residentId);

            if ($resident === null) {
                throw new ResidentNotFoundException();
            }

            $period = $params['period'] ? (int)$params['period'] : 0;

            $entity->setResident($resident);
            $entity->setPeriod($period);
            $entity->setAmount($params['amount']);
            $entity->setNotes($params['notes']);

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

            $paymentSources = $params['source'];

            $source = [];

            if (!empty($paymentSources)) {
                $amounts = array_map(function($item){return $item['amount'];} , $paymentSources);
                $sum  = array_sum($amounts);

                if ($sum > $entity->getAmount()) {
                    throw new ResidentRentNegativeRemainingTotalException();
                }

                $source = $paymentSources;
            }

            $entity->setSource($source);

            $this->validate($entity, null, ['api_admin_resident_rent_edit']);

            $this->em->persist($entity);
            $this->em->flush();
            $this->em->getConnection()->commit();
        } catch (\Exception $e) {
            $this->em->getConnection()->rollBack();

            throw $e;
        }
    }

    /**
     * @param $id
     * @throws \Throwable
     */
    public function remove($id)
    {
        try {
            $this->em->getConnection()->beginTransaction();

            /** @var ResidentRentRepository $repo */
            $repo = $this->em->getRepository(ResidentRent::class);

            /** @var ResidentRent $entity */
            $entity = $repo->getOne($this->grantService->getCurrentSpace(), $this->grantService->getCurrentUserEntityGrants(ResidentRent::class), $id);

            if ($entity === null) {
                throw new ResidentRentNotFoundException();
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
                throw new ResidentRentNotFoundException();
            }

            /** @var ResidentRentRepository $repo */
            $repo = $this->em->getRepository(ResidentRent::class);

            $residentRents = $repo->findByIds($this->grantService->getCurrentSpace(), $this->grantService->getCurrentUserEntityGrants(ResidentRent::class), $ids);

            if (empty($residentRents)) {
                throw new ResidentRentNotFoundException();
            }

            /**
             * @var ResidentRent $residentRent
             */
            foreach ($residentRents as $residentRent) {
                $this->em->remove($residentRent);
            }

            $this->em->flush();
            $this->em->getConnection()->commit();
        } catch (\Throwable $e) {
            $this->em->getConnection()->rollBack();

            throw $e;
        }
    }
}
