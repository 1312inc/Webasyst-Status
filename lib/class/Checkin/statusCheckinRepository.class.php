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
     * @param statusDay  $day
     * @param statusUser $user
     *
     * @return statusAbstractEntity|statusAbstractEntity[]
     * @throws waException
     */
    public function findByDayAndUser(statusDay $day, statusUser $user)
    {
        return $this->findByFields(
            ['date' => $day->getDate()->format('Y-m-d'), 'contact_id' => $user->getContactId()],
            null,
            true
        );
    }

    /**
     * @param statusDay          $dayStart
     * @param statusDay          $dayEnd
     * @param statusUser|null    $user
     * @param statusProject|null $project
     *
     * @return statusCheckin[]
     * @throws waException
     */
    public function findByPeriod(
        statusDay $dayStart,
        statusDay $dayEnd,
        statusUser $user = null,
        statusProject $project = null
    ) {
        if ($user instanceof statusUser) {
            $data = $this->getModel()->getByContactIdAndPeriod(
                $user->getContactId(),
                $dayStart->getDate()->format('Y-m-d'),
                $dayEnd->getDate()->format('Y-m-d')
            );
        } elseif ($project instanceof statusProject) {
            $data = [];
        } else {
            $data = $this->getModel()->getByPeriod(
                $dayStart->getDate()->format('Y-m-d'),
                $dayEnd->getDate()->format('Y-m-d')
            );
        }

        $checkins = [];
        foreach ($data as $datum) {
            if (!isset($checkins[$datum['date']])) {
                $checkins[$datum['date']] = [];
            }
            $checkins[$datum['date']][] = $this->generateWithData($datum);
        }

        return $checkins;
    }

    /**
     * @param statusUser $user
     * @param statusDay  $dayStart
     * @param statusDay  $dayEnd
     *
     * @return statusCheckin[]
     * @throws waException
     */
    public function findByUserAndPeriod(statusUser $user, statusDay $dayStart, statusDay $dayEnd)
    {
        return $this->findByPeriod($dayStart, $dayEnd, $user);
    }

    /**
     * @param statusWeek[] $weeks
     * @param statusUser   $user
     *
     * @return statusCheckin[]
     * @throws waException
     */
    public function findByWeeks(array $weeks, statusUser $user)
    {
        if (empty($weeks)) {
            return [];
        }

        $firstWeek = $weeks[0];
        $maxDay = $firstWeek->getLastDay();

        $lastWeek = $weeks[count($weeks) - 1];
        $minDay = $lastWeek->getFirstDay();

        return $this->findByUserAndPeriod($user, $minDay, $maxDay);
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
