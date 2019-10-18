<?php

$m = new statusModel();

try {
    $m->exec('select * from status_user');
} catch (waException $ex) {
    $m->exec(
        'create table status_user
        (
            id int auto_increment,
            contact_id int not null,
            last_checkin_datetime datetime default null null,
            this_week_total_duration int default 0 null,
            constraint status_user_pk
                primary key (id)
        );
        create unique index status_user_contact_id_uindex
            on status_user (contact_id)'
    );
}

try {
    $m->exec('select * from status_project');
} catch (waException $ex) {
    $m->exec(
        'create table status_project
        (
            id int auto_increment,
            name varchar(255) not null,
            color varchar(20) null,
            created_datetime datetime not null,
            last_checkin_datetime datetime null,
            this_week_total_duration int default 0 null,
            is_archived tinyint default 0 null,
            created_by int not null,
            constraint status_project_pk
                primary key (id)
        )');
}

try {
    $m->exec('select * from status_checkin');
} catch (waException $ex) {
    $m->exec(
        'create table status_checkin
        (
            id int auto_increment,
            contact_id int not null,
            date date not null,
            start_time int default 0 null,
            end_time int default 0 null,
            break_duration int default 0 null,
            total_duration int default 0 null,
            comment text null,
            timezone tinyint null,
            constraint status_checkin_pk
                primary key (id),
            constraint contact_id
                unique (contact_id),
            constraint date
                unique (date)
        )');
}

try {
    $m->exec('select * from status_checkin_projects');
} catch (waException $ex) {
    $m->exec(
        'create table status_checkin_projects
        (
            id         int auto_increment
                primary key,
            checkin_id int           not null,
            project_id int           not null,
            duration   int default 0 not null
        )');
}
