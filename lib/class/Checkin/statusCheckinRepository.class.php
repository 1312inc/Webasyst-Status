<?php

/**
 * Class statusCheckinRepository
 *
 * @method statusCheckinModel getModel()
 */
class statusCheckinRepository extends statusBaseRepository
{
    protected $entity = statusCheckin::class;

    /**
     * @param statusDay $day
     *
     * @return statusCheckin[]
     * @throws waException
     */
    public function findByDay(statusDay $day)
    {
        return $this->findByFields(['date' => $day->getDate()->format('Y-m-d')], null, true);
    }

    /**
     * @param statusDay $dayStart
     * @param statusDay $dayEnd
     *
     * @return statusCheckin[]
     * @throws waException
     */
    public function findByPeriod(statusDay $dayStart, statusDay $dayEnd)
    {
        $data = $this->getModel()->getByPeriod($dayStart->getDate(), $dayEnd->getDate());

        $checkins = [];
        foreach ($data as $datum) {
            if (!isset($checkins[$datum['date']])) {
                $checkins[$datum['date']] = [];
            }
            $checkins[$datum['date']][] = $this->generateWithData($datum);
        }

        return  $checkins;
    }

    /**
     * @param statusWeek[] $weeks
     *
     * @return statusCheckin[]
     * @throws waException
     */
    public function findByWeeks(array $weeks)
    {
        if (empty($weeks)) {
            return [];
        }

        $firstWeek = $weeks[0];
        $firstWeekDays = $firstWeek->getDays();
        $maxDay = $firstWeekDays[0];

        $lastWeek = $weeks[count($weeks) - 1];
        $lastWeekDays = $lastWeek->getDays();
        $minDay = $lastWeekDays[6];

        return $this->findByPeriod($minDay, $maxDay);
    }

    /**
     * @param statusUser $user
     * @param statusDay  $day
     *
     * @return statusCheckin[]
     * @throws waException
     */
    public function findForUserAndDay(statusUser $user, statusDay $day)
    {
        return $this->findByFields(
            [
                'date'       => $day->getDate()->format('Y-m-d'),
                'contact_id' => $user->getContactId(),
            ],
            true
        );

    }

    /**
     * @param statusProject $project
     * @param statusDay     $day
     *
     * @return statusCheckin[]
     * @throws waException
     */
    public function findForProjectAndDay(statusProject $project, statusDay $day)
    {
        $data = $this->getModel()->getByProjectIdAndDate($project->getId(), $day->getDate());

        return $this->generateWithData($data, true);
    }
}
