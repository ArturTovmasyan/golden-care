<?php

namespace App\Command;

use App\Api\V1\Common\Service\GrantService;
use App\Entity\ResidentPhysician;
use App\Entity\User;
use App\Model\Grant;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class TestGrantCommand extends ContainerAwareCommand
{
    protected function configure(): void
    {
        $this
            ->setName('app:test:grant')
            ->setDescription('Test command.')
            ->setHelp('Test command.');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $grant_service = new GrantService($this->getContainer());

        $users = $this->getContainer()->get('doctrine')->getManager()->getRepository(User::class)->findAll();
        $grant_service->setCurrentUser($users[0]);
//        echo json_encode($grant_service->getGrantsOfRoles([1, 2, 3]), JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT);

//        dump($grant_service->getEntityGrants($users[0], Facility::class));
        dump($grant_service->hasCurrentUserEntityGrant(ResidentPhysician::class, Grant::$LEVEL_DELETE));
    }

}
