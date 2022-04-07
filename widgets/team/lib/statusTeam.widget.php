<?php

class statusTeamWidget extends statusAbstractWidget
{
    /**
     * @var array
     */
    private static $groups;

    public function defaultAction()
    {
        $user = $this->getStatusUser();

        $selectedGroup = self::getSettingsGroup($this->id);
        $getWeekDataFilterRequestDto = new statusGetWeekDataFilterRequestDto(
            $selectedGroup['id'] == 0 ? -1312 : null,
            null,
            $selectedGroup['id']
        );

        $userDate = statusTimeHelper::createDatetimeForUser('Y-m-d H:i:s', new DateTime(), $user)
            ->setTime(0, 0, 0);

        $loadWeeksCount = $userDate->format('N') == 1 ? 2 : 1;

        $weeks = statusWeekFactory::createLastNWeeks($loadWeeksCount, true, 0);
        $weeksDto = statusWeekFactory::getWeeksDto($weeks, $getWeekDataFilterRequestDto);

        $weekFilter = new statusWeekFilter();
        foreach ($weeksDto as $weekDto) {
            $weekFilter->filterNonExistingUserWithNoActivity($weekDto);
        }

        $currentWeek = array_shift($weeksDto);
        $today = $currentWeek->days[0];
        if (isset($currentWeek->days[1])) {
            $yesterday = $currentWeek->days[1];
        } else {
            $prevWeek = array_shift($weeksDto);
            $yesterday = $prevWeek->days[0];
        }

        $this->display([
            'widget_id' => $this->id,
            'link' => sprintf(
                '%s#/team/%d',
//                wa()->getRootUrl(true),
                wa()->getUrl(true),
                $selectedGroup['id'] == 0 ? -1312 : $selectedGroup['id']
            ),
            'todayDto' => $today,
            'yesterdayDto' => $yesterday,
            'stts' => stts(),
            'group' => $selectedGroup,
            'ui' => wa()->whichUI('webasyst'),
        ]);

        $this->incognitoLogout();
    }

    public static function getGroupFilterControl($name, $params)
    {
        $templatePath = sprintf('%s/templates/GroupsControl.html', dirname(__FILE__, 2));

        $widgetId = waRequest::get('id', true, waRequest::TYPE_INT);

        return self::renderTemplate($templatePath, [
            'name' => $name,
            'params' => $params,
            'widget' => wa()->getWidget($widgetId)->getInfo(),
            'groups' => self::getGroups(),
            'current_group' => $params['value'] ?: self::getSettingsGroup($widgetId)['id'],
        ]);
    }

    private static function getGroups(): array
    {
        if (self::$groups === null) {
            self::$groups = [
                0 => ['name' => _w('All users'), 'id' => 0],
            ];

            $waGroups = (new statusTeamGroupGetterService())->getGroups();

            foreach ($waGroups as $visibleGroup) {
                self::$groups[(int) $visibleGroup['id']] = [
                    'name' => $visibleGroup['name'],
                    'id' => (int) $visibleGroup['id'],
                ];
            }
        }

        return self::$groups;
    }

    private static function getSettingsGroup($widgetId): array
    {
        $groups = self::getGroups();
        $settings = self::getSettingModel()->get($widgetId);
        $savedGroup = $settings['group'] ?? 0;

        return $groups[$savedGroup];
    }
}