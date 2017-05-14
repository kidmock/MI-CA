<h1>Signed Certificate Creation</h1>

<?php
include_once("./include/common.php");
@include_once("./config/config.php");


if (get_magic_quotes_gpc()) {
        $certName = stripslashes($_REQUEST['cn']);
        $certBits = stripslashes($_REQUEST['keylen']);
        $md = stripslashes($_REQUEST['md']);
        $days = stripslashes($_REQUEST['days']);

        $organizationName = stripslashes($_REQUEST['o']);
        $organizationUnit = stripslashes($_REQUEST['ou']);
        $emailAddress = stripslashes($_REQUEST['mail']);
        $localityName = stripslashes($_REQUEST['l']);
        $stateOrProvinceName = stripslashes($_REQUEST['st']);
        $countryName = stripslashes($_REQUEST['c']);
     
}
else {
        $certName = &$_REQUEST['cn'];

        $certBits = &$_REQUEST['keylen'];
        $md = &$_REQUEST['md'];
        $days = &$_REQUEST['days'];

        $organizationName = &$_REQUEST['o'];
        $organizationUnit = &$_REQUEST['ou'];
        $emailAddress = &$_REQUEST['mail'];
        $localityName = &$_REQUEST['l'];
        $stateOrProvinceName = &$_REQUEST['st'];
        $countryName = &$_REQUEST['c'];
}
$keyFile = './data/ca/keys/' . $emailAddress . '.key' ;
if (file_exists($keyFile)) {
    print "<p><b>Key ". $keyFile . "already exists and needs to be removed</b></p>\n";
    print "<a href=\"index.php\">Return to Main Page</a>\n";
    exit();
}
$csrFile = './data/ca/csr/' . $emailAddress . '.csr' ;
if (file_exists($csrFile)) {
    print "<p><b>CSR already exists and needs to be removed</b></p>\n";
    print "<a href=\"index.php\">Return to Main Page</a>\n";
    exit();
}

$certFile = './data/ca/certs/' . $emailAddress . '.crt' ;
if (file_exists($certFile)) {
    print "<p><b>Certificate already exists and needs to be revoked</b></p>\n";
    print "<a href=\"index.php\">Return to Main Page</a>\n";
    exit();
}

if ( empty($md) ) $md = 'sha512';
if ( empty($days) ) $days = 365;

$san = "email:" . $emailAddress ;


$keysize = $certBits;
if ( empty($keysize) ) $keysize = '4096';

$keyparams = array();

$keyparams['private_key_type'] = 'OPENSSL_KEYTYPE_RSA' ;
$keyparams['digest_alg'] = $md ;
$keyparams['private_key_bits'] = (int)$keysize ;

$configfile = 'config/openssl.cnf';

$dn = array();
$dn['commonName'] = $certName ;

if (!empty($emailAddress)) { $dn['emailAddress'] = $emailAddress ; }
if (!empty($organizationName)) { $dn['organizationName'] = $organizationName ; }
if (!empty($organizationUnit)) { $dn['organizationalUnitName'] = $organizationUnit ; }
if (!empty($localityName)) { $dn['localityName'] = $localityName ; }
if (!empty($stateOrProvinceName)) { $dn['stateOrProvinceName'] = $stateOrProvinceName ; }
if (!empty($countryName)) { $dn['countryName'] = $countryName ; }

print "<b>Checking your DN (Distinguished Name)...</b><br/>";
print "<pre>DN = ".var_export($dn,1)."</pre>";

print "<b>Generating new key...</b><br/>";
print "<p>openssl genpkey -algorithm RSA -out " . $emailAddress .".key -pkeyopt rsa_keygen_bits:". $keysize . "</p>\n";
$privkey = openssl_pkey_new($keyparams);
checkError($privkey);

openssl_pkey_export($privkey, $myKey);

if ($fp = fopen($keyFile, 'w')) {
        fputs($fp, $myKey) or $errorCount++;
        fclose($fp) or $errorCount++;
}
print "Done<br/><br/>\n";


       if (file_exists($keyFile)) {
                $myKey = join("", file($keyFile));

                //print "<b>Your Key:</b>\n<pre>$myKey</pre>\n";
                print "<p><a href=\"$keyFile\"download=\"" . $emailAddress . ".key\">Download Key</a></p>\n";
        }
        else {
                print "<h1>RSA Key Not Found</h1>\n";
        }

print "<b>Creating CSR...</b><br/>";
print "<p>openssl req -config " . $configfile . " -out " . $emailAddress . ".csr -key " . $emailAddress . ".key -new</p>\n";
$csr = openssl_csr_new($dn, $privkey, $config );
checkError($csr);

openssl_csr_export($csr, $myCSR);
if ($fp = fopen($csrFile, 'w')) {
        fputs($fp, $myCSR) or $errorCount++;
        fclose($fp) or $errorCount++;
}
print "Done<br/><br/>\n";


       if (file_exists($csrFile)) {
                $myCSR = join("", file($csrFile));

                //print "<b>Your CSR:</b>\n<pre>$myCSR</pre>\n";
                print "<p><a href=\"$csrFile\" download=\"" . $emailAddress . ".csr\">Download CSR</a></p>\n";
        }
        else {
                print "<h1>CSR Not Found</h1>\n";
        }

                if ($fp=fopen("data/ca/cacerts/cacert.pem","r")) {
                        $certData=fread($fp,8192);
                        fclose($fp);
                }
                else {
                        printHeader("Error");
                        print "<h1>Error</h1>";
                        print "Error reading my cert, check permissions<br/>\n";
                        printFooter();
                        return;
                }
                print "<b>Signing CSR...</b><br/>";
                $caCert = openssl_x509_read($certData);
                checkError($caCert);

                if ($fp=fopen("data/ca/keys/cakey.pem","r")) {
                        $rootKey=fread($fp,8192);
                        fclose($fp);
                }
                else {
                        printHeader("Error");
                        print "<h1>Error</h1>";
                        print "Error reading my key, check permissions<br/>\n";
                        printFooter();
                        return;
                }

                $caKey = openssl_get_privatekey($rootKey,$config['passPhrase']);
                checkError($caKey);

                $config['config'] = $configfile ;
                $config['x509_extensions'] = 'user_cert' ;
                $config['digest_alg'] = $md ;
                $signedCert = openssl_csr_sign($myCSR, $caCert, $caKey, (int)$days, $config, getSerial());
                checkError($signedCert);

                openssl_x509_export($signedCert, $myCert, false);
                print "<p>openssl ca -config " . $config . " -in " . $emailAddress . ".csr -out " . $emailAddress . ".crt -days " . $days ." -md " . $md ." -extensions user_cert </p>\n";

                print "<b>Converting Certificate to P12 for Windows...</b><br/>";
                $passphrase = rand_string(16);
                openssl_pkcs12_export_to_file($signedCert, $p12File, $privkey, $passphrase );
                print "<p>openssl pkcs12 -export -out ". $p12File ." -inkey ". $keyFile ." -in " . $certFile ." -passout pass:".$passphrase."</p>\n";
                print "Done<br/><br/>\n";
                print "<p><a href=\"$p12File\" download=\"" . $certName . ".pfx\">Download Cert P12 Format (IIS)</a> Passphrase is: <strong>".$passphrase ."</strong></p>\n";


                if ($fp = fopen($certFile, 'w')) {
                    fputs($fp, $myCert) or $errorCount++;
                    fclose($fp) or $errorCount++;
                    }
                print "Done<br/><br/>\n";
                //print "<b>Your Cert:</b>\n<pre>$myCert</pre>\n";
                print "<p><a href=\"$certFile\" download=\"" . $emailAddress . ".crt\">Download Cert</a></p>\n";


?>


<p>
Congratulations, you have now have a Signed Certificate
</p>

<p>
--&gt; <a href="index.php">Return to Main Page</a>
</p>

