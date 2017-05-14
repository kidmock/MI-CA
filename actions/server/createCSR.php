<h1>Key and CSR Creation</h1>

<?php

if (get_magic_quotes_gpc()) {
        $certName = stripslashes($_REQUEST['cn']);
        
        $certSan = stripslashes($_REQUEST['san']);
        $certBits = stripslashes($_REQUEST['keylen']);
        $md = stripslashes($_REQUEST['md']);
        $certDays = stripslashes($_REQUEST['days']);

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
        $certDays = &$_REQUEST['days'];

        $organizationName = &$_REQUEST['o'];
        $organizationUnit = &$_REQUEST['ou'];
        $emailAddress = &$_REQUEST['mail'];
        $localityName = &$_REQUEST['l'];
        $stateOrProvinceName = &$_REQUEST['st'];
        $countryName = &$_REQUEST['c'];
}

$keyFile = './data/ca/keys/' . $certName . '.key' ;
if (file_exists($keyFile)) {
    print "<p><b>Key already exists and needs to be removed</b</p>\n";
    print "<a href=\"index.php\">Return to Main Page</a>\n";
    exit();
}
$csrFile = './data/ca/csr/' . $certName . '.csr' ;
if (file_exists($csrFile)) {
    print "<p><b>CSR already exists and needs to be removed</b></p>\n";
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
copy($configfile, $certconfig);


$sanappend = "[v3_req]\nsubjectAltName=" . $san ."\n" ;
file_put_contents($certconfig, $sanappend , FILE_APPEND | LOCK_EX);


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
print "Done<br/><br/>\n";


       if (file_exists($keyFile)) {
                $myKey = join("", file($keyFile));

                print "<b>Your Key:</b>\n<pre>$myKey</pre>\n";
                print "<p><a href=\"$keyFile\"download=\"" . $certName . ".key\">Download Key</a></p>\n";
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
print "Done<br/><br/>\n";


       if (file_exists($csrFile)) {
                $myCSR = join("", file($csrFile));

                print "<b>Your CSR:</b>\n<pre>$myCSR</pre>\n";
                print "<p><a href=\"$csrFile\" download=\"" . $certName . ".csr\">Download CSR</a></p>\n";
        }
        else {
                print "<h1>CSR Not Found</h1>\n";
        }


unlink($certconfig);

?>


<p>
Congratulations, you have now have a CSR that can be signed.
</p>

<p>
The next step would be for you to Sign your CSR.<br/>
--&gt; <a href="index.php">Get a signed certificate</a>
</p>

