<?php

/**
 * Class statusTodayStatusFactory
 */
final class statusTodayStatusFactory
{
    /**
     * @param statusUser $user
     * @param bool       $force
     *
     * @return statusTodayStatus[]
     * @throws waException
     */
    public static function getAllForUser(statusUser $user, $force = false)
    {
        $key = 'getAllForUser'.$user->getId();
        $result = stts()->getCache()->get($key);
        if (!$force && $result) {
            return $result;
        }

        $defaultStatus = self::hasDefaultStatus() ? 'wcc.default_status' : 'wcc.name';

        $sql = <<<SQL
select if(isnull(wce.summary), {$defaultStatus}, wce.summary) name,
       wcc.id calendar_id,
       wcc.bg_color,
       wcc.font_color,
       datediff(wce.end, wce.start) days,
       wce.id status_id
from wa_contact_calendars wcc
         left join (
    select max(wce.id) last_wce_id ,wce.calendar_id
    from wa_contact_events wce
    where wce.is_status = 1
      and wce.contact_id = i:contact_id
      /*and wce.summary_type = 'custom'*/
    group by wce.calendar_id) last_wce on wcc.id = last_wce.calendar_id
         left join wa_contact_events wce on wce.id = last_wce.last_wce_id
SQL;

        $result = stts()->getModel()
            ->query($sql, ['contact_id' => $user->getContactId()])
            ->fetchAll();

        $statuses = [];
        foreach ($result as $item) {
            if (empty($item['name'])) {
                continue;
            }
            $statuses[] = stts()->getHydrator()->hydrate(new statusTodayStatus(), $item);
        }

        stts()->getCache()->set($key, $statuses, 10);

        return $statuses;
    }

    /**
     * @return statusTodayStatus[]
     * @throws waException
     */
    public static function getAll()
    {
        $sql = <<<SQL
select wcc.id calendar_id, wcc.*
from wa_contact_calendars wcc
SQL;

        $result = stts()->getModel()->query($sql)->fetchAll();

        $statuses = [];
        foreach ($result as $item) {
            $statuses[] = stts()->getHydrator()->hydrate(new statusTodayStatus(), $item);
        }

        return $statuses;
    }

    /**
     * @param int               $contactId
     * @param DateTimeInterface $date
     * @param bool              $force
     *
     * @return statusTodayStatus
     * @throws waException
     */
    public static function getForContactId($contactId, DateTimeInterface $date, $force = false)
    {
        $key = 'getForUser'.$contactId.$date->format('Y-m-d');
        $result = stts()->getCache()->get($key);
        if (!$force && $result) {
            return $result;
        }

        $defaultStatus = self::hasDefaultStatus() ? 'wcc.default_status' : 'wcc.name';

        $sql = <<<SQL
select if(isnull(wce.summary), {$defaultStatus}, wce.summary) name,
       wcc.id calendar_id,
       wcc.bg_color,
       wcc.font_color,
       datediff(wce.end, wce.start) days,
       wce.id status_id
from wa_contact_calendars wcc
         left join (
    select max(wce.id) last_wce_id ,wce.calendar_id
    from wa_contact_events wce
    where wce.is_status = 1
      and wce.contact_id = i:contact_id
      and wce.start between s:date1 and s:date2
      /*and wce.summary_type = 'custom'*/
    group by wce.calendar_id) last_wce on wcc.id = last_wce.calendar_id
         left join wa_contact_events wce on wce.id = last_wce.last_wce_id
where wce.is_status = 1
    and wce.contact_id = i:contact_id
    and wce.start between s:date1 and s:date2
order by wce.update_datetime DESC
limit 1
SQL;

        $result = stts()->getModel()->query(
            $sql,
            [
                'contact_id' => $contactId,
                'date1' => $date->format('Y-m-d 00:00:00'),
                'date2' => $date->format('Y-m-d 23:59:59'),
            ]
        )->fetchAll();

        $result = reset($result);
        $result = $result
            ? stts()->getHydrator()->hydrate(new statusTodayStatus(), $result)
            : new statusTodayStatus();

        stts()->getCache()->set($key, $result, 10);

        return $result;
    }

    /**
     * @return bool
     */
    private static function hasDefaultStatus()
    {
        try {
            stts()->getModel()->exec('SELECT default_status FROM wa_contact_calendars LIMIT 0');
            return true;
        } catch (Exception $e) {
            return false;
        }
    }
}