<?php

/**
 * Class statusProjectEventListener
 */
class statusProjectEventListener
{
    /**
     * @param statusEvent $event
     *
     * @throws waException
     */
    public function beforeDelete(statusEvent $event)
    {
        $project = $event->getObject();
        if (!$project instanceof statusProject) {
            return;
        }

        stts()->getModel(statusCheckinProjects::class)->deleteByField('project_id', $project->getId());
    }
}
