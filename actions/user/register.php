<?php

$domain = preg_replace('/[^@]+@/','', $config['contact']);

?>
<h1>Email Registration.</h1>

<p>
Enter Email Address to Receive a Registration Token
</p>


<form method="post" action="index.php">
<input type="hidden" name="type" value="user">
<input type="hidden" name="action" value="confirm">
	<fieldset style="width: 400px;">

		<table>
		<colgroup><col width="180px"></colgroup>
		<tr><th>Email Address</th><td><input type="text" name="mail" placeholder="username@<?=htmlspecialchars($domain)?>" size="30"></td></tr>
		<tr><td colspan=2 style="text-align: right;"><input type="submit" value="Send Token"></td></tr>
		</table>
	</fieldset>
</form>


