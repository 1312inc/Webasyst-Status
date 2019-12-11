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
        $teammates = [];
        $users = stts()->getEntityRepository(statusUser::class)->findAllExceptMe();

        if (wa()->appExists('team')) {
            wa('team');

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

            foreach ($users as $id => $user) {
                unset($teammates[$user->getContactId()]);
            }
            unset($teammates[stts()->getUser()->getContactId()]);
        }

        foreach ($users as $id => $user) {
            if (!stts()->getRightConfig()->hasAccessToTeammate($user->getContactId())) {
                unset($users[$id]);
            }
        }

        /** @var statusProjectRepository $projectRepository */
        $projectRepository = stts()->getEntityRepository(statusProject::class);
        $projects = $projectRepository->findAllOrderByLastCheckin();
        foreach ($projects as $id => $project) {
            if (!stts()->getRightConfig()->hasAccessToProject($project)) {
                unset($projects[$id]);
            }
        }

        $stat = new statusStat();

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
                'teammates' => $teammates,
                'users' => $users,
                'projects' => $projects,
                'backend_sidebar' => $eventResult,
                'timeByUserStat' => $stat->usersTimeByWeek(new DateTime()),
                'timeByProjectStat' => $stat->projectsTimeByWeek(new DateTime()),
                'isAdmin' => (int)$this->getUser()->isAdmin('status'),
            ]
        );
    }
}
