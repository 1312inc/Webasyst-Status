<?php

/**
 * Class statusDatePeriodVO
 */
class statusDatePeriodVO implements JsonSerializable
{
    const NONE_PERIOD     = 'none';
    const ALL_TIME_PERIOD = 'all_time';
    const DAYS_PERIOD     = 'days';
    const MONTH_PERIOD    = 'months';
    const YEARS_PERIOD    = 'years';
    const BETWEEN_DATES   = 'dates';

    /**
     * @var string
     */
    private $name;

    /**
     * @var DateTime
     */
    private $dateStart;

    /**
     * @var DateTime
     */
    private $dateEnd;

    /**
     * @var string
     */
    private $id;

    /**
     * statusDatePeriodVO constructor.
     *
     * @param $dateStart
     * @param $dateEnd
     * @param $name
     *
     * @throws Exception
     */
    public function __construct($dateStart, $dateEnd, $name, $id)
    {
        $this->dateStart = !$dateStart instanceof DateTimeInterface
            ? statusTimeHelper::createDatetimeForUser('Y-m-d H:i:s', $dateStart)
            : $dateStart;
        $this->dateEnd = !$dateEnd instanceof DateTimeInterface
            ? statusTimeHelper::createDatetimeForUser('Y-m-d H:i:s', $dateEnd)
            : $dateEnd;
        $this->name = $name;
        $this->id = $id;
    }

    /**
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * @return DateTime
     */
    public function getDateStart()
    {
        return $this->dateStart;
    }

    /**
     * @return DateTime
     */
    public function getDateEnd()
    {
        return $this->dateEnd;
    }

    /**
     * @return string
     */
    public function getDateStartFormat()
    {
        return $this->dateStart->format('Y-m-d');
    }

    /**
     * @return string
     */
    public function getDateEndFormat()
    {
        return $this->dateEnd->format('Y-m-d');
    }

    /**
     * @param DateTimeInterface $dateStart
     *
     * @return statusDatePeriodVO
     */
    public function setDateStart(DateTimeInterface $dateStart)
    {
        $this->dateStart = $dateStart;

        return $this;
    }

    /**
     * @param DateTimeInterface $dateEnd
     *
     * @return statusDatePeriodVO
     */
    public function setDateEnd(DateTimeInterface $dateEnd)
    {
        $this->dateEnd = $dateEnd;

        return $this;
    }

    /**
     * @return string
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @inheritDoc
     */
    public function jsonSerialize()
    {
        return [
            'name' => $this->name,
            'start' => $this->dateStart->format('Y-m-d H:i:s'),
            'end' => $this->dateEnd->format('Y-m-d H:i:s'),
        ];
    }
}
