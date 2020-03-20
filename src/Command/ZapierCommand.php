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

        $template = $input->getArgument('template');

        $render = '@api_zapier/template' . $template . '.html.twig';

        $body = $this->container->get('templating')->render($render, [

        ]);

        switch ($template) {
            case '1':
                $subject = 'New submission from Book a Tour';
                $from = ['imt.tester1@gmail.com' => 'Auburn Oaks'];
                break;
            case '2':
                $subject = 'New submission from Book a Tour';
                $from = ['imt.tester1@gmail.com' => 'Citrus Heights Terrace'];
                break;
            case '3':
                $subject = 'New submission from Contact Form';
                $from = ['imt.tester1@gmail.com' => 'Auburn Oaks'];
                break;
            case '4':
                $subject = 'New submission from Contact Form';
                $from = ['imt.tester1@gmail.com' => 'Citrus Heights Terrace'];
                break;
            case '5':
                $subject = 'New submission from Contact Form';
                $from = ['imt.tester1@gmail.com' => 'Auburn Oaks'];
                break;
        }

        $this->mailer->sendZapier($from, $subject, $body);

        $this->release();

        return 0;
    }
}