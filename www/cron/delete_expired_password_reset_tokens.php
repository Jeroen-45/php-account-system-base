<?php
/* Deletes all expired password reset tokens from the database.
 * Can be ran infrequently, for example every 24 hours */
require_once "../includes/database.php";


$db = new Database();
$db->deleteExpiredPasswordResetTokens();