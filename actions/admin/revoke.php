<?php
@include_once("./config/config.php");


if (get_magic_quotes_gpc()) {
        $certName = stripslashes($_REQUEST['cn']);
}
else {
        $certName = &$_REQUEST['cn'];
}

$date = date("ymdHis");


$csrFile = './data/ca/csr/' . $certName . '.csr' ;
$csrRevoked = './data/ca/revoked/'. $date ."_" . $certName . '.csr' ;
if (file_exists($csrFile)) {
         rename( $csrFile , $csrRevoked);      
         }

$keyFile = './data/ca/keys/' . $certName . '.key' ;
$keyRevoked = './data/ca/revoked/'. $date ."_" . $certName . '.key' ;
if (file_exists($keyFile)) {
         rename( $keyFile , $keyRevoked);
         }

$certFile = './data/ca/certs/' . $certName . '.crt' ;
$certRevoked = './data/ca/revoked/'. $date ."_" . $certName . '.crt' ;
if (!file_exists($certFile)) {
    print "<p><b>Certificate Does Not Exist</b></p>\n";
    print "<a href=\"index.php\">Return to Main Page</a>\n";
    printFooter();
    exit();
}
    else {

               print "<b>Revoking Certificate...</b><br/>";
               print "<p>openssl openssl ca -config " . $configfile ." -revoke ". $certFile ."</p>\n";
               $configfile = 'config/openssl.cnf';
               $passphrase = $config['passPhrase'] ;

               $command = "(/usr/bin/openssl ca -config $configfile -revoke $certFile -passin pass:$passphrase ) 2>&1";
               exec($command, $out, $ret);
               $command = "(/usr/bin/openssl ca -config $configfile -gencrl -out ./data/ca/cacert.crl -passin pass:$passphrase ) 2>&1";
               exec($command, $out, $ret);

               rename( $certFile , $certRevoked);      
          print "<p><b>Certificate Revoked</b></p>\n";
          print "<a href=\"index.php\">Return to Main Page</a>\n";
         } 

?>
