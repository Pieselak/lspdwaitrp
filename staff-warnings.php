<?php 
    include_once("server/functions.php");

    checkMaintenance();
    $access = validateUserAccess("staff-warnings", "staff-warnings.php", false);
    $user = $access["user"];
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
                <?php if ($access["access"]): ?>

                <?php else: ?>
                    <?php include ("components/noaccess.php") ?>
                <?php endif; ?>
            </div>
        </main>
        
        <?php include ("components/footer.php") ?>
    </body>
</html>
<?php $conn->close(); ?>