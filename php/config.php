<?php
// Database
$cfg_database["hostname"] = "";
$cfg_database["username"] = "";
$cfg_database["password"] = "";
$cfg_database["database"] = "";

// Session
$cfg_session["timeout"] = 3600;

// Discord OAuth2
$cfg_oauth["token_url"] = "https://discord.com/api/v10/oauth2/token";
$cfg_oauth["init_url"] = "";
$cfg_oauth["client_id"] = "";
$cfg_oauth["client_secret"] = "";
$cfg_oauth["redirect_uri"] = "https://localhost/lspdwaitrp/process-oauth.php";
$cfg_oauth["scope"] = "identify email guilds";

// Discord API
$cfg_discord["api_url"] = "https://discord.com/api/v10";
$cfg_discord["token"] = "/oauth2/token";
$cfg_discord["user"] = "/users/";
$cfg_discord["user_me"] = "/users/@me";
$cfg_discord["guild"] = "/guilds/";
$cfg_discord["guilds_me"] = "/users/@me/guilds";
$cfg_discord["avatar"] = "https://cdn.discordapp.com/avatars/";
$cfg_discord["icon"] = "https://cdn.discordapp.com/icons/";
$cfg_discord["default_avatar"] = "https://cdn.discordapp.com/embed/avatars/0.png";

// Discord Bot
$cfg_discord["bot_token"] = "";
$cfg_discord["service_discord"] = "https://discord.gg/ZfaDufhDj9";

// Discord Guilds
// ["guild_id" => "server_invite, "guild_id" => "server_invite"...]
$cfg_discord["required_guilds"] = ["0" => "https://discord.gg/invite"];

// Curl
$cfg_curl["timeout"] = 10;
$cfg_curl["verify_ssl"] = false;
$cfg_curl["user_agent"] = "Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/58.0.3029.110 Safari/537.3";

// Permissions
$cfg_permissions = include("list-permissions.php");

// Formatting
$cfg_format["date"] = "d.m.Y";
$cfg_format["time"] = "H:i";
$cfg_format["time_full"] = "H:i:s";
$cfg_format["datetime"] = $cfg_format["date"] . " " . $cfg_format["time"];
$cfg_format["datetime_full"] = $cfg_format["date"] . " " . $cfg_format["time_full"];
$cfg_format["allowed_tags"] = "<h1><h2><h3><h4><h5><p><span><a><b><strong><i><em><u><br>";

// Error reporting
error_reporting(E_ALL);
ini_set("display_errors", 1);
ini_set("display_startup_errors", 1);

// Timezone
date_default_timezone_set("Europe/Warsaw");