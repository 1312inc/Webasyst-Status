<?php

/**
 * Class statusReportDataDto
 */
class statusReportDataDto
{
    const TYPE_CONTACT = 'contact';
    const TYPE_PROJECT = 'project';

    /**
     * @var string
     */
    public $name;

    /**
     * @var string
     */
    public $duration;

    /**
     * @var int
     */
    public $identity;

    /**
     * @var string
     */
    public $type;

    /**
     * @var string
     */
    public $icon;

    /**
     * statusReportDataDto constructor.
     *
     * @param $name
     * @param $duration
     * @param $identity
     * @param $type
     */
    public function __construct($name, $duration, $identity, $type)
    {
        $this->duration = $duration;
        $this->identity = $identity;
        $this->type = $type;
        $this->name = $name;
    }
}
