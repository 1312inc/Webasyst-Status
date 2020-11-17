<?php

$m = new statusCheckinTraceModel();

try {
    $m->exec('truncate table status_checkin_trace');
} catch (waException $ex) {
    $m->exec('delete from status_checkin_trace');
}

