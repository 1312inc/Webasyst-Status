<?php

/**
 * Interface statusReportDataInterface
 */
interface statusReportDataProviderInterface
{
    /**
     * @param DateTime $start
     * @param DateTime $end
     * @param null     $filterId
     *
     * @return statusReportDataDto[]
     */
    public function getData(DateTime $start, DateTime $end, $filterId = null);
}
