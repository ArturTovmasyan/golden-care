<?php

namespace App\Command;

use App\Api\V1\Admin\Service\ResidentLedgerService;
use App\Api\V1\Common\Service\BaseService;
use App\Api\V1\Common\Service\Exception\ValidationException;
use App\Api\V1\Common\Service\GrantService;
use App\Entity\Facility;
use App\Entity\Resident;
use App\Entity\ResidentAdmission;
use App\Entity\ResidentLedger;
use App\Model\GroupType;
use App\Repository\ResidentAdmissionRepository;
use App\Repository\ResidentLedgerRepository;
use App\Repository\ResidentRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Command\LockableTrait;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class CreateResidentLedgerCommand extends Command
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

    /**
     * InviteCustomerCommand constructor.
     * @param EntityManagerInterface $em
     * @param BaseService $baseService
     * @param GrantService $grantService
     * @param ResidentLedgerService $residentLedgerService
     */
    public function __construct(EntityManagerInterface $em, BaseService $baseService, GrantService $grantService, ResidentLedgerService $residentLedgerService)
    {
        parent::__construct();
        $this->em = $em;
        $this->baseService = $baseService;
        $this->grantService = $grantService;
        $this->residentLedgerService = $residentLedgerService;
    }

    /**
     *
     */
    protected function configure(): void
    {
        $this
            ->setName('app:create-resident-ledger')
            ->setDescription('Create Resident Ledger.')
            ->setHelp('This command allows you create resident ledger...');
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

            /** @var ResidentAdmissionRepository $residentAdmissionRepo */
            $residentAdmissionRepo = $this->em->getRepository(ResidentAdmission::class);

            $data = [
                [
                    'groupType' => GroupType::TYPE_FACILITY,
                    'entityClass' => Facility::class,
                ]
            ];

            foreach ($data as $strategy) {
                $groupRepo = $this->em->getRepository($strategy['entityClass']);

                $groupList = $groupRepo->list($this->grantService->getCurrentSpace(), null);
                if (!empty($groupList)) {
                    $groupIds = array_map(static function ($item) {
                        return $item->getId();
                    }, $groupList);

                    $groupResidents = $residentAdmissionRepo->getActiveResidents($currentSpace, $this->grantService->getCurrentUserEntityGrants(ResidentAdmission::class), $strategy['groupType'], $groupIds);

                    $residentIds = array_map(static function ($item) {
                        return $item['id'];
                    }, $groupResidents);

                    if (!empty($residentIds)) {
                        /** @var ResidentLedgerRepository $repo */
                        $repo = $this->em->getRepository(ResidentLedger::class);
                        /** @var ResidentRepository $residentRepo */
                        $residentRepo = $this->em->getRepository(Resident::class);

                        foreach ($residentIds as $residentId) {
                            $now = new \DateTime('now');

                            /** @var Resident $resident */
                            $resident = $residentRepo->getOne($currentSpace, null, $residentId);
                            $existingLedger = $repo->getAddedYearAndMonthLedger($currentSpace, null, $residentId, $now);

                            if ($resident !== null && $existingLedger === null) {
                                $amountData = $this->residentLedgerService->calculateAmountAndGetPaymentSources($currentSpace, $residentId, $now, null);

                                if (!empty($amountData['paymentSources'])) {
                                    $residentLedger = new ResidentLedger();
                                    $residentLedger->setResident($resident);

                                    //Calculate Privat Pay Balance Due
                                    $currentMonthPrivatPayBalanceDue = $amountData['privatPayAmount'];
                                    //Calculate Not Privat Pay Balance Due
                                    $currentMonthNotPrivatPayBalanceDue = $amountData['notPrivatPayAmount'];

                                    //////////will be
                                    //Calculate Amount
                                    $amount = $amountData['amount'];

                                    $residentLedger->setAmount(round($amount, 2));

                                    //Calculate Balance Due
                                    $currentMonthBalanceDue = $amount;
                                    //////////remove

                                    //Calculate Previous Month Balance Due
                                    $previousDate = new \DateTime(date('Y-m-d', strtotime($now->format('Y-m-d')." first day of previous month")));
                                    $dateStartFormatted = $previousDate->format('m/01/Y 00:00:00');
                                    $dateEndFormatted = $previousDate->format('m/t/Y 23:59:59');
                                    $dateStart = new \DateTime($dateStartFormatted);
                                    $dateEnd = new \DateTime($dateEndFormatted);

                                    /** @var ResidentLedger $previousLedger */
                                    $previousLedger = $repo->getPreviousLedger($currentSpace, null, $residentId, $dateStart, $dateEnd);

                                    $priorBalanceDue = 0;
                                    $priorPrivatPayBalanceDue = 0;
                                    $priorNotPrivatPayBalanceDue = 0;
                                    if ($previousLedger === null) {
                                        $priorAmountData = $this->residentLedgerService->calculateAmountAndGetPaymentSources($currentSpace, $residentId, $previousDate, null);
                                        //Calculate Privat Pay Balance Due
                                        $priorPrivatPayBalanceDue = $priorAmountData['privatPayAmount'];
                                        //Calculate Not Privat Pay Balance Due
                                        $priorNotPrivatPayBalanceDue = $priorAmountData['notPrivatPayAmount'];

                                        //////////will be
                                        //Calculate Previous Month Amount
                                        $priorAmount = $priorAmountData['amount'];

                                        $priorBalanceDue = $priorAmount;
                                        //////////remove
                                    }

                                    $residentLedger->setPrivatePayBalanceDue(round($currentMonthPrivatPayBalanceDue + $priorPrivatPayBalanceDue, 2));
                                    $residentLedger->setPriorPrivatePayBalanceDue(round($priorPrivatPayBalanceDue, 2));
                                    $residentLedger->setNotPrivatePayBalanceDue(round($currentMonthNotPrivatPayBalanceDue + $priorNotPrivatPayBalanceDue, 2));
                                    $residentLedger->setPriorNotPrivatePayBalanceDue(round($priorNotPrivatPayBalanceDue, 2));

                                    //////////will be
                                    $residentLedger->setBalanceDue(round($currentMonthBalanceDue + $priorBalanceDue, 2));
                                    //////////remove

                                    $residentLedger->setSource($amountData['paymentSources']);
                                    $residentLedger->setPrivatPaySource($amountData['privatPayPaymentSources']);
                                    $residentLedger->setNotPrivatPaySource($amountData['notPrivatPayPaymentSources']);

                                    $residentLedger->setUpdatedBy($residentLedger->getCreatedBy());

                                    $this->em->persist($residentLedger);
                                }
                            }
                        }
                    }
                }
            }

            $this->em->flush();

            $this->em->getConnection()->commit();

            $output->writeln('Resident Ledger(s) successfully created');
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