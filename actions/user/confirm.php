<?php

	if (!$_REQUEST['mail']) {
		print "Your email address was not found in the input.<br/>\n";
		print "--&gt; <a href=\"index.php?type=user\">Try again</a><br/>\n";
	}

	else {

		print "<h1>Email address Confirmation</h1>\n";

		if (get_magic_quotes_gpc()) {
			$mail = stripslashes($_REQUEST['mail']);
		}
		else {
			$mail = &$_REQUEST['mail'];
		}

		if (!$_REQUEST['sent']) {
			print "<b>Sending token to your email address...</b><br/>";
			flush();

			$secretMessage = md5($config['passPhrase'] . $config['entropy'] . $mail);
			$urlEmail = urlencode($mail);
			$requestURI = preg_replace('/\?.*/', '', $_SERVER['REQUEST_URI']);
                        $headers = 'From: '. $config["contact"] . "\r\n" .  'Reply-To: '. $config["contact"] . "\r\n" .  'X-Mailer: PHP/' . phpversion();

			$message = <<<ENDE
Hi there,

This is the {$config["orgName"]} Certificate Authority.


I've just been asked to confirm this email address for somebody, and I'm sending a token to this email address so that we can validate the requestor's identity.

Your Token is:

	$secretMessage

You can go back to the website, and put this token in to get your key, or if you don't have the browser open anymore, you can click this URL to go back there:
http://{$_SERVER['HTTP_HOST']}{$requestURI}?type=user&action=confirm&sent=1&mail={$urlEmail}

Regards,
{$config["orgName"]} Certificate Authority.
mailto:{$config["contact"]}

ENDE;

			mail($mail, "{$config["orgName"]} Certificate Registration", $message, $headers);

			print "Done<br/><br/>\n";
		}
?>


<form method="post" action="index.php">
<input type="hidden" name="type" value="user">
<input type="hidden" name="action" value="genUser">
<input type="hidden" name="mail" value="<?=htmlspecialchars($mail)?>">
	<fieldset style="width: 400px;">
		<p>
		Please enter the token you were emailed in the box below:
		</p>

		<table>
		<colgroup><col width="180px"></colgroup>
		<tr><th>Token</th><td><input type="text" name="token" value="" size="40"></td></tr>
		<tr><td colspan=2 style="text-align: right;"><input type="submit" value="Validate Token"></td></tr>
		</table>
	</fieldset>
</form>

<?php

	}

?>
