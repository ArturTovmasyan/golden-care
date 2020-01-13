<?php

namespace App\Command;

use App\Entity\User;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;

class CreateUserCommand extends Command
{
    /**
     * @var UserPasswordEncoderInterface
     */
    private $encoder;

    /**
     * @var EntityManagerInterface
     */
    private $em;

    /**
     * CreateUserCommand constructor.
     * @param UserPasswordEncoderInterface $encoder
     * @param EntityManagerInterface $em
     */
    public function __construct(UserPasswordEncoderInterface $encoder, EntityManagerInterface $em)
    {
        parent::__construct();
        $this->encoder = $encoder;
        $this->em = $em;
    }

    /**
     *
     */
    protected function configure(): void
    {
        $this
            ->setName('app:create-user')
            ->setDescription('Creates a new user.')
            ->setHelp('This command allows you to create a user...')
            ->addArgument('username', InputArgument::REQUIRED, 'The username of the user.')
            ->addArgument('password', InputArgument::REQUIRED, 'The password of the user.')
            ->addArgument('email', InputArgument::REQUIRED, 'The email of the user.');
    }

    /**
     * @param InputInterface $input
     * @param OutputInterface $output
     * @return int|null|void
     * @throws \Doctrine\ORM\ORMException
     * @throws \Doctrine\ORM\OptimisticLockException
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $user = new User();
        $user->setUsername($input->getArgument('username'));
        $user->setEmail($input->getArgument('email'));
        $user->setFirstName('');
        $user->setLastName('');
        $user->setLastActivityAt(new \DateTime());
        $user->setEnabled(true);
        $user->setCompleted(true);

        $encoded = $this->encoder->encodePassword($user, $input->getArgument('password'));
        $user->setPassword($encoded);

        /** @var EntityManager $em */
        $this->em->persist($user);
        $this->em->flush();

        $output->writeln('User successfully created');
    }
}
