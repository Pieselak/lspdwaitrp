<?php 
    include_once("server/functions.php");

    checkMaintenance();
    $access = validateUserAccess("staff-admin", "staff-admin.php", false);
    $user = $access["user"];

    if ($_SERVER["REQUEST_METHOD"] == "POST") {
        if (isset($_POST["action"])) {
            switch ($_POST["action"]) {
                case "announcement":
                    $validateAnnouncement = validate($_POST["announcement"], "string-tags");
                    if ($validateAnnouncement["success"]) {
                        $setAnnouncementContent = setAnnouncementContent($validateAnnouncement["message"]);
                    } else {
                        $setAnnouncementContent = $validateAnnouncement;
                    }
                    $setAnnouncementMode = setAnnouncementMode($_POST["mode"]);
                    addLog(["action" => 2, "user" => $user["id"], "details" => "tryb: ". ($_POST["mode"] == 1 ? "włączone" : "wyłączone") . ", treść: ". ($validateAnnouncement["success"] ? $_POST["announcement"] : "Nie udało się ustawić treści banera informacyjnego")]);
                    break;
                case "maintenance":
                    $validateMaintenancePass = validate($_POST["password"], "string-password");
                    if ($validateMaintenancePass["success"]) {
                        $setMaintenancePass = setMaintenancePassword($validateMaintenancePass["message"]);
                    } else {
                        $setMaintenancePass = $validateMaintenancePass;
                    }
                    $setMaintenanceMode = setMaintenanceMode($_POST["mode"]);
                    addLog(["action" => 3, "user" => $user["id"], "details" => "tryb: ". ($_POST["mode"] == 1 ? "włączone" : "wyłączone") . ", hasło: ". ($validateMaintenancePass["success"] ? $_POST["password"] : "Nie udało się ustawić hasła dostępu do trybu konserwacji")]);
                    break;
            }
        }
    }
?>
<!DOCTYPE html>
<html lang="pl">
    <head>
        <?php include ("components/head.php") ?>
    </head>
    <body>
        <?php include ("components/header.php") ?>
        <?php include ("components/announcement.php") ?>

        <main>
            <div class="background"></div>
            <div class="page">
                <?php if ($access["access"]): ?>
                    <div class="staff">
                        <div class="header">
                            <h2>Narzędzia personelu - Administrator</h2>
                        </div>
                        <div class="item">
                            <div class="header">
                                <h3>Baner informacyjny</h3>
                            </div>
                            <div class="content">
                                <form method="POST" action="<?= htmlspecialchars($_SERVER["PHP_SELF"]) ?>">
                                    <?php
                                        $announcementMode = getAnnouncementMode();
                                        $announcementContent = getAnnouncementContent();

                                        if (isset($setAnnouncementMode) && isset($setAnnouncementContent)) {
                                            echo '<p class="status ', ($setAnnouncementMode["success"] && $setAnnouncementContent["success"]) ? 'green' : 'red', '">'. $setAnnouncementMode["message"] .', '. $setAnnouncementContent["message"] .'</p>';
                                        }

                                        if (!$announcementContent["success"] || !$announcementMode["success"]) {
                                            echo '<p class="status red">'. $announcementContent["message"] .', '. $announcementMode["message"] .'</p>';
                                        }
                                    ?>
                                    <input type="hidden" name="action" value="announcement">

                                    <label>
                                        <span>Tryb banera informacyjnego</span>
                                        <select name="mode">
                                            <option value="0" <?= $announcementMode["message"] == 0 ? "selected" : "" ?>>Wyłączony</option>
                                            <option value="1" <?= $announcementMode["message"] == 1 ? "selected" : "" ?>>Włączony</option>
                                        </select>
                                    </label>

                                    <label>
                                        <span>Treść banera informacyjnego</span>
                                        <textarea data-autoresize maxlength="300" name="announcement" placeholder="Wpisz treść banera informacyjnego"><?= $announcementContent["success"] ? $announcementContent["message"] : "Nie udało się pobrać treści baneru informacyjnego" ?></textarea>
                                    </label>

                                    <div class="buttons">
                                        <input type="submit" value="Zapisz">
                                    </div>
                                </form>
                            </div>
                        </div>

                        <div class="item">
                            <div class="header">
                                <h3>Konserwacja strony</h3>
                            </div>
                            <div class="content">
                                <form method="POST" action="<?= htmlspecialchars($_SERVER["PHP_SELF"]) ?>">
                                    <?php 
                                        $maintenanceMode = getMaintenanceMode();
                                        $maintenancePass = getMaintenancePassword();

                                        if (isset($setMaintenanceMode) && isset($setMaintenancePass)) {
                                            echo '<p class="status ', ($setMaintenanceMode["success"] && $setMaintenancePass["success"]) ? 'green' : 'red', '">'. $setMaintenanceMode["message"] .', '. $setMaintenancePass["message"] .'</p>';
                                        }

                                        if (!$maintenanceMode["success"] || !$maintenancePass["success"]) {
                                            echo '<p class="status red">'. $maintenanceMode["message"] .', '. $maintenancePass["message"] .'</p>';
                                        }
                                    ?>
                                    <input type="hidden" name="action" value="maintenance">

                                    <label>
                                        <span>Tryb konserwacji</span>
                                        <select name="mode">
                                            <option value="0" <?= $maintenanceMode["message"] == 0 ? "selected" : "" ?>>Wyłączona</option>
                                            <option value="1" <?= $maintenanceMode["message"] == 1 ? "selected" : "" ?>>Włączona</option>
                                        </select>
                                    </label>

                                    <label>
                                        <span>Hasło dostępu</span>
                                        <input type="text" name="password" placeholder="Hasło dostępu" value="<?= $maintenancePass["success"] ? $maintenancePass["message"] : "" ?>">
                                    </label>

                                    <div class="buttons">
                                        <input type="submit" value="Zapisz">
                                    </div>
                                </form>
                            </div>
                        </div>
                    </div>
                <?php else: ?>
                    <?php include ("components/noaccess.php") ?>
                <?php endif; ?>
            </div>
        </main>
        
        <?php include ("components/footer.php") ?>
    </body>
</html>
<?php $conn->close(); ?>