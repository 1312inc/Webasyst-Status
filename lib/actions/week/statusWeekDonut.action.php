<?php

/**
 * Class statusWeekDonutAction
 */
class statusWeekDonutAction extends statusChronologyAction
{
    /**
     * @param null $params
     *
     * @throws kmwaNotFoundException
     * @throws waException
     */
    public function runAction($params = null)
    {
        $weekNum = waRequest::get('week_num', 0, waRequest::TYPE_INT);
        if (!$weekNum) {
            throw new kmwaNotFoundException(_w('No week found'));
        }

        $assembler = new statusWeekDtoAssembler();
        $week = statusWeekFactory::createWeekByNum($weekNum);

        $weeksDto = new statusWeekDto($week);
        $donut = $assembler->getDonutUserStatDto($weeksDto, $week, $this->getWeekDataFilterRequestDto->getUsers());

        $this->view->assign(
            [
                'donut' => $donut,
                'isProject' => false,
            ]
        );
    }
}
