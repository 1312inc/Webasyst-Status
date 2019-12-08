<?php

/**
 * Class statusCheckinEventListener
 */
class statusCheckinEventListener
{
    /**
     * @param statusEvent $event
     *
     * @throws waException
     */
    public function beforeDelete(statusEvent $event)
    {
        $checkin = $event->getObject();
        if (!$checkin instanceof statusCheckin) {
            return;
        }

        stts()->getModel(statusCheckinProjects::class)->deleteByField('checkin_id', $checkin->getId());
    }
}
