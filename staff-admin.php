<?php 
    include_once ("php/functions.php");

    checkMaintenance();
    $access = validateUserAccess("staff-admin", "staff-admin.php", false);
    $user = $access["user"];

    if ($_SERVER["REQUEST_METHOD"] == "POST") {
        if (isset($_POST["action"])) {
            switch ($_POST["action"]) {
                case "announcement":
                    $setAnnouncement = setAnnouncement($_POST["announcement"]);
                    break;
                case "maintenance":
                    $setMaintenanceMode = setMaintenanceMode($_POST["mode"]);
                    $setMaintenancePass = setMaintenancePassword($_POST["password"]);
                    break;
            }
        }
    }
?>
<!DOCTYPE html>
<html>
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
                                        $announcement = getAnnouncement();

                                        if (isset($setAnnouncement)) {
                                            echo '<p class="status ', $setAnnouncement["success"] ? 'green' : 'red', '">'. $setAnnouncement["message"] .'</p>';
                                        }

                                        if (!$announcement["success"]) {
                                            echo '<p class="status ', $announcement["success"] ? 'green' : 'red', '">'. $announcement["message"] .'</p>';
                                        }
                                    ?>
                                    <input type="hidden" name="action" value="announcement">
                                    <textarea data-autoresize maxlength="300" name="announcement" placeholder="Wpisz treść banera informacyjnego (zapisz pusty aby nie był widoczny)"><?= $announcement["success"] ? $announcement["message"] : "Nie udało się pobrać treści baneru informacyjnego" ?></textarea>
                                    <div class="buttons">
                                        <input type="submit" value="Zapisz">
                                        <input type="reset" value="Wyczyść">
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
                                        $maintenanceMode = getMaintenance();
                                        $maintenancePass = getMaintenancePassword();

                                        if (isset($setMaintenanceMode) && isset($setMaintenancePass)) {
                                            echo '<p class="status ', ($setMaintenanceMode["success"] && $setMaintenancePass["success"]) ? 'green' : 'red', '">'. $setMaintenanceMode["message"] .'</p>';
                                        }

                                        if (!$maintenanceMode["success"] || !$maintenancePass["success"]) {
                                            echo '<p class="status ', $maintenanceMode["success"] ? 'green' : 'red', '">'. $maintenanceMode["message"] .'</p>';
                                        }
                                    ?>
                                    <input type="hidden" name="action" value="maintenance">

                                    <select name="mode">
                                        <option value="0" <?= $maintenanceMode["message"] == 0 ? "selected" : "" ?>>Wyłączona</option>
                                        <option value="1" <?= $maintenanceMode["message"] == 1 ? "selected" : "" ?>>Włączona</option>
                                    </select>

                                    <input type="text" name="password" placeholder="Hasło dostępu" value="<?= $maintenancePass["success"] ? $maintenancePass["message"] : "" ?>">
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