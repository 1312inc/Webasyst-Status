<?php

/**
 * Class statusRightConfig
 */
class statusRightConfig extends waRightConfig
{
    const CAN_MANAGE_SELF_STATUS_TIMELINE = 'can_manage_self_status_timeline';
    const CAN_SEE_TEAMMATES               = 'can_see_teammates';
    const CAN_SEE_CONTRIBUTE_TO_PROJECTS  = 'can_see_contribute_to_projects';

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
     * @throws waException
     */
    public function init()
    {
        $this->addItem(self::CAN_MANAGE_SELF_STATUS_TIMELINE, _w('Can manage self status & timeline'));

        $items = [];
        /** @var statusUser $user */
        foreach (stts()->getEntityRepository(statusUser::class)->findAll() as $user) {
            if ($user->getContact()->getId() == $this->userId) {
                continue;
            }

            $items[$user->getId()] = $user->getContact()->getName();
        }

        $this->addItem(
            self::CAN_SEE_TEAMMATES,
            _w('Can see teammates'),
            'list',
            ['items' => $items]
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
            ['items' => $items]
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
     * @param int    $contact_id
     * @param string $right
     * @param null   $value
     *
     * @return bool
     * @throws waException
     */
    public function setRights($contact_id, $right, $value = null)
    {
        $right_model = new waContactRightsModel();

        $user = stts()->getEntityRepository(statusUser::class)->findByContactId($contact_id);
        if (!$user instanceof statusUser) {
            $user = stts()->getEntityFactory(statusUser::class)->createNewWithContact(new waContact($contact_id));
            stts()->getEntityPersister()->insert($user);
        }

        return $right_model->save(
            $contact_id,
            statusConfig::APP_ID,
            $right,
            $value
        );
    }

    /**
     * @return array
     */
    public function getUserIdsWithAccess()
    {
        return $this->model->getUsers(statusConfig::APP_ID);
    }
}
