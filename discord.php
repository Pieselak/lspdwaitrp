<?php 
    include_once ("php/functions.php");
    $list = include("php/list-discord.php");

    checkMaintenance();
    $user = validateUser("discord.php");
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
                <div class="document">
                    <div class="header">
                        <h2><?= $list["title"] ?? null ?></h2>
                        <h5 class="text2"><?= $list["subtitle"] ?? null ?></h5>
                    </div>

                    <?php foreach ($list["sections"] as $section): ?>
                    <section>
                        <?php if (isset($section["title"])): ?>
                            <div class="header">
                                <h3><?= $section["title"] ?></h3>
                            </div>
                        <?php endif; ?>
                        <div class="content row">
                            <?php foreach ($section["items"] as $item): ?>
                                <div class="discord">
                                    <img src="assets/discord/<?= $item["image"] != "" ? $item["image"]  : "placeholder.png" ?>" alt="Discord's photo">
                                    <div>
                                        <h3><?= $item["name"] ?></h4>
                                        <a href="<?= $item["invite"] ?>" class="button small"><i class='bx bx-link'></i> Dołącz</a>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    </section>
                    <?php endforeach; ?>
                </div>
            </div>
        </main>
        
        <?php include ("components/footer.php") ?>
    </body>
</html>
<?php $conn->close(); ?>