<h1>Initial Setup</h1>

<p>
Please fill in the following form which contains the basic information for your CA's certificate. 
</p>

<form method="post" action="index.php">
<input type="hidden" name="action" value="create">
	<fieldset>
		<table>
                <colgroup><col width="180px"></colgroup>
                <tr><th>Certificate Name</th><td><input type="text" name="dn[commonName]" value="My Root CA" size="40"></td></tr>
                <tr><th>Contact Email Address</th><td><input type="text" name="emailAddress" value="me@mycompany.com" size="40"></td></tr>
                <tr><th>Organization Name</th><td><input type="text" name="organizationName" value="Galactic Empire, LTD" size="40"></td></tr>
                <tr><th>Department Name</th><td><input type="text" name="organizationalUnitName" value="Storm Trooper" size="40"></td></tr>
                </table>

                <p>
                The physical location of the above entity. Again, completely up to you regarding content, except for the country code which should be a proper ISO 2 letter country code.
                </p>

                <table>
                <colgroup><col width="180px"></colgroup>
                <tr><th>City</th><td><input type="text" name="localityName" value="Mos Eisley" size="40"></td></tr>
                <tr><th>State</th><td><input type="text" name="stateOrProvinceName" value="Tatooine" size="40"></td></tr>
                <tr><th>Country</th><td><input type="text" name="countryName" value="GE" size="2"></td></tr>
                </table>

		

		<table>
		<colgroup><col width="180px"></colgroup>
		<tr><th>Passphrase</th><td><input type="text" name="passPhrase" value=<?php echo rand_string(16); ?>  size="40"></td></tr>
		<tr><th>Validity Period (days)</th><td><input type="number" name="days" value=7300 size="10"></td></tr>
		<tr><td colspan=2 style="text-align: right;"><input type="submit" value="Create CA"></td></tr>
		</table>
		<p>
		Please make sure that the configuration file is well protected.
		</p>
	</fieldset>
</form>
