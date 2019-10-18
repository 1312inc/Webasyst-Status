<?php

/**
 * Class statusUserFactory
 */
class statusUserFactory extends statusBaseFactory
{
    /**
     * @return statusUser
     * @throws waException
     */
    public function createNew()
    {
        return new statusUser(new waContact());
    }

    /**
     * @param waContact $contact
     *
     * @return statusUser
     */
    public function createNewWithContact(waContact $contact)
    {
        $user = new statusUser($contact);

        return $user;
    }
}
