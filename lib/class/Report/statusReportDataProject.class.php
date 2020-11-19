<?php

/**
 * Class statusReportDataProject
 */
class statusReportDataProject implements statusReportDataProviderInterface
{
    const TYPE = 'project';

    /**
     * @param DateTimeInterface $start
     * @param DateTimeInterface $end
     * @param null|int          $contactId
     *
     * @return statusReportDataDto[]
     * @throws waException
     */
    public function getData(DateTimeInterface $start, DateTimeInterface $end, $contactId = null)
    {
        $dtos = [];
        $sql = <<<SQL
select sum(if(isnull(sp.name), sc.total_duration, ifnull(scp.duration, 0))) duration,
       ifnull(sp.id, 0) id
from status_checkin sc
         left join status_checkin_projects scp on sc.id = scp.checkin_id
         left join status_project sp on scp.project_id = sp.id
where date(sc.date) between s:start and s:end %s
group by sp.id
SQL;

        $data = stts()->getModel()
            ->query(
                sprintf($sql, $contactId ? 'and sc.contact_id = i:id' : ''),
                [
                    'start' => $start->format('Y-m-d H:i:s'),
                    'end' => $end->format('Y-m-d H:i:s'),
                    'id' => $contactId,
                ]
            )->fetchAll('id');

        if (!$data) {
            return $dtos;
        }

        /** @var statusProject[] $projects */
        $projects = stts()->getEntityRepository(statusProject::class)->findAllOrderByLastCheckin();
        if (count($projects)) {
            foreach ($projects as $project) {
                $projectId = $project->getId();
                if (!isset($data[$projectId])) {
                    continue;
                }

                $dto = new statusReportDataDto(
                    $project->getName(),
                    $data[$projectId]['duration'],
                    $projectId,
                    self::TYPE
                );
                $dto->icon = sprintf(
                    '<i class="icon16 color" style="background: %s;"></i>',
                    $project->getColor()
                );
                $dtos[] = $dto;
            }
        }
        if (isset($data[0])) {
            $dto = new statusReportDataDto(
                _w('No project'),
                $data[0]['duration'],
                0,
                self::TYPE
            );
            $dto->icon = '<i class="icon16 color" style="background: rgb(130,167,217);
background: linear-gradient(135deg, rgb(225, 127, 206) 0%, rgb(225, 127, 206) 25%, rgba(52,203,254,1) 25%, rgba(52,203,254,1) 50%, rgb(225, 127, 206) 50%, rgb(225, 127, 206) 75%, rgba(52,203,254,1) 75%, rgba(52,203,254,1) 100%); background-size: 7px 7px;"></i>';
            $dtos[] = $dto;
        }

        usort($dtos, function (statusReportDataDto $d1, statusReportDataDto $d2) {
            return $d2->duration - $d1->duration;
        });

        return $dtos;
    }
}
