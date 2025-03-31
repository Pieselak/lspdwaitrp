<?php 
    include_once ("php/functions.php");

    checkMaintenance();
    $user = validateUser("applications.php");
    $getPage = $_GET["page"] ?? null;
    $postPage = $_POST["page"] ?? null;
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
                <div class="apply">
                    <div class="header">
                        <h2>Rekrutacja</h2>
                        <div class="buttons">
                            <a href="profile.php" class="button small" page="results">Twoje wyniki</a>
                        </div>
                    </div>
                    <section class="academies apply">
                        <div class="header">
                            <h3>Dostępne akademie</h3>
                        </div>
                        <div class="content row">
                            <div class="item">
                                <h4>Akademia 20.01.2001 20:00 (#1)</h4>
                                <div class="field">
                                    <p class="small">Status:</p>
                                    <p class="small status green">Zapisy otwarte</p>
                                </div>
                                <div class="field">
                                    <p class="small">Pozostała ilość miejsc:</p>
                                    <p class="small status green">20</p>
                                </div>
                                <div class="field">
                                    <p class="small">Zamknięcie zapisów:</p>
                                    <p class="small status">01.01.2021 15:00</p>
                                </div>
                                <a href="application.php" class="button small">Zapisz się</a>
                            </div>

                            <div class="item">
                                <h4>Akademia 20.01.2001 20:00 (#2)</h4>
                                <div class="field">
                                    <p class="small">Status:</p>
                                    <p class="small status yellow">Zapisy wkróce zamykamy</p>
                                </div>
                                <div class="field">
                                    <p class="small">Pozostała ilość miejsc:</p>
                                    <p class="small status green">15</p>
                                </div>
                                <div class="field">
                                    <p class="small">Zamknięcie zapisów:</p>
                                    <p class="small status">01.01.2021 15:00</p>
                                </div>
                                <a href="application.php" class="button small">Zapisz się</a>
                            </div>

                            <div class="item">
                                <h4>Akademia 20.01.2001 20:00 (#3)</h4>
                                <div class="field">
                                    <p class="small">Status:</p>
                                    <p class="small status red">Zapisy zamknięte</p>
                                </div>
                                <div class="field">
                                    <p class="small">Pozostała ilość miejsc:</p>
                                    <p class="small status yellow">10</p>
                                </div>
                                <div class="field">
                                    <p class="small">Zamknięcie zapisów:</p>
                                    <p class="small status">01.01.2021 15:00</p>
                                </div>
                                <a href="application.php" class="button small disabled">Zapisy zamknięte</a>
                            </div>

                            <div class="item">
                                <h4>Akademia 20.01.2001 20:00 (#4)</h4>
                                <div class="field">
                                    <p class="small">Status:</p>
                                    <p class="small status orange">Odwołana</p>
                                </div>
                                <div class="field">
                                    <p class="small">Pozostała ilość miejsc:</p>
                                    <p class="small status orange">5</p>
                                </div>
                                <div class="field">
                                    <p class="small">Zamknięcie zapisów:</p>
                                    <p class="small status">01.01.2021 15:00</p>
                                </div>
                                <a href="application.php" class="button small disabled">Akademia niedostępna</a>
                            </div>

                            <div class="item">
                                <h4>Akademia 20.01.2001 20:00 (#5)</h4>
                                <div class="field">
                                    <p class="small">Status:</p>
                                    <p class="small status green">Nie wiem krasnal spadł z nieba</p>
                                </div>
                                <div class="field">
                                    <p class="small">Pozostała ilość miejsc:</p>
                                    <p class="small status red">0</p>
                                </div>
                                <div class="field">
                                    <p class="small">Zamknięcie zapisów:</p>
                                    <p class="small status">01.01.2021 15:00</p>
                                </div>
                                <a href="application.php" class="button small disabled">Brak wolnych miejsc</a>
                            </div>

                        </div>
                    </section>
                    <section class="applications apply">
                        <div class="header">
                            <h3>Twoje aplikacje</h3>
                        </div>
                        <div class="content">
                            <p>Nie napisałeś jeszcze żadnej aplikacji</p>
                        </div>
                    </section>
                    <section class="application apply">
                        <div class="header">
                            <h3>Aplikacja o przyjęcie do akademii</h3>
                            <a href="applications.php" class="button small">Powrót</a>
                        </div>
                        <div class="content">
                            
                        </div>
                    </section>
                </div>
                </div>
            </div>
        </main>
        
        <?php include ("components/footer.php") ?>
    </body>
</html>
<?php $conn->close(); ?>