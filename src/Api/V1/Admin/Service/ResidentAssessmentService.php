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
     * @return bool|void
     */
    public function gridSelect(QueryBuilder $queryBuilder, $params)
    {
        if (empty($params) || empty($params[0]['resident_id'])) {
            throw new ResidentNotFoundException();
        }

            $residentId = $params[0]['resident_id'];

            $queryBuilder
                ->where('a.resident = :residentId')
                ->setParameter('residentId', $residentId);

            $this->em->getRepository(Assessment::class)->search($this->grantService->getCurrentSpace(), $queryBuilder);
    }

    /**
     * @param $params
     * @return array|object[]
     */
    public function list($params)
    {
        if (!empty($params) && !empty($params[0]['resident_id'])) {
            $residentId = $params[0]['resident_id'];

            return $this->em->getRepository(Assessment::class)->getBy($this->grantService->getCurrentSpace(), $residentId);
        }

        throw new ResidentNotFoundException();
    }

    /**
     * @param int $id
     * @return Assessment|null|object
     */
    public function getById(int $id)
    {
        return $this->em->getRepository(Assessment::class)->getOne($this->grantService->getCurrentSpace(), $id);
    }

    /**
     * @param array $params
     * @throws \Exception
     */
    public function add(array $params) : void
    {
        try {
            /**
             * @var Form $form
             * @var Resident $resident
             */
            $this->em->getConnection()->beginTransaction();

            $currentSpace = $this->grantService->getCurrentSpace();

            $rows       = $params['rows'] ?? [];
            $formId     = $params['form_id'] ?? 0;
            $residentId = $params['resident_id'] ?? 0;

            $form = $this->em->getRepository(Form::class)->getOne($currentSpace, $formId);

            if ($form === null) {
                throw new AssessmentFormNotFoundException();
            }

            $resident = $this->em->getRepository(Resident::class)->getOne($currentSpace, $residentId);

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

            $formId     = $params['form_id'] ?? 0;
            $residentId = $params['resident_id'] ?? 0;
            $rows       = $params['rows'] ?? [];

            $form = $this->em->getRepository(Form::class)->getOne($currentSpace, $formId);

            if ($form ===  null) {
                throw new AssessmentFormNotFoundException();
            }

            $resident = $this->em->getRepository(Resident::class)->getOne($currentSpace, $residentId);

            if ($resident ===  null) {
                throw new ResidentNotFoundException();
            }

            $assessment = $this->em->getRepository(Assessment::class)->getOne($currentSpace, $id);

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
    private function calculateTotalScore(Assessment $assessment)
    {
        // create report
        $report = new \App\Model\Report\Assessment();
        $report->setTable($assessment->getForm()->getFormCategories(), $assessment->getAssessmentRows());

        return $report->getTotalScore();
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

            $currentSpace = $this->grantService->getCurrentSpace();

            /** @var Assessment $assessment */
            $assessment = $this->em->getRepository(Assessment::class)->getOne($currentSpace, $id);

            if ($assessment === null) {
                throw new AssessmentNotFoundException();
            }

            // remove related rows
            $assessmentRows = $this->em->getRepository(AssessmentRow::class)->getBy($currentSpace, $assessment);

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
     * @throws \Doctrine\DBAL\ConnectionException
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

            $assessments = $this->em->getRepository(Assessment::class)->findByIds($currentSpace, $ids);

            if (empty($assessments)) {
                throw new AssessmentNotFoundException();
            }

            foreach ($assessments as $assessment) {
                $assessmentRows = $this->em->getRepository(AssessmentRow::class)->getBy($currentSpace, $assessment);

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
}
