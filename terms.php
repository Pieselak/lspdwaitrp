<?php 
    include_once("server/functions.php");
    $list = include("server/config/list-terms.php");
    global $conn;

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
                <div class="document">
                    <div class="header">
                        <h2><?= $list["title"] ?? null ?></h2>
                        <h5 class="text2"><?= $list["subtitle"] ?? null ?></h5>
                    </div>

                    <?php $sectionCount = 1; $localCount = 1; foreach ($list["sections"] as $section): ?>
                    <div class="item">
                        <?php if (isset($section["title"])): ?>
                        <div class="header">
                            <h3><span> <?= $sectionCount . "." ?> </span> <?= $section["title"] ?></h3>
                        </div>
                        <?php endif; ?>
                        <div class="content">
                            <?php foreach ($section["items"] as $item): ?>
                            <p><span><?= $sectionCount . "." . $localCount . "." ?></span> <?= $item ?? null ?></p>
                            <?php $localCount ++; endforeach; ?>
                        </div>
                    </div>
                    <?php $sectionCount++; $localCount = 1; endforeach; ?>
                </div>
            </div>
        </main>
        
        <?php include ("components/footer.php") ?>
    </body>
</html>
<?php $conn->close(); ?>