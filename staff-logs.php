<?php 
    include_once("server/functions.php");
    global $conn;

    checkMaintenance();
    $access = validateUserAccess("staff-logs", "staff-logs.php", false);
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
                    <div class="staff">
                        <div class="header">
                            <h2 onclick="createAlert({title: 'TEST', content: 'Testujemy wszystko', icon: 'bx-server', color: 'red'})">Narzędzia personelu - Dziennik zdarzeń</h2>
                        </div>
                        <div class="item">
                            <div class="header">
                                <h3 id="count"></h3>
                                <div class="buttons">
                                    <select id="category" class="small">
                                        <option value="0">wszystkie</option>
                                        <?php
                                            $categories = getLogsCategories();
                                            if ($categories["success"]) {
                                                foreach ($categories["categories"] as $category): ?>
                                                    <option value="<?= $category["action_id"] ?>"><?= $category["action_name"] ?></option>";
                                                <?php endforeach;
                                            }
                                        ?>
                                    </select>
                                    <input id="search" type="text" placeholder="Wyszukaj..." class="small">
                                </div>
                            </div>
                            <div class="content" id="logs">
                                <p class="status primary">Ładowanie...</p>
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
    <script>
        document.addEventListener("DOMContentLoaded", () => {
            const logsCount = document.getElementById("count");
            const logsContainer = document.getElementById("logs");
            const searchInput = document.getElementById("search");
            const categoryInput = document.getElementById("category");
            const canDelete = <?= json_encode(checkUserPermission("staff-logs", "delete-logs")) ?>;
            let allLogs = [];

            if (searchInput && categoryInput) {
                function filterLogs() {
                    const searchValue = searchInput.value.toLowerCase();
                    const categoryValue = Number(categoryInput.value);

                    const filteredLogs = allLogs.filter(log => {
                        const matchesSearch = log.user_id.toString().includes(searchValue) ||
                            log.user_username.toString().includes(searchValue) ||
                            log.action_message.toLowerCase().includes(searchValue) ||
                            log.action_name.toLowerCase().includes(searchValue);
                        const matchesCategory = categoryValue === 0 || log.action_id === categoryValue;

                        return matchesSearch && matchesCategory;
                    });

                    displayLogs(filteredLogs);
                }

                searchInput.addEventListener("input", filterLogs);
                categoryInput.addEventListener("change", filterLogs);
            }

            function deleteLog(logid) {
                function deleteLogConfirmed(logid) {
                    fetch(`actions/deleteLog.php?id=${logid}`, {
                        method: "DELETE"
                    })
                        .then(response => {
                            if (!response.ok) {
                                throw new Error("Nieudało się usunąć zapisu z dziennika - " + response.status);
                            }
                            return response.json();
                        })
                        .then(data => {
                            if (!data["success"]) {
                                throw new Error("Błąd podczas usuwania zapisu: " + data["message"]);
                            }
                            createAlert({
                                title: "Usunięto zapis",
                                content: "Zapis został pomyślnie usunięty.",
                                color: "green",
                            });
                            fetchLogs();
                        })
                        .catch(error => {
                            console.error("Wystąpił błąd:", error);
                            createAlert({
                                title: "Wystąpił błąd",
                                content: error,
                                color: "red",
                            })
                        });
                }

                createAlert({
                    title: "Usuwanie zapisu",
                    content: "Czy na pewno chcesz usunąć ten zapis?",
                    icon: "bx-trash",
                    color: "red",
                    buttons: [
                        {
                            text: "Tak, usuń",
                            class: "red",
                            callback: () => {
                                deleteLogConfirmed(logid);
                            }
                        },
                        {
                            text: "Anuluj",
                            class: "green"
                        }
                    ]
                });
            }
            window.deleteLog = deleteLog;


            async function fetchLogs() {
                try {
                    const response = await fetch(`actions/getLogs.php`);
                    if (!response.ok) {
                        logsContainer.innerHTML = `<p class="status red">Nie można pobrać zapisów z dziennika zdarzeń<br>${response.status} - ${response.statusText}</p>`;
                        throw new Error("Nieudało się pobrać zapisów z dziennika - " + response.status);
                    }

                    const data = await response.json();
                    if (!data["success"]) {
                        logsContainer.innerHTML = `<p class="status red">Nie można pobrać zapisów z dziennika zdarzeń<br>${data["message"]}</p>`;
                        return;
                    }

                    data["logs"].forEach(log => {
                        let logMessage = log.action_message;
                        logMessage = logMessage.replace("{user}", log.user_id);
                        logMessage = logMessage.replace("{details}", `<b>${log.log_details}</b>`);
                        log.action_message = logMessage;
                    })

                    allLogs = data["logs"];
                    displayLogs(allLogs);
                } catch (error) {
                    console.error("Wystąpił błąd:", error);
                }
            }

            function displayLogs(logs) {
                const logCount = logs.length;
                logsCount.textContent = `Zdarzenia (${logCount})`;
                logsContainer.innerHTML = "";

                if (logCount === 0) {
                    logsContainer.innerHTML = `<p class="status primary">Brak zapisów w dzienniku zdarzeń</p>`;
                    return;
                }

                logs.forEach(log => {
                    const logItem = document.createElement("div");
                    logItem.className = "log";
                    logItem.innerHTML = `
                        <div class="log-header">
                            <p class="small status ${log.action_color}"><i class="bx ${log.action_icon}"></i>${log.action_name}</p>
                            <p class="small status"><i class="bx bx-user"></i>@${log.user_username}</p>
                            <p class="small status"><i class="bx bx-calendar"></i>${log.log_date}</p>
                            ${canDelete ? `<button class="small status red delete" onclick="deleteLog(${Number(log.log_id)})"><i class="bx bx-trash"></i></button>` : ""}
                        </div>
                        <div class="log-content">
                            <p>${log.action_message}</p>
                        </div>
                    `;
                    logsContainer.appendChild(logItem);
                });
            }

            fetchLogs();
        });
    </script>
</html>
<?php $conn->close();