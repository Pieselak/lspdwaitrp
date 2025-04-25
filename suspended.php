<?php 
    include_once("server/functions.php");

    checkMaintenance();
    $user = validateUserBasic("index.php");

    $suspensions = getUserSuspensions($user["id"]);

    if ($suspensions["success"]) {
        foreach ($suspensions["suspensions"] as $s) {
            if ($s["status_id"] == 1) {
                $suspension = $s;
                break;
            }
        }
    }
    $suspension = $suspension ?? false;

    if (!$suspension) {
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
                        <h2>Twoje konto zostało zawieszone</h2>
                        <h4 class="text2">Skontaktuj się z administratorem, aby uzyskać więcej informacji.</h4>
                    </div>
                    <div class="content">
                        <div class="info">
                            <div>
                                <p class="small">Identyfikator:</p>
                                <p>#<?= $suspension["suspension_id"] ?> | @<?= $suspension["user_username"] ?></p>
                            </div>
                            <div class="separator"></div>
                            <div>
                                <p class="small">Powód:</p>
                                <p><?= $suspension["reason"] ?></p>
                            </div>
                            <div class="separator"></div>
                            <div>
                                <p class="small">Wygasa w dniu:</p>
                                <p><?= $suspension["is_permanent"] ? "Nigdy (Permanentna)" : $suspension["expires_at"] ?></p>
                            </div>
                            <div class="separator"></div>
                            <div>
                                <p class="small">Nadana w dniu:</p>
                                <p><?= $suspension["issued_at"] ?></p>
                            </div>
                            <div class="separator"></div>
                            <div>
                                <p class="small">Nadana przez:</p>
                                <p>@<?= $suspension["issuer_username"] ?></p>
                            </div>
                        </div>
                    </div>
                    <div class="buttons">
                        <a href="logout.php" class="button">Wyloguj się</a>
                    </div>
                </div>
            </div>
        </main>
        
        <?php include ("components/footer.php") ?>
    </body>
</html>
<?php $conn->close(); ?>