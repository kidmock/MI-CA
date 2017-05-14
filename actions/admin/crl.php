<?php
@include_once("./config/config.php");


               $configfile = 'config/openssl.cnf';

               print "<b>Updating CRL...</b><br/>";
               print "<p>openssl openssl ca -config " . $configfile ." -gencrl -out ./data/ca/cacert.crl</p>\n";

               $passphrase = $config['passPhrase'] ;
               $command = "(/usr/bin/openssl ca -config $configfile -gencrl -out data/ca/cacert.crl -passin pass:$passphrase ) 2>&1";
               exec($command, $out, $ret);


          print "<p><b>Certificate Revocation List Updated</b></p>\n";
          print "<a href=\"index.php\">Return to Main Page</a>\n";

?>
