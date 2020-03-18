<?php

namespace App\Command;

use App\Util\Mailer;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Command\LockableTrait;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

class ZapierCommand extends Command
{
    use LockableTrait;

    protected $grantService;

    /** @var Mailer */
    private $mailer;

    /** @var ContainerInterface */
    private $container;

    public function __construct(
        Mailer $mailer,
        ContainerInterface $container
    )
    {
        $this->mailer = $mailer;
        $this->container = $container;

        parent::__construct();
    }

    /**
     *
     */
    protected function configure(): void
    {
        $this
            ->setName('app:zapier')
            ->setDescription('Zapier email.')
            ->setHelp('This command allows you to send zapier email...')
            ->addArgument('template', InputArgument::REQUIRED, 'Template number.');
    }

    protected function execute(InputInterface $input, OutputInterface $output): ?int
    {
        if (!$this->lock()) {
            $output->writeln('The command is already running in another process.');

            return 1;
        }

        $render = '@api_zapier/template' . $input->getArgument('template') . '.html.twig';

        $body = $this->container->get('templating')->render($render, [

        ]);

        $subject = 'This is a test zapier email';

        $this->mailer->sendZapier($subject, $body);

        $this->release();

        return 0;
    }
}