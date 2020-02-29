<?php

/**
 * Class statusReportDataUser
 */
class statusReportDataUser implements statusReportDataProviderInterface
{
    /**
     * @param DateTime $start
     * @param DateTime $end
     * @param null|int $filterId
     *
     * @return statusReportDataDto[]
     * @throws waException
     */
    public function getData(DateTime $start, DateTime $end, $filterId = null)
    {
        $dtos = [];
        $sql = <<<SQL
select sum(sc.total_duration) duration,
       sc.contact_id id
from status_checkin sc
left join status_checkin_projects scp on sc.id = scp.checkin_id
where date(sc.date) between s:start and s:end %s
group by sc.contact_id;
SQL;

        $data = stts()->getModel()
            ->query(
                sprintf($sql, $filterId ? 'and scp.project_id = i:id' : ''),
                [
                    'start' => $start->format('Y-m-d H:i:s'),
                    'end' => $end->format('Y-m-d H:i:s'),
                    'ids' => $filterId,
                ]
            )->fetchAll('id');

        $userIds = array_column($data, 'id');
        if (!$userIds) {
            return $dtos;
        }

        /** @var statusUser[] $contacts */
        $contacts = stts()->getEntityRepository(statusUser::class)->findById($userIds);
        foreach ($contacts as $contact) {
            $contactId = $contact->getId();
            $dtos[$contactId] = new statusReportDataDto(
                $contact->getName(),
                $data[$contactId]['duration'],
                $contactId,
                statusReportDataDto::TYPE_CONTACT
            );
            $dtos[$contactId]->icon = sprintf(
                '<i class="icon16 userpic20" style="background-image: url(%s)"></i>',
                $contact->getUserPic()
            );
        }

        return $dtos;
    }
}
