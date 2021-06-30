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
        if ($this->contactId == statusGetWeekDataFilterRequestDto::ALL_USERS_ID) {
            $loadWeekCount = 2;
        }

        $weeks = statusWeekFactory::createLastNWeeks(
            $loadWeekCount,
            false,
            $loadWeekCount * $offset
        );

        $weeks = statusWeekFactory::getWeeksDto($weeks, $this->getWeekDataFilterRequestDto);

        $this->view->assign(
            [
                'weeks' => $weeks,
                'isMe' => (int) $this->isMe,
                'isProject' => (int) $this->isProject,
            ]
        );
    }
}
