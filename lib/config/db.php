<?php
return array(
    'status_checkin' => array(
        'id' => array('int', 11, 'null' => 0, 'autoincrement' => 1),
        'contact_id' => array('int', 11, 'null' => 0),
        'date' => array('date', 'null' => 0),
        'start_time' => array('int', 11, 'default' => '0'),
        'end_time' => array('int', 11, 'default' => '0'),
        'break_duration' => array('int', 11, 'default' => '0'),
        'total_duration' => array('int', 11, 'default' => '0'),
        'comment' => array('text'),
        'timezone' => array('tinyint', 4),
        'create_datetime' => array('datetime'),
        'update_datetime' => array('datetime'),
        ':keys' => array(
            'PRIMARY' => 'id',
        ),
    ),
    'status_checkin_projects' => array(
        'id' => array('int', 11, 'null' => 0, 'autoincrement' => 1),
        'checkin_id' => array('int', 11, 'null' => 0),
        'project_id' => array('int', 11, 'null' => 0),
        'duration' => array('int', 11, 'null' => 0, 'default' => '0'),
        ':keys' => array(
            'PRIMARY' => 'id',
        ),
    ),
    'status_project' => array(
        'id' => array('int', 11, 'null' => 0, 'autoincrement' => 1),
        'name' => array('varchar', 255, 'null' => 0),
        'color' => array('varchar', 20),
        'create_datetime' => array('datetime', 'null' => 0),
        'last_checkin_datetime' => array('datetime'),
        'this_week_total_duration' => array('int', 11, 'default' => '0'),
        'is_archived' => array('tinyint', 4, 'default' => '0'),
        'created_by' => array('int', 11, 'null' => 0),
        ':keys' => array(
            'PRIMARY' => 'id',
        ),
    ),
    'status_user' => array(
        'id' => array('int', 11, 'null' => 0, 'autoincrement' => 1),
        'contact_id' => array('int', 11, 'null' => 0),
        'last_checkin_datetime' => array('datetime'),
        'this_week_total_duration' => array('int', 11, 'default' => '0'),
        ':keys' => array(
            'PRIMARY' => 'id',
            'status_user_contact_id_uindex' => array('contact_id', 'unique' => 1),
        ),
    ),
);
