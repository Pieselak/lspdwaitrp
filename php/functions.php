<?php 
include_once ("config.php");
include_once ("database.php");

if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

// Basic functions

function redirectTo($url) {
    header("Location: $url");
    exit();
}

function formatDate($date, $format = "datetime") {
    global $cfg_format;
    if (!isset($cfg_format[$format])) {
        $format = $cfg_format[0];
    }
    return date($cfg_format[$format], strtotime($date));
}

function getClientIP() {
    if (isset($_SERVER["HTTP_CLIENT_IP"])) {
        return $_SERVER["HTTP_CLIENT_IP"];
    } elseif (isset($_SERVER["HTTP_X_FORWARDED_FOR"])) {
        return $_SERVER["HTTP_X_FORWARDED_FOR"];
    } elseif (isset($_SERVER["HTTP_X_FORWARDED"])) {
        return $_SERVER["HTTP_X_FORWARDED"];
    } elseif (isset($_SERVER["HTTP_FORWARDED_FOR"])) {
        return $_SERVER["HTTP_FORWARDED_FOR"];
    } elseif (isset($_SERVER["HTTP_FORWARDED"])) {
        return $_SERVER["HTTP_FORWARDED"];
    } elseif (isset($_SERVER["REMOTE_ADDR"])) {
        return $_SERVER["REMOTE_ADDR"];
    } else {
        return "Nieznane";
    }
}

// Validation functions

function validate($input, $inputType = null) {
    global $cfg_format;
    $input = trim($input);
    $input = stripslashes($input);
    switch ($inputType) {
        case "string": 
            $input = filter_var($input, FILTER_VALIDATE_REGEXP, ["options" => ["regexp" => "/^[a-zA-Z0-9_\- ]+$/"]]);
            if (!$input) {
                return ["success" => false, "message" => "Niepoprawny format danych (A-Z, a-z, 0-9, _, -, spacja)"];
            }
            break;
        case "format-tags": 
            $input = strip_tags($input, $cfg_format["allowed_tags"]);
            break;
        case "int":
            $input = filter_var($input, FILTER_VALIDATE_INT);
            if (!$input) {
                return ["success" => false, "message" => "Niepoprawny format danych (tylko liczby całkowite)"];
            }
            break;
        case "float":
            $input = filter_var($input, FILTER_VALIDATE_FLOAT);
            if (!$input) {
                return ["success" => false, "message" => "Niepoprawny format danych (tylko liczby zmiennoprzecinkowe)"];
            }
            break;
        case "email":
            $input = filter_var($input, FILTER_VALIDATE_EMAIL);
            if (!$input) {
                return ["success" => false, "message" => "Niepoprawny format adresu email"];
            }
            break;
        case "url":
            $input = filter_var($input, FILTER_VALIDATE_URL);
            if (!$input) {
                return ["success" => false, "message" => "Niepoprawny format adresu URL"];
            }
            break;
        case "ip":
            $input = filter_var($input, FILTER_VALIDATE_IP);
            if (!$input) {
                return ["success" => false, "message" => "Niepoprawny format adresu IP"];
            }
            break;
        case "date":
            $input = date($cfg_format["datetime"], strtotime($input));
            if ($input == "0000-00-00 00:00") {
                return ["success" => false, "message" => "Niepoprawny format daty"];
            }
            break;
    }

    return ["success" => true, "message" => $input];
}

// Settings functions

function getSetting($setting) {
    global $conn;

    $stmt = $conn->prepare("SELECT value FROM settings WHERE setting = ?");
    $stmt->bind_param("s", $setting);
    $stmt->execute();
    if ($stmt->errno) {
        error_log("MySQL error: " . $stmt->error);
        return ["success" => false, "message" => "Wystąpił błąd podczas pobierania ustawień"];
    }
    $result = $stmt->get_result();
    $stmt->close();

    if ($result->num_rows == 0) {
        return ["success" => false, "message" => "Nie znaleziono ustawień"];
    }

    $result = $result->fetch_assoc();
    return ["success" => true, "message" => $result["value"]];
};

function setSetting($setting, $value) {
    global $conn;

    $stmt = $conn->prepare("UPDATE settings SET value = ? WHERE setting = ?");
    $stmt->bind_param("ss", $value, $setting);
    $stmt->execute();
    if ($stmt->errno) {
        error_log("MySQL error: " . $stmt->error);
        return ["success" => false, "message" => "Wystąpił błąd podczas aktualizacji ustawień"];
    }
    $stmt->close();

    return ["success" => true, "message" => "Ustawienia zostały zaktualizowane"];
};

// Maintenance functions

function getMaintenance() {
    return getSetting("maintenance_mode");
}

function getMaintenancePassword() {
    return getSetting("maintenance_password");
}

function checkMaintenance() {
    $status = getMaintenance();
    $password = getMaintenancePassword();
    $userPassword = $_SESSION["maintenancePassword"] ?? null;

    if ($status["success"] && $password["success"]) {
        if ($status["message"] == "1" && $password["message"] != $userPassword) {
            redirectTo("maintenance.php");
        }
    }
}

function setMaintenanceMode($mode) {
    switch ($mode) {
        case "1":
            $setting = setSetting("maintenance_mode", "1");
            break;
        case "0":
            $setting = setSetting("maintenance_mode", "0");
            break;
        default:
            return ["success" => false, "message" => "Niepoprawny tryb (0 - wyłączony, 1 - włączony)"];
    }

    return $setting;
}

function setMaintenancePassword($password) {
    return setSetting("maintenance_password", $password);
}

// Announcement functions

function getAnnouncement() {
    return getSetting('announcement');
}

function setAnnouncement($content) {
    return setSetting('announcement', $content);
}

// Discord API functions

function getDiscordAuthToken($code) {
    global $cfg_discord, $cfg_oauth, $cfg_curl;

    if (empty($code)) {
        return ["success" => false, "message" => "Brak kodu autoryzacyjnego", "token" => null];
    }

    $payload = [
        'code' => $code,
        'client_id' => $cfg_oauth["client_id"],
        'client_secret' => $cfg_oauth["client_secret"],
        'grant_type' => 'authorization_code',
        'redirect_uri' => $cfg_oauth["redirect_uri"],
        'scope' => $cfg_oauth["scope"],
    ];

    $curl = curl_init();
    curl_setopt_array($curl, [
        CURLOPT_URL => $cfg_discord["api_url"] . $cfg_discord["token"],
        CURLOPT_POST => true,
        CURLOPT_POSTFIELDS => http_build_query($payload),
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_HTTPHEADER => ['Content-Type: application/x-www-form-urlencoded'],
        CURLOPT_SSL_VERIFYPEER => $cfg_curl["verify_ssl"],
        CURLOPT_TIMEOUT => $cfg_curl["timeout"],
        CURLOPT_USERAGENT => $cfg_curl["user_agent"],
    ]);

    $response = curl_exec($curl);
    if (curl_error($curl)) {
        return ["success" => false, "message" => "Błąd cURL: " . curl_error($curl), "token" => null];
    }
    curl_close($curl);

    $response = json_decode($response, true);
    if (!isset($response["access_token"])) {
        if (isset($auth_data["error"])) {
            error_log("Failed to obtain authorization token (" . $auth_data["error"] . "): " . isset($auth_data["error_description"]) ? $auth_data["error_description"] : "-");
        }
        return ["success" => false, "message" => "Nie udało się uzyskać tokenu autoryzacji", "token" => null];
    }

    return ["success" => true, "message" => "Uzyskano token autoryzacji", "token" => $response["access_token"]];
}

function getDiscordUser($auth_token) {
    global $cfg_discord, $cfg_curl;

    $curl = curl_init();
    curl_setopt_array($curl, [
        CURLOPT_URL => $cfg_discord["api_url"] . $cfg_discord["user_me"],
        CURLOPT_POST => false,
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_HTTPHEADER => ['Authorization: Bearer ' . $auth_token, 'Content-Type: application/x-www-form-urlencoded'],
        CURLOPT_SSL_VERIFYPEER => $cfg_curl["verify_ssl"],
        CURLOPT_TIMEOUT => $cfg_curl["timeout"],
        CURLOPT_USERAGENT => $cfg_curl["user_agent"],
    ]);

    $response = curl_exec($curl);
    if (curl_error($curl)) {
        return ["success" => false, "message" => "Błąd cURL: " . curl_error($curl), "response" => null];
    }
    curl_close($curl);

    return ["success" => true, "message" => "Pobrano informacje o użytkowniku", "response" => json_decode($response, true)];
}

function getDiscordGuilds($auth_token) {
    global $cfg_discord, $cfg_curl;

    $curl = curl_init();
    curl_setopt_array($curl, [
        CURLOPT_URL => $cfg_discord["api_url"] . $cfg_discord["guilds_me"],
        CURLOPT_POST => false,
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_HTTPHEADER => ['Authorization: Bearer ' . $auth_token, 'Content-Type: application/x-www-form-urlencoded'],
        CURLOPT_SSL_VERIFYPEER => $cfg_curl["verify_ssl"],
        CURLOPT_TIMEOUT => $cfg_curl["timeout"],
        CURLOPT_USERAGENT => $cfg_curl["user_agent"],
    ]);

    $response = curl_exec($curl);
    if (curl_error($curl)) {
        return ["success" => false, "message" => "Błąd cURL: " . curl_error($curl), "response" => null];
    }
    curl_close($curl);

    return ["success" => true, "message" => "Pobrano informacje o gildiach", "response" => json_decode($response, true)];
}

function createDiscordUser($id) {
    global $cfg_discord, $cfg_curl;

    if (intval($id) == 0) {
        return ["success" => false, "message" => "Niepoprawne ID użytkownika", "user" => null];
    }

    $curl = curl_init();
    curl_setopt_array($curl, [
        CURLOPT_URL => $cfg_discord["api_url"] . $cfg_discord["user"] . $id,
        CURLOPT_POST => false,
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_HTTPHEADER => ['Authorization: Bot '. $cfg_discord["bot_token"], 'Content-Type: application/x-www-form-urlencoded'],
        CURLOPT_SSL_VERIFYPEER => $cfg_curl["verify_ssl"],
        CURLOPT_TIMEOUT => $cfg_curl["timeout"],
        CURLOPT_USERAGENT => $cfg_curl["user_agent"],
    ]);

    $response = curl_exec($curl);
    if (curl_error($curl)) {
        return ["success" => false, "message" => "Błąd cURL: " . curl_error($curl), "user" => null];
    }
    curl_close($curl);
    $response = json_decode($response, true);

    $params = [
        'id' => $response["id"] ?? null,
        'username' => $response["username"] ?? null,
        'displayname' => $response["username"] ?? null,
        'avatar' => $response["avatar"] ?? null,
        'email' => $response["email"] ?? null,
    ];

    return createUser($params);
}

// User functions

function getUser($id) {
    global $conn, $cfg_discord;

    $stmt = $conn->prepare("SELECT * FROM users u LEFT JOIN roles r ON u.role_id = r.role_id WHERE id = ?");
    $stmt->bind_param("s", $id);
    $stmt->execute();
    if ($stmt->errno) {
        error_log("MySQL error: " . $stmt->error);
        return ["success" => false, "message" => "Wystąpił błąd podczas pobierania użytkownika", "user" => null];
    }
    $result = $stmt->get_result();
    $stmt->close();

    if ($result->num_rows == 0) {
        return ["success" => true, "message" => "Użytkownik o ID $id nie istnieje", "user" => null];
    } else {
        $result = $result->fetch_assoc();

        $user = [
            'id' => $result["id"] ?? null,
            'username' => $result["username"] ?? "Nieznane",
            'displayname' => $result["displayname"] ?? "Nieznane",
            'avatar' => $cfg_discord["avatar"] . $result["id"] . "/" . $result["avatar"] ?? $cfg_discord["default_avatar"],
            'email' => $result["email"] ?? "Nieznane",
            'created_at' => $result["created_at"] ?? "0000-00-00 00:00:00",
            'last_update' => $result["last_update"] ?? "0000-00-00 00:00:00",
            'last_login' => getLastLogin($id)["login"] ?? "0000-00-00 00:00:00",
            'role_id' => $result["role_id"] ?? 0,
            'role_name' => $result["role_name"] ?? "Brak przypisanej roli",
        ];

        return ["success" => true, "message" => "Użytkownik o ID $id został pobrany", "user" => $user];
    }
}

function getAllUsers($search = null) {
    global $conn;

    if ($search) {
        $searchTerm = "%" . $search . "%";
        $stmt = $conn->prepare("SELECT u.id FROM users u LEFT JOIN roles r ON u.role_id = r.role_id WHERE u.id LIKE ? or u.username LIKE ? or u.displayname LIKE ? or r.role_name LIKE ?");
        $stmt->bind_param("ssss", $search, $searchTerm, $searchTerm, $search);
    } else {
        $stmt = $conn->prepare("SELECT id FROM users");
    }
    $stmt->execute();
    if ($stmt->errno) {
        error_log("MySQL error: " . $stmt->error);
        return ["success" => false, "message" => "Wystąpił błąd podczas pobierania użytkowników", "users" => null];
    }
    $result = $stmt->get_result();
    $stmt->close();

    $users = [];

    while ($row = $result->fetch_assoc()) {
        $user = getUser($row["id"]);
        if ($user["success"] && $user["user"]) {
            $users[] = $user["user"];
        }
    }

    return ["success" => true, "message" => "Pobrano wszystkich użytkowników", "users" => $users];
}

function updateUser($id, $params) {
    global $conn;

    $user = getUser($id);
    if (!$user["success"]) {
        return ["success" => false, "message" => $user["message"]];
    }
    elseif ($user["success"] && !$user["user"]) {
        return ["success" => false, "message" => "Użytkownik o ID $id nie istnieje"];
    }

    $user = $user["user"];
    $params = [
        'username' => $params["username"] ?? $user["username"],
        'displayname' => $params["displayname"] ?? $user["displayname"],
        'avatar' => $params["avatar"] ?? $user["avatar"],
        'email' => $params["email"] ?? $user["email"],
        'role_id' => $params["role_id"] ?? $user["role_id"],
    ];

    if ($params["email"] == "reset") {
        $params["email"] = null;
    }

    $stmt = $conn->prepare("UPDATE users SET username = ?, displayname = ?, avatar = ?, email = ?, role_id = ?, last_update = NOW() WHERE id = ?");
    $stmt->bind_param("ssssis", $params["username"], $params["displayname"], $params["avatar"], $params["email"], $params["role_id"], $id);
    $stmt->execute();
    if ($stmt->errno) {
        error_log("MySQL error: " . $stmt->error);
        return ["success" => false, "message" => "Wystąpił błąd podczas aktualizacji użytkownika"];
    }
    $stmt->close();

    return ["success" => true, "message" => "Użytkownik o ID $id został zaktualizowany"];
}

function createUser($params) {
    global $conn;

    $params = [
        'id' => $params["id"] ?? null,
        'username' => $params["username"] ?? null,
        'displayname' => $params["displayname"] ?? null,
        'avatar' => $params["avatar"] ?? null,
        'email' => $params["email"] ?? null,
    ];

    if (!$params["id"]) {
        return ["success" => false, "message" => "Nie podano ID użytkownika"];
    }

    $getUser = getUser($params["id"]);
    if ($getUser["success"] && $getUser["user"]) {
        return ["success" => false, "message" => "Użytkownik o ID " . $params["id"] . " już istnieje"];
    }

    $stmt = $conn->prepare("INSERT INTO users (id, username, displayname, avatar, email, created_at, last_update) VALUES (?, ?, ?, ?, ?, NOW(), NOW())");
    $stmt->bind_param("sssss", $params["id"], $params["username"], $params["displayname"], $params["avatar"], $params["email"]);
    $stmt->execute();
    if ($stmt->errno) {
        error_log("MySQL error: " . $stmt->error);
        return ["success" => false, "message" => "Wystąpił błąd podczas dodawania użytkownika do bazy danych"];
    }
    $stmt->close();

    return ["success" => true, "message" => "Użytkownik o ID " . $params["id"] . " został dodany"];
}

function deleteUser($id) {
    global $conn;

    $getUser = getUser($id);
    if (!$getUser["success"]) {
        return ["success" => false, "message" => $getUser["message"]];
    } elseif ($getUser["success"] && !$getUser["user"]) {
        return ["success" => false, "message" => "Użytkownik o ID $id nie istnieje"];
    }

    $stmt = $conn->prepare("DELETE FROM users WHERE id = ?");
    $stmt->bind_param("s", $id);
    $stmt->execute();
    $stmt->close();

    return ["success" => true, "message" => "Użytkownik o ID $id został usunięty"];
}

function refreshUser() {
    global $cfg_discord;

    if (!isUserLogged()) {
        return ["success" => false, "message" => "Użytkownik nie jest zalogowany"];
    }

    $user = $_SESSION["user"];
    $getUser = getUser($user["id"]);

    if (!$getUser["success"]) {
        return ["success" => false, "message" => $getUser["message"]];
    } elseif ($getUser["success"] && !$getUser["user"]) {
        return ["success" => false, "message" => "Użytkownik o ID " . $getUser["id"] . " nie istnieje"];
    }

    $getUser = $getUser["user"];
    $user = [
        'id' => $getUser["id"] ?? null,
        'username' => $getUser["username"] ?? "Nieznane",
        'displayname' => $getUser["displayname"] ?? "Nieznane",
        'avatar' => $getUser["avatar"] ?? $cfg_discord["default_avatar"],
        'email' => $getUser["email"] ?? "Nieznane",
        'created_at' => $getUser["created_at"] ?? "0000-00-00 00:00:00",
        'last_login' => $getUser["last_login"] ?? "0000-00-00 00:00:00",
        'last_update' => $getUser["last_update"] ?? "0000-00-00 00:00:00",
        'role_id' => $getUser["role_id"] ?? 0,
        'role_name' => $getUser["role_name"] ?? "Brak roli",
    ];
    $_SESSION["user"] = $user;

    return ["success" => true, "message" => "Zaktualizowano dane użytkownika"];
};

// Login functions

function checkUserLogin() {
    global $conn;

    if (!isset($_SESSION["user"])) {
        return ["success" => false, "message" => "Nie znaleziono sesji użytkownika"];
    }

    $sessionId = session_id();
    $clientIp = getClientIP();

    $stmt = $conn->prepare("SELECT * FROM users_logins WHERE login_session = ? AND login_ip = ?");
    $stmt->bind_param("ss", $sessionId, $clientIp);
    $stmt->execute();
    if ($stmt->errno) {
        error_log("MySQL error: " . $stmt->error);
        return ["success" => false, "message" => "Wystąpił błąd podczas sprawdzania sesji użytkownika"];
    }
    $result = $stmt->get_result();
    $stmt->close();

    if ($result->num_rows == 0) {
        return ["success" => false, "message" => "Nie znaleziono sesji użytkownika"];
    }

    $row = $result->fetch_assoc();
    if ($row["login_session_active"] == false) {
        return ["success" => false, "message" => "Sesja użytkownika została dezaktywowana"];
    }

    return ["success" => true, "message" => "Znaleziono sesję użytkownika"];
}

function addLogin($id) {
    global $conn;

    $sessionId = session_id();
    $clientIp = getClientIP();
    
    $stmt = $conn->prepare("INSERT INTO users_logins (user_id, login_session, login_ip, login_date) VALUES (?, ?, ?, NOW())");
    $stmt->bind_param("sss", $id, $sessionId, $clientIp);
    $stmt->execute();
    if ($stmt->errno) {
        error_log("MySQL error: " . $stmt->error);
        return ["success" => false, "message" => "Wystąpił błąd podczas dodawania logowania do bazy danych"];
    }
    $stmt->close();

    return ["success" => true, "message" => "Dodano logowanie użytkownika"];
}

function getLastLogin($id) {
    global $conn;

    $stmt = $conn->prepare("SELECT login_date FROM users_logins WHERE user_id = ? ORDER BY login_date DESC LIMIT 1");
    $stmt->bind_param("s", $id);
    $stmt->execute();
    if ($stmt->errno) {
        error_log("MySQL error: " . $stmt->error);
        return ["success" => false, "message" => "Wystąpił błąd podczas pobierania ostatniego logowania użytkownika", "login" => null];
    }
    $result = $stmt->get_result();
    $stmt->close();

    if ($result->num_rows == 0) {
        return ["success" => true, "message" => "Użytkownik o ID $id nie zalogował się jeszcze", "login" => null];
    }

    $row = $result->fetch_assoc();
    return ["success" => true, "message" => "Pobrano ostatnie logowanie użytkownika", "login" => $row["login_date"]];
}

function getLogins($userid) {
    global $conn;

    $stmt = $conn->prepare("SELECT * FROM users_logins WHERE user_id = ? ORDER BY login_date DESC");
    $stmt->bind_param("s", $userid);
    $stmt->execute();
    if ($stmt->errno) {
        error_log("MySQL error: " . $stmt->error);
        return ["success" => false, "message" => "Wystąpił błąd podczas pobierania logowań użytkownika", "logins" => null];
    }
    $result = $stmt->get_result();
    $stmt->close();

    $logins = [];

    while ($row = $result->fetch_assoc()) {
        $logins[] = [
            'id' => $row["id"],
            'user_id' => $row["user_id"],
            'login_session' => $row["login_session"],
            'login_session_active' => $row["login_session_active"],
            'login_ip' => $row["login_ip"],
            'login_date' => $row["login_date"],
        ];
    }

    return ["success" => true, "message" => "Pobrano logowania użytkownika", "logins" => $logins];
}

function logoutUser() 
{
    unset($_SESSION["user"]);
    unset($_SESSION["guilds"]);

    session_regenerate_id();
    redirectTo("login.php");
}

function loginUser($user, $guilds, $redirect = null) {
    global $cfg_discord;

    if ($redirect == "" || $redirect == null) {
        $redirect = "index.php";
    }

    $getUser = getUser($user["id"]);
    if (!$getUser["success"]) {
        return ["success" => false, "message" => $getUser["message"]];
    } elseif ($getUser["success"] && !$getUser["user"]) {
        createUser([
            'id' => $user["id"],
            'username' => $user["username"],
            'displayname' => $user["global_name"],
            'avatar' => $user["avatar"],
            'email' => $user["email"],
        ]);
    } elseif ($getUser["success"] && $getUser["user"]) {
        updateUser($user["id"], [
            'username' => $user["username"],
            'displayname' => $user["global_name"],
            'avatar' => $user["avatar"],
            'email' => $user["email"],
        ]);
    }
    
    $getUser = getUser($user["id"]);
    if (!$getUser["success"]) {
        return ["success" => false, "message" => $getUser["message"]];
    } elseif ($getUser["success"] && !$getUser["user"]) {
        return ["success" => false, "message" => "Użytkownik o ID " . $getUser["id"] . " nie istnieje"];
    }
    $getUser = $getUser["user"];

    $user = [
        'id' => $getUser["id"] ?? null,
        'username' => $getUser["username"] ?? "Nieznane",
        'displayname' => $getUser["displayname"] ?? "Nieznane",
        'avatar' => $getUser["avatar"] ?? $cfg_discord["default_avatar"],
        'email' => $getUser["email"] ?? "Nieznane",
        'created_at' => $getUser["created_at"] ?? "0000-00-00 00:00:00",
        'last_login' => getLastLogin($getUser["id"])["login"] ?? "0000-00-00 00:00:00",
        'last_update' => $getUser["last_update"] ?? "0000-00-00 00:00:00",
        'role_id' => $getUser["role_id"] ?? 0,
        'role_name' => $getUser["role_name"] ?? "Brak roli",
    ];

    session_regenerate_id();
    addLogin($user["id"]);
    $_SESSION["user"] = $user;
    $_SESSION["guilds"] = $guilds;

    redirectTo($redirect);
    $_SESSION["loginRedirect"] = null;
    return ["success" => true, "message" => "Zalogowano użytkownika"];
}

// User Validation functions

function isUserInGuild($guilds, $guild_id) {
    foreach ($guilds as $guild) {
        if ($guild["id"] == $guild_id) {
            return true;
        }
    }
    return false;
}

function isUserLogged() {
    return isset($_SESSION["user"]);
}

function validateUserBasic($redirect = null) {
    $refresh = refreshUser();
    if (!$refresh["success"]) {
        $_SESSION["loginRedirect"] = $redirect;
        $_SESSION["loginError"] = $refresh["message"];
        redirectTo("logout.php");
    }

    $check = checkUserLogin();
    if (!$check["success"]) {
        $_SESSION["loginRedirect"] = $redirect;
        $_SESSION["loginError"] = $check["message"];
        redirectTo("logout.php");
    }

    $user = $_SESSION["user"];
    $requiredFields = ["id", "username", "displayname", "avatar", "email", "created_at", "last_login", "last_update", "role_id", "role_name"];

    foreach ($requiredFields as $field) {
        if (!isset($user[$field])) {
            $_SESSION["loginError"] = "Niepoprawne dane użytkownika";
            redirectTo("logout.php");
        }
    }

    unset($_SESSION["accessError"]);
    unset($_SESSION["loginError"]);
    unset($_SESSION["loginRedirect"]);
    return $_SESSION["user"];
}

function validateUser($redirect = null) {
    $user = validateUserBasic($redirect);

    if ($user["role_id"] == 0) {
        $_SESSION["accessError"] = "Posiadasz niepoprawną rolę użytkownika<br><a href='https://discord.gg/ZfaDufhDj9'>Skontaktuj się z administratorem</a>";
        redirectTo("noaccess.php");
    }

    $suspensions = getUserSuspensions($user["id"]);
    if ($suspensions["success"] && $suspensions["suspensions"]) {
        foreach ($suspensions["suspensions"] as $suspension) {
            if ($suspension["statusId"] == 1) {
                redirectTo("suspended.php");
            }
        }
    }

    $warnings = getUserWarnings($user["id"]);
    if ($warnings["success"] && $warnings["warnings"]) {
        foreach ($warnings["warnings"] as $suspension) {
            if ($suspension["statusId"] == 1 && $suspension["isAccepted"] == 0) {
                redirectTo("warning.php");
            }
        }
    }

    return $_SESSION["user"];
}

// Permissions Validation functions

function checkUserPageAccess($permission, $checkLogin = true) {
    global $cfg_permissions;

    if ($checkLogin && !isUserLogged()) {
        return false;
    }

    $user = $_SESSION["user"] ?? null;

    if (isset($cfg_permissions[$user["role_id"]]) && array_key_exists($permission, $cfg_permissions[$user["role_id"]])) {
        return true;
    }

    return false;
}

function checkUserPermission($category, $permission) {
    global $cfg_permissions;

    $user = $_SESSION["user"] ?? null;
    if (checkUserPageAccess($category)) {
        if (in_array($permission, $cfg_permissions[$user["role_id"]][$category])) {
            return true;
        }
    }

    return false;
}

function validateUserAccess($permission, $redirectLogin = null, $redirectNoAccess = true) {
    $user = validateUser($redirectLogin);

    if (!checkUserPageAccess($permission, false)) {
        $_SESSION["accessError"] = "Nie posiadasz odpowiednich uprawnień do wyświetlenia tej strony!";
        if ($redirectNoAccess) {
            redirectTo("noaccess.php");
        } else {
            return ["access" => false, "user" => $user];
        }
    }

    return ["access" => true, "user" => $user];
}

// Suspensions functions

function issueSuspension($userId, $params) {
    global $conn;

    $params = [
        'issuerId' => $params["issuerId"] ?? null,
        'reason' => $params["reason"] ?? null,
        'expiresAt' => $params["expiresAt"] ?? "0000-00-00 00:00:00",
        'isPermanent' => $params["isPermanent"] ?? true,
    ];

    $stmt = $conn->prepare("INSERT INTO suspensions(user_id, issuer_id, reason, is_permanent, expires_at) VALUES (?,?,?,?,?)");
    $stmt->bind_param("sssis", $userId, $params["issuerId"], $params["reason"], $params["isPermanent"], $params["expiresAt"]);
    $stmt->execute();
    if ($stmt->errno) {
        error_log("MySQL error: " . $stmt->error);
        return ["success" => false, "message" => "Wystąpił błąd podczas dodawania zawieszenia"];
    }
    $stmt->close();

    return ["success" => true, "message" => "Użytkownik o ID $userId został zawieszony"];
}

function updateSuspension($suspensionId, $params) {
    global $conn;

    $suspension = getSuspension($suspensionId);
    if ($suspension["success"] == false) {
        return ["success" => false, "message" => $suspension["message"]];
    } else if ($suspension["success"] && !$suspension["suspension"]) {
        return ["success" => false, "message" => "Zawieszenie o ID $suspensionId nie istnieje"];
    }

    $suspension = $suspension["suspension"];
    $params = [
        'statusId' => $params["statusId"] ?? $suspension["status_id"],
        'issuerId' => $params["issuerId"] ?? $suspension["issuer_id"],
        'revokerId' => $params["revokerId"] ?? $suspension["revoker_id"],
        'reason' => $params["reason"] ?? $suspension["reason"],
        'isPermanent' => $params["isPermanent"] ?? $suspension["is_permanent"],
        'expiresAt' => $params["expiresAt"] ?? $suspension["expires_at"],
        'issuedAt' => $params["issuedAt"] ?? $suspension["issued_at"],    
        'revokedAt' => $params["revokedAt"] ?? $suspension["revoked_at"],
    ];

    $stmt = $conn->prepare("UPDATE suspensions SET status_id = ?, issuer_id = ?, revoker_id = ?, reason = ?, is_permanent = ?, expires_at = ?, issued_at = ?, revoked_at = ? WHERE suspension_id = ?");
    $stmt->bind_param("isssisssi", $params["statusId"], $params["issuerId"], $params["revokerId"], $params["reason"], $params["isPermanent"], $params["expiresAt"], $params["issuedAt"], $params["revokedAt"], $suspensionId);
    $stmt->execute();
    if ($stmt->errno) {
        error_log("MySQL error: " . $stmt->error);
        return ["success" => false, "message" => "Wystąpił błąd podczas aktualizacji zawieszenia"];
    }
    $stmt->close();

    return ["success" => true, "message" => "Zawieszenie o ID $suspensionId zostało zaktualizowane"];
}

function revokeSuspension($suspensionId, $revokerId) {
    $suspension = getSuspension($suspensionId);
    if ($suspension["success"] == false) {
        return ["success" => false, "message" => $suspension["message"]];
    } else if ($suspension["success"] && !$suspension["suspension"]) {
        return ["success" => false, "message" => "Zawieszenie o ID $suspensionId nie istnieje"];
    } else if ($suspension["success"] && $suspension["suspension"]["status_id"] == 2) {
        return ["success" => false, "message" => "Zawieszenie o ID $suspensionId jest już unieważnione"];
    }

    return updateSuspension($suspensionId, [
        'statusId' => 2,
        'revokerId' => $revokerId,
        'revokedAt' => date('Y-m-d H:i:s'),
    ]);
}

function deleteSuspension($suspensionId) {
    global $conn;

    $stmt = $conn->prepare("DELETE FROM suspensions WHERE suspension_id = ?");
    $stmt->bind_param("s", $suspensionId);
    $stmt->execute();
    if ($stmt->errno) {
        error_log("MySQL error: " . $stmt->error);
        return ["success" => false, "message" => "Wystąpił błąd podczas usuwania zawieszenia"];
    }
    $stmt->close();

    return ["success" => true, "message" => "Zawieszenie o ID $suspensionId zostało usunięte"];
}

function getSuspension($suspensionId, $checkIfExpired = true) {
    global $conn;

    $stmt = $conn->prepare("SELECT s.suspension_id, s.status_id, st.status status_content, st.color status_color, s.user_id, u.username user_username, u.displayname user_displayname, s.issuer_id, i.username issuer_username, i.displayname issuer_displayname, s.revoker_id, r.username revoker_username, r.displayname revoker_displayname, s.reason, s.is_permanent, s.expires_at, s.issued_at, s.revoked_at FROM suspensions s LEFT JOIN punishment_status st ON s.status_id = st.status_id LEFT JOIN users u ON s.user_id = u.id LEFT JOIN users i ON s.issuer_id = i.id LEFT JOIN users r ON s.revoker_id = r.id WHERE s.suspension_id = ?");
    $stmt->bind_param("s", $suspensionId);
    $stmt->execute();
    if ($stmt->errno) {
        error_log("MySQL error: " . $stmt->error);
        return ["success" => false, "message" => "Wystąpił błąd podczas pobierania informacji o zawieszeniu", "suspension" => null];
    }
    $result = $stmt->get_result();
    $stmt->close();

    if ($result->num_rows == 0) {
        return ["success" => true, "message" => "Nie znaleziono zawieszenia o ID $suspensionId", "suspension" => null];
    } else {
        $result = $result->fetch_assoc();
        $suspension = [
            'suspensionId' => $result["suspension_id"] ?? null,
            'statusId' => $result["status_id"] ?? 1,
            'statusContent' => $result["status_content"] ?? "Nieznane",
            'statusColor' => $result["status_color"] ?? "gray",
            'userId' => $result["user_id"] ?? "Nieznane",
            'userUsername' => $result["user_username"] ?? "Nieznane",
            'userDisplayname' => $result["user_displayname"] ?? "Nieznane",
            'issuerId' => $result["issuer_id"] ?? "Nieznane",
            'issuerUsername' => $result["issuer_username"] ?? "Nieznane",
            'issuerDisplayname' => $result["issuer_displayname"] ?? "Nieznane",
            'revokerId' => $result["revoker_id"] ?? "Nieznane",
            'revokerUsername' => $result["revoker_username"] ?? "Nieznane",
            'revokerDisplayname' => $result["revoker_displayname"] ?? "Nieznane",
            'reason' => $result["reason"] ?? "Nieznane",
            'isPermanent' => $result["is_permanent"] ?? true,
            'expiresAt' => $result["expires_at"] ?? "0000-00-00 00:00:00",
            'issuedAt' => $result["issued_at"] ?? "0000-00-00 00:00:00",
            'revokedAt' => $result["revoked_at"] ?? "0000-00-00 00:00:00",
        ];

        $todayDate = new DateTime();
        $expireDate = new DateTime($suspension["expiresAt"]);

        if ($checkIfExpired && $suspension["statusId"] == 1 && !$suspension["isPermanent"] && $expireDate < $todayDate) {
            $update = updateSuspension($suspensionId, ["statusId" => 3]);
            if (!$update["success"]) {
                return ["success" => false, "message" => $update["message"], "suspension" => null];
            }

            $suspension = getSuspension($suspensionId);
            if (!$suspension["success"]) {
                return ["success" => false, "message" => $suspension["message"], "suspension" => null];
            }
            return ["success" => true, "message" => "Zawieszenie o ID $suspensionId wygasło", "suspension" => $suspension["suspension"]];
        }
    }

    return ["success" => true, "message" => "Pobrano informacje o zawieszeniu o ID $suspensionId", "suspension" => $suspension];
}

function getUserSuspensions($userId) {
    global $conn;

    $stmt = $conn->prepare("SELECT s.suspension_id FROM suspensions s WHERE s.user_id = ?");
    $stmt->bind_param("s", $userId);
    $stmt->execute();
    if ($stmt->errno) {
        error_log("MySQL error: " . $stmt->error);
        return ["success" => false, "message" => "Wystąpił błąd podczas pobierania zawieszeń użytkownika", "suspensions" => null];
    }
    $result = $stmt->get_result();
    $stmt->close();

    $suspensions = [];
    while ($row = $result->fetch_assoc()) {
        $suspension = getSuspension($row["suspension_id"]);
        if ($suspension["success"] && $suspension["suspension"]) {
            array_push($suspensions, $suspension["suspension"]);
        }
    }

    return ["success" => true, "message" => "Pobrano zawieszenia użytkownika o ID $userId", "suspensions" => $suspensions];
}

function getAllSuspensions($search = null) {
    global $conn;

    if ($search) {
        $searchString = "%" . $search . "%";
        $stmt = $conn->prepare("SELECT s.suspension_id FROM suspensions s LEFT JOIN users u ON s.user_id = u.id WHERE s.suspension_id LIKE ? or s.user_id LIKE ? or u.username LIKE ? or u.displayname LIKE ? or s.reason LIKE ?");
        $stmt->bind_param("sssss", $search, $search, $searchString, $searchString, $searchString);
    } else {
        $stmt = $conn->prepare("SELECT s.suspension_id FROM suspensions s");
    }
    $stmt->execute();
    if ($stmt->errno) {
        error_log("MySQL error: " . $stmt->error);
        return ["success" => false, "message" => "Wystąpił błąd podczas pobierania zawieszeń", "suspensions" => null];
    }
    $result = $stmt->get_result();

    $suspensions = [];
    while ($row = $result->fetch_assoc()) {
        $suspension = getSuspension($row["suspension_id"]);
        if ($suspension["success"] && $suspension["suspension"]) {
            $suspensions[] = $suspension["suspension"];
        }
    }

    return ["success" => true, "message" => "Pobrano wszystkie zawieszenia", "suspensions" => $suspensions];
}

// Warnings functions

function issueWarning($userId, $params) {
    global $conn;

    $params = [
        'issuerId' => $params["issuerId"] ?? null,
        'reason' => $params["reason"] ?? null,
        'expiresAt' => $params["expiresAt"] ?? "0000-00-00 00:00:00",
        'isPermanent' => $params["isPermanent"] ?? true,
    ];

    $stmt = $conn->prepare("INSERT INTO warnings (user_id, issuer_id, reason) VALUES (?, ?, ?)");
    $stmt->bind_param("sssis", $userId, $params["issuerId"], $params["reason"]);
    $stmt->execute();
    if ($stmt->errno) {
        error_log("MySQL error: " . $stmt->error);
        return ["success" => false, "message" => "Wystąpił błąd podczas dodawania ostrzeżenia"];
    }
    $stmt->close();

    return ["success" => true, "message" => "Użytkownik o ID $userId został ostrzeżony"];
}

function acceptWarning($warningId) {
    $warning = getWarning($warningId);

    if (!$warning["success"]) {
        return ["success" => false, "message" => $warning["message"]];
    }
    if ($warning["warning"] == null) {
        return ["success" => false, "message" => "Ostrzeżenie o ID $warningId nie istnieje"];
    }
    if ($warning["warning"]["isAccepted"] == true) {
        return ["success" => false, "message" => "Ostrzeżenie o ID $warningId zostało już zaakceptowane"];
    }
    if ($warning["warning"]["statusId"] == 2) {
        return ["success" => false, "message" => "Ostrzeżenie o ID $warningId zostało unieważnione"];
    }
    if (isset($_SESSION["user"]["id"]) && $warning["warning"]["userId"] != $_SESSION["user"]["id"]) {
        return ["success" => false, "message" => "Nie masz uprawnień do zaakceptowania ostrzeżenia o ID $warningId"];
    }

    return updateWarning($warningId, ["isAccepted" => true]);
}

function updateWarning($warningId, $params) {
    global $conn;

    $warning = getWarning($warningId);
    if ($warning["success"] == false) {
        return ["success" => false, "message" => $warning["message"]];
    } else if ($warning["success"] && !$warning["warning"]) {
        return ["success" => false, "message" => "Ostrzeżenie o ID $warningId nie istnieje"];
    }

    $warning = $warning["warning"];
    $params = [
        'statusId' => $params["statusId"] ?? $warning["status_id"],
        'issuerId' => $params["issuerId"] ?? $warning["issuer_id"],
        'revokerId' => $params["revokerId"] ?? $warning["revoker_id"],
        'reason' => $params["reason"] ?? $warning["reason"],
        'isAccepted' => $params["isAccepted"] ?? $warning["is_accepted"],
        'issuedAt' => $params["issuedAt"] ?? $warning["issued_at"],    
        'revokedAt' => $params["revokedAt"] ?? $warning["revoked_at"],
    ];

    $stmt = $conn->prepare("UPDATE warnings SET status_id = ?, issuer_id = ?, revoker_id = ?, reason = ?, is_accepted = ?, issued_at = ?, revoked_at = ? WHERE warning_id = ?");
    $stmt->bind_param("isssiiss", $params["statusId"], $params["issuerId"], $params["revokerId"], $params["reason"], $params["isAccepted"], $params["issuedAt"], $params["revokedAt"], $warningId);
    $stmt->execute();
    if ($stmt->errno) {
        error_log("MySQL error: " . $stmt->error);
        return ["success" => false, "message" => "Wystąpił błąd podczas aktualizacji ostrzeżenia"];
    }
    $stmt->close();

    return ["success" => true, "message" => "Ostrzeżenie o ID $warningId zostało zaktualizowane"];
}

function revokeWarning($warningId, $revokerId) {
    $warning = getWarning($warningId);
    if ($warning["success"] == false) {
        return ["success" => false, "message" => $warning["message"]];
    } else if ($warning["success"] && !$warning["warning"]) {
        return ["success" => false, "message" => "Ostrzeżenie o ID $warningId nie istnieje"];
    } else if ($warning["success"] && $warning["warning"]["status_id"] == 2) {
        return ["success" => false, "message" => "Ostrzeżenie o ID $warningId jest już unieważnione"];
    }

    return updateWarning($warningId, [
        'statusId' => 2,
        'revokerId' => $revokerId,
        'revokedAt' => date('Y-m-d H:i:s'),
    ]);
}

function deleteWarning($warningId) {
    global $conn;

    $stmt = $conn->prepare("DELETE FROM warnings WHERE warning_id = ?");
    $stmt->bind_param("s", $warningId);
    $stmt->execute();
    if ($stmt->errno) {
        error_log("MySQL error: " . $stmt->error);
        return ["success" => false, "message" => "Wystąpił błąd podczas usuwania ostrzeżenia"];
    }
    $stmt->close();

    return ["success" => true, "message" => "Ostrzeżenie o ID $warningId zostało usunięte"];
}

function getWarning($warningId) {
    global $conn;

    $stmt = $conn->prepare("SELECT w.warning_id, w.status_id, st.status status_content, st.color status_color, w.user_id, u.username user_username, u.displayname user_displayname, w.issuer_id, i.username issuer_username, i.displayname issuer_displayname, w.revoker_id, r.username revoker_username, r.displayname revoker_displayname, w.reason, w.is_accepted, w.issued_at, w.revoked_at FROM warnings w LEFT JOIN punishment_status st ON w.status_id = st.status_id LEFT JOIN users u ON w.user_id = u.id LEFT JOIN users i ON w.issuer_id = i.id LEFT JOIN users r ON w.revoker_id = r.id WHERE w.warning_id = ?");
    $stmt->bind_param("s", $warningId);
    $stmt->execute();
    if ($stmt->errno) {
        error_log("MySQL error: " . $stmt->error);
        return ["success" => false, "message" => "Wystąpił błąd podczas pobierania informacji o ostrzeżeniu", "warning" => null];
    }
    $result = $stmt->get_result();
    $stmt->close();

    if ($result->num_rows == 0) {
        return ["success" => true, "message" => "Nie znaleziono ostrzeżenia o ID $warningId", "warning" => null];
    }

    $result = $result->fetch_assoc();
    $warning = [
        'warningId' => $result["warning_id"] ?? null,
        'statusId' => $result["status_id"] ?? 1,
        'statusContent' => $result["status_content"] ?? "Nieznane",
        'statusColor' => $result["status_color"] ?? "gray",
        'userId' => $result["user_id"] ?? "Nieznane",
        'userUsername' => $result["user_username"] ?? "Nieznane",
        'userDisplayname' => $result["user_displayname"] ?? "Nieznane",
        'issuerId' => $result["issuer_id"] ?? "Nieznane",
        'issuerUsername' => $result["issuer_username"] ?? "Nieznane",
        'issuerDisplayname' => $result["issuer_displayname"] ?? "Nieznane",
        'revokerId' => $result["revoker_id"] ?? "Nieznane",
        'revokerUsername' => $result["revoker_username"] ?? "Nieznane",
        'revokerDisplayname' => $result["revoker_displayname"] ?? "Nieznane",
        'reason' => $result["reason"] ?? "Nieznane",
        'isAccepted' => $result["is_accepted"] ?? false,
        'issuedAt' => $result["issued_at"] ?? "0000-00-00 00:00:00",
        'revokedAt' => $result["revoked_at"] ?? "0000-00-00 00:00:00"
    ];

    return ["success" => true, "message" => "Pobrano informacje o ostrzeżeniu o ID $warningId", "warning" => $warning];
}

function getUserWarnings($userId) {
    global $conn;

    $stmt = $conn->prepare("SELECT w.warning_id FROM warnings w WHERE w.user_id = ?");
    $stmt->bind_param("s", $userId);
    $stmt->execute();
    if ($stmt->errno) {
        error_log("MySQL error: " . $stmt->error);
        return ["success" => false, "message" => "Wystąpił błąd podczas pobierania ostrzeżeń użytkownika", "warnings" => null];
    }
    $result = $stmt->get_result();
    $stmt->close();

    $warnings = [];
    while ($row = $result->fetch_assoc()) {
        $warning = getWarning($row["warning_id"]);
        if ($warning["success"] && $warning["warning"]) {
            array_push($warnings, $warning["warning"]);
        }
    }

    return ["success" => true, "message" => "Pobrano ostrzeżenia użytkownika o ID $userId", "warnings" => $warnings];
}

function getAllWarnings($search = null) {
    global $conn;

    if ($search) {
        $searchString = "%" . $search . "%";
        $stmt = $conn->prepare("SELECT w.warning_id FROM warnings w LEFT JOIN users u ON w.user_id = u.id WHERE w.warning_id LIKE ? or w.user_id LIKE ? or u.username LIKE ? or u.displayname LIKE ? or w.reason LIKE ?");
        $stmt->bind_param("sssss", $search, $search, $searchString, $searchString, $searchString);
    } else {
        $stmt = $conn->prepare("SELECT w.warning_id FROM warnings w");
    }
    $stmt->execute();
    if ($stmt->errno) {
        error_log("MySQL error: " . $stmt->error);
        return ["success" => false, "message" => "Wystąpił błąd podczas pobierania ostrzeżeń", "warnings" => null];
    }
    $result = $stmt->get_result();

    $warnings = [];
    while ($row = $result->fetch_assoc()) {
        $warning = getWarning($row["warning_id"]);
        if ($warning["success"] && $warning["warning"]) {
            $warnings[] = $warning["warning"];
        }
    }

    return ["success" => true, "message" => "Pobrano wszystkie ostrzeżenia", "warnings" => $warnings];
}