<?php

$m = new statusModel();

try {
    $m->exec('select update_datetime from status_project');
} catch (waException $ex) {
    $m->exec('alter table status_project add update_datetime datetime after create_datetime');
}
