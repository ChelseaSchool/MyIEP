<?php
/** @file
 * @brief 	edit student medications
 * 
 */
 
 
//the authorization level for this page!
$MINIMUM_AUTHORIZATION_LEVEL = 100; //everybody check within



/*   INPUTS: $_GET['uid'],$_POST['uid'];
 *
 */

/**
 * Path for IPP required files.
 */

$system_message = "";

define('IPP_PATH','./');

/* eGPS required files. */
require_once(IPP_PATH . 'etc/init.php');
require_once(IPP_PATH . 'include/db.php');
require_once(IPP_PATH . 'include/auth.php');
require_once(IPP_PATH . 'include/log.php');
require_once(IPP_PATH . 'include/user_functions.php');
require_once(IPP_PATH . 'include/supporting_functions.php');
header('Pragma: no-cache'); //don't cache this page!

if(isset($_POST['LOGIN_NAME']) && isset( $_POST['PASSWORD'] )) {
    if(!validate( $_POST['LOGIN_NAME'] ,  $_POST['PASSWORD'] )) {
        $system_message = $system_message . $error_message;
        IPP_LOG($system_message,$_SESSION['egps_username'],'ERROR');
        require(IPP_PATH . 'index.php');
        exit();
    }
} else {
    if(!validate()) {
        $system_message = $system_message . $error_message;
        IPP_LOG($system_message,$_SESSION['egps_username'],'ERROR');
        require(IPP_PATH . 'index.php');
        exit();
    }
}
//************* SESSION active past here **************************

$uid="";
if(isset($_GET['uid'])) $uid= mysql_real_escape_string($_GET['uid']);
if(isset($_POST['uid'])) $uid = mysql_real_escape_string($_POST['uid']);

//get the medication for this student...
$medication_row="";
$medication_query="SELECT * FROM medication WHERE uid=$uid";
$medication_result = mysql_query($medication_query);
if(!$medication_result) {
        $error_message = "Database query failed (" . __FILE__ . ":" . __LINE__ . "): " . mysql_error() . "<BR>Query: '$medication_query'<BR>";
        $system_message= $system_message . $error_message;
        IPP_LOG($system_message,$_SESSION['egps_username'],'ERROR');
} else {
  $medication_row=mysql_fetch_array($medication_result);
}
$student_id=$medication_row['student_id'];

if($student_id=="") {
   //we shouldn't be here without a student id.
   echo "Unable to determine student id from medication uid value. Fatal, quitting";
   exit();
}

//check permission levels
$permission_level = getPermissionLevel($_SESSION['egps_username']);
if( $permission_level > $MINIMUM_AUTHORIZATION_LEVEL || $permission_level == NULL) {
    $system_message = $system_message . "You do not have permission to view this page (IP: " . $_SERVER['REMOTE_ADDR'] . ")";
    IPP_LOG($system_message,$_SESSION['egps_username'],'ERROR');
    require(IPP_PATH . 'security_error.php');
    exit();
}

$our_permission = getStudentPermission($student_id);
if($our_permission == "WRITE" || $our_permission == "ASSIGN" || $our_permission == "ALL") {
    //we have write permission.
    $have_write_permission = true;
}  else {
    $have_write_permission = false;
}

//************** validated past here SESSION ACTIVE WRITE PERMISSION CONFIRMED****************

$student_query = "SELECT * FROM student WHERE student_id = " . mysql_real_escape_string($student_id);
$student_result = mysql_query($student_query);
if(!$student_result) {
    $error_message = $error_message . "Database query failed (" . __FILE__ . ":" . __LINE__ . "): " . mysql_error() . "<BR>Query: '$student_query'<BR>";
    $system_message=$system_message . $error_message;
    IPP_LOG($system_message,$_SESSION['egps_username'],'ERROR');
} else {$student_row= mysql_fetch_array($student_result);}

//check if we are adding...
if(isset($_POST['edit_medication']) && $have_write_permission) {
   //minimal testing of input...
   if($_POST['medication_name'] == "") $system_message = $system_message . "You must give a medication name<BR>";
   else {
     $regexp = '/^\d\d\d\d-\d\d?-\d\d?$/';
     if(!preg_match($regexp,$_POST['start_date'])) { $system_message = $system_message . "Start Date must be in YYYY-MM-DD format<BR>"; }
     else {
       if(!($_POST['end_date'] == ""  || preg_match($regexp,$_POST['end_date']))) { $system_message = $system_message . "End Date must be in YYYY-MM-DD format<BR>"; }
       else {
          $update_query = "UPDATE medication SET medication_name='" . mysql_real_escape_string($_POST['medication_name']) . "',doctor='" . mysql_real_escape_string($_POST['doctor']) . "',start_date='" . mysql_real_escape_string($_POST['start_date']) . "',";
          if($_POST['end_date'] == "") $update_query .= "end_date=NULL";   //set no end date.
          else $update_query .= "end_date='" . mysql_real_escape_string($_POST['end_date']) . "'";
          //$system_message = $system_message . $add_query . "<BR>";
          $update_query .= " WHERE uid=$uid LIMIT 1";
          $update_result = mysql_query($update_query);
          if(!$update_result) {
            $error_message = $error_message . "Database query failed (" . __FILE__ . ":" . __LINE__ . "): " . mysql_error() . "<BR>Query: '$update_query'<BR>";
            $system_message=$system_message . $error_message;
            IPP_LOG($system_message,$_SESSION['egps_username'],'ERROR');
          }else {
             //redirect back...
             header("Location: " . IPP_PATH . "medication_view.php?student_id=" . $student_id);
          }
       }
     }
   }
   //$system_message = $system_message . $add_query . "<BR>";
}



?> 
<?php print_html5_primer(); ?>
<?php print_meta_for_html5($page_title)?>

    
    
    <script language="javascript" src="<?php echo IPP_PATH . "include/popcalendar.js"; ?>"></script>
    <SCRIPT LANGUAGE="JavaScript">
      function confirmChecked() {
          var szGetVars = "strengthneedslist=";
          var szConfirmMessage = "Are you sure you want to modify/delete the following:\n";
          var count = 0;
          form=document.medicationlist;
          for(var x=0; x<form.elements.length; x++) {
              if(form.elements[x].type=="checkbox") {
                  if(form.elements[x].checked) {
                     szGetVars = szGetVars + form.elements[x].name + "|";
                     szConfirmMessage = szConfirmMessage + "ID #" + form.elements[x].name + ",";
                     count++;
                  }
              }
          }
          if(!count) { alert("Nothing Selected"); return false; }
          if(confirm(szConfirmMessage))
              return true;
          else
              return false;
      }

      function noPermission() {
          alert("You don't have the permission level necessary"); return false;
      }
    </SCRIPT>
<?php print_bootstrap_head(); ?>
<link rel="stylesheet" href="//code.jquery.com/ui/1.10.4/themes/smoothness/jquery-ui.css">
<script src="//code.jquery.com/jquery-1.9.1.js"></script>
<script src="//code.jquery.com/ui/1.10.4/jquery-ui.js"></script>

<script> 
$(function() {
	$( ".datepicker" ).datepicker({ dateFormat: "yy-mm-dd" });
});
</script>
</HEAD>
    <BODY>
 <?php print_student_navbar($student_id, $student_row['first_name'] . " " . $student_row['last_name']) ; ?>       
 <?php print_jumbotron_with_page_name("Edit Medication", $student_row['first_name'] . " " . $student_row['last_name'], $our_permission) ; ?>
 <div class="container">
 <?php if ($system_message) { echo $system_message ;} ?>
 <h2>Edit and click 'Add'</h2>
<form role="form" name="edit_medication" enctype="multipart/form-data" action="<?php echo IPP_PATH . "edit_medication.php"; ?>" method="post" <?php if(!$have_write_permission) echo "onSubmit=\"return noPermission();\"" ?>>
<div class="form-group">
<input type="hidden" name="edit_medication" value="1">
<input type="hidden" name="uid" value="<?php echo $uid; ?>">
<div class="form-group">                          
<label>Medication Name</label>
<input class="form-control" type="text" tabindex="1" name="medication_name" value="<?php echo $medication_row['medication_name']; ?>" size="30" maxsize="254">
</div>
<div class="form-group">                            
<Label>Doctor</label>
<input class="form-control" type="text" tabindex="2" name="doctor" value="<?php echo $medication_row['doctor']; ?>" size="30" maxsize="254">
</div>
<div class="form-group">                        
<label>Medication Start Date (YYYY-MM-DD)</label>
<input class="form-control datepicker" pattern="\d{4}-\d{1,2}-\d{1,2}" type="datepicker" data-provide="datepicker" data-date-format="yyyy-mm-dd" tabindex="3" name="start_date" value="<?php echo $medication_row['start_date']; ?>">
</div>
<div class="form-group">                        
<label>Medication End Date (YYYY-MM-DD)</label>
<input class="form-control datepicker" pattern="\d{4}-\d{1,2}-\d{1,2}" type="datepicker" id="datepicker" data-provide="datepicker" data-date-format="yyyy-mm-dd" tabindex="4" name="end_date" value="<?php echo $medication_row['end_date']; ?>">
</div>                         
<button type="submit" class="btn btn-default" type="submit" tabindex="4" value="Edit">Edit Medication</button> 
</div></form>
                        
<!-- END add medication -->

                        
                
<?php print_complete_footer(); ?>    
</div> 
<?php print_bootstrap_js();?>
</BODY>
</HTML>
