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
        return (new statusProject())
            ->setCreatedBy(stts()->getUser()->getId())
            ->setCreateDatetime(new DateTime());
    }
}
