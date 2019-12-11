<?php

/**
 * Class statusProjectDeleteController
 */
class statusProjectDeleteController extends statusJsonController
{
    /**
     * @throws kmwaNotFoundException
     * @throws waException
     */
    public function execute()
    {
        $data = waRequest::post('project', [], waRequest::TYPE_ARRAY);

        $project = null;
        /** @var statusProject $project */
        if (!empty($data['id'])) {
            /** @var statusProjectRepository $repository */
            $repository = stts()->getEntityRepository(statusProject::class);
            $project = $repository->findById($data['id']);
        }

        if (!$project instanceof statusProject) {
            throw new kmwaNotFoundException(_w('No project found'));
        }

        if (!stts()->getRightConfig()->hasAccessToProject($project)) {
            throw new kmwaNotFoundException(_w('No project access'));
        }

        stts()->getHydrator()->hydrate($project, $data);
        if (!stts()->getEntityPersister()->delete($project)) {
            $this->setError('Delete project error');
        }
    }
}
