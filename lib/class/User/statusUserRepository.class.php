<?php

/**
 * Class statusUserRepository
 *
 * @method  statusUserModel getModel()
 */
class statusUserRepository extends statusBaseRepository
{
    protected $entity = statusUser::class;

    /**
     * @param $contactId
     *
     * @return statusUser
     * @throws waException
     */
    public function findByContactId($contactId)
    {
        $today = new DateTimeImmutable();
        $user = $this->findByFields(['contact_id' => $contactId]);
        if ($user instanceof statusUser) {
            $user->setTodayStatus(statusTodayStatusFactory::getForContactId($user->getContactId(), $today));
        } else {
            $user = new statusUser();
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

        $userData = $this->getModel()->findAllOrderByLastCheckin();
        $users = $this->generateWithData($userData, true);
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
