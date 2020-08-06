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
    private const TYPE_MONTHLY = 1;
    private const TYPE_WEEKLY = 2;

    /**
     * @param QueryBuilder $queryBuilder
     * @param $params
     */
    public function gridSelect(QueryBuilder $queryBuilder, $params): void
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
        $list = $this->getFacilityDashboardList($params);

        return $list['data'];
    }

    /**
     * @param $params
     * @return mixed
     */
    public function getFacilityDashboardList($params)
    {
        $currentSpace = $this->grantService->getCurrentSpace();

        /** @var FacilityDashboardRepository $repo */
        $repo = $this->em->getRepository(FacilityDashboard::class);

        /** @var FacilityRepository $facilityRepo */
        $facilityRepo = $this->em->getRepository(Facility::class);

        $facilityId = null;
        $type = null;
        if (!empty($params) && !empty($params[0]['facility_id'])) {
            $facilityId = $params[0]['facility_id'];

            $type = self::TYPE_MONTHLY;
            if (!empty($params[0]['type'])) {
                $type = (int)$params[0]['type'];
            }

            $facilities = $facilityRepo->getBy($currentSpace, $this->grantService->getCurrentUserEntityGrants(Facility::class), $facilityId);
        } else {
            $facilities = $facilityRepo->list($currentSpace, $this->grantService->getCurrentUserEntityGrants(Facility::class));
        }

        if (empty($facilities)) {
            throw new FacilityNotFoundException();
        }

        if ($type === self::TYPE_WEEKLY) {
            $dateFrom = new \DateTime('now');
            $dateFromFormatted = $dateFrom->format('Y-m-01 00:00:00');
            $dateToFormatted = $dateFrom->format('Y-m-t 23:59:59');

            if (!empty($params) && !empty($params[0]['date_from'])) {
                $dateFrom = new \DateTime($params[0]['date_from']);
                $dateFromFormatted = $dateFrom->format('Y-m-01 00:00:00');
                $dateToFormatted = $dateFrom->format('Y-m-t 23:59:59');
            }

            $dateFrom = new \DateTime($dateFromFormatted);
            $dateTo = new \DateTime($dateToFormatted);

            if ($dateFrom > $dateTo) {
                throw new StartGreaterEndDateException();
            }

            $interval = ImtDateTimeInterval::getWithDateTimes($dateFrom, $dateTo);

            $dateInterval = new \DateInterval('P1D');
            $dateRange = new \DatePeriod($dateFrom, $dateInterval, $dateTo);

            $weekNumber = 1;
            $weeks = [];
            foreach ($dateRange as $date) {
                $weeks[$weekNumber][] = $date->format('Y-m-d');
                if ((int)$date->format('w') === 6) {
                    $weekNumber++;
                }
            }

            function find_saturday($date) {
                if (date('w', strtotime($date)) == 6) {
                    return date('m-d-Y', strtotime($date));
                }
                return date('m-d-Y', strtotime('last Saturday', strtotime($date)));
            }

            $subIntervals = [];
            foreach ($weeks as $axis => $week) {
                $start = new \DateTime(array_shift($week));
                if (\count($weeks[$axis]) === 1) {
                    $end = new \DateTime($start->format('Y-m-d'));
                } else {
                    $end = new \DateTime(array_pop($week));
                }
                $end->setTime(23, 59, 59);

                $subIntervals['W' . '-' . $end->format('W') . ' ' . find_saturday($end->format('Y-m-d'))] = [
                    'dateFrom' => $start,
                    'dateTo' => $end,
                    'days' => $end->diff($start)->days + 1
                ];
            }
        } else {
            $dateFrom = $dateTo = new \DateTime('now');
            $dateFromFormatted = $dateFrom->format('Y-m-01 00:00:00');
            $dateToFormatted = $dateTo->format('Y-m-t 23:59:59');

            if (!empty($params) && !empty($params[0]['date_from'])) {
                $dateFrom = new \DateTime($params[0]['date_from']);
                $dateFromFormatted = $dateFrom->format('Y-m-01 00:00:00');
            }

            if (!empty($params) && !empty($params[0]['date_to'])) {
                $dateTo = new \DateTime($params[0]['date_to']);
                $dateToFormatted = $dateTo->format('Y-m-t 23:59:59');
            }

            $dateFrom = new \DateTime($dateFromFormatted);
            $dateTo = new \DateTime($dateToFormatted);

            if ($dateFrom > $dateTo) {
                throw new StartGreaterEndDateException();
            }

            $interval = ImtDateTimeInterval::getWithDateTimes($dateFrom, $dateTo);
            $dateToClone = clone $dateTo;

            $subIntervals = [];
            while ($dateToClone >= $dateFrom) {
                $start = new \DateTime($dateToClone->format('Y-m-01 00:00:00'));
                $end = new \DateTime($dateToClone->format('Y-m-t 23:59:59'));
                $axis = $start->format('M') . '-' . $start->format('y');

                $subIntervals[$axis] = [
                    'dateFrom' => $start,
                    'dateTo' => $end,
                    'days' => $end->diff($start)->days + 1,
                ];

                $dateToClone->modify('last day of previous month');
            }

            $subIntervals = array_reverse($subIntervals);
        }

        $dashboards = $repo->list($currentSpace, $this->grantService->getCurrentUserEntityGrants(FacilityDashboard::class), $this->grantService->getCurrentUserEntityGrants(Facility::class), $interval, $facilityId);

        $nestedData = [];
        $data = [];
        /** @var Facility $facility */
        foreach ($facilities as $facility) {
            foreach ($subIntervals as $key => $subInterval) {
                $days = 0;
                $bedsLicensed = 0;
                $bedsTarget = 0;
                $bedsConfigured = 0;
                $yellowFlag = 0;
                $redFlag = 0;
                $startingOccupancy = 0;
                $endingOccupancy = 0;
                $moveInsRespite = 0;
                $moveInsLongTerm = 0;
                $moveOutsRespite = 0;
                $moveOutsLongTerm = 0;
                $webLeads = 0;
                $hotLeads = 0;
                $noticeToVacate = 0;
                $projectedNearTermOccupancy = 0;//?
                $toursPerMonth = 0;
                $totalInquiries = 0;
                $qualifiedInquiries = 0;
                $notSureInquiries = 0;
                $notQualifiedInquiries = 0;
                $outreachPerMonth = 0;
                $averageRoomRent = 0;
                foreach ($dashboards as $dashboard) {
                    $i = 0;
                    if ($dashboard['date'] >= $subInterval['dateFrom'] && $dashboard['date'] <= $subInterval['dateTo'] && $dashboard['facilityId'] === $facility->getId()) {
                        $i++;

                        $days += $i;

                        $bedsLicensed += $dashboard['bedsLicensed'];
                        $bedsTarget += $dashboard['bedsTarget'];
                        $bedsConfigured += $dashboard['bedsConfigured'];
                        $yellowFlag += $dashboard['yellowFlag'];
                        $redFlag += $dashboard['redFlag'];
                        if ($dashboard['date']->format('Y-m-d H:i:s') === $subInterval['dateFrom']->format('Y-m-d H:i:s')) {
                            $startingOccupancy = $dashboard['occupancy'];
                        }
                        $endingOccupancy = $dashboard['occupancy'];
                        $moveInsRespite = $dashboard['moveInsRespite'];
                        $moveInsLongTerm = $dashboard['moveInsLongTerm'];
                        $moveOutsRespite = $dashboard['moveOutsRespite'];
                        $moveOutsLongTerm = $dashboard['moveOutsLongTerm'];
                        $webLeads = $dashboard['webLeads'];
                        $hotLeads = $dashboard['hotLeads'];
                        $noticeToVacate = $dashboard['noticeToVacate'];
                        $toursPerMonth = $dashboard['toursPerMonth'];
                        $totalInquiries = $dashboard['totalInquiries'];
                        $qualifiedInquiries = $dashboard['qualifiedInquiries'];
                        $notSureInquiries = $dashboard['notSureInquiries'];
                        $notQualifiedInquiries = $dashboard['notQualifiedInquiries'];
                        $outreachPerMonth = $dashboard['outreachPerMonth'];
                        $averageRoomRent = $dashboard['averageRoomRent'];
                    }
                }

                $nestedData[$key] = [
                    'beds_licensed' => $days > 0 ? (int)round($bedsLicensed / $days) : 0,
                    'beds_target' => $days > 0 ? (int)round($bedsTarget / $days) : 0,
                    'beds_configured' => $days > 0 ? (int)round($bedsConfigured / $days) : 0,
                    'yellow_flag' => $days > 0 ? (int)round($yellowFlag / $days) : 0,
                    'red_flag' => $days > 0 ? (int)round($redFlag / $days) : 0,
                    'starting_occupancy' => $startingOccupancy,
                    'ending_occupancy' => $endingOccupancy,
                    'move_ins_respite' => $moveInsRespite,
                    'move_ins_long_term' => $moveInsLongTerm,
                    'move_outs_respite' => $moveOutsRespite,
                    'move_outs_long_term' => $moveOutsLongTerm,
                    'web_leads' => $webLeads,
                    'hot_leads' => $hotLeads,
                    'notice_to_vacate' => $noticeToVacate,
                    'projected_near_term_occupancy' => $projectedNearTermOccupancy,
                    'tours_per_month' => $toursPerMonth,
                    'total_inquiries' => $totalInquiries,
                    'qualified_inquiries' => $qualifiedInquiries,
                    'not_sure_inquiries' => $notSureInquiries,
                    'not_qualified_inquiries' => $notQualifiedInquiries,
                    'outreach_per_month' => $outreachPerMonth,
                    'average_room_rent' => $averageRoomRent,
                ];
            }

            $data[] = [
                'id' => $facility->getId(),
                'name' => $facility->getName(),
                'data' => $nestedData
            ];
        }

        return ['data' => $data, 'subIntervals' => $subIntervals];
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
    public function add(array $params): ?int
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

            $bedsLicensed = $params['beds_licensed'] ? (int)$params['beds_licensed'] : 0;
            $bedsTarget = $params['beds_target'] ? (int)$params['beds_target'] : 0;
            $bedsConfigured = $params['beds_configured'] ? (int)$params['beds_configured'] : 0;
            $yellowFlag = $params['yellow_flag'] ? (int)$params['yellow_flag'] : 0;
            $redFlag = $params['red_flag'] ? (int)$params['red_flag'] : 0;

            $facilityDashboard = new FacilityDashboard();
            $facilityDashboard->setFacility($facility);
            $facilityDashboard->setBedsLicensed($bedsLicensed);
            $facilityDashboard->setBedsTarget($bedsTarget);
            $facilityDashboard->setBedsConfigured($bedsConfigured);
            $facilityDashboard->setYellowFlag($yellowFlag);
            $facilityDashboard->setRedFlag($redFlag);

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
    public function edit($id, array $params): void
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

            $bedsLicensed = $params['beds_licensed'] ? (int)$params['beds_licensed'] : 0;
            $bedsTarget = $params['beds_target'] ? (int)$params['beds_target'] : 0;
            $bedsConfigured = $params['beds_configured'] ? (int)$params['beds_configured'] : 0;
            $yellowFlag = $params['yellow_flag'] ? (int)$params['yellow_flag'] : 0;
            $redFlag = $params['red_flag'] ? (int)$params['red_flag'] : 0;

            $entity->setFacility($facility);
            $entity->setBedsLicensed($bedsLicensed);
            $entity->setBedsTarget($bedsTarget);
            $entity->setBedsConfigured($bedsConfigured);
            $entity->setYellowFlag($yellowFlag);
            $entity->setRedFlag($redFlag);

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

    /**
     * @param $dateFrom
     * @param $dateTo
     * @return mixed
     */
    public function reportCsv($dateFrom, $dateTo)
    {
        $params[0] = [
            'date_from' => $dateFrom,
            'date_to' => $dateTo,
        ];

        return $this->getFacilityDashboardList($params);
    }
}
