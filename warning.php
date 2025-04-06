<?php 
    include_once ("php/functions.php");

    checkMaintenance();
    $user = validateUserBasic("index.php");

    if ($_SERVER["REQUEST_METHOD"] == "POST") {
        if (isset($_POST["accept"])) {
            $warningId = $_POST["warningId"];
            $accept = acceptWarning($warningId);
            if ($accept["success"]) {
                redirectTo("index.php");
            } else {
                $error = $accept["message"];
            }
        }
    }

    $warnings = getUserWarnings($user["id"]);
    $warning;

    if ($warnings["success"]) {
        foreach ($warnings["warnings"] as $w) {
            if ($w["statusId"] == 1 && $w["isAccepted"] == 0) {
                $warning = $w;
                break;
            }
        }
    } else {
        $warning = false;
    }

    if (!$warning) {
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
                                <p>#<?= $warning["warningId"] ?> | @<?= $warning["userUsername"] ?></p>
                            </div>
                            <div>
                                <p class="small">Powód:</p>
                                <p><?= $warning["reason"] ?></p>
                            </div>
                            <div>
                                <p class="small">Nadana w dniu:</p>
                                <p><?= formatDate($warning["issuedAt"], "datetime") ?></p>
                            </div>
                            <div>
                                <p class="small">Nadana przez:</p>
                                <p><?= $warning["issuerUsername"] ?></p>
                            </div>
                        </div>
                    </div>
                    <div class="buttons">
                        <form method="POST" action="<?= htmlspecialchars($_SERVER["PHP_SELF"]) ?>">
                            <input type="hidden" name="warningId" value="<?= $warning["warningId"] ?>">
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