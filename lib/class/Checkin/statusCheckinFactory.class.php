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
            ->setContactId(stts()->getContextUser()->getContactId())
            ->setDate(date('Y-m-d'))
//            ->setDate(waDateTime::date('Y-m-d', time(), wa()->getUser()->getTimezone()))
            ->setCreateDatetime(date('Y-m-d H:i:s'));
    }
}
