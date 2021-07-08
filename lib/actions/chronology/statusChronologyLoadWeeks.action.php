<?php

/**
 * Class statusChronologyLoadWeeksAction
 */
class statusChronologyLoadWeeksAction extends statusChronologyAction
{
    /**
     * @param null|array $params
     *
     * @return mixed
     * @throws Exception
     */
    public function runAction($params = null)
    {
        $offset = waRequest::get('offset', 0, waRequest::TYPE_INT);

        $loadWeekCount = statusWeekFactory::DEFAULT_WEEKS_LOAD;
        if ($this->contactId == statusGetWeekDataFilterRequestDto::ALL_USERS_ID || $this->groupId) {
            $loadWeekCount = 1;
        }

        $weeks = statusWeekFactory::createLastNWeeks(
            $loadWeekCount,
            false,
            $loadWeekCount * $offset
        );

        $weeksDto = statusWeekFactory::getWeeksDto($weeks, $this->getWeekDataFilterRequestDto);

        $weekFilter = new statusWeekFilter();
        foreach ($weeksDto as $weekDto) {
            $weekFilter->filterNonExistingUserWithNoActivity($weekDto);
        }

        $this->view->assign(
            [
                'weeks' => $weeksDto,
                'isMe' => (int) $this->isMe,
                'isProject' => (int) $this->isProject,
            ]
        );
    }
}
