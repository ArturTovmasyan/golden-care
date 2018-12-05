<?php
namespace App\Api\V1\Admin\Service;

use App\Api\V1\Common\Service\BaseService;
use App\Api\V1\Common\Service\Exception\AssessmentCategoryMultipleException;
use App\Api\V1\Common\Service\Exception\AssessmentFormNotFoundException;
use App\Api\V1\Common\Service\Exception\AssessmentNotFoundException;
use App\Api\V1\Common\Service\Exception\AssessmentRowNotAvailableException;
use App\Api\V1\Common\Service\Exception\ResidentNotFoundException;
use App\Api\V1\Common\Service\Exception\SpaceNotFoundException;
use App\Api\V1\Common\Service\IGridService;
use App\Entity\Assessment\Assessment;
use App\Entity\Assessment\AssessmentRow;
use App\Entity\Assessment\Category;
use App\Entity\Assessment\Form;
use App\Entity\Assessment\FormCategory;
use App\Entity\Assessment\Row;
use App\Entity\Resident;
use App\Entity\ResidentAssessment;
use App\Entity\Space;
use Doctrine\ORM\QueryBuilder;

/**
 * Class AssessmentService
 * @package App\Api\V1\Admin\Service
 */
class AssessmentService extends BaseService implements IGridService
{
    /**
     * @param QueryBuilder $queryBuilder
     * @param $params
     * @return bool|void
     */
    public function gridSelect(QueryBuilder $queryBuilder, $params)
    {
        if (!empty($params) && !empty($params[0]['resident_id'])) {
            $residentId = $params[0]['resident_id'];

            $queryBuilder
                ->where('ra.resident = :residentId')
                ->setParameter('residentId', $residentId);


            return $this->em->getRepository(Assessment::class)->search($queryBuilder);
        }

        throw new ResidentNotFoundException();
    }

    /**
     * @param $params
     * @return array|object[]
     */
    public function list($params)
    {
        if (!empty($params) && !empty($params[0]['resident_id'])) {
            $residentId = $params[0]['resident_id'];

            return $this->em->getRepository(Assessment::class)->findByResident($residentId);
        }

        throw new ResidentNotFoundException();
    }

    /**
     * @param int $id
     * @return Assessment|null|object
     */
    public function getById(int $id)
    {
        return $this->em->getRepository(Assessment::class)->find($id);
    }

    /**
     * @param int $id
     * @param int $type
     * @return \App\Model\Report\Assessment
     */
    public function getReport(int $id, $type = \App\Model\Assessment::TYPE_FILLED)
    {
        /**
         * @var Assessment $assessment
         */
        $assessment = $this->em->getRepository(Assessment::class)->find($id);

        if (is_null($assessment)) {
            throw new AssessmentNotFoundException();
        }

        $form            = $assessment->getForm();
        $careLevelGroups = $form->getCareLevelGroups();

        // create report
        $report = new \App\Model\Report\Assessment();
        $report->setType($type ?? \App\Model\Assessment::TYPE_FILLED);
        $report->setTitle('Level of Care Assessment');
        $report->setPerformedBy($assessment->getPerformedBy());
        $report->setDate($assessment->getDate());
        $report->setResidentFullName($assessment->getResidentAssessment()->getResident());
        $report->setGroups($careLevelGroups);
        $report->setAllGroups($careLevelGroups);
        $report->setTable($assessment->getForm()->getFormCategories(), $assessment->getAssessmentRows());

        unset($form);
        unset($careLevelGroups);
        unset($assessment);

        return $report;
    }

    /**
     * @param array $params
     * @throws \Exception
     */
    public function add(array $params) : void
    {
        try {
            /**
             * @var Space $space
             * @var Form $form
             * @var Resident $resident
             */
            $this->em->getConnection()->beginTransaction();

            $rows       = $params['rows'] ?? [];
            $spaceId    = $params['space_id'] ?? 0;
            $formId     = $params['form_id'] ?? 0;
            $residentId = $params['resident_id'] ?? 0;
            $space      = null;
            $form       = null;
            $resident   = null;

            if ($spaceId > 0) {
                $space = $this->em->getRepository(Space::class)->find($spaceId);
            }

            if (is_null($space)) {
                throw new SpaceNotFoundException();
            }

            if ($formId > 0) {
                $form = $this->em->getRepository(Form::class)->find($formId);
            }

            if (is_null($form)) {
                throw new AssessmentFormNotFoundException();
            }

            if ($residentId > 0) {
                $resident = $this->em->getRepository(Resident::class)->find($residentId);
            }

            if (is_null($resident)) {
                throw new ResidentNotFoundException();
            }

            $assessment = new Assessment();
            $assessment->setSpace($space);
            $assessment->setForm($form);
            $assessment->setDate(\DateTime::createFromFormat('m-d-Y', $params['date']));
            $assessment->setPerformedBy($params['performed_by']);
            $assessment->setNotes($params['notes']);

            $this->validate($assessment, null, ['api_admin_assessment_add']);
            $this->em->persist($assessment);

            // save rows
            $this->saveRows($assessment, $rows);

            // save assessment resident
            $residentAssessment = new ResidentAssessment();
            $residentAssessment->setAssessment($assessment);
            $residentAssessment->setResident($resident);
            $this->em->persist($residentAssessment);

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
             * @var Space $space
             */
            $this->em->getConnection()->beginTransaction();

            $spaceId    = $params['space_id'] ?? 0;
            $formId     = $params['form_id'] ?? 0;
            $residentId = $params['resident_id'] ?? 0;
            $rows       = $params['rows'] ?? [];
            $space      = null;
            $form       = null;
            $resident   = null;

            if ($spaceId > 0) {
                $space = $this->em->getRepository(Space::class)->find($spaceId);

                if ($space === null) {
                    throw new SpaceNotFoundException();
                }
            }

            if ($formId > 0) {
                $form = $this->em->getRepository(Form::class)->find($formId);

                if (is_null($form)) {
                    throw new AssessmentFormNotFoundException();
                }
            }

            if ($residentId > 0) {
                $resident = $this->em->getRepository(Resident::class)->find($residentId);

                if (is_null($resident)) {
                    throw new ResidentNotFoundException();
                }
            }

            $assessment = $this->em->getRepository(Assessment::class)->find($id);

            if (is_null($assessment)) {
                throw new AssessmentNotFoundException();
            }

            $assessment->setSpace($space);
            $assessment->setForm($form);
            $assessment->setDate(\DateTime::createFromFormat('m-d-Y', $params['date']));
            $assessment->setPerformedBy($params['performed_by']);
            $assessment->setNotes($params['notes']);

            $this->validate($assessment, null, ['api_admin_assessment_form_edit']);
            $this->em->persist($assessment);

            // save rows
            $this->saveRows($assessment, $rows);

            // calculate and save total score
            $assessment->setScore($this->calculateTotalScore($assessment));
            $this->em->persist($assessment);

            // save assessment resident
            $residentAssessment = $assessment->getResidentAssessment();
            $residentAssessment->setResident($resident);
            $this->em->persist($residentAssessment);

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

            unset($category);
            unset($rows);
        }

        // remove old relations
        $oldAssessmentRows = $assessment->getAssessmentRows();
        if (!empty($oldAssessmentRows)) {
            foreach ($oldAssessmentRows as $assessmentRow) {
                $this->em->remove($assessmentRow);
                $this->em->flush();
            }
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
                    if (in_array($categoryId, $categoryIdsWithUniqueRow)) {
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

            /** @var Assessment $assessment */
            $assessment = $this->em->getRepository(Assessment::class)->find($id);

            if (is_null($assessment)) {
                throw new AssessmentNotFoundException();
            }

            // remove related rows
            $assessmentRows = $this->em->getRepository(AssessmentRow::class)->findBy(['assessment' => $assessment]);

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
            /**
             * @var Form $form
             */
            if (empty($ids)) {
                throw new AssessmentNotFoundException();
            }

            $assessments = $this->em->getRepository(Assessment::class)->findByIds($ids);

            if (empty($assessments)) {
                throw new AssessmentNotFoundException();
            }

            $this->em->getConnection()->beginTransaction();

            foreach ($assessments as $assessment) {
                $assessmentRows = $this->em->getRepository(AssessmentRow::class)->findBy(['assessment' => $assessment]);

                if (!empty($assessmentRows)) {
                    foreach ($assessmentRows as $assessmentRow) {
                        $this->em->remove($assessmentRow);
                    }
                }

                $this->em->remove($assessment);
                $this->em->flush();
            }

            $this->em->getConnection()->commit();
        } catch(AssessmentFormNotFoundException $e) {
            throw $e;
        } catch (\Throwable $e) {
            $this->em->getConnection()->rollBack();

            throw $e;
        }
    }
}
