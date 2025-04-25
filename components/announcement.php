<?php 
    include_once("server/functions.php");
    $componentAnnouncementMode = getAnnouncementMode();
    $componentAnnouncementContent = getAnnouncementContent();

    if ($componentAnnouncementContent["success"] && $componentAnnouncementMode["success"] && $componentAnnouncementMode["message"] == "1"):
        $message = validate($componentAnnouncementContent["message"], "string-tags");
        if ($message["success"]) {
            $message = $message["message"];
        } else {
            $message = "Błąd wyświetlania ogłoszenia";
        }
?>
<div class="announcement">
    <i class='bx bx-bell'></i>
    <p><?= $message ?></p>
</div>
<?php endif; ?>