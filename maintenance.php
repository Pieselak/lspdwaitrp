<?php 
    require_once ("php/functions.php");

    if (getSetting("maintenance_mode")["message"] == "0") {
        redirectTo("index.php");
    }

    $error = null;
    $showForm = true;
    $cooldown = 60;
    $maxTries = 3;
    if (!isset($_SESSION["maintenance_tries"]) || !isset($_SESSION["maintenance_cooldown"])) {
        $_SESSION["maintenance_tries"] = 0;
        $_SESSION["maintenance_cooldown"] = 0;
    }

    if (isset($_POST["password"])) {
        $maintenancePassword = getSetting("maintenance_password");
        if (!$maintenancePassword["success"]) {
            $error = "Błąd odczytu hasła dostępu";
        } elseif ($_POST["password"] == $maintenancePassword["message"]) {
            $_SESSION["maintenance_password"] = $_POST["password"];
            redirectTo("index.php");
        } else {
            $error = "Nieprawidłowe hasło dostępu";

            if ($_SESSION["maintenance_tries"] < $maxTries && $_SESSION["maintenance_cooldown"] < time()) {
                $_SESSION["maintenance_tries"]++;
            }

            if ($_SESSION["maintenance_tries"] >= $maxTries) {
                $_SESSION["maintenance_tries"] = 0;
                $_SESSION["maintenance_cooldown"] = time() + $cooldown;
                redirectTo("maintenance.php");
            }
        }
    }

    if ($_SESSION["maintenance_cooldown"] > time()) {
        $showForm = false;
        $error = "Przekroczono limit prób, spróbuj ponownie o " . date("H:i:s", $_SESSION["maintenance_cooldown"]);
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
                <div class="service">
                    <div class="header">
                        <h2>Przerwa techniczna</h2>
                        <h4>Strona znajduje się obecnie w trybie konserwacji</h4>
                    </div>
                    <div class="content">
                        <p>Przepraszamy za wszelkie niedogodności, prosimy spróbować ponownie później.</p>
                        <?php if (isset($error)): ?>
                            <p class="info"><?= $error ?></p>
                        <?php endif; ?>
                    </div>
                    <div class="buttons">
                        <?php if ($showForm): ?>
                        <form action="<?= htmlspecialchars($_SERVER["PHP_SELF"]) ?>" method="post">
                            <input type="password" name="password" placeholder="Hasło dostępu" required>
                            <button type="submit" class="button">Odblokuj dostęp</button>
                        </form>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </main>
    </body>
</html>
<?php $conn->close(); ?>