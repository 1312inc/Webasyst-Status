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
            ->setContactId(stts()->getUser()->getContactId())
            ->setCreateDatetime(new DateTime());
    }
}
