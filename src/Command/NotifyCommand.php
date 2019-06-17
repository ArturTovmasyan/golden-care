<?php

namespace App\Command;

use Ahc\Cron\Expression;
use App\Api\V1\Common\Service\Exception\IncorrectChangeLogException;
use App\Entity\ChangeLog;
use App\Entity\Lead\Activity;
use App\Entity\Notification;
use App\Entity\User;
use App\Entity\UserPhone;
use App\Model\ChangeLogType;
use App\Model\Lead\ActivityOwnerType;
use App\Model\NotificationTypeCategoryType;
use App\Model\Phone;
use App\Repository\ChangeLogRepository;
use App\Repository\Lead\ActivityRepository;
use App\Repository\NotificationRepository;
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

    /** @var Mailer */
    private $mailer;

    /** @var ContainerInterface */
    private $container;

    public function __construct (
        EntityManagerInterface $em,
        Mailer $mailer,
        ContainerInterface $container
    )
    {
        $this->em = $em;
        $this->mailer = $mailer;
        $this->container = $container;

        parent::__construct();
    }


    protected function configure()
    {
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        if (!$this->lock()) {
            $output->writeln('The command is already running in another process.');

            return 0;
        }

        /** @var NotificationRepository $repo */
        $repo = $this->em->getRepository(Notification::class);

        $notifications = $repo->findBy(['enabled' => true]);

        /** @var Notification $notification */
        foreach ($notifications as $notification) {

            if ($notification->getType() !== null && $notification->getSchedule() !== null && Expression::isDue($notification->getSchedule(), new \DateTime())) {

                $emails = $notification->getEmails();
                $userEmails = [];
                if (!empty($notification->getUsers())) {
                    /** @var User $user */
                    foreach ($notification->getUsers() as $user) {
                        $userEmails[] = $user->getEmail();
                    }
                }
                $emails = array_merge($emails, $userEmails);
                $emails = array_unique($emails);

                switch ($notification->getType()->getCategory()) {
                    case NotificationTypeCategoryType::TYPE_SIXTY_DAYS_REPORT:
                        //TODO
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
                }
            }
        }

        $this->release();
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
        $activities = $activityRepo->getActivitiesForCrontabNotification();

        /** @var Activity $activity */
        foreach ($activities as $activity) {
            // Sending notification per activity.
            $allEmails = [];
            $phones = [];

            $subject = 'Activity Reminder ' . $activity->getTitle();

            if ($activity->getAssignTo()) {
                $assignToEmails[] = $activity->getAssignTo()->getEmail();
                $allEmails = array_merge($emails, $assignToEmails);
                $allEmails = array_unique($allEmails);

                /** @var UserPhone $phone */
                foreach ($activity->getAssignTo()->getPhones() as $phone) {
                    if ($phone->getCompatibility() === Phone::US_COMPATIBLE && $phone->isSmsEnabled()) {
                        $phones[] = $phone->getNumber();
                    }
                }
            }

            if ($activity->getType() !== null && $activity->getType()->isDueDate() && $activity->getDueDate() <= new \DateTime('now')) {
                $subject = 'Past Due | Activity Reminder ' . $activity->getTitle();
            }

            if ($isEmail && !empty($allEmails)) {
                $body = $this->container->get('templating')->render('@api_notification/activity.email.html.twig', array(
                    'activity' => $activity,
                    'ownerTitle' => ActivityOwnerType::getTypes()[$activity->getOwnerType()],
                    'subject' => $subject
                ));

                $this->mailer->sendNotification($allEmails, $subject, $body);
            }
            //TODO
//            if ($isSms && empty($phones)) {
//                $body = $this->container->get('templating')->render('@api_notification/activity.email.html.twig', array(
//                    'activity' => $activity,
//                    'ownerTitle' => ActivityOwnerType::getTypes()[$activity->getOwnerType()],
//                    'subject' => $subject
//                ));
//
//                $this->mailer->sendNotification($allEmails, $subject, $body);
//            }
        }
    }

    /**
     * @param array $emails
     */
    public function sendTodayLeadChangeLogNotifications(array $emails): void
    {
        /** @var ChangeLogRepository $logRepo */
        $logRepo = $this->em->getRepository(ChangeLog::class);
        $changeLogs = $logRepo->getChangeLogsForCrontabNotification();

        $ownerEmails = [];
        $logs = [];
        /** @var ChangeLog $changeLog */
        foreach ($changeLogs as $changeLog) {

            if ($changeLog->getOwner() !== null) {
                $ownerEmails[] = $changeLog->getOwner()->getEmail();
            }

            $activityType = '';

            switch ($changeLog->getType()) {
                case ChangeLogType::TYPE_NEW_LEAD:
                    $title = $changeLog->getContent()['lead_name'];
                    $action = ChangeLogType::getTypes()[ChangeLogType::TYPE_NEW_LEAD];
                    $date = $changeLog->getCreatedAt()->format('m/d/Y H:i');

                    break;
                case ChangeLogType::TYPE_LEAD_UPDATED:
                    $title = $changeLog->getContent()['lead_name'];
                    $action = ChangeLogType::getTypes()[ChangeLogType::TYPE_LEAD_UPDATED];
                    $date = $changeLog->getCreatedAt()->format('m/d/Y H:i');

                    break;
                case ChangeLogType::TYPE_NEW_TASK:
                    $activityType = ActivityOwnerType::getTypes()[$changeLog->getContent()['type']];

                    $title = ActivityOwnerType::getTypes()[$changeLog->getContent()['type']].': '.$changeLog->getContent()['owner'];
                    $action = ChangeLogType::getTypes()[ChangeLogType::TYPE_NEW_TASK];
                    $date = $changeLog->getCreatedAt()->format('m/d/Y H:i');

                    break;
                case ChangeLogType::TYPE_TASK_UPDATED:
                    $activityType = ActivityOwnerType::getTypes()[$changeLog->getContent()['type']];

                    $title = ActivityOwnerType::getTypes()[$changeLog->getContent()['type']].': '.$changeLog->getContent()['owner'];
                    $action = ChangeLogType::getTypes()[ChangeLogType::TYPE_TASK_UPDATED];
                    $date = $changeLog->getCreatedAt()->format('m/d/Y H:i');

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
            $date = new \DateTime('now');
            $date->modify('-24 hours');
            $subject = 'Leads System User Activity for ' . $date->format('m/d/Y');

            $body = $this->container->get('templating')->render('@api_notification/change-log.email.html.twig', array(
                'logs' => $logs,
                'subject' => $subject
            ));

            $this->mailer->sendNotification($emails, $subject, $body);
        }
    }
}