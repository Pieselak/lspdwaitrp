<?php
include_once("php/config.php");

header("Location: " . $cfg_oauth["init_url"]);
die();