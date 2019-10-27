<?php

/**
 * Class statusUserRepository
 */
class statusUserRepository extends statusBaseRepository
{
    protected $entity = statusUser::class;

    /**
     * @param $contactId
     *
     * @return statusUser|null
     * @throws waException
     */
    public function findByContactId($contactId)
    {
        $user = $this->getFromCache($contactId);
        if (!$user instanceof statusUser) {
            $user = $this->findByFields(['contact_id' => $contactId]);
            if ($user instanceof statusUser) {
                $this->cache($contactId, $user);
            }
        }

        return $user;
    }

    /**
     * @param waContact $contact
     *
     * @return statusUser|null
     * @throws waException
     */
    public function findByContact(waContact $contact)
    {
        return $this->findByContactId($contact->getId());
    }

    /**
     * @return statusUser[]
     * @throws waException
     */
    public function findAllExceptMe()
    {
        $users = $this->findAll();
        /** @var statusUser $user */
        foreach ($users as $i => $user) {
            if ($user->isMe()) {
                unset($users[$i]);
                break;
            }
        }

        return $users;
    }
}
