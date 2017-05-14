<?php


if (get_magic_quotes_gpc()) {
        $certName = stripslashes($_REQUEST['cn']);
        $keysize = stripslashes($_REQUEST['keylen']);
        $md = stripslashes($_REQUEST['md']);
     
}
else {
        $certName = &$_REQUEST['cn'];
        $keysize = &$_REQUEST['keylen'];
        $md = &$_REQUEST['md'];

}
$keyFile = './data/ca/keys/' . $certName . '.key' ;
if (file_exists($keyFile)) {
    print "Key already exists and needs to be removed\n";
    exit();
}

if ( empty($md) ) $md = 'sha512';
if ( empty($keysize) ) $keysize = '4096';

$keyparams = array();

$keyparams['private_key_type'] = 'OPENSSL_KEYTYPE_RSA' ;
$keyparams['digest_alg'] = $md ;
$keyparams['private_key_bits'] = (int)$keysize ;

$privkey = openssl_pkey_new($keyparams);

openssl_pkey_export($privkey, $myKey);

if ($fp = fopen($keyFile, 'w')) {
        fputs($fp, $myKey) or $errorCount++;
        fclose($fp) or $errorCount++;
}



       if (file_exists($keyFile)) {
                $myKey = join("", file($keyFile));

                header("Content-Disposition: attachment; filename=\"$certName.key\"");
                Header("Content-Type: text/plain");
                print $myKey;
        }
        else {
                print "RSA Key Not Found\n";
        }



?>


