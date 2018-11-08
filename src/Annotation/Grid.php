<?php
namespace App\Annotation;

use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\QueryBuilder;

/**
 * @Annotation
 * @Target({"CLASS"})
 */
class Grid
{
    /**
     * Field options
     */
    const FIELD_OPTION_ID               = 0;
    const FIELD_OPTION_TYPE             = 1;
    const FIELD_OPTION_SORTABLE         = 2;
    const FIELD_OPTION_FILTERABLE       = 3;
    const FIELD_OPTION_ORIGINAL         = 4;
    const FIELD_OPTION_AVAILABLE_VALUES = 5;

    /**
     * Field types
     */
    const FIELD_TYPE_TEXT    = 'string';
    const FIELD_TYPE_NUMBER  = 'number';
    const FIELD_TYPE_DATE    = 'date';
    const FIELD_TYPE_ENUM    = 'enum';

    /**
     * Field options listing
     */
    public const FIELD_OPTIONS = [
        self::FIELD_OPTION_ID               => 'id',
        self::FIELD_OPTION_TYPE             => 'type',
        self::FIELD_OPTION_SORTABLE         => 'sortable',
        self::FIELD_OPTION_FILTERABLE       => 'filterable',
        self::FIELD_OPTION_ORIGINAL         => 'field',
        self::FIELD_OPTION_AVAILABLE_VALUES => 'values',
    ];

    /**
     * @var EntityManagerInterface
     */
    private $em;

    /**
     * @var QueryBuilder
     */
    private $queryBuilder;

    /**
     * @var array
     */
    private $groups = [];

    /**
     * @var array
     */
    private $groupsById = [];

    /**
     * @var int
     */
    private $page = 1;

    /**
     * @var int
     */
    private $perPage = 10;

    /**
     * @param $options
     */
    public function __construct($options = null)
    {
        if (empty($options)) {
            return false;
        }

        foreach ($options as $groupName => $groupOptions) {
            foreach ($groupOptions as $index => $groupOption) {
                foreach ($groupOption as $key => $fieldOption) {
                    if (isset(self::FIELD_OPTIONS[$key])) {
                        if (self::FIELD_OPTIONS[$key] == 'values') {
                            $className  = $fieldOption[0];
                            $methodName = $fieldOption[1];

                            $this->groups[$groupName][$index][self::FIELD_OPTIONS[$key]] = $className::$methodName();
                        } else {
                            if (self::FIELD_OPTIONS[$key] != 'field') {
                                $this->groups[$groupName][$index][self::FIELD_OPTIONS[$key]] = $fieldOption;
                            }

                            $this->groupsById[$groupName][$groupOption[0]][self::FIELD_OPTIONS[$key]] = $fieldOption;
                        }
                    }
                }
            }
        }
    }

    /**
     * @return array
     */
    public function getGroups()
    {
        return $this->groups;
    }

    /**
     * @param $groupName
     * @return array|boolean
     */
    public function getGroupOptions($groupName)
    {
        if (isset($this->groups[$groupName])) {
            return $this->groups[$groupName];
        }

        return false;
    }

    /**
     * @param $groupName
     * @return array|boolean
     */
    public function getGroupOptionsById($groupName)
    {
        if (isset($this->groupsById[$groupName])) {
            return $this->groupsById[$groupName];
        }

        return false;
    }

    /**
     * @param EntityManagerInterface $em
     * @return $this
     */
    public function setEntityManager(EntityManagerInterface $em)
    {
        $this->em = $em;

        return $this;
    }

    /**
     * @return QueryBuilder
     */
    public function getQueryBuilder()
    {
        return $this->queryBuilder;
    }

    /**
     * @param $text
     * @param $prefix
     * @return string
     */
    private function removePrefix($text, $prefix)
    {
        if (0 === strpos($text, $prefix)) {
            $text = substr($text, strlen($prefix)) . '';
        }

        return $text;
    }

    /**
     * @param array $params
     * @param $groupName
     * @return $this|bool
     */
    public function renderByGroup(array $params, $groupName)
    {
        /** @todo remove **/
        /*$params = array_merge($params, [
            'sort' => [
               'name'  => 'asc',
            ],
            'filter' => [
                'name' => [
                    'c' => 1,
                    'v' => [
                        ''
                    ]
                ],
                'default' => [
                    'c' => 3,
                    'v' => [
                        1
                    ]
                ],
                'last_activity_at' => [
                    'c' => 3,
                    'v' => [
                        '2018-11-07T17:21:22',
                        '2018-11-21T17:21:24',
                    ]
                ]
            ]
        ]);*/

        $this->queryBuilder = $this->em->createQueryBuilder();
        $options            = $this->getGroupOptionsById($groupName);

        if (!$options) {
            return false;
        }

        // set page
        if (isset($params['page']) && (int) $params['page'] >= 1) {
            $this->page = (int) $params['page'];
        }

        // set limitation
        if (isset($params['per_page']) && (int) $params['per_page'] > 1) {
            $this->perPage = (int) $params['per_page'];
        }

        // set pagination
        if ($this->page && $this->perPage) {
            $offset = ($this->page - 1) * $this->perPage;
            $this->queryBuilder->setFirstResult($offset);
            $this->queryBuilder->setMaxResults($this->perPage);
        }

        // set sorting
        if (!empty($params['sort'])) {
            foreach ($params['sort'] as $key => $sortType) {
                if (!in_array(strtolower($sortType), ['asc', 'desc'])) {
                    continue;
                }

                $key = strtolower($key);

                if (!isset($options[$key]['sortable']) || !$options[$key]['sortable'] || !isset($options[$key]['field'])) {
                    continue;
                }

                $this->queryBuilder->addOrderBy($options[$key]['field'], $sortType);
            }
        }

        // set filters
        if (!empty($params['filter'])) {
            foreach ($params['filter'] as $key => $filter) {
                $key = strtolower($key);

                if (!isset($options[$key]['field']) || !$options[$key]['field']) {
                    continue;
                }

                if (!isset($options[$key]['filterable']) || !$options[$key]['filterable']) {
                    continue;
                }

                if (!isset($filter['c']) || !isset($filter['v'])) {
                    continue;
                }

                $fieldKey    = $options[$key]['field'];
                $suffix = 0;

                switch ($options[$key][self::FIELD_OPTIONS[self::FIELD_OPTION_TYPE]]) {
                    case self::FIELD_TYPE_TEXT:
                        switch ($filter['c']) {
                            case '0':
                                $this->queryBuilder->andHaving("$fieldKey = :text_$suffix");
                                $this->queryBuilder->setParameter("text_$suffix", $filter['v'][0]);
                                break;
                            case '1':
                                $this->queryBuilder->andHaving("$fieldKey LIKE :text_$suffix");
                                $this->queryBuilder->setParameter("text_$suffix", "%" . $filter['v'][0] . "%");
                                break;
                            /*case '2':
                                $regex = $filter['v'][0];
                                $valid = !(@preg_match("/".$regex."/", null) === false);

                                if (!$valid) {
                                    $regex = preg_quote($filter['v'][0]);
                                }

                                $this->queryBuilder->andHaving("REGEXP($key, :text_$suffix) = TRUE");
                                $this->queryBuilder->setParameter("text_$suffix", $regex);
                                break;*/
                        }
                        break;
                    case self::FIELD_TYPE_NUMBER:
                        switch ($filter['c']) {
                            case '0': // =
                                $this->queryBuilder->andHaving("$fieldKey = :num_$suffix");
                                $this->queryBuilder->setParameter("num_$suffix", $filter['v'][0]);
                                break;
                            case '1': // <
                                $this->queryBuilder->andHaving("$fieldKey < :num_$suffix");
                                $this->queryBuilder->setParameter("num_$suffix", $filter['v'][0]);
                                break;
                            case '2': // >
                                $this->queryBuilder->andHaving("$fieldKey > :num_$suffix");
                                $this->queryBuilder->setParameter("num_$suffix", $filter['v'][0]);
                                break;
                            case '3': // <=
                                $this->queryBuilder->andHaving("$fieldKey <= :num_$suffix");
                                $this->queryBuilder->setParameter("num_$suffix", $filter['v'][0]);
                                break;
                            case '4': // =>
                                $this->queryBuilder->andHaving("$fieldKey >= :num_$suffix");
                                $this->queryBuilder->setParameter("num_$suffix", $filter['v'][0]);
                                break;
                            case '5': // ><
                                $this->queryBuilder->andHaving("$fieldKey >= :num_from_$suffix AND $fieldKey <= :num_to_$suffix");
                                $this->queryBuilder->setParameter("num_from_$suffix", $filter['v'][0]);
                                $this->queryBuilder->setParameter("num_to_$suffix", $filter['v'][1]);
                                break;
                        }
                        break;
                    case self::FIELD_TYPE_DATE:
                        switch ($filter['c']) {
                            case '0': // =
                                $this->queryBuilder->andHaving("$fieldKey = :date_$suffix");
                                $this->queryBuilder->setParameter("date_$suffix", $filter['v'][0]);
                                break;
                            case '1': // <=
                                $this->queryBuilder->andHaving("$fieldKey <= :date_$suffix");
                                $this->queryBuilder->setParameter("date_$suffix", new \DateTime($filter['v'][0]));
                                break;
                            case '2': // =>
                                $this->queryBuilder->andHaving("$fieldKey >= :date_$suffix");
                                $this->queryBuilder->setParameter("date_$suffix", new \DateTime($filter['v'][0]));
                                break;
                            case '3': // ><
                                $this->queryBuilder->andHaving("$fieldKey BETWEEN :date_from_$suffix AND :date_to_$suffix");
                                $this->queryBuilder->setParameter("date_from_$suffix", new \DateTime($filter['v'][0]));
                                $this->queryBuilder->setParameter("date_to_$suffix", new \DateTime($filter['v'][1]));
                                break;
                        }
                        break;
                    case self::FIELD_TYPE_ENUM:
                        if (count($filter['v']) > 0) {
                            $enumHaving = "";
                            foreach ($filter['v'] as $idx => $item) {
                                $enumHaving .= " OR $fieldKey = :enum_$suffix" . "_$idx";
                                $this->queryBuilder->setParameter("enum_$suffix" . "_$idx", $idx);
                            }
                            $this->queryBuilder->andHaving($this->removePrefix($enumHaving, " OR "));
                        }
                        break;
                }
            }
        }

        return $this;
    }
}