<?php

/**
 * Class statusBackendSidebarAction
 */
class statusBackendSidebarAction extends statusViewAction
{
    /**
     * @param null|array $params
     *
     * @return mixed
     * @throws waException
     */
    public function runAction($params = null)
    {
        $this->view->assign(
            [
                'users'    => stts()->getEntityRepository(statusUser::class)->findAllExceptMe(),
                'projects' => stts()->getEntityRepository(statusProject::class)->findAll(),
            ]
        );

        /**
         * UI in main sidebar
         *
         * @event backend_sidebar
         *
         * @param kmwaEventInterface $event Event object
         *
         * @return string HTML output
         */
        $event = new statusEvent(statusEventStorage::WA_BACKEND_SIDEBAR);
        $eventResult = stts()->waDispatchEvent($event);

        $this->view->assign(
            [
                'backend_sidebar' => $eventResult,
                'timeByUserStat'  => (new statusStat())->timeByWeek(new DateTime()),
                'isAdmin'         => $this->getUser()->isAdmin('status'),
            ]
        );
    }
}
