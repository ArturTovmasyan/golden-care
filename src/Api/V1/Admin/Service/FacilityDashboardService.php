<?php
namespace App\Api\V1\Admin\Service;

use App\Api\V1\Common\Service\BaseService;
use App\Api\V1\Common\Service\Exception\FacilityNotFoundException;
use App\Api\V1\Common\Service\Exception\FacilityDashboardNotFoundException;
use App\Api\V1\Common\Service\Exception\StartGreaterEndDateException;
use App\Api\V1\Common\Service\IGridService;
use App\Entity\Facility;
use App\Entity\FacilityDashboard;
use App\Repository\FacilityDashboardRepository;
use App\Repository\FacilityRepository;
use App\Util\Common\ImtDateTimeInterval;
use Doctrine\ORM\QueryBuilder;

/**
 * Class FacilityDashboardService
 * @package App\Api\V1\Admin\Service
 */
class FacilityDashboardService extends BaseService implements IGridService
{
    /**
     * @param QueryBuilder $queryBuilder
     * @param $params
     */
    public function gridSelect(QueryBuilder $queryBuilder, $params) : void
    {
        /** @var FacilityDashboardRepository $repo */
        $repo = $this->em->getRepository(FacilityDashboard::class);

        $repo->search($this->grantService->getCurrentSpace(), $this->grantService->getCurrentUserEntityGrants(FacilityDashboard::class), $this->grantService->getCurrentUserEntityGrants(Facility::class), $queryBuilder);
    }

    /**
     * @param $params
     * @return mixed
     */
    public function list($params)
    {
        $currentSpace = $this->grantService->getCurrentSpace();

        /** @var FacilityDashboardRepository $repo */
        $repo = $this->em->getRepository(FacilityDashboard::class);

        /** @var FacilityRepository $facilityRepo */
        $facilityRepo = $this->em->getRepository(Facility::class);

        $dateFrom = $dateTo = new \DateTime('now');
        $dateFromFormatted = $dateFrom->format('Y-m-01 00:00:00');
        $dateToFormatted = $dateTo->format('Y-m-t 23:59:59');

        $facilityId = null;
        if (!empty($params) && !empty($params[0]['facility_id'])) {
            $facilityId = $params[0]['facility_id'];

            if (!empty($params[0]['date_from'])) {
                $dateFrom = new \DateTime($params[0]['date_from']);
                $dateFromFormatted = $dateFrom->format('Y-m-01 00:00:00');
            }

            if (!empty($params[0]['date_to'])) {
                $dateTo = new \DateTime($params[0]['date_to']);
                $dateToFormatted = $dateTo->format('Y-m-t 23:59:59');
            }

            $facilities = $facilityRepo->getBy($currentSpace, $this->grantService->getCurrentUserEntityGrants(Facility::class), $facilityId);
        } else {
            $facilities = $facilityRepo->list($currentSpace, $this->grantService->getCurrentUserEntityGrants(Facility::class));
        }

        if (empty($facilities)) {
            throw new FacilityNotFoundException();
        }

        $dateFrom = new \DateTime($dateFromFormatted);
        $dateTo = new \DateTime($dateToFormatted);

        if ($dateFrom > $dateTo) {
            throw new StartGreaterEndDateException();
        }

        //TODO for test (must remove)/////////////////
//        $dateFrom = new \DateTime('2018-11-01 00:00:00');
//        $dateTo = new \DateTime('2020-01-31 23:59:59');
        ///////////////////////////////////

        $interval = ImtDateTimeInterval::getWithDateTimes($dateFrom, $dateTo);
        $dateToClone = clone $dateTo;

        $subIntervals = [];
        while ($dateToClone >= $dateFrom) {
            $start = new \DateTime($dateToClone->format('Y-m-01 00:00:00'));
            $end = new \DateTime($dateToClone->format('Y-m-t 23:59:59'));
            $axis = $start->format('M').'-'.$start->format('y');

            $subIntervals[$axis] = [
                'dateFrom' => $start,
                'dateTo' => $end,
            ];

            $dateToClone->modify('last day of previous month');
        }

        $subIntervals = array_reverse($subIntervals);

        $dashboards = $repo->list($currentSpace, $this->grantService->getCurrentUserEntityGrants(FacilityDashboard::class), $this->grantService->getCurrentUserEntityGrants(Facility::class), $interval, $facilityId);

        $nestedData = [];
        $data = [];
        /** @var Facility $facility */
        foreach ($facilities as $facility) {
            foreach ($subIntervals as $key => $subInterval) {
                $days = 0;
                $totalCapacity = 0;
                $breakEven = 0;
                $capacityYellow = 0;
                $startingOccupancy = 0;
                $endingOccupancy = 0;
                $moveInsRespite = 0;
                $moveInsLongTerm = 0;
                $moveOutsRespite = 0;
                $moveOutsLongTerm = 0;
                $hotLeads = 0;
                $noticeToVacate = 0;
                $projectedNearTermOccupancy = 0;
                $toursPerMonth = 0;
                $totalInquiries = 0;
                $qualifiedInquiries = 0;
                $outreachPerMonth = 0;
                $eventsPerMonth = 0;
                $averageRoomRent = 0;
                foreach ($dashboards as $dashboard) {
                    $i = 0;
                    if ($dashboard['date'] >= $subInterval['dateFrom'] && $dashboard['date'] <= $subInterval['dateTo'] && $dashboard['facilityId'] === $facility->getId()) {
                        $i ++;

                        $days += $i;

                        $totalCapacity += $dashboard['totalCapacity'];
                        $breakEven += $dashboard['breakEven'];
                        $capacityYellow += $dashboard['capacityYellow'];
                        if ($dashboard['date']->format('Y-m-d H:i:s') === $subInterval['dateFrom']->format('Y-m-d H:i:s')) {
                            $startingOccupancy = $dashboard['occupancy'];
                        }
                        $endingOccupancy = $dashboard['occupancy'];
                        $moveInsRespite += $dashboard['moveInsRespite'];
                        $moveInsLongTerm += $dashboard['moveInsLongTerm'];
                        $moveOutsRespite += $dashboard['moveOutsRespite'];
                        $moveOutsLongTerm += $dashboard['moveOutsLongTerm'];
                        $hotLeads += $dashboard['hotLeads'];
                        $outreachPerMonth += $dashboard['outreachPerMonth'];
                    }
                }

                $nestedData[$key] = [
                    'total_capacity' => $days > 0 ? (int) round($totalCapacity / $days) : 0,
                    'break_even' => $days > 0 ? (int) round($breakEven / $days) : 0,
                    'capacity_yellow' => $days > 0 ? (int) round($capacityYellow / $days) : 0,
                    'starting_occupancy' => $startingOccupancy,
                    'ending_occupancy' => $endingOccupancy,
                    'move_ins_respite' => $moveInsRespite,
                    'move_ins_long_term' => $moveInsLongTerm,
                    'move_outs_respite' => $moveOutsRespite,
                    'move_outs_long_term' => $moveOutsLongTerm,
                    'hot_leads' => $hotLeads,
                    'notice_to_vacate' => $noticeToVacate,
                    'projected_near_term_occupancy' => $projectedNearTermOccupancy,
                    'tours_per_month' => $toursPerMonth,
                    'total_inquiries' => $totalInquiries,
                    'qualified_inquiries' => $qualifiedInquiries,
                    'outreach_per_month' => $outreachPerMonth,
                    'events_per_month' => $eventsPerMonth,
                    'average_room_rent' => $averageRoomRent,
                ];
            }

            $data[] = [
                'id' => $facility->getId(),
                'name' => $facility->getName(),
                'data' => $nestedData
            ];
        }

        return $data;
    }

    /**
     * @param $id
     * @return FacilityDashboard|null|object
     */
    public function getById($id)
    {
        /** @var FacilityDashboardRepository $repo */
        $repo = $this->em->getRepository(FacilityDashboard::class);

        return $repo->getOne($this->grantService->getCurrentSpace(), $this->grantService->getCurrentUserEntityGrants(FacilityDashboard::class), $this->grantService->getCurrentUserEntityGrants(Facility::class), $id);
    }

    /**
     * @param array $params
     * @return int|null
     * @throws \Throwable
     */
    public function add(array $params) : ?int
    {
        $insert_id = null;
        try {
            $this->em->getConnection()->beginTransaction();

            $facilityId = $params['facility_id'] ?? 0;

            /** @var FacilityRepository $facilityRepo */
            $facilityRepo = $this->em->getRepository(Facility::class);

            /** @var Facility $facility */
            $facility = $facilityRepo->getOne($this->grantService->getCurrentSpace(), $this->grantService->getCurrentUserEntityGrants(Facility::class), $facilityId);

            if ($facility === null) {
                throw new FacilityNotFoundException();
            }

            $totalCapacity = $params['totalCapacity'] ? (int)$params['totalCapacity'] : 0;
            $breakEven = $params['breakEven'] ? (int)$params['breakEven'] : 0;
            $capacityYellow = $params['capacityYellow'] ? (int)$params['capacityYellow'] : 0;

            $facilityDashboard = new FacilityDashboard();
            $facilityDashboard->setFacility($facility);
            $facilityDashboard->setTotalCapacity($totalCapacity);
            $facilityDashboard->setBreakEven($breakEven);
            $facilityDashboard->setCapacityYellow($capacityYellow);

            $date = $params['date'];

            if (!empty($date)) {
                $date = new \DateTime($params['date']);
            }

            $facilityDashboard->setDate($date);

            $this->validate($facilityDashboard, null, ['api_admin_facility_dashboard_add']);

            $this->em->persist($facilityDashboard);
            $this->em->flush();
            $this->em->getConnection()->commit();

            $insert_id = $facilityDashboard->getId();
        } catch (\Exception $e) {
            $this->em->getConnection()->rollBack();

            throw $e;
        }

        return $insert_id;
    }

    /**
     * @param $id
     * @param array $params
     * @throws \Throwable
     */
    public function edit($id, array $params) : void
    {
        try {

            $this->em->getConnection()->beginTransaction();

            $currentSpace = $this->grantService->getCurrentSpace();

            /** @var FacilityDashboardRepository $repo */
            $repo = $this->em->getRepository(FacilityDashboard::class);

            /** @var FacilityDashboard $entity */
            $entity = $repo->getOne($currentSpace, $this->grantService->getCurrentUserEntityGrants(FacilityDashboard::class), $this->grantService->getCurrentUserEntityGrants(Facility::class), $id);

            if ($entity === null) {
                throw new FacilityDashboardNotFoundException();
            }

            $facilityId = $params['facility_id'] ?? 0;

            /** @var FacilityRepository $facilityRepo */
            $facilityRepo = $this->em->getRepository(Facility::class);

            /** @var Facility $facility */
            $facility = $facilityRepo->getOne($currentSpace, $this->grantService->getCurrentUserEntityGrants(Facility::class), $facilityId);

            if ($facility === null) {
                throw new FacilityNotFoundException();
            }

            $totalCapacity = $params['totalCapacity'] ? (int)$params['totalCapacity'] : 0;
            $breakEven = $params['breakEven'] ? (int)$params['breakEven'] : 0;
            $capacityYellow = $params['capacityYellow'] ? (int)$params['capacityYellow'] : 0;

            $entity->setFacility($facility);
            $entity->setTotalCapacity($totalCapacity);
            $entity->setBreakEven($breakEven);
            $entity->setCapacityYellow($capacityYellow);

            $date = $params['date'];

            if (!empty($date)) {
                $date = new \DateTime($params['date']);
            }

            $entity->setDate($date);

            $this->validate($entity, null, ['api_admin_facility_dashboard_edit']);

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

            /** @var FacilityDashboardRepository $repo */
            $repo = $this->em->getRepository(FacilityDashboard::class);

            /** @var FacilityDashboard $entity */
            $entity = $repo->getOne($this->grantService->getCurrentSpace(), $this->grantService->getCurrentUserEntityGrants(FacilityDashboard::class), $this->grantService->getCurrentUserEntityGrants(Facility::class), $id);

            if ($entity === null) {
                throw new FacilityDashboardNotFoundException();
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
                throw new FacilityDashboardNotFoundException();
            }

            /** @var FacilityDashboardRepository $repo */
            $repo = $this->em->getRepository(FacilityDashboard::class);

            $facilityDashboards = $repo->findByIds($this->grantService->getCurrentSpace(), $this->grantService->getCurrentUserEntityGrants(FacilityDashboard::class), $this->grantService->getCurrentUserEntityGrants(Facility::class), $ids);

            if (empty($facilityDashboards)) {
                throw new FacilityDashboardNotFoundException();
            }

            /**
             * @var FacilityDashboard $facilityDashboard
             */
            foreach ($facilityDashboards as $facilityDashboard) {
                $this->em->remove($facilityDashboard);
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
            throw new FacilityDashboardNotFoundException();
        }

        /** @var FacilityDashboardRepository $repo */
        $repo = $this->em->getRepository(FacilityDashboard::class);

        $entities = $repo->findByIds($this->grantService->getCurrentSpace(), $this->grantService->getCurrentUserEntityGrants(FacilityDashboard::class), $this->grantService->getCurrentUserEntityGrants(Facility::class), $ids);

        if (empty($entities)) {
            throw new FacilityDashboardNotFoundException();
        }

        return $this->getRelatedData(FacilityDashboard::class, $entities);
    }
}
