<?php

/**
 * Class statusProjectFactory
 */
class statusProjectFactory extends statusBaseFactory
{
    /**
     * @return statusProject
     * @throws Exception
     */
    public function createNew()
    {
        return parent::createNew()
            ->setCreatedBy(stts()->getUser()->getId())
            ->setCreateDatetime(date('Y-m-d H:i:s'));
    }
}
