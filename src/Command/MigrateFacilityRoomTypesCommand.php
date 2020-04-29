<?php

namespace App\Command;

use App\Api\V1\Common\Service\Exception\ValidationException;
use App\Entity\FacilityRoom;
use App\Entity\FacilityRoomTypes;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Command\LockableTrait;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class MigrateFacilityRoomTypesCommand extends Command
{
    use LockableTrait;

    /**
     * @var EntityManagerInterface
     */
    private $em;

    /**
     * InviteCustomerCommand constructor.
     * @param EntityManagerInterface $em
     */
    public function __construct(EntityManagerInterface $em)
    {
        parent::__construct();
        $this->em = $em;
    }

    /**
     *
     */
    protected function configure(): void
    {
        $this
            ->setName('app:migrate-room-types')
            ->setDescription('Migrate Facility Room Types.')
            ->setHelp('This command allows you migrate facility room types...');
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

//        try {
//            $this->em->getConnection()->beginTransaction();
//
//            $rooms = $this->em->getRepository(FacilityRoom::class)->findAll();
//
//            if (!empty($rooms)) {
//                /** @var FacilityRoom $room */
//                foreach ($rooms as $room) {
//                    $entity = new FacilityRoomTypes();
//                    $entity->setRoom($room);
//                    $entity->setType($room->getType());
//                    $entity->setCreatedAt($room->getUpdatedAt());
//                    $entity->setUpdatedAt($room->getUpdatedAt());
//                    $entity->setCreatedBy($room->getUpdatedBy());
//                    $entity->setUpdatedBy($room->getUpdatedBy());
//
//                    $this->em->persist($entity);
//                }
//            }
//
//            $this->em->flush();
//
//            $this->em->getConnection()->commit();
//
//            $output->writeln('Successfully migrated');
//        } catch (\Exception $e) {
//            $this->em->getConnection()->rollBack();
//
//            if ($e instanceof ValidationException) {
//                $output->writeln($e->getErrors());
//            } else {
//                $output->writeln($e->getMessage());
//            }
//        }

        $this->release();

        return 0;
    }
}