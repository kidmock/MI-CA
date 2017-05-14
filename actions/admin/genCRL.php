	<div class='sectionWide'>
		<h2 class='title'>Update Certificate Revocation List (CRL)</h2>
		<div>
			<p><p>Update this CA's CRL. The CRL is updated automatically when certificates are revoked so you do not have to manually update the CRL.</p>
			<p><span class='required'>DO NOT</span> change the <strong>CRL output format</strong> unless you have reason to do so.
			<br />CRLs for distribution purposes should be in DER format.</p></p>
		</div>
	
<form action='/cgi-bin/process.cgi' method='post' class='shadow narrowForm'>
			<p>The fields indicated with (<span class='required'>*</span>) are mandatory</p>
		<div class='formRow'>
			<div class='fieldName'><span class='required'>* CRL Format:</span></div>
			<select name='crl_format'>
				<option value='der' selected='selected'>DER</option>
				<option value='pem'>PEM</option>
			</select>
		</div>
		<div class='controls'>
				<input type='reset'>
				<input type='submit' id='submit' name='submit' value='Submit'>
			</div>
			<input name='t' type='hidden' value='crl'>
			<input name='mode' type='hidden' value=''>
		</form>    </div>
</section>

</div>

