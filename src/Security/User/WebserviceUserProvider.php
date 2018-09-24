<?php
namespace App\Security\User;

use App\Repository\UserRepository;
use App\Security\User\WebserviceUser;
use Symfony\Component\Security\Core\User\UserProviderInterface;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Security\Core\Exception\UsernameNotFoundException;
use Symfony\Component\Security\Core\Exception\UnsupportedUserException;

class WebserviceUserProvider implements UserProviderInterface
{
    /**
     * @var UserRepository
     */
    private $userRepository;

    /**
     * @param UserRepository $userRepository
     */
    function __construct(UserRepository $userRepository)
    {
        $this->userRepository = $userRepository;
    }

    /**
     * @param string $username
     * @return \App\Security\User\WebserviceUser|UserInterface
     */
    public function loadUserByUsername($username)
    {
        return $this->fetchUser($username);
    }

    /**
     * @param UserInterface $user
     * @return \App\Security\User\WebserviceUser|UserInterface
     */
    public function refreshUser(UserInterface $user)
    {
        if (!$user instanceof WebserviceUser) {
            throw new UnsupportedUserException(
                sprintf('Instances of "%s" are not supported.', get_class($user))
            );
        }

        $username = $user->getUsername();

        return $this->fetchUser($username);
    }

    /**
     * @param string $class
     * @return bool
     */
    public function supportsClass($class)
    {
        return WebserviceUser::class === $class;
    }

    /**
     * @param $username
     * @return \App\Security\User\WebserviceUser
     */
    private function fetchUser($username)
    {
        $user = $this->userRepository->findOneBy(array('username' => $username));

        if ($user) {
            return new WebserviceUser($user->getUsername(), $user->getPassword(), $user->getSalt(), $user->getRoles());
        }

        throw new UsernameNotFoundException(
            sprintf('Username "%s" does not exist.', $username)
        );
    }
}