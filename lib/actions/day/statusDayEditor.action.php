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
                throw new kmwaNotFoundException("User with id {$userId} not found");
            }

            if (!$user->getContact()->getRights(statusConfig::APP_ID)) {
                throw new kmwaForbiddenException();
            }
        }

        $dayDto = statusDayEditor::createDayDto($day, $user);

        $this->view->assign(
            [
                'day'    => $dayDto,
                'statuses' => statusTodayStatusFactory::getAllForUser($user),
                'currentStatus' => statusTodayStatusFactory::getForUser($user, $day->getDate()),
            ]
        );
    }
}
