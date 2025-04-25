<?php 
    include_once("server/functions.php");
    global $conn;

    checkMaintenance();
    $user = validateUser("settings.php");
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
                
            </div>
        </main>
        
        <?php include ("components/footer.php") ?>
    </body>
</html>
<?php $conn->close(); ?>