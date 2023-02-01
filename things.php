<?php
$inside=true;
require("librerie.php");

grafica_htmlhead();

mysql_on();

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
    //da creare nuovo oggetto nel luogo
    mysql_do("INSERT INTO things_list (places_id, identifier, regdate) VALUES ('".mysql_es($places["id"])."', '".mysql_es($getthing)."', now())");

    $ricarica=true;
};

//aggiorna dati da edit
if ($_GET["edit"]) {
    mysql_do("UPDATE things_list SET tname = '".mysql_es($_POST["tname"])."', tdesc = '".mysql_es($_POST["tdesc"])."', expi = ".(($_POST["expi"]!="")?(date("'Y-m-d'", strtotime(mysql_es($_POST["expi"])))):("NULL"))." WHERE id = '".$things["id"]."'");
    mysql_do("INSERT INTO things_log (things_id, users_id, log, regdate) VALUES ('".$things["id"]."', '".$_SESSION["utente"]["id"]."', 'Edit - Name ".mysql_es($_POST["tname"]).", Desc ".mysql_es($_POST["tdesc"]).", Expi ".mysql_es($_POST["expi"])."', now())");

    $ricarica=true;
};

//upload immagine
if ($_GET["imgup"]) {
    $cartellabase="upl/";
    $sottogenerato=md5(time())."/";
    mkdir($cartellabase.$sottogenerato);

    // based on https://stackoverflow.com/questions/18805497/php-resize-image-on-upload
    $maxDim = 500;
    $file_name = $_FILES['timg']['tmp_name'];
    list($width, $height, $type, $attr) = getimagesize( $file_name );
    if ( $width > $maxDim || $height > $maxDim ) {
        $src = imagecreatefromstring( file_get_contents( $file_name ) );

        $target_filename = $file_name;
        $ratio = $width/$height;
        if( $ratio > 1) {
            $new_width = $maxDim;
            $new_height = $maxDim/$ratio;
        } else {
            $new_width = $maxDim*$ratio;
            $new_height = $maxDim;
        };
        $dst = imagecreatetruecolor( $new_width, $new_height );
        imagecopyresampled( $dst, $src, 0, 0, 0, 0, $new_width, $new_height, $width, $height );
        imagedestroy( $src );

        // based on https://stackoverflow.com/questions/51993178/php-exif-orientation-how-to-overwrite-the-upload-image
        $exif = exif_read_data($file_name);
        if(!empty($exif['Orientation'])) {
            switch($exif['Orientation']) {
                case 8:
                    $dst = imagerotate($dst,90,0);
                    break;
                case 3:
                    $dst = imagerotate($dst,180,0);
                    break;
                case 6:
                    $dst = imagerotate($dst,-90,0);
                    break;
            };
        };

        imagepng( $dst, $target_filename );
        $addpng=".png";
        imagedestroy( $dst );
    } else {
        $addpng="";
    };

    $nomefile=preg_replace("/[^A-Za-z0-9.]+/", "", basename($_FILES['timg']['name']));
    if (move_uploaded_file($_FILES['timg']['tmp_name'], $cartellabase.$sottogenerato.$nomefile.$addpng)) {
        mysql_do("UPDATE things_list SET timg = '".mysql_es($sottogenerato.$nomefile.$addpng)."' WHERE id = '".$things["id"]."'");
        mysql_do("INSERT INTO things_log (things_id, users_id, log, regdate) VALUES ('".$things["id"]."', '".$_SESSION["utente"]["id"]."', 'Image upload - ".mysql_es($sottogenerato.$nomefile.$addpng)."', now())");
    } else {
        echo "<p>Errore di upload!</p>";
    };
    
    $ricarica=true;
};

//carica
if ($_GET["plus"]) {
    mysql_do("UPDATE things_list SET quant = quant+".floatval($_POST["quantplus"])." WHERE id = '".$things["id"]."'");
    mysql_do("INSERT INTO things_log (things_id, users_id, log, regdate) VALUES ('".$things["id"]."', '".$_SESSION["utente"]["id"]."', 'Plus: ".floatval($_POST["quantplus"])."', now())");

    $ricarica=true;
};

//scarica
if ($_GET["minus"]) {
    mysql_do("UPDATE things_list SET quant = quant-".floatval($_POST["quantminus"])." WHERE id = '".$things["id"]."'");
    mysql_do("INSERT INTO things_log (things_id, users_id, log, regdate) VALUES ('".$things["id"]."', '".$_SESSION["utente"]["id"]."', 'Minus: ".floatval($_POST["quantminus"])."', now())");

    $ricarica=true;
};

if ($ricarica) {
    //ricarica dato COPIA DA SOPRA
    $things=mysql_do("SELECT * FROM things_list WHERE places_id = '".mysql_es($places["id"])."' AND identifier = '".mysql_es($getthing)."'", true);
};
?>

<div class="pure-menu pure-menu-horizontal">
    <ul class="pure-menu-list">
        <li class="pure-menu-item"><a href="index.php" class="pure-menu-link">Home</a></li>
        <li class="pure-menu-item"><a href="places.php?iden=<?=$places["identifier"] ?>" class="pure-menu-link">Luogo (<?=$places["pname"] ?>)</a></li>
        <li class="pure-menu-item"><a href="things_log.php?place=<?=$places["id"] ?>&thing=<?=$things["identifier"] ?>" class="pure-menu-link">Log oggetto</a></li>
    </ul>
</div>

<div class="pure-g">
    <div class="pure-u-1-2">
        Quantità presente:<br>
        <h3><?=$things["quant"] ?></h3>
    </div>
    <div class="pure-u-1-2">
        <?php
            if ($things["expi"] != "") {
                $expitime=strtotime($things["expi"]);
                echo("Scadenza: ".date("Y-m-d", $expitime));
                $difftime=$expitime-time();
                if ($difftime < 0) {
                    echo("<br><h4>Scaduto!</h4>");
                } else {
                    $difftime=intval($difftime/(3600*24));
                    echo("<br>Scade tra <strong>".$difftime." giorni</strong>");
                };
            } else {
                echo("&nbsp;");
            };
            ?>
    </div>
</div>

<div class="pure-g">
    <div class="pure-u-1-2">
        <form action="things.php?place=<?=$places["id"] ?>&thing=<?=$things["identifier"] ?>&plus=true" method="post" class="pure-form pure-form-stacked">
            <fieldset>
                <label for="quantplus"><strong>Carica (+)</strong> quant.</label>
                <input type="number" name="quantplus" id="quantplus" value=1 min=0 step=0.01 required />
                <input type="submit" class="pure-button pure-button-primary" value="Carica (+)" />
            </fieldset>
        </form>
    </div>
    <div class="pure-u-1-2">
        <form action="things.php?place=<?=$places["id"] ?>&thing=<?=$things["identifier"] ?>&minus=true" method="post" class="pure-form pure-form-stacked">
            <fieldset>
                <label for="quantminus"><strong>Scarica (-)</strong> quant.</label>
                <input type="number" name="quantminus" id="quantminus" value=1 min=0 step=0.01 required />
                <input type="submit" class="pure-button" value="Scarica (-)" />
            </fieldset>
        </form>
    </div>
</div>

<hr>

<?php
if ($things["timg"] != "") {
    ?>
    <div>
        <img class="pure-img" src="upl/<?=$things["timg"] ?>" alt="image for <?=$things["identifier"] ?>" style="width:100%;max-width:500px">
    </div>
    <hr>
    <?php
};
?>

<p>Identifier: <?=$things["identifier"] ?></p>

<form action="things.php?place=<?=$places["id"] ?>&thing=<?=$things["identifier"] ?>&edit=true" method="post" class="pure-form pure-form-stacked">
    <fieldset>
        <label for="tname">Nome</label>
        <input type="text" name="tname" id="tname" placeholder="Nome oggetto" value="<?=$things["tname"] ?>" />
        <label for="tdesc">Descrizione</label>
        <input type="text" name="tdesc" id="tdesc" placeholder="Descrizione/Sottotitolo" value="<?=$things["tdesc"] ?>" />
        <label for="expi">Data di scadenza</label>
        <input type="date" name="expi" id="expi" value="<?php
            if ($things["expi"] != "")
                echo(date("Y-m-d", strtotime($things["expi"])));
        ?>" />
        <input type="submit" class="pure-button pure-button-primary" value="Aggiorna" />
    </fieldset>
</form>

<p><em>Data di prima attivazione: <?=$things["regdate"] ?></em></p>

<hr>

<h4>Modifica immagine</h4>

<form action="things.php?place=<?=$places["id"] ?>&thing=<?=$things["identifier"] ?>&imgup=true" method="post" class="pure-form" enctype="multipart/form-data">
    <fieldset>
        <input type="file" name="timg" accept="image/*" required>
        <input type="submit" class="pure-button pure-button-primary" value="Carica" />
    </fieldset>
</form>

<br>&nbsp;

<?php
grafica_htmlfoot();
?>