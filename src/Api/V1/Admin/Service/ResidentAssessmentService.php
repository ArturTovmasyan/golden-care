<?php
namespace App\Api\V1\Admin\Service;

use App\Api\V1\Common\Service\BaseService;
use App\Api\V1\Common\Service\Exception\AssessmentCategoryMultipleException;
use App\Api\V1\Common\Service\Exception\AssessmentFormNotFoundException;
use App\Api\V1\Common\Service\Exception\AssessmentNotFoundException;
use App\Api\V1\Common\Service\Exception\AssessmentRowNotAvailableException;
use App\Api\V1\Common\Service\Exception\ResidentNotFoundException;
use App\Api\V1\Common\Service\IGridService;
use App\Entity\Assessment\Assessment;
use App\Entity\Assessment\AssessmentRow;
use App\Entity\Assessment\Category;
use App\Entity\Assessment\Form;
use App\Entity\Assessment\FormCategory;
use App\Entity\Assessment\Row;
use App\Entity\Resident;
use App\Repository\Assessment\AssessmentRepository;
use App\Repository\Assessment\AssessmentRowRepository;
use App\Repository\Assessment\FormRepository;
use App\Repository\ResidentRepository;
use App\Util\ArrayUtil;
use Doctrine\ORM\QueryBuilder;

/**
 * Class ResidentAssessmentService
 * @package App\Api\V1\Admin\Service
 */
class ResidentAssessmentService extends BaseService implements IGridService
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
            ->where('a.resident = :residentId')
            ->setParameter('residentId', $residentId);

        /** @var AssessmentRepository $repo */
        $repo = $this->em->getRepository(Assessment::class);

        $repo->search($this->grantService->getCurrentSpace(), $this->grantService->getCurrentUserEntityGrants(Assessment::class), $queryBuilder);
    }

    /**
     * @param $params
     * @return mixed
     */
    public function list($params)
    {
        if (!empty($params) && !empty($params[0]['resident_id'])) {
            $residentId = $params[0]['resident_id'];

            /** @var AssessmentRepository $repo */
            $repo = $this->em->getRepository(Assessment::class);

            return $repo->getBy($this->grantService->getCurrentSpace(), $this->grantService->getCurrentUserEntityGrants(Assessment::class), $residentId);
        }

        throw new ResidentNotFoundException();
    }

    /**
     * @param int $id
     * @return Assessment|null|object
     */
    public function getById(int $id)
    {
        /** @var AssessmentRepository $repo */
        $repo = $this->em->getRepository(Assessment::class);

        return $repo->getOne($this->grantService->getCurrentSpace(), $this->grantService->getCurrentUserEntityGrants(Assessment::class), $id);
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
            /**
             * @var Form $form
             * @var Resident $resident
             */
            $this->em->getConnection()->beginTransaction();

            $currentSpace = $this->grantService->getCurrentSpace();

            $rows = $params['rows'] ? ArrayUtil::flatten1D($params['rows']) : [];
            $formId = $params['form_id'] ?? 0;
            $residentId = $params['resident_id'] ?? 0;

            /** @var FormRepository $formRepo */
            $formRepo = $this->em->getRepository(Form::class);

            $form = $formRepo->getOne($currentSpace, $this->grantService->getCurrentUserEntityGrants(Form::class), $formId);

            if ($form === null) {
                throw new AssessmentFormNotFoundException();
            }

            /** @var ResidentRepository $residentRepo */
            $residentRepo = $this->em->getRepository(Resident::class);

            /** @var Resident $resident */
            $resident = $residentRepo->getOne($currentSpace, $this->grantService->getCurrentUserEntityGrants(Resident::class), $residentId);

            if ($resident === null) {
                throw new ResidentNotFoundException();
            }

            $assessment = new Assessment();
            $assessment->setResident($resident);
            $assessment->setForm($form);
            $assessment->setDate(new \DateTime($params['date']));
            $assessment->setPerformedBy($params['performed_by']);
            $assessment->setNotes($params['notes']);

            $this->validate($assessment, null, ['api_admin_assessment_add']);
            $this->em->persist($assessment);

            // save rows
            $this->saveRows($assessment, $rows);

            $this->em->persist($assessment);
            $this->em->flush();

            // calculate and save total score
            $assessment->setScore($this->calculateTotalScore($assessment));
            $this->em->persist($assessment);
            $this->em->flush();

            $this->em->getConnection()->commit();

            $insert_id = $assessment->getId();
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
            /**
             * @var Assessment $assessment
             * @var Form $form
             * @var Resident $resident
             */
            $this->em->getConnection()->beginTransaction();

            $currentSpace = $this->grantService->getCurrentSpace();

            $formId = $params['form_id'] ?? 0;
            $residentId = $params['resident_id'] ?? 0;
            $rows = $params['rows'] ? ArrayUtil::flatten1D($params['rows']) : [];

            /** @var FormRepository $formRepo */
            $formRepo = $this->em->getRepository(Form::class);

            $form = $formRepo->getOne($currentSpace, $this->grantService->getCurrentUserEntityGrants(Form::class), $formId);

            if ($form ===  null) {
                throw new AssessmentFormNotFoundException();
            }

            /** @var ResidentRepository $residentRepo */
            $residentRepo = $this->em->getRepository(Resident::class);

            /** @var Resident $resident */
            $resident = $residentRepo->getOne($currentSpace, $this->grantService->getCurrentUserEntityGrants(Resident::class), $residentId);

            if ($resident ===  null) {
                throw new ResidentNotFoundException();
            }

            /** @var AssessmentRepository $repo */
            $repo = $this->em->getRepository(Assessment::class);

            $assessment = $repo->getOne($currentSpace, $this->grantService->getCurrentUserEntityGrants(Assessment::class), $id);

            if ($assessment ===  null) {
                throw new AssessmentNotFoundException();
            }

            $assessment->setResident($resident);
            $assessment->setForm($form);
            $assessment->setDate(new \DateTime($params['date']));
            $assessment->setPerformedBy($params['performed_by']);
            $assessment->setNotes($params['notes']);

            $this->validate($assessment, null, ['api_admin_assessment_form_edit']);
            $this->em->persist($assessment);

            // save rows
            $this->saveRows($assessment, $rows);

            $this->em->persist($assessment);
            $this->em->flush();

            // calculate and save total score
            $assessment->setScore($this->calculateTotalScore($assessment));
            $this->em->persist($assessment);
            $this->em->flush();

            $this->em->getConnection()->commit();
        } catch (\Exception $e) {
            $this->em->getConnection()->rollBack();

            throw $e;
        }
    }

    /**
     * @param Assessment $assessment
     * @param $newRows
     */
    private function saveRows(Assessment $assessment, $newRows)
    {
        /**
         * @var FormCategory $formCategory
         * @var Category $category
         * @var Row $row
         * @var AssessmentRow $assessmentRow
         */
        $categoryIdsByRowId       = [];
        $categoriesById           = [];
        $rowsById                 = [];
        $categoryIdsWithUniqueRow = [];
        $formCategories           = $assessment->getForm()->getFormCategories();

        foreach ($formCategories as $formCategory) {
            $category                           = $formCategory->getCategory();
            $categoriesById[$category->getId()] = $category;
            $rows                               = $category->getRows();

            foreach ($rows as $row) {
                $categoryIdsByRowId[$row->getId()] = $category->getId();
                $rowsById[$row->getId()]           = $row;
            }

            unset($category, $rows);
        }

        // remove old relations
        $oldAssessmentRows = $assessment->getAssessmentRows();
        if ($oldAssessmentRows !== null) {
            foreach ($oldAssessmentRows as $assessmentRow) {
                $this->em->remove($assessmentRow);
            }
            $this->em->flush();
        }

        // add new relations
        if (!empty($newRows)) {
            foreach ($newRows as $rowId) {
                // check row availability
                if (!isset($rowsById[$rowId])) {
                    throw new AssessmentRowNotAvailableException();
                }

                // check multiple row availability
                $categoryId = $categoryIdsByRowId[$rowId];
                $category   = $categoriesById[$categoryId];

                if (!$category->isMultiItem()) {
                    if (\in_array($categoryId, $categoryIdsWithUniqueRow, false)) {
                        throw new AssessmentCategoryMultipleException();
                    }

                    $categoryIdsWithUniqueRow[] = $categoryId;
                }

                $assessmentRow = new AssessmentRow();
                $assessmentRow->setAssessment($assessment);
                $assessmentRow->setRow($rowsById[$rowId]);
                $assessmentRow->setScore($rowsById[$rowId]->getScore());
                $this->em->persist($assessmentRow);
            }
        }
    }

    /**
     * @param Assessment $assessment
     * @return int
     */
    private function calculateTotalScore(Assessment $assessment) : ?int
    {
        /** @var AssessmentRowRepository $arRepo */
        $arRepo = $this->em->getRepository(AssessmentRow::class);

        $assessmentRows = $arRepo->getBy($this->grantService->getCurrentSpace(), $this->grantService->getCurrentUserEntityGrants(AssessmentRow::class), $assessment);

        // create report
        $report = new \App\Model\Report\Assessment();
        $report->setTable($assessment->getForm()->getFormCategories(), $assessmentRows);

        return $report->getTotalScore();
    }

    /**
     * @param $id
     * @throws \Throwable
     */
    public function remove($id)
    {
        try {
            $this->em->getConnection()->beginTransaction();

            $currentSpace = $this->grantService->getCurrentSpace();

            /** @var AssessmentRepository $repo */
            $repo = $this->em->getRepository(Assessment::class);

            /** @var Assessment $assessment */
            $assessment = $repo->getOne($currentSpace, $this->grantService->getCurrentUserEntityGrants(Assessment::class), $id);

            if ($assessment === null) {
                throw new AssessmentNotFoundException();
            }

            /** @var AssessmentRowRepository $rowRepo */
            $rowRepo = $this->em->getRepository(AssessmentRow::class);

            // remove related rows
            $assessmentRows = $rowRepo->getBy($currentSpace, $this->grantService->getCurrentUserEntityGrants(AssessmentRow::class), $assessment);

            if (!empty($assessmentRows)) {
                foreach ($assessmentRows as $assessmentRow) {
                    $this->em->remove($assessmentRow);
                }
            }

            $this->em->remove($assessment);
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
    public function removeBulk(array $ids)
    {
        try {
            $this->em->getConnection()->beginTransaction();

            /**
             * @var Form $form
             */
            if (empty($ids)) {
                throw new AssessmentNotFoundException();
            }

            $currentSpace = $this->grantService->getCurrentSpace();

            /** @var AssessmentRepository $repo */
            $repo = $this->em->getRepository(Assessment::class);

            $assessments = $repo->findByIds($currentSpace, $this->grantService->getCurrentUserEntityGrants(Assessment::class), $ids);

            if (empty($assessments)) {
                throw new AssessmentNotFoundException();
            }

            /** @var AssessmentRowRepository $rowRepo */
            $rowRepo = $this->em->getRepository(AssessmentRow::class);

            foreach ($assessments as $assessment) {
                $assessmentRows = $rowRepo->getBy($currentSpace, $this->grantService->getCurrentUserEntityGrants(AssessmentRow::class), $assessment);

                if (!empty($assessmentRows)) {
                    foreach ($assessmentRows as $assessmentRow) {
                        $this->em->remove($assessmentRow);
                    }
                }

                $this->em->remove($assessment);
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
            throw new AssessmentNotFoundException();
        }

        /** @var AssessmentRepository $repo */
        $repo = $this->em->getRepository(Assessment::class);

        $entities = $repo->findByIds($this->grantService->getCurrentSpace(), $this->grantService->getCurrentUserEntityGrants(Assessment::class), $ids);

        if (empty($entities)) {
            throw new AssessmentNotFoundException();
        }

        return $this->getRelatedData(Assessment::class, $entities);
    }
}
