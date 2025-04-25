<?php 
    include_once("server/functions.php");

    checkMaintenance();
    $user = validateUser("index.php");
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
    <script>

    </script>
</html>
<?php $conn->close(); ?>