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
     * statusUser constructor.
     *
     * @param waContact $contact
     */
    public function __construct(waContact $contact)
    {
        $this->contact = $contact;
        $this->contact_id = $contact->getId();
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
     */
    public function setContactId($contact_id)
    {
        $this->contact_id = $contact_id;

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
     */
    public function setContact(waContact $contact)
    {
        $this->contact = $contact;

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
}
