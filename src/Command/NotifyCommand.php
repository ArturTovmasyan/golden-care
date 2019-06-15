<?php

namespace App\Command;

use Ahc\Cron\Expression;
use App\Api\V1\Common\Service\Exception\IncorrectChangeLogException;
use App\Entity\ChangeLog;
use App\Entity\Notification;
use App\Entity\User;
use App\Model\ChangeLogType;
use App\Model\Lead\ActivityOwnerType;
use App\Model\NotificationTypeCategoryType;
use App\Repository\ChangeLogRepository;
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
                        //TODO
                        break;
                    case NotificationTypeCategoryType::TYPE_LEAD_CHANGE_LOG:
                        $this->sendTodayChangeLogNotifications($emails);
                        break;
                }
            }
        }

        $this->release();
    }

    /**
     * @param array $emails
     */
    public function sendTodayChangeLogNotifications(array $emails): void
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

            switch ($changeLog->getType()) {
                case ChangeLogType::TYPE_NEW_LEAD:
                    $content = '<b>' . ChangeLogType::getTypes()[ChangeLogType::TYPE_NEW_LEAD] . '.</b><br> User <b>' . $changeLog->getContent()['user_name'] .
                        '</b> added new lead <b>&quot;' . $changeLog->getContent()['lead_name'] . '&quot;</b>.<br>' .
                        'Name : <b>' . $changeLog->getContent()['lead_name'] . '</b><br>' .
                        'Owner : <b>' . $changeLog->getContent()['owner'] . '</b><br>' .
                        'Primary Facility : <b>' . $changeLog->getContent()['primary_facility'] . '</b>';

                    $title = $changeLog->getContent()['lead_name'];
                    $action = ChangeLogType::getTypes()[ChangeLogType::TYPE_NEW_LEAD];
                    $date = $changeLog->getCreatedAt()->format('m/d/Y H:i');

                    break;
                case ChangeLogType::TYPE_LEAD_UPDATED:
                    $content = '<b>Lead State Changed.</b><br> User <b>' . $changeLog->getContent()['user_name'] .
                        '</b> modified state in  <b>&quot;' . $changeLog->getContent()['lead_name'] . '&quot;</b> from <b>' .
                        $changeLog->getContent()['old_state'] . '</b> to <b>' . $changeLog->getContent()['new_state'] . '</b>.<br>' .
                        'Name : <b>' . $changeLog->getContent()['lead_name'] . '</b><br>' .
                        'Owner : <b>' . $changeLog->getContent()['owner'] . '</b><br>' .
                        'Primary Facility : <b>' . $changeLog->getContent()['primary_facility'] . '</b>';

                    $title = $changeLog->getContent()['lead_name'];
                    $action = ChangeLogType::getTypes()[ChangeLogType::TYPE_LEAD_UPDATED];
                    $date = $changeLog->getCreatedAt()->format('m/d/Y H:i');

                    break;
                case ChangeLogType::TYPE_NEW_TASK:
                    $content = '<b>New Activity Task.</b><br> User <b>' . $changeLog->getContent()['user_name'] .
                        '</b> added new task <b>&quot;' . $changeLog->getContent()['name'] . '&quot;</b> activity.<br>' .
                        'Name : <b>' . $changeLog->getContent()['name'] . '</b><br>' .
                        'Owner : <b>' . $changeLog->getContent()['assign_to'] . '</b><br>' .
                        'Due Date : <b>' . $changeLog->getContent()['due_date'] .  '</b><br>' .
                        ActivityOwnerType::getTypes()[$changeLog->getContent()['type']] . ' : <b>' . $changeLog->getContent()['owner'] . '</b>';

                    $title = ActivityOwnerType::getTypes()[$changeLog->getContent()['type']].': '.$changeLog->getContent()['owner'];
                    $action = ChangeLogType::getTypes()[ChangeLogType::TYPE_NEW_TASK];
                    $date = $changeLog->getCreatedAt()->format('m/d/Y H:i');

                    break;
                case ChangeLogType::TYPE_TASK_UPDATED:
                    $content = '<b>Modified Activity Task Status.</b><br> User <b>' . $changeLog->getContent()['user_name'] .
                        '</b> changed status in <b>&quot;' . $changeLog->getContent()['name'] . '&quot;</b> from <b>' .
                        $changeLog->getContent()['old_status'] . '</b> to <b>' . $changeLog->getContent()['new_status'] . '</b>.<br>' .
                        'Name : <b>' . $changeLog->getContent()['name'] . '</b><br>' .
                        'Owner : <b>' . $changeLog->getContent()['assign_to'] . '</b><br>' .
                        'Due Date : <b>' . $changeLog->getContent()['due_date'] .  '</b><br>' .
                        ActivityOwnerType::getTypes()[$changeLog->getContent()['type']] . ' : <b>' . $changeLog->getContent()['owner'] . '</b>';

                    $title = ActivityOwnerType::getTypes()[$changeLog->getContent()['type']].': '.$changeLog->getContent()['owner'];
                    $action = ChangeLogType::getTypes()[ChangeLogType::TYPE_TASK_UPDATED];
                    $date = $changeLog->getCreatedAt()->format('m/d/Y H:i');

                    break;
                default:
                    throw new IncorrectChangeLogException();
            }

            $logs[] = [
                'content' => $content,
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