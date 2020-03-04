<?php

/**
 * Class statusReportService
 */
final class statusReportService
{
    /**
     * @var statusDatePeriodVO[]
     */
    private static $datePeriods = [];

    /**
     * @var statusModel
     */
    private $model;

    /**
     * statusReportService constructor.
     *
     * @throws waException
     */
    public function __construct()
    {
        $this->model = stts()->getModel();
    }

    /**
     * @return statusDatePeriodVO[]
     * @throws Exception
     */
    public static function getPeriods()
    {
        if (empty(self::$datePeriods)) {
            $date = new DateTimeImmutable();
            self::$datePeriods[] = new statusDatePeriodVO(
                $date->modify('-30 days')->setTime(0, 0),
                clone $date->setTime(23, 59, 59),
                _w('Last 30 days'),
                'l30d'
            );
            self::$datePeriods[] = new statusDatePeriodVO(
                $date->modify('-90 days')->setTime(0, 0),
                clone $date->setTime(23, 59, 59),
                _w('Last 90 days'),
                'l90d'
            );
            self::$datePeriods[] = new statusDatePeriodVO(
                $date->modify('-365 days')->setTime(0, 0),
                clone $date->setTime(23, 59, 59),
                _w('Last 365 days'),
                'l365d'
            );
            $thisMonth = $date->modify('first day of this month')->setTime(0, 0);
            self::$datePeriods[] = new statusDatePeriodVO(
                $thisMonth,
                $date->modify('last day of this month')->setTime(23, 59, 59),
                sprintf('%s %s',_w($thisMonth->format('F')), $thisMonth->format('Y')),
                'this_month'
            );
            $previousMonth = $date->modify('first day of previous month')->setTime(0, 0);
            self::$datePeriods[] = new statusDatePeriodVO(
                $previousMonth,
                $date->modify('last day of previous month')->setTime(23, 59, 59),
                sprintf('%s %s',_w($previousMonth->format('F')), $previousMonth->format('Y')),
                'prev_month'
            );
        }

        return self::$datePeriods;
    }


    /**
     * @param DateTimeInterface $dateStart
     * @param DateTimeInterface $dateEnd
     * @param bool              $custom
     *
     * @return statusDatePeriodVO
     * @throws Exception
     */
    public function getPeriodByDates(DateTimeInterface $dateStart, DateTimeInterface $dateEnd, $custom = false)
    {
        $periods = self::getPeriods();
        $periodCustom = new statusDatePeriodVO(
            clone $dateStart->setTime(0, 0),
            clone $dateEnd->setTime(23, 59, 59),
            _w('Select dates...'),
            'custom'
        );
        if ($custom) {
            return $periodCustom;
        }

        for ($i = 0, $iMax = count($periods) - 1; $i < $iMax; $i++) {
            if ($dateStart->format('Y-m-d') == $periods[$i]->getDateStartFormat()
                && $dateEnd->format('Y-m-d') == $periods[$i]->getDateEndFormat()) {
                return $periods[$i];
            }
        }

        return $periodCustom;
    }
}
