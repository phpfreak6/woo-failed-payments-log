<form action="?page=gexcel-settings" method="post">
<h3>Google Spreadsheet Settings</h3>

<table class="form-table" style="width:550px">
    <tbody>
       <tr>
			<td style="width:147px;"><h4>Json File Code</h4></td>
			<td style="width:600px">
			<textarea name="gjsonfile" required style="width:100%;height:150px">
			<?php if(isset($gjsonfile)){ echo $gjsonfile;  } ?>
			</textarea>
			</td>
		</tr>
		
       <tr>
			<td style="width:170px;"><h4>Spreadsheet Name</h4></td>
			<td style="width:262px"><input name="spreadsheet-name" value='<?php echo (isset($spreadsheetname) && $spreadsheetname) ? $spreadsheetname :  '' ; ?>' required="" style="width: 100%;" type="text"></td>
		</tr>
    </tbody>
</table>
    <p class="submit"><input type="submit" value="Submit" class="button-primary" name="gesubmit"></p>
</form>
