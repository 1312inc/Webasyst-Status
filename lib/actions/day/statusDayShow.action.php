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
        $date = waRequest::get('date', date('Y-m-d'), waRequest::TYPE_STRING_TRIM);
        $contactId = waRequest::get('contact_id', 0, waRequest::TYPE_INT);

        if ($contactId) {
            $user = stts()->getEntityRepository(statusUser::class)->findByContactId($contactId);
            if (!$user instanceof statusUser) {
                throw new kmwaNotFoundException("User with id {$contactId} not found");
            }

            if (!$user->getContact()->getRights(statusConfig::APP_ID)) {
                throw new kmwaForbiddenException();
            }
        } else {
            $user = stts()->getUser();
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
            ]
        );
    }
}
