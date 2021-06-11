<?php

/**
 * Class statusReportDataUser
 */
class statusReportDataUser implements statusReportDataProviderInterface
{
    const TYPE = 'user';

    /**
     * @param DateTimeInterface $start
     * @param DateTimeInterface $end
     * @param null|int          $projectId
     *
     * @return statusReportDataDto[]
     * @throws waException
     */
    public function getData(DateTimeInterface $start, DateTimeInterface $end, $projectId = null)
    {
        $dtos = [];
        $sql = <<<SQL
select %s duration,
       sc.contact_id id
from status_checkin sc
%s
where date(sc.date) between s:start and s:end %s
group by sc.contact_id;
SQL;

        $filterSql = ['sum(sc.total_duration)', '', ''];
        if ($projectId) {
            $filterSql = [
                'sum(if(isnull(scp.id), sc.total_duration, scp.duration))',
                'join status_checkin_projects scp on sc.id = scp.checkin_id',
                'and scp.project_id = i:id',
            ];
        } elseif ($filterSql == 0) {
            $filterSql = [
                'sum(if(isnull(scp.id), sc.total_duration, scp.duration))',
                'left join status_checkin_projects scp on sc.id = scp.checkin_id',
                'and scp.project_id is null',
            ];
        }

        $data = stts()->getModel()
            ->query(
                sprintf($sql, $filterSql[0], $filterSql[1], $filterSql[2]),
                [
                    'start' => $start->format('Y-m-d H:i:s'),
                    'end' => $end->format('Y-m-d H:i:s'),
                    'id' => $projectId,
                ]
            )->fetchAll('id');

        $contactIds = array_column($data, 'id');
        if (!$contactIds) {
            return $dtos;
        }

        /** @var statusUser[] $contacts */
        $contacts = stts()->getEntityRepository(statusUser::class)->findByFields('contact_id', $contactIds, true);
        foreach ($contacts as $contact) {
            $contactId = $contact->getContactId();
            $dtos[$contactId] = new statusReportDataDto(
                $contact->getName(),
                isset($data[$contactId]['duration']) ? $data[$contactId]['duration'] : 0,
                $contactId,
                self::TYPE
            );
            $dtos[$contactId]->icon = sprintf(
                '<i class="userpic userpic-20" style="background-image: url(%s)"></i>',
                $contact->getUserPic()
            );
        }

        usort($dtos, function (statusReportDataDto $d1, statusReportDataDto $d2) {
            return $d2->duration - $d1->duration;
        });

        return $dtos;
    }
}
