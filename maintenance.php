<?php 
    require_once("server/functions.php");
    global $conn;

    if (getSetting("maintenance_mode")["message"] == "0") {
        redirectTo("index.php");
    }

    $cooldown = 60;
    $maxAttemps = 3;
    if (!isset($_SESSION["maintenanceAttemps"]) || !isset($_SESSION["maintenanceCooldown"])) {
        $_SESSION["maintenanceAttemps"] = 0;
        $_SESSION["maintenanceCooldown"] = 0;
    }

    if (isset($_POST["password"])) {
        $password = validate($_POST["password"], "string-password");
        $password = $password["success"] ? $password["message"] : null;
        $maintenancePassword = getSetting("maintenance_password");
        if (!$maintenancePassword["success"]) {
            $error = "Błąd odczytu hasła dostępu";
        } elseif ($maintenancePassword["message"] == $password) {
            $_SESSION["maintenancePassword"] = $password;
            redirectTo("index.php");
        } else {
            $error = "Nieprawidłowe hasło dostępu";

            if ($_SESSION["maintenanceAttemps"] < $maxAttemps && $_SESSION["maintenanceCooldown"] < time()) {
                $_SESSION["maintenanceAttemps"]++;
            }

            if ($_SESSION["maintenanceAttemps"] >= $maxAttemps) {
                $_SESSION["maintenanceAttemps"] = 0;
                $_SESSION["maintenanceCooldown"] = time() + $cooldown;
                redirectTo("maintenance.php");
            }
        }
    }

    if ($_SESSION["maintenanceCooldown"] > time()) {
        $timeout = $_SESSION["maintenanceCooldown"] - time();
        $error = "Przekroczono limit prób, spróbuj ponownie za " . $timeout . " sekund";
        $disabled = true;
    }
?>
<!DOCTYPE html>
<html lang="pl">
    <head>
        <?php include ("components/head.php") ?>
    </head>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            let timeout = <?= isset($timeout) ? $timeout : "0" ?>;
            let error = <?= isset($error) ? json_encode($error) : "null" ?>;
            const info = document.getElementById("status");
            const submit = document.getElementById("submit");

            if (error && timeout > 0 && info && submit) {
                try {
                    setInterval(() => {
                        timeout--;
                        if (timeout <= 0) {
                            info.remove();
                            submit.disabled = false;
                        } else {
                            info.innerHTML = "Przekroczono limit prób, spróbuj ponownie za " + timeout + " sekund";
                        }
                    }, 1000);
                } catch (err) {
                    console.error(err);
                }
            }
        });
    </script>
    <body>
        <?php include ("components/header.php") ?>
        <?php include ("components/announcement.php") ?>

        <main>
            <div class="background"></div>
            <div class="page">
                <div class="service">
                    <div class="header">
                        <h2>Przerwa techniczna</h2>
                        <h4>Strona znajduje się obecnie w trybie konserwacji</h4>
                    </div>
                    <div class="content">
                        <p>Przepraszamy za wszelkie niedogodności, prosimy spróbować ponownie później.</p>
                        <?php if (isset($error)): ?>
                            <p id="status" class="status red"><?= $error ?></p>
                        <?php endif; ?>
                    </div>
                    <div class="buttons">
                        <form action="<?= htmlspecialchars($_SERVER["PHP_SELF"]) ?>" method="post">
                            <input type="password" name="password" placeholder="Hasło dostępu" required>
                            <input type="submit" id="submit" value="Odblokuj dostęp" <?= isset($disabled) ? "disabled" : "" ?>>
                        </form>
                    </div>
                </div>
            </div>
        </main>
    </body>
</html>
<?php $conn->close(); ?>