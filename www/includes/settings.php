<?php
return (object) array(
/* Database connection settings */
'mysql_host'     => 'localhost',
'mysql_db'       => 'base_account_system',
'mysql_user'     => 'root',
'mysql_pass'     => '',
'mysql_charset'  => 'utf8mb4',

/* Session settings */
'session_timeout' => 60 * 60 * 24 * 30, // 30 days
);