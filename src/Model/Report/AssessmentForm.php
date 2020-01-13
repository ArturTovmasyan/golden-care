<?php

namespace App\Model\Report;

use App\Entity\Assessment\CareLevelGroup;
use App\Entity\Assessment\Category;
use App\Entity\Assessment\FormCategory;
use App\Entity\Assessment\Row;

class AssessmentForm extends Base
{
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
     * @return null|string
     */
    public function getPerformedBy(): ?string
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
     * @return null|string
     */
    public function getDate(): ?string
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
    public function setResidentFullName($residentFullName): void
    {
        $this->residentFullName = $residentFullName;
    }

    /**
     * @return string
     */
    public function getResidentFullName(): ?string
    {
        return $this->residentFullName;
    }

    /**
     * @return int
     */
    public function getTotalScore(): ?int
    {
        return $this->totalScore;
    }

    /**
     * @return array
     */
    public function getGroups(): ?array
    {
        return $this->groups;
    }

    /**
     * @param array $groups
     */
    public function setGroups($groups): void
    {
        /**
         * @var CareLevelGroup $careLevelGroup
         */
        foreach ($groups as $careLevelGroup) {
            $this->groups[] = [
                'group' => $careLevelGroup->getTitle(),
                'level' => 0,
                'levelId' => 0,
            ];
        }
    }

    /**
     * @return array
     */
    public function getAllGroups(): ?array
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
    public function getTable(): ?array
    {
        return $this->table;
    }

    /**
     * @param $formCategories
     */
    public function setTable($formCategories): void
    {
        /**
         * @var FormCategory $formCategory
         * @var Category $category
         * @var Row $row
         */
        $table = [];

        if (!empty($formCategories)) {
            foreach ($formCategories as $formCategory) {
                $category = $formCategory->getCategory();
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
        $this->table = $table;
    }
}

