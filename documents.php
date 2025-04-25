<?php 
    include_once("server/functions.php");
    $list = include("server/config/list-documents.php");
    $getPage = $_GET["page"] ?? null;
    
    checkMaintenance();
    $user = validateUser("documents.php");
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
                        <div class="nav">
                            <?php foreach ($list["sections"] as $section): ?>
                                <a href="documents.php?page=<?= $section["page"] ?>" class="button small"><?= $section["title"] ?></a>
                            <?php endforeach; ?>
                        </div>
                    </div>
                    
                    <?php $found = false; foreach ($list["sections"] as $section): 
                        if ($section["page"] == $getPage): $found = true;?>
                        <div class="item">
                            <?php if (isset($section["title"])): ?>
                            <div class="header">
                                <h3><?= $section["title"] ?></h3>
                            </div>
                            <?php endif; ?>
                            <div class="content">
                                <?php foreach ($section["items"] as $item): ?>
                                    <p><?= $item ?></p>
                                <?php endforeach; ?>
                            </div>
                        </div>
                        <?php endif;
                    endforeach; ?>

                    <?php if (!$found): ?>
                        <section>
                            <div class="header">
                                <h3>Nieznaleziono dokumentu</h3>
                            </div>
                            <div class="content">
                                <p>Brak dokumentów do wyświetlenia<?= $getPage != null ? " dla wyszukiwania: " . htmlspecialchars($getPage) : "" ?></p>
                            </div>
                        </section>
                    <?php endif; ?>
                </div>
            </div>
        </main>
        
        <?php include ("components/footer.php") ?>
    </body>
</html>
<?php $conn->close(); ?>