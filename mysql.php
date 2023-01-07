<?php

require_once('private/installation.php');

$mysqli = new mysqli(DB_HOST, DB_USER, DB_PASSWORD, DB_DATABASE);
/* Check if the connection succeeded */
if (!is_null($mysqli->connect_error)) {
   die('MySQL connection failed');
}

// Debugging only, remove this!
mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);
ini_set('display_errors', 1); ini_set('display_startup_errors', 1); error_reporting(E_ALL);

?>