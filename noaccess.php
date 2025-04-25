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
                <?php include ("components/noaccess.php") ?>
            </div>
        </main>
    </body>
</html>
<?php $conn->close(); ?>