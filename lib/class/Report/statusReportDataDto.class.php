<?php

/**
 * Class statusReportDataDto
 */
class statusReportDataDto implements JsonSerializable
{
    /**
     * @var string
     */
    public $name;

    /**
     * @var string
     */
    public $duration;

    /**
     * @var string
     */
    public $durationStr;

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
        $this->durationStr = statusTimeHelper::getTimeDurationInHuman(0, $duration * 60);
        $this->identity = $identity;
        $this->type = $type;
        $this->name = $name;
    }

    /**
     * @return array
     */
    public function jsonSerialize()
    {
        $data = [];
        foreach (get_class_vars(self::class) as $propName => $prop) {
            $data[$propName] = $this->$propName;
        }

        return $data;
    }
}
