<?php
namespace App\Api\V1\Admin\Service;

use App\Api\V1\Common\Service\BaseService;
use App\Api\V1\Common\Service\Exception\UserNotFoundException;
use App\Api\V1\Common\Service\IGridService;
use App\Entity\User;
use Doctrine\ORM\QueryBuilder;
use Doctrine\ORM\Tools\Pagination\Paginator;

/**
 * Class UserService
 * @package App\Api\V1\Service
 */
class UserService extends BaseService implements IGridService
{
    /**
     * @param QueryBuilder $queryBuilder
     * @return Paginator
     */
    public function getListing(QueryBuilder $queryBuilder, $params)
    {
        return $this->em->getRepository(User::class)->search($queryBuilder);
    }

    /**
     * @param $id
     * @return null|object|User
     */
    public function getById($id)
    {
        $user = $this->em->getRepository(User::class)->find($id);

        if (is_null($user)) {
            throw new UserNotFoundException();
        }

        return $user;
    }

    /**
     * @param array $params
     * @throws \Doctrine\DBAL\ConnectionException
     */
    public function add(array $params): void
    {
        try {
            $this->em->getConnection()->beginTransaction();

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
            $user->setConfirmPassword($params['re_password']);
            $user->setPlainPassword($params['password']);
            $user->setPassword($encoded);

            $this->validate($user, null, ["api_admin_user_add"]);

            $this->em->persist($user);
            $this->em->flush();
            $this->em->getConnection()->commit();
        } catch (\Exception $e) {
            $this->em->getConnection()->rollBack();

            throw $e;
        }
    }

    /**
     * @param $id
     * @param array $params
     * @throws \Doctrine\DBAL\ConnectionException
     */
    public function edit($id, array $params): void
    {
        try {
            $this->em->getConnection()->beginTransaction();

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
            $this->em->getConnection()->commit();
        } catch (\Exception $e) {
            $this->em->getConnection()->rollBack();

            throw $e;
        }
    }

    /**
     * @param $id
     * @throws \Exception
     */
    public function resetPassword($id)
    {

        try {
            $this->em->getConnection()->beginTransaction();

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

            $this->validate($user, null, ["api_admin_user_reset_password"]);

            $this->em->flush();

            // send email for new credentials
            $this->mailer->notifyCredentials($user);

            $this->em->getConnection()->commit();
        } catch (\Exception $e) {
            $this->em->getConnection()->rollBack();

            throw $e;
        }
    }
}