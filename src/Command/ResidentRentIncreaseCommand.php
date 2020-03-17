<?php

namespace App\Command;

use App\Api\V1\Common\Service\BaseService;
use App\Api\V1\Common\Service\Exception\ValidationException;
use App\Api\V1\Common\Service\GrantService;
use App\Entity\ResidentRent;
use App\Entity\ResidentRentIncrease;
use App\Repository\ResidentRentIncreaseRepository;
use App\Repository\ResidentRentRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Command\LockableTrait;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class ResidentRentIncreaseCommand extends Command
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
            ->setName('app:resident-rent-increase')
            ->setDescription('Resident Rent Increase.')
            ->setHelp('This command allows you increase resident rent...');
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

            /** @var ResidentRentIncreaseRepository $increaseRepo */
            $increaseRepo = $this->em->getRepository(ResidentRentIncrease::class);

            $increases = $increaseRepo->getRentIncreasesForCronJob($currentSpace, null);

            if (!empty($increases)) {
                $residentIds = array_map(static function (ResidentRentIncrease $item) {
                    return $item->getResident() ? $item->getResident()->getId() : 0;
                }, $increases);

                /** @var ResidentRentRepository $rentRepo */
                $rentRepo = $this->em->getRepository(ResidentRent::class);

                $rents = $rentRepo->getRentsByResidentIds($currentSpace, null, $residentIds);

                if (!empty($rents)) {
                    /** @var ResidentRentIncrease $increase */
                    foreach ($increases as $increase) {
                        /** @var ResidentRent $rent */
                        foreach ($rents as $rent) {
                            if ($increase->getResident() !== null && $rent->getResident() !== null && $rent->getStart() <= $increase->getEffectiveDate() && $increase->getResident()->getId() === $rent->getResident()->getId()) {

                                $residentRent = new ResidentRent();
                                $residentRent->setResident($increase->getResident());
                                $residentRent->setStart($increase->getEffectiveDate());
                                $residentRent->setEnd(null);
                                $residentRent->setAmount($increase->getAmount());
                                $residentRent->setNotes($increase->getReason() !== null ? $increase->getReason()->getTitle() : '');
                                $residentRent->setReason($increase->getReason());
                                $residentRent->setSource([]);
                                $residentRent->setCreatedBy($increase->getCreatedBy());
                                $residentRent->setUpdatedBy($increase->getCreatedBy());

                                $this->em->persist($residentRent);

                                $rent->setEnd($increase->getEffectiveDate());

                                $this->em->persist($rent);

                                $increase->setDone(true);

                                $this->em->persist($increase);
                            }
                        }
                    }
                }
            }

            $this->em->flush();

            $this->em->getConnection()->commit();

            $output->writeln('Resident Rent(s) successfully increased');
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