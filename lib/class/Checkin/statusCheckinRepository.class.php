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
     * @return statusCheckin[]
     * @throws waException
     */
    public function findByDayAndUser(statusDay $day, statusUser $user): array
    {
        return $this->findByFields(
            ['date' => $day->getDate()->format('Y-m-d'), 'contact_id' => $user->getContactId()],
            null,
            true
        );
    }

    /**
     * @param statusDay  $day
     * @param statusUser $user
     *
     * @return statusCheckin[]
     * @throws waException
     */
    public function findWithTraceByDayAndUser(statusDay $day, statusUser $user): array
    {
        return $this->generateWithData(
            $this->getModel()->getWithTraceByContactIdAndDate(
                $user->getContactId(),
                $day->getDate()->format('Y-m-d')
            ),
            true
        );
    }

    /**
     * @param statusDay          $dayStart
     * @param statusDay          $dayEnd
     * @param array              $contactIds
     * @param int|null $projectId
     *
     * @return statusCheckin[]
     * @throws waException
     */
    public function findByPeriodAndContactIds(
        statusDay $dayStart,
        statusDay $dayEnd,
        array $contactIds = [],
        $projectId = null
    ): array {
        $data = $this->getModel()->getByContactIdsAndPeriod(
            $contactIds,
            $dayStart->getDate()->format('Y-m-d'),
            $dayEnd->getDate()->format('Y-m-d'),
            $projectId
        );

        $checkins = [];
        foreach ($data as $datum) {
            if (!isset($checkins[$datum['date']])) {
                $checkins[$datum['date']] = [$datum['contact_id'] => []];
            }
            $checkins[$datum['date']][$datum['contact_id']][] = $this->generateWithData($datum);
        }

        return $checkins;
    }

    /**
     * @param statusDay          $dayStart
     * @param statusDay          $dayEnd
     * @param array              $contactIds
     * @param int|null $projectId
     *
     * @return statusCheckin[]
     * @throws waException
     */
    public function findWithTraceByPeriodAndContactIds(
        statusDay $dayStart,
        statusDay $dayEnd,
        array $contactIds = [],
        $projectId = null
    ): array {
        $data = $this->getModel()->getWithTraceByContactIdsAndPeriod(
            $contactIds,
            $dayStart->getDate()->format('Y-m-d'),
            $dayEnd->getDate()->format('Y-m-d'),
            $projectId
        );

        $checkins = [];
        foreach ($data as $datum) {
            if (!isset($checkins[$datum['date']])) {
                $checkins[$datum['date']] = [$datum['contact_id'] => []];
            }
            $checkins[$datum['date']][$datum['contact_id']][] = $this->generateWithData($datum);
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
     * @param statusWeek[]    $weeks
     * @param statusUser|null $user
     *
     * @return statusCheckin[]
     * @throws waException
     */
    public function findByWeeks(array $weeks, statusUser $user = null)
    {
        if (empty($weeks)) {
            return [];
        }

        $firstWeek = $weeks[0];
        $maxDay = $firstWeek->getLastDay();

        $lastWeek = $weeks[count($weeks) - 1];
        $minDay = $lastWeek->getFirstDay();

        return $this->findByPeriod($minDay, $maxDay, $user);
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
                'date' => $day->getDate()->format('Y-m-d'),
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
