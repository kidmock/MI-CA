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
                printHeader("Generate CSR", $inhead);

?>



	<div class='sectionWide'>
		<h2 class='title'>Create Certificate Signing Request (CSR)</h2>
		<div>
			<p><p>Create a CSR to be signed by a third-party trust authority (i.e., Verisign, Thawt, DigiCert, etc.).</p>
			<p>Enter the <strong>Fully Qualified Server Name</strong> exmaple: www.example.com<br>
			The other fields are optional </p></p>
		</div>
	
                  <form id='csrInputForm' action='index.php?type=server&action=createCSR' method='post' class='shadow wideForm' name="GenReqForm" onSubmit="return GenReq();">
			<p>The fields indicated with (<span class='required'>*</span>) are mandatory</p>
			<div class='formRow'>
				<div class='fieldName'>
					<span class='required'>* Fully Qualified Server Name:</span>
				</div>
				<input name='cn' type='text' placeholder='www.example.com'></div>

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
			<div class='formRow'><div class='fieldName'><span class='required'>E-Mail Address:</span></div><input name='mail' type='text' placeholder='me@example.com'></div>
			<div class='formRow'><div class='fieldName'><span class='required'>Organization:</span></div><input name='o' type='text' placeholder='Galactic Empire, LTD'></div>
			<div class='formRow'><div class='fieldName'><span class='required'>Department:</span></div><input name='ou' type='text' placeholder='Storm Trooper'></div>
			<div class='formRow'><div class='fieldName'><span class='required'>Locality (City):</span></div><input name='l' type='text' placeholder='Mos Eisley'></div>
			<div class='formRow'><div class='fieldName'><span class='required'>State or Province Name:</span></div><input name='st' type='text' placeholder='Tatooine'></div>
			<div class='formRow'><div class='fieldName'><span class='required'>Country:</span></div><input name='c' type='text' class='shortInput' placeholder='GE'></div>
			<div class='formRow'><div class='fieldName'><span class='required'>Alternative Name(s):</span>
                                                                    <div class='note'>Note: Multiple Names are sperated by commas</div>
                                                                    </div><textarea name='san' rows='5' placeholder='www.example.com,example.com'></textarea></div>
			<div class='controls'>
				<input type='reset'>
				<input type='submit' id='submit' name='submit' value='Create CSR'>
			</div>
		</form>    </div>
</div>
</section>

</div>


