<?php 
    include_once ("php/functions.php");
    $componentAnnouncement = getAnnouncement();

    if ($componentAnnouncement["success"] && $componentAnnouncement["message"]):
        $message = validate($componentAnnouncement["message"], "format-tags");
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