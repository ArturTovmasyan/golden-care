<?php

namespace  App\Model\Report;

use App\Model\Assessment as AssessmentReportType;
use App\Entity\Assessment\AssessmentRow;
use App\Entity\Assessment\CareLevel;
use App\Entity\Assessment\CareLevelGroup;
use App\Entity\Assessment\Category;
use App\Entity\Assessment\FormCategory;
use App\Entity\Assessment\Row;
use App\Entity\Lead\AssessmentRow as LeadAssessmentRow;

class Assessment extends Base
{
    /**
     * @var int
     */
    private $type;

    /**
     * @var string
     */
    private $performedBy;

    /**
     * @var \DateTime
     */
    private $date;

    /**
     * @var int
     */
    private $totalScore = 0;

    /**
     * @var string
     */
    private $residentFullName;

    /**
     * @var array
     */
    private $groups;

    /**
     * @var array
     */
    private $allGroups;

    /**
     * @var array
     */
    private $table;

    /**
     * @return int
     */
    public function getType(): int
    {
        return $this->type;
    }

    /**
     * @param int $type
     */
    public function setType($type): void
    {
        $this->type = $type;
    }

    /**
     * @return string
     */
    public function getPerformedBy(): string
    {
        return $this->performedBy;
    }

    /**
     * @param string $performedBy
     */
    public function setPerformedBy(string $performedBy): void
    {
        $this->performedBy = $performedBy;
    }

    /**
     * @return string
     */
    public function getDate():string
    {
        return $this->date;
    }

    /**
     * @param $date
     */
    public function setDate($date): void
    {
        if ($date instanceof \DateTime) {
            $this->date = $date->format('m/d/y');
        } else {
            $this->date = $date;
        }
    }

    /**
     * @param $residentFullName
     */
    public function setResidentFullName($residentFullName)
    {
        $this->residentFullName = $residentFullName;
    }

    /**
     * @return string
     */
    public function getResidentFullName()
    {
        return $this->residentFullName;
    }

    /**
     * @return int
     */
    public function getTotalScore()
    {
        return $this->totalScore;
    }

    /**
     * @return array
     */
    public function getGroups()
    {
        return $this->groups;
    }

    /**
     * @param array $groups
     */
    public function setGroups($groups): void
    {
        if ($this->type === AssessmentReportType::TYPE_FILLED) {
            /**
             * @var CareLevelGroup $careLevelGroup
             * @var CareLevel $careLevel
             */
            foreach ($groups as $careLevelGroup) {
                foreach ($careLevelGroup->getCareLevels() as $careLevel) {
                    if ($this->totalScore >= $careLevel->getLevelLow() && ($this->totalScore <= $careLevel->getLevelHigh() || $careLevel->getLevelHigh() === null)) {
                        $this->groups[] = [
                            'group'   => $careLevelGroup->getTitle(),
                            'level'   => $careLevel->getTitle(),
                            'levelId' => $careLevel->getId(),
                        ];
                    }
                }
            }
        } else {
            /**
             * @var CareLevelGroup $careLevelGroup
             */
            foreach ($groups as $careLevelGroup) {
                $this->groups[] = [
                    'group'   => $careLevelGroup->getTitle(),
                    'level'   => 0,
                    'levelId' => 0,
                ];
            }
        }
    }

    /**
     * @return array
     */
    public function getAllGroups()
    {
        return $this->allGroups;
    }

    /**
     * @param array $allGroups
     */
    public function setAllGroups($allGroups): void
    {
        $this->allGroups = $allGroups;
    }

    /**
     * @return array
     */
    public function getTable()
    {
        return $this->table;
    }

    /**
     * @param $formCategories
     * @param $assessmentRows
     */
    public function setTable($formCategories, $assessmentRows)
    {
        /**
         * @var FormCategory $formCategory
         * @var AssessmentRow|LeadAssessmentRow $assessmentRow
         * @var Category $category
         * @var Row $row
         */
        $table                 = [];
        $assessmentRowsByRowId = [];

        if (!empty($assessmentRows)) {
            foreach ($assessmentRows as $assessmentRow) {
                $assessmentRowsByRowId[$assessmentRow->getRow()->getId()] = $assessmentRow;
            }
        }

        if (!empty($formCategories)) {
            foreach ($formCategories as $formCategory) {
                $category            = $formCategory->getCategory();
                $subScore            = 0;
                $t                   = 0;
                $multiItemScoreWords = null;

                $table[] = [
                    0 => $category->getTitle(), // activity
                    1 => $category->isMultiItem(), // score
                    2 => null, // subScore
                    3 => 'activity',
                ];

                foreach ($category->getRows() as $row) {
                    $value = 0;

                    if (isset($assessmentRowsByRowId[$row->getId()])) {
                        $value = $assessmentRowsByRowId[$row->getId()]->getScore();
                    }

                    $subScore += $value;

                    if ($category->isMultiItem()) {
                        if ($subScore < 3) {
                            $t = 2 - $subScore;
                            $multiItemScoreWords = '( Severe mental impairment )';
                        }
                        if ($subScore > 2 && $subScore <= 4) {
                            $t = 2 - $subScore;
                            $multiItemScoreWords = '( Definite mental impairment )';
                        }
                        if ($subScore > 4 && $subScore <= 7) {
                            $t = 1 - $subScore;
                            $multiItemScoreWords = '( Mild mental impairment )';

                        }
                        if ($subScore > 7 && $subScore <= 10) {
                            $t = 0 - $subScore;
                            $multiItemScoreWords = '( Intact mental functioning )';
                        }
                    }

                    $table[] = [
                        0 => $row->getTitle(),
                        1 => $value,
                        2 => null,
                        3 => 'row',
                    ];
                }

                $table[] = array(
                    0 => null,
                    1 => $multiItemScoreWords,
                    2 => $subScore + $t,
                    3 => 'score',
                );

                $this->totalScore += $subScore + $t;
            }
        }

        $this->table = $table;
    }

    /**
     * @param $formCategories
     */
    public function setBlankTable($formCategories)
    {
        /**
         * @var FormCategory $formCategory
         * @var Category $category
         * @var Row $row
         */
        $table = [];

        if (!empty($formCategories)) {
            foreach ($formCategories as $formCategory) {
                $category            = $formCategory->getCategory();
                $multiItemScoreWords = null;

                $table[] = [
                    0 => $category->getTitle(), // activity
                    1 => null,
                    2 => null, // subScore
                    3 => 'activity',
                ];

                foreach ($category->getRows() as $row) {
                    $table[] = [
                        0 => $row->getTitle(),
                        1 => $row->getScore(),
                        2 => null,
                        3 => 'row',
                    ];
                }

                $table[] = array(
                    0 => null,
                    1 => null,
                    2 => '_________',
                    3 => 'score'
                );
            }
        }

        $this->totalScore = '_________________';
        $this->table      = $table;
    }
}

