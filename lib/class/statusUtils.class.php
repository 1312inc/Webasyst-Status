<?php

/**
 * Class statusUtils
 */
class statusUtils
{
    /**
     * @throws waException
     */
    public function fixProjectDurations()
    {
        $model = stts()->getModel();
        $checkinModel = new statusCheckinProjectsModel();

        $model->exec('delete from status_checkin_projects where duration < 1');

        $sql = <<<SQL
select sc.id, sc.total_duration, t.project_duration
from status_checkin sc
join (
    select sum(scp.duration) project_duration, scp.checkin_id
    from status_checkin_projects scp
    group by scp.checkin_id
) t on t.checkin_id = sc.id
where project_duration > total_duration
SQL;
        $projectCheckinsSql = <<<SQL
select * from status_checkin_projects scp where scp.checkin_id = i:checkin_id
SQL;

        $depth = 10;
        while (($tofix = $model->query($sql)->fetchAll()) && $depth-- > 0) {
            foreach ($tofix as $tofixDatum) {
                $delta = $tofixDatum['project_duration'] - $tofixDatum['total_duration'];
                $projectCheckins = $model->query($projectCheckinsSql, ['checkin_id' => $tofixDatum['id']])->fetchAll();

                $deltaPerProject = (int)($delta / count($projectCheckins));
                foreach ($projectCheckins as $i => &$project) {
                    $project['duration'] -= $deltaPerProject;
                    if ($project['duration'] <= 0) {
                        $deltaPerProject += (abs($project['duration']) / (count($projectCheckins) - 1));
                        $checkinModel->deleteById($project['id']);
                        continue;
                    }
                    $checkinModel->updateById($project['id'], $project);
                }
                $projectsDurations = array_sum(array_column($projectCheckins, 'duration'));
                if ($projectsDurations != $tofixDatum['total_duration'] && $project) {
                    $project['duration'] += ($tofixDatum['total_duration'] - $projectsDurations);
                    $checkinModel->updateById($project['id'], $project);
                }
                unset($project);
            }
        }
    }
}
