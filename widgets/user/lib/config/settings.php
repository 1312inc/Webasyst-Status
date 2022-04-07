<?php
return [
    'user' => [
        'title' => /*_wp*/('User'),
        'control_type' => waHtmlControl::CUSTOM . ' ' . 'statusUserWidget::getUserFilterControl',
    ],
];
