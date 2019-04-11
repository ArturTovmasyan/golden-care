<?php
namespace App\Api\V1\Admin\Service;

use App\Api\V1\Common\Service\BaseService;
use App\Api\V1\Common\Service\Exception\SpaceNotFoundException;
use App\Api\V1\Common\Service\Exception\UserAlreadyInvitedException;
use App\Api\V1\Common\Service\Exception\UserNotFoundException;
use App\Api\V1\Common\Service\Exception\UserNotYetInvitedException;
use App\Api\V1\Common\Service\IGridService;
use App\Entity\Role;
use App\Entity\Space;
use App\Entity\User;
use App\Entity\UserInvite;
use App\Entity\UserLog;
use App\Model\Log;
use App\Repository\UserInviteRepository;
use Doctrine\ORM\QueryBuilder;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

/**
 * Class UserInviteService
 * @package App\Api\V1\Admin\Service
 */
class UserInviteService extends BaseService implements IGridService
{
    /**
     * @param QueryBuilder $queryBuilder
     * @param $params
     */
    public function gridSelect(QueryBuilder $queryBuilder, $params) : void
    {
        /** @var UserInviteRepository $repo */
        $repo = $this->em->getRepository(UserInvite::class);

        $repo->search($this->grantService->getCurrentSpace(), $this->grantService->getCurrentUserEntityGrants(UserInvite::class), $queryBuilder);
    }

    /**
     * @param $params
     * @return mixed
     */
    public function list($params)
    {
        /** @var UserInviteRepository $repo */
        $repo = $this->em->getRepository(UserInvite::class);

        return $repo->list($this->grantService->getCurrentSpace(), $this->grantService->getCurrentUserEntityGrants(UserInvite::class));
    }

    /**
     * @param $id
     * @return UserInvite|null|object
     */
    public function getById($id)
    {
        /** @var UserInviteRepository $repo */
        $repo = $this->em->getRepository(UserInvite::class);

        return $repo->getOne($this->grantService->getCurrentSpace(), $this->grantService->getCurrentUserEntityGrants(UserInvite::class), $id);
    }

    /**
     * @param $spaceId
     * @param $userId
     * @param $email
     * @param $owner
     * @param $roles
     * @param $baseUrl
     * @return int|null
     * @throws \Exception
     */
    public function add($spaceId, $userId, $email, $owner, $roles, $baseUrl) : ?int
    {
        $insert_id = null;
        try {
            $this->em->getConnection()->beginTransaction();

            /**
             * @var UserInvite $userInvite|null
             * @var Space $space|null
             * @var User $user|null
             */
            $userInvite = $this->em->getRepository(UserInvite::class)->findOneBy(['email' => $email]);
            $space = $this->em->getRepository(Space::class)->find($spaceId);
            $user = $this->em->getRepository(User::class)->find($userId);

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
            $userInvite->setEmail($email);
            $userInvite->setToken();
            $userInvite->setOwner($owner);
            $userInvite->setSpace($space);
            $userInvite->setUser($user);

            if(\count($roles) > 0) {
                $userInvite->getRoleObjects()->clear();

                foreach ($roles as $roleId) {
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

            $this->mailer->inviteUser($email, $baseUrl, $userInvite->getToken(), $user->getFullName());

            // create log
            $log = new UserLog();
            $log->setCreatedAt(new \DateTime());
            $log->setUser(null);
            $log->setSpace($space);
            $log->setType(UserLog::LOG_TYPE_INVITATION);
            $log->setLevel(Log::LOG_LEVEL_LOW);
            $log->setMessage(sprintf('User %s invited to join space ', $email));
            $this->em->persist($log);

            $this->em->flush();
            $this->em->getConnection()->commit();

            $insert_id = $userInvite->getId();
        } catch (\Exception $e) {
            $this->em->getConnection()->rollBack();

            throw $e;
        }

        return $insert_id;
    }

    /**
     * @param $id
     * @throws \Throwable
     */
    public function remove($id): void
    {
        try {
            $this->em->getConnection()->beginTransaction();

            /** @var UserInvite $userInvite */
            $userInvite = $this->em->getRepository(UserInvite::class)->find($id);

            if ($userInvite === null) {
                throw new UserNotYetInvitedException();
            }

            // create log
            $log = new UserLog();
            $log->setCreatedAt(new \DateTime());
            $log->setUser(null);
            $log->setSpace($userInvite->getSpace());
            $log->setType(UserLog::LOG_TYPE_REJECT_INVITATION);
            $log->setLevel(Log::LOG_LEVEL_LOW);
            $log->setMessage(sprintf('User %s reject invitation for space', $userInvite->getEmail()));
            $this->em->persist($log);

            $this->em->remove($userInvite);
            $this->em->flush();
            $this->em->getConnection()->commit();
        } catch (\Throwable $e) {
            $this->em->getConnection()->rollBack();

            throw $e;
        }
    }
}
