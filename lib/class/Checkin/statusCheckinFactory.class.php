<?php

/**
 * Class statusCheckinFactory
 */
class statusCheckinFactory extends statusBaseFactory
{
    /**
     * @return statusCheckin
     * @throws Exception
     */
    public function createNew()
    {
        return parent::createNew()
            ->setStartTime(statusTimeHelper::MINUTES_10AM)
            ->setEndTime(statusTimeHelper::MINUTES_18PM)
            ->setContactId(stts()->getUser()->getContactId())
            ->setCreateDatetime(new DateTime());
    }
}
