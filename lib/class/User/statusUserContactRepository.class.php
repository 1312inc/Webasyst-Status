<?php

final class statusUserContactRepository
{
    /**
     * @var array<waContact>
     */
    private $contacts;

    /**
     * @param int $id
     *
     * @return waContact
     * @throws waException
     */
    public function loadContact($id): waContact
    {
        if (!isset($this->contacts[$id])) {
            $this->contacts[$id] = new waContact($id);
        }

        return $this->contacts[$id];
    }
}
