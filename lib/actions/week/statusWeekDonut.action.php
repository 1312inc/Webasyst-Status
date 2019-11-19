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

        /** @var statusProject $project */
        if (!$weekNum) {
            throw new kmwaNotFoundException('No week with num ' . $weekNum);
        }

        $assembler = new statusWeekDtoAssembler();
        $week = statusWeekFactory::createWeekByNum($weekNum);

        $weeksDto = new statusWeekDto($week);
        $donut = $assembler->getDonutUserStatDto($weeksDto, $week, $this->user);

        $this->view->assign('donut', $donut);
    }
}
