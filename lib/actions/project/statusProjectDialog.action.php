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
        /** @var statusProject $project */
        $project = null;
        if ($id) {
            $project = stts()->getEntityRepository(statusProject::class)->findById($this->getId());
        }

        if (!$project instanceof statusProject) {
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
