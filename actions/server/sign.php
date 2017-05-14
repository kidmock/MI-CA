
<?php
@include_once("./config/config.php");


if (get_magic_quotes_gpc()) {
        $certName = stripslashes($_REQUEST['cn']);
        $certSan = stripslashes($_REQUEST['san']);
        $md = stripslashes($_REQUEST['md']);
        $certRequest = stripslashes($_REQUEST['csr']);
        $days = stripslashes($_REQUEST['days']);


}
else {
        $certName = &$_REQUEST['cn'];
        $certSan = &$_REQUEST['san'];
        $md = &$_REQUEST['md'];
        $certRequest = &$_REQUEST['csr'];
        $days = &$_REQUEST['days'];

}

$certFile = './data/ca/certs/' . $certName . '.crt' ;
if (file_exists($certFile)) {
    printHeader("Certificate application and issue");
    print "<p><b>Certificate already exists and needs to be revoked</b></p>\n";
    print "<a href=\"index.php\">Return to Main Page</a>\n";
    printFooter();
    exit();
}


if ( empty($md) ) $md = 'sha512';

if ( empty($days) ) $days = 365;

if ( empty($certSan) ) $certSan = $certName ;

$sanDomains = explode(',', $certSan);
array_push($sanDomains, $certName );
$addPrefix = function ($value) {
  return 'DNS:' . $value;
};


$sanDomainPrefixed = array_map($addPrefix, $sanDomains);

$san = implode(',', $sanDomainPrefixed);

$configfile = 'config/openssl.cnf';
$ssconfig = 'config/ss'. $certName . '.cnf';

copy($configfile, $ssconfig);


$ssappend = "subjectAltName=" . $san ."\n" ;
file_put_contents($ssconfig, $ssappend , FILE_APPEND | LOCK_EX);


               
                if ($fp = fopen($csrFile, 'w')) {
                    fwrite($fp, $certRequest) or $errorCount++;
                    fclose($fp) or $errorCount++;
                    }


                if ($fp=fopen("data/ca/cacerts/cacert.pem","r")) {
                        $certData=fread($fp,8192);
                        fclose($fp);
                }
                else {
                        print "Error reading my cert, check permissions<br/>\n";
                        return;
                }
                $caCert = openssl_x509_read($certData);
                checkError($caCert);

                if ($fp=fopen("data/ca/keys/cakey.pem","r")) {
                        $rootKey=fread($fp,8192);
                        fclose($fp);
                }
                else {
                        print "Error reading my key, check permissions<br/>\n";
                        return;
                }

                $caKey = openssl_get_privatekey($rootKey,$config['passPhrase']);
                checkError($caKey);

                $config['config'] = $ssconfig ;
                $config['digest_alg'] = $md ;
                $config['x509_extensions'] = 'server_cert' ;
                $signedCert = openssl_csr_sign($certRequest, $caCert, $caKey, (int)$days, $config, getSerial());
                checkError($signedCert);

                openssl_x509_export($signedCert, $myCert, false);

                if ($fp = fopen($certFile, 'w')) {
                    fputs($fp, $myCert) or $errorCount++;
                    fclose($fp) or $errorCount++;
                    }

       if (file_exists($certFile)) {
                $signedCert = join("", file($certFile));
                header('Content-Type: application/force-download');
                header("Content-Disposition: attachment; filename=\"$certName.crt\"");
                Header("Content-Type: text/plain");
                print $signedCert;
        }
        else {
                print "Unable able to sign CSR";
        }
unlink($ssconfig);
?>
