<?php

/**
 * Class statusDayCheckinUserDto
 */
class statusDayCheckinUserDto
{
    /**
     * @var int
     */
    public $id;

    /**
     * @var statusTodayStatus
     */
    public $todayStatus;

    /**
     * @var int
     */
    public $contactId;

    /**
     * @var string
     */
    public $name;

    /**
     * @var string
     */
    public $username;

    /**
     * @var string
     */
    public $photoUrl;

    /**
     * @var string
     */
    public $userPic;

    /**
     * @var string
     */
    public $login;

    /**
     * @var bool
     */
    public $me;

    /**
     * @var bool
     */
    public $exists;

    /**
     * statusDayCheckinUserDto constructor.
     *
     * @param statusUser $user
     */
    public function __construct(statusUser $user)
    {
        $this->name = $user->getName();
        $this->contactId = $user->getContactId();
        $this->id = $user->getId();
        $this->exists = $user->isExists();
        $this->login = $user->getLogin();
        $this->me = $user->isMe();
        $this->photoUrl = $user->getPhotoUrl();
        $this->userPic = $user->getUserPic();
        $this->username = $user->getUsername();
    }
}