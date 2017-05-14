<?php

	if (get_magic_quotes_gpc()) {
		$mail = stripslashes($_REQUEST['mail']);
		$token = stripslashes($_REQUEST['token']);
	}
	else {
		$mail = &$_REQUEST['mail'];
		$token = &$_REQUEST['token'];
	}

	if (!$_REQUEST['mail']) {
		printHeader("Error");
		print "<h1>Error</h1>\n";
		print "Your email address was not found in the input.<br/>\n";
		print "--&gt; <a href=\"index.php?type=user\">Try again</a><br/>\n";
		printFooter();
	}

	elseif (!$_REQUEST['token']) {
		printHeader("Error");
		print "<h1>Error</h1>\n";
		print "Your token was not found in the input.<br/>\n";
		print "--&gt; <a href=\"index.php?type=user&action=confirm&sent=1&mail=".urlencode($mail)."\">Try again</a><br/>\n";
		printFooter();
	}

	elseif (md5($config['passPhrase'] . $config['entropy'] . $mail) != $token) {
		printHeader("Error");
		print "<h1>Error</h1>\n";
		print "Your token does not seem to be correct. Make sure it has no spaces and is exactly as it appears in your email.<br/>\n";
		print "--&gt; <a href=\"index.php?type=user&action=confirm&sent=1&mail=".urlencode($mail)."\">Try again</a><br/>\n";
		printFooter();
	}

	else {

$emailparts= explode('@', $mail );

$username = $emailparts[0];
$domain =  $emailparts[1];

		$inhead = <<<HTML
<script type="text/javascript">
<!--
var ie = (document.all && document.getElementById);
var ns = (!document.all && document.getElementById);

function GenReq()
{
	var szName = "";
	var objID = "1.3.6.1.4.1.311.2.1.21";

	szName = "";

	if (document.GenReqForm.mail.value == "") {
		alert("Email Address Required");
		return false;
	} 
	else szName = "E=" + document.GenReqForm.mail.value;

	if (document.GenReqForm.cn.value == "") {
		alert("Full Name Required");
		return false;
	} 
	else szName = szName + ", CN=" + document.GenReqForm.cn.value;

	if (document.GenReqForm.c.value == "") {
		alert("Contry Required");
		return false;
	}
	else szName = szName + ", C=" + document.GenReqForm.c.value;

	if (document.GenReqForm.st.value == "") {
		alert("State Required");
		return false;
	}
	else szName = szName + ", S=" + document.GenReqForm.st.value;

	if (document.GenReqForm.l.value == "") {
		alert("City Required");
		return false;
	}
	else szName = szName + ", L=" + document.GenReqForm.l.value;

	if (document.GenReqForm.o.value == "") {
		alert("Organization Required");
		return false;
	}
	else szName = szName + ", O=" + document.GenReqForm.o.value;

	if (document.GenReqForm.ou.value == "") {
		alert("Deaprtment Required");
		return false;
	}
	else szName = szName + ", OU=" + document.GenReqForm.ou.value;

	if (!ie) return true;

}
//-->
</script>
HTML;
		printHeader("User Certificate", $inhead);
}	
?>
<h1>User Certificate Request</h1>

<p>
Please fill in the following form which contains the basic information for your certificate. 
</p>

<form id='csrInputForm' class='shadow wideForm' method="post" action="index.php?type=user&action=signUser" name="GenReqForm" onSubmit="return GenReq();">
<input type="hidden" name="token" value="<?=htmlspecialchars($token)?>">

                        <p>The fields indicated with (<span class='required'>*</span>) are mandatory</p>
                        <div class='formRow'><div class='fieldName'><span class='required'>* Your Full Name:</span> </div><input id="cn" name='cn' type='text' value="<?=htmlspecialchars($username)?>" ></div> 
                        <div class='formRow'><div class='fieldName'><span class='required'>E-Mail Address:</span></div><input id="mail" name='mail' type='text' value="<?=htmlspecialchars($mail)?>"></div>
                        <div class='formRow'><div class='fieldName'><span class='required'>Organization:</span></div><input id="o" name='o' type='text' value="<?=htmlspecialchars($config['orgName'])?>"></div>
                        <div class='formRow'><div class='fieldName'><span class='required'>Department:</span></div><input id="ou" name='ou' type='text' value='Staff'></div>
                        <div class='formRow'><div class='fieldName'><span class='required'>Locality (City):</span></div><input id="l" name='l' type='text' value="<?=htmlspecialchars($config['city'])?>"></div>
                        <div class='formRow'><div class='fieldName'><span class='required'>State or Province Name:</span></div><input id="st" name='st' type='text' value="<?=htmlspecialchars($config['state'])?>"></div>
                        <div class='formRow'><div class='fieldName'><span class='required'>Country:</span></div><input id="c" name='c' type='text' class='shortInput' value="<?=htmlspecialchars($config['country'])?>"></div>
                        <div class='formRow'>
                                <div class='fieldName'><span class='required'>* Message Digest Algorithm:</span></div>
                                <select name='md'>
                                        <option value='md5'>MD5</option>
                                        <option value='sha1'>SHA-1</option>
                                        <option value='sha256'>SHA-256</option>
                                        <option value='sha512' selected='selected'>SHA-512</option>
                                </select>
                        </div>

                        <div class='formRow'>
                                <div class='fieldName'><span class='required'>* Key length (bits):</span></div>
                                <select name='keylen'>
                                        <option value='1024'>1024</option>
                                        <option value='2048'>2048</option>
                                        <option value='3072'>3072</option>
                                        <option value='4096' selected='selected'>4096</option>
                                        <option value='5120'>5120</option>
                                        <option value='6144'>6144</option>
                                </select>
                        </div>
                        <div class='controls'>
                                <input type="hidden" name="reqEntry">
                                <input type='reset'>
                                <input type='submit' id='submit' name='submit' value='Get Cert'>
                        </div>


</form>

