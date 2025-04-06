<div class="service">
    <div class="header">
        <h2>403</h2>
        <h4>Brak dostępu</h4>
    </div>
    <div class="content">
        <p>Przepraszamy, ale nie masz dostępu do tej strony</p>
        <?php if(isset($_SESSION["accessError"])): ?>
            <p class='status red'><?= $_SESSION["accessError"] ?></p>
        <?php endif; ?>
    </div>
    <div class="buttons">
        <a class="button" href="index.php">Wróć do strony głównej</a>
    </div>
</div>