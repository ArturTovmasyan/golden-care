<?php

namespace App\Api\V1\Admin\Service\Report;

use App\Api\V1\Common\Service\BaseService;
use App\Entity\UserLog;
use App\Model\GroupType;
use App\Model\Report\UserLogActivity;
use App\Repository\UserLogRepository;
use Symfony\Component\Routing\Exception\InvalidParameterException;

class UserReportService extends BaseService
{
    /**
     * @param $group
     * @param bool|null $groupAll
     * @param $groupIds
     * @param $groupId
     * @param bool|null $residentAll
     * @param $residentId
     * @param $date
     * @param $dateFrom
     * @param $dateTo
     * @param $assessmentId
     * @param $assessmentFormId
     * @param $discontinued
     * @return UserLogActivity
     */
    public function getUserLoginActivityReport($group, ?bool $groupAll, $groupIds, $groupId, ?bool $residentAll, $residentId, $date, $dateFrom, $dateTo, $assessmentId, $assessmentFormId, $discontinued): UserLogActivity
    {
        $currentSpace = $this->grantService->getCurrentSpace();

        $type = $group;

        if (!\in_array($type, GroupType::getTypeValues(), false)) {
            throw new InvalidParameterException('group');
        }

        /** @var UserLogRepository $repo */
        $repo = $this->em->getRepository(UserLog::class);

        $logs = $repo->getUserLoginActivity($currentSpace);

        $report = new UserLogActivity();
        $report->setData($logs);

        return $report;
    }
}