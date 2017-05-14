<?php

	$certFile = "./data/ca/cacerts/cacert.pem";
	if (file_exists($certFile)) {
		$myCert = join("", file($certFile));

                header("Content-Disposition: attachment; filename=\"CACert.crt\"");
		header("Content-Type: application/x-x509-ca-cert");
		print $myCert;
	}
	else {
		printHeader("Certificate Retrieval");
		print "<h1>X509 CA certificate not found</h1>\n";
		printFooter();
	}

?>
