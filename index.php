<?php
$inside=true;
require("librerie.php");

grafica_htmlhead();
mysql_on();

//cambio metodo scansione, se da lettore qr smartphone o se da lettore barcode esterno (impostazioen modificata da menu appena qui sotto)
if ($_GET["cambioscan"]) {
    if ($_SESSION["scanphone"]) {
        $_SESSION["scanphone"]=false;
    } else {
        $_SESSION["scanphone"]=true;
    };
};
?>

<h4>Ciao, <?=$_SESSION["utente"]["name"] ?>!</h4>

<div class="pure-menu pure-menu-horizontal">
    <ul class="pure-menu-list">
        <?php
        //apri i link per permettere il cambio se da lettore qr smartphone o se da lettore barcode esterno
        if ($_SESSION["scanphone"]) {
            ?>
            <li class="pure-menu-item"><a href="index.php?cambioscan=true" class="pure-menu-link">Passa a scan. lettore esterno</a></li>
            <?php
        } else {
            ?>
            <li class="pure-menu-item"><a href="index.php?cambioscan=true" class="pure-menu-link">Passa a scan. fotocamera smartphone</a></li>
            <?php
        };
        ?>
        <li class="pure-menu-item"><a href="userpw.php" class="pure-menu-link">Cambia password</a></li>
        <li class="pure-menu-item"><a href="login.php?out=true" class="pure-menu-link">Logout</a></li>
    </ul>
</div>

<hr>

<h3>Scan luogo</h3>

<?php
if ($_SESSION["scanphone"]) {
    ?>
    <div style="width: 400px; max-width:95%;" id="reader"></div>
    <script>
        var html5QrcodeScanner = new Html5QrcodeScanner("reader", { fps: 10, qrbox: 320 });
        function onScanSuccess(decodedText, decodedResult) {
            html5QrcodeScanner.clear();
            var idenscan=decodedText.replace("mystorm.it/p.php?i=", "");
            window.location.href = "places.php?iden="+encodeURIComponent(idenscan);
        }
    </script>
    <div id="scanchiuso">
        <p><a href="#" onclick='$("#scanchiuso").hide();html5QrcodeScanner.render(onScanSuccess);'>Apri scansione</a>
    </div>
    <?php
} else {
    ?>
    <form class="pure-form" method="get" action="places.php">
        <fieldset>
            <input type="text" name="iden" placeholder="abc123..." />
            <input type="submit" class="pure-button pure-button-primary" value="Vai">
        </fieldset>
    </form>
    <?php
};
?>

<hr>

<h3>Lista luoghi</h3>

<div class="pure-menu pure-menu-horizontal">
    <ul class="pure-menu-list">
        <li class="pure-menu-item"><a href="places_new.php" class="pure-menu-link">Nuovo luogo</a></li>
    </ul>
</div>

<br>

<table class="pure-table pure-table-bordered pure-table-striped">
        <thead>
            <tr>
                <th>Luogo</th>
                <th>Descrizione</th>
                <th>&nbsp;</th>
            </tr>
        </thead>
        <tbody>
            <?php
            $places=mysql_do("SELECT places_list.identifier AS asiden, places_list.pname AS asname, places_list.pdesc AS asdesc FROM places_list, places_auth WHERE places_auth.users_id = '".$_SESSION["utente"]["id"]."' AND places_list.id = places_auth.places_id AND places_list.active = 1 AND places_auth.plevel > 0", true, true);
            foreach($places as $pl) {
                ?>
                <tr>
                    <td><?=$pl["asname"] ?></td>
                    <td><?=$pl["asdesc"] ?></td>
                    <td><a href="places.php?iden=<?=$pl["asiden"] ?>">Apri</a></td>
                </tr>
                <?php
            };
            ?>
        </tbody>
    </table>

<?php
grafica_htmlfoot();
?>