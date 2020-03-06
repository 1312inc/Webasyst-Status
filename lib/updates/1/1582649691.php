<?php

try {
    (new statusUtils())->fixProjectDurations();
} catch (waException $ex) {
    waLog::log($ex->getMessage(), 'status.log');
    waLog::log($ex->getTraceAsString(), 'status.log');
}
