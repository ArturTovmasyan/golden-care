<?php
namespace App\Api\V1\Admin\Service;

use App\Api\V1\Common\Service\BaseService;
use App\Api\V1\Common\Service\Exception\UserNotFoundException;
use App\Entity\User;

/**
 * Class UserService
 * @package App\Api\V1\Service
 */
class UserService extends BaseService
{
    /**
     * @param array $params
     */
    public function addUser(array $params): void
    {
        // create user
        $user = new User();
        $user->setFirstName($params['first_name']);
        $user->setLastName($params['last_name']);
        $user->setUsername(strtolower($params['username']));
        $user->setEmail($params['email']);
        $user->setLastActivityAt(new \DateTime());
        $user->setEnabled((bool) $params['email']);
        $user->setPhone($params['phone']);
        $user->setCompleted(true);

        // encode password
        $encoded = $this->encoder->encodePassword($user, $params['password']);
        $user->setPassword($encoded);

        $this->validate($user, null, ["api_admin_user_add"]);

        $this->em->persist($user);
        $this->em->flush();
    }

    /**
     * @param int $id
     * @param array $params
     */
    public function editUser($id, array $params): void
    {
        $user = $this->em->getRepository(User::class)->find($id);

        if (is_null($user)) {
            throw new UserNotFoundException();
        }

        $user->setFirstName($params['first_name']);
        $user->setLastName($params['last_name']);
        $user->setEnabled((bool) $params['enabled']);
        $user->setPhone($params['phone']);

        $this->validate($user, null, ["api_admin_user_edit"]);

        $this->em->persist($user);
        $this->em->flush();
    }

    /**
     * @param $id
     * @throws \Exception
     */
    public function resetPassword($id)
    {
        /** @var User $user **/
        $user = $this->em->getRepository(User::class)->find($id);

        if (is_null($user)) {
            return;
        }

        $this->em->getConnection()->beginTransaction();

        $password = $this->generatePassword(8);
        $encoded  = $this->encoder->encodePassword($user, $password);

        $user->setPlainPassword($password);
        $user->setPassword($encoded);
        $this->em->persist($user);
        $this->em->flush();

        // send email for new credentials
        $this->mailer->notifyCredentials($user);
    }
}