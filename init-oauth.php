<?php
include_once("server/config.php");

header("Location: " . $cfg_oauth["init_url"]);
die();