<?php

/**
 * Class statusDayLoadEditorAction
 */
class statusDayEditorAction extends statusViewAction
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
        $day = new statusDay(new DateTime($date));

        $userId = waRequest::get('user_id', 0, waRequest::TYPE_INT);
        $user = stts()->getUser();
        if ($userId) {
            $user = stts()->getEntityRepository(statusUser::class)->findById($userId);
            if (!$user instanceof statusUser) {
                throw new kmwaNotFoundException(_w('User with not found'));
            }
        }

        $dayDto = statusDayEditor::createDayDto($day, $user);
        $userDto = reset($dayDto->users);
        $userDayInfoDto = reset($dayDto->userDayInfos);

        $this->view->assign(
            [
                'day' => $dayDto,
                'statuses' => statusTodayStatusFactory::getAllForUser($user),
                'user' => $userDto,
                'userDayInfo' => $userDayInfoDto,
            ]
        );
    }
}
