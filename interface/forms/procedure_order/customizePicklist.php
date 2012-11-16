
<?php // Copyright (C) 2010 Rod Roark <rod@sunsetsystems.com>
//
// This program is free software; you can redistribute it and/or
// modify it under the terms of the GNU General Public License
// as published by the Free Software Foundation; either version 2
// of the License, or (at your option) any later version.

require_once ("../../globals.php");
require_once ("$srcdir/api.inc");
require_once ("$srcdir/forms.inc");
require_once ("$srcdir/options.inc.php");
require_once ("$srcdir/formdata.inc.php");
require_once ("$srcdir/formatting.inc.php");

include_once ("functions.php");


?>
<html>
<head>
<?php html_header_show(); ?>
<link rel="stylesheet" href="<?php echo $css_header; ?>" type="text/css" />

<style>
	td {
		font-size: 10pt;
	}

	.inputtext {
		padding-left: 2px;
		padding-right: 2px;
	}

</style>
<style type="text/css">@import url(<?php echo $GLOBALS['webroot'] ?>/interface/forms/procedure_order/easyui.css);</style>
<script type="text/javascript" src="../../../library/dialog.js"></script>
<script type="text/javascript" src="<?php echo $GLOBALS['webroot'] ?>/library/textformat.js"></script>
<script type="text/javascript" src="<?php echo $GLOBALS['webroot'] ?>/interface/forms/procedure_order/jquery-1.4.3.min.js"></script>
<script type="text/javascript" src="<?php echo $GLOBALS['webroot'] ?>/interface/forms/procedure_order/jquery-1.7.2.min.js"></script>
<script type="text/javascript" src="<?php echo $GLOBALS['webroot'] ?>/interface/forms/procedure_order/jquery-ui-1.8.21.custom.min.js"></script>
<script type="text/javascript" src="../../../library/dialog.js"></script>	
<script type="text/javascript" src="<?php echo $GLOBALS['webroot'] ?>/interface/forms/procedure_order/jquery.easyui.min.js"></script>


<script type="text/javascript">

//Show Pick list Drop down when LOINC Code selected from Codes Type.
	$(document).ready(function() {
		var customize_value;
		$.ajax({
				url:  "<?php echo $GLOBALS['webroot'] ?>/interface/forms/procedure_order/functions.php?func=lonicPickList&selected_value=<?php echo $_GET['selected_value_customize']; ?>",
				async:false,
				success: function (response) {
						customize_value = response;
						$('#picklist_id').html(customize_value); 
						$('#picklist_name').html(customize_value); 
						$('#selectedCodeType').val(<?php echo $_GET['selected_code']; ?>);
					}
			});	
	});
	
	function generatePickList(picklist, rootdir) {
		var selected = picklist;
		var selpick = selected.options[selected.options.selectedIndex].text;
		
			urlLink= rootdir + "/forms/procedure_order/functions.php?mode=picklist&name=" + selpick;
			$('#picklistItem').datagrid({
				url:urlLink
			});		
	}		
	
	function showSearchCodes(rootdir)
	{	
		code=$('#loincsearchbox').val();
		urlLink=rootdir + "/forms/procedure_order/functions.php?mode=SearchCodes&name=" + code+"&type="+$('#selectedCodeType').val();		
		if(code!='' && code.length >2)
		{	
		$('#searchItems').datagrid({
				url: urlLink
			});	
			
		}		
	}
	
	
	function saveToPickList(selectCtrl,rootdir) {	
		var selected=selectCtrl;	
		var selpick = selected.options[selected.options.selectedIndex].text;		
		var values ='';		
			
		var tempTitle = [];
		var rows = $('#searchItems').datagrid('getSelections');
		if (rows != null && rows.length > 0) {
			
		var code = ""; var codetext="";
		for (var i = 0; i < rows.length; i++) {	
			if(rows[i].code!='')			
				code += rows[i].code + '@@@';
				codetext+= rows[i].code_text + '@@@';		
		}	
		
		urlLink=rootdir + "/forms/procedure_order/functions.php?mode=addLoincCode&name=" + code+'&type='+selpick+'&codeText='+codetext;	
		//urlLink=rootdir + "/forms/procedure_order/functions.php?mode=addLoincCode&name=" + code+'&type='+selpick+'&codeText='+codetext;					
		$.ajax({
			url : urlLink,
			type : "POST",
			cache : false,
			dataType : "json",
			success : function(result) {
				if (result.success) {
					$("#picklistItem").datagrid('reload');					
				}
			}
		});
		
		}
	}
	
	
	function removeCheckedItems(rootdir)
	{
	var tempTitle = [];
	var rows = $('#picklistItem').datagrid('getSelections');
	if (rows != null && rows.length > 0) {
		var data = "";
		for (var i = 0; i < rows.length; i++) {	
			if(rows[i].code!='')			
				data += rows[i].code + '@@@';			
		}		
		urlLink=rootdir + "/forms/procedure_order/functions.php?mode=removeLoincCode&name=" + data;
		$.ajax({
			url : urlLink,
			type : "POST",
			cache : false,
			dataType : "json",
			success : function(result) {
				if (result.success) {
					$("#picklistItem").datagrid('reload');					
				}
			}
		});
	} else {
		$('#MessageBoxPanel').html(MessageBox('Please select an picklist Item..', 'error'));
	}
	}	
	
	
	

</script>

</head>

<body class="body_top">
	<form method="post" action="<?php echo $rootdir ?>/forms/procedure_order/customizePicklist.php" >
<table width="100%">
<tr>
<td width="25%"> <b>Pick List Category :</b></td>
<td width="75%"><select  name="picklist_id" id="picklist_id" onchange="generatePickList(this,'<?php echo $rootdir; ?>','custom')"> </select>
</td></tr>

<tr><td><b> PickList Items : </b></td><td>
	<table class="easyui-datagrid" style="width:400px;height:200px" id="picklistItem" >  
    <thead>  
        <tr>  
        	<th field="ck" checkbox="true"></th>  
            <th data-options="field:'code',width:100 ">Code</th>  
            <th data-options="field:'code_text',width:250">Description</th>             
        </tr>  
    </thead>  
</table>
		
</td>

<tr><td>&nbsp; </td><td> <input type="button" name="removeFromPickList" id="removeFromPickList" value="Remove Checked" onclick="removeCheckedItems('<?php echo $rootdir; ?>')"></td></tr>

<tr><td > &nbsp; </td><td></td></tr>

<tr>
	<td> <b> Search Codes: </b> </td>
	<td> <input type='text' size='15' name='loincsearchbox' id='loincsearchbox' />  
		<input type='button' name='searchloinc' id='searchloinc' value='Go' onclick="showSearchCodes('<?php echo $rootdir; ?>')"/>
		<input type="hidden" name="selectedCodeType" id="selectedCodeType" value=''/>
		</td>
</tr>	

<tr>
<td> <b> Search Result : </b></td>	
<td> <table class="easyui-datagrid" style="width:400px;height:200px" id="searchItems" >  
    <thead>  
        <tr>  
            <th field="ck" checkbox="true"></th>  
            <th data-options="field:'code',width:100 ">Code</th>  
            <th data-options="field:'code_text',width:250">Description</th>   
        </tr>  
    </thead>  
</table> </td>	
</tr>

	<tr>
		<td> <b> Add to Picklist :  </b></td>
		<td> <select  name="picklist_name" id="picklist_name" ></select>
			
			&nbsp;&nbsp; &nbsp;&nbsp;&nbsp;&nbsp;&nbsp; &nbsp;&nbsp;&nbsp;<input type="button" name="savePicklist" id="savePicklist" value="Save" onclick="saveToPickList(document.getElementById('picklist_name'),'<?php echo $rootdir; ?>')"/>
			</td>
	</tr>



<tr><td > &nbsp; </td><td></td></tr>
</table>
</form>
</body>
</html>