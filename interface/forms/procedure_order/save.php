<?php
include_once("../../globals.php");
include_once("$srcdir/api.inc");
include_once("$srcdir/forms.inc");


$OrderedDate= $_POST["order_completion_date"]?$_POST["order_completion_date"]:date('Y-m-d H:i:s');

$CollectionDate= $_POST["specimen_collection_date"]?$_POST["specimen_collection_date"]:date('Y-m-d');


$CollectionTime= $_POST["form_hour"].':'.$_POST["form_minute"]. ':' .$_POST["form_ampm"];

if ($encounter == "")
$encounter = date("Ymd");

if ($_GET["mode"] == "new"){	
$Sql="insert into procedure_order (pid,groupname,user,authorized,activity,encounter_id,provider_id,order_completion_fasting,date,"
	."referral_to_specialist,patient_instructions,specimen_type,specimen_location,specimen_collection_date ,specimen_collection_time".
	",specimen_volume,order_status,order_priority,date_ordered,lab_id)values('".
		$_SESSION['pid']."','".$_SESSION['authProvider']."','".$_SESSION['authUser']."',$userauthorized,1,".$_SESSION['encounter'].",'"
		.$_POST["provider_id"]."','".$_POST["order_completion_fasting"]."',NOW(),'".$_POST["referral_to_specialist"]."','".$_POST["patient_instructions"]."','".
		$_POST["specimen_type"]
		."','".$_POST["specimen_location"]."','".$CollectionDate."','".$CollectionTime."','".$_POST["specimen_volume"]."','".$_POST["order_status"]
		."','".$_POST["order_priority"]."','".$_POST["date_ordered"]."','".$_POST["selectedLabCode"]."')";

		
	$return=sqlInsert($Sql);

/* Selected ICD Code from the Datagrid */

if (isset($_POST['selectedICD'])) {
	$IcdCode  = trim($_POST['selectedICD']);
    $IcdCodeArray =@explode('@@@', $IcdCode);   	
	foreach($IcdCodeArray as $k=>$IcdCode){
		$IcdCode = trim($IcdCode);
			if($IcdCode!=''){
		$selectedICDCode =@explode(':', $IcdCode);		
			$Sql="insert into procedure_order_code (forms_procedure_id,codename,codetype,codetext) values( ".$return.", '".$selectedICDCode[0]."', 1,'".$selectedICDCode[1]."')";	
	        $re =sqlInsert($Sql);
			}		
	
		}	 
	} 


/* Selected Loinc  Code from the Datagrid */

if (isset($_POST['selectedLOINC'])) {
	$loincCode  = $_POST['selectedLOINC'];	
    $lonicCodeArray =@explode('@@@', $loincCode);   	
	foreach($lonicCodeArray as $k=>$lonicCode){
		$lonicCode = trim($lonicCode);
			if($lonicCode!=''){
		$selectedLoincCode =@explode(':', $lonicCode);		
			$Sql="insert into procedure_order_code (forms_procedure_id,codename,codetype,codetext) values( ".$return.", '".$selectedLoincCode[0]."', 2,'".$selectedLoincCode[1]."')";	
	        $re =sqlInsert($Sql);
			}		
	
		}	 
	} 

$SqlCompendium="update procedure_compendium set procedure_order_id=$return where procedure_order_id=0";
$re =@mysql_query($SqlCompendium);


addForm($encounter, "Procedure Order", $return, "procedure_order", $pid, $userauthorized);

}




/*Update existing form */

elseif ($_GET["mode"] == "update") {
	$return=sqlInsert("update procedure_order set pid = {$_SESSION["pid"]},groupname='".$_SESSION["authProvider"]."',user='".$_SESSION["authUser"]."',authorized=$userauthorized,activity=1,date=NOW()
	,encounter_id='".$_SESSION["encounter"]."',
	provider_id = '".$_POST["provider_id"]."',
	order_completion_fasting='".$_POST["order_completion_fasting"]."',
	referral_to_specialist= '".$_POST["referral_to_specialist"]."',	
	patient_instructions= '".$_POST["patient_instructions"]."',
	specimen_type= '".$_POST["specimen_type"]."',
  	specimen_location = '".$_POST["specimen_location"]."',
    specimen_collection_date = '".$CollectionDate."',
  	specimen_collection_time = '".$CollectionTime."',
  	order_status='".$_POST["order_status"]."',  
  	order_priority='".$_POST["order_priority"]."',
  	date_ordered='".$_POST["date_ordered"]."',
  	lab_id='".$_POST["selectedLabCode"]."',  		
  	specimen_volume = '".$_POST["specimen_volume"]."'
	where procedure_order_id=$id");



 		$Sql="delete from procedure_order_code where forms_procedure_id=$id";
	    $re =@mysql_query($Sql);
	   
	  $SqlCompendium="update procedure_compendium set procedure_order_id=$id where procedure_order_id=0";
		$re =@mysql_query($SqlCompendium); 
	   
	  
	
	if (isset($_POST['loinc_code'])) {
	
		 $PrevLoincCode  = $_POST['loinc_code'];	
		  
		foreach($PrevLoincCode as $k=>$PreviousLoinc){
			$PreviousLoinc = trim($PreviousLoinc);
			if($PreviousLoinc!=''){
				$PrevLoinccode =@explode(':', $PreviousLoinc);					
					$Sql="insert into procedure_order_code (forms_procedure_id,codename,codetype,codetext) values( ".$id.", '".$PrevLoinccode[0]."', 2,'".$PrevLoinccode[1]."')";	
	        		$re =sqlInsert($Sql);			     
			}
		} 
	}	
	
	
	if (isset($_POST['icd_code'])) {
	
		 $PrevICDCode  = $_POST['icd_code'];	
		  
		foreach($PrevICDCode as $k=>$PreviousDiag){
			$PreviousDiag = trim($PreviousDiag);
			if($PreviousDiag!=''){
				$PreviousDiag =@explode(':', $PreviousDiag);					
					$Sql="insert into procedure_order_code (forms_procedure_id,codename,codetype,codetext) values( ".$id.", '".$PreviousDiag[0]."', 1,'".$PreviousDiag[1]."')";	
	        		$re =sqlInsert($Sql);			     
			}
		} 
	}	

		
	if (isset($_POST['selectedICD'])) {
	$IcdCode  = trim($_POST['selectedICD']);
    $IcdCodeArray =@explode('@@@', $IcdCode);   	
	foreach($IcdCodeArray as $k=>$IcdCode){
		$IcdCode = trim($IcdCode);
			if($IcdCode!=''){
		$selectedICDCode =@explode(':', $IcdCode);		
			$Sql="insert into procedure_order_code (forms_procedure_id,codename,codetype,codetext) values( ".$id.", '".$selectedICDCode[0]."', 1,'".$selectedICDCode[1]."')";	
	        $re =sqlInsert($Sql);
			}		
	
		}	 
	} 


/* Selected Loinc  Code from the Datagrid */

if (isset($_POST['selectedLOINC'])) {
	$loincCode  = trim($_POST['selectedLOINC']);
    $lonicCodeArray =@explode('@@@', $loincCode);   	
	foreach($lonicCodeArray as $k=>$lonicCode){
		$lonicCode = trim($lonicCode);
			if($lonicCode!=''){
		$selectedLoincCode =@explode(':', $lonicCode);		
			$Sql="insert into procedure_order_code (forms_procedure_id,codename,codetype,codetext) values( ".$id.", '".$selectedLoincCode[0]."', 2,'".$selectedLoincCode[1]."')";	
	        $re =sqlInsert($Sql);
			}		
	
		}	 
	} 
}

$_SESSION["encounter"] = $encounter;
formHeader("Redirecting....");
formJump();
formFooter();
?>