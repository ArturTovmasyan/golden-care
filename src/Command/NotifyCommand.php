<?php

namespace App\Command;

use Ahc\Cron\Expression;
use App\Api\V1\Admin\Service\Report\ResidentReportService;
use App\Api\V1\Common\Service\AmazonSnsService;
use App\Api\V1\Common\Service\GrantService;
use App\Api\V1\Common\Service\Exception\IncorrectChangeLogException;
use App\Entity\Apartment;
use App\Entity\ChangeLog;
use App\Entity\CorporateEvent;
use App\Entity\CorporateEventUser;
use App\Entity\EmailLog;
use App\Entity\Facility;
use App\Entity\FacilityEvent;
use App\Entity\Lead\Activity;
use App\Entity\Notification;
use App\Entity\Region;
use App\Entity\ResidentRent;
use App\Entity\ResidentRentIncrease;
use App\Entity\ResidentResponsiblePerson;
use App\Entity\User;
use App\Model\ChangeLogType;
use App\Model\GroupType;
use App\Model\Lead\ActivityOwnerType;
use App\Model\NotificationTypeCategoryType;
use App\Repository\ChangeLogRepository;
use App\Repository\CorporateEventRepository;
use App\Repository\FacilityEventRepository;
use App\Repository\Lead\ActivityRepository;
use App\Repository\NotificationRepository;
use App\Repository\ResidentRentIncreaseRepository;
use App\Repository\ResidentRentRepository;
use App\Repository\ResidentResponsiblePersonRepository;
use App\Util\Mailer;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Command\LockableTrait;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

class NotifyCommand extends Command
{
    use LockableTrait;

    protected static $defaultName = 'app:notify';

    /** @var EntityManagerInterface */
    private $em;

    /** @var  GrantService */
    protected $grantService;

    /** @var Mailer */
    private $mailer;

    /** @var ContainerInterface */
    private $container;

    /** @var ResidentReportService */
    private $residentReportService;

    /** @var AmazonSnsService */
    private $amazonSnsService;

    public function __construct(
        EntityManagerInterface $em,
        GrantService $grantService,
        Mailer $mailer,
        ContainerInterface $container,
        ResidentReportService $residentReportService,
        AmazonSnsService $amazonSnsService
    )
    {
        $this->em = $em;
        $this->grantService = $grantService;
        $this->mailer = $mailer;
        $this->container = $container;
        $this->residentReportService = $residentReportService;
        $this->amazonSnsService = $amazonSnsService;

        parent::__construct();
    }


    protected function configure(): void
    {
    }

    protected function execute(InputInterface $input, OutputInterface $output): ?int
    {
        if (!$this->lock()) {
            $output->writeln('The command is already running in another process.');

            return 1;
        }

        /** @var NotificationRepository $repo */
        $repo = $this->em->getRepository(Notification::class);

        $notifications = $repo->findBy(['enabled' => true]);

        /** @var Notification $notification */
        foreach ($notifications as $notification) {

            if ($notification->getType() !== null && $notification->getSchedule() !== null && Expression::isDue($notification->getSchedule(), new \DateTime())) {

                $notificationEmails = $notification->getEmails();
                $userEmails = [];
                if (!empty($notification->getUsers())) {
                    /** @var User $user */
                    foreach ($notification->getUsers() as $user) {
                        if ($user->isenabled()) {
                            $userEmails[] = $user->getEmail();
                        }
                    }
                }
                $emails = array_merge($notificationEmails, $userEmails);
                $emails = array_unique($emails);

                switch ($notification->getType()->getCategory()) {
                    case NotificationTypeCategoryType::TYPE_SIXTY_DAYS_REPORT:
                        if ($notification->getType()->isEmail()) {
                            $this->sendSixtyDaysReportNotifications($emails, $notification->getFacilities(), $notification->getApartments(), $notification->getRegions(), $notification->getType()->getEmailSubject(), $notification->getType()->getEmailMessage());
                        }
                        break;
                    case NotificationTypeCategoryType::TYPE_LEAD_ACTIVITY:
                        if ($notification->getType()->isEmail() || $notification->getType()->isSms()) {
                            $this->sendTodayLeadActivityNotifications($emails, $notification->getType()->isEmail(), $notification->getType()->isSms());
                        }
                        break;
                    case NotificationTypeCategoryType::TYPE_LEAD_CHANGE_LOG:
                        if ($notification->getType()->isEmail()) {
                            $this->sendTodayLeadChangeLogNotifications($emails);
                        }
                        break;
                    case NotificationTypeCategoryType::TYPE_FACILITY_ACTIVITY:
                        if ($notification->getType()->isEmail()) {
                            $this->sendOnTheDayBeforeFacilityActivityNotifications($emails, $notification->getType()->isEmail());
                        }
                        break;
                    case NotificationTypeCategoryType::TYPE_CORPORATE_ACTIVITY:
                        if ($notification->getType()->isEmail()) {
                            $this->sendOnTheDayBeforeCorporateActivityNotifications($emails, $notification->getType()->isEmail());
                        }
                        break;
                    case NotificationTypeCategoryType::TYPE_RESIDENT_RENT_INCREASE:
                        if ($notification->getType()->isEmail()) {
                            $this->sendTodayResidentRentIncreaseNotifications($emails, $notification->getType()->isEmail());
                        }
                        break;
                }
            }
        }

        $this->release();

        return 0;
    }

    /**
     * @param array $emails
     * @param $facilities
     * @param $apartments
     * @param $regions
     * @param $subjectText
     * @param $message
     */
    public function sendSixtyDaysReportNotifications(array $emails, $facilities, $apartments, $regions, $subjectText, $message): void
    {
        $message = str_replace(['\r\n', '  '], ['<br>', '&nbsp;&nbsp;'], $message);

        $date = new \DateTime('now');

        if (!empty($facilities)) {
            /** @var Facility $facility */
            foreach ($facilities as $facility) {
                $groupId = $facility->getId();
                $groupName = $facility->getName();
                $data = $this->residentReportService->getSixtyDaysReport(GroupType::TYPE_FACILITY, false, null, $groupId, false, null, $date->format('m/d/Y'), null, null, null, null);

                $report = $this->container->get('templating')->render('@api_report/resident/sixty-days-roster.csv.twig', array(
                    'data' => $data
                ));

                $path = '/tmp/60DaysRosterCsv-' . lcfirst(GroupType::getTypes()[GroupType::TYPE_FACILITY]) . '-' . $date->format('m-d-Y') . '-' . uniqid('', false) . '.csv';
                file_put_contents($path, $report);

                $subject = $subjectText . ' - ' . $groupName . ' - ' . $date->format('F') . ' ' . $date->format('Y');

                $spaceName = '';
                if ($facility->getSpace() !== null) {
                    $spaceName = $facility->getSpace()->getName();
                }

                $status = $this->mailer->sendReportNotification($emails, $subject, $message, $path, $spaceName);

                $this->saveEmailLog($status, $subject, $spaceName, $emails);
            }
        }
        if (!empty($apartments)) {
            /** @var Apartment $apartment */
            foreach ($apartments as $apartment) {
                $groupId = $apartment->getId();
                $groupName = $apartment->getName();
                $data = $this->residentReportService->getSixtyDaysReport(GroupType::TYPE_APARTMENT, false, null, $groupId, false, null, $date->format('m/d/Y'), null, null, null, null);

                $report = $this->container->get('templating')->render('@api_report/resident/sixty-days-roster.csv.twig', array(
                    'data' => $data
                ));

                $path = '/tmp/60DaysRosterCsv-' . lcfirst(GroupType::getTypes()[GroupType::TYPE_APARTMENT]) . '-' . $date->format('m-d-Y') . '-' . uniqid('', false) . '.csv';
                file_put_contents($path, $report);

                $subject = $subjectText . ' - ' . $groupName . ' - ' . $date->format('F') . ' ' . $date->format('Y');

                $spaceName = '';
                if ($apartment->getSpace() !== null) {
                    $spaceName = $apartment->getSpace()->getName();
                }

                $status = $this->mailer->sendReportNotification($emails, $subject, $message, $path, $spaceName);

                $this->saveEmailLog($status, $subject, $spaceName, $emails);
            }
        }
        if (!empty($regions)) {
            /** @var Region $region */
            foreach ($regions as $region) {
                $groupId = $region->getId();
                $groupName = $region->getName();
                $data = $this->residentReportService->getSixtyDaysReport(GroupType::TYPE_REGION, false, null, $groupId, false, null, $date->format('m/d/Y'), null, null, null, null);

                $report = $this->container->get('templating')->render('@api_report/resident/sixty-days-roster.csv.twig', array(
                    'data' => $data
                ));

                $path = '/tmp/60DaysRosterCsv-' . lcfirst(GroupType::getTypes()[GroupType::TYPE_REGION]) . '-' . $date->format('m-d-Y') . '-' . uniqid('', false) . '.csv';
                file_put_contents($path, $report);

                $subject = $subjectText . ' - ' . $groupName . ' - ' . $date->format('F') . ' ' . $date->format('Y');

                $spaceName = '';
                if ($region->getSpace() !== null) {
                    $spaceName = $region->getSpace()->getName();
                }

                $status = $this->mailer->sendReportNotification($emails, $subject, $message, $path, $spaceName);

                $this->saveEmailLog($status, $subject, $spaceName, $emails);
            }
        }
    }

    /**
     * @param array $emails
     * @param $isEmail
     * @param $isSms
     */
    public function sendTodayLeadActivityNotifications(array $emails, $isEmail, $isSms): void
    {
        /** @var ActivityRepository $activityRepo */
        $activityRepo = $this->em->getRepository(Activity::class);
        $activities = $activityRepo->getActivitiesForCrontabNotification($this->grantService->getCurrentSpace());

        /** @var Activity $activity */
        foreach ($activities as $activity) {
            $allEmails = [];

            $subject = 'Activity Reminder ' . $activity->getTitle();
            if ($activity->getType() !== null && $activity->getType()->isDueDate() && $activity->getDueDate() <= new \DateTime('now')) {
                $subject = 'Past Due | Activity Reminder ' . $activity->getTitle();
            }

            if ($activity->getAssignTo()) {
                //for email
                if ($activity->getAssignTo()->isEnabled()) {
                    $allEmails = array_merge($emails, [$activity->getAssignTo()->getEmail()]);
                } else {
                    $allEmails = $emails;
                }
                $allEmails = array_unique($allEmails);

                //for sms
                if ($isSms && $activity->getLead() !== null) {
                    $message = 'Activity: ' . $activity->getType()->getTitle() . ', ';
                    if ($activity->getType()->isDueDate()) {
                        $dueDate = $activity->getDueDate() ? $activity->getDueDate()->format('m/d/Y') : '';
                        $message .= 'Date: ' . $dueDate . ', ';
                    }
                    $message .= 'Lead: ' . $activity->getLead()->getFirstName() . ' ' . $activity->getLead()->getLastName() . ', ';
                    $message .= 'Person: ' . $activity->getLead()->getResponsiblePersonFirstName() . ' ' . $activity->getLead()->getResponsiblePersonLastName() . ', ';
                    if ($activity->getLead()->getResponsiblePersonPhone()) {
                        $message .= 'Contact: ' . $activity->getLead()->getResponsiblePersonPhone() . ', ';
                    }
                    if ($activity->getStatus()) {
                        $message .= 'Status: ' . $activity->getStatus()->getTitle() . ', ';
                    }
                    $message .= 'Notes: ' . $activity->getNotes();

                    // Aws allows only 140 chars length text message.
                    $this->amazonSnsService->sendMessageToUser($activity->getAssignTo(), $message);
                }
            }

            // Sending email notification per activity
            if ($isEmail && !empty($allEmails)) {
                $spaceName = '';
                if ($activity->getStatus() !== null && $activity->getStatus()->getSpace() !== null) {
                    $spaceName = $activity->getStatus()->getSpace()->getName();
                }

                $body = $this->container->get('templating')->render('@api_notification/activity.email.html.twig', array(
                    'activity' => $activity,
                    'ownerTitle' => ActivityOwnerType::getTypes()[$activity->getOwnerType()],
                    'subject' => $subject
                ));

                $status = $this->mailer->sendNotification($allEmails, $subject, $body, $spaceName);

                $this->saveEmailLog($status, $subject, $spaceName, $allEmails);
            }
        }
    }

    /**
     * @param array $emails
     */
    public function sendTodayLeadChangeLogNotifications(array $emails): void
    {
        /** @var ChangeLogRepository $logRepo */
        $logRepo = $this->em->getRepository(ChangeLog::class);
        $changeLogs = $logRepo->getChangeLogsForCrontabNotification($this->grantService->getCurrentSpace());

        $ownerEmails = [];
        $logs = [];
        /** @var ChangeLog $changeLog */
        foreach ($changeLogs as $changeLog) {

            if ($changeLog->getOwner() !== null && $changeLog->getOwner()->isEnabled()) {
                $ownerEmails[] = $changeLog->getOwner()->getEmail();
            }

            $activityType = '';

            switch ($changeLog->getType()) {
                case ChangeLogType::TYPE_NEW_LEAD:
                    $title = $changeLog->getContent()['lead_name'];
                    $action = ChangeLogType::getTypes()[ChangeLogType::TYPE_NEW_LEAD];
                    $date = $changeLog->getCreatedAt() !== null ? $changeLog->getCreatedAt()->format('m/d/Y H:i') : '';

                    break;
                case ChangeLogType::TYPE_LEAD_UPDATED:
                    $title = $changeLog->getContent()['lead_name'];
                    $action = ChangeLogType::getTypes()[ChangeLogType::TYPE_LEAD_UPDATED];
                    $date = $changeLog->getCreatedAt() !== null ? $changeLog->getCreatedAt()->format('m/d/Y H:i') : '';

                    break;
                case ChangeLogType::TYPE_NEW_TASK:
                    $activityType = ActivityOwnerType::getTypes()[$changeLog->getContent()['type']];

                    $title = ActivityOwnerType::getTypes()[$changeLog->getContent()['type']] . ': ' . $changeLog->getContent()['owner'];
                    $action = ChangeLogType::getTypes()[ChangeLogType::TYPE_NEW_TASK];
                    $date = $changeLog->getCreatedAt() !== null ? $changeLog->getCreatedAt()->format('m/d/Y H:i') : '';

                    break;
                case ChangeLogType::TYPE_TASK_UPDATED:
                    $activityType = ActivityOwnerType::getTypes()[$changeLog->getContent()['type']];

                    $title = ActivityOwnerType::getTypes()[$changeLog->getContent()['type']] . ': ' . $changeLog->getContent()['owner'];
                    $action = ChangeLogType::getTypes()[ChangeLogType::TYPE_TASK_UPDATED];
                    $date = $changeLog->getCreatedAt() !== null ? $changeLog->getCreatedAt()->format('m/d/Y H:i') : '';

                    break;
                default:
                    throw new IncorrectChangeLogException();
            }

            $logs[] = [
                'type' => $changeLog->getType(),
                'content' => $changeLog->getContent(),
                'activity_type' => $activityType,
                'title' => strip_tags($title),
                'action' => $action,
                'date' => $date,
            ];
        }

        $emails = array_merge($emails, $ownerEmails);
        $emails = array_unique($emails);

        if (!empty($emails)) {
            $spaceName = '';
            if ($changeLog->getSpace() !== null) {
                $spaceName = $changeLog->getSpace()->getName();
            }

            $date = new \DateTime('now');
            $date->modify('-24 hours');
            $subject = 'Daily Leads System User Activity Summary - ' . $date->format('m/d/Y');

            $body = $this->container->get('templating')->render('@api_notification/change-log.email.html.twig', array(
                'logs' => $logs,
                'subject' => $subject
            ));

            $status = $this->mailer->sendNotification($emails, $subject, $body, $spaceName);

            $this->saveEmailLog($status, $subject, $spaceName, $emails);
        }
    }

    /**
     * @param array $emails
     * @param $isEmail
     */
    public function sendOnTheDayBeforeFacilityActivityNotifications(array $emails, $isEmail): void
    {
        /** @var FacilityEventRepository $activityRepo */
        $activityRepo = $this->em->getRepository(FacilityEvent::class);
        $activities = $activityRepo->getActivitiesForCrontabNotification($this->grantService->getCurrentSpace());

        /** @var FacilityEvent $activity */
        foreach ($activities as $activity) {
            $allEmails = [];

            $subject = 'Facility Activity Reminder - ' . $activity->getTitle();

            if (!empty($activity->getUsers())) {
                $activityUserEmails = [];
                /** @var User $activityUser */
                foreach ($activity->getUsers() as $activityUser) {
                    if ($activityUser->isEnabled()) {
                        $activityUserEmails[] = $activityUser->getEmail();
                    }
                }

                //for email
                $allEmails = array_merge($emails, $activityUserEmails);
                $allEmails = array_unique($allEmails);
            }

            // Sending email notification per activity
            if ($isEmail && !empty($allEmails)) {
                $spaceName = '';
                if ($activity->getFacility() !== null && $activity->getFacility()->getSpace() !== null) {
                    $spaceName = $activity->getFacility()->getSpace()->getName();
                }

                $body = $this->container->get('templating')->render('@api_notification/facility.activity.email.html.twig', array(
                    'activity' => $activity,
                    'subject' => $subject
                ));

                $status = $this->mailer->sendNotification($allEmails, $subject, $body, $spaceName);

                $this->saveEmailLog($status, $subject, $spaceName, $allEmails);
            }
        }
    }

    /**
     * @param array $emails
     * @param $isEmail
     */
    public function sendOnTheDayBeforeCorporateActivityNotifications(array $emails, $isEmail): void
    {
        /** @var CorporateEventRepository $activityRepo */
        $activityRepo = $this->em->getRepository(CorporateEvent::class);
        $activities = $activityRepo->getActivitiesForCrontabNotification($this->grantService->getCurrentSpace());

        /** @var CorporateEvent $activity */
        foreach ($activities as $activity) {
            $allEmails = [];

            $isDone = $activity->getDefinition() !== null ? $activity->getDefinition()->isDone() : false;
            $subjectText = $isDone ? 'Corporate Activity Task Reminder - ' : 'Corporate Activity Reminder - ';
            $subject = $subjectText . $activity->getTitle();

            if (!empty($activity->getCorporateEventUsers())) {
                $activityUserEmails = [];
                /** @var CorporateEventUser $activityUser */
                foreach ($activity->getCorporateEventUsers() as $activityUser) {
                    if (!$activityUser->isDone() && $activityUser->getUser() !== null && $activityUser->getUser()->isEnabled()) {
                        $activityUserEmails[] = $activityUser->getUser()->getEmail();
                    }
                }

                //for email
                $allEmails = array_merge($emails, $activityUserEmails);
                $allEmails = array_unique($allEmails);
            }

            // Sending email notification per activity
            if ($isEmail && !empty($allEmails)) {
                $spaceName = '';
                if ($activity->getDefinition() !== null && $activity->getDefinition()->getSpace() !== null) {
                    $spaceName = $activity->getDefinition()->getSpace()->getName();
                }

                $body = $this->container->get('templating')->render('@api_notification/corporate.activity.email.html.twig', array(
                    'activity' => $activity,
                    'subject' => $subject
                ));

                $status = $this->mailer->sendNotification($allEmails, $subject, $body, $spaceName);

                $this->saveEmailLog($status, $subject, $spaceName, $allEmails);
            }
        }
    }

    /**
     * @param array $emails
     * @param $isEmail
     */
    public function sendTodayResidentRentIncreaseNotifications(array $emails, $isEmail): void
    {
        $currentSpace = $this->grantService->getCurrentSpace();

        /** @var ResidentRentIncreaseRepository $increaseRepo */
        $increaseRepo = $this->em->getRepository(ResidentRentIncrease::class);
        $increases = $increaseRepo->getRentIncreasesForCronJobNotification($currentSpace);

        $residentIds = array_map(static function (ResidentRentIncrease $item) {
            return $item->getResident() ? $item->getResident()->getId() : 0;
        }, $increases);

        if (!empty($residentIds)) {
            /** @var ResidentRentRepository $rentRepo */
            $rentRepo = $this->em->getRepository(ResidentRent::class);

            $rents = $rentRepo->getRentsByResidentIds($currentSpace, null, $residentIds);

            if (!empty($rents)) {
                /** @var ResidentResponsiblePersonRepository $responsiblePersonRepo */
                $responsiblePersonRepo = $this->em->getRepository(ResidentResponsiblePerson::class);

                $responsiblePersons = $responsiblePersonRepo->getResponsiblePersonByResidentIds($currentSpace, null, $residentIds);

                $responsiblePersonEmails = [];
                if (!empty($responsiblePersons)) {
                    /** @var ResidentResponsiblePerson $responsiblePerson */
                    foreach ($responsiblePersons as $responsiblePerson) {
                        if ($responsiblePerson->getResident() !== null && $responsiblePerson->getResponsiblePerson() !== null) {
                            $responsiblePersonEmails[$responsiblePerson->getResident()->getId()][] = $responsiblePerson->getResponsiblePerson()->getEmail();
                        }
                    }
                }

                /** @var ResidentRentIncrease $increase */
                foreach ($increases as $increase) {
                    $resident = $increase->getResident();

                    /** @var ResidentRent $rent */
                    foreach ($rents as $rent) {
                        if ($resident !== null && $rent->getResident() !== null && $resident->getId() === $rent->getResident()->getId()) {
                            $subject = 'Room Rent Increase - ' . $resident->getFirstName() . ' ' . $resident->getLastName();

                            $rpEmails = [];
                            if (array_key_exists($resident->getId(), $responsiblePersonEmails)) {
                                $rpEmails = $responsiblePersonEmails[$resident->getId()];
                            }

                            //for email
                            $allEmails = array_merge($emails, $rpEmails);
                            $allEmails = array_unique($allEmails);

                            // Sending email notification per increase
                            if ($isEmail && !empty($allEmails)) {
                                $spaceName = '';
                                if ($resident->getSpace() !== null) {
                                    $spaceName = $resident->getSpace()->getName();
                                }

                                $body = $this->container->get('templating')->render('@api_notification/resident.rent.increase.email.html.twig', array(
                                    'increase' => $increase,
                                    'reason' => $increase->getReason() !== null ? $increase->getReason()->getTitle() : '',
                                    'subject' => $subject
                                ));

                                $status = $this->mailer->sendNotification($allEmails, $subject, $body, $spaceName);

                                $this->saveEmailLog($status, $subject, $spaceName, $allEmails);
                            }
                        }
                    }
                }
            }
        }
    }

    /**
     * @param $status
     * @param $subject
     * @param $spaceName
     * @param $emails
     * @throws \Exception
     */
    private function saveEmailLog($status, $subject, $spaceName, $emails): void
    {
        try {
            $this->em->getConnection()->beginTransaction();

            $emailLog = new EmailLog();
            $emailLog->setSuccess($status);
            $emailLog->setSubject($subject);
            $emailLog->setSpace($spaceName);
            $emailLog->setEmails($emails);

            $this->em->persist($emailLog);
            $this->em->flush();

            $this->em->getConnection()->commit();
        } catch (\Exception $e) {
            $this->em->getConnection()->rollBack();

            throw $e;
        }
    }
}