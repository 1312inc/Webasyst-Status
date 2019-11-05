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

        $this->view->assign(['weeks' => statusWeekFactory::getWeeksDto($offset)]);
    }
}
