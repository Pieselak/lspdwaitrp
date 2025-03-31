<?php 
include_once ("php/functions.php");
if (!isset($user)) {
    $user = $_SESSION["user"] ?? null;
}
?>
<header>
    <div class="logo">
        <img src="assets/logo.png" alt="Logo">
        <div class="logo-text">
            <h3>LSPD</h3>
            <h5 class="normal">WaitRP</h5>
        </div>
    </div>
    <button class="menu-button">
        <i class='bx bx-menu menu-icon'></i>
    </button>
    <div class="navbar">
        <nav>
            <a href="index.php"><i class='bx bx-home'></i>Strona główna</a>
            <a href="applications.php"><i class='bx bx-task'></i>Dołącz do LSPD</a>
            <div class="dropdown">
                <button class="dropdown-button" expanded="false">
                    <a><i class='bx bx-group'></i>O nas</a>
                    <i class='bx bx-chevron-down toggle-icon transition'></i>
                </button>
                <div class="dropdown-content">
                    <a href="command.php"><i class='bx bx-group'></i>Zarząd departamentu</a>
                    <a href="discord.php"><i class='bx bx-link'></i>Serwery discord</a>
                    <div class="dropdown-divider"></div>
                    <a href="terms.php"><i class='bx bx-file-find'></i>Warunki korzystania z usługi</a>
                </div>
            </div>
            <div class="dropdown">
                <button class="dropdown-button" expanded="false">
                    <a><i class='bx bx-book-alt'></i>Dokumenty</a>
                    <i class='bx bx-chevron-down toggle-icon transition'></i>
                </button>
                <div class="dropdown-content">
                    <a href="documents.php?page=skargi"><i class='bx bx-help-circle'></i>Skargi i odwołania</a>
                    <div class="dropdown-divider"></div>
                    <a href="documents.php?page=przebieg-akademii"><i class='bx bx-book-alt'></i>Przebieg akademiii</a>
                    <a href="documents.php?page=regulamin-akademii"><i class='bx bx-book'></i>Regulamin akademii</a>
                    <a href="documents.php?page=kompendium-wiedzy"><i class='bx bx-book-bookmark'></i>Kompendium wiedzy</a>
                    <div class="dropdown-divider"></div>
                    <a href="documents.php?page=pozwolenie-na-bron"><i class='bx bx-id-card'></i>Pozwolenie na broń</a>
                </div>
            </div>
            <?php
                $navStaffPages = ["staff-admin", "staff-applications", "staff-academies", "staff-bans", "staff-warnings", "staff-users", "staff-logs"];
                $navStaffAccess = false;
                foreach ($navStaffPages as $navStaffPage) {
                    if (checkUserPageAccess($navStaffPage)) {
                        $navStaffAccess = true;
                        break;
                    }
                }

                if ($navStaffAccess):?>
            <div class="dropdown">
                <button class="dropdown-button" expanded="false">
                    <a><i class='bx bx-search-alt' ></i>Narzędzia personelu</a>
                    <i class='bx bx-chevron-down toggle-icon transition'></i>
                </button>
                <div class="dropdown-content">
                    <?php if (checkUserPageAccess("staff-admin")): ?><a href="staff-admin.php"><i class='bx bx-shield-quarter'></i>Administrator</a><?php endif; ?>
                    <?php if (checkUserPageAccess("staff-applications")): ?><a href="staff-applications.php"><i class='bx bx-file'></i>Aplikacje</a><?php endif; ?>
                    <?php if (checkUserPageAccess("staff-academies")): ?><a href="staff-academies.php"><i class='bx bx-calendar-edit'></i>Akademie</a><?php endif; ?>
                    <?php if (checkUserPageAccess("staff-suspensions")): ?><a href="staff-suspensions.php"><i class='bx bx-block'></i>Zawieszenia</a><?php endif; ?>
                    <?php if (checkUserPageAccess("staff-warnings")): ?><a href="staff-warnings.php"><i class='bx bx-error'></i>Ostrzeżenia</a><?php endif; ?>
                    <?php if (checkUserPageAccess("staff-users")): ?><a href="staff-users.php"><i class='bx bx-group'></i>Użytkownicy</a><?php endif; ?>
                    <?php if (checkUserPageAccess("staff-logs")): ?><a href="staff-logs.php"><i class='bx bx-spreadsheet'></i>Dziennik zdarzeń</a><?php endif; ?>
                </div>
            </div>
            <?php endif; ?>
        </nav>
        <div class="profile">
            <div class="dropdown">
                <button class="dropdown-button" expanded="false">
                    <div class="profile-wrapper">
                        <?= $user ? "<img src='" .  $user['avatar'] . "' alt='Profile' class='profile-image'>" : "" ?>
                        <div class="profile-info">
                            <h4><?= $user ? "@" . $user["username"] : "Zaloguj się" ?></h4>
                            <h5 class="normal"><?= $user ? $user["role_name"] : "Zobacz więcej" ?></h5>
                        </div>
                        <i class='bx bx-chevron-down toggle-icon'></i>
                    </div>
                </button>
                <div class="dropdown-content">
                    <?php if ($user): ?>
                        <a href="profile.php"><i class='bx bx-user'></i> Mój profil</a>
                        <a href="settings.php"><i class='bx bx-cog'></i> Ustawienia</a>
                    <?php endif; ?>
                    <button class="theme-button"><i class='bx bx-sun theme-icon'></i> <span class="theme-text">Zmień motyw</span></button>
                    <div class="dropdown-divider"></div>
                    <?php if(!$user): ?>
                        <a href="login.php"><i class='bx bx-log-in'></i> Zaloguj się</a>
                    <?php endif; ?>
                    <?php if($user): ?>
                        <a href="logout.php" class="logout-link"><i class='bx bx-log-out'></i> Wyloguj się</a>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</header>