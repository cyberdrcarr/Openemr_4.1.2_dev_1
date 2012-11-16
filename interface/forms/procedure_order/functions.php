<?php

include_once ("../../globals.php");
include_once ("$srcdir/api.inc");
include_once ("$srcdir/forms.inc");

$mode = strip_tags(trim($_REQUEST['mode']));
$picklist = strip_tags(trim($_REQUEST['name']));
$type = strip_tags(trim($_REQUEST['type']));
$loinccode= strip_tags(trim($_REQUEST['code']));


$answers= strip_tags(trim($_REQUEST['answer']));
$questions= strip_tags(trim($_REQUEST['question']));
$labCode= strip_tags(trim($_REQUEST['labCode']));
$procedureCode= strip_tags(trim($_REQUEST['procedureCode']));
$procedureId= strip_tags(trim($_REQUEST['procedureId']));


switch($mode) {
	case 'orderlist' :
		generateLabList($picklist);
		break;
	
	case 'codeslist' :
		generateCodesList($picklist);
		break;

	case 'SearchCodes':
		SearchCodesList($picklist,$type);
		break;

	case 'icdlist' :
		generateICDList($picklist);
		break;	
			
	case 'picklist':
		generatePickList($picklist,$type);
		break;	
		
	case 'PrevLOINC':
		PrevLOINC();
		break;			
		
	case 'PrevICD':
		PrevICD();
		break;	
		
	case 'PrevLabTest':
		PrevLabTest();
		break;		
		
	case 'loadCompendium':	
		loadCompendium($picklist,$loinccode,$procedureId);
		break;	
		
	case 'saveCompendium':		
		saveCompendium($answers,$questions,$labCode,$procedureCode,$procedureId);
		break;
		
	case 'removeLoincCode':
		removeLoincCode($picklist);
		break;		
	
	case 'addLoincCode' :
		addLoincCode($picklist,$codeText,$type);
		break;	
	
	case 'codelist' :
		generatePickList($picklist,$type);
		break;
		
}

//****************** Patient Name from the Pis Session value
function patientName() {
	$select = sqlStatement("select fname,mname,lname from patient_data where pid=" . $_SESSION['pid']);
	while ($Row = sqlFetchArray($select)) {
		$fname = $Row['fname'];
		$lname = $Row['lname'];
		$mname = $Row['mname'];
		echo $fname . " " . $mname . " " . $lname;
	}
}

//****************** Patient Name from the Pis Session value
function patientInsurance() {
	$select = sqlStatement("select * from insurance_data where pid=" . $_SESSION['pid'] . " && type='primary'");
	while ($Row = sqlFetchArray($select)) {
		$planName = $Row['plan_name'];
		$relationship = $Row['subscriber_relationship'];
		$policyNumber = $Row['policy_number'];
		echo $policyNumber . " , " . $planName . ",  " . $relationship;
	}
}

//******************** Generate the Priority List from list_options table
function PrioritySelect($selected = '') {
	$query = "SELECT * FROM list_options WHERE list_id='lab_priority' order by seq";
	$res = sqlStatement($query);
	echo "    <option value=''>" . text("Please Select..") . "\n";
	while ($row = sqlFetchArray($res)) {
		$priorityid = $row['seq'];
		echo "    <option value='" . attr($priorityid) . "'";
		if ($selected != '' && $selected == $priorityid)
			echo " selected";
		echo ">" . text($row['title']) . "\n";
	}
}

/* --------------Provider list  ------------------------------*/
function ProviderList($selected = '') {
	$query = "SELECT id, lname, fname FROM users WHERE " . "( authorized = 1 OR info LIKE '%provider%' ) AND username != '' " . "AND active = 1 AND ( info IS NULL OR info NOT LIKE '%Inactive%' ) " . "ORDER BY lname, fname";
	$authid=$_SESSION['authId'];
	$res = sqlStatement($query);
	while ($row = sqlFetchArray($res)) {
		$Userid = $row['id'];		
		echo "    <option value='" . attr($Userid) . "'";
		if ($selected == '' && $Userid == $authid)		
			echo " selected";
		else if($Userid==$selected )
			{
				echo " selected";
			}
		echo ">" . text($row['lname'] . ", " . $row['fname']) . "\n";
	}
}


//multiple="multiple"

//******************** Generate the Order List from list_options table
function OrderType($selected = '') {
	$query = "SELECT * FROM list_options WHERE list_id='abook_type' && option_id REGEXP 'inhouse_lab|ord_lab|ord_xray|spe|proc' order by seq";
	$res = sqlStatement($query);
	
	while ($row = sqlFetchArray($res)) {
		$Orderid = $row['option_id'];
		echo "    <option value='" . attr($Orderid) . "'";
		if ($selected != '' && $selected == $Orderid)
			echo " selected";
		echo ">" . text($row['title']) . "\n";
	}
}


//******************** Generate the Labs/Imaging from the Multiselect Drop Down
function generateLabList($OrderType) {		
	$codeTypeList =@explode('@@@', $OrderType);		
	$result='';
	$items = array();
	foreach($codeTypeList as $codeType)
	{		
		$tmp = "abook_type LIKE '$codeType' && abook_type !=''"; //"abook_type LIKE 'ord\\_%'";
		$rs = sqlStatement("SELECT * FROM users " .
			"WHERE active = 1 AND ( info IS NULL OR info NOT LIKE '%Inactive%' ) " .
					"AND $tmp " .
					"ORDER BY organization, lname, fname");
	
		while($row = @mysql_fetch_object($rs)){					
			array_push($items, $row);
			}
	}	
	$result["rows"] = $items;		     
	echo json_encode($result);			
}

			
function CodeType($selected = '') {
	$query = "SELECT * FROM code_types where ct_key REGEXP 'LOINC|SNOMED|CPT4' ";
	$res = sqlStatement($query);
	echo "    <option value=''>" . text("Please Select..") . "\n";
	while ($row = sqlFetchArray($res)) {
		$priorityid = $row['ct_id'];
		echo "    <option value='" . attr($priorityid) . "'";
		if ($selected != '' && $selected == $priorityid)
			echo " selected";
		echo ">" . text($row['ct_label']) . "\n";
	}
}


//******************** Generate the CPT/SNOMED Codes on drop down select
function generateCodesList($codeTypes) {
	$result='';
	$items = array();
	$rs = sqlStatement("select code,code_text from codes where code_type=$codeTypes order by code");		
	while($row = @mysql_fetch_object($rs)){					
			array_push($items, $row);
		}	
		$result["rows"] = $items;	     
		echo json_encode($result);
		
}
	

//******************** Generate the Lonic codes from the Pick list selected List

if(isset($_GET['func'])) {
  lonicPickList();
}
function lonicPickList($selected = '') {	
	$query = "SELECT * FROM list_options where list_id='".$_GET['selected_value']."_Picklist'  order by seq";
	$res = sqlStatement($query);
	echo " <option value=''>" . text("Please Select..") . "\n";
	while ($row = sqlFetchArray($res)) {
		$picklist_id = $row['picklist_id'];
		echo "    <option value='" . attr($picklist_id) . "'";
		if ($selected != '' && $selected == $picklist_id) {
			echo " selected";
		}
		echo ">" . text($row['title']) . "\n";
	}
}

function generatePickList($picklistname,$type) {
	$items = array();
	$result = "";
	$rs = sqlStatement("SELECT code,code_text FROM codes WHERE  " . "(code_text_short LIKE '%" . $picklistname . "%') && (code <> NULL OR code <> '') ORDER BY code");
		
		while($row = @mysql_fetch_object($rs)){					
			array_push($items, $row);
		}	
		$result["rows"] = $items;	     
		echo json_encode($result);
}



function SearchCodesList($searchname,$type) {
	$items = array();
	$result = "";
	$rs = sqlStatement("SELECT code,code_text FROM codes WHERE  " . "(code_text LIKE '%" . $searchname . "%' OR code LIKE '%" . $searchname . "%') && (code <> NULL OR code <> '') && code_type= $type ORDER BY code");
		
		while($row = @mysql_fetch_object($rs)){					
			array_push($items, $row);
		}	
		$result["rows"] = $items;	     
		echo json_encode($result);	
}



//******************** Generate the ICd Codes with the filter in the textbox
function generateICDList($icdname) {
	if($icdname !='')	
	{
		$items = array();
	$result = "";
	$rs = sqlStatement("select code,code_text from codes where " . " (code_text LIKE '%" . $icdname . "%' OR code LIKE '%" . $icdname . "%') && code_type=2 order by code");
		
		while($row = @mysql_fetch_object($rs)){					
			array_push($items, $row);
		}	
		$result["rows"] = $items;	     
		echo json_encode($result);	
	}
}


//******************** Generate the Priority List from list_options table
function specimenType($selected = '') {
	$query = "SELECT * FROM list_options WHERE list_id='Specimen_Type' order by seq";
	$res = sqlStatement($query);
	echo "    <option value=''>" . text("Please Select..") . "\n";
	while ($row = sqlFetchArray($res)) {
		$typeId = $row['option_id'];
		echo "    <option value='" . attr($typeId) . "'";
		if ($selected != '' && $selected == $typeId)
			echo " selected";
		echo ">" . text($row['title']) . "\n";
	}
}



//******************** Generate the Priority List from list_options table
function specimenLocation($selected = '') {
	$query = "SELECT * FROM list_options WHERE list_id='Specimen_Location' order by seq";
	$res = sqlStatement($query);
	echo "    <option value=''>" . text("Please Select..") . "\n";
	while ($row = sqlFetchArray($res)) {
		$LocationId = $row['option_id'];
		echo "    <option value='" . attr($LocationId) . "'";
		if ($selected != '' && $selected == $LocationId)
			echo " selected";
		echo ">" . text($row['title']) . "\n";
	}
}


//******************** Generate the Priority List from list_options table
function orderStatus($selected = '') {
	$query = "SELECT * FROM list_options WHERE list_id='order_status' order by seq";
	$res = sqlStatement($query);
	echo "    <option value=''>" . text("Please Select..") . "\n";
	while ($row = sqlFetchArray($res)) {
		$LocationId = $row['option_id'];
		echo "    <option value='" . attr($LocationId) . "'";
		if ($selected != '' && $selected == $LocationId)
			echo " selected";
		echo ">" . text($row['title']) . "\n";
	}
}


function timeDisplay($time)
{
	$titles = @explode(':', $time); 
	if($titles[0] > 12)
	{
	$hour= $titles[0];
	$hour=$hour-12; echo $hour;
	}
	else echo $titles[0];
}

function minutesDisplay($time)
{
	$titles = @explode(':', $time); 
	echo $titles[1];
}


function am($time)
{
	$titles = @explode(':', $time); 
	if($titles[2] =='AM')
	{
		echo 'selected="selected"';
	}
	else echo '';	
}

function pm($time)
{
	$titles = @explode(':', $time); 
	if($titles[2] =='PM')
	{
		echo 'selected="selected"';
	}
	else echo '';	
	
}


function PrevLabTest()
{
	
	$items = array();
	$result = "";
	$res = sqlStatement("select * from procedure_order where  procedure_order_id= ".$_GET['id']);
	while($row = @mysql_fetch_object($res)){				
				$id = intval($row -> lab_id);				
				$labResult= sqlStatement("select * from users where id=$id && active=1");			
					while($lab = @mysql_fetch_object($labResult)){						
							array_push($items, $lab);
					}
			}
	//print_r($items);		
		$result["rows"] = $items;
		//print_r($result);	     
		echo json_encode($result);
}


function PrevLOINC()
{
	$items = array();
	$result = "";	
	$rs = sqlStatement("select * from procedure_order_code where  forms_procedure_id = ".$_GET['id']." and codetype=2");		
		while($row = @mysql_fetch_object($rs)){					
			array_push($items, $row);
		}	
		$result["rows"] = $items;	     
		echo json_encode($result);
	
	
	
}


function PrevICD()
{
		
	$items = array();
	$result = "";
	$rs = sqlStatement("select * from procedure_order_code where forms_procedure_id = ".$_GET['id']." and codetype=1");
		
		while($row = @mysql_fetch_object($rs)){					
			array_push($items, $row);
		}	
		$result["rows"] = $items;	     
		echo json_encode($result);
}

function removeLoincCode($Selcodes)
{
		$codes =@explode('@@@', $Selcodes);
			foreach($codes as $code)
			{
				 $Loinccode = trim($code);
				if($Loinccode!="")
				{
					$sql ="delete from codes where code='$Loinccode'";
					$result=@mysql_query($sql);	
					}
				}		
			
		echo json_encode(array('success'=>true));	
			
}


function addLoincCode($Selcodes,$codeText,$pickListName)
{
		$codes =@explode('@@@', $Selcodes);
		$codesText =@explode('@@@', $codeText);
			$count=0;	
			foreach($codes as $code)
			{
				 $Loinccode = trim($code);
				if($Loinccode!="")
				{
					$sql ="insert into codes(code,code_text,code_text_short,code_type)values('$Loinccode','$codesText[$count]','$pickListName',104)";
					$result=@mysql_query($sql);	
					}
				$count++;
				}			
		echo json_encode(array('success'=>true));				
}

function loadCompendium($labName,$LOINCCode,$procedureTableId)
{	
	$result = "";
	
	$selectProc = sqlStatement("SELECT procedure_code FROM procedure_type WHERE  standard_code= 'LOINC:$LOINCCode' && lab_id=$labName");	
		$count = @mysql_num_rows($select);
	while ($ProcId = sqlFetchArray($selectProc)) {
		$procedureId=$ProcId['procedure_code'];	
		
		$select = sqlStatement("SELECT * FROM procedure_compendium WHERE  procedure_code= $procedureId  && procedure_order_id=$procedureTableId");
		$count = @mysql_num_rows($select);
		if ($count > 0) {
				$result .= '<form id="fmnewcategory" action="<?php echo $rootdir; ?>/forms/procedure_order/save.php?mode=compendium" method="post" onsubmit="return false;" novalidate><div class="fitem"><table><tr>';
			//$count = 1;
			
			$items='';
			//$rs = sqlStatement("select one.question_text as question_text from procedure_questions as one, procedure_compendium as two where two.procedure_code=$procedureId && one.procedure_code=$procedureId GROUP BY one.procedure_code");
			$rs = sqlStatement("SELECT * FROM procedure_questions WHERE  procedure_code= $procedureId");
				while($row = @mysql_fetch_object($rs)){					
					$items.='@@@'.$row->question_text;
				}
				
			$questionslist = @explode('@@@', $items);
				$questioncount=1; 
			
			while ($Row = sqlFetchArray($select)) {
						
				$code=$Row['procedure_code'];			
				$questiontext = $questionText['question_text'];		
				$questionCode= $Row['question_code'];
				$answer=$Row['answer'];	
					
						
				$result .= "<td align='left' scope='row' width='40%' name='$questionCode'><b>$questionslist[$questioncount]</b><input type='hidden' id='$questionCode' value=$questionCode class='question'/>
				<input type='hidden' id='$procedureId' value=$procedureId class='procedure'/></td><td valign='top' width='60%'>";
				$result.="<input type='text' size='25'  id='$questionCode' name='answers' title='Please enter an asnswer' class='answer' value='$answer'/> </td></tr><tr>";		
				//$count++;	
				$questioncount++;	
				}
				$result .= "</table></div><div>&nbsp;</div><div align='center'><input type='submit' class='RoundedCornerButtonMedium' onclick='SaveCompendium()' id='compendium_submit' value='OK'>";
				$result .="<input type='button' class='RoundedCornerButtonMedium' onclick='CancelCompendium()' name='Cancel'	value='Cancel'/></div></form>";
			
				echo json_encode($result);
			}
		
		else
		{
		$select = sqlStatement("SELECT * FROM procedure_questions WHERE  procedure_code= $procedureId");
		$count = @mysql_num_rows($select);
		if ($count > 0) {
		   
			$result .= '<form id="fmnewcategory" action="<?php echo $rootdir; ?>/forms/procedure_order/save.php?mode=compendium" method="post" onsubmit="return false;" novalidate><div class="fitem"><table><tr>';
			$count = 1;
			while ($Row = sqlFetchArray($select)) {
				$code=$Row['procedure_code'];			
				$questiontext = $Row['question_text'];		
				$questionCode= $Row['question_code'];
						
				$result .= "<td align='left' scope='row' width='40%' name='$questionCode'><b>$questiontext</b><input type='hidden' id='$questionCode' value=$questionCode class='question'/>
				<input type='hidden' id='$procedureId' value=$procedureId class='procedure'/></td><td valign='top' width='60%'>";
				$result.="<input type='text' size='25'  id='$questionCode' value='' name='answers_$count' title='Please enter an asnswer' class='answer'/> </td></tr><tr>";		
				$count++;		
				}
				$result .= "</table></div><div>&nbsp;</div><div align='center'><input type='submit' class='RoundedCornerButtonMedium' onclick='SaveCompendium()' id='compendium_submit' value='OK'>";
				$result .="<input type='button' class='RoundedCornerButtonMedium' onclick='CancelCompendium()' name='Cancel'	value='Cancel'/></div></form>";
			
				echo json_encode($result);
			}
		} 
	}
	
}





function saveCompendium($answers,$questions,$labCode,$procedureCode,$procedureTableId)
{
	$answerslist = @explode('@@@', $answers); 
	$questionslist = @explode('@@@', $questions);
	$count=0; 
	$test='';
	
	
	//print_r($questionslist);
	
	foreach($questionslist as $ques){
		//From new.php
		if($procedureTableId==0)
		{
			$displayQuestion = trim($ques);
				if($displayQuestion!="")
				{	
			$select = @mysql_query("SELECT * FROM procedure_compendium WHERE  procedure_code= $procedureCode && question_code='$displayQuestion' && procedure_order_id=0");
			$defaultNum = @mysql_num_rows($select);
			
			if ($defaultNum > 0) {
					$SqlUpdate="update procedure_compendium set answer='".$answerslist[$count]."',lab_code='".$labCode."',pid=".$_SESSION["pid"]."  where question_code= '$displayQuestion' && procedure_code=$procedureCode and procedure_order_id=$procedureTableId";	
	        		@mysql_query($SqlUpdate);	
				}			
			else
				{
					$Sql="insert into procedure_compendium (question_code,answer,lab_code,procedure_code,pid) values( '".$displayQuestion."', '".$answerslist[$count]."', '".$labCode."','".$procedureCode."',".$_SESSION["pid"].")";
					@mysql_query($Sql);				
				}
			}			
		}
		
		
		//view.php
		else
		{
			$displayQuestion = trim($ques);
				if($displayQuestion!="")
				{
					$select = @mysql_query("SELECT * FROM procedure_compendium WHERE  procedure_code= $procedureCode && question_code='$displayQuestion' && procedure_order_id=$procedureTableId");
					$defaultNum = @mysql_num_rows($select);
			
					if ($defaultNum > 0) {
						$SqlUpdate="update procedure_compendium set answer='".$answerslist[$count]."',lab_code='".$labCode."',pid=".$_SESSION["pid"]."  where question_code= '$displayQuestion' && procedure_code=$procedureCode and procedure_order_id=$procedureTableId";	
	        			@mysql_query($SqlUpdate);	
					}			
				else
					{
						$Sql="insert into procedure_compendium (question_code,answer,lab_code,procedure_code,pid,procedure_order_id) values( '".$displayQuestion."', '".$answerslist[$count]."', '".$labCode."','".$procedureCode."',".$_SESSION["pid"].",".$procedureTableId.")";
						@mysql_query($Sql);				
					}
				}
			
		}
	
		$count++;		
	}
}

























?>