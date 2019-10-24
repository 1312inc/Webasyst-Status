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
     * @throws kmwaLogicException
     */
    public function createNewWithContact(waContact $contact)
    {
        $user = (new statusUser())->setContact($contact);

        return $user;
    }
}
