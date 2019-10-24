<?php

/**
 * Class statusBackendAction
 */
class statusBackendAction extends statusViewAction
{
    /**
     * @param null|array $params
     *
     * @return mixed
     * @throws Exception
     */
    public function runAction($params = null)
    {
        $this->view->assign(
            [
                'currentWeek'  => statusWeekFactory::createCurrentWeek(),
                'weeks'        => statusWeekFactory::createLastNWeeks(),
                'sidebar_html' => (new statusBackendSidebarAction())->display(),
            ]
        );
    }
}
