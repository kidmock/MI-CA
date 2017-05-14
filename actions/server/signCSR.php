<?php


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

        if (document.GenReqForm.cn.value == "") {
                alert("Server Name Required");
                return false;
        } 
        else szName = szName + ", CN=" + document.GenReqForm.cn.value;
        if (!ie) return true;

}
//-->
</script>
HTML;
                printHeader("Sign Certificate", $inhead);

?>


	<div class='sectionWide'>
		<h2 class='title'>Sign Certificate</h2>
		<div>
			<p><p>Create a new certificate by signing an existing CSR with this CA.</p>
				<p>Enter the <strong>Fully Qualified Server Name</strong> and <strong>CSR</strong> file contents.<br>
				The other fields are pre-populated based on default values. Only change the default values as necessary.</p></p>
		</div>
	
<form id='existingCsrInput' method='post' action='index.php?type=server&action=sign' class='shadow wideForm' name="GenReqForm" onSubmit="return GenReq();">
			<p>The fields indicated with (<span class='required'>*</span>) are mandatory</p>
			<div class='formRow'><div class='fieldName'><span class='required'>* Fully Qualified Server Name:</span></div><input name='cn' type='text' placeholder='test.example.com'></div>
			<div class='formRow'>
				<div class='fieldName'><span class='required'>Message Digest Algorithm:</span></div>
				<select name='md'>
					<option value='md5'>MD5</option>
					<option value='sha1'>SHA-1</option>
					<option value='sha256'>SHA-256</option>
					<option value='sha512' selected='selected'>SHA-512</option>
				</select>
			</div>
			<div class='formRow'><div class='fieldName'><span class='required'>* Validity Period (days):</span></div><input name='days' type='text' class='shortInput' value='365'></div>
			<div class='formRow'><div class='fieldName'><span class='required'>Subject Alternative Name(s):</span>
                        <div class='note'>Note: Multiple Names are sperated by commas</div>
                        </div></td><td>
                        <textarea name='san' rows='5' placeholder='test.example.com,example.com'></textarea></div>
			<div class='formRow'> <div class='fieldName'><span class='required'> Contents of CSR file:</span><br>
                    <div class='note'>TIP: If you used this CA to create the CSR, this can be left blank. Otherwise, a CSR is required.</div>
                </div>
				<textarea wrap='virtual' rows='20' name='csr'></textarea>
			</div>
			<div class='controls'>
				<input type='reset'>
				<input type='submit' id='submit' name='submit' value='Sign CSR'>
			</div>
		</form>    </div>
</section>

</div>

