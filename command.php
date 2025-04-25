<?php 
    include_once ("server/functions.php");
    global $conn;

    checkMaintenance();
    $user = validateUser("command.php");
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
                        <h2>Zarząd departamentów</h2>
                        <h5 class="text2">City of Los Santos, State of San Andreas</h5>
                    </div>
                </div>
            </div>
        </main>
        
        <?php include ("components/footer.php") ?>
    </body>
    <script>
        document.addEventListener("DOMContentLoaded", function() {
            function fetchOfficersDepartments() {
                fetch("actions/getOfficersDepartments.php")
                    .then(response => {
                        if (!response.ok) {
                            throw new Error("Nie udało się pobrać listy departamentów" + response.status);
                        }
                        return response.json();
                    })
                    .then(data => {
                        if (data.success) {
                            const departments = data.departments;

                            if (departments.length === 0) {
                                throw new Error("Nie znaleziono żadnych departamentów w bazie danych");
                            } else {
                                const officersContainer = document.querySelector(".document");
                                departments.forEach(department => {
                                    const departmentElement = document.createElement("div");
                                    departmentElement.classList.add("item");
                                    departmentElement.innerHTML = `
                                    <div class="header">
                                        <h3>${department.name}</h3>
                                    </div>
                                    <div class="content row tech" id="department_${department.id}">
                                    </div>
                                    `;
                                    officersContainer.appendChild(departmentElement);
                                });
                            }
                        } else {
                            throw new Error("Nie można pobrać danych o oficerach: " + data.message);
                        }
                    })
                    .then(() => fetchOfficers())
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

            function fetchOfficers() {
                fetch("actions/getOfficers.php")
                    .then(response => {
                        if (!response.ok) {
                            throw new Error("Nie udało się pobrać listy officerów - " + response.status);
                        }
                        return response.json();
                    })
                    .then(data => {
                        if (data.success) {
                            const officers = data.officers;
                            if (officers.length === 0) {
                                throw new Error("Nie znaleziono żadnych oficerów w bazie danych");
                            } else {
                                officers.forEach(officer => {
                                    const departmentElement = document.getElementById(`department_${officer.department_id}`);
                                    if (officer.department_id === 3) {
                                        console.log(officer);
                                        console.log(departmentElement);
                                    }
                                    if (departmentElement) {
                                        const officerElement = document.createElement("a");
                                        officerElement.classList.add("tech");
                                        officerElement.href = `https://discord.com/users/${officer.discord_id}`;
                                        officerElement.innerHTML = `
                                        <img src="assets/officers/${officer.image}" alt="Officer's photo">
                                        <h4 class="officer_name">(${officer.badge}) ${officer.name}</h4>
                                        <p class="officer_rank">${officer.rank}</p>
                                    `;
                                        departmentElement.appendChild(officerElement);
                                    }
                                });
                            }
                        } else {
                            throw new Error("Nie można pobrać danych o oficerach: " + data.message);
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

            fetchOfficersDepartments();
        })
    </script>
</html>
<?php $conn->close(); ?>