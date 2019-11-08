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
        $projectId = waRequest::get('project_id', 0, waRequest::TYPE_INT);

        $weeks = statusWeekFactory::getWeeksDto(
            $this->user,
            statusWeekFactory::DEFAULT_WEEKS_LOAD,
            false,
            $offset
        );

        $this->view->assign(['weeks' => $weeks]);
    }
}
