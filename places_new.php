<?php
$inside=true;
require("librerie.php");

grafica_htmlhead();

if (($_GET["n"]) && ($_POST["pname"] != "")) {
    mysql_on();
    $identifier=md5(time()."mysotrm".$_SESSION["utente"]["id"]);

    //Check non esista lo stesso identificativo, cosa tipo impossibile
    $r=mysql_do("SELECT id FROM places_list WHERE identifier='".mysql_es($identifier)."'", true);
	if (intval($r["id"]) > 0) die("Errore inaspettato ed imprevedibile. Riprovare.");

    //Inserisci luogo
    $newid=mysql_do("INSERT INTO places_list (identifier, pname, pdesc, pwhere, notes, regdate) VALUES ('".mysql_es($identifier)."', '".mysql_es($_POST["pname"])."', '".mysql_es($_POST["pdesc"])."', '".mysql_es($_POST["pwhere"])."', '".mysql_es($_POST["notes"])."', now())", false, false, true);

    //Inserisci autorizzazione come amministratore = 3
    mysql_do("INSERT INTO places_auth (places_id, users_id, plevel, regdate) VALUES ('".intval($newid)."', '".intval($_SESSION["utente"]["id"])."', '3', now());");

    //inserisci log
    mysql_do("INSERT INTO places_log (places_id, users_id, log, regdate) VALUES ('".intval($newid)."', '".intval($_SESSION["utente"]["id"])."', 'New - Identifier: ".mysql_es($identifier).", Name: ".mysql_es($_POST["pname"]).", Desc: ".mysql_es($_POST["pdesc"]).", Where: ".mysql_es($_POST["pwhere"]).", Notes: ".mysql_es($_POST["notes"])."', now());");
    ?>

    <h4>Luogo <?=mysql_es($_POST["name"]) ?> creato con successo!</h4>

    <p>Identificativo univoco: <strong><?=$identifier ?></strong></p>

    <div id="qrcode"></div>
    <script type="text/javascript">
        new QRCode(document.getElementById("qrcode"), "<?=$dominioinstall ?>/p.php?i=<?=$identifier ?>");
    </script>

    <p>Puoi stampare questo codice qr ed attaccarlo nel luogo di interesse per accedere più facilmente a questo sito ed ai suoi contenuti.</p>

    <p><a class="pure-button pure-button-primary" href="index.php">Torna alla home</a></p>

    <?php
    grafica_htmlfoot();
    die();
};
?>

<div class="pure-menu pure-menu-horizontal">
    <ul class="pure-menu-list">
        <li class="pure-menu-item"><a href="index.php" class="pure-menu-link">Home</a></li>
    </ul>
</div>

<h3>Nuovo luogo</h3>

<form action="places_new.php?n=true" method="post" class="pure-form pure-form-stacked">
    <fieldset>
        <label for="pname">Nome</label>
        <input type="text" name="pname" id="pname" placeholder="Nome luogo" required />
        <label for="pdesc">Descrizione</label>
        <input type="text" name="pdesc" id="pdesc" placeholder="Descrizione/Sottotitolo" />
        <label for="pwhere">Localizzazione</label>
        <input type="text" name="pwhere" id="pwhere" placeholder="Indicazioni su dove trovarlo" />
        <label for="notes">Note</label>
        <input type="text" name="notes" id="notes" placeholder="Note aggiuntive" />
        <input type="submit" class="pure-button pure-button-primary" value="Crea" />
    </fieldset>
</form>

<?php
grafica_htmlfoot();
?>