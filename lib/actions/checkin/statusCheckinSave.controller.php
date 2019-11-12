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
            $this->response = new statusDayCheckinDto($checkin);
        }

        /** @var statusCheckinProjectsModel $chprModel */
        $chprModel = stts()->getModel('statusCheckinProjects');
        foreach ($projects as $projectId => $project) {
            if ($project['project_check_id'] && $project['on'] == 0) {
                $chprModel->deleteById($project['project_check_id']);
            } elseif ($project['project_check_id'] && $project['on'] == 1) {
                $chprModel->updateById(
                    $project['project_check_id'],
                    [
                        'duration' => $project['duration'] * 60,
                    ]
                );
            } elseif ($project['project_check_id'] == 0 && $project['on'] == 1) {
                $chprModel->insert(
                    [
                        'checkin_id' => $checkin->getId(),
                        'project_id' => $projectId,
                        'duration'   => $project['duration'] * 60,
                    ]
                );
            }
        }
    }
}
