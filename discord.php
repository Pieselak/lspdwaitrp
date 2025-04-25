<?php
    include_once("server/functions.php");
    global $conn;

    checkMaintenance();
    $user = validateUser("discord.php");
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
                        <h2>Lista serwerów discord</h2>
                        <h5 class="text2">Wait Roleplay Fivem</h5>
                    </div>
                </div>
            </div>
        </main>
        
        <?php include ("components/footer.php") ?>
    </body>
    <script>
        document.addEventListener("DOMContentLoaded", function() {
            function fetchDiscords() {
                fetch("actions/getDiscords.php")
                    .then(response => {
                        if (!response.ok) {
                            throw new Error("Nie udało się pobrać listy departamentów" + response.status);
                        }
                        return response.json();
                    })
                    .then(data => {
                        if (data.success) {
                            const discords = data.discords;

                            if (discords.length === 0) {
                                throw new Error("Nie znaleziono żadnych serwerów Discord w bazie danych");
                            } else {
                                const documentContainer = document.querySelector(".document");
                                const itemsContainer = document.createElement("div");
                                itemsContainer.classList.add("item");
                                itemsContainer.innerHTML = `
                                <div class="content row tech" id="discords">
                                </div>
                                `;
                                documentContainer.appendChild(itemsContainer);
                                const discordsContainer = document.getElementById("discords");

                                discords.forEach(discord => {
                                    const discordElement = document.createElement("div");
                                    discordElement.classList.add("tech");
                                    discordElement.innerHTML = `
                                    <img src="assets/discord/${discord.image}" alt="Discord's photo">
                                    <div>
                                        <h4>${discord.name}</h4>
                                        <a href="https://discord.com/invite/${discord.invite}" class="button small"><i class='bx bx-link'></i> Dołącz</a>
                                    </div>
                                `;
                                    discordsContainer.appendChild(discordElement);
                                });
                            }
                        } else {
                            throw new Error(data.message);
                        }
                    })
                    .catch(error => {
                        console.error("Wystąpił błąd:", error);
                        createAlert({
                            title: "Wystąpił błąd",
                            content: error,
                            color: "red",
                            closable: false
                        });
                    });
            }

            fetchDiscords();
        })
    </script>
</html>
<?php $conn->close(); ?>