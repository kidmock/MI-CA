<?php

$cwd = getcwd();
$config = array(
	'config' => "$cwd/config/openssl.cnf",
	'dir' => "$cwd/data/ca",
);


if ( empty($theme) ) $theme = 'default';

function checkError($result) {
	if (!$result) {
		while (($error = openssl_error_string()) !== false) {
			if ($error == "error:0E06D06C:configuration file routines:NCONF_get_string:no value") {
				if ($nokeyError++ == 0) {
					$errors .= "One or more configuration variables could not be found (possibly non-fatal)<br/>\n";
				}
			}
			else {
				$errorCount++;
				$errors .= "Error $errorCount: $error<br/>\n";
			}
		}
	}
	if ($errorCount or (!$result and $nokeyError)) {
		print "FATAL: An error occured in the script. Possibly due to a misconfiguration.<br/>\nThe following errors were reported during execution:<br/>\n$errors";
		exit();
	}
}
	
function getSerial() {
	$fp = fopen("./data/ca/serial", "r");
	list($serial) = fscanf($fp, "%x");
	fclose($fp);

	$fp = fopen("./data/ca/serial", "w");
	fputs($fp, sprintf("%04X", $serial + 1) . chr(0) . chr(10) );
	fclose($fp);
	return $serial + 1;
}

function printHeaderbar() {
	global $config;
	global $theme;
	$name = "";
	if ($org = $config['orgName']) { $name = "<small>For: $org.</small>"; }
        if ( empty($theme) ) $theme = 'default';
	print <<<ENDE
<section class="header">
    <div class="container">
        <div class="row">
            <div class="col-xs-6">
                <a href="index.php?type=main"><img class="logo" src="themes/$theme/img/logo.svg" alt="$org" /></a>
            </div>
            <div class="col-xs-6 header--title">
            </div>
        </div>
    </div>
</section>

<section class="title--main">
    <div class="container">
        <h1>Certificate Authority</h1>
    </div>
</section>
</header>

<div class="main" role="main">
<section class="section--main">

ENDE;
}

function printHeader($title = "", $inhead = "") {
	global $config;
	global $theme;
	if ($org = $config['orgName']) { $name = "<small>For: $org.</small>"; }
        if ( empty($theme) ) $theme = 'default';
	print <<<ENDE

<!doctype html>
<html class="no-js" lang="en">

<head>
<meta charset="utf-8" />
<meta name="viewport" content="width=device-width, initial-scale=1.0" />
<title>$org :: $title</title>
<!-- Favicons -->
<link rel="icon" type="image/png" href="themes/$theme/img/favicon.png">
<link rel="stylesheet" href="themes/$theme/css/main.css" />
<link rel="stylesheet" href="themes/$theme/css/ca.css" />
$inhead
</head>
<body>
<header>

ENDE;
	printHeaderbar();
        print "<div class=\"container\">\n";
}

function printFooter() {
        print <<<ENDE
</section></div>
    <footer>
        <section class="footer">
            
        </section>
    </footer>
</body>

</html>

ENDE;
}


function rand_string( $length ) {

    $chars = "abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789";
    return substr(str_shuffle($chars),0,$length);

}
?>
