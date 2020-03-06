<?php

/**
 * Class statusCheckinSaveController
 */
class statusCheckinSaveController extends statusJsonController
{
    /**
     * @throws kmwaNotFoundException
     * @throws waException
     */
    public function execute()
    {
        $user = stts()->getUser();
        $data = waRequest::post('checkin', [], waRequest::TYPE_ARRAY);
        $projects = waRequest::post('projects', [], waRequest::TYPE_ARRAY);

        /** @var statusCheckin $checkin */
        if (!empty($data['id'])) {
            /** @var statusCheckinRepository $repository */
            $repository = stts()->getEntityRepository(statusCheckin::class);
            $checkin = $repository->findById($data['id']);
            if (!$checkin instanceof statusCheckin) {
                throw new kmwaNotFoundException('No checkin with id '.$data['id']);
            }
        } else {
            /** @var statusBaseFactory $factory */
            $factory = stts()->getEntityFactory(statusCheckin::class);
            $checkin = $factory->createNew();
        }

        if (empty($data['break'])) {
            $data['break_duration'] = 0;
        }

        stts()->getHydrator()->hydrate($checkin, $data);
        if (!stts()->getEntityPersister()->save($checkin)) {
            $this->setError('Save checkin error');
        } else {
            $user->setLastCheckinDatetime(date('Y-m-d H:i:s'));
            $totalDuration = (new statusStat())->usersTimeByWeek(new DateTime());
            $user->setThisWeekTotalDuration($totalDuration[$this->getUser()->getId()]['time'] + $checkin->getTotalDuration());
            stts()->getEntityPersister()->save($user);

            $this->response = new statusDayCheckinDto($checkin);
        }

        $percents = 0;
        /** @var statusCheckinProjectsModel $chprModel */
        $chprModel = stts()->getModel('statusCheckinProjects');
        /** @var statusProjectModel $projectModel */
        $projectModel = stts()->getModel(statusProject::class);
        foreach ($projects as $projectId => $project) {
            if (!stts()->getRightConfig()->hasAccessToProject($projectId, $user)) {
                continue;
            }
            $percents += $project['duration'];
            if ($percents > 100) {
                $project['duration'] = 0;
            }

            if ($project['project_check_id'] && (empty($project['on']) || empty($project['duration']))) {
                $chprModel->deleteByField(['checkin_id' => $checkin->getId(), 'project_id' => $projectId]);
            } elseif ($project['on'] == 1) {
                $duration = ceil($project['duration'] * ($checkin->getTotalDuration() / 100));
                $chprModel->insert(
                    [
                        'checkin_id' => $checkin->getId(),
                        'project_id' => $projectId,
                        'duration'   => $duration,
                    ],
                    waModel::INSERT_ON_DUPLICATE_KEY_UPDATE
                );
                $projectModel->updateById($projectId, ['last_checkin_datetime' => date('Y-m-d H:i:s')]);
            }
        }
    }
}
