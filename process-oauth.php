<?php   
include_once("server/functions.php");

if (!isset($_GET["code"])) {
    die ("No code provided");
}

$discord_code = $_GET["code"];

$auth_token = getDiscordAuthToken($discord_code);

if (!$auth_token["success"]) {
    die("Nie udało się uzyskać tokenu autoryzacyjnego (". $auth_token["message"] .")");
}

$user = getDiscordUser($auth_token["token"]);

if (!$user["success"]) {
    die("Nie udało się uzyskać informacji o użytkowniku (". $user["message"] .")");
}

$guilds = getDiscordGuilds($auth_token["token"]);

if (!$guilds["success"]) {
    die("Nie udało się uzyskać informacji o gildiach (". $guilds["message"] .")");
}

$login = loginUser($user["response"], $guilds["response"], $_SESSION["loginRedirect"] ?? null);
if (!$login["success"]) {
    die("Nie udało się zalogować użytkownika (". $login["message"] .")");
}