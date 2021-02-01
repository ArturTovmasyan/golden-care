<?php

namespace App\Command;

use App\Api\V1\Admin\Service\ResidentLedgerService;
use App\Api\V1\Common\Service\BaseService;
use App\Api\V1\Common\Service\GrantService;
use App\Entity\EmailLog;
use App\Entity\Facility;
use App\Entity\ResidentAdmission;
use App\Entity\Role;
use App\Entity\User;
use App\Model\GroupType;
use App\Repository\FacilityRepository;
use App\Repository\ResidentAdmissionRepository;
use App\Repository\RoleRepository;
use App\Repository\UserRepository;
use App\Util\Mailer;
use Doctrine\ORM\EntityManagerInterface;
use Psr\Container\ContainerInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Command\LockableTrait;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class InvalidResidentRentCommand extends Command
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
     * @var ResidentLedgerService
     */
    protected $residentLedgerService;

    /** @var Mailer */
    private $mailer;

    /** @var ContainerInterface */
    private $container;

    /**
     * InvalidResidentRentCommand constructor.
     * @param EntityManagerInterface $em
     * @param BaseService $baseService
     * @param GrantService $grantService
     * @param ResidentLedgerService $residentLedgerService
     * @param Mailer $mailer
     * @param ContainerInterface $container
     */
    public function __construct(
        EntityManagerInterface $em,
        BaseService $baseService,
        GrantService $grantService,
        ResidentLedgerService $residentLedgerService,
        Mailer $mailer,
        ContainerInterface $container
    )
    {
        parent::__construct();
        $this->em = $em;
        $this->baseService = $baseService;
        $this->grantService = $grantService;
        $this->residentLedgerService = $residentLedgerService;
        $this->mailer = $mailer;
        $this->container = $container;
    }

    /**
     *
     */
    protected function configure(): void
    {
        $this
            ->setName('app:invalid-resident-rent')
            ->setDescription('Invalid Resident Rent.')
            ->setHelp('This command allows you to send email notification to Admin if has invalid(has not Payment Sources) resident rents ...');
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

        $now = new \DateTime('now');
        $currentDate = new \DateTime('now');
        $currentDateFormatted = $currentDate->format('m/t/Y');
        $lastDate = new \DateTime($currentDateFormatted);

        if ($now->format('d') === $lastDate->format('d')) {
            $currentSpace = $this->grantService->getCurrentSpace();

            /** @var ResidentAdmissionRepository $residentAdmissionRepo */
            $residentAdmissionRepo = $this->em->getRepository(ResidentAdmission::class);

            /** @var FacilityRepository $groupRepo */
            $groupRepo = $this->em->getRepository(Facility::class);

            $groupList = $groupRepo->list($this->grantService->getCurrentSpace(), null);
            if (!empty($groupList)) {
                /** @var RoleRepository $roleRepo */
                $roleRepo = $this->em->getRepository(Role::class);
                /** @var UserRepository $userRepo */
                $userRepo = $this->em->getRepository(User::class);

                /** @var Facility $group */
                foreach ($groupList as $group) {
                    $groupId = $group->getId();
                    $groupName = $group->getName();
                    $roleNames = ['Facility Admin', 'Administrator', 'Facility Admin Assistant', 'Facility Admin w/Finance', 'Corporate Facility Admin'];

                    $roles = $roleRepo->findByNames($roleNames);

                    $emails = [];
                    if (!empty($roles)) {
                        $roleIds = array_map(static function (Role $item) {
                            return $item->getId();
                        }, $roles);

                        $userFacilityIds = $userRepo->getEnabledUserFacilityIdsByRoles($currentSpace, null, $roleIds);

                        if (!empty($userFacilityIds)) {
                            $facilityIds = [$groupId];

                            foreach ($userFacilityIds as $userFacilityId) {
                                if ($userFacilityId['facilityIds'] === null) {
                                    $emails[] = $userFacilityId['email'];
                                } else {
                                    $explodedUserFacilityIds = explode(',', $userFacilityId['facilityIds']);

                                    if (!empty(array_intersect($explodedUserFacilityIds, $facilityIds))) {
                                        $emails[] = $userFacilityId['email'];
                                    }
                                }
                            }
                        }
                    }

                    $groupResidents = $residentAdmissionRepo->getActiveResidents($currentSpace, $this->grantService->getCurrentUserEntityGrants(ResidentAdmission::class), GroupType::TYPE_FACILITY, [$groupId]);

                    $invalidRents = [];
                    if (!empty($groupResidents)) {
                        foreach ($groupResidents as $resident) {
                            $residentId = $resident['id'];
                            $spaceName = $resident['space'];

                            $amountData = $this->residentLedgerService->calculateAmountAndGetPaymentSources($currentSpace, $residentId, $now, null);
                            $amountData['rentData'];

                            if (!empty($amountData['rentData'])) {
                                foreach ($amountData['rentData'] as $rent) {
                                    if (empty($rent['sources'])) {
                                        $invalidRents[] = $rent;
                                    }
                                }
                            }
                        }
                    }

                    if (!empty($emails)) {
                        $subject = 'Invalid Payment Source' . ' - ' . $groupName . ' - ' . $now->format('m/d/Y');

                        $body = $this->container->get('templating')->render('@api_notification/invalid-resident-rent.email.html.twig', array(
                            'data' => $invalidRents,
                            'subject' => $subject,
                            'groupName' => $groupName
                        ));

                        $status = $this->mailer->sendNotification($emails, $subject, $body, $spaceName);

                        $this->saveEmailLog($status, $subject, $spaceName, $emails);
                    }
                }
            }
        }

        $this->release();

        return 0;
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