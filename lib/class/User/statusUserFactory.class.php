<?php

/**
 * Class statusUserFactory
 */
class statusUserFactory extends statusBaseFactory
{
    /**
     * @return statusUser
     */
    public function createNew()
    {
        return new statusUser();
    }

    /**
     * @param waContact $contact
     *
     * @return statusUser
     */
    public function createNewWithContact(waContact $contact)
    {
        return (new statusUser())->setContact($contact);
    }
}
