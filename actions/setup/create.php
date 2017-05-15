<?php


$keyFile = "./data/ca/keys/cakey.pem";
$csrFile = "./data/ca/csr/cacert.csr";
$certFile = "./data/ca/cacerts/cacert.pem";
$indexFile = "./data/ca/index.txt";
$serialFile = "./data/ca/serial";
$crlFile = "./data/ca/crlnumber";
$configFile = "./config/config.php";
$opensslcnfsmpl = "./config/openssl.cnf.sample";
$opensslcnf = "./config/openssl.cnf";

$folders = array(
	'data',
	'data/ca',
	'data/ca/keys',
	'data/ca/csr',
	'data/ca/certs',
	'data/ca/newcerts',
	'data/ca/cacerts',
	'data/ca/revoked',
);

copy($opensslcnfsmpl, $iopensslcnf);

foreach ($folders as $pos=>$folder) {
	if (file_exists($folder)) {
		if (!is_dir($folder)) {
			print "Error, $folder is in the road, please remove it<br/>\n";
			return;
		}
		if (!touch("$folder/test")) {
			print "Error, $folder appears to have insufficient permissions for the current user to write to it.<br/>\n";
			return;
		}
		unlink("$folder/test");
	}
	else {
		mkdir($folder);
		if (!is_dir($folder)) {
			print "Couldn't create $folder, please check permissions<br/>\n";
			return;
		}
	}
}

if (get_magic_quotes_gpc()) {
	$passPhrase = stripslashes($_REQUEST['passPhrase']);
        $organizationName = stripslashes($_REQUEST['organizationName']);
        $emailAddress = stripslashes($_REQUEST['emailAddress']);
        $localityName = stripslashes($_REQUEST['localityName']);
        $stateOrProvinceName = stripslashes($_REQUEST['stateOrProvinceName']);
        $countryName = stripslashes($_REQUEST['countryName']);
        $days = stripslashes($_REQUEST['days']);
	while (list($key, $val) = each($_REQUEST['dn'])) {
		$dn[$key] = stripslashes($val);
	}
}
else {
	$passPhrase = &$_REQUEST['passPhrase'];
        $organizationName = &$_REQUEST['organizationName'];
        $emailAddress = &$_REQUEST['emailAddress'];
        $localityName = &$_REQUEST['localityName'];
        $stateOrProvinceName = &$_REQUEST['stateOrProvinceName'];
        $countryName = &$_REQUEST['countryName'];
        $days = &$_REQUEST['days'];
        $dn = &$_REQUEST['dn'];
}

$days = (int)$days ;
?>
<h1>Creating initial CA certificate</h1>

<p>
This is the point where we will generate the CA's certificate. The software will generate a key pair (a public key and a matching private key), and then sign it's own keypair, thus creating a self-signed certificate.
</p>

<p>
Now creating my own self-signed certificate... Please wait...
</p>
<?php
// Ok, lets go. Time to create us a CA Cert.
$errorCount = 0;

print "<b>Checking your DN (Distinguished Name)...</b><br/>";
print "<pre>DN = ".var_export($_REQUEST['dn'],1)."</pre>";

print "<b>Generating new key...</b><br/>";
$config = array(
    'config'=>'config/openssl.cnf',
    "digest_alg" => "sha512",
    "private_key_bits" => 4096,
    "private_key_type" => OPENSSL_KEYTYPE_RSA,
    "x509_extensions" => "v3_ca",
);
$privkey = openssl_pkey_new($config);
checkError($privkey);
print "Done<br/><br/>\n";

print "<b>Issuing CSR...</b><br/>";
$csr = openssl_csr_new($_REQUEST['dn'], $privkey, $config );
checkError($csr);
print "Done<br/><br/>\n";

print "<b>Exporting CSR...</b><br/>";
checkError(openssl_csr_export($csr, $myCSR));
print "Done<br/><br/>\n";

print "<b>Self-signing CSR...</b><br/>";
$sscert = openssl_csr_sign($csr, null, $privkey, $days, $config , getSerial());
checkError($sscert);
print "Done<br/><br/>\n";

print "<b>Exporting X509 Certificate...</b><br/>";
checkError(openssl_x509_export($sscert, $myCert));
print "Done<br/><br/>\n";

print "<b>Exporting encoded private key...</b><br/>";
checkError(openssl_pkey_export($privkey, $myKey, $passPhrase));
print "Done<br/><br/>\n";

print "<b>Saving your CSR...</b><br/>";
if ($fp = fopen($csrFile, 'w')) {
	fputs($fp, $myCSR) or $errorCount++;
	fclose($fp) or $errorCount++;
}
else $errorCount++;

if ($errorCount) {
	print "FATAL: an error occured in the script. Possibly due to inadequate file permissions.";
	exit();
}
print "Done<br/><br/>\n";

print "<b>Saving your certificate...</b><br/>";
if ($fp = fopen($certFile, 'w')) {
	fputs($fp, $myCert) or $errorCount++;
	fclose($fp) or $errorCount++;
}
else $errorCount++;

if ($errorCount) {
	print "FATAL: an error occured in the script. Possibly due to inadequate file permissions.";
	exit();
}
print "Done<br/><br/>\n";

print "<b>Saving your encoded key...</b><br/>";
if ($fp = fopen($keyFile, 'w')) {
	fputs($fp, $myKey) or $errorCount++;
	fclose($fp) or $errorCount++;
}
else $errorCount++;
if ($errorCount) {
	print "FATAL: an error occured in the script. Possibly due to inadequate file permissions.";
	exit();
}
print "Done<br/><br/>\n";

$date = date("Y-m-d H:i:s (Z)");
$quotedPassPhrase = addslashes($passPhrase);
$quotedOrganization = addslashes($organizationName);
$quotedContact = addslashes($emailAddress);
$quotedCity = addslashes($localityName);
$quotedState = addslashes($stateOrProvinceName);
$quotedCountry = addslashes($countryName);

$myConfig = <<<ENDE
<?php
// Locally generated configfile generated on $date
//

\$caSetup = true;
\$theme = 'default';
\$config['passPhrase'] = "$quotedPassPhrase";
\$config['orgName'] = "$quotedOrganization";
\$config['contact'] = "$quotedContact";
\$config['city'] = "$quotedCity";
\$config['state'] = "$quotedState";
\$config['country'] = "$quotedCountry";

ENDE;
$myConfig .= '?'.'>';

print "<b>Saving your index file...</b><br/>";
if ($fp = fopen($indexFile, 'w')) {
	fputs($fp, '');
	fclose($fp) or $errorCount++;
} else $errorCount++;
if ($errorCount) {
	print "FATAL: an error occured in the script. Possibly due to inadequate file permissions.";
	exit();
}
print "Done<br/><br/>\n";

print "<b>Saving your serial file...</b><br/>";
if ($fp = fopen($serialFile, 'w')) {
	fputs($fp, '1000') or $errorCount++;
	fclose($fp) or $errorCount++;
}
else $errorCount++;
if ($errorCount) {
	print "FATAL: an error occured in the script. Possibly due to inadequate file permissions.";
	exit();
}
if ($fp = fopen($crlFile, 'w')) {
        fputs($fp, '1000') or $errorCount++;
        fclose($fp) or $errorCount++;
}
else $errorCount++;
if ($errorCount) {
        print "FATAL: an error occured in the script. Possibly due to inadequate file permissions.";
        exit();
}
print "Done<br/><br/>\n";

print "<b>Saving your configuration file...</b><br/>";
if ($fp = fopen($configFile, 'w')) {
	fputs($fp, $myConfig) or $errorCount++;
	fclose($fp) or $errorCount++;
}
else $errorCount++;
if ($errorCount) {
	print "FATAL: an error occured in the script. Possibly due to inadequate file permissions.";
	exit();
}
print "Done<br/><br/>\n";



print "<b>Your certificate:</b>\n<pre>$myCert</pre>\n";
print "<b>Your key:</b>\n<pre>$myKey</pre>\n";

?>
<h1>Successfully created CA Certificate and CA Key.</h1>

<p>
Congratulations, you have now created your CA, and this site is live.
</p>

<p>
The next step would be for you to create your own personal certificate.<br/>
--&gt; <a href="index.php">Get a signed certificate</a>
</p>
