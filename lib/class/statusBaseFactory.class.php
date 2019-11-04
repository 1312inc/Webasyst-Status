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
     * @return statusAbstractEntity
     */
    public function createNew()
    {
        $entity = $this->getEntity();

        return new $entity();
    }

    /**
     * @param array $data
     *
     * @return statusAbstractEntity|object
     */
    public function createNewWithData(array $data)
    {
        $entity = $this->createNew();

        return stts()->getHydrator()->hydrate($entity, $data);
    }
}
