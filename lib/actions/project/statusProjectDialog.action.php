<?php

/**
 * Class statusProjectDialogAction
 */
class statusProjectDialogAction extends statusViewAction
{
    /**
     * @param null $params
     *
     * @return mixed|void
     * @throws waException
     * @throws kmwaNotFoundException
     */
    public function runAction($params = null)
    {
        $id = waRequest::get('id', 0, waRequest::TYPE_INT);
        $project = null;

        /** @var statusProject $project */
        if ($id) {
            $project = stts()->getEntityRepository(statusProject::class)->findById($this->getId());

            if (!stts()->getRightConfig()->hasAccessToProject($project)) {
                throw new kmwaNotFoundException(_w('No project access'));
            }
        }

        if (!$project instanceof statusProject) {
            if (!stts()->getRightConfig()->hasAccessToProject()) {
                throw new kmwaNotFoundException(_w('No projects access'));
            }

            /** @var statusProject $project */
            $project = stts()->getEntityFactory(statusProject::class)->createNew();
        }

        /**
         * UI in project settings dialog
         * @event backend_project_dialog
         *
         * @param statusEvent $event Event object with statusProject object (new or existing)
         * @return string HTML output
         */
        $event = new statusEvent(statusEventStorage::WA_BACKEND_PROJECT_DIALOG, $project);
        $eventResult = stts()->waDispatchEvent($event);

        $this->view->assign(['project' => $project, 'backend_project_dialog' => $eventResult]);
    }
}
