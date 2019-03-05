<?php
namespace App\Api\V1\Admin\Service;

use App\Api\V1\Common\Service\BaseService;
use App\Api\V1\Common\Service\Exception\AllergenNotFoundException;
use App\Api\V1\Common\Service\Exception\ResidentAllergenNotFoundException;
use App\Api\V1\Common\Service\Exception\ResidentNotFoundException;
use App\Api\V1\Common\Service\IGridService;
use App\Entity\Allergen;
use App\Entity\Resident;
use App\Entity\ResidentAllergen;
use App\Repository\AllergenRepository;
use App\Repository\ResidentAllergenRepository;
use App\Repository\ResidentRepository;
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
     */
    public function gridSelect(QueryBuilder $queryBuilder, $params) : void
    {
        if (empty($params) || empty($params[0]['resident_id'])) {
            throw new ResidentNotFoundException();
        }

        $residentId = $params[0]['resident_id'];

        $queryBuilder
            ->where('ra.resident = :residentId')
            ->setParameter('residentId', $residentId);

        /** @var ResidentAllergenRepository $repo */
        $repo = $this->em->getRepository(ResidentAllergen::class);

        $repo->search($this->grantService->getCurrentSpace(), $this->grantService->getCurrentUserEntityGrants(ResidentAllergen::class), $queryBuilder);
    }

    /**
     * @param $params
     * @return mixed
     */
    public function list($params)
    {
        if (!empty($params) && !empty($params[0]['resident_id'])) {
            $residentId = $params[0]['resident_id'];

            /** @var ResidentAllergenRepository $repo */
            $repo = $this->em->getRepository(ResidentAllergen::class);

            return $repo->getBy($this->grantService->getCurrentSpace(), $this->grantService->getCurrentUserEntityGrants(ResidentAllergen::class), $residentId);
        }

        throw new ResidentNotFoundException();
    }

    /**
     * @param $id
     * @return ResidentAllergen|null|object
     */
    public function getById($id)
    {
        /** @var ResidentAllergenRepository $repo */
        $repo = $this->em->getRepository(ResidentAllergen::class);

        return $repo->getOne($this->grantService->getCurrentSpace(), $this->grantService->getCurrentUserEntityGrants(ResidentAllergen::class), $id);
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

            $currentSpace = $this->grantService->getCurrentSpace();

            /** @var ResidentRepository $residentRepo */
            $residentRepo = $this->em->getRepository(Resident::class);

            /** @var Resident $resident */
            $resident = $residentRepo->getOne($currentSpace, $this->grantService->getCurrentUserEntityGrants(Resident::class), $params['resident_id']);

            /** @var AllergenRepository $allergenRepo */
            $allergenRepo = $this->em->getRepository(Allergen::class);

            /** @var Allergen $allergen */
            $allergen = $allergenRepo->getOne($currentSpace, $this->grantService->getCurrentUserEntityGrants(Allergen::class), $params['allergen_id']);

            if ($resident === null) {
                throw new ResidentNotFoundException();
            }

            if ($allergen === null) {
                throw new AllergenNotFoundException();
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

            $insert_id = $residentAllergen->getId();
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

            /** @var ResidentAllergenRepository $repo */
            $repo = $this->em->getRepository(ResidentAllergen::class);

            /** @var ResidentAllergen $entity */
            $entity = $repo->getOne($currentSpace, $this->grantService->getCurrentUserEntityGrants(ResidentAllergen::class), $id);

            if ($entity === null) {
                throw new ResidentAllergenNotFoundException();
            }

            /** @var ResidentRepository $residentRepo */
            $residentRepo = $this->em->getRepository(Resident::class);

            /** @var Resident $resident */
            $resident = $residentRepo->getOne($currentSpace, $this->grantService->getCurrentUserEntityGrants(Resident::class), $params['resident_id']);

            /** @var AllergenRepository $allergenRepo */
            $allergenRepo = $this->em->getRepository(Allergen::class);

            /** @var Allergen $allergen */
            $allergen = $allergenRepo->getOne($currentSpace, $this->grantService->getCurrentUserEntityGrants(Allergen::class), $params['allergen_id']);

            if ($resident === null) {
                throw new ResidentNotFoundException();
            }

            if ($allergen === null) {
                throw new AllergenNotFoundException();
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
     * @throws \Throwable
     */
    public function remove($id)
    {
        try {
            $this->em->getConnection()->beginTransaction();

            /** @var ResidentAllergenRepository $repo */
            $repo = $this->em->getRepository(ResidentAllergen::class);

            /** @var ResidentAllergen $entity */
            $entity = $repo->getOne($this->grantService->getCurrentSpace(), $this->grantService->getCurrentUserEntityGrants(ResidentAllergen::class), $id);

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
     * @throws \Throwable
     */
    public function removeBulk(array $ids): void
    {
        try {
            $this->em->getConnection()->beginTransaction();

            if (empty($ids)) {
                throw new ResidentAllergenNotFoundException();
            }

            /** @var ResidentAllergenRepository $repo */
            $repo = $this->em->getRepository(ResidentAllergen::class);

            $residentAllergens = $repo->findByIds($this->grantService->getCurrentSpace(), $this->grantService->getCurrentUserEntityGrants(ResidentAllergen::class), $ids);

            if (empty($residentAllergens)) {
                throw new ResidentAllergenNotFoundException();
            }

            /**
             * @var ResidentAllergen $residentAllergen
             */
            foreach ($residentAllergens as $residentAllergen) {
                $this->em->remove($residentAllergen);
            }

            $this->em->flush();
            $this->em->getConnection()->commit();
        } catch (\Throwable $e) {
            $this->em->getConnection()->rollBack();

            throw $e;
        }
    }
}
