<?php
$inside=true;
require("librerie.php");

grafica_htmlhead();
?>

<div class="pure-menu pure-menu-horizontal">
    <ul class="pure-menu-list">
        <li class="pure-menu-item"><a href="index.php" class="pure-menu-link">Home</a></li>
    </ul>
</div>

<h3>Cambia password</h3>

<?php
if (($_GET["n"]) && ($_POST["newpw"] != "")) {
    mysql_on();
    mysql_do("UPDATE users_list SET pass = '".md5($_POST["newpw"])."' WHERE id = '".$_SESSION["utente"]["id"]."'");
    echo("<h5>Eseguito!</h5>");
};
?>

<form action="userpw.php?n=true" method="post" class="pure-form pure-form-stacked">
    <fieldset>
        <label for="newpw">Nuova password</label>
        <input type="password" name="newpw" id="newpw" required />
        <input type="submit" class="pure-button pure-button-primary" value="Cambia" />
    </fieldset>
</form>

<?php
grafica_htmlfoot();
?>