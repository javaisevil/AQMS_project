<?php

define('DB_HOST', 'localhost');
define('DB_USER', 'root');
define('DB_PASS', 'root');
define('DB_NAME', 'AQMS_db');

$script_name = str_replace('\\', '/', $_SERVER['SCRIPT_NAME'] ?? '');
$project_dir = '/' . basename(__DIR__);
$project_pos = strpos($script_name, $project_dir . '/');
$base_url = $project_pos === false ? '' : substr($script_name, 0, $project_pos + strlen($project_dir));

define('BASE_URL', $base_url);
define('UNIVERSITY_NAME', 'Al Yamamah University');
