<?php

final class statusUserWidget extends statusAbstractWidget
{
    /**
     * @var array
     */
    private static $users;

    public function defaultAction(): void
    {
        $user = $this->getStatusUser();

        $days = [];
        $dayDtos = [];
        $selectedUser = self::getSettingsUser($this->id);
        if ($selectedUser['id']) {
            $getWeekDataFilterRequestDto = new statusGetWeekDataFilterRequestDto(
                $selectedUser['id'],
                null,
                null
            );

            $userDate = statusTimeHelper::createDatetimeForUser('Y-m-d H:i:s', new DateTime(), $user)
                ->setTime(0, 0, 0);

            $loadWeeksCount = $userDate->format('N') == 7 ? 1 : 2;

            $weeks = statusWeekFactory::createLastNWeeks($loadWeeksCount, true, 0);
            $weeksDto = statusWeekFactory::getWeeksDto($weeks, $getWeekDataFilterRequestDto);

            $weekFilter = new statusWeekFilter();
            foreach ($weeksDto as $weekDto) {
                $weekFilter->filterNonExistingUserWithNoActivity($weekDto);
            }

            $currentWeek = array_shift($weeksDto);
            $dayDtos = $currentWeek->days;

            if (count($dayDtos) < 7) {
                $currentWeek = array_shift($weeksDto);

                while (count($dayDtos) < 7) {
                    $dayDtos[] = array_shift($currentWeek->days);
                }
            }

            foreach ($dayDtos as $dayDto) {
                $days[] = [
                    'date' => $dayDto->date,
                    'day' => $dayDto,
                ];
            }

            $contact = new waContact($selectedUser['id']);
            $selectedUser['photoUrl'] = $contact->getPhoto();
        }

        $this->display([
            'widget_id' => $this->id,
            'link' => sprintf('%s#/contact/%d', wa()->getUrl(true), $selectedUser['id']),
            'days' => $days,
            'stts' => stts(),
            'user' => $selectedUser,
            'ui' => wa()->whichUI('webasyst'),
        ]);

        $this->incognitoLogout();
    }

    public static function getUserFilterControl($name, $params)
    {
        $templatePath = sprintf('%s/templates/UserControl.html', dirname(__FILE__, 2));

        $widgetId = waRequest::get('id', true, waRequest::TYPE_INT);

        return self::renderTemplate($templatePath, [
            'name' => $name,
            'params' => $params,
            'widget' => wa()->getWidget($widgetId)->getInfo(),
            'users' => self::getUsers(),
            'current_user' => $params['value'] ?: self::getSettingsUser($widgetId)['id'],
        ]);
    }

    private static function getUsers(): array
    {
        if (self::$users === null) {
            self::$users = [];
            $users = stts()->getEntityRepository(statusUser::class)->findAllExceptMe();
            foreach ($users as $id => $user) {
                if (!$user->isExists() && !$user->getContact()->exists()) {
                    unset($users[$id]);
                    continue;
                }

                if (!stts()->getRightConfig()->hasAccessToTeammate($user->getContactId())) {
                    unset($users[$id]);
                }

                self::$users[$user->getContactId()] = [
                    'id' => $user->getContactId(),
                    'name' => $user->getName(),
                ];
            }
        }

        return self::$users;
    }

    private static function getSettingsUser($widgetId): array
    {
        $noUser = ['id' => null, 'name' => ''];

        $users = self::getUsers();
        $settings = self::getSettingModel()->get($widgetId);
        if (!empty($settings['user']) && isset($users[$settings['user']])) {
            return $users[$settings['user']];
        }

        return $noUser;
    }
}