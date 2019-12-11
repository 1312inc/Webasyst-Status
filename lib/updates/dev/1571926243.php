<?php

try {
    $m = new waModel();
    $m->exec('drop index contact_id on status_checkin');
    $m->exec('drop index date on status_checkin');
} catch (waException $ex) {
    kmwaWaLogger::error($ex->getMessage());
}
