<?php


class statusGetWeekDataFilterRequestDto
{
    public const ALL_USERS_ID = -1312;

    /**
     * @var array<statusUser>|null
     */
    private $users;

    /**
     * @var statusProject|null
     */
    private $project;

    public function __construct(?int $contactId, ?int $projectId, ?int $groupId)
    {
        /** @var statusUserRepository $userRepository */
        $userRepository = stts()->getEntityRepository(statusUser::class);

        /** @var statusProjectRepository $projectRepository */
        $projectRepository = stts()->getEntityRepository(statusProject::class);

        if ($projectId) {
            $this->project = $projectRepository->findById($projectId);

            if (!$this->project instanceof statusProject) {
                throw new kmwaNotFoundException('Project not found');
            }

            if (!stts()->getRightConfig()->hasAccessToProject($projectId)) {
                throw new kmwaForbiddenException(_w('You don`t have access to this project'));
            }
        } elseif ($groupId) {
            $groupContactsIds = (new waUserGroupsModel())->getContactIds($groupId);

            foreach ($groupContactsIds as $groupContactsId) {
                $statusUser = $userRepository->findByContactId($groupContactsId);
                if (!$statusUser->getId()) {
                    continue;
                }

                if (!$statusUser->isExists()) {
                    continue;
                }

                $this->users[] = $statusUser;
            }

            if (!$this->users) {
                throw new kmwaNotFoundException('Team data not found');
            }
        } elseif ($contactId == self::ALL_USERS_ID) {
            if (!stts()->getRightConfig()->hasAccessToTeammate()) {
                throw new kmwaForbiddenException(_w('You don`t have access to this user'));
            }

            /** @var statusUser[] $allUsers */
            $allUsers = $userRepository->findAll();
            foreach ($allUsers as $user) {
                $this->users[] = $user;
            }
        } else {
            $user = !$contactId
                ? stts()->getUser()
                : $userRepository->findByContactId($contactId);

            if (!$user->getId()) {
                $user = stts()->getEntityFactory(statusUser::class)->createNewWithContact(
                    new waContact($contactId)
                );
            }

            if (!$user->getContact()->exists()) {
                throw new kmwaNotFoundException('User not found');
            }

            if ($user->getContactId() != wa()->getUser()->getId()
                && !stts()->getRightConfig()->hasAccessToTeammate($user)) {
                throw new kmwaForbiddenException(_w('You don`t have access to this user'));
            }

            $this->users = [$user];
        }
    }

    /**
     * @return statusUser[]|null
     */
    public function getUsers(): ?array
    {
        return $this->users;
    }

    public function getProject(): ?statusProject
    {
        return $this->project;
    }
}
