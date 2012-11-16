<?php
include_once("../../globals.php");
include_once($GLOBALS["srcdir"] . "/api.inc");

function procedure_order_report($pid, $encounter, $cols, $id) {
 $count = 0;
 $dataObject= sqlStatement ( "select * from procedure_order where procedure_order_id='$id'" ) ;
while($row=sqlFetchArray($dataObject))
{

$data =$row;
 if ($data) {
  print "<table>\n<tr>\n";
  foreach($data as $key => $value) {
   if ($key == "id" || $key == "pid" || $key == "user" || $key == "groupname" ||
       $key == "authorized" || $key == "activity" || 
       $value == "" || $value == "0000-00-00 00:00:00") {
    continue;
   }
   if ($value == "on") {
    $value = "yes";
   }
   $key=ucwords(str_replace("_"," ",$key)); 

	
   print "<td valign='top'><span class='bold'>$key: </span><span class='text'>$value</span></td>\n";

   $count++;
   if ($count == $cols) {
    $count = 0;
    print "</tr>\n<tr>\n";
   }
  }
  print "</tr>\n</table>\n";
 }
}
 
 
 
 /* report from forms_laborder_code for selected icd and loinc code */ 
 
 $dataObject= sqlStatement ( "select * from `procedure_order_code` where forms_procedure_id='$id'" );
	
while($data=sqlFetchArray($dataObject))
{
 if ($data) {
  print "<table>\n<tr>\n";
	$temp='';
  foreach($data as $key => $value) {
   if ($key == "forms_procedure_id"  || $key == "id" || $key == "codetext" ||$key == "codetype"  ||
       $value == "" || $value == "0000-00-00 00:00:00") {
    continue;
   }
	     if ($value == "on") {
    $value = "yes";
   }   
   $key=ucwords(str_replace("_"," ",$key));   
   
   $key=ucwords(str_replace("Codename","code",$key));
   
  // if($key=="codetype" && $value=='1')
 //   $key=ucwords(str_replace("codename","Diagnosis Code",$key)); 

    // if($key=="codetype" && $value=='2')
   // $key=ucwords(str_replace("codename","Loinc Code",$key));
	
   print "<td valign='top'><span class='bold'>$key: </span><span class='text'>$value</span></td>";

   $count++;
   if ($count == $cols) {
    $count = 0;
    print "</tr>\n<tr>\n";
   }
  }
  print "</tr>\n</table>\n";
 }
 }
}
?> 
