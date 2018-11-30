<?php
namespace App\Api\V1\Admin\Service;

use App\Api\V1\Common\Service\BaseService;
use App\Api\V1\Common\Service\Exception\ResidentNotFoundException;
use App\Api\V1\Common\Service\Exception\ResidentRentNegativeRemainingTotalException;
use App\Api\V1\Common\Service\Exception\ResidentRentNotFoundException;
use App\Api\V1\Common\Service\IGridService;
use App\Entity\Resident;
use App\Entity\ResidentRent;
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
     * @return void
     */
    public function gridSelect(QueryBuilder $queryBuilder, $params)
    {
        if (!empty($params) && !empty($params[0]['resident_id'])) {
            $residentId = $params[0]['resident_id'];

            $this->em->getRepository(ResidentRent::class)->findBy(['resident' => $residentId]);
        } else {
            $this->em->getRepository(ResidentRent::class)->search($queryBuilder);
        }
    }

    public function list($params)
    {
        if (!empty($params) && !empty($params[0]['resident_id'])) {
            $residentId = $params[0]['resident_id'];

            return $this->em->getRepository(ResidentRent::class)->findBy(['resident' => $residentId]);
        }

        return $this->em->getRepository(ResidentRent::class)->findAll();
    }

    /**
     * @param $id
     * @return ResidentRent|null|object
     */
    public function getById($id)
    {
        return $this->em->getRepository(ResidentRent::class)->find($id);
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

            $residentRent = new ResidentRent();
            $residentRent->setResident($resident);
            $residentRent->setType($params['type']);
            $residentRent->setAmount($params['amount']);
            $residentRent->setNotes($params['notes']);

            $start = $params['start'];

            if (!empty($start)) {
                $start = new \DateTime($params['start']);
                $start->setTime(0, 0, 0);
            }

            $residentRent->setStart($start);

            $end = $params['end'];

            if (!empty($end)) {
                $end = new \DateTime($params['end']);
                $end->setTime(0, 0, 0);
            } else {
                $end = null;
            }

            $residentRent->setEnd($end);

            $paymentSources = $params['source'];

            $source = json_encode([]);

            if (!empty($paymentSources)) {
                $amounts = array_map(function($item){return $item['amount'];} , $paymentSources);
                $sum  = array_sum($amounts);

                if ($sum > $residentRent->getAmount()) {
                    throw new ResidentRentNegativeRemainingTotalException();
                }

                $source = json_encode($paymentSources);
            }

            $residentRent->setSource($source);

            $this->validate($residentRent, null, ['api_admin_resident_rent_add']);

            $this->em->persist($residentRent);
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

            /** @var ResidentRent $entity */
            $entity = $this->em->getRepository(ResidentRent::class)->find($id);

            if ($entity === null) {
                throw new ResidentRentNotFoundException();
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

            $entity->setResident($resident);
            $entity->setType($params['type']);
            $entity->setAmount($params['amount']);
            $entity->setNotes($params['notes']);

            $start = $params['start'];

            if (!empty($start)) {
                $start = new \DateTime($params['start']);
                $start->setTime(0, 0, 0);
            }

            $entity->setStart($start);

            $end = $params['end'];

            if (!empty($end)) {
                $end = new \DateTime($params['end']);
                $end->setTime(0, 0, 0);
            } else {
                $end = null;
            }

            $entity->setEnd($end);

            $paymentSources = $params['source'];

            $source = json_encode([]);

            if (!empty($paymentSources)) {
                $amounts = array_map(function($item){return $item['amount'];} , $paymentSources);
                $sum  = array_sum($amounts);

                if ($sum > $entity->getAmount()) {
                    throw new ResidentRentNegativeRemainingTotalException();
                }

                $source = json_encode($paymentSources);
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
     * @throws \Doctrine\DBAL\ConnectionException
     * @throws \Throwable
     */
    public function remove($id)
    {
        try {
            $this->em->getConnection()->beginTransaction();

            /** @var ResidentRent $entity */
            $entity = $this->em->getRepository(ResidentRent::class)->find($id);

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
     * @throws \Doctrine\DBAL\ConnectionException
     * @throws \Throwable
     */
    public function removeBulk(array $ids): void
    {
        try {
            if (empty($ids)) {
                throw new ResidentRentNotFoundException();
            }

            $residentRents = $this->em->getRepository(ResidentRent::class)->findByIds($ids);

            if (empty($residentRents)) {
                throw new ResidentRentNotFoundException();
            }

            /**
             * @var ResidentRent $residentRent
             */
            $this->em->getConnection()->beginTransaction();

            foreach ($residentRents as $residentRent) {
                $this->em->remove($residentRent);
            }

            $this->em->flush();
            $this->em->getConnection()->commit();
        } catch (ResidentRentNotFoundException $e) {
            throw $e;
        } catch (\Throwable $e) {
            $this->em->getConnection()->rollBack();

            throw $e;
        }
    }
}
