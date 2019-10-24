<?php

/**
 * Trait kmwaWaViewTrait
 */
trait kmwaWaViewTrait
{
    /**
     * @param int $id
     *
     * @return int|mixed
     * @throws kmwaNotFoundException
     */
    protected function getId($id = 0)
    {
        $id = $id
            ?: waRequest::request('id', 0, waRequest::TYPE_INT)
                ?: waRequest::request('item_id', 0, waRequest::TYPE_INT)
                    ?: waRequest::request('list_id', 0, waRequest::TYPE_INT)
                        ?: waRequest::request('pocket_id', 0, waRequest::TYPE_INT);

        if (!$id) {
            throw new kmwaNotFoundException();
        }

        return $id;
    }
}
