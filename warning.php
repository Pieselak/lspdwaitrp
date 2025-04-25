<?php 
    include_once("server/functions.php");
    global $conn;

    checkMaintenance();
    $user = validateUserBasic("index.php");

    if ($_SERVER["REQUEST_METHOD"] == "POST") {
        if (isset($_POST["accept"])) {
            $warningId = $_POST["warning_id"];
            $accept = acceptWarning($warningId);
            if ($accept["success"]) {
                redirectTo("index.php");
            } else {
                $error = $accept["message"];
            }
        }
    }

    $warnings = getUserWarnings($user["id"]);

    if ($warnings["success"]) {
        foreach ($warnings["warnings"] as $w) {
            if ($w["status_id"] == 1 && $w["is_accepted"] == 0) {
                $warning = $w;
                break;
            }
        }
    }
    $warning = $warning ?? false;

    if (!$warning) {
        redirectTo("index.php");
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
                <div class="service">
                    <div class="header">
                        <h2>Twoje konto otrzymało ostrzeżenie</h2>
                        <h4 class="text2">Otrzymanie kolejnych ostrzeżeń może skutkować zawieszeniem Twojego konta.</h4>
                    </div>
                    <div class="content">
                        <?php if (isset($error)): ?>
                            <div class="info">
                                <p><?= $error ?></p>
                            </div>
                        <?php endif; ?>
                        <div class="info">
                            <div>
                                <p class="small">Identyfikator:</p>
                                <p>#<?= $warning["warning_id"] ?> | @<?= $warning["user_username"] ?></p>
                            </div>
                            <div class="separator"></div>
                            <div>
                                <p class="small">Powód:</p>
                                <p><?= $warning["reason"] ?></p>
                            </div>
                            <div class="separator"></div>
                            <div>
                                <p class="small">Nadane w dniu:</p>
                                <p><?= $warning["issued_at"] ?></p>
                            </div>
                            <div class="separator"></div>
                            <div>
                                <p class="small">Nadane przez:</p>
                                <p>@<?= $warning["issuer_username"] ?></p>
                            </div>
                        </div>
                    </div>
                    <div class="buttons">
                        <form method="POST" action="<?= htmlspecialchars($_SERVER["PHP_SELF"]) ?>">
                            <input type="hidden" name="warning_id" value="<?= $warning["warning_id"] ?>">
                            <button type="submit" name="accept" class="button">Akceptuj ostrzeżenie</button>
                        </form>
                        <a href="logout.php" class="button">Wyloguj się</a>
                    </div>
                </div>
            </div>
        </main>
        
        <?php include ("components/footer.php") ?>
    </body>
</html>
<?php $conn->close(); ?>