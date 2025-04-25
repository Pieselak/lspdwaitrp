<?php 
    require_once("server/functions.php");
    global $conn;
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
                        <h2>404</h2>
                        <h4>Nieznaleziono</h4>
                    </div>
                    <div class="content">
                        <p>Przepraszamy, ale wyszukana strona nie istnieje</p>
                    </div>
                    <div class="buttons">
                        <a href="index.php" class="button">Wróć do strony głównej</a>
                    </div>
                </div>
            </div>
        </main>
    </body>
</html>
<?php $conn->close(); ?>