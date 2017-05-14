<?php

$caSetup = false;
include_once("./include/common.php");
@include_once("./config/config.php");

if (!$caSetup) {
	printHeader('CA setup');
	if(!isset($_REQUEST['action'])) $_REQUEST['action']="";

	switch ($_REQUEST['action']) {
		case "":
			include_once("./actions/setup/intro.php");
			break;
		case "create":
			include_once("./actions/setup/create.php");
			break;
		default:
			print "Unknown setup option: " . htmlspecialchars($_REQUEST['action']);
			break;
	}

	printFooter();
	exit();
}

switch ($_REQUEST['type']) {
	case "main":
	case "":
		switch ($_REQUEST['action']) {
			case "":
				printHeader("Welcome to the {$config['orgName']} Certificate Authority");
				include_once("./actions/main/main.php");
				printFooter();
				break;

			case "about":
				printHeader("About");
				include_once("./actions/main/about.php");
				printFooter();
				break;

			case "help":
				printHeader("Help");
				include_once("./actions/main/help.php");
				printFooter();
				break;

			case "loadCA":
				include_once("./actions/main/loadCA.php");
				break;

			default:
				printHeader("Error");
				print "Unknown application option: " . htmlspecialchars($_REQUEST['action']);
				printFooter();
				break;
		}
		break;
	
	case "server":
		switch ($_REQUEST['action']) {
			case "genCert":
				include_once("./actions/server/genCert.php");
				printFooter();
				break;

			case "createCert":
				printHeader("Create Certificate");
				include_once("./actions/server/createCert.php");
				printFooter();
				break;

			case "genCSR":
				include_once("./actions/server/genCSR.php");
				printFooter();
				break;

			case "createCSR":
				printHeader("Create CSR");
				include_once("./actions/server/createCSR.php");
				printFooter();
				break;

			case "signCSR":
				include_once("./actions/server/signCSR.php");
				printFooter();
				break;

			case "key":
				include_once("./actions/server/key.php");
				break;

			case "sign":
				include_once("./actions/server/sign.php");
				break;

		}
		break;

				
	case "user":
		switch ($_REQUEST['action']) {
			case "":
				printHeader("Register");
				include_once("./actions/user/register.php");
				printFooter();
				break;
				
			case "confirm":
				printHeader("Confirm");
				include_once("./actions/user/confirm.php");
				printFooter();
				break;
				
			case "genUser":
				include_once("./actions/user/genUser.php");
				printFooter();
				break;
				
			case "signUser":
				printHeader("Sign User Certificate");
				include_once("./actions/user/signUser.php");
				printFooter();
				break;
				
			default:
				printHeader("Error");
				print "Unknown application option: " . htmlspecialchars($_REQUEST['action']);
				printFooter();
				break;
		}
		break;

	case 'admin':
		switch ($_REQUEST['action']) {

			case "":
			case "main":
				printHeader('Admin');
				include_once("./actions/admin/main.php");
				printFooter();
			break;

			case "genRevoke":
				printHeader('CA Certification Revocation');
				include_once("./actions/admin/genRevoke.php");
				printFooter();
			break;

			case "revoke":
				printHeader('CA Certification Revocation');
				include_once("./actions/admin/revoke.php");
				printFooter();
			break;

			case "crl":
				printHeader('Generate CRL');
				include_once("./actions/admin/crl.php");
				printFooter();
			break;

			default:
				printHeader("Error");
				print "Unknown administration option: " . htmlspecialchars($_REQUEST['action']);
				printFooter();
			break;
		}
		break;
	
	default:
		printHeader("Unknown type");
		print "Unknown type: " . htmlspecialchars($_REQUEST['type']);
		printFooter();
		break;
}


?>
