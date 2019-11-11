<?php

namespace App\Command;

use App\Api\V1\Common\Service\BaseService;
use App\Api\V1\Common\Service\Exception\FacilityNotFoundException;
use App\Api\V1\Common\Service\Exception\ValidationException;
use App\Api\V1\Common\Service\GrantService;
use App\Entity\Facility;
use App\Entity\FacilityDashboard;
use App\Entity\ResidentAdmission;
use App\Model\AdmissionType;
use App\Repository\FacilityRepository;
use App\Repository\ResidentAdmissionRepository;
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
            ->setName('app:facility_dashboard')
            ->setDescription('Facility Dashboard data.')
            ->setHelp('This command allows you add facility dashboard data...');
    }

    /**
     * @param InputInterface $input
     * @param OutputInterface $output
     * @return int
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        if (!$this->lock()) {
            $output->writeln('The command is already running in another process.');

            return 0;
        }

        try {
            $this->em->getConnection()->beginTransaction();

            $currentSpace = $this->grantService->getCurrentSpace();

            /** @var FacilityRepository $facilityRepo */
            $facilityRepo = $this->em->getRepository(Facility::class);

            $facilities = $facilityRepo->list($currentSpace, null);

            if (empty($facilities)) {
                throw new FacilityNotFoundException();
            }

            $yesterday = new \DateTime('-1 day');
            $dateFormatted = $yesterday->format('Y-m-d 00:00:00');
            $date = new \DateTime($dateFormatted);

            /** @var ResidentAdmissionRepository $admissionRepo */
            $admissionRepo = $this->em->getRepository(ResidentAdmission::class);
            $activeAdmissions = $admissionRepo->getActiveResidentsForFacilityDashboard($currentSpace, null, null);
            $admissions = $admissionRepo->getResidentsForFacilityDashboard($currentSpace, null, null, $date, new \DateTime($date->format('Y-m-d 23:59:59')));

            /** @var Facility $facility */
            foreach ($facilities as $facility) {
                $entity = new FacilityDashboard();
                $entity->setFacility($facility);
                $entity->setDate($date);
                $entity->setTotalCapacity($facility->getCapacity());
                $entity->setBreakEven($facility->getCapacityRed());
                $entity->setCapacityYellow($facility->getCapacityYellow());

                $occupancy = 0;
                if (!empty($activeAdmissions)) {
                    foreach ($activeAdmissions as $activeAdmission) {
                        $i = 0;
                        if ($activeAdmission['typeId'] === $facility->getId()) {
                            $i ++;

                            $occupancy += $i;
                        }
                    }
                }
                $entity->setOccupancy($occupancy);

                $moveInsRespite = 0;
                $moveInsLongTerm = 0;
                if (!empty($admissions)) {
                    foreach ($admissions as $admission) {
                        $j = 0;
                        $k = 0;
                        if ($admission['typeId'] === $facility->getId()) {
                            if ($admission['admissionType'] === AdmissionType::SHORT_ADMIT) {
                                $j ++;

                                $moveInsRespite += $j;
                            }

                            if ($admission['admissionType'] === AdmissionType::LONG_ADMIT) {
                                $k ++;

                                $moveInsLongTerm += $k;
                            }
                        }
                    }
                }
                $entity->setMoveInsRespite($moveInsRespite);
                $entity->setMoveInsLongTerm($moveInsLongTerm);

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
    }
}