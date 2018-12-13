<?php
namespace App\Api\V1\Admin\Service;

use App\Api\V1\Common\Service\BaseService;
use App\Api\V1\Common\Service\Exception\ResidentNotFoundException;
use App\Api\V1\Common\Service\Exception\ResidentPaymentNegativeRemainingTotalException;
use App\Api\V1\Common\Service\Exception\ResidentPaymentNotFoundException;
use App\Api\V1\Common\Service\IGridService;
use App\Entity\Resident;
use App\Entity\ResidentPayment;
use Doctrine\ORM\QueryBuilder;

/**
 * Class ResidentPaymentService
 * @package App\Api\V1\Admin\Service
 */
class ResidentPaymentService extends BaseService implements IGridService
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
            ->where('rp.resident = :residentId')
            ->setParameter('residentId', $residentId);

        $this->em->getRepository(ResidentPayment::class)->search($queryBuilder);
    }

    public function list($params)
    {
        if (!empty($params) && !empty($params[0]['resident_id'])) {
            $residentId = $params[0]['resident_id'];

            return $this->em->getRepository(ResidentPayment::class)->findBy(['resident' => $residentId]);
        }

        throw new ResidentNotFoundException();
    }

    /**
     * @param $id
     * @return ResidentPayment|null|object
     */
    public function getById($id)
    {
        return $this->em->getRepository(ResidentPayment::class)->find($id);
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

            $residentPayment = new ResidentPayment();
            $residentPayment->setResident($resident);
            $residentPayment->setAmount($params['amount']);
            $residentPayment->setNotes($params['notes']);

            $date = $params['date'];

            if (!empty($date)) {
                $date = new \DateTime($params['date']);
            }

            $residentPayment->setDate($date);

            $paymentSources = $params['source'];

            $source = [];

            if (!empty($paymentSources)) {
                $amounts = array_map(function($item){return $item['amount'];} , $paymentSources);
                $sum  = array_sum($amounts);

                if ($sum > $residentPayment->getAmount()) {
                    throw new ResidentPaymentNegativeRemainingTotalException();
                }

                $source = $paymentSources;
            }

            $residentPayment->setSource($source);

            $this->validate($residentPayment, null, ['api_admin_resident_payment_add']);

            $this->em->persist($residentPayment);
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

            /** @var ResidentPayment $entity */
            $entity = $this->em->getRepository(ResidentPayment::class)->find($id);

            if ($entity === null) {
                throw new ResidentPaymentNotFoundException();
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
            $entity->setAmount($params['amount']);
            $entity->setNotes($params['notes']);

            $date = $params['date'];

            if (!empty($date)) {
                $date = new \DateTime($params['date']);
            }

            $entity->setDate($date);

            $paymentSources = $params['source'];

            $source = [];

            if (!empty($paymentSources)) {
                $amounts = array_map(function($item){return $item['amount'];} , $paymentSources);
                $sum  = array_sum($amounts);

                if ($sum > $entity->getAmount()) {
                    throw new ResidentPaymentNegativeRemainingTotalException();
                }

                $source = $paymentSources;
            }

            $entity->setSource($source);

            $this->validate($entity, null, ['api_admin_resident_payment_edit']);

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

            /** @var ResidentPayment $entity */
            $entity = $this->em->getRepository(ResidentPayment::class)->find($id);

            if ($entity === null) {
                throw new ResidentPaymentNotFoundException();
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
                throw new ResidentPaymentNotFoundException();
            }

            $residentPayments = $this->em->getRepository(ResidentPayment::class)->findByIds($ids);

            if (empty($residentPayments)) {
                throw new ResidentPaymentNotFoundException();
            }

            /**
             * @var ResidentPayment $residentPayment
             */
            $this->em->getConnection()->beginTransaction();

            foreach ($residentPayments as $residentPayment) {
                $this->em->remove($residentPayment);
            }

            $this->em->flush();
            $this->em->getConnection()->commit();
        } catch (ResidentPaymentNotFoundException $e) {
            throw $e;
        } catch (\Throwable $e) {
            $this->em->getConnection()->rollBack();

            throw $e;
        }
    }
}
