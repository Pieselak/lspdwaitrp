<?php 
include_once("config/config.php");
include_once ("database.php");

if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

// ###################
// # Basic functions #
// ###################

function redirectTo($url, $exit = true): void {
    header("Location: $url");
    if ($exit) {
        exit();
    }
}

function formatDate($date, $format = "datetime") {
    global $cfg_format;
    if (!isset($cfg_format[$format])) {
        $format = $cfg_format[0];
    }
    return date($cfg_format[$format], strtotime($date));
}

function validate($input, $inputType = "string"): array {
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
        case "string-password":
            $input = filter_var($input, FILTER_VALIDATE_REGEXP, ["options" => ["regexp" => "/^[a-zA-Z0-9_\-!@#$%^&*()]+$/"]]);
            if (!$input) {
                return ["success" => false, "message" => "Niepoprawny format hasła (A-Z, a-z, 0-9, _, -, !@#$%^&*())"];
            }
            break;
        case "string-tags":
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
        return null;
    }
}

// ######################
// # Settings functions #
// ######################

function getSetting($setting): array {
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
}

function setSetting($setting, $value): array {
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
}
// #########################
// # Maintenance functions #
// #########################

function getMaintenanceMode(): array {
    return getSetting("maintenance_mode");
}

function getMaintenancePassword(): array {
    return getSetting("maintenance_password");
}

function checkMaintenance(): void {
    $status = getMaintenanceMode();
    $password = getMaintenancePassword();
    $userPassword = $_SESSION["maintenancePassword"] ?? null;

    if ($status["success"] && $password["success"]) {
        if ($status["message"] == "1" && $password["message"] != $userPassword) {
            redirectTo("maintenance.php");
        }
    }
}

function setMaintenanceMode($mode): array {
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

function setMaintenancePassword($password): array {
    return setSetting("maintenance_password", $password);
}

// Announcement functions
function getAnnouncementMode(): array {
    return getSetting('announcement_mode');
}

function getAnnouncementContent(): array {
    return getSetting('announcement_content');
}

function setAnnouncementMode($mode): array {
    switch ($mode) {
        case "1":
            $setting = setSetting("announcement_mode", "1");
            break;
        case "0":
            $setting = setSetting("announcement_mode", "0");
            break;
        default:
            return ["success" => false, "message" => "Niepoprawny tryb (0 - wyłączony, 1 - włączony)"];
    }

    return $setting;
}

function setAnnouncementContent($content): array {
    return setSetting('announcement_content', $content);
}

// #########################
// # Discord API functions #
// #########################

function getDiscordAuthToken($code): array {
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

function getDiscordUser($auth_token): array {
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

function getDiscordGuilds($auth_token): array {
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

function createDiscordUser($id):array {
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

// ##################
// # User functions #
// ##################

function getUser($id): array {
    global $conn, $cfg_discord;

    $stmt = $conn->prepare("SELECT * FROM users u LEFT JOIN users_roles r ON u.role_id = r.role_id WHERE id = ?");
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
            'created_at' => formatDate($result["created_at"]) ?? "Nieznane",
            'last_update' => formatDate($result["last_update"]) ?? "Nieznane",
            'last_login' => formatDate(getUserLastLoginDate($id)["login"]) ?? "Niezanne",
            'role_id' => $result["role_id"] ?? 0,
            'role_name' => $result["role_name"] ?? "Brak przypisanej roli",
        ];

        return ["success" => true, "message" => "Użytkownik o ID $id został pobrany", "user" => $user];
    }
}

function getAllUsers($search = null): array {
    global $conn;

    if ($search) {
        $searchTerm = "%" . $search . "%";
        $stmt = $conn->prepare("SELECT u.id FROM users u LEFT JOIN users_roles r ON u.role_id = r.role_id WHERE u.id LIKE ? or u.username LIKE ? or u.displayname LIKE ? or r.role_name LIKE ?");
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

function updateUser($id, $params): array {
    global $conn;

    $user = getUser($id);
    if (!$user["success"]) {
        return ["success" => false, "message" => $user["message"]];
    } elseif (!$user["user"]) {
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

function createUser($params): array {
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

function deleteUser($id): array {
    global $conn;

    $getUser = getUser($id);
    if (!$getUser["success"]) {
        return ["success" => false, "message" => $getUser["message"]];
    } elseif (!$getUser["user"]) {
        return ["success" => false, "message" => "Użytkownik o ID $id nie istnieje"];
    }

    $stmt = $conn->prepare("DELETE FROM users WHERE id = ?");
    $stmt->bind_param("s", $id);
    $stmt->execute();
    $stmt->close();

    return ["success" => true, "message" => "Użytkownik o ID $id został usunięty"];
}

function refreshUser(): array {
    if (!isUserLogged()) {
        return ["success" => false, "message" => "Użytkownik nie jest zalogowany"];
    }

    $getUser = getUser($_SESSION["user"]["id"]);

    if (!$getUser["success"]) {
        return ["success" => false, "message" => $getUser["message"]];
    } elseif (!$getUser["user"]) {
        return ["success" => false, "message" => "Użytkownik o ID " . $getUser["id"] . " nie istnieje"];
    }

    $_SESSION["user"] = $getUser["user"];

    return ["success" => true, "message" => "Zaktualizowano dane użytkownika"];
}

// ##################
// # Logs functions #
// ##################

function getLogs(): array {
    global $conn;

    $stmt = $conn->prepare("SELECT l.log_id, l.action_id, la.action_name, la.action_icon, la.action_color, la.action_message, l.user_id, u.username, u.displayname, l.log_details, l.log_date FROM logs l LEFT JOIN logs_actions la ON l.action_id = la.action_id LEFT JOIN users u ON l.user_id = u.id ORDER BY l.log_id DESC");
    $stmt->execute();
    if ($stmt->errno) {
        error_log("MySQL error: " . $stmt->error);
        return ["success" => false, "message" => "Wystąpił błąd podczas pobierania zapisów z dziennika", "logs" => null];
    }

    $result = $stmt->get_result();
    $stmt->close();

    $logs = array();

    while ($row = $result->fetch_assoc()) {
        $log = [
            'log_id' => $row["log_id"] ?? "Nieznane",
            'action_id' => $row["action_id"] ?? "Nieznane",
            'action_name' => $row["action_name"] ?? "Nieznane",
            'action_icon' => $row["action_icon"] ?? "bx bx-error",
            'action_color' => $row["action_color"] ?? "gray",
            'action_message' => $row["action_message"] ?? "Nieznane",
            'user_id' => $row["user_id"] ?? "Nieznane",
            'user_username' => $row["username"] ?? "Nieznane",
            'user_displayname' => $row["displayname"] ?? "Nieznane",
            'log_details' => $row["log_details"] ?? "Brak szczegółów",
            'log_date' => formatDate($row["log_date"] ?? "0000-00-00 00:00:00"),
            'log_date_raw' => $row["log_date"] ?? "0000-00 00:00:00",
        ];
        $logs[] = $log;
    }

    return ["success" => true, "message" => "Pobrano wszystkie zapisy z dziennika", "logs" => $logs];
}

function getLogsCategories(): array {
    global $conn;

    $stmt = $conn->prepare("SELECT action_id, action_name FROM logs_actions");
    $stmt->execute();
    if ($stmt->errno) {
        error_log("MySQL error: " . $stmt->error);
        return ["success" => false, "message" => "Wystąpił błąd podczas pobierania kategorii z dziennika", "categories" => null];
    }

    $result = $stmt->get_result();
    $stmt->close();

    $categories = array();

    while ($row = $result->fetch_assoc()) {
        $categories[] = [
            'action_id' => $row["action_id"] ?? 0,
            'action_name' => $row["action_name"] ?? "Nieznane",
        ];
    }

    return ["success" => true, "message" => "Pobrano wszystkie kategorie z dziennika", "categories" => $categories];
}

function addLog($params): array {
    global $conn;

    $params = [
        'action' => $params["action"] ?? null,
        'user' => $params["user"] ?? null,
        'details' => $params["details"] ?? null,
    ];

    $stmt = $conn->prepare("INSERT INTO logs (user_id, action_id, log_details, log_date) VALUES (?, ?, ?, NOW())");
    $stmt->bind_param("sss", $params["user"], $params["action"], $params["details"]);
    $stmt->execute();
    if ($stmt->errno) {
        error_log("MySQL error: " . $stmt->error);
        return ["success" => false, "message" => "Wystąpił błąd podczas dodawania zapisu do dziennika"];
    }
    $stmt->close();

    return ["success" => true, "message" => "Zapis został dodany do dziennika"];
}
function deleteLog($id): array {
    global $conn;

    $stmt = $conn->prepare("DELETE FROM logs WHERE log_id = ?");
    $stmt->bind_param("s", $id);
    $stmt->execute();
    if ($stmt->errno) {
        error_log("MySQL error: " . $stmt->error);
        return ["success" => false, "message" => "Wystąpił błąd podczas usuwania zapisu z dziennika"];
    }
    $stmt->close();

    return ["success" => true, "message" => "Zapis o ID $id został usunięty"];
}


// ###################
// # Login functions #
// ###################

function checkUserLogin(): array {
    global $conn;

    if (!isset($_SESSION["user"])) {
        return ["success" => false, "message" => "Nie znaleziono sesji użytkownika"];
    }

    $user = $_SESSION["user"];
    $sessionId = session_id();
    $clientIp = getClientIP();

    $stmt = $conn->prepare("SELECT login_session_active FROM users_logins WHERE user_id = ? AND login_session = ? AND login_ip = ?");
    $stmt->bind_param("sss", $user["id"], $sessionId, $clientIp);
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
    if (!$row["login_session_active"]) {
        return ["success" => false, "message" => "Sesja użytkownika została dezaktywowana"];
    }

    return ["success" => true, "message" => "Znaleziono sesję użytkownika"];
}

function addUserLogin($id): array {
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

function getUserLastLoginDate($id): array {
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
    return ["success" => true, "message" => "Pobrano ostatnie logowanie użytkownika", "login" => $row["login_date"] ?? null];
}

function getUserLogins($userid): array {
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
            'login_id' => $row["login_id"] ?? null,
            'user_id' => $row["user_id"] ?? null,
            'login_session' => $row["login_session"] ?? "Nieznane",
            'login_session_active' => $row["login_session_active"] ?? 0,
            'login_ip' => $row["login_ip"] ?? "Nieznane",
            'login_date' => formatDate($row["login_date"] ?? "0000-00-00 00:00:00"),
            'login_date_raw' => $row["login_date"] ?? "0000-00-00 00:00:00",
        ];
    }

    return ["success" => true, "message" => "Pobrano logowania użytkownika", "logins" => $logins];
}

function deactivateUserLogin($id, $userid): array {
    global $conn;

    $stmt = $conn->prepare("UPDATE users_logins SET login_session_active = 0 WHERE user_id = ? AND login_session = ? ");
    $stmt->bind_param("ss", $userid, $id);
    $stmt->execute();
    if ($stmt->errno) {
        error_log("MySQL error: " . $stmt->error);
        return ["success" => false, "message" => "Wystąpił błąd podczas dezaktywacji sesji użytkownika"];
    }
    $stmt->close();

    return ["success" => true, "message" => "Dezaktywowano sesje użytkownika"];
}

function logoutUser() 
{
    if (isUserLogged()) {
        unset($_SESSION["user"]);
        unset($_SESSION["guilds"]);
        deactivateUserLogin(session_id(), $_SESSION["user"]["id"]);

        unset($_SESSION["maintenancePassword"]);
        unset($_SESSION["maintenanceAttemps"]);
        unset($_SESSION["maintenanceCooldown"]);
    }

    session_regenerate_id();
    redirectTo("login.php");
}

function loginUser($user, $guilds, $redirect = null): array {
    if ($redirect == "" || $redirect == null) {
        $redirect = "index.php";
    }

    $getUser = getUser($user["id"]);
    if (!$getUser["success"]) {
        return ["success" => false, "message" => $getUser["message"]];
    } elseif (!$getUser["user"]) {
        createUser([
            'id' => $user["id"],
            'username' => $user["username"],
            'displayname' => $user["global_name"],
            'avatar' => $user["avatar"],
            'email' => $user["email"],
        ]);
    } else {
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
    } elseif (!$getUser["user"]) {
        return ["success" => false, "message" => "Użytkownik o ID " . $getUser["id"] . " nie istnieje"];
    }

    session_regenerate_id();
    addUserLogin($user["id"]);
    $_SESSION["user"] = $getUser["user"];
    $_SESSION["guilds"] = $guilds;

    unset($_SESSION["loginRedirect"]);
    redirectTo($redirect, false);
    return ["success" => true, "message" => "Zalogowano użytkownika"];
}
// #############################
// # User Validation functions #
// #############################
function isUserInGuild($guilds, $guild_id): bool {
    foreach ($guilds as $guild) {
        if ($guild["id"] == $guild_id) {
            return true;
        }
    }
    return false;
}

function isUserLogged(): bool {
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

    if (isUserSuspended($user["id"])) {
        redirectTo("suspended.php");
    }

    if (isUserWarned($user["id"])) {
        redirectTo("warning.php");
    }

    return $_SESSION["user"];
}

// #########################
// # Permissions functions #
// #########################

function checkUserPageAccess($permission, $checkLogin = true): bool {
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

function checkUserPermission($category, $permission): bool {
    global $cfg_permissions;

    $user = $_SESSION["user"] ?? null;
    if (checkUserPageAccess($category)) {
        if (in_array($permission, $cfg_permissions[$user["role_id"]][$category])) {
            return true;
        }
    }

    return false;
}

function validateUserAccess($permission, $redirectLogin = null, $redirectNoAccess = true): array {
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

// #########################
// # Suspensions functions #
// #########################

function isUserSuspended($userId): bool {
    $suspensions = getUserSuspensions($userId);
    if ($suspensions["success"] && $suspensions["suspensions"]) {
        foreach ($suspensions["suspensions"] as $suspension) {
            if ($suspension["status_id"] == 1) {
                return true;
            }
        }
    }
    return false;
}

function issueSuspension($userId, $params): array {
    global $conn;

    $params = [
        'issuer_id' => $params["issuer_id"] ?? null,
        'reason' => $params["reason"] ?? null,
        'expires_at' => $params["expires_at"] ?? "0000-00-00 00:00:00",
        'is_permanent' => $params["is_permanent"] ?? true,
    ];

    $stmt = $conn->prepare("INSERT INTO suspensions(user_id, issuer_id, reason, is_permanent, expires_at) VALUES (?,?,?,?,?)");
    $stmt->bind_param("sssis", $userId, $params["issuer_id"], $params["reason"], $params["is_permanent"], $params["expires_at"]);
    $stmt->execute();
    if ($stmt->errno) {
        error_log("MySQL error: " . $stmt->error);
        return ["success" => false, "message" => "Wystąpił błąd podczas dodawania zawieszenia"];
    }

    $stmt->close();
    return ["success" => true, "message" => "Użytkownik o ID $userId został zawieszony"];
}

function updateSuspension($suspensionId, $params): array {
    global $conn;

    $suspension = getSuspension($suspensionId);
    if (!$suspension["success"]) {
        return ["success" => false, "message" => $suspension["message"]];
    } else if (!$suspension["suspension"]) {
        return ["success" => false, "message" => "Zawieszenie o ID $suspensionId nie istnieje"];
    }

    $suspension = $suspension["suspension"];
    $params = [
        'status_id' => $params["status_id"] ?? $suspension["status_id"],
        'issuer_id' => $params["issuer_id"] ?? ($suspension["issuer_id"] != "Nieznane" ? $suspension["issuer_id"] : null),
        'revoker_id' => $params["revoker_id"] ?? ($suspension["revoker_id"] != "Nieznane" ? $suspension["revoker_id"] : null),
        'reason' => $params["reason"] ?? $suspension["reason"],
        'is_permanent' => $params["is_permanent"] ?? $suspension["is_permanent"],
        'expires_at' => $params["expires_at"] ?? $suspension["expires_at_raw"],
        'issued_at' => $params["issued_at"] ?? $suspension["issued_at_raw"],
        'revoked_at' => $params["revoked_at"] ?? $suspension["revoked_at_raw"],
    ];

    $stmt = $conn->prepare("UPDATE suspensions SET status_id = ?, issuer_id = ?, revoker_id = ?, reason = ?, is_permanent = ?, expires_at = ?, issued_at = ?, revoked_at = ? WHERE suspension_id = ?");
    $stmt->bind_param("isssisssi", $params["status_id"], $params["issuer_id"], $params["revoker_id"], $params["reason"], $params["is_permanent"], $params["expires_at"], $params["issued_at"], $params["revoked_at"], $suspensionId);
    $stmt->execute();
    if ($stmt->errno) {
        error_log("MySQL error: " . $stmt->error);
        return ["success" => false, "message" => "Wystąpił błąd podczas aktualizacji zawieszenia"];
    }

    $stmt->close();
    return ["success" => true, "message" => "Zawieszenie o ID $suspensionId zostało zaktualizowane"];
}

function revokeSuspension($suspensionId, $revoker_id): array {
    $suspension = getSuspension($suspensionId);
    if (!$suspension["success"]) {
        return ["success" => false, "message" => $suspension["message"]];
    } else if (!$suspension["suspension"]) {
        return ["success" => false, "message" => "Zawieszenie o ID $suspensionId nie istnieje"];
    } else if ($suspension["suspension"]["status_id"] == 2) {
        return ["success" => false, "message" => "Zawieszenie o ID $suspensionId jest już unieważnione"];
    }

    return updateSuspension($suspensionId, [
        'status_id' => 2,
        'revoker_id' => $revoker_id,
        'revoked_at' => date('Y-m-d H:i:s'),
    ]);
}

function deleteSuspension($suspensionId): array {
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

function getSuspension($suspensionId, $checkIfExpired = true): array {
    global $conn;

    $stmt = $conn->prepare("SELECT s.suspension_id, s.status_id, st.status_content, st.status_color, s.user_id, u.username user_username, u.displayname user_displayname, s.issuer_id, i.username issuer_username, i.displayname issuer_displayname, s.revoker_id, r.username revoker_username, r.displayname revoker_displayname, s.reason, s.is_permanent, s.expires_at, s.issued_at, s.revoked_at FROM suspensions s LEFT JOIN punishment_status st ON s.status_id = st.status_id LEFT JOIN users u ON s.user_id = u.id LEFT JOIN users i ON s.issuer_id = i.id LEFT JOIN users r ON s.revoker_id = r.id WHERE s.suspension_id = ?");
    $stmt->bind_param("s", $suspensionId);
    $stmt->execute();
    if ($stmt->errno) {
        error_log("MySQL error: " . $stmt->error);
        return ["success" => false, "message" => "Wystąpił błąd podczas pobierania informacji o zawieszeniu", "suspension" => null];
    }
    $result = $stmt->get_result();

    if ($result->num_rows == 0) {
        return ["success" => true, "message" => "Nie znaleziono zawieszenia o ID $suspensionId", "suspension" => null];
    } else {
        $result = $result->fetch_assoc();

        $suspension = [
            'suspension_id' => $result["suspension_id"] ?? null,
            'status_id' => $result["status_id"] ?? 1,
            'status_content' => $result["status_content"] ?? "Nieznane",
            'status_color' => $result["status_color"] ?? "gray",
            'user_id' => $result["user_id"] ?? "Nieznane",
            'user_username' => $result["user_username"] ?? "Nieznane",
            'user_displayname' => $result["user_displayname"] ?? "Nieznane",
            'issuer_id' => $result["issuer_id"] ?? "Nieznane",
            'issuer_username' => $result["issuer_username"] ?? "Nieznane",
            'issuer_displayname' => $result["issuer_displayname"] ?? "Nieznane",
            'revoker_id' => $result["revoker_id"] ?? "Nieznane",
            'revoker_username' => $result["revoker_username"] ?? "Nieznane",
            'revoker_displayname' => $result["revoker_displayname"] ?? "Nieznane",
            'reason' => $result["reason"] ?? "Nieznane",
            'is_permanent' => $result["is_permanent"] ?? true,
            'expires_at' => formatDate($result["expires_at"] ?? "0000-00-00 00:00:00"),
            'expires_at_raw' => $result["expires_at"] ?? "0000-00-00 00:00:00",
            'issued_at' => formatDate($result["issued_at"] ?? "0000-00-00 00:00:00"),
            'issued_at_raw' => $result["issued_at"] ?? "0000-00-00 00:00:00",
            'revoked_at' => formatDate($result["revoked_at"] ?? "0000-00-00 00:00:00"),
            'revoked_at_raw' => $result["revoked_at"] ?? "0000-00-00 00:00:00",
        ];

        $todayDate = new DateTime();
        $expireDate = new DateTime($suspension["expires_at_raw"]);

        if ($checkIfExpired && $suspension["status_id"] == 1 && !$suspension["is_permanent"] && $expireDate < $todayDate) {
            $update = updateSuspension($suspensionId, ["status_id" => 3]);
            if (!$update["success"]) {
                return ["success" => false, "message" => $update["message"], "suspension" => null];
            }

            $suspension = getSuspension($suspensionId, false);
            if (!$suspension["success"]) {
                return ["success" => false, "message" => $suspension["message"], "suspension" => null];
            }
            return ["success" => true, "message" => "Zawieszenie o ID $suspensionId wygasło", "suspension" => $suspension["suspension"]];
        }
    }

    $stmt->close();
    return ["success" => true, "message" => "Pobrano informacje o zawieszeniu o ID $suspensionId", "suspension" => $suspension];
}

function getUserSuspensions($userId): array {
    global $conn;

    $stmt = $conn->prepare("SELECT s.suspension_id FROM suspensions s WHERE s.user_id = ?");
    $stmt->bind_param("s", $userId);
    $stmt->execute();
    if ($stmt->errno) {
        error_log("MySQL error: " . $stmt->error);
        return ["success" => false, "message" => "Wystąpił błąd podczas pobierania zawieszeń użytkownika", "suspensions" => null];
    }
    $result = $stmt->get_result();

    $suspensions = [];
    while ($row = $result->fetch_assoc()) {
        $suspension = getSuspension($row["suspension_id"]);
        if ($suspension["success"] && $suspension["suspension"]) {
            $suspensions[] = $suspension["suspension"];
        }
    }

    $stmt->close();
    return ["success" => true, "message" => "Pobrano zawieszenia użytkownika o ID $userId", "suspensions" => $suspensions];
}

function getAllSuspensions($search = null): array {
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

    $stmt->close();
    return ["success" => true, "message" => "Pobrano wszystkie zawieszenia", "suspensions" => $suspensions];
}

// ######################
// # Warnings functions #
// ######################

function isUserWarned($userId): bool {
    $warnings = getUserWarnings($userId);
    if ($warnings["success"] && $warnings["warnings"]) {
        foreach ($warnings["warnings"] as $warning) {
            if ($warning["status_id"] == 1 && $warning["is_accepted"] == 0) {
                return true;
            }
        }
    }
    return false;
}

function issueWarning($userId, $params): array {
    global $conn;

    $params = [
        'issuer_id' => $params["issuer_id"] ?? null,
        'reason' => $params["reason"] ?? null,
    ];

    $stmt = $conn->prepare("INSERT INTO warnings (user_id, issuer_id, reason) VALUES (?, ?, ?)");
    $stmt->bind_param("sssis", $userId, $params["issuer_id"], $params["reason"]);
    $stmt->execute();
    if ($stmt->errno) {
        error_log("MySQL error: " . $stmt->error);
        return ["success" => false, "message" => "Wystąpił błąd podczas dodawania ostrzeżenia"];
    }

    $stmt->close();
    return ["success" => true, "message" => "Użytkownik o ID $userId został ostrzeżony"];
}

function acceptWarning($warningId): array {
    $warning = getWarning($warningId);

    if (!$warning["success"]) {
        return ["success" => false, "message" => $warning["message"]];
    } else if (!$warning["warning"]) {
        return ["success" => false, "message" => "Ostrzeżenie o ID $warningId nie istnieje"];
    } else if ($warning["warning"]["is_accepted"]) {
        return ["success" => false, "message" => "Ostrzeżenie o ID $warningId zostało już zaakceptowane"];
    } else if ($warning["warning"]["status_id"] == 2) {
        return ["success" => false, "message" => "Ostrzeżenie o ID $warningId zostało unieważnione"];
    } else if (isset($_SESSION["user"]["id"]) && $warning["warning"]["user_id"] != $_SESSION["user"]["id"]) {
        return ["success" => false, "message" => "Nie masz uprawnień do zaakceptowania ostrzeżenia o ID $warningId"];
    }

    return updateWarning($warningId, ["is_accepted" => true]);
}

function updateWarning($warningId, $params): array {
    global $conn;

    $warning = getWarning($warningId);
    if (!$warning["success"]) {
        return ["success" => false, "message" => $warning["message"]];
    } else if (!$warning["warning"]) {
        return ["success" => false, "message" => "Ostrzeżenie o ID $warningId nie istnieje"];
    }

    $warning = $warning["warning"];
    $params = [
        'status_id' => $params["status_id"] ?? $warning["status_id"],
        'issuer_id' => $params["issuer_id"] ?? ($warning["issuer_id"] != "Nieznane" ? $warning["issuer_id"] : null),
        'revoker_id' => $params["revoker_id"] ?? ($warning["revoker_id"] != "Nieznane" ? $warning["revoker_id"] : null),
        'reason' => $params["reason"] ?? $warning["reason"],
        'is_accepted' => $params["is_accepted"] ?? $warning["is_accepted"],
        'issued_at' => $params["issued_at"] ?? $warning["issued_at_raw"],
        'revoked_at' => $params["revoked_at"] ?? $warning["revoked_at_raw"],
    ];

    var_dump($params);
    echo "<br>";

    $stmt = $conn->prepare("UPDATE warnings SET status_id = ?, issuer_id = ?, revoker_id = ?, reason = ?, is_accepted = ?, issued_at = ?, revoked_at = ? WHERE warning_id = ?");
    $stmt->bind_param("isssiiss", $params["status_id"], $params["issuer_id"], $params["revoker_id"], $params["reason"], $params["is_accepted"], $params["issued_at"], $params["revoked_at"], $warningId);
    $stmt->execute();
    if ($stmt->errno) {
        error_log("MySQL error: " . $stmt->error);
        return ["success" => false, "message" => "Wystąpił błąd podczas aktualizacji ostrzeżenia"];
    }

    $stmt->close();
    return ["success" => true, "message" => "Ostrzeżenie o ID $warningId zostało zaktualizowane"];
}

function revokeWarning($warningId, $revoker_id): array {
    $warning = getWarning($warningId);
    if (!$warning["success"]) {
        return ["success" => false, "message" => $warning["message"]];
    } else if (!$warning["warning"]) {
        return ["success" => false, "message" => "Ostrzeżenie o ID $warningId nie istnieje"];
    } else if ($warning["warning"]["status_id"] == 2) {
        return ["success" => false, "message" => "Ostrzeżenie o ID $warningId jest już unieważnione"];
    }

    return updateWarning($warningId, [
        'status_id' => 2,
        'revoker_id' => $revoker_id,
        'revoked_at' => date('Y-m-d H:i:s'),
    ]);
}

function deleteWarning($warningId): array {
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

function getWarning($warningId): array {
    global $conn;

    $stmt = $conn->prepare("SELECT w.warning_id, w.status_id, st.status_content, st.status_color, w.user_id, u.username user_username, u.displayname user_displayname, w.issuer_id, i.username issuer_username, i.displayname issuer_displayname, w.revoker_id, r.username revoker_username, r.displayname revoker_displayname, w.reason, w.is_accepted, w.issued_at, w.revoked_at FROM warnings w LEFT JOIN punishment_status st ON w.status_id = st.status_id LEFT JOIN users u ON w.user_id = u.id LEFT JOIN users i ON w.issuer_id = i.id LEFT JOIN users r ON w.revoker_id = r.id WHERE w.warning_id = ?");
    $stmt->bind_param("s", $warningId);
    $stmt->execute();
    if ($stmt->errno) {
        error_log("MySQL error: " . $stmt->error);
        return ["success" => false, "message" => "Wystąpił błąd podczas pobierania informacji o ostrzeżeniu", "warning" => null];
    }
    $result = $stmt->get_result();

    if ($result->num_rows == 0) {
        return ["success" => true, "message" => "Nie znaleziono ostrzeżenia o ID $warningId", "warning" => null];
    }

    $result = $result->fetch_assoc();
    $warning = [
        'warning_id' => $result["warning_id"] ?? null,
        'status_id' => $result["status_id"] ?? 1,
        'status_content' => $result["status_content"] ?? "Nieznane",
        'status_color' => $result["status_color"] ?? "gray",
        'user_id' => $result["user_id"] ?? "Nieznane",
        'user_username' => $result["user_username"] ?? "Nieznane",
        'user_displayname' => $result["user_displayname"] ?? "Nieznane",
        'issuer_id' => $result["issuer_id"] ?? "Nieznane",
        'issuer_username' => $result["issuer_username"] ?? "Nieznane",
        'issuer_displayname' => $result["issuer_displayname"] ?? "Nieznane",
        'revoker_id' => $result["revoker_id"] ?? "Nieznane",
        'revoker_username' => $result["revoker_username"] ?? "Nieznane",
        'revoker_displayname' => $result["revoker_displayname"] ?? "Nieznane",
        'reason' => $result["reason"] ?? "Nieznane",
        'is_accepted' => $result["is_accepted"] ?? false,
        'issued_at' => formatDate($result["issued_at"] ?? "0000-00-00 00:00:00"),
        'issued_at_raw' => $result["issued_at"] ?? "0000-00-00 00:00:00",
        'revoked_at' => formatDate($result["revoked_at"] ?? "0000-00-00 00:00:00"),
        'revoked_at_raw' => $result["revoked_at"] ?? "0000-00-00 00:00:00",
    ];

    $stmt->close();
    return ["success" => true, "message" => "Pobrano informacje o ostrzeżeniu o ID $warningId", "warning" => $warning];
}

function getUserWarnings($userId): array {
    global $conn;

    $stmt = $conn->prepare("SELECT w.warning_id FROM warnings w WHERE w.user_id = ?");
    $stmt->bind_param("s", $userId);
    $stmt->execute();
    if ($stmt->errno) {
        error_log("MySQL error: " . $stmt->error);
        return ["success" => false, "message" => "Wystąpił błąd podczas pobierania ostrzeżeń użytkownika", "warnings" => null];
    }
    $result = $stmt->get_result();

    $warnings = [];
    while ($row = $result->fetch_assoc()) {
        $warning = getWarning($row["warning_id"]);
        if ($warning["success"] && $warning["warning"]) {
            $warnings[] = $warning["warning"];
        }
    }

    $stmt->close();
    return ["success" => true, "message" => "Pobrano ostrzeżenia użytkownika o ID $userId", "warnings" => $warnings];
}

function getAllWarnings($search = null): array {
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

    $stmt->close();
    return ["success" => true, "message" => "Pobrano wszystkie ostrzeżenia", "warnings" => $warnings];
}

// ####################################
// # Documents functions --- Officers #
// ####################################

function getOfficersDepartments(): array {
    global $conn;

    $stmt = $conn->prepare("SELECT * FROM officers_departments");
    $stmt->execute();
    if ($stmt->error) {
        error_log("MySQL error: " . $stmt->error);
        return ["success" => false, "message" => "Wystąpił błąd podczas pobierania departamentów", "departments" => null];
    }
    $result = $stmt->get_result();

    $departments = [];
    while ($row = $result->fetch_assoc()) {
        $departments[] = [
            "id" => $row["officer_department_id"] ?? 0,
            "name" => $row["officer_department_name"] ?? "Nieznane",
        ];
    }

    $stmt->close();
    return ["success" => true, "message" => "Pobrano departamenty", "departments" => $departments];
}

function getOffcers(): array {
    global $conn;

    $stmt = $conn->prepare("SELECT o.officer_name, o.officer_badge, o.officer_image, r.officer_rank_name, o.officer_department_id, d.officer_department_name, o.officer_discord_id FROM officers o LEFT JOIN officers_ranks r ON o.officer_rank_id = r.officer_rank_id LEFT JOIN officers_departments d ON o.officer_department_id = d.officer_department_id ORDER BY o.officer_rank_id ASC");
    $stmt->execute();
    if ($stmt->error) {
        error_log("MySQL error: " . $stmt->error);
        return ["success" => false, "message" => "Wystąpił błąd podczas pobierania oficerów", "officers" => null];
    }
    $result = $stmt->get_result();

    $officers = [];
    while ($row = $result->fetch_assoc()) {
        $officers[] = [
            "name" => $row["officer_name"] ?? "Nieznane",
            "badge" => $row["officer_badge"] ?? "?",
            "image" => $row["officer_image"] ?? "placeholder.png",
            "rank" => $row["officer_rank_name"] ?? "Nieznane",
            "department_id" => $row["officer_department_id"] ?? 0,
            "department" => $row["officer_department_name"] ?? "Nieznane",
            "discord_id" => $row["officer_discord_id"] ?? "Nieznane",
        ];
    }

    $stmt->close();
    return ["success" => true, "message" => "Pobrano oficerów", "officers" => $officers];
}

// ####################################
// # Documents functions --- Discords #
// ####################################

function getDiscords(): array {
    global $conn;

    $stmt = $conn->prepare("SELECT * FROM discords ORDER BY discord_display_order ASC");
    $stmt->execute();
    if ($stmt->error) {
        error_log("MySQL error: " . $stmt->error);
        return ["success" => false, "message" => "Wystąpił błąd podczas pobierania discordów", "discords" => null];
    }
    $result = $stmt->get_result();

    $discords = [];
    while ($row = $result->fetch_assoc()) {
        $discords[] = [
            "name" => $row["discord_name"],
            "invite" => $row["discord_invite"],
            "image" => $row["discord_image"] ?? "placeholder.png",
        ];
    }

    $stmt->close();
    return ["success" => true, "message" => "Pobrano wszystkie serwery Discord", "discords" => $discords];
}