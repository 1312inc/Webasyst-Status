<?php

$m = new statusModel();

try {
    $m->exec('select counter from status_checkin_trace');
} catch (waException $ex) {
    $m->exec('alter table status_checkin_trace add counter int default 0 null');
}
