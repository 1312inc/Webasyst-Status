<?php

$m = new statusModel();

try {
    $m->exec('select * from status_checkin_trace');
} catch (waException $ex) {
    $m->exec(
        'create table status_checkin_trace
(
    id              int auto_increment
        primary key,
    contact_id      int           not null,
    date            date          not null,
    start_time      int default 0 null,
    end_time        int default 0 null,
    break_duration  int default 0 null,
    total_duration  int default 0 null,
    comment         text          null,
    timezone        tinyint       null,
    create_datetime datetime      null,
    update_datetime datetime      null
)
    engine = MyISAM;'
    );
}
