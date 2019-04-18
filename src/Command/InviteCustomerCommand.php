<?php
namespace App\Command;

use App\Annotation\ValidationSerializedName;
use App\Api\V1\Common\Service\Exception\SpaceNotFoundException;
use App\Api\V1\Common\Service\Exception\UserAlreadyInvitedException;
use App\Api\V1\Common\Service\Exception\UserNotFoundException;
use App\Api\V1\Common\Service\Exception\ValidationException;
use App\Entity\Role;
use App\Entity\Space;
use App\Entity\User;
use App\Entity\UserInvite;
use App\Entity\UserLog;
use App\Model\Log;
use App\Util\Mailer;
use Doctrine\Common\Annotations\Reader;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;

class InviteCustomerCommand extends Command
{
    /**
     * @var EntityManagerInterface
     */
    private $em;

    /**
     * @var Mailer
     */
    protected $mailer;

    /**
     * @var ValidatorInterface
     */
    protected $validator;

    /**
     * @var Reader
     */
    protected $reader;

    /**
     * InviteCustomerCommand constructor.
     * @param EntityManagerInterface $em
     * @param Mailer $mailer
     * @param ValidatorInterface $validator
     * @param Reader $reader
     */
    public function __construct(EntityManagerInterface $em, Mailer $mailer, ValidatorInterface $validator, Reader $reader)
    {
        parent::__construct();
        $this->em        = $em;
        $this->mailer    = $mailer;
        $this->validator = $validator;
        $this->reader    = $reader;
    }

    /**
     *
     */
    protected function configure()
    {
        $this
            ->setName('app:invite-customer')
            ->setDescription('Invite a new customer.')
            ->setHelp('This command allows you to invite a customer...')
            ->addArgument('domain', InputArgument::REQUIRED, 'The domain of the customer.')
            ->addArgument('space_id', InputArgument::REQUIRED, 'The space id of the customer.')
            ->addArgument('user_id', InputArgument::REQUIRED, 'The user id who invited the customer.')
            ->addArgument('email', InputArgument::REQUIRED, 'The email of the customer.')
            ->addArgument('roles', InputArgument::IS_ARRAY, 'The roles of the customer.')
        ;
    }

    /**
     * @param InputInterface $input
     * @param OutputInterface $output
     * @return int|null|void
     * @throws \Exception
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        try {
            $this->em->getConnection()->beginTransaction();

            /**
             * @var UserInvite $userInvite|null
             * @var Space $space|null
             * @var User $user|null
             */
            $userInvite = $this->em->getRepository(UserInvite::class)->findOneBy(['email' => $input->getArgument('email')]);
            $space = $this->em->getRepository(Space::class)->find($input->getArgument('space_id'));
            $user = $this->em->getRepository(User::class)->find($input->getArgument('user_id'));

            if ($userInvite !== null) {
                throw new UserAlreadyInvitedException();
            }

            if ($user === null) {
                throw new SpaceNotFoundException();
            }

            if ($space === null) {
                throw new UserNotFoundException();
            }

            $userInvite = new UserInvite();
            $userInvite->setEmail($input->getArgument('email'));
            $userInvite->setToken();
            $userInvite->setOwner(true);
            $userInvite->setSpace($space);
            $userInvite->setUser($user);

            if(\count($input->getArgument('roles')) > 0) {
                $userInvite->getRoleObjects()->clear();

                foreach ($input->getArgument('roles') as $roleId) {
                    /** @var Role $role */
                    $role = $this->em->getRepository(Role::class)->find($roleId);
                    if($role) {
                        $userInvite->getRoleObjects()->add($role);
                    }
                }
            }

            // validate user
            $this->validate($userInvite, null, ['api_admin_user_invite']);

            $this->em->persist($userInvite);

            $this->mailer->inviteCustomer($input->getArgument('email'), $input->getArgument('domain'), $userInvite->getToken(), $user->getFullName());

            // create log
            $log = new UserLog();
            $log->setCreatedAt(new \DateTime());
            $log->setUser(null);
            $log->setSpace($space);
            $log->setType(UserLog::LOG_TYPE_INVITATION);
            $log->setLevel(Log::LOG_LEVEL_LOW);
            $log->setMessage(sprintf('Customer %s invited to join space ', $input->getArgument('email')));
            $this->em->persist($log);

            $this->em->flush();

            $this->em->getConnection()->commit();

            $output->writeln('Customer successfully invited');
        } catch (\Exception $e) {
            $this->em->getConnection()->rollBack();

            if ($e instanceof ValidationException) {
                $output->writeln($e->getErrors());
            } else {
                $output->writeln($e->getMessage());
            }
        }
    }

    /**
     * @param $entity
     * @param null $constraints
     * @param null $groups
     * @return bool
     * @throws \ReflectionException
     */
    protected function validate($entity, $constraints = null, $groups = null)
    {
        $validationErrors = $this->validator->validate($entity, $constraints, $groups);
        $errors           = [];

        if ($validationErrors->count() > 0) {
            foreach ($validationErrors as $error) {
                $propertyPath = ValidationSerializedName::convert(
                    $this->reader,
                    $this->em->getClassMetadata(\get_class($entity))->getName(),
                    $groups[0],
                    $error->getPropertyPath()
                );

                $errors[$propertyPath] = $error->getMessage();
            }

            throw new ValidationException($errors);
        }

        return true;
    }
}
