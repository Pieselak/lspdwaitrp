<?php 
    include_once ("php/functions.php");

    checkMaintenance();
    $access = validateUserAccess("staff-admin", "staff-admin.php", false);
    $user = $access["user"];
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
                <?php if ($access["access"]): ?>
                    <div class="staff">
                        <div class="name">
                            <h2>Panel administracyjny</h2>
                        </div>
                        <div class="item">
                            <div class="header">
                                <h3>Baner informacyjny</h3>
                                <div class="content">
                                    <form method="POST" action="<?= htmlspecialchars($_SERVER["PHP_SELF"]) ?>">
                                        <textarea name="announcement" placeholder="Wpisz treść banera informacyjnego (zostaw pusty aby nie był widoczny)"></textarea>
                                </div>
                            </div>
                        </div>
                    </div>
                <?php else: ?>
                    <?php include ("components/noaccess.php") ?>
                <?php endif; ?>
            </div>
        </main>
        
        <?php include ("components/footer.php") ?>
    </body>
</html>
<?php $conn->close(); ?>