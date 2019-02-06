<?php

namespace App\Command;

use App\Api\V1\Common\Service\GrantService;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Yaml\Yaml;

class TestGrantCommand extends ContainerAwareCommand
{
    protected function configure()
    {
        $this
            ->setName('app:test:grant')
            ->setDescription('Test command.')
            ->setHelp('Test command.');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $grant_service = new GrantService($this->getContainer());

        echo json_encode($grant_service->getGrantsOfRoles([1, 2, 3]), JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT);
    }

}
