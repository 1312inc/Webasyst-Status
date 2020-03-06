<?php

/**
 * Class statusRightConfig
 */
class statusRightConfig extends waRightConfig
{
    const CAN_MANAGE_SELF_STATUS_TIMELINE = 'can_manage_self_status_timeline';
    const CAN_SEE_TEAMMATES               = 'can_see_teammates';
    const CAN_SEE_CONTRIBUTE_TO_PROJECTS  = 'can_see_contribute_to_projects';
    const CAN_SEE_REPORTS                 = 'can_see_reports';

    const TEAMMATE_USER = 'user';

    const RIGHT_CAN    = 1;
    const RIGHT_CANNOT = 0;

    /**
     * @var int
     */
    private $userId;

    /**
     * @var waContactRightsModel
     */
    private $model;

    /**
     * @var array
     */
    private $accesses = [];

    /**
     * pocketlistsRightConfig constructor.
     */
    public function __construct()
    {
        $this->userId = waRequest::post('user_id', 0, waRequest::TYPE_INT);

        if (!$this->userId) {
            $this->userId = waRequest::request('id', 0, waRequest::TYPE_INT);
        }

        $this->model = new waContactRightsModel();

        parent::__construct();
    }

    /**
     * @return array
     */
    public function getRightsList()
    {
        return [
            self::CAN_MANAGE_SELF_STATUS_TIMELINE,
            self::CAN_SEE_TEAMMATES.'.all',
            self::CAN_SEE_TEAMMATES.'.%',
            self::CAN_SEE_CONTRIBUTE_TO_PROJECTS.'.all',
            self::CAN_SEE_CONTRIBUTE_TO_PROJECTS.'.%',
        ];
    }

    /**
     * @throws waException
     */
    public function init()
    {
        $this->addItem(
            self::CAN_MANAGE_SELF_STATUS_TIMELINE,
            _w('Can manage self status & timeline'),
            'always_enabled'
        );

        $this->addItem(
            self::CAN_SEE_REPORTS,
            _w('Can see reports for all projects and users'),
            'checkbox'
        );

        $items = [];
        /** @var statusUser $user */
        foreach (stts()->getEntityRepository(statusUser::class)->findAll() as $user) {
            if ($user->getContact()->getId() == $this->userId) {
                continue;
            }

            $items[$user->getContactId()] = $user->getContact()->getName();
            if (!$user->isExists()) {
                $items[$user->getContactId()] .= sprintf(' %s', _w('(inactive)'));
            }
        }

        $this->addItem(
            self::CAN_SEE_TEAMMATES,
            _w('Can see teammates'),
            'list',
            ['items' => $items, 'hint1' => 'all_checkbox']
        );

        $items = [];
        /** @var statusUser $user */
        foreach (stts()->getEntityRepository(statusProject::class)->findAll() as $project) {
            $items[$project->getId()] = $project->getName();
        }

        $this->addItem(
            self::CAN_SEE_CONTRIBUTE_TO_PROJECTS,
            _w('Can see & contribute to projects'),
            'list',
            ['items' => $items, 'hint1' => 'all_checkbox']
        );

        /**
         * @event rights.config
         *
         * @param waRightConfig $this Rights setup object
         *
         * @return void
         */
        wa()->event('rights.config', $this);
    }

    /**
     * @param int $contact_id
     *
     * @return array
     */
    public function getDefaultRights($contact_id)
    {
        return [
            self::CAN_MANAGE_SELF_STATUS_TIMELINE => 1,
        ];
    }

    /**
     * @param int    $contactId
     * @param string $right
     * @param null   $value
     *
     * @return bool
     * @throws waException
     */
    public function setRights($contactId, $right, $value = null)
    {
        $right_model = new waContactRightsModel();

        $saveGroup = 0;
        if ($contactId < 1) {
            $contactIds = (new waUserGroupsModel())->getContactIds(abs($contactId));
            $saveGroup = $contactId;
            $right_model->save($contactId, statusConfig::APP_ID, $right, $value);
        } else {
            $contactIds = [$contactId];
        }

        foreach ($contactIds as $contactId) {
            $user = stts()->getEntityRepository(statusUser::class)->findByContactId($contactId);
            if (!$user instanceof statusUser) {
                $user = stts()->getEntityFactory(statusUser::class)->createNewWithContact(new waContact($contactId));
                stts()->getEntityPersister()->insert($user);
            }

            if (!$saveGroup) {
                $right_model->save($contactId, statusConfig::APP_ID, $right, $value);
            }
        }

        return true;

    }

    /**
     * @return array
     */
    public function getUserIdsWithAccess()
    {
        return $this->model->getUsers(statusConfig::APP_ID);
    }

    /**
     * @param int|statusUser|null $user
     *
     * @return bool
     */
    public function isAdmin($user = null)
    {
        if ($user === null) {
            $user = wa()->getUser()->getId();
        }
        if ($user instanceof statusUser) {
            $user = $user->getContactId();
        }

        $this->loadRightsForContactId($user);

        return isset($this->accesses[$user]['backend']) && $this->accesses[$user]['backend'] > 1;
    }

    /**
     * @param int|statusUser      $teammate
     * @param int|statusUser|null $user
     *
     * @return bool
     * @throws waException
     */
    public function hasAccessToTeammate($teammate = null, $user = null)
    {
        if ($user === null) {
            $user = wa()->getUser()->getId();
        }
        if ($user instanceof statusUser) {
            $user = $user->getContactId();
        }
        if ($teammate instanceof statusUser) {
            $teammate = $teammate->getContactId();
        }

        if ($user == $teammate) {
            return true;
        }

        $this->loadRightsForContactId($user);

        if ($this->isAdmin($user)) {
            return true;
        }

        if ($teammate === null) {
            return !empty($this->accesses[$user][self::CAN_SEE_TEAMMATES.'.all']);
        }

        return !empty($this->accesses[$user][self::CAN_SEE_TEAMMATES.'.'.$teammate])
            || !empty($this->accesses[$user][self::CAN_SEE_TEAMMATES.'.all']);
    }

    /**
     * @todo refactor
     * @param int|statusProject|null $project
     * @param int|statusUser|null    $user
     *
     * @return bool
     * @throws waException
     */
    public function hasAccessToProject($project = null, $user = null)
    {
        if ($user === null) {
            $user = wa()->getUser()->getId();
        }
        if ($user instanceof statusUser) {
            $user = $user->getContactId();
        }
        if ($project instanceof statusProject) {
            $project = $project->getId();
        }

        $this->loadRightsForContactId($user);

        if ($this->isAdmin($user)) {
            return true;
        }

        if ($project === null) {
            return !empty($this->accesses[$user][self::CAN_SEE_CONTRIBUTE_TO_PROJECTS.'.all']);
        }

        return !empty($this->accesses[$user][self::CAN_SEE_CONTRIBUTE_TO_PROJECTS.'.'.$project])
            || !empty($this->accesses[$user][self::CAN_SEE_CONTRIBUTE_TO_PROJECTS.'.all']);
    }

    /**
     * @param int|statusUser|null $user
     *
     * @return bool
     * @throws waException
     */
    public function hasAccessToApp($user = null)
    {
        return $this->hasAccessToRight('backend', $user);
    }

    /**
     * @param int|statusUser|null $user
     *
     * @return bool
     * @throws waException
     */
    public function hasAccessToRight($right, $user = null)
    {
        if ($user === null) {
            $user = wa()->getUser()->getId();
        }
        if ($user instanceof statusUser) {
            $user = $user->getContactId();
        }

        $this->loadRightsForContactId($user);

        if ($this->isAdmin($user)) {
            return true;
        }

        return !empty($this->accesses[$user][$right]);
    }

    /**
     * @param int $contactId
     *
     * @throws waException
     */
    private function loadRightsForContactId($contactId)
    {
        if (isset($this->accesses[$contactId])) {
            return;
        }

        $contact = new waContact($contactId);

        $this->accesses[$contactId] = $contact->getRights(statusConfig::APP_ID);
    }
}
