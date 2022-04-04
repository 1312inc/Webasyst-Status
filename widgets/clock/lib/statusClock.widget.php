<?php

class statusClockWidget extends waWidget
{
    protected $params;

    const TYPE_ROUND      = 'round';
    const FORMAT_24       = '24';

    public function defaultAction()
    {
        $date = statusTimeHelper::createDatetimeForUser('Y-m-d')->format('Y-m-d');
        $user = stts()->getUser();

        $week = statusWeekFactory::createWeekByDate(new DateTime($date));
        $day = new statusDay(new DateTime($date));
        $week->setDays([$day]);
        $dto = new statusGetWeekDataFilterRequestDto($user->getId(), null, null);
        $weeksDto = statusWeekFactory::getWeeksDto([$week], $dto);
        $weekDto = reset($weeksDto);
        $dayDto = reset($weekDto->days);

        $this->display([
            'widget_id' => $this->id,
            'widget_url' => $this->getStaticUrl(),
            'widget_app' => $this->info['app_id'],
            'widget_name' => $this->info['widget'],
            'type' => $this->getType(),
            'format' => $this->getFormat(),
            'size' => $this->info['size'],
            'day' => $dayDto,
            'ui' => wa()->whichUI('webasyst'),
        ], $this->getTemplatePath(ucfirst($this->getType())) . '.html');
    }

    public function getType()
    {
        return $this->getSettings('type') ?: self::TYPE_ROUND;
    }

    public function getFormat()
    {
        return $this->getSettings('format') ?: self::FORMAT_24;
    }
}