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

        $weeks = statusWeekFactory::createLastNWeeks(
            statusWeekFactory::DEFAULT_WEEKS_LOAD,
            false,
            statusWeekFactory::DEFAULT_WEEKS_LOAD * $offset
        );

        $weeks = statusWeekFactory::getWeeksDto($weeks, $this->user);

        $this->view->assign(['weeks' => $weeks]);
    }
}
