<?php 
    include_once ("php/functions.php");
    $announcement = getAnnouncement();

    if ($announcement["success"] && $announcement["message"]):
    $message = validate($announcement["message"], "format-tags");
        if ($message["success"]) {
            $message = $message["message"];
        } else {
            $message = "Błąd wyświetlania ogłoszenia";
        }?>
<div class="announcement">
    <i class='bx bx-bell'></i>
    <p><?= $message ?></p>
</div>
<?php endif; ?>