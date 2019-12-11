<?php

/**
 * Class statusEntityPersist
 */
class statusEntityPersist
{
    /**
     * @param statusAbstractEntity $entity
     * @param array                $fields
     * @param int                  $type
     *
     * @return bool
     * @throws waException
     */
    public function insert(
        statusAbstractEntity $entity,
        $fields = [],
        $type = waModel::INSERT_ON_DUPLICATE_KEY_UPDATE
    ) {
        if (!$entity->beforeSave()) {
            return false;
        }

        $model = stts()->getModel(get_class($entity));
        $data = stts()->getHydrator()->extract($entity, $fields, $model->getMetadata());

        /**
         * Before every entity insert
         *
         * @event entity_insert.before
         *
         * @param kmwaEventInterface $event Event with statusAbstractEntity object
         *
         * @return array Entity data to merge and insert
         */
        $event = new statusEvent(statusEventStorage::ENTITY_INSERT_BEFORE, $entity, ['data' => $data]);
        $eventResult = stts()->waDispatchEvent($event);
        foreach ($eventResult as $plugin => $responseData) {
            if (!empty($responseData) && is_array($responseData)) {
                $data = array_merge($data, $responseData);
            }
        }

        stts()->getEventDispatcher()->dispatch($event);

        unset($data['id']);

        $id = $model->insert($data, $type);

        if ($id) {
            if (method_exists($entity, 'setId')) {
                $entity->setId($id);
            }

            /**
             * After every entity insert
             *
             * @event entity_insert.after
             *
             * @param statusEvent $event Event with statusAbstractEntity object
             *
             * @return void
             */
            $event = new statusEvent(statusEventStorage::ENTITY_INSERT_AFTER, $entity);
            stts()->getEventDispatcher()->dispatch($event);

            return true;
        }

        return false;
    }

    /**
     * @param statusAbstractEntity $entity
     *
     * @return bool
     * @throws waException
     */
    public function delete(statusAbstractEntity $entity)
    {
        if (method_exists($entity, 'getId')) {
            /**
             * Before every entity delete
             *
             * @event entity_delete.before
             *
             * @param kmwaEventInterface $event Event with statusAbstractEntity object
             *
             * @return bool If false - entity delete will be canceled
             */
            $event = new statusEvent(statusEventStorage::ENTITY_DELETE_BEFORE, $entity);
            $eventResult = stts()->waDispatchEvent($event);
            foreach ($eventResult as $plugin => $responseData) {
                if ($responseData === false) {
                    return false;
                }
            }

            stts()->getEventDispatcher()->dispatch($event);

            $model = stts()->getModel(get_class($entity));
            $deleted = $model->deleteById($entity->getId());

            /**
             * After every entity delete
             *
             * @event entity_delete.after
             *
             * @param kmwaEventInterface $event Event with statusAbstractEntity object
             *
             * @return void
             */
            $event = new statusEvent(statusEventStorage::ENTITY_DELETE_AFTER, $entity);
            stts()->waDispatchEvent($event);

            return $deleted;
        }

        throw new waException('No id in entity');
    }

    /**
     * @param statusAbstractEntity $entity
     * @param array                $fields
     *
     * @return bool|waDbResultUpdate|null
     * @throws waException
     */
    public function update(statusAbstractEntity $entity, $fields = [])
    {
        if (method_exists($entity, 'getId')) {
            if (!$entity->beforeSave()) {
                return false;
            }

            $model = stts()->getModel(get_class($entity));
            $data = stts()->getHydrator()->extract(
                $entity,
                $fields,
                $model->getMetadata()
            );

            /**
             * Before every entity update
             *
             * @event entity_update.before
             *
             * @param kmwaEventInterface $event Event with statusAbstractEntity object
             *
             * @return array Entity data to merge and update
             */
            $event = new statusEvent(statusEventStorage::ENTITY_UPDATE_BEFORE, $entity, ['data' => $data]);
            $eventResult = stts()->waDispatchEvent($event);
            foreach ($eventResult as $plugin => $responseData) {
                if (!empty($responseData) && is_array($responseData)) {
                    $data = array_merge($data, $responseData);
                }
            }

            stts()->getEventDispatcher()->dispatch($event);

            unset($data['id']);

            $updated = $model->updateById($entity->getId(), $data);

            if ($updated) {
                /**
                 * After every entity update
                 *
                 * @event entity_update.after
                 *
                 * @param statusEvent $event Event with statusAbstractEntity object
                 *
                 * @return void
                 */
                $event = new statusEvent(statusEventStorage::ENTITY_UPDATE_AFTER, $entity, ['data' => $data]);
                stts()->waDispatchEvent($event);
            }

            return $updated;
        }

        throw new waException('No id in entity');
    }

    /**
     * @param statusAbstractEntity $entity
     * @param array                $fields
     *
     * @return bool|waDbResultUpdate|null
     * @throws waException
     */
    public function save(statusAbstractEntity $entity, $fields = [])
    {
        if (method_exists($entity, 'getId')) {
            if ($entity->getId()) {
                return $this->update($entity, $fields);
            }

            return $this->insert($entity, $fields);
        }

        throw new waException('No id in entity');
    }

}