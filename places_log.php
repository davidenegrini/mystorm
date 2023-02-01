<?php
$inside=true;
$paginaplacesphp=true;
require("librerie.php");

//[PRIMA PARTE REPLICATA DA PLACES.PHP]

$getiden=str_replace($dominioinstall."/p.php?i=", "", trim(urldecode($_GET["iden"])));

grafica_htmlhead();

mysql_on();

//check se accesso con iden o con id e conseguente controllo permessi places
$places=mysql_do("SELECT * FROM places_list WHERE identifier = '".mysql_es($getiden)."'", true);

$auths=mysql_do("SELECT * FROM places_auth WHERE places_id = '".$places["id"]."' AND users_id = '".$_SESSION["utente"]["id"]."'", true);

if ($auths["plevel"] >= 1) { /*autorizzato*/ } else {
    ?>
    <p>Sembra tu non abbia i permessi per accedere a questo luogo.</p>
    <p><a class="pure-button" href="index.php">Torna alla home</a></p>
    <?php
    grafica_htmlfoot();
    die();
};
?>

<div class="pure-menu pure-menu-horizontal">
    <ul class="pure-menu-list">
        <li class="pure-menu-item"><a href="index.php" class="pure-menu-link">Home</a></li>
        <li class="pure-menu-item"><a href="places.php?iden=<?=$places["identifier"] ?>" class="pure-menu-link">Torna al luogo</a></li>
    </ul>
</div>

<h3>Log per <?=$places["identifier"] ?></h3>

<?php
$log=mysql_do("SELECT places_log.log as aslog, places_log.regdate as asregdate, users_list.mail as asmail FROM places_log, users_list WHERE places_log.places_id = '".$places["id"]."' AND places_log.users_id = users_list.id", true, true);
foreach($log as $line) {
    ?>
    <p><?=$line["aslog"] ?> (<?=$line["asregdate"] ?> - <?=$line["asmail"] ?>)</p>
    <?php
};
?>

<?php
grafica_htmlfoot();
?>