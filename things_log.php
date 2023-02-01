<?php
$inside=true;
require("librerie.php");

grafica_htmlhead();

mysql_on();

//[PRIMA PARTE REPLICATA DA THINGS.PHP]

//PARTE DEL CHECK PLACE [COPIA DA PLACES.PHP]
//check se accesso con iden o con id e conseguente controllo permessi places
$places=mysql_do("SELECT * FROM places_list WHERE id = '".mysql_es(intval($_GET["place"]))."'", true);

$auths=mysql_do("SELECT * FROM places_auth WHERE places_id = '".$places["id"]."' AND users_id = '".$_SESSION["utente"]["id"]."'", true);

if ($auths["plevel"] >= 1) { /*autorizzato*/ } else {
    ?>
    <p>Sembra tu non abbia i permessi per accedere a questo luogo.</p>
    <p><a class="pure-button" href="index.php">Torna alla home</a></p>
    <?php
    grafica_htmlfoot();
    die();
};

$getthing=trim(urldecode($_GET["thing"]));

//carica dato iniziale
$things=mysql_do("SELECT * FROM things_list WHERE places_id = '".mysql_es($places["id"])."' AND identifier = '".mysql_es($getthing)."'", true);

if ($things["id"] > 0) { /*ok presente*/ } else {
    ?>
    <p>Oggetto inesistente.</p>
    <p><a class="pure-button" href="index.php">Torna alla home</a></p>
    <?php
    grafica_htmlfoot();
    die();
};
?>

<div class="pure-menu pure-menu-horizontal">
    <ul class="pure-menu-list">
        <li class="pure-menu-item"><a href="index.php" class="pure-menu-link">Home</a></li>
        <li class="pure-menu-item"><a href="places.php?iden=<?=$places["identifier"] ?>" class="pure-menu-link">Luogo (<?=$places["pname"] ?>)</a></li>
        <li class="pure-menu-item"><a href="things.php?place=<?=$places["id"] ?>&thing=<?=$things["identifier"] ?>" class="pure-menu-link">Torna all'oggetto</a></li>
    </ul>
</div>

<h3>Log per <?=$things["identifier"] ?></h3>

<?php
$log=mysql_do("SELECT things_log.log as aslog, things_log.regdate as asregdate, users_list.mail as asmail FROM things_log, users_list WHERE things_log.things_id = '".$things["id"]."' AND things_log.users_id = users_list.id", true, true);
foreach($log as $line) {
    ?>
    <p><?=$line["aslog"] ?> (<?=$line["asregdate"] ?> - <?=$line["asmail"] ?>)</p>
    <?php
};
?>

<?php
grafica_htmlfoot();
?>