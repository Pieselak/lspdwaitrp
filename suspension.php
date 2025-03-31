<?php 
    include_once ("php/functions.php");

    checkMaintenance();
    $user = validateUserBasic("index.php");

    $suspensions = getUserSuspensions($user["id"]);
    $suspension;

    if ($suspensions["success"]) {
        foreach ($suspensions["suspensions"] as $s) {
            if ($s["statusId"] == 1) {
                $suspension = $s;
                break;
            }
        }
    } else {
        $suspension = false;
    }

    if (!$suspension) {
        redirectTo("index.php");
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
                        <h2>Twoje konto zostało zawieszone</h2>
                        <h4 class="text2">Skontaktuj się z administratorem, aby uzyskać więcej informacji.</h4>
                    </div>
                    <div class="content">
                        <div class="info">
                            <div>
                                <p class="small">Identyfikator:</p>
                                <p><?= $suspension["suspensionId"] ?> | @<?= $suspension["userUsername"] ?></p>
                            <div>
                                <p class="small">Powód:</p>
                                <p><?= $suspension["reason"] ?></p>
                            </div>
                            <div>
                                <p class="small">Wygasa w dniu:</p>
                                <p><?= $suspension["isPermanent"] ? "Nigdy (Permanentna)" : formatDate($suspension["expiresAt"], "datetime") ?></p>
                            </div>
                            <div>
                                <p class="small">Nadana w dniu:</p>
                                <p><?= formatDate($suspension["issuedAt"], "datetime") ?></p>
                            </div>
                            <div>
                                <p class="small">Nadana przez:</p>
                                <p>@<?= $suspension["issuerUsername"] ?></p>
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