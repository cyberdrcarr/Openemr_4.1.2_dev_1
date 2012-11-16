<?php 
require_once ("../../globals.php");
require_once ("$srcdir/api.inc");
require_once ("$srcdir/forms.inc");
require_once ("$srcdir/options.inc.php");
require_once ("$srcdir/formdata.inc.php");
require_once ("$srcdir/formatting.inc.php");

include_once ("functions.php");

// Defaults for new orders.
$row = array('provider_id' => $_SESSION['authUserID'], 'date_ordered' => date('Y-m-d'), 'date_collected' => date('Y-m-d H:i'), );

if (!$encounter) {// comes from globals.php
	die("Internal error: we do not seem to be in an encounter!");
}

function cbvalue($cbname) {
	return $_POST[$cbname] ? '1' : '0';
}

function cbinput($name, $colname) {
	global $row;
	$ret = "<input type='checkbox' name='$name' value='1'";
	if ($row[$colname])
		$ret .= " checked";
	$ret .= " />";
	return $ret;
}

function cbcell($name, $desc, $colname) {
	return "<td width='25%' nowrap>" . cbinput($name, $colname) . "$desc</td>\n";
}

function QuotedOrNull($fld) {
	if (empty($fld))
		return "NULL";
	return "'$fld'";
}

//$enrow = sqlQuery("SELECT p.fname, p.mname, p.lname, fe.date FROM " . "form_encounter AS fe, forms AS f, patient_data AS p WHERE " . "p.pid = '$pid' AND f.pid = '$pid' AND f.encounter = '$encounter' AND " . "f.formdir = 'newpatient' AND f.deleted = 0 AND " . "fe.id = f.form_id LIMIT 1");

?>
<html>
<head>
<?php html_header_show(); ?>
<link rel="stylesheet" href="<?php echo $css_header; ?>" type="text/css" />
<?php $query = 'select order_priority  from procedure_order where procedure_order_id='.$_GET["id"]; $result = mysql_query($query); while ($row = mysql_fetch_assoc($result)){$sql = $row['order_priority'];} ?>
<style>
	td {
		font-size: 10pt;
	}

	.inputtext {
		padding-left: 2px;
		padding-right: 2px;
	}
	
	#compendum
	{
		display: none;
		position: fixed;
		width: 550px;
		height: 160px;
		top: 50%;
		left: 50%;
		margin-left: -350px;
		margin-top: -100px;
		background-color: white;
		border: 2px solid #369;
		padding: 25px;
		z-index: 102;
	}
	
	.panel-tool a{
color : #FFF;
	}

</style>



<style type="text/css">@import url(<?php echo $GLOBALS['webroot']?>/library/dynarch_calendar.css);</style>
<script type="text/javascript" src="<?php echo $GLOBALS['webroot'] ?>/library/dynarch_calendar.js"></script>
<script type="text/javascript" src="<?php echo $GLOBALS['webroot'] ?>/library/dynarch_calendar_en.js"></script>
<script type="text/javascript" src="<?php echo $GLOBALS['webroot'] ?>/library/dynarch_calendar_setup.js"></script>	
<script type="text/javascript" src="<?php echo $GLOBALS['webroot'] ?>/library/js/jquery-1.4.3.min.js"></script>
<script type="text/javascript" src="<?php echo $GLOBALS['webroot'] ?>/interface/forms/procedure_order/js/jquery-1.7.2.min.js"></script>
<script type="text/javascript" src="<?php echo $GLOBALS['webroot'] ?>/interface/forms/procedure_order/js/jquery-ui-1.8.21.custom.min.js"></script>
<script type="text/javascript" src="<?php echo $GLOBALS['webroot'] ?>/interface/forms/procedure_order/js/jquery.easyui.min.js"></script>

<style type="text/css">@import url(<?php echo $GLOBALS['webroot'] ?>/interface/forms/procedure_order/easyui.css);</style>
<script type="text/javascript" src="../../../library/dialog.js"></script>
<script type="text/javascript" src="<?php echo $GLOBALS['webroot'] ?>/library/textformat.js"></script>


<script language='JavaScript'>
$(document).ready(function() {
					
		urlLink= "../../forms/procedure_order/functions.php?mode=PrevLabTest&id=<?php echo $_GET["id"];?>";
		$('#order_view').datagrid({
				url:urlLink
			});			
		urlLink= "../../forms/procedure_order/functions.php?mode=PrevLOINC&id=<?php echo $_GET["id"];?>";
		$('#sel_codes_view').datagrid({
				url:urlLink
			});	
			
		urlLink= "../../forms/procedure_order/functions.php?mode=PrevICD&id=<?php echo $_GET["id"];?>";
		$('#sel_IcdCode_view').datagrid({
				url:urlLink
			});	
		var selected_priority = "<?php echo $sql ?>";
		if(selected_priority != ''){
			if(selected_priority == "1")
			{
				$("#order_priority").val('1');
			}else{
				$("#order_priority").val('2');
			}
		}else{
			$("#order_priority").val('');
		}
					
			$('#sel_codes_view').datagrid({
						onSelect : function(rowIndex, rowData ) {
										var labCode='';
										var LabrowData = $('#order_view').datagrid('getSelected');										
								if (LabrowData != null) {
									labCode=LabrowData.id;											
								}								
								urlLink= "../../forms/procedure_order/functions.php?mode=loadCompendium&name=" + labCode+"&code="+rowData.codename+"&procedureId=<?php echo $_GET["id"];?>";
									$.ajax({
										url : urlLink,
										type : "POST",
										cache : false,
										dataType : "json",
										success : function(result) {
												dlgItems.innerHTML = result;
												if(result!=null)
												{
												$('#dlgItems').dialog('open').dialog('setTitle', 'Compendium Details');
												$('#fm').form('clear');
												}												
										}
									});															
						}
					});						
});



	// This invokes the find-procedure-type popup.
	var ptvarname;
	function sel_proc_type(varname) {
		var f = document.forms[0];
		if ( typeof varname == 'undefined')
			varname = 'form_proc_type';
		ptvarname = varname;
		dlgopen('../../orders/types.php?popup=1&order=' + f[ptvarname].value, '_blank', 800, 500);
	}

	// This is for callback by the find-procedure-type popup.
	// Sets both the selected type ID and its descriptive name.
	function set_proc_type(typeid, typename) {
		var f = document.forms[0];
		f[ptvarname].value = typeid;
		f[ptvarname + '_desc'].value = typename;
	}
	

	//Order List ( Lab Service, Imaging,X-Ray,External/ Internal);
	function generateOrderList(rootdir)
	{
		var selected=document.getElementById('order_type');
		var sel = selected.options[selected.options.selectedIndex].text;
		var values ='';
			var $sltObj = selected;		
			var opts = $sltObj.options;	
			
			for (var i = 0; i < opts.length; i++) {								
				if(opts[i].selected == true)
				values += opts[i].value+ '@@@';				
			}
			
			urlLink= rootdir + "/forms/procedure_order/functions.php?mode=orderlist&name=" + values;
			$('#order_view').datagrid({
				url:urlLink
			});			
	}
	
	//Order List ( Lab Service, Imaging,X-Ray,External/ Internal);
	
	// Codes List (CPT, ICD,LOINC, SNOMED)
	function generateCodesList(rootdir)
	{
		var selected_value = $("#code_type option:selected").text();
		var values ='';	
		var str_function;
		var selected=document.getElementById('code_type');    //Code_type ICD =2, CPT =1, LOINC=104, CVX =100			
		var sltObj = selected;
		var opts = sltObj.options;				
			for (var i = 0; i < opts.length; i++) {								
				if(opts[i].selected == true)
				{			
				if(opts[i].value != '')
					{	
						$.ajax({
						   url:  rootdir + "/forms/procedure_order/functions.php?func=lonicPickList&selected_value="+selected_value,
							async:false,
						   success: function (response) {
							 str_function = response;
							 $('#picklist_id').html(str_function); 
						   }
						});	
						
						$('#lonic_pickList','#lonicSearch').hide();
						$('#picklist_view').show();					
					}
					else{
						$('#lonic_pickList','#lonicSearch').show();						
						$('#picklist_view').hide();
						values=opts[i].value ;						
					}				
				}					
			}	
	}
	
	
	//*************************Show Pick list Drop down when LOINC Code selected from Codes Type. */
	function generatePickList(rootdir) {
		var selected=document.getElementById('picklist_id');		
		var selpick = selected.options[selected.options.selectedIndex].text;
				
		urlLink= rootdir + "/forms/procedure_order/functions.php?mode=picklist&name=" + selpick;
		$('#codes_view').datagrid({
				url:urlLink
			});	
	}	
	
	
	//Search textbox for codes search
	function searchCodes(picklist, rootdir) {		
		var selected = picklist.value;
		var select=document.getElementById('code_type');
		var type = select.options[select.options.selectedIndex].value;			
		urlLink= rootdir + "/forms/procedure_order/functions.php?mode=SearchCodes&name=" +  selected+"&type=" +type;
		$('#codes_view').datagrid({
				url:urlLink
			});			
	}	
	
	
	
	//Show the ICD List
	function showICDList(icdSearch,rootdir) {	
		if(icdSearch!='' && icdSearch.length >2)
		{	
			urlLink= rootdir + "/forms/procedure_order/functions.php?mode=icdlist&name=" + icdSearch;
			$('#icdCode_view').datagrid({
				url:urlLink
			});			
		}
	}	
	
	
	
	function generateCodesType(rootdir)
	{
		var selected=$("#picklist_id option:selected").text();
		
			urlLink= rootdir + "/forms/procedure_order/functions.php?mode=codelist&name=" + selected;
			$('#codes_view').datagrid({
				url:urlLink
			});			
				
	}
	
		
	function addselectedLOINC()
	{		
		var rows = $('#codes_view').datagrid('getSelections');	
				
				if (rows != null && rows.length > 0) {				
				for (var i = 0; i < rows.length; i++) {
					$('#sel_codes_view').datagrid('appendRow',{
						codename: rows[i].code,						
						codetext: rows[i].code_text
					});						
				}
			}
	}

	function removeLOINC()
	{		
		
		var rows = $('#sel_codes_view').datagrid('getSelections');  // get all selected rows
		var ids = [];
		for(var i=0; i<rows.length; i++){
			var index = $('#sel_codes_view').datagrid('getRowIndex',rows[i]);  // get the row index
			ids.push(index);
		}
		ids.sort();  // sort index
		ids.reverse();  // sort reverse
			for(var i=0; i<ids.length; i++){
			$('#sel_codes_view').datagrid('deleteRow',ids[i]);			
		}		
		
	}


	function addselectedICD()
	{		
		var rows = $('#icdCode_view').datagrid('getSelections');
				
			if (rows != null && rows.length > 0) {				
			for (var i = 0; i < rows.length; i++) {
				$('#sel_IcdCode_view').datagrid('appendRow',{
					codename: rows[i].code,						
					codetext: rows[i].code_text
				});						
			}
		}
	}


	
	function removeICD()
	{		
		var rows = $('#sel_IcdCode_view').datagrid('getSelections');  // get all selected rows
		var ids = [];
		for(var i=0; i<rows.length; i++){
			var index = $('#sel_IcdCode_view').datagrid('getRowIndex',rows[i]);  // get the row index
			ids.push(index);
		}
		ids.sort();  // sort index
		ids.reverse();  // sort reverse
			for(var i=0; i<ids.length; i++){
			$('#sel_IcdCode_view').datagrid('deleteRow',ids[i]);			
		}	
	}
	
	
	
	function CustomizePickList(rootdir)
	{		
		var selected_value_customize = $("#code_type option:selected").text();
		var select=document.getElementById('code_type');
		var type = select.options[select.options.selectedIndex].value;
		dlgopen(rootdir+'/forms/procedure_order/customizePicklist.php?selected_value_customize='+selected_value_customize+'&selected_code='+type, '_blank', 750, 350);
		return false;
	}


	function SaveCompendium()
	{   
		 var answers='' ;
   		 $.each($('.answer'), function() {
        	answers+='@@@'+$(this).val();
    	}); 
    	
    	var questions='';
    	 $.each($('.question'), function() {
        	questions+='@@@'+$(this).val();
    	});
 
 
 		var procedureCode=$('.procedure').val();
    		    	
    	var LabrowData = $('#order_view').datagrid('getSelected');
										
		if (LabrowData != null) 
		{
				labCode=LabrowData.id;											
		}    	
    	urlLink='../../forms/procedure_order/functions.php?mode=saveCompendium&answer='+answers+'&question='+questions+'&labCode='+labCode+'&procedureCode='+procedureCode+"&procedureId=<?php echo $_GET["id"];?>";
		$.ajax({
			type : 'POST',
			url : urlLink,			
			success : function(result) {				
			},
				error: function(data){
					alert(data);
				}			
		});  
    	$('#dlgItems').dialog('close');
	}



	function CancelCompendium()
	{	
		$('#dlgItems').dialog('close');	
	}

	function validate(){
		var rows = $('#sel_codes_view').datagrid('getRows');
		var selectedCodes=$('#selectedLOINC').val();
		if (rows != null && rows.length > 0) {				
				for (var i = 0; i < rows.length; i++) {	
					selectedCodes+='@@@'+rows[i].codename+':'+rows[i].codetext;	
					$('#selectedLOINC').val(selectedCodes);	
				}
			}
			
		var rows = $('#sel_IcdCode_view').datagrid('getRows');
		selectedCodes=$('#selectedICD').val();
		if (rows != null && rows.length > 0) {				
				for (var i = 0; i < rows.length; i++) {	
					selectedCodes+='@@@'+rows[i].codename+':'+rows[i].codetext;	
					$('#selectedICD').val(selectedCodes);	
					}	
				}
				
				
				var rows = $('#order_view').datagrid('getChecked');		
		selectedCodes=$('#selectedLabCode').val();		
		if (rows != null && rows.length > 0) {
				for (var i = 0; i < rows.length; i++) {	
					selectedCodes=rows[i].id;	
					$('#selectedLabCode').val(selectedCodes);	
					}	
				}						
				document.procedureOrder.submit();
			}



</script>

</head>

<body class="body_top">
<?php
include_once("$srcdir/api.inc");
$id=$_GET["id"];

$obj = sqlQuery("SELECT * FROM procedure_order WHERE " . "procedure_order_id = '$id' AND activity = '1'");
?>
<form method="post"	action="<?php echo $rootdir; ?>/forms/procedure_order/save.php?mode=update&id=<?php echo $_GET["id"];?>" name="procedureOrder" onsubmit="return top.restoreSession()">	

<p class='title' style='margin-top:8px;margin-bottom:8px;text-align:center'>
<?php
echo xl('Procedure Order Form');?>
</p>

<center>

 <table align="center"  border="0px"  style="border:1px solid grey" cellspacing="5px" cellpadding="5px" width="85%" class="formtable">
  <tr><td>
<fieldset>
    <legend style="color:blue;"><b>Patient Details </b></legend>
    <table align="center"  border="0px" width="100%">

<?php $ptid = -1;
// -1 means no order is selected yet
$ptrow = array('name' => '');
if (!empty($row['procedure_type_id'])) {
	$ptid = $row['procedure_type_id'];
	$ptrow = sqlQuery("SELECT name FROM procedure_type WHERE " . "procedure_type_id = '$ptid'");
}
?>

<!-- For Procedure Order Change -->


<!--   Patient Name -->
<tr>
<td align="left" scope="row" width="25%" ><b><?php xl('Patient Name', 'e'); ?>: </b></td>
<td valign="top" width="75%">
<input type="text" size='40' id="patient_name" value="<?php PatientName() ?>" readonly/>
</td>
</tr>

<!-- Patient Insurance -->

<tr>
<td width='1%' nowrap><b><?php xl('Insurance Info', 'e'); ?>: </b></td>
<td>
<input type="text" size='40' id="patient_name" value="<?php patientInsurance() ?>" readonly/>
</td>
</tr>

</table>
	</fieldset>



<br/> <br/>
	<fieldset>
    <legend style="color:blue;"> <b> <?php xl('Order Details','e')?> </b> </legend>
    <table align="center"  border="0px" width="100%">
	
	
	
<!-- Order Type Drop Down -->


<tr>
<td align="left" scope="row" width="25%"><b><?php xl('Lab Code', 'e'); ?>: </b></td>
<td valign="top" width="75%">
<select name="order_type"  id="order_type" multiple="multiple" style="width:300px" size="5" onclick="generateOrderList('<?php echo $rootdir; ?>')"><?php OrderType(); ?></select>
<div> </div>
<table class="easyui-datagrid" style="width:450px;height:150px" id="order_view" data-options="singleSelect:true">  
    <thead>  
        <tr>  
            <th field="ck" checkbox="true"></th>  
            <th data-options="field:'abook_type',width:100 "> <?php xl('Type','e')?></th>  
            <th data-options="field:'fname',width:100"><?php xl('Name','e')?></th> 
            <th data-options="field:'organization',width:250"><?php xl('Organization','e')?></th>  
        </tr>  
    </thead>  
</table>
</td>
</tr>  <!-- selectedLabCode -->
<tr><td colspan="2"><input type="hidden" name="selectedLabCode" id="selectedLabCode" value=''/></td></tr>



<!-- Ordering Provider -->
<tr>
<td width='1%' nowrap><b><?php xl('Ordering Provider', 'e'); ?>:
</b></td>
<td>
<select name="provider_id"  id="provider_id" ><?php ProviderList(stripslashes($obj{"provider_id"})); ?></select>
</td>
</tr>

<!-- Multi select Lab/imaging/x-ray Codes (Order Type)-->
<tr><td colspan="2">&nbsp;</td></tr>

<!-- Multi select Lab/imaging/x-ray Codes (Order Type)-->

<tr>
<td width='1%' nowrap><b><?php xl('Lab Test Code', 'e'); ?>: </b></td>
<td> 
	<table><tr><td colspan="2"><!-- multiple="multiple" -->
<select name="code_type"  id="code_type" style="width:200px"  onchange="generateCodesList('<?php echo $rootdir; ?>')"><?php CodeType(); ?></select>
	&nbsp;&nbsp;&nbsp;<input type="text" size='15'  id="lonic_pickList" value="" style="vertical-align: top"/> 
  	<input type="button" name="lonicSearch" id="lonicSearch" value="Go" onclick="searchCodes(document.getElementById('lonic_pickList'),'<?php echo $rootdir; ?>')" style="vertical-align: top"/>
 </td></tr>
 
 <tr><td colspan="2">
<div id='picklist_view' style="display:none" padding="10px"> <br/>
	Search From PickList : <select  name="picklist_id" id="picklist_id" onchange=" generateCodesType('<?php echo $rootdir; ?>')">  </select>
	&nbsp;&nbsp; <input type='button' name='showsearchloinc' id='showsearchloinc' value="Manage Picklist" onclick="CustomizePickList('<?php echo $rootdir; ?>')">	
</div>
</td></tr>

<tr><td>
<table class="easyui-datagrid" style="width:300px;height:150px" id="codes_view" data-options="title :'Lab Test Codes'">  
    <thead>  
        <tr>  
            <th field="ck" checkbox="true"></th>  
            <th data-options="field:'code',width:100 "><?php xl('Code','e')?></th>  
            <th data-options="field:'code_text',width:180"><?php xl('Description','e')?></th>             
        </tr>  
    </thead>  
</table>
</td>

<td> <table class="easyui-datagrid" style="width:300px;height:150px" id="sel_codes_view" data-options="title :'Selected Test Codes'">  
    <thead>  
        <tr>  
            <th field="ck" checkbox="true"></th>  
            <th data-options="field:'codename',width:100 "><?php xl('Code','e')?></th>  
            <th data-options="field:'codetext',width:180"><?php xl('Description','e')?></th>             
        </tr>  
    </thead>  
</table></td>

</tr>

<tr><td><input type='button' name='add_LOINC' id='add_LOINC' value="Add to Selected List" onclick="addselectedLOINC()"/></td>
	
	<td><input type='button' name='remove_LOINC' id='remove_LOINC' value="Remove Selected Codes" onclick="removeLOINC()"/></td>
</tr>
</table>

</td>
</tr>

<tr><td><input type="hidden" name="selectedLOINC" id="selectedLOINC" value=''/></td></tr>

<tr><td colspan="2">&nbsp;</td></tr>

<!-- Diagnosis Codes -->
<tr>
<td width='1%' nowrap><b><?php xl('Diagnosis Codes', 'e'); ?>: </b></td>
<td>
	<table><tr><td colspan="2">
<input type="text" size='20'  id="icd_id" value="" name="icd_id" title="Please select ICD Code"/>
<input type="button" name="icdSearch" id="icdSearch" value="Go" onclick="showICDList(document.getElementById('icd_id').value,'<?php echo $rootdir; ?>')"/>
</td></tr>
<tr><td> <table class="easyui-datagrid" style="width:300px;height:150px" id="icdCode_view" data-options="title :'Diagnosis Codes'">  
    <thead>  
        <tr>  
            <th field="ck" checkbox="true"></th>  
            <th data-options="field:'code',width:100 "><?php xl('Code','e')?></th>  
            <th data-options="field:'code_text',width:180"><?php xl('Description','e')?></th>             
        </tr>  
    </thead>  
</table></td>
	<td>
<table class="easyui-datagrid" style="width:300px;height:150px" id="sel_IcdCode_view" data-options="title :'Selected Diagnosis Codes'">  
    <thead>  
        <tr>  
            <th field="ck" checkbox="true"></th>  
            <th data-options="field:'codename',width:100"><?php xl('Code','e')?></th>  
            <th data-options="field:'codetext',width:180"><?php xl('Description','e')?></th>             
        </tr>  
    </thead>  
</table>
</td></tr>
<tr><td><input type='button' name='add_ICD' id='add_ICD' value="Add to Selected Diagnosis" onclick="addselectedICD()"/></td>
		<td><input type='button' name='remove_ICD' id='remove_ICD' value="Remove Selected Codes" onclick="removeICD()"/></td>
	
</tr>
</table>


</td>
</tr>

<tr><td colspan="2"><input type="hidden" name="selectedICD" id="selectedICD" value=''/></td></tr>

<tr>
<td width='1%' nowrap><b><?php xl('Fasting', 'e'); ?>: </b></td>
<td><input type="radio" id="order_completion_fasting" name="order_completion_fasting" value="Yes"  value="Yes" 
  		 <?php if (stripslashes($obj{"order_completion_fasting"} == "Yes")) echo "checked";;?>/><?php xl('Yes','e')?>
<input type="radio" id="order_completion_fasting" name="order_completion_fasting" value="No"
 value="Yes" 
  		 <?php if (stripslashes($obj{"order_completion_fasting"} == "No")) echo "checked";;?>/><?php xl('No','e')?>
</td>
</tr>

<tr><td colspan="2">&nbsp;</td></tr>
<tr>
<td width='1%' nowrap><b><?php xl('Priority', 'e'); ?>:
</b></td>
<td>
	<select name="order_priority"  id="order_priority"><?php PrioritySelect(); ?></select>
</td>
</tr>

<tr>
<td width='1%' nowrap><b><?php xl('Date Ordered', 'e'); ?>:
</b></td>
<td><input type='text' size='20' name='date_ordered' id='date_ordered' 
					title='<?php xl('yyyy-mm-dd Order Date', 'e'); ?>'
					onkeyup='datekeyup(this,mypcc)' onblur='dateblur(this,mypcc);'  value="<?php echo stripslashes($obj{"date_ordered"});?>" readonly/> 
					<img src='../../pic/show_calendar.gif' align='absbottom' width='24'
					height='22' id='img_order_date' border='0' alt='[?]'
					style='cursor: pointer; cursor: hand'
					title='<?php xl('Click here to choose Order date', 'e'); ?>'> 
					<script	LANGUAGE="JavaScript">
						Calendar.setup({inputField:'date_ordered', ifFormat:'%Y-%m-%d %H:%M:%S', button:'img_order_date', showsTime:'true'});
   </script> </td>

</tr>


<tr>
<td width='1%' nowrap><b><?php xl('Status', 'e'); ?>:
</b></td>
<td>
<select  name="order_status" id="order_status"> <?php orderStatus(stripslashes($obj{"order_status"})); ?> </select>
</td>
</tr>


<!--Instruction Header -->

<tr>
<td width='1%' nowrap><b><?php xl('Patient Instructions', 'e'); ?>:
</b></td>
<td>
<textarea rows='3' cols='70' name='patient_instructions' size="35px" id='patient_instructions'/><?php echo stripslashes($obj{"patient_instructions"});?></textarea>
</td>
</tr>
</table>

</fieldset>

<br/> <br/>
	<fieldset>
    <legend style="color:blue;"> <b> <?php xl('Specimen Details','e')?> </b> </legend>
    <table align="center"  border="0px" width="100%">



<tr>
<td align="left" scope="row" width="25%"><b><?php xl('Specimen Type', 'e'); ?>:</b></td>
<td valign="top" width="75%">
	<select  name="specimen_type" id="specimen_type"> <?php specimenType(stripslashes($obj{"specimen_type"})); ?> </select>
</td>
</tr>


<tr>
<td width='1%' nowrap><b><?php xl('Specimen Location', 'e'); ?>:
</b></td>
<td>
	<select  name="specimen_location" id="specimen_location"> <?php specimenLocation(stripslashes($obj{"specimen_location"})); ?> </select>
</td>
</tr>

<tr>
<td width='1%' nowrap><b><?php xl('Collection Date', 'e'); ?>:
</b></td>
<td><input type='text' size='20' name='specimen_collection_date' id='specimen_collection_date' 
					title='<?php xl('yyyy-mm-dd Order Date', 'e'); ?>'
					onkeyup='datekeyup(this,mypcc)' onblur='dateblur(this,mypcc);' value="<?php echo stripslashes($obj{"specimen_collection_date"});?>" readonly/> 
					<img src='../../pic/show_calendar.gif' align='absbottom' width='24'
					height='22' id='img_collection_date' border='0' alt='[?]'
					style='cursor: pointer; cursor: hand'
					title='<?php xl('Click here to choose Order date', 'e'); ?>'> 
					<script	LANGUAGE="JavaScript">
						Calendar.setup({
							inputField : "specimen_collection_date",
							ifFormat : "%Y-%m-%d",
							button : "img_collection_date"
						});
   </script> </td>

</tr>

<tr>
<td width='1%' nowrap><b><?php xl('Collection Time', 'e'); ?>:
</b></td>
<td>
	<input type='text' size='2' name='form_hour' value="<?php timeDisplay(stripslashes($obj{"specimen_collection_time"}));?>"
    title='<?php echo xla('Event start time'); ?>' /> :
   <input type='text' size='2' name='form_minute' value='<?php minutesDisplay(stripslashes($obj{"specimen_collection_time"})); ?>'
    title='<?php echo xla('Event start time'); ?>' />&nbsp;
   <select name='form_ampm' title='<?php echo xla("Note: 12:00 noon is PM, not AM"); ?>'>
    <option value='AM' <?php am(stripslashes($obj{"specimen_collection_time"}))?> ><?php echo xlt('AM'); ?> </option>
    <option value='PM' <?php pm(stripslashes($obj{"specimen_collection_time"}))?> ><?php echo xlt('PM'); ?></option>
   </select>
  </td>
</tr>
               

<tr>
<td width='1%' nowrap><b><?php xl('Specimen volume (ml)', 'e'); ?>:
</b></td>
<td>
<input type="text" size='15'  id="specimen_volume" value="<?php echo stripslashes($obj{"specimen_volume"});?>" name="specimen_volume" title="Please specimen Volume"/>
</td>
</tr>
</table>
</fieldset>
<br/> <br/>

<div id="compendum"></div>

</td></tr>

</table>
</center>
<p>
<a href="javascript:top.restoreSession();validate();"
			class="link_submit"><b><?php xl(' [Save]','e')?></b></a>
			&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
<a href="<?php echo $GLOBALS['form_exit_url']; ?>" class="link" style="color: #483D8B"
 onclick="top.restoreSession()">[<b><?php xl('Don\'t Save', 'e'); ?></b>]</a>
</p>

</form>
</body>
</html>


<?php 	include ("compendium.php"); ?>
