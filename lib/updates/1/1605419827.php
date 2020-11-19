<?php

$m = new statusCheckinTraceModel();

$m->exec("delete from status_checkin_trace where date < '2020-11-13'");

// пока все оставляем как есть
//
//$userTimezone = wa()->getUser()->getTimezone(true);
//$serverTimezone = new DateTimeZone(waDateTime::getDefaultTimeZone());
//
//if ($userTimezone === $serverTimezone) {
//    return;
//}
//
//$traces = $m->query('select * from status_checkin_trace order by id')->fetchAll();
//foreach ($traces as $trace) {
//    $oldDate = new DateTimeImmutable($trace['date']);
//    $newDate = (new DateTime($trace['date']))->setTimezone($userTimezone)
//        ->setTime(0, 0);
//    $update = true;
//    $newTrace = $trace;
//    if ($newDate->format('Y-m-d') !== $trace['date']) {
//        $update = false;
//    }
//
//    $newTrace['date'] = $newDate->format('Y-m-d');
//
//    $newCreatedAtDate = (new DateTimeImmutable($trace['create_datetime']))->setTimezone($userTimezone);
//    $newTrace['create_datetime'] = $newCreatedAtDate->format('Y-m-d H:i:s');
//
//    $newUpdatedAtDate = (new DateTimeImmutable($trace['update_datetime']))->setTimezone($userTimezone);
//    $newTrace['update_datetime'] = $newUpdatedAtDate->format('Y-m-d H:i:s');
//
//    $newStartTime = $oldDate->modify("+{$trace['start_time']} minutes")->setTimezone($userTimezone);
//    $newEndTime = $oldDate->modify("+{$trace['end_time']} minutes")->setTimezone($userTimezone);
//
//    $tsNewDate = strtotime($newDate->format('Y-m-d'));
//    $newTrace['start_time'] = (int) ((strtotime($newStartTime->format('Y-m-d H:i:s')) - $tsNewDate) / 60);
//    $newTrace['end_time'] = (int) ((strtotime($newEndTime->format('Y-m-d H:i:s')) - $tsNewDate) / 60);
//
//    if ($newEndTime->format('Y-m-d') !== $newDate->format('Y-m-d')) {
//        // забьем на ситуацию, когда автоматический трейс попадает между датами
//        continue;
//    }
//
//    if (!$update) {
//        $m->deleteById($trace['id']);
//        $m->insert($newTrace);
//    } else {
//        $m->updateById($trace['id'], $newTrace);
//    }
//}
