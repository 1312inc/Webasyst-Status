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
        $today = new DateTimeImmutable();
        $user = $this->getFromCache($contactId);
        if (!$user instanceof statusUser) {
            $user = $this->findByFields(['contact_id' => $contactId]);
            if ($user instanceof statusUser) {
                $user->setTodayStatus(statusTodayStatusFactory::getForContactId($user->getContactId(), $today));
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
        $today = new DateTimeImmutable();

        $users = $this->findAll();
        /** @var statusUser $user */
        foreach ($users as $i => $user) {
            if ($user->isMe()) {
                unset($users[$i]);
                continue;
            }

            $user->setTodayStatus(statusTodayStatusFactory::getForContactId($user->getContactId(), $today));
        }

        return $users;
    }
}
