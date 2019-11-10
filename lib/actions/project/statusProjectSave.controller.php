<?php

/**
 * Class statusProjectSaveController
 */
class statusProjectSaveController extends statusJsonController
{
    /**
     * @throws kmwaNotFoundException
     * @throws waException
     */
    public function execute()
    {
        $data = waRequest::post('project', [], waRequest::TYPE_ARRAY);

        /** @var statusProject $project */
        if (!empty($data['id'])) {
            /** @var statusProjectRepository $repository */
            $repository = stts()->getEntityRepository(statusProject::class);
            $project = $repository->findById($data['id']);
            if (!$project instanceof statusProject) {
                throw new kmwaNotFoundException('No project with id ' . $data['id']);
            }
        } else {
            /** @var statusProjectFactory $factory */
            $factory = stts()->getEntityFactory(statusProject::class);
            $project = $factory->createNew();
        }

        stts()->getHydrator()->hydrate($project, $data);
        if (!stts()->getEntityPersister()->save($project)) {
            $this->setError('Save project error');
        } else {
            $this->response = stts()->getHydrator()->extract($project);
        }
    }
}
