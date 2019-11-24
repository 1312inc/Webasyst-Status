<?php

/**
 * Class statusUser
 */
class statusUser extends statusAbstractEntity
{
    /**
     * @var int
     */
    private $id;

    /**
     * @var int
     */
    private $contact_id;

    /**
     * @var waContact
     */
    private $contact;

    /**
     * @var DateTime|string
     */
    private $last_checkin_datetime;

    /**
     * @var int
     */
    private $this_week_total_duration = 0;

    /**
     * @var string
     */
    protected $name = '(DELETED USER)';

    /**
     * @var string
     */
    protected $username = '(DELETED USER)';

    /**
     * @var string
     */
    protected $photoUrl = '/wa-content/img/userpic96@2x.jpg';

    /**
     * @var string
     */
    protected $userPic = '/wa-content/img/userpic20@2x.jpg';

    /**
     * @var string
     */
    protected $status;

    /**
     * @var statusTodayStatus
     */
    protected $todayStatus;

    /**
     * @var string
     */
    protected $login = 'deleted';

    /**
     * @var bool
     */
    protected $me = false;

    /**
     * @var bool
     */
    protected $exists = false;

    /**
     * @var int
     */
    protected $lastActivity = 0;

    /**
     * @var array
     */
    protected $listActivities;

    /**
     * @var string|null
     */
    protected $email = 'deleted@1312.localhost';

    /**
     * @var string
     */
    protected $locale;

    public function __construct()
    {
        $this->status = new statusTodayStatus();
    }

    /**
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @param int $id
     *
     * @return statusUser
     */
    public function setId($id)
    {
        $this->id = $id;

        return $this;
    }

    /**
     * @return int
     */
    public function getContactId()
    {
        return $this->contact_id;
    }

    /**
     * @param int $contact_id
     *
     * @return statusUser
     * @throws waException
     * @throws kmwaLogicException
     */
    public function setContactId($contact_id)
    {
        $this->setContact(new waContact($contact_id));

        return $this;
    }

    /**
     * @return waContact
     */
    public function getContact()
    {
        return $this->contact;
    }

    /**
     * @param waContact $contact
     *
     * @return statusUser
     * @throws kmwaLogicException
     */
    public function setContact(waContact $contact)
    {
        $this->contact = $contact;
        if ($contact->getId() && $contact->exists()) {
            $this->contact_id = $contact->getId();
            $this->init();
        } else {
            throw new kmwaLogicException('No waContact for statusUser');
        }

        return $this;
    }

    /**
     * @return DateTime|string
     */
    public function getLastCheckinDatetime()
    {
        return $this->last_checkin_datetime;
    }

    /**
     * @param DateTime|string $last_checkin_datetime
     *
     * @return statusUser
     */
    public function setLastCheckinDatetime($last_checkin_datetime)
    {
        $this->last_checkin_datetime = $last_checkin_datetime;

        return $this;
    }

    /**
     * @return int
     */
    public function getThisWeekTotalDuration()
    {
        return $this->this_week_total_duration;
    }

    /**
     * @param int $this_week_total_duration
     *
     * @return statusUser
     */
    public function setThisWeekTotalDuration($this_week_total_duration)
    {
        $this->this_week_total_duration = $this_week_total_duration;

        return $this;
    }

    /**
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * @return string
     */
    public function getUsername()
    {
        return $this->username;
    }

    /**
     * @return string
     */
    public function getPhotoUrl()
    {
        return $this->photoUrl;
    }

    /**
     * @return string
     */
    public function getUserPic()
    {
        return $this->userPic;
    }

    /**
     * @return statusTodayStatus|null
     */
    public function getTodayStatus()
    {
        return $this->todayStatus;
    }

    /**
     * @param statusTodayStatus|null $todayStatus
     *
     * @return $this
     */
    public function setTodayStatus(statusTodayStatus $todayStatus = null)
    {
        $this->todayStatus = $todayStatus;

        return $this;
    }

    /**
     * @return string
     */
    public function getLogin()
    {
        return $this->login;
    }

    /**
     * @return bool
     */
    public function isMe()
    {
        return $this->me;
    }

    /**
     * @return bool
     */
    public function isExists()
    {
        return $this->exists;
    }

    /**
     * @return int
     */
    public function getLastActivity()
    {
        return $this->lastActivity;
    }

    /**
     * @return array
     */
    public function getListActivities()
    {
        return $this->listActivities;
    }

    /**
     * @return string|null
     */
    public function getEmail()
    {
        return $this->email;
    }

    /**
     * @return string
     */
    public function getLocale()
    {
        return $this->locale;
    }

    /**
     * @return $this
     */
    protected function init()
    {
        if ($this->contact->exists()) {
            $this->me = ($this->contact->getId() == wa()->getUser()->getId());
            $this->name = $this->contact->getName();
            $this->username = $this->contact->getName();
            $this->contact_id = $this->contact->getId();
            $this->photoUrl = $this->contact->getPhoto();
            $this->login = $this->contact->get('login');
            $this->userPic = $this->contact->getPhoto(20);
            $this->status = $this->contact->getStatus();
            $this->exists = $this->contact->get('is_user') != -1;
            $this->email = $this->getContact()->get('email', 'default');
        }

        return $this;
    }
}
