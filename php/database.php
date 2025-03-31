<?php
include_once ("config.php");

$conn = new mysqli($cfg_database["hostname"], $cfg_database["username"], $cfg_database["password"], $cfg_database["database"]);
if ($conn->connect_errno) {
    die ("Failed to connect to MySQL: " . $conn->connect_error);
}