<h1>Signed Certificate Creation</h1>

<?php
include_once("./include/common.php");
@include_once("./config/config.php");


if (get_magic_quotes_gpc()) {
        $certName = stripslashes($_REQUEST['cn']);
        
        $certSan = stripslashes($_REQUEST['san']);
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

        $certSan = &$_REQUEST['san'];
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
$keyFile = './data/ca/keys/' . $certName . '.key' ;
if (file_exists($keyFile)) {
    print "<p><b>Key already exists and needs to be removed</b></p>\n";
    print "<a href=\"index.php\">Return to Main Page</a>\n";
    exit();
}
$csrFile = './data/ca/csr/' . $certName . '.csr' ;
if (file_exists($csrFile)) {
    print "<p><b>CSR already exists and needs to be removed</b></p>\n";
    print "<a href=\"index.php\">Return to Main Page</a>\n";
    exit();
}

$certFile = './data/ca/certs/' . $certName . '.crt' ;
if (file_exists($certFile)) {
    print "<p><b>Certificate already exists and needs to be revoked</b></p>\n";
    print "<a href=\"index.php\">Return to Main Page</a>\n";
    exit();
}
$p12File = './data/ca/certs/' . $certName . '.pfx' ;
if (file_exists($p12File)) {
    print "<p><b>Certificate already exists and needs to be revoked</b></p>\n";
    print "<a href=\"index.php\">Return to Main Page</a>\n";
    exit();
}

if ( empty($md) ) $md = 'sha512';


if ( empty($certSan) ) $certSan = $certName ;

$sanDomains = explode(',', $certSan);
array_push($sanDomains, $certName );

$addPrefix = function ($value) {
  return 'DNS:' . $value;
};


$sanDomainPrefixed = array_map($addPrefix, $sanDomains);

$san = implode(',', $sanDomainPrefixed);


$keysize = $certBits;
if ( empty($keysize) ) $keysize = '4096';
$keysize = $certBits;
if ( empty($keysize) ) $keysize = '4096';

$keyparams = array();

$keyparams['private_key_type'] = 'OPENSSL_KEYTYPE_RSA' ;
$keyparams['digest_alg'] = $md ;
$keyparams['private_key_bits'] = (int)$keysize ;

$configfile = 'config/openssl.cnf';
$certconfig = 'config/'. $certName . '.cnf';
$ssconfig = 'config/ss'. $certName . '.cnf';
copy($configfile, $certconfig);
copy($configfile, $ssconfig);


$sanappend = "[v3_req]\nsubjectAltName=" . $san ."\n" ;
file_put_contents($certconfig, $sanappend , FILE_APPEND | LOCK_EX);

$ssappend = "subjectAltName=" . $san ."\n" ;
file_put_contents($ssconfig, $ssappend , FILE_APPEND | LOCK_EX);


$csrconfig = array();

$csrconfig['config'] = $certconfig ;
$csrconfig['req_extensions'] = 'v3_req' ;

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

print "<b>Checking your SAN (Subject Alternative Names)...</b><br/>";
print "<pre>SAN = ".var_export($sanDomains,1)."</pre>";

print "<b>Generating new key...</b><br/>";
print "<p>openssl genpkey -algorithm RSA -out " . $certName .".key -pkeyopt rsa_keygen_bits:". $keysize . "</p>\n";
$privkey = openssl_pkey_new($keyparams);
checkError($privkey);

openssl_pkey_export($privkey, $myKey);

if ($fp = fopen($keyFile, 'w')) {
        fputs($fp, $myKey) or $errorCount++;
        fclose($fp) or $errorCount++;
}


       if (file_exists($keyFile)) {
                $myKey = join("", file($keyFile));
                print "Done<br/><br/>\n";
                print "<p><a href=\"$keyFile\"download=\"" . $certName . ".key\">Download Key</a></p>\n";
                print "<b>Your Key:</b>\n<pre>$myKey</pre>\n";
        }
        else {
                print "<h1>RSA Key Not Found</h1>\n";
        }

print "<b>Creating CSR...</b><br/>";
print "<p>openssl req -config " . $certconfig . " -out " . $certName . ".csr -key " . $certName . ".key -reqexts v3_req -new</p>\n";
$csr = openssl_csr_new($dn, $privkey, $csrconfig );
checkError($csr);

openssl_csr_export($csr, $myCSR);
if ($fp = fopen($csrFile, 'w')) {
        fputs($fp, $myCSR) or $errorCount++;
        fclose($fp) or $errorCount++;
}


       if (file_exists($csrFile)) {
                $myCSR = join("", file($csrFile));
                print "Done<br/><br/>\n";
                print "<p><a href=\"$csrFile\" download=\"" . $certName . ".csr\">Download CSR</a></p>\n";
                print "<b>Your CSR:</b>\n<pre>$myCSR</pre>\n";
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

                $config['config'] = $ssconfig ;
                $config['x509_extensions'] = 'server_cert' ;
                $config['digest_alg'] = $md ;
                $signedCert = openssl_csr_sign($myCSR, $caCert, $caKey, (int)$days, $config, getSerial());
                checkError($signedCert);

                openssl_x509_export($signedCert, $myCert, false);
                print "<p>openssl ca -config " . $ssconfig . " -in " . $certName . ".csr -out " . $certName . ".crt -days " . $days ." -md " . $md ." -extensions server_cert </p>\n";
                
                if ($fp = fopen($certFile, 'w')) {
                    fputs($fp, $myCert) or $errorCount++;
                    fclose($fp) or $errorCount++;
                    }

                print "Done<br/><br/>\n";
                print "<p><a href=\"$certFile\" download=\"" . $certName . ".crt\">Download Cert PEM Format (Apache)</a></p>\n";


                print "<b>Converting Certificate to P12 for Windows...</b><br/>";
                $passphrase = rand_string(16);
                openssl_pkcs12_export_to_file($signedCert, $p12File, $privkey, $passphrase );
                print "<p>openssl pkcs12 -export -out ". $p12File ." -inkey ". $keyFile ." -in " . $certFile ." -passout pass:".$passphrase."</p>\n";
                print "Done<br/><br/>\n";
                print "<p><a href=\"$p12File\" download=\"" . $certName . ".pfx\">Download Cert P12 Format (IIS)</a> Passphrase is: <strong>".$passphrase ."</strong></p>\n";

                print "<b>Your Cert:</b>\n<pre>$myCert</pre>\n";

unlink($certconfig);
unlink($ssconfig);

?>


<p>
Congratulations, you have now have a Signed Certificate
</p>

<p>
--&gt; <a href="index.php">Return to Main Page</a>
</p>

