<?php
$inside=true;
$paginaplacesphp=true;
require("librerie.php");

$getiden=str_replace($dominioinstall."/p.php?i=", "", trim(urldecode($_GET["iden"])));

grafica_htmlhead();

mysql_on();

//check se accesso con iden o con id e conseguente controllo permessi places
if (intval($_GET["id"]) > 0) {
    $places=mysql_do("SELECT * FROM places_list WHERE id = '".mysql_es(intval($_GET["id"]))."'", true);
} else {
    $places=mysql_do("SELECT * FROM places_list WHERE identifier = '".mysql_es($getiden)."'", true);
};

$auths=mysql_do("SELECT * FROM places_auth WHERE places_id = '".$places["id"]."' AND users_id = '".$_SESSION["utente"]["id"]."'", true);

if ($auths["plevel"] >= 1) { /*autorizzato*/ } else {
    ?>
    <p>Sembra tu non abbia i permessi per accedere a questo luogo.</p>
    <p><a class="pure-button" href="index.php">Torna alla home</a></p>
    <?php
    grafica_htmlfoot();
    die();
};

//edit delle impostazioni del luogo
if (($_GET["edit"]) && ($auths["plevel"] >= 3)) {
    mysql_do("UPDATE places_list SET pname = '".mysql_es($_POST["pname"])."', pdesc = '".mysql_es($_POST["pdesc"])."', pwhere = '".mysql_es($_POST["pwhere"])."', notes = '".mysql_es($_POST["notes"])."' WHERE id = '".intval($places["id"])."'");
    
    //inserisci log
    mysql_do("INSERT INTO places_log (places_id, users_id, log, regdate) VALUES ('".intval($places["id"])."', '".intval($_SESSION["utente"]["id"])."', 'Edit - Identifier: ".mysql_es($places["identifier"])." (fixed), Name: ".mysql_es($_POST["pname"]).", Desc: ".mysql_es($_POST["pdesc"]).", Where: ".mysql_es($_POST["pwhere"]).", Notes: ".mysql_es($_POST["notes"])."', now());");

    //ricarica il place visto l'aggiornamento
    $ricarica=true;
};

//edit delle impostazioni del luogo
if (($_GET["deactivate"]) && ($auths["plevel"] >= 3)) {
    mysql_do("UPDATE places_list SET active = 0 WHERE id = '".intval($places["id"])."'");
    
    //inserisci log
    mysql_do("INSERT INTO places_log (places_id, users_id, log, regdate) VALUES ('".intval($places["id"])."', '".intval($_SESSION["utente"]["id"])."', 'Deactivate - Identifier: ".mysql_es($places["identifier"])." (fixed)', now());");

    //ricarica il place visto l'aggiornamento
    $ricarica=true;
};

if ($ricarica) {
    //ricarica dato COPIA DA SOPRA
    $places=mysql_do("SELECT * FROM places_list WHERE id = '".$places["id"]."'", true);
};
?>

<div class="pure-menu pure-menu-horizontal">
    <ul class="pure-menu-list">
        <li class="pure-menu-item"><a href="index.php" class="pure-menu-link">Home</a></li>
        <li class="pure-menu-item"><a href="places_log.php?iden=<?=$places["identifier"] ?>" class="pure-menu-link">Log luogo</a></li>
    </ul>
</div>

<h3>Luogo<?=(($places["active"])?(""):(" (Disattivato!)")) ?>: <?=$places["pname"] ?></h4>
<p><strong>Identifier: <?=$places["identifier"] ?> <!--id:<?=$places["id"] ?>--> - <a href="#" onclick='$("#infoagg").toggle()'>Apri/Chiudi info</a></strong></p>

<div id="infoagg" style="display:none">
    <div id="qrcode"></div>
    <script type="text/javascript">
        new QRCode(document.getElementById("qrcode"), "<?=$dominioinstall ?>/p.php?i=<?=$places["identifier"] ?>");
    </script>
    <p><em>QR code da stampare per l'accesso diretto al luogo.</em></p>
    <br>
    
    <form action="places.php?iden=<?=$places["identifier"] ?><?=(($auths["plevel"] >= 3)?("&edit=true"):("")) ?>" method="post" class="pure-form pure-form-stacked">
        <fieldset>
            <label for="pname">Nome</label>
            <input type="text" name="pname" id="pname" placeholder="Nome luogo" value="<?=$places["pname"] ?>" required />
            <label for="pdesc">Descrizione</label>
            <input type="text" name="pdesc" id="pdesc" placeholder="Descrizione/Sottotitolo" value="<?=$places["pdesc"] ?>" />
            <label for="pwhere">Localizzazione</label>
            <input type="text" name="pwhere" id="pwhere" placeholder="Indicazioni su dove trovarlo" value="<?=$places["pwhere"] ?>" />
            <label for="notes">Note</label>
            <input type="text" name="notes" id="notes" placeholder="Note aggiuntive" value="<?=$places["notes"] ?>" />
            <?php
            if ($auths["plevel"] >= 3) {
                ?>
                <input type="submit" class="pure-button pure-button-primary" value="Aggiorna" />
                <?php
            };
            ?>
        </fieldset>
    </form>
    <br>
    <p>Data attivazione: <?=$places["regdate"] ?></p>
    <p><em>Stato: <?=(($places["active"])?("Attivo"):("Disattivo")) ?></em></p>
</div>

<hr>

<h3>Scan oggetto</h3>

<?php
if ($_SESSION["scanphone"]) {
    ?>
    <div style="width: 400px; max-width:95%;" id="reader"></div>
    <script>
        var html5QrcodeScanner = new Html5QrcodeScanner("reader", { fps: 10, qrbox: 320 });
        function onScanSuccess(decodedText, decodedResult) {
            html5QrcodeScanner.clear();
            window.location.href = "things.php?place=<?=$places["id"] ?>&thing="+encodeURIComponent(decodedText);
        }
    </script>
    <div id="scanchiuso">
        <p><a href="#" onclick='$("#scanchiuso").hide();html5QrcodeScanner.render(onScanSuccess);'>Apri scansione</a>
    </div>
    <?php
} else {
    ?>
    <form class="pure-form" method="get" action="things.php">
        <fieldset>
            <input type="hidden" name="place" value="<?=$places["id"] ?>" />
            <input type="text" name="thing" placeholder="abc123..." />
            <input type="submit" class="pure-button pure-button-primary" value="Vai">
        </fieldset>
    </form>
    <?php
};
?>

<hr>

<h3>Inventario</h3>

<div id="invechiuso">
    <p><a href="#" onclick='$("#invechiuso").hide();$("#inveaperto").show();'>Apri sezione</a>
</div>
<div id="inveaperto" style="display:none">
    
    <table class="pure-table pure-table-bordered pure-table-striped">
        <thead>
            <tr>
                <th>Iden.</th>
                <th>Nome</th>
                <th>Scad.</th>
                <th>Quant.</th>
            </tr>
        </thead>
        <tbody>
            <?php
            $inv=mysql_do("SELECT * FROM things_list WHERE places_id = '".$places["id"]."' AND active = 1 AND quant > 0 ORDER BY tname, identifier", true, true);
            foreach($inv as $thi) {
                ?>
                <tr>
                    <td><a href="things.php?place=<?=$places["id"] ?>&thing=<?=$thi["identifier"] ?>"><?=$thi["identifier"] ?></a></td>
                    <td><?php
                        if ($thi["quant"] <= 0) {
                            echo('<span style="color:purple">'.$thi["tname"].'</span>');
                        } else {
                            echo($thi["tname"]);
                        };
                    ?></td>
                    <td><?php
                        if ($thi["expi"] != "") {
                            $expi=strtotime($thi["expi"]);
                            if ($expi < time()) {
                                echo('<span style="color:red">'.date('Y-m-d', $expi).'</span>');
                            } elseif ($expi < (time()+(3600*24*7))) {
                                echo('<span style="color:orange">'.date('Y-m-d', $expi).'</span>');
                            } elseif ($expi < (time()+(3600*24*30))) {
                                echo('<span style="color:olive">'.date('Y-m-d', $expi).'</span>');
                            } else {
                                echo('<span style="color:green">'.date('Y-m-d', $expi).'</span>');
                            };
                        };
                    ?></td>
                    <td><?php
                        if ($thi["quant"] <= 0) {
                            echo('<span style="color:purple">'.$thi["quant"].'</span>');
                        } else {
                            echo($thi["quant"]);
                        };
                    ?></td>
                </tr>
                <?php
            };
            ?>
        </tbody>
    </table>

</div>

<hr>

<h3>Autorizzazioni</h3>

<?php
//rimozione utente
if (($auths["plevel"] >= 3) && ($_GET["removeuserid"] > 0) && ($_GET["removeuserid"] != $_SESSION["utente"]["id"])) {
    mysql_do("DELETE FROM places_auth WHERE places_id = '".$places["id"]."' AND users_id = '".mysql_es(intval($_GET["removeuserid"]))."'");

    //inserisci log
    mysql_do("INSERT INTO places_log (places_id, users_id, log, regdate) VALUES (".$places["id"]."', '".$_SESSION["utente"]["id"]."', 'Delete auth user - UserID: ".mysql_es($_GET["removeuserid"])."', now());");
};

//aggiunta utente
if (($auths["plevel"] >= 3) && ($_GET["adduser"]) && ($_POST["nuovamail"] != "")) {
    $nuovamail=mysql_do("SELECT * FROM users_list WHERE mail = '".mysql_es($_POST["nuovamail"])."'", true);
    $livello=2;
    if ($nuovamail["id"]>0) {
        mysql_do("INSERT INTO places_auth (places_id, users_id, plevel, regdate) VALUES ('".$places["id"]."', '".$nuovamail["id"]."', '".$livello."', now())");

        //inserisci log
        mysql_do("INSERT INTO places_log (places_id, users_id, log, regdate) VALUES (".$places["id"]."', '".$_SESSION["utente"]["id"]."', 'Add auth user - UserID: ".mysql_es($_GET["removeuserid"]).", Level: ".$livello.", Mail: ".mysql_es($nuovamail["mail"])."', now());");
    };
};
?>

<div id="authchiuso">
    <p><a href="#" onclick='$("#authchiuso").hide();$("#authaperto").show();'>Apri sezione</a>
</div>
<div id="authaperto" style="display:none">
    
    <table class="pure-table pure-table-bordered pure-table-striped">
        <thead>
            <tr>
                <th>Email</th>
                <th>Nome</th>
                <th>Livello</th>
                <?php
                if ($auths["plevel"] >= 3) {
                    ?>
                    <th>&nbsp;</th>
                    <?php
                };
                ?>
            </tr>
        </thead>
        <tbody>
            <?php
            $aut=mysql_do("SELECT places_auth.plevel as asplevel, places_auth.users_id as asusers_id, users_list.mail as asmail, users_list.name as asname FROM places_auth, users_list WHERE places_auth.places_id = '".$places["id"]."' AND places_auth.plevel > 0 AND places_auth.users_id = users_list.id ORDER BY asplevel, asmail", true, true);
            foreach($aut as $use) {
                ?>
                <tr>
                    <td><?=$use["asmail"] ?></td>
                    <td><?=$use["asname"] ?></td>
                    <td><?=$use["asplevel"] ?></td>
                    <?php
                    if ($auths["plevel"] >= 3) {
                        ?>
                        <td><a href="places.php?iden=<?=$places["identifier"] ?>&removeuserid=<?=$use["asusers_id"] ?>">Elimina</a></td>
                        <?php
                    };
                    ?>
                </tr>
                <?php
            };
            ?>
        </tbody>
    </table>

    <?php
    if ($auths["plevel"] >= 3) {
        ?>
        <h5>Nuova autorizzazione</h5>

        <form class="pure-form pure-form-aligned" method="post" action="places.php?iden=<?=$places["identifier"] ?>&adduser=true">
            <fieldset>
                <legend>Inserire l'email di un utente registrato. Se l'utente non è registrato non verrà data l'autorizzazione richiesta.</legend>
                <div class="pure-control-group">
                    <label for="nuovamail">Email</label>
                    <input type="email" name="nuovamail" id="nuovamail" placeholder="Email" required />
                </div>
                <div class="pure-controls">
                    <input type="submit" class="pure-button pure-button-primary" value="Aggiungi" />
                </div>
            </fieldset>
        </form>
        <?php
    };

    if (($auths["plevel"] >= 3) && ($places["active"] > 0)) {
        ?>
        <hr>

        <h5>Disattiva il luogo</h5>
        <p>La disattivazione è irreversibile ma non comporta una vera cancellazione del luogo, se non dalla lista in home dei luoghi a cui si è abilitati.</p>
        <p><a href="places.php?iden=<?=$places["identifier"] ?>&deactivate=true">Sono sicuro, disattiva il luogo</a></p>
        <?php
    };
    ?>

</div>

<?php
grafica_htmlfoot();
?>