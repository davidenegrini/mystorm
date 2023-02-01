<?php
if (!$inside) die("Non hai i permessi necessari per accedere a questo file.");

$codiciinvitoregistrazione=array();

/* IMPOSTAZIONI */

$nomeapplicazione="MyStorageManager";	// app name
$dominioinstall="mystorm.it";			// domain name base

$mysql_dataconn["h"]="localhost";		// hostname
$mysql_dataconn["u"]="username";		// username
$mysql_dataconn["p"]="password";		// password
$mysql_dataconn["d"]="database";		// database

$codiciinvitoregistrazione[]="codice1";
$codiciinvitoregistrazione[]="codice2";

/* FINO A QUI */


session_start();

if ((!$paginaloginphp) && (!$paginaplacesphp)) {
	if ((!$_SESSION["mystorm"]) || ($_SESSION["utente"]["id"] == "") || ($_SESSION["utente"]["id"] == "0")) {
		header("Location: login.php");
		die();
	};
};
if (($paginaplacesphp) && ((!$_SESSION["mystorm"]) || ($_SESSION["utente"]["id"] == "") || ($_SESSION["utente"]["id"] == "0"))) {
	header("Location: login.php?replaces=".urlencode($_GET["iden"]));
	die();
};

/**********************************************************/

// da https://github.com/davidenegrini/lib_mysql/

$mysql_dataconn["n"]="3306";		// port (default: 3306)
$mysql_autostart=true;				// autostart mysql_on() at the first mysql_do() or mysql_es()
$mysql_printerrors=true;			// print all errors or keep them secret
$mysql_dieifstarterror=true;		// die() if error in mysql_on()
$mysql_ratelimit["a"]=false;		// rate limiter activation, works only in mysql_do()
$mysql_ratelimit["n"]=100;			// rate limiting after N queries if active
$mysql_ratelimit["t"]=4;			// rate limiter sleep(seconds) time
$mysql_resfree=false;				// use free() at the end mysql_do()

function mysql_on() {	// open mysql connection
	global $mysql_conn, $mysql_conn_active, $mysql_dataconn, $mysql_printerrors, $mysql_dieifstarterror;
	// check if already connected and then mysql_off()
	if ($mysql_conn_active) {
		mysql_off();
	};
	// connection
	$mysql_conn=mysqli_init();
	if (!$mysql_conn->real_connect($mysql_dataconn["h"], $mysql_dataconn["u"], $mysql_dataconn["p"], $mysql_dataconn["d"], $mysql_dataconn["n"])) {
		// error triggered
		if ($mysql_printerrors) {	//report
			echo("[MySQL_on error: (".mysqli_connect_errno().") ".mysqli_connect_error()."]");
		};
		if ($mysql_dieifstarterror) {	//die
			die();
		};
		return false;
	};
	// set active
	$mysql_conn_active=true;
	return true;
};

function mysql_off() {	// close mysql connection
	global $mysql_conn, $mysql_conn_active;
	$mysql_conn->close();
	$mysql_conn_active=false;
	return true;
};

function mysql_do($sql, $return=false, $many=false, $rlastid=false) {	// execute mysql query, if returns results $return=true, if returns bidimensional array $return=true and $many=true, if insert and need last primary key id $return=false and $many=false and $rlastid=true
	global $mysql_conn, $mysql_conn_active, $mysql_autostart, $mysql_printerrors, $mysql_ratelimit, $mysql_resfree;
	// check if active mysql connection
	if (!$mysql_conn_active) {
		if ($mysql_autostart) {
			mysql_on();
		} else {
			return false;
		};
	};
	// ratelimiter
	if ($mysql_ratelimit["a"]) {
		if (!isset($mysql_ratelimit["c"])) {
			$mysql_ratelimit["c"]=0;
		};
		$mysql_ratelimit["c"]++;
		if ($mysql_ratelimit["c"] > $mysql_ratelimit["n"]) {
			$mysql_ratelimit["c"]=0;
			sleep($mysql_ratelimit["t"]);
		};
	};
	// query
	$res=$mysql_conn->query($sql);
	if (!$res) { //error
		if ($mysql_printerrors) {	//report
			echo("[MySQL_do error: ".$mysql_conn->error." - ".$sql."]");
		};
		return false;
	};
	// results
	if ($return) {	// parse returned results as array
		if ($many) {	// bidimensional
			$finale=array();
			while ($row=$res->fetch_assoc()) {
				$finale[]=$row;
			};
		} else {		// only one row
			$finale=$res->fetch_assoc();
		};
	} else {		// only query execution
		if ($rlastid) {
			$finale=$mysql_conn->insert_id;
		} else {
			$finale=true;
		};
	};
	// close and return
	if ($mysql_resfree) {
		$res->free();
	};
	return $finale;
};

function mysql_es($in, $html=true) {	// you have to escape strings before using in mysql_do(), htmlspecialchars if $html=true
	global $mysql_conn, $mysql_conn_active, $mysql_autostart;
	// check if active mysql connection
	if (!$mysql_conn_active) {
		if ($mysql_autostart) {
			mysql_on();
		} else {
			return false;
		};
	};
	// other funcions
	if ($html) {	// (default)
		$temp=trim(htmlspecialchars($in));
	} else {		// nothing else
		$temp=$in;
	};
	//return
	return $mysql_conn->real_escape_string($temp);
};

/**********************************************************/

function grafica_htmlhead() {
	global $nomeapplicazione;
    ?><!doctype html>
<html>
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
		<title><?=$nomeapplicazione ?></title>

        <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/pure/3.0.0/pure-min.css">
        <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.6.3/jquery.min.js"></script>
		<script src="https://cdnjs.cloudflare.com/ajax/libs/qrcodejs/1.0.0/qrcode.min.js"></script>
		<script src="https://unpkg.com/html5-qrcode"></script>

		<style>
			#titolo, #contenuto {
				margin:5px;
			}
		</style>
    </head>
    <body>
		<h1 id="titolo"><?=$nomeapplicazione ?></h1>
		<div id="contenuto">
    <?php
};

function grafica_htmlfoot() {
    ?>
		</div>
    </body>
</html>

    <?php
};

/**********************************************************/

function puliscinomeutente($tx) {
	$txt=$tx;
	$txt=htmlspecialchars(str_replace(array(" ", "'", '"'), "", trim($txt)));
	return $txt;
};

?>