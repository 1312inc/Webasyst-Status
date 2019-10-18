<?php

/**
 * Class statusBaseFactory
 */
class statusBaseFactory
{
    /**
     * @var string
     */
    protected $entity;

    /**
     * @return mixed
     */
    public function getEntity()
    {
        return $this->entity;
    }

    /**
     * @param string $entity
     *
     * @return statusBaseFactory
     */
    public function setEntity($entity)
    {
        $this->entity = $entity;

        return $this;
    }

    /**
     * @return mixed
     */
    public function createNew()
    {
        $entity = $this->getEntity();

        return new $entity();
    }
}
