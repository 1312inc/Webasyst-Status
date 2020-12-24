<?php

/**
 * Class statusDayLoadEditorAction
 */
class statusDayShowAction extends statusViewAction
{
    /**
     * @param null|array $params
     *
     * @return mixed
     * @throws Exception
     */
    public function runAction($params = null)
    {
        $date = waRequest::get(
            'date',
            statusTimeHelper::createDatetimeForUser('Y-m-d')->format('Y-m-d'),
            waRequest::TYPE_STRING_TRIM
        );
        $contactId = waRequest::get('contact_id', 0, waRequest::TYPE_INT);

        if ($contactId) {
            $user = stts()->getEntityRepository(statusUser::class)->findByContactId($contactId);
            if (!$user->getId()) {
                throw new kmwaNotFoundException(_w('User not found'));
            }
        } else {
            $user = stts()->getUser();
        }

        if (!stts()->getRightConfig()->hasAccessToTeammate($user)) {
            throw new kmwaForbiddenException(_w('No access to this user'));
        }

        $week = statusWeekFactory::createWeekByDate(new DateTime($date));
        $day = new statusDay(new DateTime($date));
        $week->setDays([$day]);
        $weeksDto = statusWeekFactory::getWeeksDto([$week], $user);
        $weekDto = reset($weeksDto);
        $dayDto = reset($weekDto->days);

        $this->view->assign(
            [
                'day' => $dayDto,
                'statuses' => statusTodayStatusFactory::getAllForUser($user),
                'user' => new statusUserDto($user),
                'isProject' => false,
            ]
        );
    }
}
