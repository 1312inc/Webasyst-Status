<?php

/**
 * Class statusUserRepository
 */
class statusUserRepository extends statusBaseRepository
{
    protected $entity = pocketlistsUser::class;

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
            $userData = $this->findByFields(['contact_id' => $contactId]);
            $user = $this->generateWithData($userData);
            $this->cache($contactId, $user);
        }

        return $user;
    }
}
