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
        $userIds = [];
        $teammates = [];
        $users = stts()->getEntityRepository(statusUser::class)->findAllExceptMe();

        if (wa('team')) {
            $teammates = teamUser::getList(
                'users',
                [
                    'order' => 'last_seen',
                    'convert_to_utc' => 'update_datetime',
                    'additional_fields' => [
                        'update_datetime' => 'c.create_datetime',
                    ],
                ]
            );

            foreach ($users as $user) {
                unset($teammates[$user->getContactId()]);
            }
            unset($teammates[stts()->getUser()->getContactId()]);
        }

        $this->view->assign(
            [
                'teammates' => $teammates,
                'users' => $users,
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
                'timeByUserStat' => (new statusStat())->timeByWeek(new DateTime()),
                'isAdmin' => (int)$this->getUser()->isAdmin('status'),
            ]
        );
    }
}
