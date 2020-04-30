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
                throw new kmwaNotFoundException('No checkin with id ' . $data['id']);
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
            $user->setThisWeekTotalDuration(
                $totalDuration[$this->getUser()->getId()]['time'] + $checkin->getTotalDuration()
            );
            stts()->getEntityPersister()->save($user);

            $this->response = new statusDayCheckinDto($checkin);
        }

        $percents = 0;
        /** @var statusCheckinProjectsModel $chprModel */
        $chprModel = stts()->getModel('statusCheckinProjects');
        /** @var statusProjectModel $projectModel */
        $projectModel = stts()->getModel(statusProject::class);

        if ($projects) {
            $projectsToInsert = [];
            $chprModel->deleteByField(['checkin_id' => $checkin->getId()]);
            foreach ($projects as $projectId => $project) {
                if (!stts()->getRightConfig()->hasAccessToProject($projectId, $user)) {
                    continue;
                }

                if (!empty($project['on'])) {
                    if (($percents + $project['duration']) > 100) {
                        $project['duration'] = 100 - $percents;
                    }
                    $percents += $project['duration'];

                    $duration = ceil($project['duration'] * ($checkin->getTotalDuration() / 100));
                    $projectsToInsert[] = [
                        'checkin_id' => $checkin->getId(),
                        'project_id' => $projectId,
                        'duration' => $duration,
                    ];
                    $projectModel->updateById($projectId, ['last_checkin_datetime' => date('Y-m-d H:i:s')]);
                }
            }

            if ($projectsToInsert) {
                $chprModel->multipleInsert($projectsToInsert);
            }
        }
    }
}
