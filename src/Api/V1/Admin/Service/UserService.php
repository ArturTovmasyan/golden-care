<?php
namespace App\Api\V1\Admin\Service;

use App\Api\V1\Common\Service\BaseService;
use App\Api\V1\Common\Service\Exception\PhoneSinglePrimaryException;
use App\Api\V1\Common\Service\Exception\UserNotFoundException;
use App\Api\V1\Common\Service\IGridService;
use App\Entity\User;
use App\Entity\UserPhone;
use Doctrine\ORM\QueryBuilder;

/**
 * Class UserService
 * @package App\Api\V1\Service
 */
class UserService extends BaseService implements IGridService
{
    /**
     * @param QueryBuilder $queryBuilder
     * @param $params
     * @return void
     */
    public function gridSelect(QueryBuilder $queryBuilder, $params)
    {
        $this->em->getRepository(User::class)->search($queryBuilder);
    }

    public function list($params)
    {
        return $this->em->getRepository(User::class)->findAll();
    }

    /**
     * @param $id
     * @return null|object|User
     */
    public function getById($id)
    {
        $user = $this->em->getRepository(User::class)->find($id);

        if ($user === null) {
            throw new UserNotFoundException();
        }

        return $user;
    }

    /**
     * @param array $params
     * @throws \Exception
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
            $user->setEnabled((bool) $params['enabled']);
            $user->setLastActivityAt(new \DateTime());

            $user->setCompleted(true);

            $user->setPlainPassword($params['password']);
            $user->setConfirmPassword($params['re_password']);
            $user->setPhones($this->savePhones($user, $params['phones'] ?? []));

            $this->validate($user, null, ['api_admin_user_add']);

            $encoded = $this->encoder->encodePassword($user, $params['password']);
            $user->setPassword($encoded);

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
     * @throws \Exception
     */
    public function edit($id, array $params): void
    {
        try {
            $this->em->getConnection()->beginTransaction();

            /** @var User $user */
            $user = $this->em->getRepository(User::class)->find($id);

            if ($user === null) {
                throw new UserNotFoundException();
            }

            $user->setFirstName($params['first_name']);
            $user->setLastName($params['last_name']);
            $user->setUsername(strtolower($params['username']));
            $user->setEmail($params['email']);
            $user->setEnabled((bool) $params['enabled']);

            if(!empty($params['password'])) {
                $user->setPlainPassword($params['password']);
                $user->setConfirmPassword($params['re_password']);
            }

            $user->setPhones($this->savePhones($user, $params['phones'] ?? []));

            $this->validate($user, null, ['api_admin_user_edit']);

            if(!empty($params['password'])) {
                $encoded = $this->encoder->encodePassword($user, $params['password']);
                $user->setPassword($encoded);
            }

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

            if ($user === null) {
                return;
            }

            $this->em->getConnection()->beginTransaction();

            $password = $this->generatePassword(8);
            $encoded  = $this->encoder->encodePassword($user, $password);

            $user->setPlainPassword($password);
            $user->setPassword($encoded);
            $this->em->persist($user);

            $this->validate($user, null, ['api_admin_user_reset_password']);

            $this->em->flush();

            // send email for new credentials
            $this->mailer->notifyCredentials($user);

            $this->em->getConnection()->commit();
        } catch (\Exception $e) {
            $this->em->getConnection()->rollBack();

            throw $e;
        }
    }



    /**
     * @param User $user
     * @param array $phones
     * @return array
     */
    private function savePhones($user, array $phones = [])
    {
        /**
         * @var UserPhone[] $oldPhones
         */
        $oldPhones = $this->em->getRepository(UserPhone::class)->findBy(['user' => $user]);

        foreach ($oldPhones as $phone) {
            $this->em->remove($phone);
        }

        $hasPrimary = false;

        $userPhones = [];

        foreach($phones as $phone) {
            $userPhone = new UserPhone();
            $userPhone->setUser($user);
            $userPhone->setCompatibility($phone['compatibility'] ?? null);
            $userPhone->setType($phone['type']);
            $userPhone->setNumber($phone['number']);
            $userPhone->setPrimary((bool) $phone['primary'] ?? false);
            $userPhone->setSmsEnabled((bool) $phone['sms_enabled'] ?? false);

            if ($userPhone->isPrimary()) {
                if ($hasPrimary) {
                    throw new PhoneSinglePrimaryException();
                }

                $hasPrimary = true;
            }

            $this->em->persist($userPhone);

            $userPhones[] = $userPhone;
        }

        return $userPhones;
    }
}
