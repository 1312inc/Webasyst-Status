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
        $hiddenUsers = [];

        usort($users, function (statusUser $user) {
           return !$user->isExists();
        });

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
            if (!$user->isExists() && !$user->getContact()->exists()) {
                unset($users[$id]);
                continue;
            }

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

        $waGroups = (new waGroupModel())->select('*')
            ->order('sort')
            ->fetchAll('id');

        $userGroupModel = new waUserGroupsModel();
        $allUsers = stts()->getEntityRepository(statusUser::class)->findAll();
        foreach ($waGroups as $i => $waGroup) {
            $groupContactsIds = $userGroupModel->getContactIds($i);

            $groupIsVisible = false;

            if ($groupContactsIds) {
                foreach ($allUsers as $user) {
                    if (!$user->isExists()) {
                        continue;
                    }

                    if (in_array($user->getContactId(), $groupContactsIds)) {
                        $groupIsVisible = true;
                        break;
                    }
                }
            }

            if ($groupIsVisible === false) {
                unset($waGroups[$i]);
            }
        }

        foreach ($users as $i => $user) {
            if (!$user->isExists()) {
                unset($users[$i]);
                $hiddenUsers[] = $user;
            }
        }

        $tinyAd = (new statusTinyAddService())->getAd($this->getUser());

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
                'hiddenUsers' => $hiddenUsers,
                'projects' => $projects,
                'backend_sidebar' => $eventResult,
                'timeByUserStat' => $stat->usersTimeByWeek(new DateTime()),
                'timeByProjectStat' => $stat->projectsTimeByWeek(new DateTime()),
                'isAdmin' => (int)$this->getUser()->isAdmin('status'),
                'groups' => $waGroups,
                'tinyAd' => $tinyAd,
            ]
        );
    }
}
