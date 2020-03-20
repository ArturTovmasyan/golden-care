<?php

namespace App\Command;

use App\Api\V1\Common\Service\BaseService;
use App\Api\V1\Common\Service\Exception\FacilityNotFoundException;
use App\Api\V1\Common\Service\Exception\ValidationException;
use App\Api\V1\Common\Service\GrantService;
use App\Api\V1\Component\Rent\RentPeriodFactory;
use App\Entity\Facility;
use App\Entity\FacilityBed;
use App\Entity\FacilityDashboard;
use App\Entity\FacilityRoom;
use App\Entity\Lead\Activity;
use App\Entity\Lead\Lead;
use App\Entity\Lead\LeadTemperature;
use App\Entity\Lead\Outreach;
use App\Entity\ResidentAdmission;
use App\Entity\ResidentRent;
use App\Entity\User;
use App\Model\AdmissionType;
use App\Model\GroupType;
use App\Model\RentPeriod;
use App\Repository\FacilityBedRepository;
use App\Repository\FacilityRepository;
use App\Repository\FacilityRoomRepository;
use App\Repository\Lead\ActivityRepository;
use App\Repository\Lead\LeadRepository;
use App\Repository\Lead\LeadTemperatureRepository;
use App\Repository\Lead\OutreachRepository;
use App\Repository\ResidentAdmissionRepository;
use App\Repository\ResidentRentRepository;
use App\Repository\UserRepository;
use App\Util\Common\ImtDateTimeInterval;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Command\LockableTrait;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class FacilityDashboardCommand extends Command
{
    use LockableTrait;

    /**
     * @var EntityManagerInterface
     */
    private $em;

    /**
     * @var BaseService
     */
    protected $baseService;

    /**
     * @var GrantService
     */
    protected $grantService;

    /**
     * InviteCustomerCommand constructor.
     * @param EntityManagerInterface $em
     * @param BaseService $baseService
     * @param GrantService $grantService
     */
    public function __construct(EntityManagerInterface $em, BaseService $baseService, GrantService $grantService)
    {
        parent::__construct();
        $this->em = $em;
        $this->baseService = $baseService;
        $this->grantService = $grantService;
    }

    /**
     *
     */
    protected function configure(): void
    {
        $this
            ->setName('app:facility:dashboard')
            ->setDescription('Facility Dashboard data.')
            ->setHelp('This command allows you add facility dashboard data...');
    }

    /**
     * @param InputInterface $input
     * @param OutputInterface $output
     * @return int
     */
    protected function execute(InputInterface $input, OutputInterface $output): ?int
    {
        if (!$this->lock()) {
            $output->writeln('The command is already running in another process.');

            return 1;
        }

        try {
            $this->em->getConnection()->beginTransaction();

            $currentSpace = $this->grantService->getCurrentSpace();

            /** @var FacilityRepository $facilityRepo */
            $facilityRepo = $this->em->getRepository(Facility::class);
            /** @var FacilityBedRepository $bedRepo */
            $bedRepo = $this->em->getRepository(FacilityBed::class);

            $facilities = $facilityRepo->list($currentSpace, null);

            if (empty($facilities)) {
                throw new FacilityNotFoundException();
            }

            $yesterday = new \DateTime('-1 day');
            $dateFormatted = $yesterday->format('Y-m-d 00:00:00');
            $date = new \DateTime($dateFormatted);

            $startDate = $date;
            $endDate = new \DateTime($date->format('Y-m-d 23:59:59'));

            $monthStartDate = new \DateTime($startDate->format('Y-m-01 00:00:00'));
            $monthEndDate = new \DateTime($endDate->format('Y-m-t 23:59:59'));

            /** @var ResidentAdmissionRepository $admissionRepo */
            $admissionRepo = $this->em->getRepository(ResidentAdmission::class);
            $activeAdmissions = $admissionRepo->getActiveResidentsForFacilityDashboard($currentSpace, null, null);
            $admissions = $admissionRepo->getResidentsForFacilityDashboard($currentSpace, null, null, $monthStartDate, $monthEndDate);

            $dischargedResidentIds = [];
            $longTermResidentIds = [];
            $shortTermResidentIds = [];
            if (!empty($admissions)) {
                foreach ($admissions as $admission) {
                    if ($admission['admissionType'] === AdmissionType::DISCHARGE) {
                        $dischargedResidentIds[] = $admission['id'];
                    }
                }

                $longTermAdmissions = $admissionRepo->getLongTermAdmittedResidentIds($currentSpace, null, null, $dischargedResidentIds);
                $longTermResidentIds = array_map(static function ($item) {
                    return $item['leadId'];
                }, $longTermAdmissions);

                $shortTermAdmissions = $admissionRepo->getShortTermAdmittedResidentIds($currentSpace, null, null, $dischargedResidentIds);
                $shortTermResidentIds = array_map(static function ($item) {
                    return $item['id'];
                }, $shortTermAdmissions);
            }

            /** @var LeadRepository $leadRepo */
            $leadRepo = $this->em->getRepository(Lead::class);
            $monthWebLeads = $leadRepo->getWebLeadsForFacilityDashboard($currentSpace, null, $monthStartDate, $monthEndDate);

            /** @var LeadTemperatureRepository $leadTemperatureRepo */
            $leadTemperatureRepo = $this->em->getRepository(LeadTemperature::class);
            $leadTemperatures = $leadTemperatureRepo->getHotLeadsForFacilityDashboard($currentSpace, null, $monthStartDate, $monthEndDate);

            /** @var ActivityRepository $activityRepo */
            $activityRepo = $this->em->getRepository(Activity::class);
            $leadTourActivities = $activityRepo->getLeadTourActivitiesForFacilityDashboard($currentSpace, null, $monthStartDate, $monthEndDate);

            $leads = $leadRepo->getLeadsForFacilityDashboard($currentSpace, null, $monthStartDate, $monthEndDate);

            /** @var OutreachRepository $outreachRepo */
            $outreachRepo = $this->em->getRepository(Outreach::class);
            $outreaches = $outreachRepo->getOutreachesForFacilityDashboard($currentSpace, null, $monthStartDate, $monthEndDate);

            $finalOutreaches = [];
            if (!empty($outreaches)) {
                $modifiedOutreaches = [];
                $allUserIds = [];

                foreach ($outreaches as $outreach) {
                    $modifiedOutreaches[$outreach['id']] = explode(',', $outreach['participants']);
                }

                foreach ($modifiedOutreaches as $userIds) {
                    foreach ($userIds as $userId) {
                        $allUserIds[] = $userId;
                    }
                }

                $allUserIds = array_unique($allUserIds);

                /** @var UserRepository $userRepo */
                $userRepo = $this->em->getRepository(User::class);
                $userFacilities = $userRepo->getFacilityIdsByIds($currentSpace, null, $allUserIds);

                foreach ($modifiedOutreaches as $outreachId => $userIds) {
                    $allowedFacilityIds = [];
                    foreach ($userFacilities as $userFacility) {
                        if (\in_array($userFacility['id'], $userIds, false)) {
                            $userFacilityIds = $userFacility['facilityIds'] !== null ? explode(',', $userFacility['facilityIds']) : [];

                            $allowedFacilityIds = array_merge([], $userFacilityIds);
                            $allowedFacilityIds = array_unique($allowedFacilityIds);

                            if ($userFacility['facilityIds'] === null) {
                                $allowedFacilityIds = [];
                                break;
                            }
                        }
                    }

                    $finalOutreaches[] = [
                        'id' => $outreachId,
                        'facilityIds' => $allowedFacilityIds,
                        'all' => empty($allowedFacilityIds) ? true : false
                    ];
                }
            }

            /** @var ResidentRentRepository $rentRepo */
            $rentRepo = $this->em->getRepository(ResidentRent::class);
            $subInterval = ImtDateTimeInterval::getWithDateTimes($monthStartDate, $monthEndDate);

            $residentRents = $rentRepo->getAdmissionRoomRentDataForFacilityDashboard($currentSpace, null, GroupType::TYPE_FACILITY, $subInterval, null, null);
            $rents = [];
            $countResidentIds = [];
            if (!empty($residentRents)) {
                $rentPeriodFactory = RentPeriodFactory::getFactory($subInterval);

                foreach ($residentRents as $rent) {
                    $rents[] = [
                        'typeId' => $rent['typeId'],
                        'amount' => $rentPeriodFactory->calculateForFacilityDashboard(
                            ImtDateTimeInterval::getWithDateTimes($subInterval->getStart(), $subInterval->getEnd()),
                            RentPeriod::MONTHLY,
                            $rent['amount']
                        )
                    ];

                    $countResidentIds[$rent['typeId']] = array_key_exists($rent['typeId'], $countResidentIds) ? $countResidentIds[$rent['typeId']] + 1 : 1;
                }
            }

            /** @var FacilityRoomRepository $facilityRoomRepo */
            $facilityRoomRepo = $this->em->getRepository(FacilityRoom::class);

            $roomTypeIds = [];
            $roomTypeValues = [];
            /** @var Facility $facility */
            foreach ($facilities as $facility) {
                $entity = new FacilityDashboard();
                $entity->setFacility($facility);
                $entity->setDate($date);
                $entity->setBedsLicensed($facility->getBedsLicensed());
                $entity->setBedsTarget($facility->getBedsTarget());
                $entity->setBedsConfigured($bedRepo->getBedCount($facility->getId()));
                $entity->setYellowFlag($facility->getYellowFlag());
                $entity->setRedFlag($facility->getRedFlag());

                $occupancy = 0;
                $roomTypeValues[$facility->getId()] = [];
                $roomTypeIds[$facility->getId()] = [];
                if (!empty($activeAdmissions)) {
                    foreach ($activeAdmissions as $activeAdmission) {
                        $i = 0;
                        if ($activeAdmission['typeId'] === $facility->getId()) {
                            $i++;

                            $occupancy += $i;

                            $roomTypeIds[$facility->getId()][] = $activeAdmission['roomTypeId'];
                        }
                    }

                    $roomTypeIds[$facility->getId()] = array_unique($roomTypeIds[$facility->getId()]);
                    $roomTypeIds[$facility->getId()] = array_values($roomTypeIds[$facility->getId()]);

                    $rooms = $facilityRoomRepo->getByRoomTypeIds($currentSpace, null, null, $roomTypeIds[$facility->getId()]);

                    foreach ($roomTypeIds[$facility->getId()] as $roomTypeId) {
                        $countRooms = 0;
                        foreach ($rooms as $room) {
                            if ($room['roomTypeId'] === $roomTypeId) {
                                ++$countRooms;
                            }
                        }

                        $roomTypeValues[$facility->getId()][$roomTypeId] = $countRooms;
                    }
                }
                $entity->setOccupancy($occupancy);
                $entity->setRoomTypeValues($roomTypeValues[$facility->getId()]);

                $moveInsRespite = 0;
                $moveInsLongTerm = 0;
                $moveOutsRespite = 0;
                $moveOutsLongTerm = 0;
                $noticeToVacate = 0;
                if (!empty($admissions)) {
                    foreach ($admissions as $admission) {
                        $j = 0;
                        $k = 0;
                        $l = 0;
                        $m = 0;
                        $h = 0;
                        if ($admission['typeId'] === $facility->getId()) {
                            if ($admission['admissionType'] === AdmissionType::SHORT_ADMIT) {
                                $j++;

                                $moveInsRespite += $j;
                            }

                            if ($admission['admissionType'] === AdmissionType::LONG_ADMIT) {
                                $k++;

                                $moveInsLongTerm += $k;
                            }

                            if ($admission['admissionType'] === AdmissionType::DISCHARGE && \in_array($admission['id'], $shortTermResidentIds, false)) {
                                $l++;

                                $moveOutsRespite += $l;
                            }

                            if (($admission['admissionType'] === AdmissionType::DISCHARGE && \in_array($admission['id'], $longTermResidentIds, false)) ||
                                ($admission['admissionType'] === AdmissionType::DISCHARGE && !\in_array($admission['id'], $shortTermResidentIds, false))) {
                                $m++;

                                $moveOutsLongTerm += $m;
                            }

                            if ($admission['admissionType'] === AdmissionType::PENDING_DISCHARGE) {
                                $h++;

                                $noticeToVacate += $h;
                            }
                        }
                    }
                }
                $entity->setMoveInsRespite($moveInsRespite);
                $entity->setMoveInsLongTerm($moveInsLongTerm);
                $entity->setMoveOutsRespite($moveOutsRespite);
                $entity->setMoveOutsLongTerm($moveOutsLongTerm);
                $entity->setNoticeToVacate($noticeToVacate);

                $webLeads = 0;
                if (!empty($monthWebLeads)) {
                    foreach ($monthWebLeads as $webLead) {
                        $r = 0;
                        if ($webLead['typeId'] === $facility->getId()) {
                            $r++;

                            $webLeads += $r;
                        }
                    }
                }
                $entity->setWebLeads($webLeads);

                $hotLeads = 0;
                if (!empty($leadTemperatures)) {
                    foreach ($leadTemperatures as $hotLead) {
                        $n = 0;
                        if ($hotLead['typeId'] === $facility->getId()) {
                            $n++;

                            $hotLeads += $n;
                        }
                    }
                }
                $entity->setHotLeads($hotLeads);

                $toursPerMonth = 0;
                if (!empty($leadTourActivities)) {
                    foreach ($leadTourActivities as $activity) {
                        $o = 0;
                        if ($activity['typeId'] === $facility->getId()) {
                            $o++;

                            $toursPerMonth += $o;
                        }
                    }
                }
                $entity->setToursPerMonth($toursPerMonth);

                $totalInquiries = 0;
                if (!empty($leads)) {
                    foreach ($leads as $lead) {
                        $p = 0;
                        if ($lead['typeId'] === $facility->getId()) {
                            $p++;

                            $totalInquiries += $p;
                        }
                    }
                }
                $entity->setTotalInquiries($totalInquiries);

                $outreachPerMonth = 0;
                if (!empty($finalOutreaches)) {
                    foreach ($finalOutreaches as $outreach) {
                        $q = 0;
                        if ($outreach['all'] === true || ($outreach['all'] === false && \in_array($facility->getId(), $outreach['facilityIds'], false))) {
                            $q++;

                            $outreachPerMonth += $q;
                        }
                    }
                }
                $entity->setOutreachPerMonth($outreachPerMonth);

                $averageRoomRent = 0;
                if (!empty($rents)) {
                    foreach ($rents as $rent) {
                        if ($rent['typeId'] === $facility->getId()) {
                            $averageRoomRent += $rent['amount'];
                        }
                    }

                    if (array_key_exists($facility->getId(), $countResidentIds)) {
                        $averageRoomRent /= $countResidentIds[$facility->getId()];
                    }
                }

                $entity->setAverageRoomRent(round($averageRoomRent, 2));

                $this->baseService->validate($entity, null, ['api_admin_facility_dashboard_add']);

                $this->em->persist($entity);
            }

            $this->em->flush();

            $this->em->getConnection()->commit();

            $output->writeln('Facility Dashboard data successfully added');
        } catch (\Exception $e) {
            $this->em->getConnection()->rollBack();

            if ($e instanceof ValidationException) {
                $output->writeln($e->getErrors());
            } else {
                $output->writeln($e->getMessage());
            }
        }

        $this->release();
        return 0;
    }
}