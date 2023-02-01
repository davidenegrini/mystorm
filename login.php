<?php
$inside=true;
$paginaloginphp=true;

require_once("librerie.php");

if (($_GET["in"]) && ($_POST["mail"] != "") && ($_POST["pass"] != "")) {
	$km=addslashes(trim(puliscinomeutente($_POST["mail"])));
	$kp=md5($_POST["pass"]);
	
	mysql_on();
	$r=mysql_do("SELECT * FROM users_list WHERE mail='".mysql_es($km)."' AND pass='".$kp."'", true);
	if (intval($r["id"]) > 0) {
		$_SESSION["mystorm"]=true;
		$_SESSION["scanphone"]=true;
		$_SESSION["utente"]=$r;

		mysql_off();
		if($_GET["replaces"] != "") {
			header("Location: places.php?iden=".urlencode($_GET["replaces"]));
		} else {
			header("Location: index.php");
		};
		die();
	} else {
		mysql_off();
		header("Location: login.php?mess=err");
		die();
	};
};

if (($_GET["reg"]) && ($_POST["name"] != "") && ($_POST["mail"] != "") && ($_POST["pass"] != "")) {
	$kn=addslashes(trim(puliscinomeutente($_POST["name"])));
	$km=addslashes(trim(puliscinomeutente($_POST["mail"])));
	$kp=md5($_POST["pass"]);
	
	mysql_on();
	$r=mysql_do("SELECT * FROM users_list WHERE mail='".mysql_es($km)."'", true);
	if (intval($r["id"]) > 0) {
		mysql_off();
		header("Location: login.php?mess=errreg&plus=giapresente");
		die();
	} else {
		
		//check invito
		if (!in_array($_POST["codice"], $codiciinvitoregistrazione)) {
			mysql_off();
			header("Location: login.php?mess=errreg&plus=regcode");
			die();
		};

		mysql_do("INSERT INTO users_list (mail, pass, name, regdate) VALUES ('".mysql_es($km)."', '".$kp."', '".mysql_es($kn)."', now())");
		mysql_off();

		header("Location: login.php?mess=reg");
		die();
	};
};

if (($_GET["out"]) || ($_SESSION["mystorm"])) {
	$_SESSION["mystorm"]=false;
	$_SESSION["utente"]=false;
	session_destroy();
    header("Location: login.php?mess=out");
	die();
};

grafica_htmlhead();
?>

<?php
if ($_GET["mess"] == "err") {
	?>
	<h4>Errore</h4>
	<p><strong>Utente o password errati</strong></p>
	<?php
};
if ($_GET["mess"] == "errreg") {
	?>
	<h4>Errore</h4>
	<p><strong>Errore in fase di registrazione</strong></p>
	<?php
};
if ($_GET["mess"] == "out") {
	?>
	<p><strong>Logout eseguito</strong></p>
	<?php
};
if ($_GET["mess"] == "reg") {
	?>
	<p><strong>Registrato, ora si può accedere</strong></p>
	<?php
};
?>

<h2>Accesso</h2>
<div>
	<form action="login.php?in=true<?=(($_GET["replaces"]!="")?("&replaces=".urlencode($_GET["replaces"])):("")) ?>" method="post" class="pure-form pure-form-aligned">
		<fieldset>
			<div class="pure-control-group">
				<label for="mail">Email</label>
				<input name="mail" id="mail" type="email" placeholder="Email" required>
			</div>
			<div class="pure-control-group">
				<label for="pass">Password</label>
				<input name="pass" id="pass" type="password" placeholder="Password" required>
			</div>
			<div class="pure-controls">
				<input type="submit" value="Accedi" class="pure-button pure-button-primary">
			</div>
		</fieldset>
        </form>
    </div>
</div>

<p><small>In caso di password dimenticata contattare l'assistenza.</small></p>

<hr>

<h2>Registrazione</h2>
<div>
	<form action="login.php?reg=true" method="post" class="pure-form pure-form-aligned">
		<fieldset>
			<div class="pure-control-group">
				<label for="name">Nome</label>
				<input name="name" id="name" type="text" placeholder="Nome" required>
			</div>
			<div class="pure-control-group">
				<label for="mail">Email</label>
				<input name="mail" id="mail" type="email" placeholder="Email" required>
			</div>
			<div class="pure-control-group">
				<label for="pass">Password</label>
				<input name="pass" id="pass" type="password" placeholder="Password" required>
			</div>
			<div class="pure-control-group">
				<label for="codice">Codice invito</label>
				<input name="codice" id="codice" type="text" placeholder="Codice invito" required>
			</div>
			<div class="pure-controls">
				<input type="submit" value="Registrati" class="pure-button pure-button-primary">
			</div>
		</fieldset>
        </form>
    </div>
</div>

<?php
grafica_htmlfoot();
?>