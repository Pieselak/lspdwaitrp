<?php
include_once("server/config.php");
global $cfg_oauth;

header("Location: " . $cfg_oauth["init_url"]);
die();