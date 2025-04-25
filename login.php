<?php 
    include_once("server/functions.php");
    global $conn, $cfg_discord;
    $redirect = $_SESSION["loginRedirect"] ?? null;
    $error = $_SESSION["loginError"] ?? null;
    
    $user = $_SESSION["user"] ?? null;
    checkMaintenance();
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
                <div class="login">
                    <div class="header">
                        <h2>Zaloguj się</h2>
                        <h4>Portal internetowy LSPD</h4>
                    </div>

                    <div class="content">
                        <?php if ($error): ?>
                            <p class="status red"><?= $error ?></p>
                        <?php endif; ?>
                        <?php if ($user): ?>
                            <p>Jesteś zalogowany jako @<?= $user["username"] ?></p>
                            <a href="logout.php" class="button">Wyloguj się</a>
                        <?php else: ?>
                            <a href="init-oauth.php" class="button">Autoryzuj przez Discord</a>
                        <?php endif; ?>
                    </div>

                    <div class="notes">
                        <p>Masz problem z logowaniem? <a href="<?= $cfg_discord["service_discord"] ?>">Skontaktuj się z administratorem</a></p>
                        <p>Logując się do serwisu akceptujesz <a href="terms.php">zasady korzystania z usługi</a></p>
                    </div>
                </div>
            </div>
        </main>
        
        <?php include ("components/footer.php") ?>
    </body>
</html>
<?php $conn->close(); ?>