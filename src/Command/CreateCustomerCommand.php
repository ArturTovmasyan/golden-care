<?php

namespace App\Command;

use App\Annotation\ValidationSerializedName;
use App\Api\V1\Common\Service\Exception\DefaultRoleNotFoundException;
use App\Api\V1\Common\Service\Exception\ValidationException;
use App\Entity\Role;
use App\Entity\Space;
use App\Entity\User;
use App\Entity\UserLog;
use App\Entity\UserPhone;
use App\Model\Log;
use App\Model\Phone;
use App\Repository\RoleRepository;
use App\Util\Mailer;
use Doctrine\Common\Annotations\Reader;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;

class CreateCustomerCommand extends Command
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
     * CreateCustomerCommand constructor.
     * @param UserPasswordEncoderInterface $encoder
     * @param EntityManagerInterface $em
     * @param Mailer $mailer
     * @param ValidatorInterface $validator
     * @param Reader $reader
     */
    public function __construct(UserPasswordEncoderInterface $encoder, EntityManagerInterface $em, Mailer $mailer, ValidatorInterface $validator, Reader $reader)
    {
        parent::__construct();
        $this->encoder = $encoder;
        $this->em = $em;
        $this->mailer = $mailer;
        $this->validator = $validator;
        $this->reader = $reader;
    }

    /**
     *
     */
    protected function configure(): void
    {
        $this
            ->setName('app:create-customer')
            ->setDescription('Create a new customer.')
            ->setHelp('This command allows you to create a customer...')
            ->addArgument('domain', InputArgument::REQUIRED, 'The domain of the customer.')
            ->addArgument('organization', InputArgument::REQUIRED, 'The organization of the customer.')
            ->addArgument('first_name', InputArgument::REQUIRED, 'The first name of the customer.')
            ->addArgument('last_name', InputArgument::REQUIRED, 'The last name of the customer.')
            ->addArgument('email', InputArgument::REQUIRED, 'The email of the customer.')
            ->addArgument('phone', InputArgument::REQUIRED, 'The phone of the customer.');
    }

    /**
     * @param InputInterface $input
     * @param OutputInterface $output
     * @return int|null|void
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        try {
            $this->em->getConnection()->beginTransaction();

            /** @var RoleRepository $roleRepo */
            $roleRepo = $this->em->getRepository(Role::class);

            /** @var Role $defaultRole * */
            $defaultRole = $roleRepo->getDefaultRole();
            if ($defaultRole === null) {
                throw new DefaultRoleNotFoundException();
            }

            // create space
            $space = new Space();
            $space->setName($input->getArgument('organization'));
            $this->validate($space, null, ['api_account_signup']);
            $this->em->persist($space);

            // create user
            $user = new User();
            $user->setFirstName($input->getArgument('first_name'));
            $user->setLastName($input->getArgument('last_name'));
            $user->setUsername(strtolower($input->getArgument('last_name')) . time());
            $user->setEmail($input->getArgument('email'));
            $user->setLastActivityAt(new \DateTime());
            $user->setEnabled(true);
            $user->setCompleted(true);

            // encode password
            $password = 'Seniorcare1!';
            $encoded = $this->encoder->encodePassword($user, $password);
            $user->setPlainPassword($password);
            $user->setConfirmPassword($password);
            $user->setPassword($encoded);
            $user->setActivationHash();
            $user->setOwner(true);
            $user->setLicenseAccepted(false);
            $user->setSpace($space);
            $user->setPhone($input->getArgument('phone'));

            // validate user
            $this->validate($user, null, ['api_account_signup']);

            if ($defaultRole) {
                $user->getRoleObjects()->add($defaultRole);
            }

            $this->em->persist($user);

            if ($input->getArgument('phone')) { // TODO: review
                $userPhone = new UserPhone();
                $userPhone->setUser($user);
                $userPhone->setCompatibility(null);
                $userPhone->setType(Phone::TYPE_OFFICE);
                $userPhone->setNumber($user->getPhone());
                $userPhone->setPrimary(true);
                $userPhone->setSmsEnabled(false);

                $this->em->persist($userPhone);
            }

            // create log
            $log = new UserLog();
            $log->setCreatedAt(new \DateTime());
            $log->setUser($user);
            $log->setType(UserLog::LOG_TYPE_AUTHENTICATION);
            $log->setLevel(Log::LOG_LEVEL_LOW);
            $log->setMessage(sprintf('Customer %s (%s) registered in ', $user->getFullName(), $user->getUsername()));
            $this->em->persist($log);

            $this->em->flush();

            // send mail with complete url
            $this->mailer->createCustomer($user->getEmail(), $input->getArgument('domain'), $user->getFullName(), $password);

            $this->em->getConnection()->commit();

            $output->writeln('Customer successfully created');
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
     */
    protected function validate($entity, $constraints = null, $groups = null): ?bool
    {
        $validationErrors = $this->validator->validate($entity, $constraints, $groups);
        $errors = [];

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
