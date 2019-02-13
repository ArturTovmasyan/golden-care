<?php
namespace App\Api\V1\Admin\Service;

use App\Api\V1\Common\Service\BaseService;
use App\Api\V1\Common\Service\Exception\AllergenNotSingleException;
use App\Api\V1\Common\Service\Exception\AllergenNotFoundException;
use App\Api\V1\Common\Service\Exception\ResidentAllergenNotFoundException;
use App\Api\V1\Common\Service\Exception\ResidentNotFoundException;
use App\Api\V1\Common\Service\IGridService;
use App\Entity\Allergen;
use App\Entity\Resident;
use App\Entity\ResidentAllergen;
use Doctrine\ORM\QueryBuilder;

/**
 * Class ResidentAllergenService
 * @package App\Api\V1\Admin\Service
 */
class ResidentAllergenService extends BaseService implements IGridService
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
            ->where('ra.resident = :residentId')
            ->setParameter('residentId', $residentId);

        $this->em->getRepository(ResidentAllergen::class)->search($this->grantService->getCurrentSpace(), $queryBuilder);
    }

    public function list($params)
    {
        if (!empty($params) && !empty($params[0]['resident_id'])) {
            $residentId = $params[0]['resident_id'];

            return $this->em->getRepository(ResidentAllergen::class)->getBy($this->grantService->getCurrentSpace(), $residentId);
        }

        throw new ResidentNotFoundException();
    }

    /**
     * @param $id
     * @return ResidentAllergen|null|object
     */
    public function getById($id)
    {
        return $this->em->getRepository(ResidentAllergen::class)->getOne($this->grantService->getCurrentSpace(), $id);
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

            $allergenId = $params['allergen_id'];
            $newAllergen = $params['allergen'];

            if ((empty($allergenId) && empty($newAllergen)) || (!empty($allergenId) && !empty($newAllergen))) {
                throw new AllergenNotSingleException();
            }

            $allergen = null;

            if (!empty($newAllergen)) {
                $newAllergenTitle = $newAllergen['title'] ?? '';
                $newAllergenDescription = $newAllergen['description'] ?? '';

                $allergen = new Allergen();
                $allergen->setTitle($newAllergenTitle);
                $allergen->setDescription($newAllergenDescription);
                $allergen->setSpace($resident->getSpace());
            }

            if (!empty($allergenId)) {
                /** @var Allergen $allergen */
                $allergen = $this->em->getRepository(Allergen::class)->getOne($currentSpace, $allergenId);

                if ($allergen === null) {
                    throw new AllergenNotFoundException();
                }
            }

            $residentAllergen = new ResidentAllergen();
            $residentAllergen->setResident($resident);
            $residentAllergen->setAllergen($allergen);
            $residentAllergen->setNotes($params['notes']);

            $this->validate($residentAllergen, null, ['api_admin_resident_allergen_add']);

            $this->em->persist($allergen);
            $this->em->persist($residentAllergen);
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

            /** @var ResidentAllergen $entity */
            $entity = $this->em->getRepository(ResidentAllergen::class)->getOne($currentSpace, $id);

            if ($entity === null) {
                throw new ResidentAllergenNotFoundException();
            }

            $residentId = $params['resident_id'] ?? 0;

            /** @var Resident $resident */
            $resident = $this->em->getRepository(Resident::class)->getOne($currentSpace, $residentId);

            if ($resident === null) {
                throw new ResidentNotFoundException();
            }

            $allergenId = $params['allergen_id'];
            $newAllergen = $params['allergen'];

            if ((empty($allergenId) && empty($newAllergen)) || (!empty($allergenId) && !empty($newAllergen))) {
                throw new AllergenNotSingleException();
            }

            $allergen = null;

            if (!empty($newAllergen)) {
                $newAllergenTitle = $newAllergen['title'] ?? '';
                $newAllergenDescription = $newAllergen['description'] ?? '';

                $allergen = new Allergen();
                $allergen->setTitle($newAllergenTitle);
                $allergen->setDescription($newAllergenDescription);
                $allergen->setSpace($resident->getSpace());
            }

            if (!empty($allergenId)) {
                /** @var Allergen $allergen */
                $allergen = $this->em->getRepository(Allergen::class)->getOne($currentSpace, $allergenId);

                if ($allergen === null) {
                    throw new AllergenNotFoundException();
                }
            }

            $entity->setResident($resident);
            $entity->setAllergen($allergen);
            $entity->setNotes($params['notes']);

            $this->validate($entity, null, ['api_admin_resident_allergen_edit']);

            $this->em->persist($allergen);
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

            /** @var ResidentAllergen $entity */
            $entity = $this->em->getRepository(ResidentAllergen::class)->getOne($this->grantService->getCurrentSpace(), $id);

            if ($entity === null) {
                throw new ResidentAllergenNotFoundException();
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
                throw new ResidentAllergenNotFoundException();
            }

            $residentAllergens = $this->em->getRepository(ResidentAllergen::class)->findByIds($this->grantService->getCurrentSpace(), $ids);

            if (empty($residentAllergens)) {
                throw new ResidentAllergenNotFoundException();
            }

            /**
             * @var ResidentAllergen $residentAllergen
             */
            $this->em->getConnection()->beginTransaction();

            foreach ($residentAllergens as $residentAllergen) {
                $this->em->remove($residentAllergen);
            }

            $this->em->flush();
            $this->em->getConnection()->commit();
        } catch (ResidentAllergenNotFoundException $e) {
            throw $e;
        } catch (\Throwable $e) {
            $this->em->getConnection()->rollBack();

            throw $e;
        }
    }
}
